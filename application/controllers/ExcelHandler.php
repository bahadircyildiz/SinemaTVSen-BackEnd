<?php


class ExcelHandler extends CI_Controller{

    function index($params = null){
        if($params == null) $this->load->view("options_view");
        else $this->load->view("json_response", $params);
    }

    function parse_excel(){
        $params = $_FILES['spreadsheet'];
        $data = $this->ExcelHandler_model->parse_excel($params);
        $this->load->view("options_view", array( 'data' => $data));
    }

    function get_payments(){
        $param = $this->input->post('uye_no');
        $data = $this->ExcelHandler_model->getPaymentsByUyeNo($param);
        $this->index(array( 'data' => $data));
    }

    function get_user_data(){
        $param = $this->input->post('uye_no');
        $data = $this->ExcelHandler_model->getUserByUyeNo($param);
        $this->index(array( 'data' => $data));
    }



}


?>
