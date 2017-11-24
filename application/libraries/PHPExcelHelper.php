<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
use Cocur\Slugify\Slugify;

define ("USERID_COLUMN", 'A');
define ("COLOR_FIRST_COLUMN", 'D');
define ("COLOR_LAST_COLUMN", 'O');
define ("UYE_TABLO_ISMI", "uye_bilgileri");

class PHPExcelHelper{
    
    private $objPHPExcel;
    public $slugger;
    public $sheetObj = array("tablo" => array(), "aidat" => array());
    public $response = array( "headers" => array( 
                                    "tablo" => array(), 
                                    "aidat" => array()
                                ), "content" => array(
                                    "tablo" => array(), 
                                    "aidat" => array()          
                                ) 
                            );
    public $month_grid =  array(    
                                    'Ocak' => 1, 
                                    'Şubat' => 2, 
                                    'Mart' =>3, 
                                    'Nisan' => 4, 
                                    'Mayıs' => 5, 
                                    'Haziran' => 6, 
                                    'Temmuz' => 7, 
                                    'Ağustos' => 8, 
                                    'Eylül' => 9, 
                                    'Ekim' => 10, 
                                    'Kasım' => 11, 
                                    'Aralık' => 12);
    
    function __construct($ss){
        $this->slugger = new Slugify(['separator' => '_']);
        $this->slugger->activateRuleset('turkish');
        $this->setSpreadsheet($ss);
    }
    
    function setSpreadsheet($ss){
        try{
            if($ss['error'])    throw new Exception('Error: ' . $ss['error']);
            $inputFile = $ss['tmp_name'];
            $extension = strtoupper(pathinfo($ss['name'], PATHINFO_EXTENSION));
            if($extension != 'XLSX' && $extension != 'ODS') throw new Exception('Wrong File Format');
            $inputFileType = PHPExcel_IOFactory::identify($inputFile);
            $objReader = PHPExcel_IOFactory::createReader($inputFileType);
            //Set PHPExcel Attributes
            // $objReader->setReadDataOnly(false);
            $this->objPHPExcel = $objReader->load($inputFile);
            $sheetNames = $this->objPHPExcel->getSheetNames();
            foreach($sheetNames as $sheetIndex=>$sheetName){
                $sheetNameArray = explode(";", $sheetName);
                if(count($sheetNameArray) == 2 ){
                    if($sheetNameArray[0] == "tablo"){
                        $sheetName = $this->slugger->slugify($sheetNameArray[1]);
                        $this->sheetObj["tablo"][$sheetIndex] = $sheetName;
                        $this->response["content"]["tablo"][$sheetName] = array();
                    } else if ($sheetNameArray[0] == "aidat"){
                        $sheetName = $this->slugger->slugify($sheetNameArray[1]);
                        $this->sheetObj["aidat"][$sheetIndex] = $sheetName;
                        $this->response["content"]["aidat"][$sheetName] = array();
                    }
                }
            }
            foreach($this->sheetObj as $type=>$content){
                foreach ($content as $sheetIndex => $sheetName){
                    $this->response["headers"][$type][$sheetName] = $this->headerFunc($type, $sheetIndex, $sheetName); 
                }
            }
        } catch(Exception $e){
            die($e->getMessage());
        } 
    }

    function headerFunc($type, $sheetIndex, $sheetName){
        $sheet = $this->objPHPExcel->getSheet($sheetIndex); 
        $highestRow = $sheet->getHighestRow(); 
        $highestColumn = $sheet->getHighestColumn();
        $ret = array(
            'sheet' => $sheet, 
            'highestRow' => $highestRow,
            'highestColumn' => $highestColumn,
            'month_grid' => $this->month_grid);
        switch ($type) {
            case 'aidat':
                $colorArray = $this->getColorArray($sheet);
                $ret['colorArray'] =  $colorArray;
                break;
            case "tablo":
                $attributes = $sheet->rangeToArray('A1:' . $highestColumn . "1", NULL, TRUE, FALSE)[0];
                foreach ($attributes as &$attr) {
                    $attr = $this->slugger->slugify($attr);
                }
                $ret["attributes"] = $attributes;
                break;
        }
        return $ret;
    }

    function getColorArray($sheet){
        
        //Get Color - Month Relation
        $colorArray = array();
        $colors = $sheet->rangeToArray( COLOR_FIRST_COLUMN.'1:'.COLOR_LAST_COLUMN.'1', NULL, TRUE, FALSE)[0];
        $colorLastColumn = COLOR_LAST_COLUMN;   
        $colorLastColumn++;
        for ($cnt = 0, $column = COLOR_FIRST_COLUMN; $column != $colorLastColumn; $column++, $cnt++) {
            $color = $sheet->getStyle($column.'1')->getFill()->getStartColor()->getARGB();
            $colorArray[$color] = $colors[$cnt];
        }
        return $colorArray;
    } 
    
    
    function objectify(){
        foreach ($this->sheetObj as $type => $content) {
            foreach ($content as $tableName) {
                extract($this->response["headers"][$type][$tableName], EXTR_PREFIX_SAME, "wddx");
                for ($row = 2; $row <= $highestRow; $row++){
                    $rowData = array();
                    switch ($type) {
                        case 'tablo':
                            $rawRowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, NULL, TRUE, FALSE)[0];
                            for ($attrow = 0; $attrow < count($rawRowData); $attrow++){
                                $rowData[$attributes[$attrow]] = $rawRowData[$attrow];
                            }
                            if($tableName == UYE_TABLO_ISMI){
                                $rowData["telefon"] = is_numeric($rowData["telefon"]) ? $rowData['telefon'] : null;
                                $rowData["bagis"] = is_numeric($rowData["bagis"]) ? $rowData['bagis'] : null;
                            }
                            $this->response["content"][$type][$tableName][] = $rowData;
                            break;
                        case 'aidat':
                            $rowData = $this->getPaymentAnnually($row, $tableName);
                            foreach ($rowData as $rd) {
                                $this->response["content"][$type][$tableName][] = $rd;
                            }
                            break;
                    }
                }
                if($tableName == UYE_TABLO_ISMI){

                }
                
            }
        }
        return $this->response;
    }
    
    function getPaymentAnnually($row, $year){
        extract($this->response["headers"]["aidat"][$year], EXTR_PREFIX_SAME, "wddx"); 
        $paymentArray = array();
        $RowData = $sheet->rangeToArray(USERID_COLUMN. $row . ':' . COLOR_LAST_COLUMN . $row, NULL, TRUE, FALSE)[0];
        if($RowData[0] != null) {
            for($cnt = 1, $column = COLOR_FIRST_COLUMN; $column <= COLOR_LAST_COLUMN; $column++, $cnt++){
                $targetColor = $sheet->getStyle($column.$row)->getFill()->getStartColor()->getARGB();
                $uye_no = $RowData[0];
                $monthlyPayment = array( 'uye_no' => $uye_no, 
                                'aidat_tarihi'=> $this->dateParser($cnt, $year),
                                'odeme_tipi'=> $RowData[$cnt+2]);
                if($targetColor == "FF000000" && $monthlyPayment["odeme_tipi"] != null){
                    $monthlyPayment['odendigi_tarih'] = $this->dateParser($cnt, $year); 
                } else if(array_key_exists($targetColor, $colorArray)) {
                    $monthlyPayment['odendigi_tarih'] = $this->dateParser($month_grid[$colorArray[$targetColor]], $year);   
                } else{
                    $monthlyPayment['odendigi_tarih'] = null;
                }
                $paymentArray[] = $monthlyPayment;
            };  
        }
        return $paymentArray;
    }
    
    function dateParser($month, $year){
        $date = date_create($year."/".$month."/01");
        return date('Y-m-d h:i:s',$date->getTimestamp());
    }

    function toJSON(){
        return json_encode($this->response);
    }
}
?>