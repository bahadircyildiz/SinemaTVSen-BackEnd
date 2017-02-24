<?php
use Cocur\Slugify\Slugify;

class PHPExcelHelper{
    
    private $objPHPExcel;
    private $worksheetNames;
    private $response = array();
    
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
            $this->objPHPExcel = $objReader->load($inputFile);
            $this->worksheetNames = $objReader->listWorksheetNames($inputFile);
            $this->response['status'] = 200;
            $this->response['message'] = "OK";
        } catch(Exception $e){
            $this->response['status'] = 400;
            $this->response['message'] = $e->getMessage(); 
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
        $sheet = $this->objPHPExcel->getSheet(0); 
        $highestColumn = $sheet->getHighestColumn();
        $attributes = $sheet->rangeToArray('A1:' . $highestColumn . "1", NULL, TRUE, FALSE)[0];
        foreach ($attributes as $key => $val) {
            $slugged = $slugify->slugify($val);
            $response[$slugged] = $val;
        }
        $this->response['content'] = $response;
        return $this;
    }
    
    function objectify(){
        $this->response['content'] = array();
        for ($worksheetCount = 0; $worksheetCount < count($this->worksheetNames); $worksheetCount++) {
            //Get worksheet dimensions
            $sheet = $this->objPHPExcel->getSheet($worksheetCount); 
            $highestRow = $sheet->getHighestRow(); 
            $highestColumn = $sheet->getHighestColumn();
            $attributes = $sheet->rangeToArray('A1:' . $highestColumn . "1", NULL, TRUE, FALSE)[0];
            //Loop through each row of the worksheet in turn
            $this->response['content'][$this->worksheetNames[$worksheetCount]] = array();
            
            for ($row = 2; $row <= $highestRow; $row++){ 
                //  Read a row of data into an array
                $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, NULL, TRUE, FALSE)[0];
                $person = array();
                for ($attrow = 0; $attrow < count($rowData); $attrow++){
                    $person[$attributes[$attrow]] = $rowData[$attrow];
                }
                array_push($this->response['content'][$this->worksheetNames[$worksheetCount]], $person);                        
                //Insert into database
            }
        }
        return $this;
    }
}
?>