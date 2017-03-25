<?php

class SMSHandler extends CI_Controller{
    
    function index($params = null){
        if($params == null) $this->load->view("options_view"); 
        else $this->load->view("xml_response", $params);
    }
    
    
    function send_message(){
        $gsm = $this->input->post('gsm');
        $result = $this->SMS_model->sendAuthKey($gsm);
        $this->index(array('data' => $result));
    } 
}


?>