<?php  
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class MY_Controller extends CI_Controller {

    public $data = array();
    public $title = "Title";

    function __construct()
    { 
        parent::__construct();
        $this->load->library("WPIntegration");
    }

    function _output($content)
    {
        if(!$this->wpintegration->isLoggedIn()){
            echo "Wordpress Not Logged In!!!";
            $this->wpintegration->wp_redirect($this->wpintegration->loginLink());
        } else{
            // Load the base template with output content available as $content
            $this->data['content'] = &$content;
            $this->data['title'] = $this->title;
            echo($this->load->view('base', $this->data, true));
        }
    }
}
?>