<!DOCTYPE html>
<?php

$title = get_bloginfo('name', 'display');
if(is_home() || is_front_page())
	$title .= ' - ' . get_bloginfo('description', 'display');
else
	$title .= wp_title('&raquo;', false);


$options = get_option('webonary_options');

$logo = esc_url($options['webonary2_logo'] ?? '');
$home_url = home_url();
$host = filter_input(INPUT_SERVER, 'HTTP_HOST', FILTER_UNSAFE_RAW);

if (!empty($logo_url))
	$logo = "<a href=\"$home_url\"><img src=\"$logo\" alt=\"Logo\"></a>";
elseif (strpos($host, 'localhost') !== false)
	$logo = "<a href=\"$home_url\"><img src=\"https://www.webonary.org/wp-content/uploads/webonary.png\" alt=\"Logo\"></a>";

?>
<!--suppress HtmlRequiredLangAttribute -->
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title><?php echo $title ?></title>

    <link rel="profile" href="http://gmpg.org/xfn/11">
    <link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">

    <?php if ( is_singular() && get_option( 'thread_comments' ) ) wp_enqueue_script( 'comment-reply' ); ?>
	<?php wp_head(); ?>
</head>
<body>
<header>
    <div class="header">
        <div class="container-flex logo">
            <div class="row logo-div">
                <?php echo $logo; ?>
            </div>
            <div id="primary-menu">
		        <?php
		        // Get Top Navigation out of Theme Options

		        wp_nav_menu(array(
			        'theme_location' => 'primary',
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
    </div>
</header>

<?php wp_footer(); ?>
<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>

</body>
</html>
