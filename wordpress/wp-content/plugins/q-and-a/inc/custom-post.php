<?php 
add_action( 'init', 'create_qa_post_types', 0 );
function create_qa_post_types() {
	 
	 global $qaplus_options;
	 
	 $labels = array(
		'name' => _x( 'FAQ Categories', 'qa-free' ),
		'singular_name' => _x( 'FAQ Category', 'qa-free'),
		'search_items' =>  __( 'Search FAQ Categories', 'qa-free'),
		'all_items' => __( 'All FAQ Categories', 'qa-free' ),
		'parent_item' => __( 'Parent FAQ Category', 'qa-free' ),
		'parent_item_colon' => __( 'Parent FAQ Category:', 'qa-free'),
		'edit_item' => __( 'Edit FAQ Category', 'qa-free'), 
		'update_item' => __( 'Update FAQ Category', 'qa-free'),
		'add_new_item' => __( 'Add New FAQ Category', 'qa-free'),
		'new_item_name' => __( 'New FAQ Category Name', 'qa-free')
  	);

  	register_post_type( 'qa_faqs',
		array(
			'labels' => array(
				'name' => __( 'FAQs', 'qa-free' ),
				'singular_name' => __( 'FAQ', 'qa-free' ),
				'edit_item'	=>	__( 'Edit FAQ', 'qa-free'),
				'add_new_item'	=>	__( 'Add FAQ', 'qa-free')
			),
			'public' => true,
			'show_ui' => true,
			'capability_type' => 'post',
			'rewrite' => array( 'slug' => $qaplus_options['faq_slug'], 'with_front' => false ),
			'taxonomies' => array( 'FAQs '),
			'supports' => array('title','editor')	
		)
	); 	
  
  	register_taxonomy('faq_category',array('qa_faqs'), array(
		'hierarchical' => true,
		'labels' => $labels,
		'show_ui' => true,
		'query_var' => true,
		'rewrite' => array( 'slug' => 'faq-category' ),
  ));
  
}	

add_action('restrict_manage_posts','restrict_listings_by_categories');
function restrict_listings_by_categories() {
    global $typenow;
    global $wp_query;
    if ($typenow=='qa_faqs') {
        
		$tax_slug = 'faq_category';
        
		// retrieve the taxonomy object
		$tax_obj = get_taxonomy($tax_slug);
		$tax_name = $tax_obj->labels->name;
		// retrieve array of term objects per taxonomy
		$terms = get_terms($tax_slug);

		// output html for taxonomy dropdown filter
		echo "<select name='$tax_slug' id='$tax_slug' class='postform'>";
		echo "<option value=''>Show All $tax_name</option>";
		foreach ($terms as $term) {
			// output each select option line, check against the last $_GET to show the current option selected
			echo '<option value='. $term->slug, $_GET[$tax_slug] == $term->slug ? ' selected="selected"' : '','>' . $term->name .' (' . $term->count .')</option>';
		}
		echo "</select>";
    }
}

/* Add custom columns to the CPT manage screen */

function set_qa_faqs_columns($columns) {
    return array(
        'cb' => '<input type="checkbox" />',
        'title' => __('Title', 'qa-free'),
        'faq_category' => __('Category', 'qa-free'),
        'date' => __('Date', 'qa-free')
    );
}
add_filter('manage_qa_faqs_posts_columns' , 'set_qa_faqs_columns');

add_action('manage_posts_custom_column',  'qa_show_columns');
function qa_show_columns($name) {
    global $post;
    switch ($name) {
        case 'faq_category':
            $faq_cats = get_the_terms(0, "faq_category");
			$cats_html = array();
			if(is_array($faq_cats)){
				foreach ($faq_cats as $term)
						array_push($cats_html, '<a href="edit.php?post_type=qa_faqs&faq_category='.$term->slug.'">' . $term->name . '</a>');

				echo implode($cats_html, ", ");
			}
			break;
		default :
			break;	
	}
}

