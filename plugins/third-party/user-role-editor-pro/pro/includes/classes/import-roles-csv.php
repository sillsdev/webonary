<?php
/* Project: User Role Editor Pro WordPress plugin
 * Import user roles to the current site from CSV file
 * Author: Vladimir Garagulia
 * Email: support@role-editor.com
 * Site: https://role-editor.com
 * License: GPL v.3
 */
 
 class URE_Import_Roles_CSV {
            
     private $lib = null;
     
    
    function __construct() {
            
        $this->lib = URE_Lib_Pro::get_instance();

        add_action('ure_settings_tools_show', array( $this, 'show') );
        add_action('ure_settings_tools_exec', array( $this, 'act') );
        
    }
    // end of __construct()
             
        
    public function show($tab_idx) {

        if ( !current_user_can('ure_import_roles') ) {
            return;
        }
        
        $link = URE_Settings::get_settings_link();
?>               

        <div style="margin: 10px 0 10px 0; border: 1px solid green; padding: 0 10px 10px 10px; text-align:left;">
            <form name="ure_import_roles_csv_form" id="ure_import_roles_csv_form" method="post" enctype="multipart/form-data" 
                  action="<?php echo $link; ?>?page=settings-<?php echo URE_PLUGIN_FILE; ?>" >
                <h3><?php esc_html_e('Import User Roles from CSV file', 'user-role-editor'); ?></h3>            
                <div style="padding:10px;">
                    <input type="file" name="roles_file" id="roles_file" style="width: 350px;"/>
                </div>    
                <div style="padding:10px;">
                    <input type="checkbox" name="add_new_roles" id="add_new_roles" checked="checked">
                    <label for="add_new_roles"><?php esc_html_e('Add new roles', 'user-role-editor');?></label><br/>
                    <input type="checkbox" name="overwrite_existing_roles" id="overwrite_existing_roles" checked="checked">
                    <label for="overwrite_existing_roles"><?php esc_html_e('Overwrite existing roles', 'user-role-editor');?></label><br/>
                    <input type="checkbox" name="delete_not_existing_roles" id="delete_not_existing_roles" checked="checked">
                    <label for="delete_not_existing_roles"><?php esc_html_e('Delete not existing roles', 'user-role-editor');?></label>
                </div>                          
                <br>
                <button id="ure_import_roles_csv_button" name="ure_import_roles_csv_button" style="width: 100px;" 
                        title="<?php esc_html_e('Import user roles from CSV', 'user-role-editor'); ?>"><?php esc_html_e('Import', 'user-role-editor'); ?></button> 
    <?php wp_nonce_field('user-role-editor'); ?>
                <input type="hidden" name="ure_settings_tools_exec" value="1" />
                <input type="hidden" name="ure_import_roles_csv_exec" value="1" />
                <input type="hidden" name="ure_tab_idx" value="<?php echo $tab_idx; ?>" />
                <input type="hidden" name="ure_nonce" value="<?php echo wp_create_nonce('user-role-editor');?>" />
            </form>                
        </div>
<?php            
        
    }
    // end of show()
    
    
    public function is_applicable() {
                        
        if ( !isset( $_POST['ure_import_roles_csv_exec'] ) ) {
            return false;
        }
        
        if ( empty($_POST['ure_nonce']) || !wp_verify_nonce($_POST['ure_nonce'], 'user-role-editor') ) {
            $message = esc_html__('Wrong nonce. Action prohibitied.', 'user-role-editor');
            $this->lib->show_message( $message, true );
            return false;
        }

        if ( !current_user_can('ure_import_roles') ) {
            $message = esc_html__('You do not have sufficient permissions to import roles.', 'user-role-editor');
            $this->lib->show_message( $message, true );
            return false;
        }      

        return true;
    }        
    // end of check()
    
    
    private function decode_csv( $data ) {
        
        $csv_roles = explode(PHP_EOL, $data );        
        $roles = array();
        for( $i = 1; $i < count( $csv_roles ); $i++ ) {
            if ( strlen($csv_roles[$i] )==0 ) {
                continue;
            } 
            $role = str_getcsv( $csv_roles[$i] );
            $role_id = $role[0];
            if ( empty( $role_id ) ) {
                continue;
            }
            $role_name = $role[1];
            $raw_caps = explode(',', $role[2] );
            $capabilities = array();
            foreach( $raw_caps as $cap ) {
                $cap = trim( $cap );
                if ( !empty( $cap ) ) {
                    $capabilities[$cap] = true;
                }
            }
            $roles[$role_id] = array(
              'name'=>$role_name,
              'capabilities'=>$capabilities  
            );
        }
        
        return $roles;
    }
    // end of decode_csv()


    private function validate_roles( $roles ) {
    
        foreach( $roles as $role_id=>$role ) {
            $result = URE_Import_Validator::validate_role( $role_id, $role );
            if ( !$result->success ) {
                $this->lib->show_message( $result->message .' - '. esc_html( $role_id ), true );
                return false;
            }
            $result = URE_Import_Validator::validate_capabilities( $role_id, $role['capabilities'] );
            if ( !$result->success ) {
                $this->lib->show_message( $result->message .' - ['. esc_html( $role_id ) .']', true );
                return false;
            }
        }
        
        return true;
    }
    // end of validate_roles()
    

    /*
     * remove any existing role which is not presented at the imported roles list
     */
    private function delete_not_existing_roles( $roles0, $roles1 ) {
        
        foreach( array_keys( $roles0 ) as $role_id ) {
            if ( !isset( $roles1[$role_id] ) ) {
                unset( $roles0[$role_id] );
            }
        }
        
        return $roles0;    
    }
    // end of delete_not_existing_roles()
    
    
    /*
     * Overwrite any existing role with one from the imported roles
     */
    private function overwrite_existing_roles( $roles0, $roles1 ) {
        
        foreach( $roles1 as $role_id=>$role ) {
            if ( isset( $roles0[$role_id] ) ) {
                $roles0[$role_id]['name'] = $role['name'];
                $roles0[$role_id]['capabilities'] = $role['capabilities'];
            }
        }
        
        return $roles0;
        
    }
    // end of overwrite existing_roles()
    
    
    /*
     * Add new roles from the imported roles
     */
    private function add_new_roles( $roles0, $roles1 ) {
        
        foreach( $roles1 as $role_id=>$role ) {
            if ( !isset( $roles0[$role_id] ) ) {
                $roles0[$role_id] = array(
                    'name'=>$role['name'],
                    'capabilities'=>$role['capabilities']
                        );
            }
        }
        
        return $roles0;        
    }
    // end of overwrite existing_roles()
    
    
    private function update_roles( $roles ) {
        global $wpdb;
        
        $add_new_roles = $this->lib->get_request_var('add_new_roles', 'post', 'checkbox');
        $overwrite_existing_roles = $this->lib->get_request_var('overwrite_existing_roles', 'post', 'checkbox');
        $delete_not_existing_roles = $this->lib->get_request_var('delete_not_existing_roles', 'post', 'checkbox');
    
        if ( !( $add_new_roles && $overwrite_existing_roles && $delete_not_existing_roles ) ) {
            $roles0 = $this->lib->get_user_roles();
            if ( $delete_not_existing_roles ) {
                $roles0 = $this->delete_not_existing_roles( $roles0, $roles );
            }
            if ( $overwrite_existing_roles ) {
                $roles0 = $this->overwrite_existing_roles( $roles0, $roles );
            }
            if ( $add_new_roles ) {
                $roles0 = $this->add_new_roles( $roles0, $roles );
            }
            
            $roles = $roles0;
        }
                
        $option_name = $wpdb->prefix . 'user_roles';
        update_option( $option_name, $roles );
    }
    // end of update_roles()
    
    
    public function act() {        
        
        if ( !$this->is_applicable() ) {
            return;
        }
                                        
        $upload_dir = wp_upload_dir();
        if ( !empty( $upload_dir['error'] ) ) {
            $message = esc_html__('File upload error.', 'user-role-editor') .' '. $upload_dir['error'];
            $this->lib->show_message( $message, true );            
        }        
        $upload_file = $upload_dir['path'] . '/roles-data.csv';
        if ( !move_uploaded_file($_FILES['roles_file']['tmp_name'], $upload_file) ) {
            $message = esc_html__('File upload error. Can not write to', 'user-role-editor') .' '. $upload_file;
            $this->lib->show_message( $message, true );
            return;
        }
        
        $csv_data = file_get_contents( $upload_file );
        unlink( $upload_file );   
        
        $roles = $this->decode_csv( $csv_data );
        if ( !$this->validate_roles( $roles ) ) {
            return;
        }
        
        $this->update_roles( $roles);                        
        
        $message = esc_html__('Roles are imported successfully', 'user-role-editor') .' '. $upload_dir['error'];
        $this->lib->show_message( $message );            
    }
    // end of update()
    
}
 // end of URE_Import_Roles_CSV
