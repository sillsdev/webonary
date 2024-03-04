<?php

/*
 * User Role Editor WordPress plugin
 * Force post types to use their own capabilities set
 * Author: Vladimir Garagulya
 * Author email: support@role-editor.com
 * Author URI: https://www.role-editor.com
 * License: GPL v2+ 
 */

class URE_Post_Types_Own_Caps {
    
    private $lib = null;
    
    public function __construct() {        
        
        $this->lib = URE_Lib_Pro::get_instance();
        
        // support Divi theme custom post type 'et_pb_builder'
        add_filter('et_builder_should_load_framework', array($this, 'should_load_divi_core'));
        
        add_action('wp_loaded', array($this, 'set_own_caps'), 98, 2);    // execute before URE_Create_Posts_Cap        
    }
    // end of __construct()
    
    
    /**
     * 
     * Divi theme does not register own custom post type 'et_pb_layout' at wp-admin/users.php page: 
     * Divi/includes/builder/core.php::et_builder_should_load_framework()
     * So et_pb_layout CPT is not available by default at User Role Editor page, 
     * and we have to tell Divi to load it for URE pages via Divi's custom filter 'et_builder_should_load_framework'
     * 
     */     
    public function should_load_divi_core($load) {
        global $pagenow;

        // Make it for User Role Editor pages only
        if (!($pagenow=='users.php' && isset($_GET['page']) && $_GET['page']=='users-user-role-editor-pro.php')) {
            return $load;
        }
        
        $load = true;
        
        return $load;
    }
    // end of divi_post_type_load()
    
            
    private function set_cpt_own_caps() {        
        global $wp_post_types;
        
        $post_types = get_post_types(array(), 'objects');
        $_post_types = $this->lib->_get_post_types();
        foreach ($post_types as $post_type) {
            if (!in_array($post_type->name, $_post_types)) {
                continue;
            }
            if ($post_type->name=='post' || $post_type->name=='page') { // do not touch built-in post types
                continue;
            }
            if ($post_type->capability_type!='post' && $post_type->capability_type!='page') {   // Custom post type uses its own capabilities already
                continue;
            }

            $do_it = apply_filters( 'ure_set_cpt_own_caps', true, $post_type->name );
            if ( !$do_it ) {
                continue;
            }
            
            $wp_post_types[$post_type->name]->capability_type = $post_type->name;
            $wp_post_types[$post_type->name]->map_meta_cap = true;
            $cap_object = new stdClass();
            $cap_object->capability_type = $wp_post_types[$post_type->name]->capability_type;
            $cap_object->map_meta_cap = true;
            $cap_object->capabilities = array();
            $create_posts0 = $wp_post_types[$post_type->name]->cap->create_posts;
            $wp_post_types[$post_type->name]->cap = get_post_type_capabilities($cap_object);
            if ($post_type->name=='attachment') {
                $wp_post_types[$post_type->name]->cap->create_posts = $create_posts0;   // restore initial 'upload_files'
            }
        }
    }
    // end of set_cpt_own_caps()
    

    private function set_taxonomies_own_caps() {
        global $wp_taxonomies;
        
        $taxonomies = $this->lib->get_custom_taxonomies( 'names' );
        foreach( $taxonomies as $taxonomy) {
            if ( empty( $wp_taxonomies[$taxonomy]->object_type ) || 
                 !is_array( $wp_taxonomies[$taxonomy]->object_type ) ) {
                continue;
            }
            $object_type = reset( $wp_taxonomies[$taxonomy]->object_type ); // take 1st element of object_type array
            $do_it_for_cpt = apply_filters( 'ure_set_cpt_own_caps', true, $object_type );
            if ( !$do_it_for_cpt ) {
                continue;
            }
            $do_it_for_taxonomy = apply_filters( 'ure_set_cpt_taxonomy_own_caps', true, $taxonomy, $object_type );
            if ( !$do_it_for_taxonomy ) {
                continue;
            }
            
            $wp_taxonomies[$taxonomy]->cap->manage_terms = 'manage_'. $object_type .'_terms';
            $wp_taxonomies[$taxonomy]->cap->edit_terms = 'edit_'. $object_type .'_terms';
            $wp_taxonomies[$taxonomy]->cap->delete_terms = 'delete_'. $object_type .'_terms';
            $wp_taxonomies[$taxonomy]->cap->assign_terms = 'assign_'. $object_type .'_terms';
        }
    }
    // end of set_taxonomies_own_caps()

    
    public function set_own_caps() {
        
        $this->set_cpt_own_caps();
        $this->set_taxonomies_own_caps();
    }
    // end of set_own_caps
        
}
// end of URE_Post_Types_Own_Caps class