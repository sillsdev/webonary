<?php 
global $qaplus_options;	
$qaplus_options = get_option( 'qaplus_options' );
$qaplus_admin = get_option( 'qaplus_admin_options' );
$qaplus_auth = get_option( 'qaplus_auth' );
 
$qaplus_errors = array();

$page_exists = get_page_by_path( $qaplus_options['faq_slug'] );
$qaplus_admin = get_option( 'qaplus_admin_options' );

if ( ! $page_exists && $qaplus_admin['dismiss_slug'] != "true" ) {
	$qaplus_errors[] = '' . __('The FAQ homepage', 'qa-free') . '"' . $qaplus_options['faq_slug'] . '"' . __('doesn\'t exist yet. Would you like to create it?', 'qa-free') . '<br /><br />
	<form type="post" action="" id="createPage">
	<input type="hidden" name="action" value="addQaplusPage"/>
	<input  class="button-primary" type="submit" value="Create Page">
	</form>
	
	<form type="post" action="" id="dismissQaplus">
	<input type="hidden" name="action" value="dismissQaplusCreate"/>
	<input type="submit" value="dismiss">
	</form>
	<div id="feedback"></div>'; 
}

// Add a menu for our option page
add_action('admin_menu', 'q_a_plus_add_page');
function q_a_plus_add_page() {
	add_options_page( 'Q & A', 'Q & A', 'manage_options', 'qaplus', 'q_a_plus_option_page' );
}

function qaplus_admin_scripts() {
	global $qaplus_options;
	wp_enqueue_script( 'jquery' );
	wp_enqueue_script( 'jquery-ui-core' );
	wp_enqueue_script( 'jquery-ui-tabs' );
	wp_register_script( 'q-a-plus-admin', plugins_url( 'js/q-a-plus-admin.js', __FILE__ ), false, $qaplus_options['version'], true); 
	wp_enqueue_script( 'q-a-plus-admin' );
	wp_register_style( 'q-a-plus-admin', plugins_url( 'css/q-a-plus-admin.css', __FILE__ ), false, $qaplus_options['version'], 'screen' ); 
	wp_enqueue_style( 'q-a-plus-admin' );
}

if ( isset( $_GET['page'] ) && $_GET['page'] == "qaplus" ) {
	add_action('init', 'qaplus_admin_scripts');
}

// Draw the option page
function q_a_plus_option_page() {


global $qaplus_errors;	

$qaplus_options = get_option( 'q_a_plus_settings' );

/* We're going to run this a second time here */

function qaplus_flush_rules() {
	global $wp_rewrite;
	$wp_rewrite->flush_rules();
}

if ( isset($_GET['settings-updated'])) {
	qaplus_flush_rules();
} 

// set up some defaults if these fields are empty
?>
	<div class="wrap">

	<div class="updated fade" style="max-width: 780px; margin: 18px 0 0 18px">
    <p style="line-height: 1.4em;"><?php _e('Thanks for downloading Q & A! If you like it, please be sure to give us a positive rating in the <a href="http://wordpress.org/extend/plugins/q-and-a/">WordPress repository</a>, it means a lot to us.', 'qa-free'); ?></p>
  	<p style="line-height: 1.4em;"><?php _e('If you like Q & A and would like more advanced features, please take a look at <strong><a href="http://madebyraygun.com/wordpress/plugins/q-and-a-plus">Q & A Plus</a></strong>, our premium version of the plugin. It\'s got more display options, support for user submissions, user ratings, and even more options to explore.', 'qa-free'); ?></p>
  </div>

	<div id="tabs">
		<ul>
			<li><a id="tab-anchor-1" class="tab-anchor" href="#tabs-1"><?php _e('Plugin Settings', 'qa-free'); ?></a></li>
			<li><a id="tab-anchor-2" class="tab-anchor" href="#tabs-2"><?php _e('Documentation', 'qa-free'); ?></a></li>
		</ul>

		<?php screen_icon(); ?>
		<h2>Q &amp; A</h2>

		<?php if ( $qaplus_errors ) {
			foreach ( $qaplus_errors as $error ) { 
				echo '<div id="message" class="updated fade">
					<p>' . $error . '</p>
					</div>';	
			}
		}?>
		
		<div id="tabs-1" class="tab">
		
		<form action="options.php" method="post">
			<?php settings_fields('qaplus_options'); ?>
			<?php do_settings_sections('q_a_plus'); ?>
			<p class="submit">
			<input type="submit" class="button-primary" value="<?php _e('Save Changes', 'qa-free'); ?>" />
			</p>
		</form>
		
		</div><!--#tabs-1-->
										
		<div id="tabs-2" class="tab" style="width:700px">
		
		<?php require('documentation.php'); ?>
								
		</div><!--#tabs-2-->
	</div><!--#tabs-->
		
	<a href="http://madebyraygun.com"><img style="margin-top:30px;" src="<?php echo plugins_url( 'img/logo.png', __FILE__ );?>" width="50" height="50" alt="Made by Raygun" /></a>
	<p><?php _e('You\'re using Q &amp; A Plus, made by <a href="http://madebyraygun.com">Raygun</a>. Check out our <a href="http://madebyraygun.com/wordpress/" target="_blank">other plugins</a>, and if you have any problems, stop by our <a href="http://madebyraygun.com/support/" target="_blank">support forum</a>!', 'qa-free'); ?></p>
</div>

<?php
}

// Register and define the settings
add_action('admin_init', 'q_a_plus_admin_init');

function q_a_plus_admin_init(){

	global $gallery_style_options;

	register_setting(
		'qaplus_options',
		'qaplus_options',
		'q_a_plus_validate_options'
	);
	
	add_settings_section(
		'q_a_plus_homepage_settings',
		__('FAQ Homepage Options', 'qa-free'),
		'q_a_plus_section_text',
		'q_a_plus'
	);

	add_settings_section(
		'q_a_plus_single_settings',
		__('Single FAQ Options','qa-free'),
		'q_a_plus_section_text',
		'q_a_plus'
	);
	
	add_settings_section(
		'q_a_plus_general_settings',
		__('Global Options','qa-free'),
		'q_a_plus_section_text',
		'q_a_plus'
	);

	add_settings_section(
		'q_a_plus_advanced_settings',
		__('Advanced Options','qa-free'),
		'q_a_plus_section_text',
		'q_a_plus'
	);
			
	add_settings_field(
		'q_a_plus_slug',
		__( 'FAQ homepage <span class="vtip" title="Where would you like your FAQ homepage to live? This can be a page that already exists on your site, but it shouldn\'t be the front page.">?</span>', 'qa-free' ),
		'q_a_plus_slug_input',
		'q_a_plus',
		'q_a_plus_homepage_settings'
	);
	
	add_settings_field(
		'q_a_plus_limit',
		__( 'FAQs per category <span class="vtip" title="How many items should we show in each category on the Q & A homepage? -1 shows all FAQ entries. Users will be able to click a link to see all questions on the category page.">?</span>', 'qa-free' ),
		'q_a_plus_limit_input',
		'q_a_plus',
		'q_a_plus_homepage_settings'
	);

	add_settings_field(
		'q_a_plus_catlink',
		__( 'Show category links <span class="vtip" title="Show links to the single category page below each category. Works well in conjunction with the limit setting to condense your FAQ homepage.">?</span>', 'qa-free' ),
		'q_a_plus_catlink_input',
		'q_a_plus',
		'q_a_plus_homepage_settings'
	);

	add_settings_field(
		'q_a_plus_postnumber',
		__( 'Show number of entries <span class="vtip" title="Show total number of FAQ entries in the category header.">?</span>', 'qa-free' ),
		'q_a_plus_postnumber_input',
		'q_a_plus',
		'q_a_plus_homepage_settings'
	);

	add_settings_field(
		'q_a_plus_excerpts',
		__( 'Show excerpts <span class="vtip" title="Show excerpts instead of full entries on the FAQ homepage.">?</span>', 'qa-free' ),
		'q_a_plus_excerpt_input',
		'q_a_plus',
		'q_a_plus_homepage_settings'
	);


	add_settings_field(
		'q_a_plus_breadcrumbs',
		__( 'Show breadcrumbs <span class="vtip" title="Enables a link back to the FAQ homepage in the page title. This can cause problems in some themes so check to make sure it\'s working.">?</span>', 'qa-free' ),
		'q_a_plus_breadcrumbs_input',
		'q_a_plus',
		'q_a_plus_single_settings'
	);

	add_settings_field(
		'q_a_plus_search',
		__( 'Show search <span class="vtip" title="Show the search form on the home and category pages.">?</span>', 'qa-free' ),
		'q_a_plus_search_input',
		'q_a_plus',
		'q_a_plus_general_settings'
	);

	add_settings_field(
		'q_a_plus_search_position',
		__( 'Search box position <span class="vtip" title="Add the search box before or after the FAQ content.">?</span>', 'qa-free' ),
		'q_a_plus_search_position_input',
		'q_a_plus',
		'q_a_plus_general_settings'
	);


	add_settings_field(
		'q_a_plus_show_permalinks',
		__( 'Show permalinks <span class="vtip" title="Add a permalink to each FAQ entry">?</span>', 'qa-free' ),
		'q_a_plus_show_permalinks_input',
		'q_a_plus',
		'q_a_plus_general_settings'
	);

	add_settings_field(
		'q_a_plus_animation_style',
		__( 'Animation style <span class="vtip" title="What animation should be used to show/hide the FAQs?">?</span>', 'qa-free' ),
		'q_a_plus_animation_style_input',
		'q_a_plus',
		'q_a_plus_general_settings'
	);

	add_settings_field(
		'q_a_plus_accordion',
		__( 'Accordion behavior <span class="vtip" title="Clicking a FAQ title closes any other open FAQs.">?</span>', 'qa-free' ),
		'q_a_plus_accordion_input',
		'q_a_plus',
		'q_a_plus_general_settings'
	);

	add_settings_field(
		'q_a_plus_collapsible',
		__( 'Enable show/hide behavior <span class="vtip" title="Uncheck this to turn off the show/hide behavior.">?</span>', 'qa-free' ),
		'q_a_plus_collapsible_input',
		'q_a_plus',
		'q_a_plus_general_settings'
	);

	add_settings_field(
		'q_a_plus_jquery',
		__( 'jQuery version <span class="vtip" title="Which version of jQuery should the plugin use? This can sometimes help with compatibility issues. You should leave this set to <em>WP Included</em> unless you have a specific reason to change it.">?</span>', 'qa-free' ),
		'q_a_plus_jquery_input',
		'q_a_plus',
		'q_a_plus_advanced_settings'
	);
		
	add_settings_field(
		'q_a_plus_version',
		__( 'Version', 'qa-free' ),
		'q_a_plus_version_input',
		'q_a_plus',
		'q_a_plus_advanced_settings'
	);		
}


// Draw the section header
function q_a_plus_section_text() {
}

// Display and fill the form fields

function q_a_plus_slug_input() {
	$qaplus_options = get_option( 'qaplus_options' );
	
	echo "<input name='qaplus_options[faq_slug]' type='text' size='20' value='$qaplus_options[faq_slug]' />";
}

function q_a_plus_limit_input() {
	$qaplus_options = get_option( 'qaplus_options' ); ?>
	
	<input name="qaplus_options[limit]" type="text" size="5" value="<?php echo $qaplus_options['limit'];?>" />
<?php }

function q_a_plus_catlink_input() {
	$qaplus_options = get_option( 'qaplus_options' ); ?>
	
	<input type="checkbox" name="qaplus_options[catlink]" value="true" <?php checked( "true", $qaplus_options['catlink'] ); ?> />
<?php }

function q_a_plus_postnumber_input() {
	$qaplus_options = get_option( 'qaplus_options' ); ?>
	
	<input type="checkbox" name="qaplus_options[postnumber]" value="true" <?php checked( "true", $qaplus_options['postnumber'] ); ?> />
<?php }

function q_a_plus_excerpt_input() {
	$qaplus_options = get_option( 'qaplus_options' ); ?>
	
	<input type="checkbox" name="qaplus_options[excerpts]" value="true" <?php checked( "true", $qaplus_options['excerpts'] ); ?> />
<?php }

function q_a_plus_breadcrumbs_input() {
	$qaplus_options = get_option( 'qaplus_options' ); ?>
	
	<input type="checkbox" name="qaplus_options[breadcrumbs]" value="true" <?php checked( "true", $qaplus_options['breadcrumbs'] ); ?> />
<?php }

function q_a_plus_search_input() {
	$qaplus_options = get_option( 'qaplus_options' );?>

	<select name="qaplus_options[search]" value="<?php echo $qaplus_options[search]; ?>" />
		<option value="home" <?php if ( $qaplus_options['search'] == "home" ) echo " selected='selected'";?>><?php _e( 'On home page', 'qa-free');?></option>
		<option value="category" <?php if ( $qaplus_options['search'] == "categories" ) echo " selected='selected'";?>><?php _e( 'On category pages', 'qa-free');?></option>
		<option value="both" <?php if ( $qaplus_options['search'] == "both" ) echo " selected='selected'";?>><?php _e( 'Both home and category pages', 'qa-free');?></option>
		<option value="none" <?php if ( $qaplus_options['search'] == "none" ) echo " selected='selected'";?>><?php _e( 'Do not enable search', 'qa-free');?></option>
	</select>
<?php }

function q_a_plus_search_position_input() {
	$qaplus_options = get_option( 'qaplus_options' );?>

	<select name="qaplus_options[searchpos]" value="<?php echo $qaplus_options[searchpos]; ?>" />
		<option value="top" <?php if ( $qaplus_options['searchpos'] == "top" ) echo " selected='selected'";?>><?php _e( 'Top', 'qa-free');?></option>
		<option value="bottom" <?php if ( $qaplus_options['searchpos'] == "bottom" ) echo " selected='selected'";?>><?php _e( 'Bottom', 'qa-free');?></option>
	</select>
<?php }

function q_a_plus_animation_style_input() {
	$qaplus_options = get_option( 'qaplus_options' );?>

	<select name="qaplus_options[animation]" value="<?php echo $qaplus_options[animation]; ?>" />
		<option value="fade" <?php if ( $qaplus_options['animation'] == "fade" ) echo " selected='selected'";?>><?php _e( 'Fade', 'qa-free'); ?></option>
		<option value="slide" <?php if ( $qaplus_options['animation'] == "slide" ) echo " selected='selected'";?>><?php _e( 'Reveal', 'qa-free'); ?></option>
		<option value="none" <?php if ( $qaplus_options['animation'] == "none" ) echo " selected='selected'";?>><?php _e( 'None', 'qa-free'); ?></option>
	</select>
<?php }

function q_a_plus_accordion_input() {
	$qaplus_options = get_option( 'qaplus_options' ); ?>
	
	<input type="checkbox" name="qaplus_options[accordion]" value="true" <?php checked( "true", $qaplus_options['accordion'] ); ?> />
<?php }

function q_a_plus_collapsible_input() {
	$qaplus_options = get_option( 'qaplus_options' ); ?>
	
	<input type="checkbox" name="qaplus_options[collapsible]" value="true" <?php checked( "true", $qaplus_options['collapsible'] ); ?> />
<?php }

function q_a_plus_show_permalinks_input() {
	$qaplus_options = get_option( 'qaplus_options' ); ?>
	
	<input type="checkbox" name="qaplus_options[permalinks]" value="true" <?php checked( "true", $qaplus_options['permalinks'] ); ?> />
<?php }

function q_a_plus_jquery_input() {
	$qaplus_options = get_option( 'qaplus_options' );?>

	<select name="qaplus_options[jquery]" value="<?php echo $qaplus_options[jquery]; ?>" />
		<option value="wp" <?php if ( $qaplus_options['jquery'] == "wp" ) echo " selected='selected'";?>><?php _e( 'WP included', 'qa-free');?></option>
		<option value="force" <?php if ( $qaplus_options['jquery'] == "force" ) echo " selected='selected'";?>><?php _e( 'Force latest version', 'qa-free');?></option>
		<option value="none" <?php if ( $qaplus_options['jquery'] == "none" ) echo " selected='selected'";?>><?php _e( 'None', 'qa-free');?></option>
	</select>
<?php }
				
function q_a_plus_version_input() {
	// get option 'text_string' value from the database
	$qaplus_options = get_option( 'qaplus_options' );
		
	// echo the field
	echo '<input type="text" readonly="readonly" id="version" name="qaplus_options[version]" type="text" value="' . $qaplus_options['version'] . '" />';
}

// Validate user input
function q_a_plus_validate_options( $input ) {
	global $qaplus_options;
	if ( isset( $input['faq_slug'] ) ) {
		global $wp_rewrite;
		$wp_rewrite->flush_rules();
	}	   

	if ( ! $input['limit'] )
		$input['limit'] = '10';

	if ( ! isset( $input['postnumber'] ) )
		$input['postnumber'] = null;
	$input['postnumber'] = ( $input['postnumber'] == "true" ? "true" : "false" );

	if ( ! isset( $input['catlink'] ) )
		$input['catlink'] = null;
	$input['catlink'] = ( $input['catlink'] == "true" ? "true" : "false" );

	if ( ! isset( $input['breadcrumbs'] ) )
		$input['breadcrumbs'] = null;
	$input['breadcrumbs'] = ( $input['breadcrumbs'] == "true" ? "true" : "false" );

	if ( ! isset( $input['excerpt'] ) )
		$input['excerpt'] = null;
	$input['excerpt'] = ( $input['excerpt'] == "true" ? "true" : "false" );

	if ( ! isset( $input['accordion'] ) )
		$input['accordion'] = null;
	$input['accordion'] = ( $input['accordion'] == "true" ? "true" : "false" );

	if ( ! isset( $input['collapsible'] ) )
		$input['collapsible'] = null;
	$input['collapsible'] = ( $input['collapsible'] == "true" ? "true" : "false" );

	if ( ! isset( $input['permalinks'] ) )
		$input['permalinks'] = null;
	$input['permalinks'] = ( $input['permalinks'] == "true" ? "true" : "false" );
		
	/* Sanitize textarea input (strip html tags, and escape characters) */
		
	$input['faq_slug'] =  wp_filter_nohtml_kses($input['faq_slug']);
	$input['faq_slug'] = str_replace(' ', '-', $input['faq_slug']);
	$input['faq_slug'] = strtolower($input['faq_slug']);
	
	if ( $input['faq_slug'] != $qaplus_options['faq_slug'] ) {
		$qaplus_admin = get_option( 'qaplus_admin_options' );
		$qaplus_admin['dismiss_slug'] = "false";
		update_option( 'qaplus_admin_options', $qaplus_admin );
	}
	
	return $input;		
}