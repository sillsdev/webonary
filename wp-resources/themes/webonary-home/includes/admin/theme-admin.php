<?php

add_action('admin_menu', 'themezee_admin_add_page');
function themezee_admin_add_page() {
	add_theme_page(__('Theme Options', 'themezee_lang'), __('Theme Options', 'themezee_lang'), 'edit_theme_options', 'themezee', 'themezee_options_page');
}

// Display admin options page
function themezee_options_page() {
	$options = get_option('themezee_options');
	$theme_data = wp_get_theme();
?>
			
	<div class="wrap zee_admin_wrap">

		<div id="zee_admin_head">
			<div id="zee_options_logo">
				<a href="http://themezee.com/" target="_blank">
					<img src="<?php echo get_template_directory_uri(); ?>/includes/admin/images/themezee_logo.png" alt="Logo" />
				</a>
			</div>
		</div>
		<div class="clear"></div>
		
		<div id="zee_admin_heading">
		<div class="icon32" id="icon-themes"></div>
		<h2><?php echo $theme_data->Name; ?> <?php _e('Theme Options', 'themezee_lang'); ?></h2>
		</div>
		<?php if ( isset( $_GET['settings-updated'] ) ) : ?>
			<div class="updated"><p><?php _e('Theme settings updated successfully.', 'themezee_lang'); ?></p></div>
		<?php endif; ?>
		<div class="clear"></div>
			
		<?php
			themezee_options_page_tabs();
			
			if ( isset ( $_GET['tab'] ) ) : $tab = esc_attr($_GET['tab']); else: $tab = 'welcome'; endif;
			
			if ( $tab == 'welcome' ) :
				themezee_options_welcome_screen();
			else:
		?>
			<form class="zee_form" action="options.php" method="post">
				
					<div class="zee_settings">
						<?php settings_fields('themezee_options'); ?>
						<?php do_settings_sections('themezee'); ?>
					</div>
				
				
				<input name="themezee_options[validation-submit]" type="hidden" value="<?php echo $tab ?>" />

				<p><input name="Submit" class="button-primary" type="submit" value="<?php esc_attr_e('Save Changes', 'themezee_lang'); ?>" /></p>
			</form>
			<?php endif; ?>
			
			<?php themezee_options_sidebar(); ?>
	</div>

<?php
}

// Display Sidebar
function themezee_options_sidebar() {
	$theme_data = wp_get_theme();
	$pro_url = ZEE_THEME_URL;
	$club_url = 'http://themezee.com/join-the-theme-club/';
?>
	<div class="zee_options_sidebar">
	
		<dl><dt><h4><?php _e('Theme Data', 'themezee_lang'); ?></h4></dt>
			<dd>
				<p><?php _e('Name', 'themezee_lang'); ?>: <?php echo $theme_data->Name; ?><br/>
				<?php _e('Version', 'themezee_lang'); ?>: <b><?php echo $theme_data->Version; ?></b>
				<a href="<?php echo get_template_directory_uri(); ?>/changelog.txt" target="_blank"><?php _e('(Changelog)', 'themezee_lang'); ?></a><br/>
				<?php _e('Author', 'themezee_lang'); ?>: <a href="http://themezee.com/" target="_blank">ThemeZee</a><br/>
				</p>
			</dd>
		</dl>
		
		<dl><dt><h4><?php _e('Upgrade', 'themezee_lang'); ?> <?php echo $theme_data->Name; ?></h4></dt>
			<dd>
				<ul>
					<li><a href="<?php echo $pro_url; ?>#proversion" target="_blank"><?php _e('Check out the PRO Version', 'themezee_lang'); ?></a></li>
					<li><a href="<?php echo $club_url; ?>" target="_blank"><?php _e('Join the Theme Club and get Support', 'themezee_lang'); ?></a></li>
				</ul>
			</dd>
		</dl>
		
		<dl><dt><h4><?php _e('About ThemeZee', 'themezee_lang'); ?></h4></dt>
			<dd>
				<p><?php _e('ThemeZee is a stunning place of the <b>greatest WordPress Themes</b>.', 'themezee_lang'); ?></p>
				<p><?php _e('You can download several <b>FREE WordPress Themes</b>, join the <b>Theme Club</b> or browse through the <b>best premium themes</b> from other developers. ', 'themezee_lang'); ?></p>
				<p><a href="http://themezee.com/" target="_blank"><?php _e('Visit ThemeZee.com now', 'themezee_lang'); ?></a></p>
			</dd>
		</dl>
				
		<dl><dt><h4><?php _e('Subscribe Now', 'themezee_lang'); ?></h4></dt>
			<dd>
				<p><?php _e('Subscribe now and get informed about each <b>Theme Release</b> from ThemeZee.', 'themezee_lang'); ?></p>
				<ul class="subscribe">
					<li><img src="<?php echo get_template_directory_uri(); ?>/includes/admin/images/rss.png"/><a href="http://themezee.com/feed/" target="_blank"><?php _e('RSS Feed', 'themezee_lang'); ?></a></li>
					<li><img src="<?php echo get_template_directory_uri(); ?>/includes/admin/images/email.png"/><a href="http://feedburner.google.com/fb/a/mailverify?uri=Themezee" target="_blank"><?php _e('Email Subscription', 'themezee_lang'); ?></a></li>
					<li><img src="<?php echo get_template_directory_uri(); ?>/includes/admin/images/twitter.png"/><a href="http://twitter.com/ThemeZee" target="_blank"><?php _e('Follow me on Twitter', 'themezee_lang'); ?></a></li>
					<li><img src="<?php echo get_template_directory_uri(); ?>/includes/admin/images/facebook.png"/><a href="http://www.facebook.com/ThemeZee" target="_blank"><?php _e('Become a Facebok Fan', 'themezee_lang'); ?></a></li>
				</ul>
			</dd>
		</dl>
	</div>
	<div class="clear"></div>
<?php
}
// Display Welcome Screen
function themezee_options_welcome_screen() {
	$theme_data = wp_get_theme();
	$pro_url = ZEE_THEME_URL;
	$club_url = 'http://themezee.com/join-the-theme-club/';
?>
	<div id="zee_welcome">
		<h3><?php _e('Thank you for installing this theme!', 'themezee_lang'); ?></h3>
		<div class="container">
			<h1><?php _e('Welcome to', 'themezee_lang'); ?> <?php echo $theme_data->Name; ?></h1>
			<div class="zee_intro">
				<?php _e("First of all, the number of options might alarm you, <b>but don't panic</b>. Everything is organized and documented well enough for you.", 'themezee_lang'); ?>
			</div>
		</div>
		<div class="welcome_halfed">
			<div class="welcome_left">
				<h3><?php _e('Want more features?', 'themezee_lang'); ?></h3>
				<div class="container">
					<h2><?php _e('Check out', 'themezee_lang'); ?> <?php echo $theme_data->Name; ?>Pro</h2>
					<p><?php _e('The <b>PRO Version</b> provide additional features to <b>customize</b> and configure your Theme.', 'themezee_lang'); ?></p>
					<p><h4>Some Pro Features:</h4>
						<ul>
							<li>+ several Pro Widgets</li>
							<li>+ advanced Custom Color Management</li>
							<li>+ advanced Layout Options</li>
							<li>+ Frontpage Template</li>
							<li>+ unlimited Font Manager</li>
							<li>+ and a lot of more..</li>
						</ul>
						<a class="welcome_button" href="<?php echo $pro_url; ?>#proversion" target="_blank"><?php _e('Learn more about the PRO Version', 'themezee_lang'); ?></a>
					</p>
				</div>
			</div>
			<div class="welcome_right">
				<h3><?php _e('Need support?', 'themezee_lang'); ?></h3>
				<div class="container">
					<h2><?php _e('Join the Theme Club', 'themezee_lang'); ?></h2>
					<p><?php _e('You want <b>top-notch Support</b> for installing and configuring your Theme? Become a <b>Member</b>!', 'themezee_lang'); ?></p>
					<p><h4>Theme Club Features:</h4>
						<ul>
							<li>+ access to the Support Forum at ThemeZee.com</li>
							<li>+ download all Pro Themes </li>
							<li>+ advanced online Theme Documentation</li>
							<li>+ fast and helpful answers to all your questions</li>
						</ul>
						<a class="welcome_button" href="<?php echo $club_url; ?>" target="_blank"><?php _e('Learn more about the Theme Club', 'themezee_lang'); ?></a>
					</p>
				</div>
			</div>
			<div class="clear"></div>
		</div>
					
		<h3><?php _e('Not happy with', 'themezee_lang'); ?> <?php echo $theme_data->Name; ?>?</h3>
		<div class="container">
		<p><?php _e('ThemeZee.com provide several other <b>free WordPress Themes</b>.', 'themezee_lang'); ?>
		<a href="http://themezee.com/wordpress/free-themes/" target="_blank"><?php _e('Click here to browse through all of my themes.', 'themezee_lang'); ?></a>
		</p>
		</div>
	</div>
<?php
}

// Display Settings Page Tabs Navigation Bar
function themezee_options_page_tabs( $current = 'welcome' ) {
	
	// Get the current tab
	if ( isset( $_GET['tab'] ) ) :
		$current = esc_attr($_GET['tab']);
	else:
		$current = 'welcome';
	endif;
	
	// Fetch all Tabs from theme-settings.php
	$tabs = themezee_get_settings_page_tabs();
	
	// Loop to create Tabs Navigation
	$links = array();
	foreach( $tabs as $tab => $name ) :
		if ( $tab == $current ) :
			$links[] = "<a class=\"nav-tab nav-tab-active\" href=\"?page=themezee&tab=$tab\">$name</a>";
		else :
			$links[] = "<a class=\"nav-tab\" href=\"?page=themezee&tab=$tab\">$name</a>";
		endif;
	endforeach;
	
	// Display Tab Navigaiton
	echo '<h2 id="zee_tabs_navi" class="nav-tab-wrapper">';
	foreach ( $links as $link ) : echo $link; endforeach;
	echo '</h2>';
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
		
		case 'url':
			echo "<input id='".$setting['id']."' name='themezee_options[".$setting['id']."]' type='text' value='". esc_url($options[$setting['id']]) ."' />";
			echo '<br/><label>'.$setting['desc'].'</label>';
		break;
		
		case 'textarea':
			echo "<textarea id='".$setting['id']."' name='themezee_options[".$setting['id']."]' rows='5'>" . esc_attr($options[$setting['id']]) . "</textarea>";
			echo '<br/><label>'.$setting['desc'].'</label>';
		break;
		
		case 'html':
			echo "<textarea id='".$setting['id']."' name='themezee_options[".$setting['id']."]' rows='5'>" . esc_attr($options[$setting['id']]) . "</textarea>";
			echo '<br/><label>'.$setting['desc'].'</label>';
		break;
			
		case 'checkbox':
			echo "<input id='".$setting['id']."' name='themezee_options[".$setting['id']."]' type='checkbox' value='true'";
			checked( $options[$setting['id']], 'true' );
			echo ' /><label> '.$setting['desc'].'</label>';
		break;
		
		case 'multicheckbox':
			echo "<input id='".$setting['id']."' name='themezee_options[".$setting['id']."]' type='hidden' value='true' />";
			foreach ( $setting['choices'] as $value => $label ) {
				$checkbox = $setting['id'] . '_' . $value;
				if ( ! isset( $options[$checkbox] ) )
					$options[$checkbox] = $setting['std'];
		
				echo "<input id='".$checkbox."'";
				checked( $options[$checkbox], 'true' );
				echo " type='checkbox' name='themezee_options[".$checkbox."]' value='true'/> " . $label . "<br/>";
			}
			echo '<label>'.$setting['desc'].'</label>';
		break;
	
		case 'select':
			echo "<select id='".$setting['id']."' name='themezee_options[".$setting['id']."]'>";
		 
			foreach ( $setting['choices'] as $value => $label ) {
				echo "<option ".selected( $options[$setting['id']], $value )." value='" . $value . "' >" . $label . "</option>";
			}
		 
			echo "</select>";
			echo '<br/><label>'.$setting['desc'].'</label>';
		break;
		
		case 'radio':
			foreach ( $setting['choices'] as $value => $label ) {
				echo "<input id='".$setting['id']."'";
				checked( $options[$setting['id']], $value );
				echo " type='radio' name='themezee_options[".$setting['id']."]' value='" . $value . "'/> " . $label . "<br/>";
			}
			echo '<label>'.$setting['desc'].'</label>';
		break;

		case 'image':
			echo "<p class='zee-image-bg'><img id='".$setting['id']."img' src='" . esc_attr($options[$setting['id']]) . "' /></p>";
			echo '<input class="zee-upload-image-field" id="'.$setting['id'].'" name="themezee_options['.$setting['id'].']" type="text" value="'. esc_attr($options[$setting['id']]) .'" />';
			echo '<input class="zee-upload-image-button button-secondary" type="button" value="'. __("Upload Image", "themezee_lang") .'" />';
			echo '	<label>'.$setting['desc'].'</label>';
			
		break;

		case 'fontpicker':
			echo "<select id='".$setting['id']."' name='themezee_options[".$setting['id']."]'>";
				foreach ( $setting['choices'] as $value => $label ) {
					echo "<option style='font-size: 1.3em; font-family: ".$value.";' ".selected( $options[$setting['id']], $value )." value='" . $value . "' >" . $label . "</option>";
				}
			echo "</select>";
			echo '<br/><label>'.$setting['desc'].'</label>';
			echo "<div id='zee-font-bg' style='font-family: " . esc_attr($options[$setting['id']]) . ";'>Grumpy wizards make toxic brew for the evil Queen and Jack.</div>";

		break;
		
		case 'colorpicker':
			echo "#<input id='".$setting['id']."' name='themezee_options[".$setting['id']."]' class='colorpickerfield' type='text' maxlength='6' value='". esc_attr($options[$setting['id']]) ."' />";
			echo '<br/><label>'.$setting['desc'].'</label>';
		break;
		
		case 'fontsizer':
			echo "<input id='".$setting['id']."' name='themezee_options[".$setting['id']."]' class='fontsizerfield' type='text' maxlength='2' value='". esc_attr($options[$setting['id']]) ."' /> pt";
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
function themezee_register_settings() {

	// Choose Setting Tab
	if ( isset ( $_GET['tab'] ) ) :
		$tab = esc_attr($_GET['tab']);
	else:
		$tab = 'general';
	endif;
	
	$themezee_sections = themezee_get_sections($tab);
	$themezee_settings = themezee_get_settings($tab);
	
	//register_setting( 'themezee_options', 'themezee_options', 'themezee_options_validate' );
	
	// Create Setting Sections
	foreach ($themezee_sections as $section) {
		add_settings_section($section['id'], $section['name'], 'themezee_section_text', 'themezee');
	}
	
	// Create Setting Fields
	foreach ($themezee_settings as $setting) {
		add_settings_field($setting['id'], $setting['name'], 'themezee_display_setting', 'themezee', $setting['section'], $setting);
	}
}

// Validate Settings
function themezee_options_validate($input) {
	$options = get_option('themezee_options');

	if ( isset ( $input['validation-submit'] ) ) :
		$tab = $input['validation-submit'];
	else:
		$tab = 'general';
	endif;
	$validate_settings = themezee_get_settings($tab);

	foreach ($validate_settings as $setting) {
		
		if ($setting['type'] == 'checkbox' and !isset($input[$setting['id']]) )
		{
			$options[$setting['id']] = 'false';
		}
		elseif ($setting['type'] == 'multicheckbox')
		{
			foreach ( $setting['choices'] as $value => $label ) {
				$checkbox = $setting['id'] . '_' . $value;
				if ( !isset($input[$checkbox] ) ) :
					$options[$checkbox] = 'false';
				else :
					$options[$checkbox] = 'true';
				endif;
			}
		}
		elseif ($setting['type'] == 'radio' and !isset($input[$setting['id']]) )
		{
			$options[$setting['id']] = 1;
		}
		elseif ($setting['type'] == 'textarea')
		{
			$options[$setting['id']] = esc_textarea(trim($input[$setting['id']]));
		}
		elseif ($setting['type'] == 'html')
		{
			$options[$setting['id']] = wp_kses_post(trim($input[$setting['id']]));
		}
		elseif ($setting['type'] == 'url')
		{
			$options[$setting['id']] = esc_url(trim($input[$setting['id']]));
		}
		else
		{
			$options[$setting['id']] = esc_attr(trim($input[$setting['id']]));
		}
	}
	return $options;
}
function themezee_section_text() {}

// Get Default Options
function themezee_get_default_options() {
	$options = array();
	
	// Fetch all Tabs from theme-settings.php
	$tabs = themezee_get_settings_page_tabs();
	
	foreach( $tabs as $tab => $name ) :
		
		$themezee_settings = themezee_get_settings($tab);
		foreach ($themezee_settings as $setting) :
			
			if ( $setting['type'] != 'multicheckbox' ) :
				$options[$setting['id']] = $setting['std'];
			else :
				foreach ( $setting['choices'] as $value => $label ) {
					$checkbox = $setting['id'] . '_' . $value;
					$options[$checkbox] = $setting['std'];
				}
			endif;
		endforeach;
		
	endforeach;
	
	return $options;
}

// Set Default Options
function themezee_set_default_options() {
     $theme_options = get_option( 'themezee_options' );
     if ( false === $theme_options ) {
          $theme_options = themezee_get_default_options();
     }
    // update_option( 'themezee_options', $theme_options );
}
// Initialize Theme options
//add_action('after_setup_theme','themezee_set_default_options', 9 );

?>