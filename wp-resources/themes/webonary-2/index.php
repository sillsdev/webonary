<!DOCTYPE html>
<?php
$search = esc_html__('Search', WEBONARY_THEME_DOMAIN);

if (has_nav_menu('primary'))
    $footer_class= 'footer-primary';
else
	$footer_class= 'footer-alternate';

$copyright = get_theme_mod('webonary_copyright') ?? '';
$copyright = str_replace('[year]', date('Y'), $copyright);

?>
<!--suppress HtmlRequiredLangAttribute -->
<html <?php language_attributes(); ?> dir="ltr">
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title><?php echo Webonary2_Functions::PageTitle(); ?></title>

    <link rel="profile" href="https://gmpg.org/xfn/11">
    <link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">
    <link href="<?php echo Webonary2_Functions::DefaultCSS(); ?>" rel="stylesheet">

    <?php if ( is_singular() && get_option( 'thread_comments' ) ) wp_enqueue_script( 'comment-reply' ); ?>
	<?php wp_head(); ?>
</head>
<body>
<header>
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container-fluid p-0">
	        <?php echo Webonary2_Functions::SiteLogo(); ?>

            <button class="navbar-toggler mx-3" type="button" data-bs-toggle="collapse" data-bs-target="#navbar-toggler-div" aria-controls="navbar-toggler-div" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="container-fluid d-flex flex-column p-0" id="top-nav-div">
                <div class="d-none d-lg-table" id="top-nav-search-div">
                    <form method="get" id="search" action="https://duckduckgo.com/" target="_blank">
                        <input type="hidden" name="sites" value="www.webonary.org">
                        <input type="text" name="q" maxlength="255" placeholder="<?php echo $search ?>" class="form-control d-inline-block w-auto" title="<?php echo $search ?>">
                        <input type="submit" value="<?php echo $search ?>" class="btn btn-secondary" style="margin-top: -2px;">
                    </form>
                </div>

                <div class="collapse navbar-collapse" id="navbar-toggler-div">
                    <ul class="navbar-nav mb-2 mb-lg-0">
			            <?php echo Webonary2_Menu::BootstrapMenu('primary'); ?>
                    </ul>
                </div>

            </div>
        </div>

    </nav>

    <div class="title-bar container-fluid">
        <h2 class="my-2"><?php bloginfo('name'); ?></h2>
    </div>
</header>

<div class="container">
    <div class="row">
        content here
    </div>
</div>

<footer class="<?php echo $footer_class ?> text-center">
    <div class="container-fluid d-flex flex-column">
        <div>
	        <?php echo $copyright ?>
        </div>
        <hr>
        <div id="bottom-branding" class="container-fluid d-flex flex-row flex-wrap justify-content-between">
            <div><img src="<?php echo  get_template_directory_uri(); ?>/images/sil-icon.gif" style="vertical-align:middle;"> <span style="width: 20%; margin-left:10px;">© <?php echo "2013 - " . date("Y"); ?> <a href="http://www.sil.org" target="_blank">SIL International</a><sup>®</sup></span></div>
            <div><img src="<?php echo  get_template_directory_uri(); ?>/images/webonary-icon.png" style="vertical-align:middle;"> <a href="https://www.webonary.org" target="_blank">Webonary.org</a></div>
            <div><a href="https://www.webonary.org/sil-international-terms-of-service-for-webonary-org/?lang=<?php if (function_exists('qtranxf_init_language')) { echo qtranxf_getLanguage(); } else { echo "en"; } ?>" style="width:20%;"><?php _e("Terms of Service", ZEE_LANG); ?></a></div>
        </div>
    </div>
</footer>

<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
<?php wp_footer(); ?>

</body>
</html>
