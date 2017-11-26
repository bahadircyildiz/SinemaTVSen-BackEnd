<?php  
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class MainMenu extends MY_Controller {

    function __construct(){
        parent::__construct();
    }

    function index()
    {
        $this->parse_excel();
    }

    function parse_excel()
    {
        $data = array();
        if($_FILES){
            $params = $_FILES['spreadsheet'];
            $data = $this->ExcelHandler_model->parse_excel($params);
        }
        $this->load->view("options_view", array( 'data' => $data));
    }

}
?>