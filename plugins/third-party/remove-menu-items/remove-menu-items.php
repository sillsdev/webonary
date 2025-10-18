<?php

/*
 * Plugin Name: Remove Menu Items
 * Author: Brajesh Singh, extended by Philip Perry
 * Version: 1.0
 * Network: true
 * Description: It disables the site delete capability of blog owners(admins) on a multisite blog network and removes some menu items. Only network administrators can delete the sites. Works with WordPress Multisite and BuddyPress
 * License: GPL
 *
 *
 */
/**
 * Helper class for disablin site delete
 * I am using Fused prefix as it easier(and comes from the name of my other site)
 */
class Remove_Menu_Items_Helper{

    private static $instance;

    private function __construct() {
     //remove from menu
     add_action( 'admin_menu', array($this,'remove_from_menu' ));
     add_action('wp_dashboard_setup', array($this,'remove_dashboard_widgets' ));

     //disable delete capability
     add_action('delete_blog',array($this,'disable_delete_cap'),10,2);
     //when a site deletion is initiated, WordPress sends a mail. We will not allow that and kill it right there.
     //add_action('delete_site_email_content',array($this,'disable_delete_email'));
     add_action('pre_update_option_delete_blog_hash',array($this,'disable_delete_option'),10,2);
     //add localization
     add_action('plugins_loaded',array($this,'load_localization'));

    }

    /**
     * Creates singleton instance
     *
     * @return Remove_Menu_Items_Helper_Helper
     */
    public static function get_instance(){

        if( ! isset ( self::$instance ) )
                self::$instance = new self();

        return self::$instance;

    }
    /**
     * Load the localization file
     */
    function load_localization(){
         $mofile=plugin_dir_path(__FILE__).'/languages/'.get_locale().'.mo';
         load_textdomain( 'disable-delete-site', $mofile);
    }

    /**
     * Remove the delete site link from the tools menu if the user is not network administrator
     * @return type
     */
    public function remove_from_menu () {
        if(is_super_admin())
            return ;//do not prevent super administrators
        //for everyone else
        remove_menu_page( 'tools.php');
        //remove_submenu_page( 'tools.php', 'ms-delete-site.php' );
		remove_menu_page('link-manager.php');
		//remove "Posts"
		remove_menu_page('edit.php');
		remove_submenu_page('edit.php', 'post-new.php');
		//remove Appearance / Customize
		/*
		$customize_url_arr = array();
		$customize_url_arr[] = 'customize.php'; // 3.x
		$customize_url = add_query_arg( 'return', urlencode( wp_unslash( $_SERVER['REQUEST_URI'] ) ), 'customize.php' );
		$customize_url_arr[] = $customize_url;
		foreach ( $customize_url_arr as $customize_url ) {
			remove_submenu_page( 'themes.php', $customize_url );
		}
		*/
		remove_submenu_page('themes.php', 'themes.php');
		remove_submenu_page( 'themes.php', 'nav-menus.php' );
		remove_submenu_page( 'themes.php', 'widgets.php' );
		remove_menu_page('plugins.php');
		remove_menu_page('options-general.php');

		remove_menu_page( 'wpcf7' );
    }


    function remove_dashboard_widgets() {
    	global $wp_meta_boxes;

    	unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_activity']);
		unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_quick_press']);
		unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_right_now']);
		unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_primary']);
		unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_secondary']);
    }


    /**
     * do not allow wpmu_delete_blog to delete a site if the action is triggered by non network administrator
     * @param type $blog_id
     * @param type $drop
     * @return type
     */
    public function disable_delete_cap($blog_id, $drop){
        //if super admin, don't do anything
        if(is_super_admin())
            return $blog_id;

        wp_die(__('You are not allowed to delete this site. Please contact network administrator for any help','disable-delete-site'));

    }
    /**
     * We hack around update_option to avoid sending the mail to the user which is used to confirm/delete the site
     *
     * @param type $new_val
     * @param type $old_val
     * @return type
     */
    function disable_delete_option($new_val,$old_val){
        if(is_super_admin())
            return $new_val;

        wp_die(__('You are not allowed to delete this site. Please contact network administrator for any help','disable-delete-site'));

    }
}

//This adds back the menu item "Additional CSS" which otherwise only displays for the network admin but not site admin
function multisite_custom_css_map_meta_cap( $caps, $cap ) {
	if ( 'edit_css' === $cap && is_multisite() ) {
		$caps = array( 'edit_theme_options' );
	}
	return $caps;
}
add_filter( 'map_meta_cap', 'multisite_custom_css_map_meta_cap', 20, 2 );

Remove_Menu_Items_Helper::get_instance();
