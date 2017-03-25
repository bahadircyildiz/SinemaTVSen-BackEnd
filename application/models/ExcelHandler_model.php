<?php

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
        $query = $this->db->insert_batch('payments', $data);
        return $query;
    }
    
    function getPaymentsByUyeNo($uye_no = null){
        $response = array('status' => 200, 'message' => 'OK');
        try {
            if($uye_no == null){
                $query = $this->db->get('payments');
            } else{
                $query = $this->db->get_where('payments', array('uye_no' => $uye_no));   
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
        $response = array('status' => 200, 'message' => 'OK');
        try {
            if($uye_no == null){
                $query = $this->db->get('userinfo');
            } else{
                $query = $this->db->get_where('userinfo', array('uye_no' => $uye_no));   
            }
            $result = $query->result(); $ret = array();
            if ($result == null) throw new Exception($this->db->_error_message);
            // foreach ($result as $r) {
            //     if(!array_key_exists($r->uye_no, $ret)) $ret[$r->uye_no] = array();
            //     array_push($ret[$r->uye_no], $r);
            // }
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
        $response = array('status' => 200, 'message' => 'OK'); $result = array();
        setlocale(LC_TIME, 'tr_TR.UTF-8');
        // $year = $today['year'] ; $month = $today['mon'];
        try {
            $sql = $this->db
                    ->select('*')
                    ->from('payments')
                    ->where(array('aidat_tarihi <=' => date('Y-m-d h:i:s'), 'odendigi_tarih' => null, 'uye_no' => $uye_no))
                    ->order_by('aidat_tarihi ASC')
                    ->get();
            foreach ($sql->result() as $q) {
                $aidat_tarihi = $this->ExcelHandler_model->stringDateParser($q->aidat_tarihi);
                // $timeObj = strtotime($q->aidat_tarihi);
                // $year = date('Y', $timeObj);
                // $month = date('n', $timeObj);
                $q->aidat_ayi = $aidat_tarihi['month'];
                $q->aidat_yili = $aidat_tarihi['year'];
                
                // if(!array_key_exists($year, $result)) $result[$year] = array();
                array_push($result, $q);
            }
            $response['content'] = $result;
        } catch (Exception $e) {
            $response = array('status' => $this->db->_error_number, 'message' => $this->db->_error_message);
        }
        return $response;
    }
    
}

?>