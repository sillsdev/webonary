<?php
/*
 * Class: Edit access restrictions to Gravity Forms for user
 * Project: User Role Editor Pro WordPress plugin
 * Author: Vladimir Garagulya
 * email: vladimir@shinephp.com
 * 
 */

class URE_GF_Access_User {    
    
    const EDIT_GF_ACCESS_CAP = 'ure_edit_gravityforms_access';
    const UMK_WHAT_TO_DO = 'ure_gravity_forms_access_what_to_do';
    const UMK_WHAT_FORMS_LIST = 'ure_allow_gravity_forms';
    
    static $gf_list = null; // array with full list of Gravity Forms ID
    
    
    public static function init() {
        
        add_action( 'edit_user_profile', 'URE_GF_Access_User::show_options', 10, 2 );     
        add_action( 'profile_update', 'URE_GF_Access_User::save_options', 10 );
      
    }
    // end of init()
        
    
    public static function can_edit( $user ) {
                
        if ( !method_exists( 'GFCommon', 'all_caps' ) ) {
            return false;
        }        
            
        $lib = URE_Lib_Pro::get_instance();            
        $min_cap = $lib->user_can_which( $user, GFCommon::all_caps() );
        if ( !empty( $min_cap ) ) {
            return true;            
        } else {
            return false;
        }   
    }
    // end of can_edit()

    
    private static function get_options( $user_id ) {
        global $wpdb;
        
        $umk_what_to_do = $wpdb->prefix . self::UMK_WHAT_TO_DO;
        $what_to_do = get_user_meta( $user_id, $umk_what_to_do, true);
        if ( $what_to_do!=1 && $what_to_do!=2 && $what_to_do!=3 ) {
            $what_to_do = 1; // Allow (by default)
        }
        
        $umk_forms_list = $wpdb->prefix . self::UMK_WHAT_FORMS_LIST;
        $forms_list = get_user_meta($user_id, $umk_forms_list, true);
        
        $result = array(
            'user_profile'=>1,
            'what_to_do'=>$what_to_do,
            'forms_list'=>$forms_list
        );
        
        return $result;
    }
    // end of get_options()    
    
    
    public static function show_options( $user ) {
        
        $result = stripos( $_SERVER['REQUEST_URI'], 'network/user-edit.php');
        if ($result !== false) {  // exit, this code just for single site user profile only, not for network admin center UI
            return;
        }
                
        if ( !current_user_can( 'edit_users', $user->ID ) ) {
            return;            
        }        
        
        if ( !current_user_can( URE_GF_Access_User::EDIT_GF_ACCESS_CAP ) ) {
            return;            
        }
                
        if ( !self::can_edit( $user ) ) {
            return;
        }
        
        $args = self::get_options( $user->ID );
        
        echo URE_GF_Access_View::get_html( $args );

    }
    // end of show_options()
    
    
    public static function save_options( $user_id ) {
        global $wpdb;
                
        if ( !current_user_can('edit_users', $user_id ) ) {
            return;
        }
        if ( !current_user_can( URE_GF_Access_User::EDIT_GF_ACCESS_CAP ) ) {
            return;            
        }

        $lib = URE_Lib_Pro::get_instance();
        // update Gravity Forms access restriction: what to do value
        $what_to_do = (int) $lib->get_request_var('ure_gf_what_to_do', 'post', 'int');
        if ( $what_to_do!=1 && $what_to_do!=2 && $what_to_do!=3 ) {  // sanitize user input
            $what_to_do = 1;
        }
        $umk_what_to_do = $wpdb->prefix . self::UMK_WHAT_TO_DO;
        update_user_meta( $user_id, $umk_what_to_do, $what_to_do );
        
        // update Gravity Forms access restriction: comma separated GF IDs list
        $gf_list = URE_Utils::filter_int_list_from_post('ure_gf_list');
        if ( count( $gf_list )>0 ) {
            $gf_list_str = implode(', ', $gf_list);
        } else {
            $gf_list_str = '';
        }
        $umk_forms_list = $wpdb->prefix . self::UMK_WHAT_FORMS_LIST;
        update_user_meta( $user_id, $umk_forms_list, $gf_list_str );
    }
    // end of save_user_options()    

    
    private static function get_gf_list() {        
        global $wpdb;        
        
        if ( self::$gf_list!==null ) {
            return self::$gf_list;
        }
       
        $table   = GFFormsModel::get_form_table_name();
        $query     = "SELECT id from {$table}";
        $data = $wpdb->get_col( $query );
        if ( empty( $data ) ) {
            self::$gf_list = array();
        } else {
            self::$gf_list = $data;
        }        
        
        return self::$gf_list;
    }
    // end of get_gf_list()
    

    private static function get_roles_data( $user_id, $user_what_to_do ) {
                
        $access_data = get_option( URE_GF_Access_Role::ACCESS_DATA_KEY );
        if ( !is_array( $access_data ) ) {
            $data = array(
                'what_to_do'=>1,
                'forms_list'=>array()
        );
            return $data;
        }
        
        $all_forms = array();        
        $what_to_do = -1;   // Not initialized
        $user = get_user_by('id', $user_id );
        foreach( $user->roles as $role_id ) {
            if ( !isset( $access_data[$role_id] ) ) {
                continue;
            }
            $role_data = $access_data[$role_id];
            if ( empty( $role_data ) || !isset( $role_data['what_to_do'] ) || !isset( $role_data['forms_list'] ) ) {
                continue;
            }
            
            if ( $what_to_do===-1 ) {
                // Take value directly from user or from the 1st role granted to user
                $what_to_do = ( $user_what_to_do>0 && $user_what_to_do<3 ) ? $user_what_to_do : $role_data['what_to_do'];
            }
            if ( $what_to_do!=$role_data['what_to_do'] ) {
                // skip role as it has different what to do value 
                continue;
            }
            if ( empty( $role_data['forms_list'] ) ) {
                continue;
            }
            
            $forms = URE_Utils::filter_int_array_from_str( $role_data['forms_list'] );
            $all_forms = ure_array_merge( $all_forms, $forms );
        }
        
        $data = array(
          'what_to_do'=>$what_to_do,
          'forms_list'=>$all_forms  
        );
        
        return $data;
    }
    // end of get_roles_data()
    
        
    public static function get_allowed_forms() {
        
        $allowed_forms = array();
        $current_user_id = get_current_user_id();
        $user_data = self::get_options( $current_user_id );                                
        
        $roles_data = self::get_roles_data( $current_user_id, $user_data['what_to_do'] );
        if ( $user_data['what_to_do']==3 ) { // Look at roles
            if ( $roles_data['what_to_do']==1 ) {   // Allow
                $allowed_forms = $roles_data['forms_list'];
            } elseif ( $roles_data['what_to_do']==2 ) { // Prohibit
                $gf_list = self::get_gf_list();
                $allowed_forms = array_diff( $gf_list, $roles_data['forms_list'] );
            }
        } elseif ( $user_data['what_to_do']==1 ) {    // Allow
            $user_allowed_forms = URE_Utils::filter_int_array_from_str( $user_data['forms_list'] );
            $allowed_forms = ure_array_merge( $user_allowed_forms, $roles_data['forms_list'] );
        } elseif ( $user_data['what_to_do']==2 ) {    // Prohibit 
            $gf_list = self::get_gf_list();
            $prohibited_forms0 = URE_Utils::filter_int_array_from_str( $user_data['forms_list'] );
            $prohibited_forms = ure_array_merge( $prohibited_forms0, $roles_data['forms_list'] );
            $allowed_forms = array_diff( $gf_list, $prohibited_forms );
        }
            
        $allowed_forms_list = apply_filters('ure_get_allowed_gf_forms', $allowed_forms);
               
        return $allowed_forms_list;
    }
    // end of get_allowed_forms()
    
}
// end of URE_GF_Access_User class
