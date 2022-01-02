<?php

$has_sidebar = get_theme_mod('display_post_sidebar') && is_active_sidebar('sidebar-post');
$content_class = $has_sidebar ? 'col-md-8 col-xl-9' : '';
get_header();
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    <div class="entry-content">

        <div class="container my-5">
            <div class="row px-4">
                <div class="col-12 col-lg-8">
                    <?php
                    get_template_part('template-parts/post/post' , 'loop');
                    get_template_part('template-parts/post/post' , 'pagination');
                    ?>
                </div>

                <?php if ($has_sidebar) get_sidebar('pages'); ?>
                <aside class="col-12 col-lg-4 px-0 px-lg-4">
                    <?php get_sidebar('post');?>
                </aside>

            </div>
        </div>

    </div>
</article>

<?php
get_footer();
