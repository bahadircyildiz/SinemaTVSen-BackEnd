<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class UserHandler extends CI_Controller{
    
    function index($params = null){
        if($params == null) $this->load->view("json_response"); 
        else $this->load->view("json_response", $params);
    }
    
    
    function send_sikayet(){
        $params = array(
            'uye_no' => $this->input->post('uye_no'),
            'tipi' => $this->input->post('tipi'),
            'icerik' => $this->input->post('icerik'),
            'gizli' => $this->input->post('gizli'),
        );
        $result = $this->User_model->sendSikayet($params);
        $this->index(array('data' => $result));
    }

    function get_debt(){
        $param = $this->input->post('uye_no');
        $data = $this->ExcelHandler_model->getDebtsTillToday($param);
        $this->index(array( 'data' => $data));
    }
}


?>