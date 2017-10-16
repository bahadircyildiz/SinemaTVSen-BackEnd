<?php  
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class MainMenu extends MY_Controller {

    public function index()
    {
        $this->title = "Welcome!!!";
        $this->load->view('options_view');
    }

    function parse_excel()
    {
        $params = $_FILES['spreadsheet'];
        $data = $this->ExcelHandler_model->parse_excel($params);
        $this->load->view("options_view", array( 'data' => $data));
    }

}
?>