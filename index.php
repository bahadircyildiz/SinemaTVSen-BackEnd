<?php
error_reporting(E_ALL);
ini_set('display_errors', TRUE); 
ini_set('display_startup_errors', TRUE); 
header('Content-Type: application/json');

define('EOL',(PHP_SAPI == 'cli') ? PHP_EOL : '<br />');


//Excel File constants

define("USERINFO_LAST_COL", "H");

// /** Include PHPExcel */
// require_once dirname(__FILE__) . '/lib/ExcelParser/Classes/PHPExcel.php';
require 'vendor/autoload.php';
require 'PHPExcelHelper.php';

if(isset($_FILES['spreadsheet'])){
    
    $spreadsheet = new PHPExcelHelper($_FILES['spreadsheet']);
    echo $spreadsheet->getSluggedAliases()->toJSON();
    // $response = [];

    // if($_FILES['spreadsheet']['tmp_name']){
    //     if(!$_FILES['spreadsheet']['error']){
    //         $inputFile = $_FILES['spreadsheet']['tmp_name'];
    //         $extension = strtoupper(pathinfo($_FILES['spreadsheet']['name'], PATHINFO_EXTENSION));
    //         if($extension == 'XLSX' || $extension == 'ODS'){
    //             //Read spreadsheeet workbook
    //             try {
    //                 $inputFileType = PHPExcel_IOFactory::identify($inputFile);
    //                 $objReader = PHPExcel_IOFactory::createReader($inputFileType);
    //                 $objPHPExcel = $objReader->load($inputFile);
    //                 $worksheetNames = $objReader->listWorksheetNames($inputFile);
    //             } catch(Exception $e) {
    //                 $response['status'] = 400;
    //                 $response['message'] = $e->getMessage();
    //                 die($e->getMessage());
    //             }

    //             $response['content'] = array();
    //             for ($worksheetCount = 0; $worksheetCount < count($worksheetNames); $worksheetCount++) {
    //                 //Get worksheet dimensions
    //                 $sheet = $objPHPExcel->getSheet($worksheetCount); 
    //                 $highestRow = $sheet->getHighestRow(); 
    //                 $highestColumn = $sheet->getHighestColumn();
    //                 $attributes = $sheet->rangeToArray('A1:' . $highestColumn . "1", NULL, TRUE, FALSE)[0];
    //                 //Loop through each row of the worksheet in turn
    //                 $response['content'][$worksheetNames[$worksheetCount]] = array();
                    
    //                 for ($row = 2; $row <= $highestRow; $row++){ 
    //                     //  Read a row of data into an array
    //                     $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, NULL, TRUE, FALSE)[0];
    //                     $person = array();
    //                     for ($attrow = 0; $attrow < count($rowData); $attrow++){
    //                         $person[$attributes[$attrow]] = $rowData[$attrow];
    //                     }
    //                     array_push($response['content'][$worksheetNames[$worksheetCount]], $person);                        
    //                     //Insert into database
    //                 }
    //                 $response['status'] = "200";
    //                 $response['message'] = "OK";
    //             }
    //         }
    //         else{
    //             $response['status'] = 400;
    //             $response['message'] = "Please upload an XLSX or ODS file";
    //         }
    //     }
    //     else{
    //         $response['status'] = 400;
    //         $response['message'] = $_FILES['spreadsheet']['error'];
    //     }
    // }
    
    // echo json_encode($response);
}
?>