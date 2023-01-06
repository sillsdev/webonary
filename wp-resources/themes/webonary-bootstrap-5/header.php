<?php

/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package Bootscore
 * 
 * @version 5.2.0.0
 */

$webonary_theme_options = get_option('theme_mods_webonary-bootstrap-5');

?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <!-- Favicons -->
	<link rel="shortcut icon" href="<?php echo get_stylesheet_directory_uri(); ?>/favicon.ico" type="image/x-icon" />
	<link rel="icon" href="<?php echo get_stylesheet_directory_uri(); ?>/favicon.ico" type="image/x-icon" />
    <?php wp_head(); ?>
	<style>
		:root {
			--webonary-header_bg_color: <?php echo $webonary_theme_options['header_bg_color'] ?? '#f8f9fa'; ?>;
			--webonary-header_text_color: <?php echo $webonary_theme_options['header_text_color'] ?? '#000000'; ?>;
			--webonary-footer_bg_color: <?php echo $webonary_theme_options['footer_bg_color'] ?? '#85005B'; ?>;
			--webonary-footer_text_color: <?php echo $webonary_theme_options['footer_text_color'] ?? '#ffffff'; ?>;
			--webonary-highlight_bg_color: <?php echo $webonary_theme_options['highlight_bg_color'] ?? '#85005B'; ?>;
			--webonary-highlight_text_color: <?php echo $webonary_theme_options['highlight_text_color'] ?? '#ffffff'; ?>;
		}
	</style>
</head>
<body <?php body_class(); ?>>

    <?php wp_body_open(); ?>

    <div id="page" class="site">
		<header id="masthead" class="site-header">
			<div class="webonary-header-background">
				<nav id="nav-main" class="navbar navbar-expand-lg p-0">

					<div class="container-fluid container-lg align-items-stretch">

						<!-- Navbar Brand -->
						<a class="navbar-brand p-0 m-0" href="<?php echo esc_url(home_url()); ?>"><img src="https://www.webonary.org/wp-content/uploads/webonary.png" alt="logo" class="logo xs"></a>

						<!-- Offcanvas Navbar -->
						<div class="d-flex flex-column justify-content-around justify-content-lg-between">
							<div class="d-flex">
								<?php if (is_active_sidebar('language-chooser')) { ?>
									<div class="language-chooser-widget container d-flex flex-row-reverse px-0 pt-0 pt-lg-2">
										<div class="d-flex align-items-center">
											<?php dynamic_sidebar('language-chooser'); ?>
										</div>
									</div>
								<?php } ?>

								<div class="header-actions d-flex d-lg-none align-items-center justify-content-end">

									<!-- Top Nav Widget -->
									<div class="top-nav-widget">
										<?php if (is_active_sidebar('top-nav')) : ?>
											<div>
												<?php dynamic_sidebar('top-nav'); ?>
											</div>
										<?php endif; ?>
									</div>

									<!-- Searchform Large -->
									<div class="d-none d-lg-block ms-1 ms-md-2 top-nav-search-lg">
										<?php if (is_active_sidebar('top-nav-search')) : ?>
											<div>
												<?php dynamic_sidebar('top-nav-search'); ?>
											</div>
										<?php endif; ?>
									</div>

									<!-- Search Toggler Mobile -->
									<button class="btn btn-outline-secondary d-lg-none ms-1 ms-md-2 top-nav-search-md" type="button"
											data-bs-toggle="collapse" data-bs-target="#collapse-search" aria-expanded="false"
											aria-controls="collapse-search">
										<i class="fa-solid fa-magnifying-glass"></i><span
											class="visually-hidden-focusable">Search</span>
									</button>

									<!-- Navbar Toggler -->
									<button class="btn btn-outline-secondary d-lg-none ms-2 ms-sm-3" type="button" style="padding-top: 6px"
											data-bs-toggle="offcanvas" data-bs-target="#offcanvas-navbar"
											aria-controls="offcanvas-navbar">
										<i class="fa-solid fa-bars"></i><span class="visually-hidden-focusable">Menu</span>
									</button>

								</div><!-- .header-actions -->
							</div>

							<div class="offcanvas offcanvas-end flex-grow-0" tabindex="-1" id="offcanvas-navbar">
								<div class="offcanvas-header bg-light">
									<span class="h5 mb-0"><?php esc_html_e('Menu', 'bootscore'); ?></span>
									<button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
								</div>
								<div class="offcanvas-body">
									<!-- Bootstrap 5 Nav Walker Main Menu -->
									<?php
									wp_nav_menu(array(
										'theme_location' => 'main-menu',
										'container' => false,
										'menu_class' => '',
										'fallback_cb' => '__return_false',
										'items_wrap' => '<ul id="bootscore-navbar" class="navbar-nav ms-auto %2$s">%3$s</ul>',
										'depth' => 2,
										'walker' => new bootstrap_5_wp_nav_menu_walker()
									));
									?>
									<!-- Bootstrap 5 Nav Walker Main Menu End -->
								</div>
							</div>

						</div>

					</div><!-- .container -->

				</nav><!-- .navbar -->

				<!-- Top Nav Search Mobile Collapse -->
				<div class="collapse container d-lg-none" id="collapse-search">
					<?php if (is_active_sidebar('top-nav-search')) : ?>
						<div class="mb-2">
							<?php dynamic_sidebar('top-nav-search'); ?>
						</div>
					<?php endif; ?>
				</div>

			</div><!-- .fixed-top .bg-light -->
    	</header><!-- #masthead -->
		<div class="site-name-bar highlight-color">
			<div class="container-fluid container-lg align-items-start">
				<div class="row">
					<div class="site-name"><?php bloginfo('name'); ?></div>
				</div>
			</div>
		</div>