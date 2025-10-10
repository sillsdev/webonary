<?php 

if (is_admin()) require_once(ABSPATH . 'wp-includes/pluggable.php');

$qaplus_options = get_option( 'qaplus_options' );
$qaplus_admin = get_option( 'qaplus_admin_options' );

if ( ! $qaplus_options) { // Create the defaults for a new installation

	$qaplus_options['faq_slug'] = 'faqs';
	$qaplus_options['limit'] = '-1';
	$qaplus_options['columns'] = '2';
	$qaplus_options['postnumber'] = 'true';	
	$qaplus_options['excerpts'] = 'false';
	$qaplus_options['search'] = 'home';
	$qaplus_options['searchpos'] = 'top';
	$qaplus_options['submissions'] = 'false';
	$qaplus_options['catdesc'] = 'false';
	$qaplus_options['catlink'] = 'false';
	$qaplus_options['expandall'] = 'none';
	$qaplus_options['ratings'] = 'true';
	$qaplus_options['open'] = 'none';
	$qaplus_options['sort'] = 'rating';
	$qaplus_options['breadcrumbs'] = 'false';
	$qaplus_options['permalinks'] = 'false';
	$qaplus_options['collapsible'] = 'true';
	$qaplus_options['accordion'] = 'true';	
	$qaplus_options['animation'] = 'fade';
	$qaplus_options['version'] = Q_A_PLUS_VERSION;
	$qaplus_options['license'] = '';
	$qaplus_options['jquery'] = 'wp';
	$qaplus_options['licensekey'] = '';
	$qaplus_admin['dismiss_slug'] = "false";
	$qaplus_auth['error'] = '';
	$qaplus_auth['activated'] = '';
	$qaplus_auth['instance'] = '';

	update_option( 'qaplus_options', $qaplus_options );
	update_option( 'qaplus_admin_options', $qaplus_admin );

	/* Now add the zero votes for all pre-existing posts */

	$args = array(
		'post_type'     => 'qa_faqs',
		'post_status'   => 'publish',
		'posts_per_page' => -1
	);
		
	$qa_faqs = new WP_Query( $args );

	while( $qa_faqs->have_posts() ): $qa_faqs->the_post();
		global $post;
		add_post_meta($post->ID, 'votes_count', '0', true);
	endwhile;

} else {  /* Installation already exists, install updates */
	
	if ( $qaplus_options['version'] < '1.0.5' ) { //added a new option in 1.0.5
		$qaplus_options['catlink'] = "false"; 
		$qaplus_options['breadcrumbs'] = "false"; 
	}

	$qaplus_options['version'] = Q_A_PLUS_VERSION;
	update_option( 'qaplus_options', $qaplus_options );
}

?>