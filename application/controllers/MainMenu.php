<?php  
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class MainMenu extends MY_Controller {

    function __construct(){
        parent::__construct();
    }

    function index()
    {
        $this->ParseExcel();
    }

    function ParseExcel()
    {
        $this->title = "Excel Okuma Sistemi";
        if($_FILES){
            $params = $_FILES['spreadsheet'];
            $data = $this->ExcelHandler_model->parse_excel($params);
            $this->load->view("options_view", array( 'data' => $data));
        } else {
            $this->load->view("options_view");
        }
    }

    function AppSettings()
    {
        $this->load->model("Settings_model");
        $this->title = "Mobil App Ayarlari";

        if(count($this->input->post()) > 0){
            $postData = $this->input->post();
            $settings = $this->Settings_model->set_settings($postData);            
        } else {
            $settings = $this->Settings_model->get_settings();
        }
        $this->load->view("app_settings_view", $settings);
    }


}
?>