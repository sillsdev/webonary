<?php
/*
 * Gravity Forms Access Restrict on per site - per user - per role - per form basis class
 * part of User Role Editor Pro plugin
 * Author: Vladimir Garagulya
 * email: support@role-editor.com
 * 
 */

class URE_GF_Access {
            
    private $lib = null;
    private $umk_what_to_do = 0;
    private $umk_forms_list = '';
    private $form_table_name = '';
    private $form_from_key = '';
    private $count_forms_query = '';
    private $allowed_forms_list = null;
    
    public function __construct() {    
        global $wpdb;
        
        $this->lib = URE_Lib_Pro::get_instance();
        
        $this->umk_what_to_do = $wpdb->prefix . 'ure_gravity_forms_access_what_to_do';
        $this->umk_forms_list = $wpdb->prefix . 'ure_allow_gravity_forms';
        
        $this->form_table_name = GFFormsModel::get_form_table_name();
        $this->form_from_key = "FROM {$this->form_table_name}";
        // GF v.2.5.1.2: forms_model.php, line 794, function get_form_count()
        $this->count_forms_query = "
            SELECT
            (SELECT count(0) FROM {$this->form_table_name} WHERE is_trash = 0) as total,
            (SELECT count(0) FROM {$this->form_table_name} WHERE is_active=1 AND is_trash = 0 ) as active,
            (SELECT count(0) FROM {$this->form_table_name} WHERE is_active=0 AND is_trash = 0 ) as inactive,
            (SELECT count(0) FROM {$this->form_table_name} WHERE is_trash=1) as trash
            ";
                
        URE_GF_Access_Role::init();    
        URE_GF_Access_User::init();
            
        add_action( 'admin_head', array($this, 'prohibited_links_redirect') );
        add_action('admin_init', array($this, 'set_final_hooks'));
        
    }
    // end of __construct()
    
    
    
    private function add_gf_import_capability() {
        global $wp_roles;
        
        if (!isset($wp_roles)) {
            $wp_roles = new WP_Roles();
        }
        
        $admin_role = $wp_roles->get_role('administrator');
        if (!empty($admin_role) && !$admin_role->has_cap('gravityforms_import')) {        
            $wp_roles->use_db = true;   //  save changes to the database
            $admin_role->add_cap('gravityforms_import');
        }        
        
    }
    // end of add_gf_import_capability()            
    
    
    public function set_final_hooks() {
                
        $this->add_gf_import_capability();
        $current_user = wp_get_current_user();
        if ( empty( $current_user ) ) {
            return false;
        }
        if ( is_a( $current_user, 'WP_User') && $current_user->ID==0) {
            return false;
        }
        if ( $this->lib->user_is_admin( $current_user->ID ) ) {
            return;
        }
        
        if ( URE_GF_Access_User::can_edit( $current_user ) ) {
            add_filter('query', array($this, 'restrict_form_list' ) );
        }
                        
    }
    // end of set_final_hooks()        
                        
    
    private function get_allowed_forms() {
                
        if ( $this->allowed_forms_list===null ) {
            $this->allowed_forms_list = URE_GF_Access_User::get_allowed_forms();            
        }                
        
        return $this->allowed_forms_list;
    }
    // end of get_allowed_forms()
    
    
    protected function is_allowed_import_link() {
        
        $current_user = wp_get_current_user();                        
        $allowed_forms_list = $this->get_allowed_forms();
        if ($this->lib->user_has_capability($current_user, 'gravityforms_import') && count($allowed_forms_list)==0) {
            return true;
        }
?>
        <script>
            document.location.href = '<?php echo admin_url('admin.php?page=gf_export'); ?>';
        </script>
<?php                    
        die;        
    }
    // end of check_import_link()
    
    
    private function is_controled_url($args) {
        if ($args['page']=='gf_export') {
            if ( !(isset($args['view']) && $args['view']=='import_form') ) {
                return false;                
            }
            if ($this->is_allowed_import_link()) {
                return false;
            }
        } elseif ($args['page']=='gf_edit_forms') {
            // we control URLs similar too:
            //  admin.php?page=gf_edit_forms&id=
            //  admin.php?page=gf_edit_forms&view=settings
            if ( !(isset($args['id']) || (isset($args['view']) && $args['view']=='settings')) ) {
                return false;
            }
        } elseif ($args['page']=='gf_entries') {
            // we control URLs similar too:
            // admin.php?page=gf_entries&id=
            // admin.php?page=gf_entries&view=entry&id=
            if ( !(isset($args['id']) || (isset($args['view']) && $args['view']=='entry')) ) {
                return false;
            }
        }
        
        return true;
    }
    // end of is_controled_url()
    
        
    private function get_form_id($args) {
        
        $id = 0;        
        if ( isset($args['id']) ) {
            $id = (int) $args['id'];
        } elseif (isset($_POST['action_argument'])) {   // delete, duplicate
            $id = (int) $_POST['action_argument'];            
        } elseif (isset($_POST['form'])) {  // bulk actions
            $allowed_forms_list = $this->get_allowed_forms();            
            foreach($_POST['form'] as $form_id) {
                if (!in_array($form_id, $allowed_forms_list)) {
                    $id = $form_id;
                    break;
                }
            }
        }        
        
        return $id;
    }
    // end of get_form_id()
    
    
    private function is_allowed_entry($args) {
        global $wpdb;
        
        if (!isset($args['lid'])) { //  it's not an entry view request
            return true;
        }
        
        // check access to entries
        $lid = filter_input(INPUT_GET, 'lid', FILTER_SANITIZE_NUMBER_INT);
        if (empty($lid)) {
            return false;
        }
        
        $entries_table_name = GFFormsModel::get_entry_table_name();
        $query = $wpdb->prepare(
                    "SELECT form_id FROM {$entries_table_name} WHERE id=%d LIMIT 0, 1",
                    array($lid)
                        );
        $form_id = $wpdb->get_var($query);
        $allowed_forms_list = $this->get_allowed_forms();
        if (!in_array($form_id, $allowed_forms_list)) {
            return false;
        }
        
        return true;
    }
    // end of is_allowed_entry()
    

    private function redirect_to_forms() {
    
        // its late to user wp_redirect() ad WP sent some headers already, so use this method for redirection
?>
        <script>
            document.location.href = '<?php echo admin_url('admin.php?page=gf_edit_forms'); ?>';
        </script>
<?php                    
        die;
        
    }
    // end of redirect_to_forms()

    
    public function prohibited_links_redirect() {
        
        $current_user = wp_get_current_user();
        $min_cap = $this->lib->user_can_which($current_user, GFCommon::all_caps());
        if ( empty($min_cap) ) {
            return;   
        }
        
        $url_parts = wp_parse_url($_SERVER['REQUEST_URI']);
        if (strpos($url_parts['path'], 'admin.php')===false) {  // URL is not under our control
            return;
        }
        
        $args = wp_parse_args($url_parts['query'], array());
        if (!$this->is_controled_url($args)) {
            return;
        }

        $allowed_forms_list = $this->get_allowed_forms();
        if (count($allowed_forms_list)==0) {   // no limits
            return;
        }
        
        $id = $this->get_form_id($args);                
        if ($id==0) {
            return;
        }                  
        
        if ( !in_array($id, $allowed_forms_list) ) {    // access to this form is prohibited - redirect user back to the forms list
            $this->redirect_to_forms();
        }
        
        if (!$this->is_allowed_entry($args)) {  // requiested entry belongs to not allowed form - redirect user back to the forms list
            $this->redirect_to_forms();
        }
                                    
    }
    // end of prohibited_links_redirect()

    
    protected function modify_recent_forms_list($query) {
        
        $allowed_forms = $this->get_allowed_forms();
        if (count($allowed_forms)>0) {
            $allowed_forms_str = URE_Base_Lib::esc_sql_in_list('int', $allowed_forms);
            $query .= " AND id IN ($allowed_forms_str)";
        }        
        
        return $query;
    }
    // end of modify_recent_forms_list()
    
    
    protected function modify_forms_list($query, $allowed_forms_str) {
        
        $insert_where_str = "f.id IN ($allowed_forms_str)"; 
        $orderby_pos = strpos($query, 'ORDER BY');
        $where_pos = strpos($query, 'WHERE');
        if ($where_pos===false) {
          $insert_where_str = 'WHERE '.$insert_where_str;
        } else {
            $insert_where_str = 'AND '.$insert_where_str;
        }
        if ($orderby_pos!==false) {
            $query = substr($query, 0, $orderby_pos - 1) .' '. $insert_where_str .' '. substr($query, $orderby_pos);
        } else {
            $query = $query .' '. $insert_where_str;
        }
                
        return $query;
    } 
    // end of modify_forms_list();   
    
    
    protected function filter_form_list_gf_pages($query) {
        
        $allowed_forms = $this->get_allowed_forms();
        if (count($allowed_forms)>0) {
            $allowed_forms_str = URE_Base_Lib::esc_sql_in_list('int', $allowed_forms);
            if (substr(trim($query), 0, 11)==='SELECT f.id') {                   
                $query = $this->modify_forms_list($query, $allowed_forms_str);
            } else if ($query==$this->count_forms_query) {
                $query = "
            SELECT
            (SELECT count(0) FROM {$this->form_table_name} WHERE is_trash = 0 AND id IN ($allowed_forms_str)) as total,
            (SELECT count(0) FROM {$this->form_table_name} WHERE is_active=1 AND is_trash = 0 AND id IN ($allowed_forms_str)) as active,
            (SELECT count(0) FROM {$this->form_table_name} WHERE is_active=0 AND is_trash = 0 AND id IN ($allowed_forms_str)) as inactive,
            (SELECT count(0) FROM {$this->form_table_name} WHERE is_trash=1 AND id IN ($allowed_forms_str)) as trash
            ";
            }
        }                        
        
        return $query;
        
    }
    // end of filter_form_list_gf_pages()
    
        
    protected function dashboard_widget_query_injection($query, $where_field, $inject_key) {
        
        $allowed_forms = $this->get_allowed_forms();
        if (count($allowed_forms)>0) {
            $allowed_forms_str = URE_Base_Lib::esc_sql_in_list('int', $allowed_forms);
            $insert_where_str = ' AND '. $where_field. " IN ($allowed_forms_str)"; 
            $groupby_pos = strpos($query, $inject_key.' BY');                
            $query = substr($query, 0, $groupby_pos - 1) .' '. $insert_where_str .' '. substr($query, $groupby_pos);
        }
        
        return $query;
    }
    // end of dashboard_widget_query_injection()
    
    
    protected function _restrict_form_list($query) {
        
        if (strpos($query, $this->form_table_name)!==false && 
            substr(trim($query), 0, 54)==='SELECT display_meta, confirmations, notifications FROM') {
            $query = $this->modify_recent_forms_list($query);
            return $query;
        }
        
        $controlled_pages = array(
            'form_list',
            'form_editor',
            'form_settings',
            'entry_list', 
            'entry_detail', 
            'export_entry', 
            'export_form');
        $page = GFForms::get_page();
        if (in_array($page, $controlled_pages) && strpos($query, $this->form_from_key)!==false) {
            $query = $this->filter_form_list_gf_pages($query);
            return $query;
        }
        
        if (is_blog_admin()) {  // if not admin dashboard - nothing to change
            $uri = trim($_SERVER['REQUEST_URI']);
            $question_pos = strpos($uri, '?');
            if ($question_pos!==false) {
                $uri = substr($uri, 0, $question_pos);
            }
            $admin_url1 = admin_url('index.php');
            $parsed_url1 = parse_url($admin_url1);
            $uri_len = strlen($uri);
            $admin_path_len1 = strlen($parsed_url1['path']);
            $compare1 = substr($uri, $uri_len - $admin_path_len1);
            if ($compare1!==$parsed_url1['path']) {  // '/wp-admin/index.php'
                $admin_url2 = admin_url();
                $parsed_url2 = parse_url($admin_url2);
                $admin_path_len2 = strlen($parsed_url2['path']);
                $compare2 = substr($uri, $uri_len - $admin_path_len2);
                if ($compare2!==$parsed_url2['path']) {  //  '/wp-admin/'
                    return $query;
                }            
            }
            // set filter for dashboard GF widget queries at forms_model.php, v. 1.8.3 get_form_summary(), line # 170
            if (substr(trim($query), 0, 17)==='SELECT l.form_id,') {
                $query = $this->dashboard_widget_query_injection($query, 'l.form_id', 'GROUP');
            } elseif (substr(trim($query), 0, 17)==='SELECT id, title,') {
                $query = $this->dashboard_widget_query_injection($query, 'id', 'ORDER');
            }        
        }
               
        return $query;

    }
    // end of _restrict_form_list()
    
        
    public function restrict_form_list($query) {
        remove_filter('query', array($this, 'restrict_form_list' ));
        $query = $this->_restrict_form_list($query);
        add_filter('query', array($this, 'restrict_form_list' ));

        return $query;
    }
    // restrict_form_list()
    
}
// end of URE_GF_Access