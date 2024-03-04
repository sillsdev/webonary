<?php
/*
 * Class: Edit access to Gravity Forms for role/user data views support
 * Project: User Role Editor Pro WordPress plugin
 * Author: Vladimir Garagulya
 * email: support@role-editor.com
 * 
 */

class URE_GF_Access_View {
 

    /**
     * echo HTML for modal dialog window
     */
    static public function dialog_html() {
        
?>
        <div id="ure_gf_edit_access_dialog" class="ure-modal-dialog">
            <div id="ure_gf_edit_access_container">
            </div>    
        </div>
<?php        
        
    }
    // end of dialog_html()

    
    static public function add_toolbar_button() {
        
        $button_title = esc_html__('Allow/Prohibit editing selected Gravity Forms', 'user-role-editor');
        $button_label = esc_html__('Gravity Forms', 'user-role-editor');
?>                
        <button id="ure_gf_edit_access_button" class="ure_toolbar_button" title="<?php echo $button_title; ?>"><?php echo $button_label; ?></button>
<?php

    }
    // end of add_toolbar_button()
    
    
    /**
     * Build and return the string with HTML form for input/update posts edit access data 
     * 
     * @param array $args
     * @return string
     */
    static public function get_html( $args ) {
                
        extract( $args );        
        ob_start();
        
        if ( isset( $user_profile ) ) { // show section at user profile
            echo '<h3>'. esc_html__('Gravity Forms Restrictions', 'user-role-editor') .'</h3>'. PHP_EOL;
        } else {    // show form with data for currently selected role at User Role Editor dialog window
?>
<form name="ure_gf_access_form" id="ure_gf_access_form" method="POST"
      action="<?php echo admin_url() . URE_PARENT .'?page=users-'. URE_PLUGIN_FILE;?>" >
<?php
        }
?>        
        <table class="form-table">
            <tr>
                <th scope="role">
                    <?php esc_html_e('What to do', 'user-role-editor'); ?>
                </th>    
                <td>
                    <input type="radio" name="ure_gf_what_to_do" id="ure_gf_what_to_do_1" value="1" <?php  checked( $what_to_do, 1 ); ?> >
                    <label for="ure_gf_what_to_do_1"><?php esc_html_e('Allow', 'user-role-editor'); ?></label>&nbsp;
                    <input type="radio" name="ure_gf_what_to_do" id="ure_gf_what_to_do_2" value="2" <?php  checked( $what_to_do, 2 ); ?> >
                    <label for="ure_gf_what_to_do_2"><?php esc_html_e('Prohibit', 'user-role-editor'); ?></label>&nbsp;
<?php
    if ( isset( $user_profile ) ) {
?>
                    <input type="radio" name="ure_gf_what_to_do" id="ure_gf_what_to_do_0" value="3" <?php  checked( $what_to_do, 3 );?> >
                    <label for="ure_gf_what_to_do_3"><?php esc_html_e('Look at roles', 'user-role-editor'); ?></label>
<?php
    }
?>
                </td>
            </tr>    
            <tr>
            <tr>
        	<th scope="row">               
                    <?php esc_html_e('with forms ID (comma separated)', 'user-role-editor'); ?>
                </th>
                <td>
                    <input type="text" name="ure_gf_list" id="ure_gf_list" value="<?php echo $forms_list; ?>" size="40" />
                </td>
            </tr>              
        </table>   
<?php
    if ( !isset( $user_profile ) ) {
?>
    <input type="hidden" name="action" id="action" value="ure_update_gf_access" />
    <input type="hidden" name="ure_object_type" id="ure_object_type" value="<?php echo $object_type;?>" />
    <input type="hidden" name="ure_object_name" id="ure_object_name" value="<?php echo $object_name;?>" />
<?php    
    if ($object_type=='role') {
?>
    <input type="hidden" name="user_role" id="ure_role" value="<?php echo $object_name;?>" />
<?php
    }
    wp_nonce_field('user-role-editor', 'ure_nonce'); 
?>
</form>
<?php    
    }
        $output = ob_get_contents();
        ob_end_clean();
        
        return $output;
    }
    // end of get_html()
    
}
// end of URE_GF_Access_View class