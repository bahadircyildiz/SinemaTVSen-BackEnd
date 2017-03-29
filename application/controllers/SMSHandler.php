<?php

class SMSHandler extends CI_Controller{
    
    function index($params = null){
        if($params == null) $this->load->view("options_view"); 
        else $this->load->view("json_response", $params);
    }
    
    
    function send_auth_key(){
        $gsm = $this->input->post('gsm');
        $result = $this->SMS_model->sendAuthKey($gsm);
        $this->index(array('data' => $result));
    }
    
    function check_auth_key(){
        $onekey = $this->input->post('onekey');
        $result = $this->SMS_model->checkOneKey($onekey);
        $this->index(array('data' => $result));
    }
}


?>