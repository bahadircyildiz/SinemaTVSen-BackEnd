<?php
class Settings_model extends CI_Model{
    
    function get_settings(){
        try{
            $query = $this->db->get("ayarlar");
            $data = array();
            $result = $query->result(); 
            if(!$result){
                $error = $this->db->error();
                if($error["code"] != 0) throw new Exception($error["message"], $error["code"]);
            }
            foreach ($result as $r) {
                $data[$r->name] = $r->value;
            }
            return $data;
        } catch(Excaption $e){
            $this->output->set_status_header($e->getCode(), $e->getMessage());
            exit();
        }
    }

    function set_settings($params){
        try{
            $data = array();
            foreach ($params as $name => $value) {
                $temp = array("name"=>$name, "value"=>$value);
                $data[] = $temp;
            }
            $result = $this->db->update_batch("ayarlar", $data, "name");
            if(!$result){
                $error = $this->db->error();
                if($error["code"] != 0) throw new Exception($error["message"], $error["code"]);
            }
            return array( "save_result" => $result );
        } catch(Excaption $e){
            return array( "error_code" =>$e->getCode(), "error_message" => $e->getMessage());
        }
    }
}
?>