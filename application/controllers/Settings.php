<?php  
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Settings extends MY_Controller {

    function __construct(){
        parent::__construct();
        $this->load->model("Settings_model");
        $this->title = "Mobil App Ayarlari";
    }

    public function index()
    {
        $settings = $this->Settings_model->get_settings();
        $this->load->view('app_settings_view', $settings);
    }

    function set_settings()
    {
        $postData = $this->input->post();
        $result = $this->Settings_model->set_settings($postData);
        $this->load->view("app_settings_view", array_merge($result, $postData));
    }

}
?>