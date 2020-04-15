<?php
/**
 * @var int $slider_id Slider ID.
 * @var int $show_loading_icon 1 for show, otherwise 0.
 * @var string $loading_icon_type
 * @var Hugeit_Slider_Slide[] $slides
 * @var Hugeit_Slider_Slider $slider
 */
?>

<div class="slider-parent"></div>
<div class="slider_<?php echo $slider_id; ?>" <?php if ($slider->get_view() !== 'thumb_view') {
    echo 'thumb_view';
} ?>>
    <?php
    if ($show_loading_icon) {
        echo '<div class="slider-loader-' . $slider_id . '"></div>';
    }
    ?>
    <ul id="slider_<?php echo $slider_id; ?>" class="<?php if ($slider->get_lightbox()) {
        echo 'slider_lightbox_' . $slider_id;
    } ?> huge-it-slider" data-autoplay="<?php echo $slider->get_video_autoplay(); ?>">
        <?php
        foreach ($slides as $key => $slide) {
            $slide_type = $slides[$key]->get_type();
            $i = 0;
            switch ($slide_type) {
                case 'image': ?>
                    <li class="group"
                        data-thumb="<?php echo wp_get_attachment_url($slides[$key]->get_attachment_id()); ?>"
                        data-title="<?php echo $slides[$key]->get_title(); ?>"
                        data-description="<?php echo $slides[$key]->get_description(); ?>">
                        <?php if ($slider->get_lightbox()) { ?>
                            <a href="<?php echo wp_get_attachment_url($slides[$key]->get_attachment_id()); ?>">
                                <img src="<?php echo wp_get_attachment_url($slides[$key]->get_attachment_id()); ?>"
                                     alt="<?php echo $slides[$key]->get_title(); ?>"/>
                            </a>
                        <?php } else {
                            if ($slides[$key]->get_url()) {
                                $target = ($slides[$key]->get_in_new_tab()) ? "_blank" : "";
                                echo '<a href="' . $slides[$key]->get_url() . '" target="' . $target . '">';
                            } ?>
                            <img src="<?php echo wp_get_attachment_url($slides[$key]->get_attachment_id()); ?>"
                                 alt="<?php echo $slides[$key]->get_title(); ?>"/>
                            <?php if ($slides[$key]->get_url()) {
                                echo '</a>';
                            }
                        } ?>

                        <?php if ($slider->get_lightbox()) {
                            if ($slider->get_view() === 'none' && $slides[$key]->get_title()) {
                                if ($slides[$key]->get_url()) {
                                    $target = ($slides[$key]->get_in_new_tab()) ? "_blank" : "";
                                    echo '<a href="' . $slides[$key]->get_url() . '" class="title_url" target="' . $target . '">';
                                } ?>
                                <div class="huge-it-caption slider-title">
                                    <div><?php echo $slides[$key]->get_title(); ?></div>
                                </div>
                                <?php if ($slides[$key]->get_url()) {
                                    echo '</a>';
                                }
                            }
                        } else {
                            if ($slider->get_view() === 'none' && $slides[$key]->get_title()) { ?>
                                <div class="huge-it-caption slider-title">
                                    <div><?php echo $slides[$key]->get_title(); ?></div>
                                </div>
                            <?php }
                        } ?>

                        <?php if ($slider->get_view() === 'none' && $slides[$key]->get_description()) { ?>
                            <div class="huge-it-caption slider-description">
                                <div><?php echo $slides[$key]->get_description(); ?></div>
                            </div>
                        <?php } ?>
                    </li>
                    <?php
                    break;
                case 'post':
                    $args = array(
                        'numberposts' => $slides[$key]->get_max_post_count(),
                        'offset' => 0,
                        'category' => $slides[$key]->get_term_id(),
                        'orderby' => 'post_date',
                        'order' => 'DESC',
                        'post_type' => 'post',
                        'post_status' => 'publish, future, pending, private',
                        'suppress_filters' => true);
                    $posts = wp_get_recent_posts($args, ARRAY_A);
                    foreach ($posts as $_key => $last_posts) {
                        $imagethumb = wp_get_attachment_image_src(get_post_thumbnail_id($last_posts["ID"]), 'thumbnail-size', true);
                        if (get_post_thumbnail_id($last_posts["ID"])) {
                            ?>
                            <li class="group"
                                data-thumb="<?php if (get_the_post_thumbnail($last_posts["ID"], 'thumbnail')) {
                                    echo $imagethumb[0];
                                }; ?>"
                                data-title="<?php echo $slides[$key]->get_title(); ?>"
                                data-description="<?php echo wp_strip_all_tags($last_posts["post_excerpt"]); ?>">
                                <?php if ($slider->get_lightbox()) { ?>
                                    <a href="<?php if (get_the_post_thumbnail($last_posts["ID"], 'thumbnail')) {
                                        echo $imagethumb[0];
                                    }; ?>">
                                        <img src="<?php if (get_the_post_thumbnail($last_posts["ID"], 'thumbnail')) {
                                            echo $imagethumb[0];
                                        }; ?>" alt="<?php echo $last_posts["post_title"]; ?>"/>
                                    </a>
                                <?php } else {
                                    if ($last_posts["guid"]) {
                                        $target = ($slides[$key]->get_in_new_tab()) ? "_blank" : "";
                                        echo '<a href="' . $last_posts["guid"] . '" target="' . $target . '">';
                                    } ?>
                                    <img src="<?php if (get_the_post_thumbnail($last_posts["ID"], 'thumbnail')) {
                                        echo $imagethumb[0];
                                    }; ?>" alt="<?php echo $last_posts["post_title"]; ?>"/>
                                    <?php if ($last_posts["guid"]) {
                                        echo '</a>';
                                    }
                                } ?>

                                <?php if ($slider->get_lightbox()) {
                                    if ($slider->get_view() === 'none' && $last_posts["post_title"]) {
                                        if ($last_posts["guid"]) {
                                            $target = ($slides[$key]->get_in_new_tab()) ? "_blank" : "";
                                            echo '<a href="' . $last_posts["guid"] . '" class="title_url" target="' . $target . '">';
                                        } ?>
                                        <div class="huge-it-caption slider-title">
                                            <div><?php echo $last_posts["post_title"]; ?></div>
                                        </div>
                                        <?php if ($last_posts["guid"]) {
                                            echo '</a>';
                                        }
                                    }
                                } else {
                                    if ($slider->get_view() === 'none' && $last_posts["post_title"]) { ?>
                                        <div class="huge-it-caption slider-title">
                                            <div><?php echo $last_posts["post_title"]; ?></div>
                                        </div>
                                    <?php }
                                } ?>

                                <?php if ($slider->get_view() === 'none' && wp_strip_all_tags($last_posts["post_excerpt"])) { ?>
                                    <div class="huge-it-caption slider-description">
                                        <div><?php echo wp_strip_all_tags($last_posts["post_excerpt"]); ?></div>
                                    </div>
                                <?php } ?>
                            </li>
                            <?php
                        }
                        $i++;
                    }
                    break;
            }
        }
        ?>
    </ul>
    <?php if (Hugeit_Slider_Options::get_share_buttons() == 1) {
        switch (Hugeit_Slider_Options::get_share_buttons_hover_style()) {
            case '0':
                $class = 'icon-link_' . $slider_id . ' fill';
                break;
        }

        $actual_link = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        ?>


        <div class="share_buttons_<?php echo $slider_id; ?>">
            <?php if (Hugeit_Slider_Options::get_share_buttons_facebook() == 1) { ?>
                <a class="<?php echo $class; ?> share_buttons_facebook_<?php echo $slider_id; ?>"
                   href="https://www.facebook.com/sharer/sharer.php?u=<?php echo $actual_link; ?>"
                   title="Share on Facebook">
                    <i class="fa fa-facebook"></i>
                </a>
            <?php } ?>
            <?php if (Hugeit_Slider_Options::get_share_buttons_twitter() == 1) { ?>
                <a class="<?php echo $class; ?> share_buttons_twitter_<?php echo $slider_id; ?>"
                   href="https://twitter.com/share?url=<?php echo $actual_link; ?>&text=Share Buttons Demo&via=sunnyismoi"
                   title="Share on Twitter">
                    <i class="fa fa-twitter"></i>
                </a>
            <?php } ?>
            <?php if (Hugeit_Slider_Options::get_share_buttons_gp() == 1) { ?>
                <a class="<?php echo $class; ?> share_buttons_gp_<?php echo $slider_id; ?>"
                   href="https://plus.google.com/share?url=<?php echo $actual_link; ?>"
                   title="Share on Google+">
                    <i class="fa fa-google-plus"></i>
                </a>
            <?php } ?>
        </div>
    <?php } ?>
</div>
<script>
    var slider;

    jQuery(function () {
        switch (singleSlider_<?php echo $slider_id; ?>.view) {
            case 'none':
                jQuery('#slider_<?php echo $slider_id; ?>').sliderPlugin({
                    maxWidth: singleSlider_<?php echo $slider_id; ?>.width,
                    maxHeight: singleSlider_<?php echo $slider_id; ?>.height,
                    transition: singleSlider_<?php echo $slider_id; ?>.effect,
                    controls: singleSlider_<?php echo $slider_id; ?>.navigate_by,
                    cropImage: hugeitSliderObj.crop_image,
                    navigation: hugeitSliderObj.show_arrows,
                    delay: +singleSlider_<?php echo $slider_id; ?>.pause_time,
                    transitionDuration: +singleSlider_<?php echo $slider_id; ?>.change_speed,
                    pauseOnHover: singleSlider_<?php echo $slider_id; ?>.pause_on_hover
                });
                break;
            case 'carousel1':
                var $pager = false,
                    $thumb = false;
                switch (singleSlider_<?php echo $slider_id; ?>.navigate_by) {
                    case 'dot':
                        $pager = true;
                        $thumb = false;
                        break;
                    case 'thumbnail':
                        $pager = true;
                        $thumb = true;
                        break;
                    case 'none':
                        $pager = false;
                        $thumb = false;
                        break;
                }

                jQuery('#slider_<?php echo $slider_id; ?>').RSlider({
                    item: +singleSlider_<?php echo $slider_id; ?>.itemscount,
                    pause: +singleSlider_<?php echo $slider_id; ?>.pause_time,
                    speed: +singleSlider_<?php echo $slider_id; ?>.change_speed,
                    pager: $pager,
                    gallery: $thumb,
                    pauseOnHover: +singleSlider_<?php echo $slider_id; ?>.pause_on_hover,
                    thumbItem: +hugeitSliderObj.thumb_count_slides,
                    controls: +hugeitSliderObj.show_arrows,
                    view: singleSlider_<?php echo $slider_id; ?>.view,
                    maxWidth: singleSlider_<?php echo $slider_id; ?>.width,
                    maxHeight: singleSlider_<?php echo $slider_id; ?>.height
                });
                break;
            case 'thumb_view':
                var $pager = false, $thumb = false;

                if (singleSlider_<?php echo $slider_id; ?>.pager === '1') {
                    $pager = true;
                    $thumb = true;
                }

                slider = jQuery('#slider_<?php echo $slider_id; ?>').RSlider({
                    item: 1,
                    view: singleSlider_<?php echo $slider_id; ?>.view,
                    maxWidth: singleSlider_<?php echo $slider_id; ?>.width,
                    maxHeight: singleSlider_<?php echo $slider_id; ?>.height,
                    mode: singleSlider_<?php echo $slider_id; ?>.mode,
                    speed: +singleSlider_<?php echo $slider_id; ?>.change_speed,
                    pauseOnHover: singleSlider_<?php echo $slider_id; ?>.pause_on_hover === '1',
                    pause: +singleSlider_<?php echo $slider_id; ?>.pause_time,
                    controls: singleSlider_<?php echo $slider_id; ?>.controls === '1',
                    fullscreen: singleSlider_<?php echo $slider_id; ?>.fullscreen === '1',
                    vertical: singleSlider_<?php echo $slider_id; ?>.vertical === '1',
                    sliderHeight: +singleSlider_<?php echo $slider_id; ?>.height,
                    vThumbWidth: +singleSlider_<?php echo $slider_id; ?>.vthumbwidth,
                    hThumbHeight: +singleSlider_<?php echo $slider_id; ?>.hthumbheight,
                    thumbItem: 5,
                    thumbMargin: +singleSlider_<?php echo $slider_id; ?>.thumbmargin,
                    thumbPosition: singleSlider_<?php echo $slider_id; ?>.thumbposition === '1',
                    thumbControls: singleSlider_<?php echo $slider_id; ?>.thumbcontrols === '1',
                    pager: $pager,
                    gallery: $thumb,
                    dragdrop: singleSlider_<?php echo $slider_id; ?>.dragdrop === '1',
                    swipe: singleSlider_<?php echo $slider_id; ?>.swipe === '1',
                    thumbdragdrop: singleSlider_<?php echo $slider_id; ?>.thumbdragdrop === '1',
                    thumbswipe: singleSlider_<?php echo $slider_id; ?>.thumbswipe === '1',
                    title: singleSlider_<?php echo $slider_id; ?>.titleonoff === '1',
                    description: singleSlider_<?php echo $slider_id; ?>.desconoff === '1',
                    titlesymbollimit: +singleSlider_<?php echo $slider_id; ?>.titlesymbollimit,
                    descsymbollimit: 96
                });
                break;
        }
    });

    jQuery(window).load(function () {
        jQuery('.slider_lightbox_<?php echo $slider_id; ?>').lightbox({
            slideAnimationType: singleSlider_<?php echo $slider_id; ?>.slide_effect,
            arrows: singleSlider_<?php echo $slider_id; ?>.arrows_style,
            openCloseType: singleSlider_<?php echo $slider_id; ?>.open_close_effect
        });
    });
</script>
