<?php
error_reporting(E_ALL);
ini_set('display_errors', TRUE); 
ini_set('display_startup_errors', TRUE); 
date_default_timezone_set('Europe/London');

define('EOL',(PHP_SAPI == 'cli') ? PHP_EOL : '<br />');

// /** Include PHPExcel */
require_once dirname(__FILE__) . '/Classes/PHPExcel.php';
echo "POST";
$debug = var_export([$_POST , $_FILES], true);
echo $debug;

echo "FILES"

?>