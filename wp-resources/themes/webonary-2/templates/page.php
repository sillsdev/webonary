<?php
$is_front_page = is_front_page();
$sidebar_name = null;

if ($is_front_page) {
    if (get_theme_mod('display_frontpage_sidebar')) {
        if (is_active_sidebar('sidebar-front')) {
	        $sidebar_name = 'front';
        }
        elseif (is_active_sidebar('sidebar-pages')) {
            $sidebar_name = 'page';
        }
    }
}
elseif (get_theme_mod('display_page_sidebar') && is_active_sidebar('sidebar-pages')) {
	$sidebar_name = 'page';
}

$content_class = $sidebar_name ? 'col-md-8 col-xl-9' : '';

?>
<main id="page" class="row">

	<div id="content" class="col col-12 <?php echo $content_class ?>">

		<?php
		if (have_posts()) {

			while (have_posts()) {
				the_post(); ?>

                <div id="page-<?php the_ID(); ?>" <?php post_class(); ?>>

                    <?php if (!$is_front_page) { ?>
                        <h2><?php the_title(); ?></h2>
                    <?php } ?>
                    <div class="pageentry">
                        <?php the_post_thumbnail('medium', array('class' => 'alignleft')); ?>
                        <?php the_content(); ?>
                        <div class="clear"></div>
                        <?php wp_link_pages(); ?>
                    </div>

                </div>

        <?php } } ?>

	</div>

	<?php if ($sidebar_name) get_sidebar($sidebar_name); ?>

</main>
