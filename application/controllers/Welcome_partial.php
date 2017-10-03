<?php  
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Welcome_partial extends MY_Controller {

    function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $this->title = "Welcome!!!";
        $this->load->view('welcome_message');
    }

}
?>