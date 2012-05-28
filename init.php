<?php

/**
 * @author    Paul Dragoonis <dragoonis@php.net>
 * @license   http://opensource.org/licenses/mit-license.php MIT
 * @package   PPI
 * @link      www.ppi.io
 */
defined('PPI_VERSION') || define('PPI_VERSION', '2.0');
defined('DS') || define('DS', DIRECTORY_SEPARATOR);
defined('PPI_PATH') || define('PPI_PATH', realpath(__DIR__) . '/');
defined('PPI_VENDOR_PATH') || define('PPI_VENDOR_PATH', dirname(PPI_PATH) . '/Vendor/');

$path = dirname(PPI_PATH);