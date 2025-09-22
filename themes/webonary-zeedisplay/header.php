<!DOCTYPE html><!-- HTML 5 -->
<?php
$options = get_option('themezee_options');

if ( isset($options['themeZee_logo']) and $options['themeZee_logo'] <> "") {
	$host = filter_input(INPUT_SERVER, 'HTTP_HOST', FILTER_UNSAFE_RAW);
	if (strpos($host, 'localhost') !== false)
		$logo = '<a href="' . home_url() . '"><img src="https://www.webonary.org/wp-content/uploads/webonary.png" alt="Logo"></a>';
    else
        $logo = '<a href="' . home_url() . '"><img src="' . esc_url($options['themeZee_logo']) . '" alt="Logo"></a>';
}
else {
    $logo = '';
}
?>
<html <?php language_attributes(); ?>>
<head>
	<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />
	<meta name="viewport" content="width=device-width,initial-scale=1.0" />
	<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />

	<title><?php bloginfo('name'); if(is_home() || is_front_page()) { echo ' - '; bloginfo('description'); } else { wp_title(); } ?></title>

	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,400;0,700;1,400;1,700&family=Ubuntu:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">

	<?php webonary_zeedisplay_link_style_sheets() ?>

	<?php if ( is_singular() ) wp_enqueue_script( 'comment-reply' ); ?>
<!-- wp_head -->
	<?php wp_head();
	$upload_dir = wp_upload_dir();
	?>
<!-- wp_head end -->
</head>
<body <?php body_class(); ?> style="text-align:center">
<div align="center">
<!-- My IME complains about an unmatched div here and below, but matching the
     divs causes problems with the site. I assume they are closed elsewhere.-->
<div id="wrapper">

	<div id="wrap">
		<div id="head">
			<div id="logo"><?php echo $logo; ?></div>
			<?php if (has_nav_menu('top_navi') && !isMobile()) {?>
			<div style="display: table-cell; vertical-align: bottom; position: static; padding-bottom: 10px;">
			<?php if (function_exists('qtrans_init')) { ?><div  style="height: 50px;"><div id=navlanguage><nobr><?php echo qtrans_getLanguageLinks('text'); ?></nobr></div></div> <?php } ?>
			<?php
			if(is_active_sidebar('sidebar-header')) : ?>
			<div style="height: 50px;">
				<div id=navlanguage>
				<ul>
					<?php dynamic_sidebar('sidebar-header'); ?>
				</ul>
				<div style="clear: both;"></div>
				</div>
			</div>
			<?php endif; ?>

				<div id="topnavi">
					<?php
					// Get Top Navigation out of Theme Options

						wp_nav_menu(array(
							'theme_location' => 'top_navi',
							'container' => false,
							'echo' => true,
							'before' => '',
							'after' => '',
							'link_before' => '',
							'link_after' => '',
							'depth' => 0,
							'fallback_cb' => ''));

					?>
				</div>
			</div>
			<?php } ?>
		</div>

			<?php if (has_nav_menu('top_navi') && isMobile()) {?>
			<?php if (function_exists('qtrans_init')) { ?><div  style="height: 50px;"><div id=navlanguage><nobr><?php echo qtrans_getLanguageLinks('text'); ?></nobr></div></div> <?php } ?>
			<?php
			if(is_active_sidebar('sidebar-header')) : ?>
			<div style="height: 50px;">
				<div id=navlanguage>
				<ul>
					<?php dynamic_sidebar('sidebar-header'); ?>
				</ul>
				<div style="clear: both;"></div>
				</div>
			</div>
			<?php endif; ?>
			<div style="clear: both;"></div>
				<div id="topnavi">
					<?php
					// Get Top Navigation out of Theme Options
						wp_nav_menu(array(
							'theme_location' => 'top_navi',
							'container' => false,
							'echo' => true,
							'before' => '',
							'after' => '',
							'link_before' => '',
							'link_after' => '',
							'depth' => 0,
							'fallback_cb' => ''));
					?>
				</div>
			<?php } ?>

	<?php if (has_nav_menu('top_navi')){?>
		<div id="navbar"><div id="navtitle"><?php bloginfo('name'); ?></div></div>
	<?php } ?>

		<?php if (has_nav_menu('main_navi')){?>
			<div id="navi">
				<?php
				// Get Main Navigation out of Theme Options
					wp_nav_menu(array(
						'theme_location' => 'main_navi',
						'container' => false,
						'echo' => true,
						'before' => '',
						'after' => '',
						'link_before' => '',
						'link_after' => '',
						'depth' => 0,
						'fallback_cb' => ''));


				?>
			</div>
		<?php } ?>

		<div style="clear: both;"></div>

	<?php if( get_header_image() != '') : ?>
		<div id="custom_header">
			<img src="<?php echo get_header_image(); ?>" style="width:100%;"/>
		</div>
	<?php endif; ?>

<!-- My IME complains about an unmatched div, but matching the divs here causes
problems with the site. I assume they are closed elsewhere.-->
