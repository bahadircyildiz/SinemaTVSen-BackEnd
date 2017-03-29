<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
use Cocur\Slugify\Slugify;

define ("USERDATA_LAST_COLUMN", 'H');
define ("COLOR_FIRST_COLUMN", 'S');
define ("COLOR_LAST_COLUMN", 'AD');
define ("FIRST_YEAR", 2015);
define ("FIRST_YEAR_FIRST_COLUMN", 'I');
define ("FIRST_YEAR_LAST_COLUMN", 'Q');

class PHPExcelHelper{
    
    
    private $objPHPExcel;
    public $response;
    public $headVars = array();
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
            $this->headVars = $this->headerFunc();
        } catch(Exception $e){
            die($e->getMessage());
        } 
    }
    
    
    function toJSON(){
        return json_encode($this->response);
    }
    
    
    function getSluggedAliases(){
        // $this->response['content'] = array();
        $response = array();
        $slugify = new Slugify(['separator' => '_']);
        $slugify->activateRuleset('turkish');
        extract($this->head, EXTR_PREFIX_SAME, "wddx");
        foreach ($attributes as $key => $val) {
            $entry = array("alias" => $val, "slug" => $slugify->slugify($val)) ;
            array_push($response, $entry);
        }
        $this->response = $response;
        return $this;
    }
    
    function headerFunc(){
        $sheet = $this->objPHPExcel->getSheet(0); 
        $highestRow = $sheet->getHighestRow(); 
        $highestColumn = $sheet->getHighestColumn();
        $attributes = $sheet->rangeToArray('A1:' . USERDATA_LAST_COLUMN . "1", NULL, TRUE, FALSE)[0];
        $colorArray = $this->getColorArray($sheet);
        $month_grid = $this->month_grid;
        $ret = array('sheet' => $sheet, 'highestRow' =>$highestRow, 'highestColumn' =>$highestColumn, 'attributes'=>$attributes, 'colorArray' => $colorArray, 'month_grid' =>$month_grid);
        return $ret;
    }
    
    
    
    function objectify(){
        $response = array(); $users = array(); $payments = array();
        
        //Get worksheet dimensions
        // $sheet = $this->objPHPExcel->getSheet(0); 
        // $highestRow = $sheet->getHighestRow(); 
        // $highestColumn = $sheet->getHighestColumn();
        extract($this->headVars, EXTR_PREFIX_SAME, "wddx");
        
        
        for ($row = 2, $year = 0; $row <= $highestRow; $row++){ 
            //Set User Data First;
            $user = $this->getUserData($row);
            array_push($users, $user);
            //Get 2015 Data Manually
            $start_col = FIRST_YEAR_FIRST_COLUMN; $end_col = FIRST_YEAR_LAST_COLUMN; $year = FIRST_YEAR;
            $payments = array_merge($this->getPaymentAnnually($start_col, $end_col, $row, $year, $user), $payments);
            //Insert into database
        }
        $response = array('userinfo' => $users, 'payments' => $payments);
        $this->response = $response;
        return $this;
    }
    
    function getUserData($row){
        extract($this->headVars, EXTR_PREFIX_SAME, "wddx");
        $slugify = new Slugify(['separator' => '_']);
        $slugify->activateRuleset('turkish');
        $userData = $sheet->rangeToArray('A' . $row . ':' . USERDATA_LAST_COLUMN . $row, NULL, TRUE, FALSE)[0];
        $user = array();
        for ($attrow = 0; $attrow < count($userData); $attrow++){
            $user[$slugify->slugify($attributes[$attrow])] = $userData[$attrow];
        }
        return $user;
    }
    
    function getColorArray($sheet){
        
        //Get Color - Month Relation
        $colorArray = array();
        $colors = $sheet->rangeToArray( COLOR_FIRST_COLUMN.'1:'.COLOR_LAST_COLUMN.'1', NULL, TRUE, FALSE)[0];
        $colorLastColumn = COLOR_LAST_COLUMN;   
        $colorLastColumn++;
        for ($cnt = 0, $column = COLOR_FIRST_COLUMN; $column != $colorLastColumn; $column++, $cnt++) {
            $color = $sheet->getStyle($column.'1')->getFill()->getStartColor()->getRGB();
            $colorArray[$color] = $colors[$cnt];
            // array_push($colorArray, array('name'=>$colors[$cnt], 'color' =>$color));
        }
        return $colorArray;
    } 
    
    function dateParser($month, $year){
        $date = date_create($year."/".$month."/01");
        return date('Y-m-d h:i:s',$date->getTimestamp());
    }
    
    function getPaymentAnnually($start_col, $end_col, $row, $year, $user){
        extract($this->headVars, EXTR_PREFIX_SAME, "wddx");
        $paymentArray = array(); 
        // $fyfc = FIRST_YEAR_FIRST_COLUMN;
        // $year = FIRST_YEAR;
        
        $YearMonths = $sheet->rangeToArray($start_col. '1' . ':' . $end_col . '1', NULL, TRUE, FALSE)[0];
        $YearData = $sheet->rangeToArray($start_col. $row . ':' . $end_col . $row, NULL, TRUE, FALSE)[0];
        // var_dump($colorArray);
        $cellExists = true;
        for (; $cellExists == true;) {
            // echo "Looking for columns between " . $start_col . $row . " and " . $end_col . $row . PHP_EOL;
            for ($column_counter = 0, $column = $start_col; $column_counter < count($YearData); $column_counter++, $column++){
                
                $targetColor = $sheet->getStyle($column.$row)->getFill()->getStartColor()->getRGB();
                if($YearMonths[$column_counter] != null){
                    $tempP = array( 'uye_no' => $user['uye_no'], 
                                    'aidat_tarihi'=> $this->dateParser($month_grid[$YearMonths[$column_counter]], $year),
                                    'odeme_tipi'=> $YearData[$column_counter]);
                    // var_dump($targetColor . "   " . $start_col);
                    if($tempP['odeme_tipi'] == '1'){
                        $tempP['odendigi_tarih'] = $this->dateParser($month_grid[$YearMonths[$column_counter]], $year); 
                    }
                    else if(array_key_exists($targetColor, $colorArray)) {
                        $tempP['odendigi_tarih'] = $this->dateParser($month_grid[$colorArray[$targetColor]], $year);   
                    }
                    array_push($paymentArray, $tempP);   
                }
            }
            $end_col++;
            //Check for Next Year
            // $cellExists = $sheet->cellExists($end_col . $row);
            $cellExists_val = $sheet->getCell($end_col . $row)->getValue();
            $cellExists_rgb = $sheet->getStyle($end_col . $row)->getFill()->getStartColor()->getRGB();
            // echo "Cell Exists in Cell " . $end_col . $row . " and data: " . $cellExists_val . " rgb: ". $cellExists_rgb . PHP_EOL;
            if($cellExists_val == "" && $cellExists_rgb == "000000") $cellExists = false;
            if($cellExists){
                $start_col = $end_col;
                $start_col++;
                for($x = 0; $x<12; $x++) $end_col++;
                $year++;
            }
            
        }
        // var_dump($paymentArray);
        // $end_col++;
        // $cellExists = $sheet->cellExists($end_col . $row); $recurse = array();
        // if ($cellExists){
        //     $start_col = $end_col;
        //     $start_col ++;
        //     for($x = 0; $x<12; $x++) $end_col++;
        //     $recurse = $this->getPaymentAnnually($start_col, $end_col, $row, $year+1, $user);
        // }
        return $paymentArray;
    }
    
    
}
?>