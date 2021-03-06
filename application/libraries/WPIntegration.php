<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
 
/**
* WordPres Integration Class
*
* This class enables the use of wordpress functions
*
* @author       Oscar Dias
* @link     http://oscardias.com/codeigniter/integrating-wordpress-with-codeigniter
*/
class WPIntegration {
    public function __construct() {
        global $table_prefix, $wp_embed, $wp_widget_factory, $_wp_deprecated_widgets_callbacks, $wp_locale, $wp_rewrite;
        // Additional WordPress global variables
        //$wpdb, $current_user, $auth_secure_cookie, $wp_roles, $wp_the_query, $wp_query, $wp, $_updated_user_settings,
        //$wp_taxonomies, $wp_filter, $wp_actions, $merged_filters, $wp_current_filter, $wp_registered_sidebars,
        //$wp_registered_widgets, $wp_registered_widget_controls, $wp_registered_widget_updates, $_wp_deprecated_widgets_callbacks,
        //$posts, $post, $wp_did_header, $wp_did_template_redirect, $wp_version, $id, $comment, $user_ID;
    
        require_once '../wp-load.php';

    }
    public function isLoggedIn()
    {
        // $this->load->helper("cookie");
        // $cookie_name = "wordpress_logged_in_d94104ef73bac9a1643114af44c1e033";
        // $current_user = get_cookie($cookie_name);
        // var_dump($current_user);
        // return $current_user != null;
        return is_user_logged_in();
    }
    public function isSuperAdmin()
    {
        if(wp_get_current_user()->user_level >= 10)
            return true;
        else
            return false;
    }
    public function loginLink()
    {
        $CI = & get_instance();
        $CI->load->helper('ci_url');
        $redirect = current_url();
    
        return wp_login_url()."?redirect_to=/backend/";
    }
    
    public function wp_redirect($link){
        return wp_safe_redirect($link);
    }

    public function logoutLink()
    {
        $CI = & get_instance();
        $CI->load->helper('ci_url');
        $redirect = current_url();
    
        return wp_logout_url()."&redirect_to=$redirect";
    }
    public function blogLink()
    {
        return get_option('siteurl');
    }
    
    public function adminLink()
    {
        return get_option('siteurl') . "/wp-admin";
    }
    //
    // Our code goes here!!!
    //
 
}
/* End of file Wpintegration.php */