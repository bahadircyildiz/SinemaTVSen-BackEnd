<?php
error_reporting(E_ALL);
ini_set('display_errors', TRUE); 
ini_set('display_startup_errors', TRUE); 
date_default_timezone_set('Europe/London');

define('EOL',(PHP_SAPI == 'cli') ? PHP_EOL : '<br />');


//Excel File constants

define("USERINFO_LAST_COL", 7);

// /** Include PHPExcel */
require_once dirname(__FILE__) . '/Classes/PHPExcel.php';

$response = [];

if(isset($_FILES['spreadsheet'])){
    if($_FILES['spreadsheet']['tmp_name']){
        if(!$_FILES['spreadsheet']['error']){
            $inputFile = $_FILES['spreadsheet']['tmp_name'];
            $extension = strtoupper(pathinfo($_FILES['spreadsheet']['name'], PATHINFO_EXTENSION));
            if($extension == 'XLSX' || $extension == 'ODS'){
                //Read spreadsheeet workbook
                try {
                    
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
                
                $attributes = $sheet->rangeToArray('A1:' . $highestColumn . "1", NULL, TRUE, FALSE)
                //Loop through each row of the worksheet in turn
                for ($row = 2; $row <= $highestRow; $row++){ 
                        //  Read a row of data into an array
                        $rowData = $sheet->rangeToArray('A' . $row . ':' . USERINFO_LAST_COL . $row, NULL, TRUE, FALSE);
                        $person = [];
                        for ($attrow = 0; $attrow <= USERINFO_LAST_COL; $attrow++){
                            $person[$attributes[$attrow]] = $rowData[$attrow];
                        }
                        array_push($response, $person);                        
                        //Insert into database
                }
                var_dump($response);
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