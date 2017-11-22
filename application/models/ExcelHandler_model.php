<?php

if ( ! defined('BASEPATH')) exit('No direct script access allowed');
define ("AIDAT_TABLO_ISMI", 'aidat');
define ("UYE_TABLO_ISMI", 'uye_bilgileri');
define ("DEV_TABLO_ISMI", 'dev_bilgileri');

class ExcelHandler_model extends CI_Model{
        public $month_grid =  array( 'Ocak', 
                                'Şubat', 
                                'Mart', 
                                'Nisan', 
                                'Mayıs', 
                                'Haziran', 
                                'Temmuz', 
                                'Ağustos', 
                                'Eylül', 
                                'Ekim', 
                                'Kasım', 
                                'Aralık');
    
    function get_aliases(){
        $query = $this->db->get('aliases');
        return $query->result();
    }
    
    function add_alias($data){
        $deferred = new React\Promise\Deferred();
        $query = $this->db->insert('aliases', $data);
        $deferred->resolve($query->result);
    }
    
    function update_alias($data){
        $deferred = new React\Promise\Deferred();
        $query = $this->db->where('id', $data['id']);
        $query = $this->db->update('aliases', $data);
        $deferred->resolve($query->result);
    }
    
    function delete_alias(){
        $deferred = new React\Promise\Deferred();
        $query = $this->db->where('id', $this->uri->segment(3));
        $query = $this->db->delete('aliases');
        $deferred->resolve($query->result);
    }
    
    function add_alias_list($data){
        // $deferred = new React\Promise\Deferred();
        $query =$this->db->insert_batch('aliases', $data);
        // $deferred->resolve($query->result);
        return $query;
    }
    
    function empty_aliases(){
        $deferred = new React\Promise\Deferred();
        $this->db->empty_table('aliases');
        $deferred->resolve();
    }
    
    function replace_aliases($data){
        $this->db->replace('aliases', $data);
    }
    
    function replace_alias_list($data){
        foreach ($data as $d) {
            $this->ExcelHandler_model->replace_aliases($d);
        }
    }
    
    function add_payment_list($data){
        $query = $this->db->insert_batch(AIDAT_TABLO_ISMI, $data);
        return $query;
    }
    
    function getPaymentsByUyeNo($uye_no = null){
        $response = array('status' => 200, 'message' => 'OK');
        try {
            if($uye_no == null){
                $query = $this->db->get(AIDAT_TABLO_ISMI);
            } else{
                $query = $this->db->get_where(AIDAT_TABLO_ISMI, array('uye_no' => $uye_no));   
            }
            $result = $query->result(); $ret = array();
            if ($result == null) throw new Exception($this->db->_error_message);
            foreach ($result as $r) {
                if(!array_key_exists($r->uye_no, $ret)) $ret[$r->uye_no] = array();
                array_push($ret[$r->uye_no], $r);
            }
            $response['content'] = $ret;
        } catch (Exception $e) {
            $response = array('status' => $this->db->_error_number, 'message' => $e->getMessage());
        }
        return $response;
    }
    
    function getUserByUyeNo($uye_no = null){
        $response = array();
        try {
            if($uye_no == null){
                $query = $this->db->get(UYE_TABLO_ISMI);
            } else{
                $query = $this->db->get_where(UYE_TABLO_ISMI, array('uye_no' => $uye_no));   
            }
            $result = $query->result(); $ret = array();
            if ($result == null) throw new Exception($this->db->_error_message);
            $response['content'] = $result;
        } catch (Exception $e) {
            $response = array('status' => $this->db->_error_number, 'message' => $e->getMessage());
        }
        
        return $response;
    }
    
    function stringDateParser($date){
        $timeObj = strtotime($date);
        $year = date('Y', $timeObj);
        $month = date('n', $timeObj);
        return array('year' => $year, 'month' => $this->month_grid[$month-1]);
    }
    
    function getDebtsTillToday($uye_no){
        $response = array();
        setlocale(LC_TIME, 'tr_TR.UTF-8');
        try {
            $sql = $this->db
                    ->select('*')
                    ->from(AIDAT_TABLO_ISMI)
                    ->where(array('aidat_tarihi <=' => date('Y-m-d h:i:s'), 'odendigi_tarih' => null, 'uye_no' => $uye_no))
                    ->order_by('aidat_tarihi ASC')
                    ->get();
            foreach ($sql->result() as $q) {
                $aidat_tarihi = $this->ExcelHandler_model->stringDateParser($q->aidat_tarihi);
                $q->aidat_ayi = $aidat_tarihi['month'];
                $q->aidat_yili = $aidat_tarihi['year'];
                array_push($response, $q);
            }
        } catch (Exception $e) {
            $response = array('status' => $this->db->_error_number, 'message' => $this->db->_error_message);
        }
        return $response;
    }

    function object_excel($params){
        $this->load->library('PHPExcelHelper', $params);
        $obj = $this->phpexcelhelper->objectify();
        return $obj;
    }

    
    function parse_excel($params){
        $this->load->library('PHPExcelHelper', $params);
        $this->load->dbforge();
        $obj = $this->phpexcelhelper->objectify();
        try {
            $this->db->empty_table(AIDAT_TABLO_ISMI);
            // $this->db->query("ALTER TABLE ".AIDAT_TABLO_ISMI." AUTO_INCREMENT 1");
            foreach ($obj["content"] as $type => $content) {
                foreach ($content as $tableName => $data) {
                    if($type == "aidat"){
                        $tableName = AIDAT_TABLO_ISMI;
                    } else if ($type == "tablo") {
                        if($this->db->table_exists($tableName)){
                            $this->db->empty_table($tableName);
                        } else {
                            $this->dbforge->create_table($tableName, false, $obj["headers"][$type][$tableName]);
                        }
                    }
                    $result = $this->db->insert_batch($tableName, $data);
                    if(!$result) {
                        throw new Exception($this->db->_error_message);
                    }
                    // foreach ($data as &$d) {
                    //     $result = $this->db->insert($tableName, $d);
                    //     if($result){
                    //         if($type == AIDAT_TABLO_ISMI){
                    //             if(array_key_exists("aidat_tarihi", $d)){
                    //                 $aidat_tarihi = $this->ExcelHandler_model->stringDateParser($d['aidat_tarihi']);
                    //                 unset($d['aidat_tarihi']);
                    //                 $d['aidat_yili'] = $aidat_tarihi['year'];
                    //                 $d['aidat_ayi'] = $aidat_tarihi['month'];
                    //             }
                    //             if(array_key_exists("odendigi_tarih", $d)){
                    //                 $odendigi_tarih = $this->ExcelHandler_model->stringDateParser($d['odendigi_tarih']);
                    //                 unset($d['odendigi_tarih']);
                    //                 $d['odendigi_ay'] = $odendigi_tarih['month'];
                    //             }
                    //         }
                    //     } else {
                    //         throw new Exception($this->db->_error_message);
                    //     }
                    // }

                }
            }
            $response = $obj["content"];
        } 
        catch (Exception $e ) {
            $response = $e->getMessage();
        }
        return $response;
    }
}

?>