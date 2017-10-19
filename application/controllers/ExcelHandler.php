<?php


class ExcelHandler extends CI_Controller{

    function index($params = null){
        if($params == null) $this->load->view("json_response");
        else $this->load->view("json_response", $params);
    }

    function get_payments(){
        $param = $this->input->post('uye_no');
        $data = $this->ExcelHandler_model->getPaymentsByUyeNo($param);
        $this->index(array('data' => $data));
    }

    function get_user_data(){
        $param = $this->input->post('uye_no');
        $data = $this->ExcelHandler_model->getUserByUyeNo($param);
        $this->index(array( 'data' => $data));
    }

    function object_excel()
    {
        $params = $_FILES['spreadsheet'];
        $data = $this->ExcelHandler_model->object_excel($params);
        $this->index(array("data" => $data));
    }


}


?>
