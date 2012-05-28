<?php
/**
 *
 * @version   1.0
 * @author    Paul Dragoonis <dragoonis@php.net>
 * @license   http://opensource.org/licenses/mit-license.php MIT
 * @copyright Digiflex Development
 * @package   Model
 */
namespace PPI\Model;
class Email_Template extends PPI_Model {
    private $_sender;
    private $_recipient;
    private $_body;
    private $_subject;
    private $_replacerData;
    private $_templateName;
    private $_headers;

    function __construct() {
        parent::__construct('ppi_email_template', 'id');
    }

    /**
     * Set the template to be used
     * @param string $p_sTemplate Template Name
     * @param array $p_aReplacerData Replacer Tags
     * @return $this (fluent interface)
     */
    function setTemplate($p_sTemplate, $p_aReplacerData = array()) {
        // get the template data, does the template exist ?
        $rows = parent::getList("name = '$p_sTemplate'");
        if(count($rows) < 1) {
            throw new PPI_Exception('Trying to use Email Template: '.$p_sTemplate . ' but it doesn\'t exist');
        }
        // set the replacer data if its been specified here.
        if(count($p_aReplacerData) > 0) {
            $this->setReplacerData($p_aReplacerData);
        }
        $this->_subject         = $rows[0]['subject'];
        $this->_body             = $rows[0]['body'];
        $this->_sender            = $rows[0]['from'];
        $this->_templateName     = $p_sTemplate;
        return $this;
    }

    /**
     * Replace the data from the templates
     * @return void
     */
    function replaceData() {

        if(count($this->_replacerData) > 0) {
            foreach($this->_replacerData as $key => $replace) {
                // Try to replace the subject
                if($this->_subject != '') {
                    $this->_subject = str_ireplace('['.$key.']', $this->_replacerData[$key], $this->_subject);
                }
                if($this->_body != '') {
                    $this->_body = str_ireplace('['.$key.']', $this->_replacerData[$key], $this->_body);
                }
            }
        }
    }

    /**
     * Send the email out
     */
    function sendMail() {
        // check required properties (to,from,subject,[body](warning(maybe))
        if(is_array($this->_recipient) && count($this->_recipient) < 1) {
            throw new PPI_Exception('Unable to send email: No recipient specified');
        } elseif(is_string($this->_recipient) && $this->_recipient == '') {
            throw new PPI_Exception('Unable to send email: No recipient specified');
        }
        if($this->_sender == '') {
            throw new PPI_Exception('Unable to send email: No sender specified');
        }
        if($this->_subject == '') {
            throw new PPI_Exception('Unable to send email: No subject specified');
        }

        $this->setHeaders();
        $this->replaceData();

        // send the mail(s)
        if(is_array($this->_recipient)) {
            foreach($this->_recipient as $to) {
                $ret = mail($to, $this->_subject, $this->_body, $this->_headers);
            }
        } else {
            $ret = mail($this->_recipient, $this->_subject, $this->_body, $this->_headers);
        }
        // this needs tested on the live server so mail() can work.

        // log the mail send - this is a bug, it will try to insert to ppi_email_templates - this should insert to email_log instead.
        /*
        $oLog = new PPI_Model_Log();
        $oLog->addEmailLog(array(
            'to'       => $this->_recipient,
            'subject'  =>  $this->_subject,
            'body'     => $this->_body,
            'headers'  => $this->_headers,
            'tpl_name' => $this->_templateName
        ));
        */
    }

    /**
     * Set the email headers
     * @return void
     */
    function setHeaders() {
        $this->_headers     = "Content-Type: text/html; charset=iso-8859-1\n";
        $this->_headers     .= "From: <".$this->_sender.">\nReply-to: <noreply@".getHostname().">\n";
        $this->_headers     .= "X-mailer: php\r\n";
        $this->_headers     .= "X-Priority: 1\r\n";
    }

    /**
     * Set the Sender
     * @param string $p_sSender The Sender
     * @return $this (fluent interface)
     */
    function setSender($p_sSender) {
        $this->_sender = $p_sSender;
        return $this;
    }

    /**
     * Set the Recipient
     * @param mixed $p_mRecipient The Recipient(s)
     * @return $this (fluent interface)
     */
    function setRecipient($p_mRecipient) {
        if(is_array($p_mRecipient)) {

        } elseif(is_string($p_mRecipient)) {
            $this->_recipient = $p_mRecipient;
        }
        return $this;
    }

    /**
     * Set the Subject
     * @param string $p_sSubject The Subject
     * @return $this (fluent interface)
     */
    function setSubject($p_sSubject) {
        $this->_subject = $p_sSubject;
        return $this;
    }
    /**
     * Set the replacer data
     * @param array $p_aData The replacer tags
     * @return $this (fluent interface)
     */
    function setReplacerData(array $p_aData) {
        $this->_replacerData = $p_aData;
        return $this;
    }

    /**
     * Get the addedit structure for Formbuilder
     * @param string $p_sMode The Mode
     * @return array
     */
    function getAddEditFormStructure($p_sMode) {
        $structure = array(
            'fields' => array(
                'title'       => array('type' => 'text', 'size' => 55, 'label' => 'Name'),
                'code'       => array('type' => 'text', 'size' => 55, 'label' => 'Code'),
                'description' => array('type' => 'textarea', 'rows' => 10, 'cols' => 45, 'label' => 'Description')
            ),
            'rules' =>  array(
                'title'       => array('type' => 'required', 'message' => 'Template name cannot be blank'),
                'code'       => array('type' => 'required', 'message' => 'Template code cannot be blank'),
                'description' => array('type' => 'required', 'message' => 'Description cannot be blank')
            )
        );
        return $structure;
    }
}