<?php

class User_model extends CI_Model{
    
    function sendSikayet($data){
        try {
            $query = $this->db->insert('sikayet', $data);
            if($query == false){
                $error = $this->db->error();
                throw new Exception($error['message'], $error['code']);
            }
            return $query; 
        } catch( Exception $e ){
            $this->output->set_status_header($e->getCode(), $e->getMessage());
        }
    }

    function getSikayet($uye_no = null){
        return true;
    }
    
}

?>