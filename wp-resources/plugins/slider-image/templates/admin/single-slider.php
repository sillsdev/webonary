<?php
/**
 * @var Hugeit_Slider_Slider $slider
 * @var array $all_sliders_id_name_pair
 * @var string $save_slider_nonce
 */
?>

<div class="wrap">
    <?php echo Hugeit_Slider_Template_Loader::render(HUGEIT_SLIDER_ADMIN_TEMPLATES_PATH . DIRECTORY_SEPARATOR . 'free-banner.php'); ?>
    <form action="admin.php?page=hugeit_slider&id=<?php echo $slider->get_id(); ?>" method="post" name="adminForm"
          id="adminForm">
        <div id="poststuff">
            <div id="slider-header">
                <ul id="sliders-list">
                    <?php
                    foreach ($all_sliders_id_name_pair as $pair) :
                        if ($pair->id != $slider->get_id()) :
                            $edit_page_safe_link = wp_nonce_url('admin.php?page=hugeit_slider&task=edit&id=' . $pair->id, 'edit_slider_' . $pair->id, 'hugeit_slider_edit_slider_nonce');
                            ?>
                            <li data-slider-id="<?php echo $pair->id; ?>">
                                <a href="#"
                                   onclick="window.location.href='<?php echo $edit_page_safe_link; ?>'"><?php echo $pair->name; ?></a>
                            </li>
                        <?php else : ?>
                            <li data-slider-id="<?php echo $pair->id; ?>" class="active">
                                <input class="text_area" type="text" name="name" id="name" maxlength="250"
                                       value="<?php echo $pair->name; ?>"/>
                            </li>
                        <?php endif;
                    endforeach;
                    ?>
                    <li class="add-new">
                        <a onclick="window.location.href='<?php echo $add_slider_safe_link; ?>'">+</a>
                    </li>
                </ul>
            </div>
            <div id="post-body" class="metabox-holder columns-2">
                <div class="save-result">
                    <p class="message"></p>
                </div>
                <div id="zoomed-image-section">
                    <img src="" alt="zoomed image"/>
                </div>
                <div id="post-body-content">
                    <?php add_thickbox(); ?>
                    <div id="hugeit-slider-add-video-popup" style="display: none;">
                        <?php echo Hugeit_Slider_Html_Loader::get_video_slide_popup_html(); ?>
                    </div>
                    <div id="hugeit-slider-add-post-popup" style="display: none;">
                        <?php echo Hugeit_Slider_Html_Loader::get_post_slide_popup_html(); ?>
                    </div>

                    <div id="post-body">
                        <div id="post-body-heading">
                            <h3><?php _e('Slides', 'hugeit-slider'); ?></h3>
                            <div class="image_preview_on_hover">
                                <label for="enable_preview_on_hover"><?php _e('Image preview on hover', 'hugeit-slider'); ?></label>
                                <input type="checkbox" id="enable_preview_on_hover" checked="checked"/>
                            </div>
                            <span class="wp-media-buttons-icon"></span>

                            <div id="hugeit_add_slide_buttons_wrapper">
                                <a href="#TB_inline?width=800&height=600&inlineId=hugeit-slider-add-post-popup"
                                   class="thickbox">
                                    <button type="button" id="hugeit_slider_add_post_slide_button"
                                            class="button button-primary button-large"><?php _e('Add Post Slide', 'hugeit-slider'); ?>
                                        <img src="<?php echo HUGEIT_SLIDER_ADMIN_IMAGES_URL . '/pro-logo.png'; ?>"
                                             alt="pro logo" class="hugeit_slider_pro_logo"/>
                                    </button>
                                </a>
                                <a href="#TB_inline?width=800&height=600&inlineId=hugeit-slider-add-video-popup"
                                   class="thickbox">
                                    <button style="<?php if ($slider->get_view() === 'carousel1') {
                                        echo 'display: none;';
                                    } ?>" type="button" id="hugeit_slider_add_video_slide_button"
                                            class="butbutton button-primary button-large">
                                        <?php _e('Add Video Slide', 'hugeit-slider'); ?>
                                        <img src="<?php echo HUGEIT_SLIDER_ADMIN_IMAGES_URL . '/pro-logo.png'; ?>"
                                             alt="pro logo" class="hugeit_slider_pro_logo"/>

                                    </button>
                                </a>
                                <button type="button" id="hugeit_slider_add_image_slide_button"
                                        class="button button-primary button-large"><?php _e('Add Image Slide', 'hugeit-slider'); ?></button>
                            </div>

                        </div>
                        <ul id="slides-list" class="images-list-block">
                            <?php
                            $has_background = true;

                            $slides = $slider->get_slides();
                            /**
                             * @var Hugeit_Slider_Slide_Image $slide
                             */
                            foreach ($slides as $order => $slide) :
                                switch ($slide->get_type()) :
                                    case 'image' : ?>
                                        <li data-type="<?php echo 'image'; ?>" class="slider-cell"
                                            data-slide-id="<?php echo $slide->get_id(); ?>"
                                            data-order="<?php echo $order; ?>">
                                            <?php echo Hugeit_Slider_Html_Loader::get_slide_html($slide); ?>
                                        </li>
                                        <?php
                                        break;
                                endswitch;
                            endforeach; ?>
                        </ul>
                    </div>

                </div>

                <!-- SIDEBAR -->
                <div id="postbox-container-1" class="postbox-container">
                    <div id="side-sortables" class="meta-box-sortables ui-sortable">
                        <div id="slider-options" class="postbox">
                            <div id="major-publishing-actions">
                                <div id="publishing-action">
                                    <span class="spinner" id="hugeit_slider_save_slider_spinner"></span>
                                    <input type="submit" value="<?php _e('Save Slider', 'hugeit-slider'); ?>"
                                           id="save-buttom" class="button button-primary button-large"
                                           data-nonce="<?php echo $save_slider_nonce; ?>">
                                </div>
                                <div class="clear"></div>
                            </div>
                            <h3 class="handle"><span><?php _e('Current Slider Options', 'hugeit-slider'); ?></span></h3>
                            <ul id="slider-unique-options-list">
                                <li style="<?php if ($slider->get_view() == 'carousel1') {
                                    echo 'display: none;';
                                } ?>" class="standart_view_display">
                                    <label for="width"><?php _e('Width', 'hugeit-slider'); ?></label>
                                    <input type="text" name="width" id="width"
                                           value="<?php echo $slider->get_width(); ?>" class="text_area"/>
                                </li>
                                <li style="<?php if ($slider->get_view() == 'carousel1') {
                                    echo 'display: none;';
                                } ?>" class="standart_view_display ">
                                    <label for="height"><?php _e('Height', 'hugeit-slider'); ?></label>
                                    <input type="text" name="height" id="height"
                                           value="<?php echo $slider->get_height(); ?>" class="text_area"/>
                                </li>
                                <li style="<?php if ($slider->get_view() !== 'carousel1') {
                                    echo 'display: none;';
                                } ?>" class="carousel_view_display">
                                    <label for="height"><?php _e('Items count', 'hugeit-slider'); ?></label>
                                    <input type="number" step="2" min="3" max="7" name="itemscount" id="itemscount"
                                           value="<?php echo $slider->get_itemscount(); ?>" class="text_area"/>
                                </li>
                                <li>
                                    <label for="view"><?php _e('Views', 'hugeit-slider'); ?></label>
                                    <select name="view" id="view">
                                        <option <?php if ($slider->get_view() == 'none') echo 'selected'; ?>
                                                value="none"><?php _e('Standart', 'hugeit-slider'); ?></option>
                                        <option <?php if ($slider->get_view() == 'carousel1') echo 'selected'; ?>
                                                value="carousel1"><?php _e('Carousel', 'hugeit-slider'); ?></option>
                                        <option <?php if ($slider->get_view() == 'thumb_view') echo 'selected'; ?>
                                                value="thumb_view"><?php _e('Thumbnail View', 'hugeit-slider'); ?></option>
                                    </select>
                                </li>
                                <li style="<?php if ($slider->get_view() !== 'thumb_view') {
                                    echo 'display: none;';
                                } ?>" class="thumb_view_display">
                                    <label for="mode"><?php _e('Effects', 'hugeit-slider'); ?></label>
                                    <select name="mode" id="mode">
                                        <option <?php if ($slider->get_mode() == 'slide') echo 'selected'; ?>
                                                value="slide"><?php _e('Slide', 'hugeit-slider'); ?></option>
                                        <option <?php if ($slider->get_mode() == 'fade') echo 'selected'; ?>
                                                value="fade"><?php _e('Fade', 'hugeit-slider'); ?></option>
                                    </select>
                                </li>
                                <li style="<?php if ($slider->get_view() != 'thumb_view') {
                                    echo 'display: none;';
                                } ?>" class="thumb_view_display">
                                    <label for="vertical"><?php _e('Vertical', 'hugeit-slider'); ?></label>
                                    <input type="checkbox" name="vertical" value="1"
                                           id="vertical" <?php if ($slider->get_vertical() == '1') echo 'checked="checked"'; ?> />
                                </li>
                                <li style="<?php if ($slider->get_view() != 'thumb_view') {
                                    echo 'display: none;';
                                } ?>" class="thumb_view_display">
                                    <label for="fullscreen"><?php _e('Fullscreen', 'hugeit-slider'); ?></label>
                                    <input type="checkbox" name="fullscreen" value="1"
                                           id="fullscreen" <?php if ($slider->get_fullscreen() == '1') echo 'checked="checked"'; ?> />
                                </li>
                                <li style="<?php if ($slider->get_view() != 'thumb_view') {
                                    echo 'display: none;';
                                } ?>" class="thumb_view_display">
                                    <label for="swipe"><?php _e('Swipe', 'hugeit-slider'); ?></label>
                                    <input type="checkbox" name="swipe" value="1"
                                           id="swipe" <?php if ($slider->get_swipe() == '1') echo 'checked="checked"'; ?> />
                                </li>
                                <li style="<?php if ($slider->get_view() != 'thumb_view') {
                                    echo 'display: none;';
                                } ?>" class="thumb_view_display">
                                    <label for="controls"><?php _e('Controls', 'hugeit-slider'); ?></label>
                                    <input type="checkbox" name="controls" value="1" id="controls" disabled/>
                                    <a class="probuttonlink" href="https://huge-it.com/slider/" target="_blank"><span
                                                style="color: red;font-size: 14px;"> PRO </span></a>
                                </li>
                                <li style="<?php if ($slider->get_view() != 'thumb_view') {
                                    echo 'display: none;';
                                } ?>" class="thumb_view_display">
                                    <label for="pager"><?php _e('Thumbnails', 'hugeit-slider'); ?></label>
                                    <input type="checkbox" name="pager" value="1" id="pager" disabled/>
                                    <a class="probuttonlink" href="https://huge-it.com/slider/" target="_blank"><span
                                                style="color: red;font-size: 14px;"> PRO </span></a>
                                </li>
                                <li style="<?php if ($slider->get_view() != 'thumb_view') {
                                    echo 'display: none;';
                                } ?>" class="thumb_view_display">
                                    <label for="vthumbwidth"><?php _e('Thumbs Width', 'hugeit-slider'); ?></label>
                                    <input type="text" name="vthumbwidth" id="vthumbwidth" value="100" class="text_area"
                                           disabled/>
                                    <a class="probuttonlink" href="https://huge-it.com/slider/" target="_blank"><span
                                                style="color: red;font-size: 14px;"> PRO </span></a>
                                </li>
                                <li style="<?php if ($slider->get_view() != 'thumb_view') {
                                    echo 'display: none;';
                                } ?>" class="thumb_view_display">
                                    <label for="hthumbheight"><?php _e('Thumbs Height', 'hugeit-slider'); ?></label>
                                    <input type="text" name="hthumbheight" id="hthumbheight" value="80"
                                           class="text_area" disabled/>
                                    <a class="probuttonlink" href="https://huge-it.com/slider/" target="_blank"><span
                                                style="color: red;font-size: 14px;"> PRO </span></a>
                                </li>
                                <li style="<?php if ($slider->get_view() != 'thumb_view') {
                                    echo 'display: none;';
                                } ?>" class="thumb_view_display">
                                    <label for="thumbitem"><?php _e('Thumbs Count', 'hugeit-slider'); ?></label>
                                    <input type="text" name="thumbitem" id="thumbitem" value="5" class="text_area"
                                           disabled/>
                                    <a class="probuttonlink" href="https://huge-it.com/slider/" target="_blank"><span
                                                style="color: red;font-size: 14px;"> PRO </span></a>
                                </li>
                                <li style="<?php if ($slider->get_view() != 'thumb_view') {
                                    echo 'display: none;';
                                } ?>" class="thumb_view_display">
                                    <label for="thumbmargin"><?php _e('Thumbs Margin', 'hugeit-slider'); ?></label>
                                    <input type="text" name="thumbmargin" id="thumbmargin" value="5" class="text_area"
                                           disabled/>
                                    <a class="probuttonlink" href="https://huge-it.com/slider/" target="_blank"><span
                                                style="color: red;font-size: 14px;"> PRO </span></a>
                                </li>
                                <li style="<?php if ($slider->get_view() != 'thumb_view') {
                                    echo 'display: none;';
                                } ?>" class="thumb_view_display">
                                    <label for="thumbcontrols"><?php _e('Thumb Controls', 'hugeit-slider'); ?></label>
                                    <input type="checkbox" name="thumbcontrols" value="1" id="thumbcontrols" disabled/>
                                    <a class="probuttonlink" href="https://huge-it.com/slider/" target="_blank"><span
                                                style="color: red;font-size: 14px;"> PRO </span></a>
                                </li>
                                <li style="<?php if ($slider->get_view() != 'thumb_view') {
                                    echo 'display: none;';
                                } ?>" class="thumb_view_display">
                                    <label for="dragdrop"><?php _e('Drag & Drop', 'hugeit-slider'); ?></label>
                                    <input type="checkbox" name="dragdrop" value="1" id="dragdrop" disabled/>
                                    <a class="probuttonlink" href="https://huge-it.com/slider/" target="_blank"><span
                                                style="color: red;font-size: 14px;"> PRO </span></a>
                                </li>
                                <li style="<?php if ($slider->get_view() != 'thumb_view') {
                                    echo 'display: none;';
                                } ?>" class="thumb_view_display">
                                    <label for="thumbdragdrop"><?php _e('Thumb Drag & Drop', 'hugeit-slider'); ?></label>
                                    <input type="checkbox" name="thumbdragdrop" value="1" id="thumbdragdrop" disabled/>
                                    <a class="probuttonlink" href="https://huge-it.com/slider/" target="_blank"><span
                                                style="color: red;font-size: 14px;"> PRO </span></a>
                                </li>
                                <li style="<?php if ($slider->get_view() != 'thumb_view') {
                                    echo 'display: none;';
                                } ?>" class="thumb_view_display">
                                    <label for="thumbswipe"><?php _e('Thumb Swipe', 'hugeit-slider'); ?></label>
                                    <input type="checkbox" name="thumbswipe" value="1" id="thumbswipe" disabled/>
                                    <a class="probuttonlink" href="https://huge-it.com/slider/" target="_blank"><span
                                                style="color: red;font-size: 14px;"> PRO </span></a>
                                </li>
                                <li style="<?php if ($slider->get_view() != 'thumb_view') {
                                    echo 'display: none;';
                                } ?>" class="thumb_view_display">
                                    <label for="titleonoff"><?php _e('Title On/Off', 'hugeit-slider'); ?></label>
                                    <input type="checkbox" name="titleonoff" value="1" id="titleonoff" disabled/>
                                    <a class="probuttonlink" href="https://huge-it.com/slider/" target="_blank"><span
                                                style="color: red;font-size: 14px;"> PRO </span></a>
                                </li>
                                <li style="<?php if ($slider->get_view() != 'thumb_view') {
                                    echo 'display: none;';
                                } ?>" class="thumb_view_display">
                                    <label for="desconoff"><?php _e('Description On/Off', 'hugeit-slider'); ?></label>
                                    <input type="checkbox" name="desconoff" value="1" id="desconoff" disabled/>
                                    <a class="probuttonlink" href="https://huge-it.com/slider/" target="_blank"><span
                                                style="color: red;font-size: 14px;"> PRO </span></a>
                                </li>
                                <li style="<?php if ($slider->get_view() != 'thumb_view') {
                                    echo 'display: none;';
                                } ?>" class="thumb_view_display">
                                    <label for="titlesymbollimit"><?php _e('Title Symbol Limit', 'hugeit-slider'); ?></label>
                                    <input type="text" name="titlesymbollimit" id="titlesymbollimit" value="20"
                                           class="text_area" disabled/>
                                </li>
                                <li style="<?php if ($slider->get_view() != 'thumb_view') {
                                    echo 'display: none;';
                                } ?>" class="thumb_view_display">
                                    <label for="descsymbollimit"><?php _e('Description Symbol Limit', 'hugeit-slider'); ?></label>
                                    <input type="text" name="descsymbollimit" id="descsymbollimit" value="96"
                                           class="text_area" disabled/>
                                </li>
                                <li style="<?php if ($slider->get_view() != 'thumb_view') {
                                    echo 'display: none;';
                                } ?>" class="thumb_view_display">
                                    <label for="thumbposition"><?php _e('Thumb Position', 'hugeit-slider'); ?></label>
                                    <select name="thumbposition" id="thumbposition" disabled>
                                        <option <?php if ($slider->get_thumbposition() == '0') echo 'selected'; ?>
                                                value="0">
                                            <?php _e('Bottom', 'hugeit-slider'); ?>
                                        </option>
                                        <option <?php if ($slider->get_thumbposition() == '1') echo 'selected'; ?>
                                                value="1">
                                            <?php _e('Top', 'hugeit-slider'); ?>
                                        </option>
                                    </select>
                                    <a class="probuttonlink" href="https://huge-it.com/slider/" target="_blank"><span
                                                style="color: red;font-size: 14px;"> PRO </span></a>
                                </li>
                                <li style="<?php if ($slider->get_view() != 'thumb_view') {
                                    echo 'display: none;';
                                } ?>" class="thumb_view_display">
                                    <label for="thumbbgc"><?php _e('Thumb Background Color', 'hugeit-slider'); ?></label>
                                    <select id="thumbbgc" disabled>
                                        <option selected>
                                            <?php _e('Light', 'hugeit-slider'); ?>
                                        </option>
                                    </select>
                                    <a class="probuttonlink" href="https://huge-it.com/slider/" target="_blank"><span
                                                style="color: red;font-size: 14px;"> PRO </span></a>
                                </li>
                                <li style="<?php if ($slider->get_view() == 'carousel1' || $slider->get_view() == 'thumb_view') {
                                    echo 'display: none;';
                                } ?>" class="standart_view_display_">
                                    <label for="effect"><?php _e('Effects', 'hugeit-slider'); ?></label>
                                    <select name="effect" id="effect">
                                        <option <?php if ($slider->get_effect() == 'none') $slider->get_width() ?>
                                                value="none"><?php _e('None', 'hugeit-slider'); ?></option>
                                        <option <?php if ($slider->get_effect() == 'cube_h') echo 'selected'; ?>
                                                value="cube_h"><?php _e('Cube Horizontal', 'hugeit-slider'); ?></option>
                                        <option <?php if ($slider->get_effect() == 'cube_v') echo 'selected'; ?>
                                                value="cube_v"><?php _e('Cube Vertical', 'hugeit-slider'); ?></option>
                                        <option <?php if ($slider->get_effect() == 'fade') echo 'selected'; ?>
                                                value="fade"><?php _e('Fade', 'hugeit-slider'); ?></option>
                                        <option <?php if ($slider->get_effect() == 'slice_h') echo 'selected'; ?>
                                                value="slice_h"><?php _e('Slice Horizontal', 'hugeit-slider'); ?></option>
                                        <option <?php if ($slider->get_effect() == 'slice_v') echo 'selected'; ?>
                                                value="slice_v"><?php _e('Slice Vertical', 'hugeit-slider'); ?></option>
                                        <option <?php if ($slider->get_effect() == 'slide_h') echo 'selected'; ?>
                                                value="slide_h"><?php _e('Slide Horizontal', 'hugeit-slider'); ?></option>
                                        <option <?php if ($slider->get_effect() == 'slide_v') echo 'selected'; ?>
                                                value="slide_v"><?php _e('Slide Vertical', 'hugeit-slider'); ?></option>
                                        <option <?php if ($slider->get_effect() == 'scale_out') echo 'selected'; ?>
                                                value="scale_out"><?php _e('Scale Out', 'hugeit-slider'); ?></option>
                                        <option <?php if ($slider->get_effect() == 'scale_in') echo 'selected'; ?>
                                                value="scale_in"><?php _e('Scale In', 'hugeit-slider'); ?></option>
                                        <option <?php if ($slider->get_effect() == 'block_scale') echo 'selected'; ?>
                                                value="block_scale"><?php _e('Block Scale', 'hugeit-slider'); ?></option>
                                        <option <?php if ($slider->get_effect() == 'kaleidoscope') echo 'selected'; ?>
                                                value="kaleidoscope"><?php _e('Kaleidoscope', 'hugeit-slider'); ?></option>
                                        <option <?php if ($slider->get_effect() == 'fan') echo 'selected'; ?>
                                                value="fan"><?php _e('Fan', 'hugeit-slider'); ?></option>
                                        <option <?php if ($slider->get_effect() == 'blind_h') echo 'selected'; ?>
                                                value="blind_h"><?php _e('Blind Horizontal', 'hugeit-slider'); ?></option>
                                        <option <?php if ($slider->get_effect() == 'blind_v') echo 'selected'; ?>
                                                value="blind_v"><?php _e('Blind Vertical', 'hugeit-slider'); ?></option>
                                        <option <?php if ($slider->get_effect() == 'random') echo 'selected'; ?>
                                                value="random"><?php _e('Random', 'hugeit-slider'); ?></option>
                                    </select>
                                </li>
                                <li>
                                    <label for="pause_time"><?php _e('Pause Time', 'hugeit-slider'); ?></label>
                                    <input type="text" name="pause_time" id="pause_time"
                                           value="<?php echo $slider->get_pause_time(); ?>" class="text_area"/>
                                </li>
                                <li>
                                    <label for="change_speed"><?php _e('Change Speed', 'hugeit-slider'); ?></label>
                                    <input type="text" name="change_speed" id="change_speed"
                                           value="<?php echo $slider->get_change_speed(); ?>" class="text_area"/>
                                </li>
                                <li>
                                    <label for="position"><?php _e('Slider Position', 'hugeit-slider'); ?></label>
                                    <select name="position" id="position">
                                        <option <?php if ($slider->get_position() === 'left') echo 'selected'; ?>
                                                value="left"><?php _e('Left', 'hugeit-slider'); ?></option>
                                        <option <?php if ($slider->get_position() === 'right') echo 'selected'; ?>
                                                value="right"><?php _e('Right', 'hugeit-slider'); ?></option>
                                        <option <?php if ($slider->get_position() === 'center') echo 'selected'; ?>
                                                value="center"><?php _e('Center', 'hugeit-slider'); ?></option>
                                    </select>
                                </li>
                                <li>
                                    <label for="show_loading_icon"><?php _e('Loading Icon', 'hugeit-slider'); ?></label>
                                    <select id="show_loading_icon" name="show_loading_icon">
                                        <option <?php if ($slider->get_show_loading_icon() == '1') echo 'selected'; ?>
                                                value="1"><?php _e('On', 'hugeit-slider'); ?></option>
                                        <option <?php if ($slider->get_show_loading_icon() == '0') echo 'selected'; ?>
                                                value="0"><?php _e('Off', 'hugeit-slider'); ?></option>
                                    </select>
                                </li>
                                <li style="<?php if ($slider->get_view() == 'thumb_view') {
                                    echo 'display: none;';
                                } ?>">
                                    <label for="navigate_by"><?php _e('Navigate By', 'hugeit-slider'); ?></label>
                                    <select id="navigate_by" name="navigate_by">
                                        <option <?php if ($slider->get_navigate_by() == 'dot') echo 'selected'; ?>
                                                value="dot"><?php _e('Dots', 'hugeit-slider'); ?></option>
                                        <option <?php if ($slider->get_navigate_by() == 'thumbnail') echo 'selected'; ?>
                                                value="thumbnail"><?php _e('Thumbnails', 'hugeit-slider'); ?></option>
                                        <option <?php if ($slider->get_navigate_by() == 'none') echo 'selected'; ?>
                                                value="none"><?php _e('No Navigation', 'hugeit-slider'); ?></option>
                                    </select>
                                </li>
                                <li>
                                    <label for="pause_on_hover"><?php _e('Pause on Hover', 'hugeit-slider'); ?></label>
                                    <input type="checkbox" name="pause_on_hover" value="1"
                                           id="pause_on_hover" <?php if ($slider->get_pause_on_hover() == '1') echo 'checked="checked"'; ?> />
                                </li>
                                <li style="<?php if ($slider->get_view() === 'carousel1') {
                                    echo 'display: none;';
                                } ?>">
                                    <label for="video_autoplay">Video Autoplay</label>
                                    <input type="checkbox" name="video_autoplay" value="1" id="video_autoplay"
                                           disabled="disabled"/>
                                    <a class="probuttonlink" href="https://huge-it.com/slider/" target="_blank"><span
                                                style="color: red;font-size: 14px;"> PRO </span></a>
                                </li>
                                <!--###########RANDOM UPDATE##############-->
                                <li>
                                    <label for="random"><?php _e('Random', 'hugeit-slider'); ?></label>
                                    <input type="checkbox" name="random" value="1"
                                           id="random" <?php if ($slider->get_random() == '1') echo 'checked="checked"'; ?> />
                                </li>
                                <li>
                                    <label for="lightbox"><?php _e('Lightbox', 'hugeit-slider'); ?></label>
                                    <input type="checkbox" name="lightbox" value="1"
                                           id="lightbox" <?php if ($slider->get_lightbox() == '1') echo 'checked="checked"'; ?> />
                                </li>
                                <li style="<?php if ($slider->get_lightbox() == 'off') {
                                    echo 'display: none;';
                                } ?>">
                                    <label for="slide_effect"><?php _e('Slide Effect', 'hugeit-slider'); ?></label>
                                    <select id="slide_effect" name="slide_effect">
                                        <option <?php if ($slider->get_slide_effect() == 'effect_1') echo 'selected'; ?>
                                                value="effect_1"><?php _e('Effect 1', 'hugeit-slider'); ?></option>
                                        <option <?php if ($slider->get_slide_effect() == 'effect_2') echo 'selected'; ?>
                                                value="effect_2"><?php _e('Effect 2', 'hugeit-slider'); ?></option>
                                        <option <?php if ($slider->get_slide_effect() == 'effect_3') echo 'selected'; ?>
                                                value="effect_3"><?php _e('Effect 3', 'hugeit-slider'); ?></option>
                                        <option <?php if ($slider->get_slide_effect() == 'effect_4') echo 'selected'; ?>
                                                value="effect_4"><?php _e('Effect 4', 'hugeit-slider'); ?></option>
                                        <option <?php if ($slider->get_slide_effect() == 'effect_5') echo 'selected'; ?>
                                                value="effect_5"><?php _e('Effect 5', 'hugeit-slider'); ?></option>
                                        <option <?php if ($slider->get_slide_effect() == 'effect_6') echo 'selected'; ?>
                                                value="effect_6"><?php _e('Effect 6', 'hugeit-slider'); ?></option>
                                        <option <?php if ($slider->get_slide_effect() == 'effect_7') echo 'selected'; ?>
                                                value="effect_7"><?php _e('Effect 7', 'hugeit-slider'); ?></option>
                                        <option <?php if ($slider->get_slide_effect() == 'effect_8') echo 'selected'; ?>
                                                value="effect_8"><?php _e('Effect 8', 'hugeit-slider'); ?></option>
                                        <option <?php if ($slider->get_slide_effect() == 'effect_9') echo 'selected'; ?>
                                                value="effect_9"><?php _e('Effect 9', 'hugeit-slider'); ?></option>
                                        <option <?php if ($slider->get_slide_effect() == 'effect_10') echo 'selected'; ?>
                                                value="effect_10"><?php _e('Effect 10', 'hugeit-slider'); ?></option>
                                    </select>
                                </li>
                                <li style="<?php if ($slider->get_lightbox() == 'off') {
                                    echo 'display: none;';
                                } ?>">
                                    <label for="open_close_effect"><?php _e('Open/Close Effect', 'hugeit-slider'); ?></label>
                                    <select id="open_close_effect" name="open_close_effect">
                                        <option <?php if ($slider->get_open_close_effect() == 'none') echo 'selected'; ?>
                                                value="none"><?php _e('None', 'hugeit-slider'); ?></option>
                                        <option <?php if ($slider->get_open_close_effect() == 'unfold') echo 'selected'; ?>
                                                value="unfold"><?php _e('Unfold', 'hugeit-slider'); ?></option>
                                        <option <?php if ($slider->get_open_close_effect() == 'unfold_r') echo 'selected'; ?>
                                                value="unfold_r"><?php _e('Unfold R', 'hugeit-slider'); ?></option>
                                        <option <?php if ($slider->get_open_close_effect() == 'blowup') echo 'selected'; ?>
                                                value="blowup"><?php _e('BlowUp', 'hugeit-slider'); ?></option>
                                        <option <?php if ($slider->get_open_close_effect() == 'blowup_r') echo 'selected'; ?>
                                                value="blowup_r"><?php _e('BlowUp R', 'hugeit-slider'); ?></option>
                                        <option <?php if ($slider->get_open_close_effect() == 'roadrunner') echo 'selected'; ?>
                                                value="roadrunner"><?php _e('RoadRunner', 'hugeit-slider'); ?></option>
                                        <option <?php if ($slider->get_open_close_effect() == 'roadrunner_r') echo 'selected'; ?>
                                                value="roadrunner_r"><?php _e('RoadRunner R', 'hugeit-slider'); ?></option>
                                        <option <?php if ($slider->get_open_close_effect() == 'runner') echo 'selected'; ?>
                                                value="runner"><?php _e('Runner', 'hugeit-slider'); ?></option>
                                        <option <?php if ($slider->get_open_close_effect() == 'runner_r') echo 'selected'; ?>
                                                value="runner_r"><?php _e('Runner R', 'hugeit-slider'); ?></option>
                                        <option <?php if ($slider->get_open_close_effect() == 'rotate') echo 'selected'; ?>
                                                value="rotate"><?php _e('Rotate', 'hugeit-slider'); ?></option>
                                        <option <?php if ($slider->get_open_close_effect() == 'rotate_r') echo 'selected'; ?>
                                                value="rotate_r"><?php _e('Rotate R', 'hugeit-slider'); ?></option>
                                    </select>
                                </li>
                                <li style="<?php if ($slider->get_lightbox() == 'off') {
                                    echo 'display: none;';
                                } ?>">
                                    <label for="arrows_style"><?php _e('Arrows Style', 'hugeit-slider'); ?></label>
                                    <select id="arrows_style" name="arrows_style" style="width: 100px;">
                                        <option <?php if ($slider->get_arrows_style() == 'arrows_1') echo 'selected'; ?>
                                                value="arrows_1"><?php _e('Arrows 1', 'hugeit-slider'); ?></option>
                                        <option <?php if ($slider->get_arrows_style() == 'arrows_2') echo 'selected'; ?>
                                                value="arrows_2"><?php _e('Arrows 2', 'hugeit-slider'); ?></option>
                                        <option <?php if ($slider->get_arrows_style() == 'arrows_3') echo 'selected'; ?>
                                                value="arrows_3"><?php _e('Arrows 3', 'hugeit-slider'); ?></option>
                                        <option <?php if ($slider->get_arrows_style() == 'arrows_4') echo 'selected'; ?>
                                                value="arrows_4"><?php _e('Arrows 4', 'hugeit-slider'); ?></option>
                                        <option <?php if ($slider->get_arrows_style() == 'arrows_5') echo 'selected'; ?>
                                                value="arrows_5"><?php _e('Arrows 5', 'hugeit-slider'); ?></option>
                                        <option <?php if ($slider->get_arrows_style() == 'arrows_6') echo 'selected'; ?>
                                                value="arrows_6"><?php _e('Arrows 6', 'hugeit-slider'); ?></option>
                                    </select>
                                    <div id="view-image_frame">
                                        <ul>
                                            <li data-id="arrows_1" <?php if ($slider->get_arrows_style() == 'arrows_1') echo 'class="active"'; ?>>
                                                <img src="<?php echo HUGEIT_SLIDER_ADMIN_IMAGES_URL . '/arrows/slightbox_arrows1.png' ?>">
                                            </li>
                                        </ul>
                                        <ul>
                                            <li data-id="arrows_2" <?php if ($slider->get_arrows_style() == 'arrows_2') echo 'class="active"'; ?>>
                                                <img src="<?php echo HUGEIT_SLIDER_ADMIN_IMAGES_URL . '/arrows/slightbox_arrows2.png' ?>">
                                            </li>
                                        </ul>
                                        <ul>
                                            <li data-id="arrows_3" <?php if ($slider->get_arrows_style() == 'arrows_3') echo 'class="active"'; ?>>
                                                <img src="<?php echo HUGEIT_SLIDER_ADMIN_IMAGES_URL . '/arrows/slightbox_arrows3.png' ?>">
                                            </li>
                                        </ul>
                                        <ul>
                                            <li data-id="arrows_4" <?php if ($slider->get_arrows_style() == 'arrows_4') echo 'class="active"'; ?>>
                                                <img src="<?php echo HUGEIT_SLIDER_ADMIN_IMAGES_URL . '/arrows/slightbox_arrows4.png' ?>">
                                            </li>
                                        </ul>
                                        <ul>
                                            <li data-id="arrows_5" <?php if ($slider->get_arrows_style() == 'arrows_5') echo 'class="active"'; ?>>
                                                <img src="<?php echo HUGEIT_SLIDER_ADMIN_IMAGES_URL . '/arrows/slightbox_arrows5.png' ?>">
                                            </li>
                                        </ul>
                                        <ul>
                                            <li data-id="arrows_6" <?php if ($slider->get_arrows_style() == 'arrows_6') echo 'class="active"'; ?>>
                                                <img src="<?php echo HUGEIT_SLIDER_ADMIN_IMAGES_URL . '/arrows/slightbox_arrows6.png' ?>">
                                            </li>
                                        </ul>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div id="slider-shortcode-box" class="postbox shortcode ms-toggle">
                        <h3 class="hndle"><span><?php _e('Shortcodes', 'hugeit-slider'); ?></span></h3>
                        <div class="inside">
                            <ul>
                                <li rel="tab-1" class="selected">
                                    <h4><?php _e('Shortcode for posts/pages/plugins', 'hugeit-slider'); ?></h4>
                                    <p><?php _e('Copy &amp; paste the shortcode directly into any WordPress post or page', 'hugeit-slider'); ?>
                                        .</p>
                                    <textarea class="full"
                                              readonly="readonly">[huge_it_slider id="<?php echo $slider->get_id(); ?>
                                        "]</textarea>
                                </li>
                                <li rel="tab-2">
                                    <h4><?php _e('Shortcode for templates/themes', 'hugeit-slider'); ?></h4>
                                    <p><?php _e('Copy &amp; paste this code into a template file to include the slideshow within your theme', 'hugeit-slider'); ?>
                                        .</p>
                                    <textarea class="full" readonly="readonly">&lt;?php echo do_shortcode("[huge_it_slider id='<?php echo $slider->get_id(); ?>
                                        ']"); ?&gt;</textarea>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>