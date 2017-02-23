<?php
error_reporting(E_ALL);
ini_set('display_errors', TRUE); 
ini_set('display_startup_errors', TRUE); 
date_default_timezone_set('Europe/London');

define('EOL',(PHP_SAPI == 'cli') ? PHP_EOL : '<br />');

// /** Include PHPExcel */
require_once dirname(__FILE__) . '/Classes/PHPExcel.php';

var_dump($_FILES);

if(isset($_FILES['spreadsheet'])){
    echo "Found Spreadsheet file";
    if($_FILES['spreadsheet']['tmp_name']){
        if(!$_FILES['spreadsheet']['error']){
            echo "No Errors on Spreadsheet file";
            $inputFile = $_FILES['spreadsheet']['tmp_name'];
            $extension = strtoupper(pathinfo($_FILES['spreadsheet']['name'], PATHINFO_EXTENSION));
            echo "File extension " . $extension . " Input File" . $inputFiles;
            if($extension == 'XLSX' || $extension == 'ODS'){
                //Read spreadsheeet workbook
                try {
                    echo "Reading Spreadsheet file";
                    $inputFileType = PHPExcel_IOFactory::identify($inputFile);
                    $objReader = PHPExcel_IOFactory::createReader($inputFileType);
                    $objPHPExcel = $objReader->load($inputFile);
                } catch(Exception $e) {
                    die($e->getMessage());
                }
                //Get worksheet dimensions
                $sheet = $objPHPExcel->getSheet(0); 
                $highestRow = $sheet->getHighestRow(); 
                $highestColumn = $sheet->getHighestColumn();
        
                //Loop through each row of the worksheet in turn
                for ($row = 1; $row <= $highestRow; $row++){ 
                        //  Read a row of data into an array
                        $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, NULL, TRUE, FALSE);
                        //Insert into database
                        var_dump($rowData);
                }
            }
            else{
                echo "Please upload an XLSX or ODS file";
            }
        }
        else{
            echo $_FILES['spreadsheet']['error'];
        }
    }
}
?>