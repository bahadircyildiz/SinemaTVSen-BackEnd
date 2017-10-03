<?php  
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class MY_Controller extends CI_Controller {

    public $data = array();
    public $title = "Title";

    function __construct()
    { 
        parent::__construct();
        $this->load->library("WPIntegration");
        // require_once('../wp-load.php');
        // if (!function_exists('ci_site_url')) {
        //     function ci_site_url($uri = '')
        //     {
        //         $CI =& get_instance();
        //         return $CI->config->site_url($uri);
        //     }
        // }
        // if (!function_exists('ci_base_url')) {
        //     function ci_base_url($uri = '')
        //     {
        //         $CI =& get_instance();
        //         return $CI->config->base_url($uri);
        //     }
        // }
    }

    function _output($content)
    {
        // if(!$this->WPIntegration->isLoggedIn()){
        //     $this->WPIntegration->wp_redirect($this->WPIntegration->loginLink());
        // }
        // Load the base template with output content available as $content
        $this->load->helper('html');
        $this->data['content'] = &$content;
        $this->data['title'] = $this->title;
        echo($this->load->view('base', $this->data, true));
    }
}
?>