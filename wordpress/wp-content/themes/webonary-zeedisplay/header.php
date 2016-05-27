<!DOCTYPE html><!-- HTML 5 -->
<?php
$options = get_option('themezee_options');
?>
<html <?php language_attributes(); ?>>
<head>
	<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />
	<meta name="description" content="<?php echo $options['themeZee_description']; ?>">
	<meta name="keywords" content="<?php echo $options['themeZee_keywords']; ?>">
	<?php if(isMobile())
	{
	?>
	<meta name="viewport" content="width=device-width,initial-scale=1.0" />
	<?php
	}
	?>
	<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />
	
	<title><?php bloginfo('name'); if(is_home() || is_front_page()) { echo ' - '; bloginfo('description'); } else { wp_title(); } ?></title>

	<?php
	if(!isMobile()) {  ?>
		<style type="text/css">
		#topnavi {
			position: static; float: right; margin-right: 10px;
		}
		</style>
	<?php
	}
	?>

	<?php webonary_zeedisplay_link_style_sheets() ?>

	<?php if ( is_singular() ) wp_enqueue_script( 'comment-reply' ); ?>
	<?php wp_head();
	$upload_dir = wp_upload_dir();
	?>
</head>
<body <?php body_class(); ?> style="text-align:center">
<div align="center">
<!-- My IME complains about an unmatched div here and below, but matching the
     divs causes problems with the site. I assume they are closed elsewhere.-->
<div id="wrapper" <?php if(!isMobile()) { ?>style=max-width:900px; <?php } ?>>

	<div id="wrap">
		<div id="head">
			<div id="logo" align=left>
				<?php
				$options = get_option('themezee_options');
				if ( isset($options['themeZee_logo']) and $options['themeZee_logo'] <> "") { ?>
					<a href="<?php echo home_url(); ?>"><img src="<?php echo esc_url($options['themeZee_logo']); ?>" alt="Logo" /></a>
				<?php } else { /* ?>
					<a href="<?php echo home_url(); ?>/"><h1><?php bloginfo('name'); ?></h1></a>
				<?php  */ } ?>
			</div>
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
		<div id="navbar" <?php if(!isMobile()) { ?>style=max-width: 900px; <?php } ?>><div id="navtitle"  <?php if(!isMobile()) { ?>style=padding: 12px 0 8px 70px;<?php } ?>><?php bloginfo('name'); ?></div></div>
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