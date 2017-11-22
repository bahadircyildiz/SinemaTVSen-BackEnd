<?php  
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Settings_API extends CI_Controller {

    function __construct(){
        parent::__construct();
        $this->load->model("Settings_model");
    }

    public function index()
    {
        $settings = $this->Settings_model->get_settings();
        $this->load->view('json_response', array("data" => $settings));
    }

}
?>