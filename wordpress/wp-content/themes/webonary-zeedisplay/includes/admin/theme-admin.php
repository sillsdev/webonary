<?php

add_action('admin_menu', 'themezee_admin_add_page');
function themezee_admin_add_page() {
	add_theme_page("Footer", "Footer", 'edit_theme_options', 'themezee', 'themezee_options_page');
}

// Display admin options page
function themezee_options_page() {
	$options = get_option('themezee_options');
?>
	<div class="wrap zee_admin_wrap">
	
		<div id="zee_admin_header">
			
			<div id="zee_admin_logo">
				<img src="<?php echo get_template_directory_uri(); ?>/includes/admin/images/themezee_logo.png" alt="Logo" />
			</div>
			
			<div id="zee_admin_menu">
				<ul>
					<li><a href="<?php echo THEME_INFO; ?>">Theme Info<span>See theme details</span></a></li>
					<li><a href="http://themezee.com">Other Wordpress Themes<span>Browse all of my free wordpress themes</span></a></li>
				</ul>
			</div>
			<div class="clear"></div>
		</div>
		<div class="clear"></div>
		
		<div class="icon32" id="icon-themes"></div>
		<h2><?php _e('Theme Options', ZEE_LANG); ?></h2>
		
			<form class="zee_form" action="options.php?customcss=<?php echo $_GET['customcss']; ?>" method="post">
				
				<p><input name="Submit" class="button-primary" type="submit" value="<?php esc_attr_e('Save Changes', ZEE_LANG); ?>" /></p>
				
					<div class="zee_settings">
						<?php settings_fields('themezee_options'); ?>
						<?php do_settings_sections('themezee'); ?>
					</div>
					
				<p><input name="Submit" class="button-primary" type="submit" value="<?php esc_attr_e('Save Changes', ZEE_LANG); ?>" /></p>
			</form>
		
	</div>

<?php
}

// Display Setting Fields
function themezee_display_setting( $setting = array() ) {
	$options = get_option('themezee_options');
	
	if ( ! isset( $options[$setting['id']] ) )
		$options[$setting['id']] = $setting['std'];

	switch ( $setting['type'] ) {
	
		case 'text':
			echo "<input id='".$setting['id']."' name='themezee_options[".$setting['id']."]' type='text' value='". esc_attr($options[$setting['id']]) ."' />";
			echo '<br/><label>'.$setting['desc'].'</label>';
		break;
		
		case 'textarea':
			echo "<textarea id='".$setting['id']."' name='themezee_options[".$setting['id']."]' rows='5'>" . esc_attr($options[$setting['id']]) . "</textarea>";
			echo '<br/><label>'.$setting['desc'].'</label>';
		break;
			
		case 'checkbox':
			echo "<input id='".$setting['id']."' name='themezee_options[".$setting['id']."]' type='checkbox' value='true'";
			checked( $options[$setting['id']], 'true' );
			echo ' /><label> '.$setting['desc'].'</label>';
		break;
		
		case 'select':
			echo "<select id='".$setting['id']."' name='themezee_options[".$setting['id']."]'>";
		 
			foreach ( $setting['choices'] as $value => $label ) {
				echo "<option ".selected( $options[$setting['id']], $value )." value='" . $value . "' >" . $label . "</option>";
			}
		 
			echo "</select>";
			echo '<br/><label>'.$setting['desc'].'</label>';
		break;
		case 'showCustomCSS':
			echo "<textarea id='".$setting['id']."' name='themezee_options[".$setting['id']."]' rows='5'>" . esc_attr($options[$setting['id']]) . "</textarea>";
			echo '<br/><label>'.$setting['desc'].'</label>';
		break;
		case 'hideCustomCSS':
			echo "<input id='".$setting['id']."' name='themezee_options[".$setting['id']."]' type='hidden' value='". esc_attr($options[$setting['id']]) ."' />";
		break;
		case 'radio':
			foreach ( $setting['choices'] as $value => $label ) {
				echo "<input id='".$setting['id']."'";
				checked( $options[$setting['id']], $value );
				echo " type='radio' name='themezee_options[".$setting['id']."]' value='" . $value . "'/> " . $label . "<br/>";
			}
		break;
		
		case 'logo':
			$current_user = wp_get_current_user();
			if($current_user->user_login == "vernastutzman" || $current_user->user_login == "philip")
			{
				echo "<p id='zee-logo-bg'><img id='zee-logo-img' src='" . esc_attr($options[$setting['id']]) . "' /></p>";
				echo "<input id='".$setting['id']."' name='themezee_options[".$setting['id']."]' type='text' value='". esc_attr($options[$setting['id']]) ."' />";
				echo '<br/><label>'.$setting['desc'].'</label>';
			}
			else
			{
				echo "<input id='".$setting['id']."' name='themezee_options[".$setting['id']."]' type='hidden' value='". esc_attr($options[$setting['id']]) ."' />";
			}
		break;
		
		case 'colorpicker':
			echo "#<input id='".$setting['id']."' name='themezee_options[".$setting['id']."]' class='colorpickerfield' type='text' maxlength='6' value='". esc_attr($options[$setting['id']]) ."' />";
			echo '<br/><label>'.$setting['desc'].'</label>';
		break;
		
		default:
			echo "<input id='".$setting['id']."' name='themezee_options[".$setting['id']."]' size='40' type='text' value='". esc_attr($options[$setting['id']]) ."' />";
			echo '<br/><label>'.$setting['desc'].'</label>';
		break;
	}
}

// Register Settings
add_action('admin_init', 'themezee_register_settings');
function themezee_register_settings(){
	global $settings;
	global $sections;
					
	register_setting( 'themezee_options', 'themezee_options', 'themezee_options_validate' );
	
	// Create Setting Sections
	foreach ($sections as $section) {
		add_settings_section($section['id'], $section['name'], 'themezee_section_text', 'themezee');
	}
	
	// Create Setting Fields
	foreach ($settings as $setting) {
		if($setting['id'] != "themeZee_custom_css" || $_GET['customcss'] == 1)
		{
			add_settings_field($setting['id'], $setting['name'], 'themezee_display_setting', 'themezee', $setting['section'], $setting);
		}
	}
}

// Validate Settings
function themezee_options_validate($input) {
	global $settings;

	foreach ($settings as $setting) {
		
		if ($setting['type'] == 'checkbox' and !isset($input[$setting['id']]) )
		{
			$options[$setting['id']] = 'false';
		}
		elseif ($setting['type'] == 'radio' and !isset($input[$setting['id']]) )
		{
			$options[$setting['id']] = 1;
		}
		else
		{
			$options[$setting['id']] = trim($input[$setting['id']]);
		}
	}
	return $options;
}
function themezee_section_text() {}

?>