<?php
/*
 * Gravity Forms Access Restrict on per role - per form basis class
 * part of User Role Editor Pro plugin
 * Author: Vladimir Garagulya
 * email: support@role-editor.com
 * 
 */

class URE_GF_Access_Role {

    const ACCESS_DATA_KEY = 'ure_gravityforms_edit_access_data';    
    

    public static function init() {
               
        if ( !( defined('DOING_AJAX') && DOING_AJAX ) ) {
            add_action('ure_role_edit_toolbar_service', 'URE_GF_Access_Role::add_toolbar_button');
            add_action('ure_load_js', 'URE_GF_Access_Role::add_js');
            add_action('ure_dialogs_html', 'URE_GF_Access_View::dialog_html');
            add_action('ure_process_user_request', 'URE_GF_Access_Role::update_data');
        }

    }
    // end of __construct()

    
    public static function add_toolbar_button() {
        if ( !current_user_can( URE_GF_Access_User::EDIT_GF_ACCESS_CAP ) ) {
            return;
        }
            
        URE_GF_Access_View::add_toolbar_button();
        
    }
    // end of add_toolbar_buttons()

    
    public static function add_js() {
        
        wp_register_script('ure-gf-edit-access', plugins_url('/pro/js/gf-edit-access.js', URE_PLUGIN_FULL_PATH ), array(), URE_VERSION );
        wp_enqueue_script ('ure-gf-edit-access');
        wp_localize_script('ure-gf-edit-access', 'ure_data_gf_edit_access',
                array(
                    'gf_edit' => esc_html__('Gravity Forms', 'user-role-editor'),
                    'dialog_title' => esc_html__('Gravity Forms Edit Access', 'user-role-editor'),
                    'update_button' => esc_html__('Update', 'user-role-editor'),
                    'role_should_can_gf'=> esc_html__('Grant to the role some of Gravity Forms capabilities first', 'user-role-editor'),
                    'gf_caps'=>GFCommon::all_caps()
                ));
    }
    // end of add_js()    
                
    
    private static function load_data( $role_id ) {
        
        $access_data = get_option( URE_GF_Access_Role::ACCESS_DATA_KEY );
        if (is_array($access_data) && array_key_exists( $role_id, $access_data ) ) {
            $result =  $access_data[$role_id];
        } else {
            $result = array(
                'what_to_do'=>1,
                'forms_list'=>''
            );
        }
        $result['object_type'] = 'role';
        $result['object_name'] = $role_id;
        
        return $result;
        
    }
    // end of load_data()
    
                                   
    /**
     * returns JSON with form data as the response for AJAX request from URE's main page
     * 
     * @return array
     */
    public static function get_html() {
        
        if ( !current_user_can( URE_GF_Access_User::EDIT_GF_ACCESS_CAP ) ) {
            return array('result'=>'error', 'message'=>'Not enough permissions');
        }        
        
        $lib = URE_Lib_Pro::get_instance();
        $role_id = $lib->get_request_var('current_role', 'post'); 
        if ( empty( $role_id ) ) {
            return array('result'=>'error', 'message'=>'Role ID is required');
        }
        $wp_roles = wp_roles();
        if ( !isset( $wp_roles->roles[$role_id] ) ) {
            return array('result'=>'error', 'message'=>'Role '. $role_id .' does not exist');
        }
        
        $args = self::load_data( $role_id );
        $html = URE_GF_Access_View::get_html( $args );
        
        return array('result'=>'success', 'message'=>'Gravity Forms edit permissions for role:'. $role_id, 'html'=>$html);
    }
    // end of get_html()
   
    
    private static function get_post_data() {
                
        $what_to_do = isset( $_POST['values']['ure_gf_what_to_do'] ) ? $_POST['values']['ure_gf_what_to_do'] : false;
        if ($what_to_do!=1 && $what_to_do!=2) { // got invalid value
            $what_to_do = 1;  // use default value
        }        
        
        $fl0 = isset( $_POST['values']['ure_gf_list'] ) ? $_POST['values']['ure_gf_list'] : '';        
        $fl1 = URE_Base_Lib::filter_string_var( $fl0 );        
        $fl = URE_Utils::filter_int_array_from_str( $fl1 );
        $forms_list = implode(', ', $fl);
        
        $access_data = array(
            'what_to_do'=>$what_to_do,
            'forms_list'=>$forms_list
        );
                
        return $access_data;        
    }
    // end of get_post_data()
    
    
    private static function _update_data( $role_id ) {
        
        $wp_roles = wp_roles();
        
        $role_data = self::get_post_data();
        $access_data = get_option(self::ACCESS_DATA_KEY);        
        if ( !is_array( $access_data ) ) {
            $access_data = array();
        }
        if ( count( $role_data )>0 ) {
            $access_data[$role_id] = $role_data;
        } else {
            unset( $access_data[$role_id] );
        }
        foreach ( array_keys( $access_data ) as $role_id ) {
            if ( !isset( $wp_roles->role_names[$role_id] ) ) {
                unset( $access_data[$role_id] );
            }
        }
        update_option( self::ACCESS_DATA_KEY, $access_data );
    }
    // end of update_data()

    
    public static function update_data() {
        
        $answer = array('result'=>'error', 'message'=>'');                
        
        if ( !current_user_can( URE_GF_Access_User::EDIT_GF_ACCESS_CAP ) ) {
            $answer['message'] = esc_html__('URE: Insufficient permissions to use this add-on','user-role-editor');
            return $answer;
        }
        
        $ure_object_type = ( isset( $_POST['values']['ure_object_type'] ) ) ? URE_Base_Lib::filter_string_var( $_POST['values']['ure_object_type'] ) : false;
        if ( $ure_object_type!=='role') {
            $answer['message'] = esc_html__('URE: Gravity forms edit access: Wrong object type. Data was not updated.', 'user-role-editor');
            return $answer;
        }
        
        $role_id = isset( $_POST['values']['ure_object_name'] ) ? URE_Base_Lib::filter_string_var( $_POST['values']['ure_object_name'] ) : false;
        if ( empty( $role_id ) ) {
            $answer['message'] = esc_html__('URE: Gravity forms edit access: Empty role ID. Data was not updated', 'user-role-editor');
            return $answer;
        }
        
        $roles = wp_roles();
        if ( !isset( $roles->roles[$role_id] ) ) {
            $answer['message'] = esc_html__('URE: Gravity forms edit access: role does not exists. Data was not updated', 'user-role-editor');
            return $answer;
        }
                                
        self::_update_data( $role_id );
                
        $answer['result'] = 'success';
        $answer['message'] = esc_html__('Gravity forms edit access data was updated successfully', 'user-role-editor');
        
        return $answer;        
        
    }
    // end of update_data()
    
}
// end of URE_GF_Access_Role class