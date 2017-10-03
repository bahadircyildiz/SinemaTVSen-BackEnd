<?php

class SMSHandler extends CI_Controller{
    
    function index(){
        $this->load->view("options_view"); 
    }

    function json_response($params){
        $this->load->view("json_response", $params);
    }
    
    function send_auth_key(){
        $gsm = $this->input->post('gsm');
        $result = $this->SMS_model->sendAuthKey($gsm);
        $this->json_response(array('data' => $result));
    }
    
    function check_auth_key(){
        $onekey = $this->input->post('onekey');
        $secret = $this->input->post('secret');
        $gsm = $this->input->post('gsm');
        $result = $this->SMS_model->checkOneKey($onekey, $secret,$gsm);
        $this->json_response(array('data' => $result));
    }
}


?>