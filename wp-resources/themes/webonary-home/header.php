<!DOCTYPE html><!-- HTML 5 -->
<html <?php language_attributes(); ?>>

<head>
	<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />
	<meta name="viewport" content="width=device-width" />
	<link rel="shortcut icon" href="/wp-content/blogs.dir/1/files/favicon.ico" />
	<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />

	<title><?php bloginfo('name'); //wp_title('|', true, 'right'); ?></title>

<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<div id="wrapper">
<div id="wrap">
	<div id="head">
		<div id="logo">
			<?php
			$options = get_option('themezee_options');
			if ( isset($options['themeZee_general_logo']) and $options['themeZee_general_logo'] <> "" ) { ?>
				<a href="<?php echo home_url(); ?>"><img src="<?php echo esc_url($options['themeZee_general_logo']); ?>" alt="Logo" /></a>
			<?php } else { ?>
				<a href="<?php echo home_url(); ?>/"><h1><?php bloginfo('name'); ?></h1></a>
			<?php } ?>
		</div>
	<?php if (has_nav_menu('top_navi')){?>
			<div id="topnavi">
		<div style="float:right; margin-bottom: 20px;">
			<form method="get" id="search" action="https://duckduckgo.com/" target="_blank">
				<input type="hidden" name="sites"value="www.webonary.org"/>
				<input type="text" name="q" maxlength="255" placeholder="Search"/>
				<input type="submit" value="Search" />
			</form>
		</div>
		<div style="clear:both;"></div>
				<?php
					wp_nav_menu(array('theme_location' => 'top_navi', 'container' => false, 'menu_id' => 'topnav', 'echo' => true, 'fallback_cb' => 'themezee_default_menu', 'before' => '', 'after' => '', 'link_before' => '', 'link_after' => '', 'depth' => 0));
				?>
			</div>
		<?php } ?>
	</div>
	<?php if (has_nav_menu('top_navi')){?>
		<div id="navbar"><div id="navtitle"><?php bloginfo('name'); ?></div></div>
	<?php } ?>

		<?php if (has_nav_menu('main_navi')){?>
		<div id="navi">
			<?php
				// Get Navigation out of Theme Options
				wp_nav_menu(array('theme_location' => 'main_navi', 'container' => false, 'menu_id' => 'nav', 'echo' => true, 'fallback_cb' => 'themezee_default_menu', 'before' => '', 'after' => '', 'link_before' => '', 'link_after' => '', 'depth' => 0));
			?>
		</div>
		<?php } ?>
		<div class="clear"></div>

	<?php if( get_header_image() != '' and !is_page_template('template-frontpage.php') ) : ?>
		<div id="custom_header">
			<img src="<?php echo get_header_image(); ?>" />
		</div>
	<?php endif; ?>