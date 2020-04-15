<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (function_exists('current_user_can')) {
    if (!current_user_can('manage_options')) {
        die('Access Denied');
    }
} else {
    die('Access Denied');
}

?>

<div class="wrap">
    <?php echo Hugeit_Slider_Template_Loader::render(HUGEIT_SLIDER_ADMIN_TEMPLATES_PATH . DIRECTORY_SEPARATOR . 'free-banner.php'); ?>
    <div id="poststuff">
        <div id="post-body-content" class="slider-options">
            <div id="post-body-heading">
                <h3><?php _e('Advanced Options (PRO)', 'hugeit-slider'); ?></h3>
                <a onclick="document.getElementById('adminForm').submit()"
                   class="save-slider-options button-primary"><?php _e('Save', 'hugeit-slider'); ?></a>
                <!--TODO: review-->
                <script>
                    jQuery(document).ready(function () {

                        jQuery("#slideup<?php echo isset($key) ? $key : ''; ?>").click(function () {
                            window.parent.uploadID = jQuery(this).prev('input');
                            formfield = jQuery('.upload').attr('name');
                            tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
                            return false;
                        });
                        window.send_to_editor = function (html) {
                            var imgurl = jQuery('img', html).attr('src');
                            window.parent.uploadID.val(imgurl);
                            tb_remove();
                        };
                    });

                </script>
            </div>
            <div id="slider-options-list-free">
                <form action="admin.php?page=hugeit_slider_general_options&task=save" method="post" id="adminForm"
                      name="adminForm">
                    <div class="options-block" id="options-block-share">
                        <h3>Social Sharing</h3>
                        <div class="has-background">
                            <label for="slider_share_buttons"><?php _e('Share buttons', 'hugeit-slider'); ?>
                                <div class="help">?
                                    <div class="help-block">
                                        <span class="pnt"></span>
                                        <p><?php _e('Enable/disable share buttons.', 'hugeit-slider'); ?></p>
                                    </div>
                                </div>
                            </label>
                            <input type="hidden" value="0" name="params[share_buttons]"/>
                            <input type="checkbox"
                                   id="slider_share_buttons" <?php if (Hugeit_Slider_Options::get_share_buttons()) echo 'checked="checked"'; ?>
                                   name="params[share_buttons]" value="1"/>
                        </div>
                        <div>
                            <label for="slider_share_buttons_style"><?php _e('Share buttons style', 'hugeit-slider'); ?>
                                <div class="help">?
                                    <div class="help-block">
                                        <span class="pnt"></span>
                                        <p><?php _e('Circle or square style.', 'hugeit-slider'); ?></p>
                                    </div>
                                </div>
                            </label>
                            <select id="slider_share_buttons_style" name="params[share_buttons_style]">
                                <option <?php if (Hugeit_Slider_Options::get_share_buttons_style() === 'circle') echo 'selected'; ?>
                                        value="circle"><?php _e('Circle', 'hugeit-slider'); ?></option>
                                <option <?php if (Hugeit_Slider_Options::get_share_buttons_style() === 'square') echo 'selected'; ?>
                                        value="square"><?php _e('Square', 'hugeit-slider'); ?></option>
                            </select>
                        </div>
                        <div class="has-background">
                            <label for="slider_share_buttons_facebook"><?php _e('Facebook', 'hugeit-slider'); ?>
                                <div class="help">?
                                    <div class="help-block">
                                        <span class="pnt"></span>
                                        <p><?php _e('Enable/disable Facebook share.', 'hugeit-slider'); ?></p>
                                    </div>
                                </div>
                            </label>
                            <input type="hidden" value="0" name="params[share_buttons_facebook]"/>
                            <input type="checkbox"
                                   id="slider_share_buttons_facebook" <?php if (Hugeit_Slider_Options::get_share_buttons_facebook()) echo 'checked="checked"'; ?>
                                   name="params[share_buttons_facebook]" value="1"/>
                        </div>
                        <div>
                            <label for="slider_share_buttons_twitter"><?php _e('Twitter', 'hugeit-slider'); ?>
                                <div class="help">?
                                    <div class="help-block">
                                        <span class="pnt"></span>
                                        <p><?php _e('Enable/disable Twitter share.', 'hugeit-slider'); ?></p>
                                    </div>
                                </div>
                            </label>
                            <input type="hidden" value="0" name="params[share_buttons_twitter]"/>
                            <input type="checkbox"
                                   id="slider_share_buttons_twitter" <?php if (Hugeit_Slider_Options::get_share_buttons_twitter()) echo 'checked="checked"'; ?>
                                   name="params[share_buttons_twitter]" value="1"/>
                        </div>
                        <div class="has-background">
                            <label for="slider_share_buttons_gp"><?php _e('Google Plus', 'hugeit-slider'); ?>
                                <div class="help">?
                                    <div class="help-block">
                                        <span class="pnt"></span>
                                        <p><?php _e('Enable/disable Google Plus share.', 'hugeit-slider'); ?></p>
                                    </div>
                                </div>
                            </label>
                            <input type="hidden" value="0" name="params[share_buttons_gp]"/>
                            <input type="checkbox"
                                   id="slider_share_buttons_gp" <?php if (Hugeit_Slider_Options::get_share_buttons_gp()) echo 'checked="checked"'; ?>
                                   name="params[share_buttons_gp]" value="1"/>
                        </div>
                        <div class="partly_overlay hugeit_slider_black_overlay">
                            <div class="">
                                <div>
                                    <label for="slider_share_buttons_pinterest"><?php _e('Pinterest', 'hugeit-slider'); ?>
                                        <div class="help">?
                                            <div class="help-block">
                                                <span class="pnt"></span>
                                                <p><?php _e('Enable/disable Pinterest share.', 'hugeit-slider'); ?></p>
                                            </div>
                                        </div>
                                    </label>
                                    <input type="hidden" value="0" name=""/>
                                    <input type="checkbox"
                                           id="slider_share_buttons_pinterest" <?php if (Hugeit_Slider_Options::get_share_buttons_pinterest()) echo 'checked="checked"'; ?>
                                           name="" value="1"/>
                                </div>
                            </div>
                            <div class="has-background">
                                <label for="slider_share_buttons_linkedin"><?php _e('Linkedin', 'hugeit-slider'); ?>
                                    <div class="help">?
                                        <div class="help-block">
                                            <span class="pnt"></span>
                                            <p><?php _e('Enable/disable Linkedin share.', 'hugeit-slider'); ?></p>
                                        </div>
                                    </div>
                                </label>
                                <input type="hidden" value="0" name=""/>
                                <input type="checkbox"
                                       id="slider_share_buttons_linkedin" <?php if (Hugeit_Slider_Options::get_share_buttons_linkedin()) echo 'checked="checked"'; ?>
                                       name="" value="1"/>
                            </div>
                            <div class="">
                                <div>
                                    <label for="slider_share_buttons_tumblr"><?php _e('Tumblr', 'hugeit-slider'); ?>
                                        <div class="help">?
                                            <div class="help-block">
                                                <span class="pnt"></span>
                                                <p><?php _e('Enable/disable Tumblr share.', 'hugeit-slider'); ?></p>
                                            </div>
                                        </div>
                                    </label>
                                    <input type="hidden" value="0" name=""/>
                                    <input type="checkbox"
                                           id="slider_share_buttons_tumblr" <?php if (Hugeit_Slider_Options::get_share_buttons_tumblr()) echo 'checked="checked"'; ?>
                                           name="" value="1"/>
                                </div>
                            </div>
                            <div class=" has-background">
                                <label for="slider_share_buttons_hover_style"><?php _e('Share buttons 15 hover styles', 'hugeit-slider'); ?>
                                    <div class="help">?
                                        <div class="help-block">
                                            <span class="pnt"></span>
                                            <p><?php _e('Choose how to behave the hover effect in buttons.', 'hugeit-slider'); ?></p>
                                        </div>
                                    </div>
                                </label>
                                <select id="slider_share_buttons_hover_style" name="">
                                    <option <?php if (Hugeit_Slider_Options::get_share_buttons_hover_style() === '0') echo 'selected'; ?>
                                            value="0"><?php _e('None', 'hugeit-slider'); ?></option>
                                </select>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="hugeit_slider_black_overlay">
                <div id="slider-options-list">
                    <form action="#" method="post" id="adminForm" name="adminForm">
                        <div class="options-block" id="options-block-slider">
                            <h3><?php _e('Slider Styles', 'hugeit-slider'); ?></h3>
                            <div class="has-background">
                                <label for="slider_crop_image"><?php _e('Image Behaviour', 'hugeit-slider'); ?>
                                    <div class="help">?
                                        <div class="help-block">
                                            <span class="pnt"></span>
                                            <p><?php _e('Choose how to behave the image in slider', 'hugeit-slider'); ?></p>
                                        </div>
                                    </div>
                                </label>
                                <select id="slider_crop_image">
                                    <option <?php if (Hugeit_Slider_Options::get_crop_image() === 'stretch') echo 'selected'; ?>
                                            value="stretch"><?php _e('Stretch', 'hugeit-slider'); ?></option>
                                    <option <?php if (Hugeit_Slider_Options::get_crop_image() === 'fill') echo 'selected'; ?>
                                            value="fill"><?php _e('Fill', 'hugeit-slider'); ?></option>
                                </select>
                            </div>

                            <div>
                                <label for="slider_slider_background_color_transparency"><?php _e('Slider Background Color Opacity', 'hugeit-slider'); ?>
                                    <div class="help">?
                                        <div class="help-block">
                                            <span class="pnt"></span>
                                            <p><?php _e('Set the color transparency for background of the slider', 'hugeit-slider'); ?></p>
                                        </div>
                                    </div>
                                </label>
                                <div class="slider-container">
                                    <input id="slider_slider_background_color_transparency" data-slider-highlight="true"
                                           data-slider-values="0,10,20,30,40,50,60,70,80,90,100" type="text"
                                           data-slider="true"
                                           value="<?php echo 100 * Hugeit_Slider_Options::get_slider_background_color_transparency(); ?>"/>
                                    <span><?php echo 100 * Hugeit_Slider_Options::get_slider_background_color_transparency(); ?>
                                        %</span>
                                </div>
                            </div>

                            <div class="has-background">
                                <label for="slider_slider_background_color"><?php _e('Slider Background Color', 'hugeit-slider'); ?>
                                    <div class="help">?
                                        <div class="help-block">
                                            <span class="pnt"></span>
                                            <p><?php _e('Set the color for background of the slider', 'hugeit-slider'); ?></p>
                                        </div>
                                    </div>
                                </label>
                                <input type="text" class="color" id="slider_slider_background_color"
                                       value="#<?php echo Hugeit_Slider_Options::get_slider_background_color(); ?>"
                                       size="10">
                            </div>

                            <div class="">
                                <label for="slider_slideshow_border_size"><?php _e('Slider Border Size', 'hugeit-slider'); ?>
                                    <div class="help">?
                                        <div class="help-block">
                                            <span class="pnt"></span>
                                            <p><?php _e('Set the border for the slideshow', 'hugeit-slider'); ?></p>
                                        </div>
                                    </div>
                                </label>
                                <input type="text" id="slider_slideshow_border_size"
                                       value="<?php echo Hugeit_Slider_Options::get_slideshow_border_size(); ?>"
                                       class="text"/>
                            </div>
                            <div class="has-background">
                                <label for="slider_slideshow_border_color"><?php _e('Slider Border Color', 'hugeit-slider'); ?>
                                    <div class="help">?
                                        <div class="help-block">
                                            <span class="pnt"></span>
                                            <p><?php _e('Select the border color for the slideshow', 'hugeit-slider'); ?></p>
                                        </div>
                                    </div>
                                </label>
                                <input type="text" class="color" id="slider_slideshow_border_color"
                                       value="#<?php echo Hugeit_Slider_Options::get_slideshow_border_color(); ?>"
                                       size="10">
                            </div>
                            <div class="">
                                <label for="slider_slideshow_border_radius"><?php _e('Slider Border Radius', 'hugeit-slider'); ?>
                                    <div class="help">?
                                        <div class="help-block">
                                            <span class="pnt"></span>
                                            <p><?php _e('Set the border radius for the slideshow', 'hugeit-slider'); ?></p>
                                        </div>
                                    </div>
                                </label>
                                <input type="text" id="slider_slideshow_border_radius"
                                       value="<?php echo Hugeit_Slider_Options::get_slideshow_border_radius(); ?>"
                                       class="text"/>
                            </div>
                            <div class="has-background">
                                <label for="slider_load_icon"><?php _e('Slider Loading Image', 'hugeit-slider'); ?>
                                    <div class="help">?
                                        <div class="help-block">
                                            <span class="pnt"></span>
                                            <p><?php _e('Set the loading icon for the slideshow', 'hugeit-slider'); ?></p>
                                        </div>
                                    </div>
                                </label>
                                <ul id="slider-loading-icon">
                                    <?php for ($i = 1; $i < 7; ++$i) : ?>
                                        <li <?php if (Hugeit_Slider_Options::get_loading_icon_type() == $i) echo ' class="act"'; ?>>
                                            <label for="params[loading_icon_type]_<?php echo $i; ?>"
                                                   class="hugeit-slider-loading-icon-options">
                                                <div class="image-block-icon">
                                                    <img src="<?php echo HUGEIT_SLIDER_FRONT_IMAGES_URL . '/loading/loading' . $i . '.gif'; ?>"
                                                         alt=""/>
                                                </div>
                                            </label>
                                            <input type="radio" id="params[loading_icon_type]_<?php echo $i; ?>"
                                                   value="<?php echo $i; ?>" <?php if (Hugeit_Slider_Options::get_loading_icon_type() == $i) echo 'checked="checked"'; ?>>
                                        </li>
                                    <?php endfor; ?>
                                </ul>
                            </div>
                        </div>
                        <div class="hugeit_slider_black_overlay"></div>
                        <div class="options-block" id="options-block-title" style="margin-top: -450px;">
                            <div class="hugeit_slider_black_overlay_title"></div>
                            <h3>Title Styles</h3>
                            <div class="has-background">
                                <label for="title-container-width"><?php _e('Title Width', 'hugeit-slider'); ?>
                                    <div class="help">?
                                        <div class="help-block">
                                            <span class="pnt"></span>
                                            <p><?php _e('Select the width for the title', 'hugeit-slider'); ?></p>
                                        </div>
                                    </div>
                                </label>
                                <div class="slider-container">
                                    <input id="title-container-width" data-slider-range="1,100" type="text"
                                           data-slider="true" data-slider-highlight="true"
                                           value="<?php echo Hugeit_Slider_Options::get_title_width(); ?>"/>
                                    <span><?php echo Hugeit_Slider_Options::get_title_width(); ?>%</span>
                                </div>
                                <div style="clear:both;"></div>
                            </div>
                            <div>
                                <label for="slider_title_has_margin"><?php _e('Title Has Margin', 'hugeit-slider'); ?>
                                    <div class="help">?
                                        <div class="help-block">
                                            <span class="pnt"></span>
                                            <p><?php _e('Choose the margin level for title', 'hugeit-slider'); ?></p>
                                        </div>
                                    </div>
                                </label>
                                <input type="hidden" value="0"/>
                                <input type="checkbox"
                                       id="slider_title_has_margin" <?php if (Hugeit_Slider_Options::get_title_has_margin()) echo 'checked="checked"'; ?>
                                       value="1"/>
                            </div>
                            <div class="has-background">
                                <label for="slider_title_font_size"><?php _e('Title Font Size', 'hugeit-slider'); ?>
                                    <div class="help">?
                                        <div class="help-block">
                                            <span class="pnt"></span>
                                            <p><?php _e('Specify the font size for the image title', 'hugeit-slider'); ?></p>
                                        </div>
                                    </div>
                                </label>
                                <input type="text" id="slider_title_font_size"
                                       value="<?php echo Hugeit_Slider_Options::get_title_font_size(); ?>"
                                       class="text"/>
                                <span><?php _e('px', 'hugeit-slider'); ?></span>
                            </div>
                            <div>
                                <label for="slider_title_color"><?php _e('Title Text Color', 'hugeit-slider'); ?>
                                    <div class="help">?
                                        <div class="help-block">
                                            <span class="pnt"></span>
                                            <p><?php _e('Set the color for the title', 'hugeit-slider'); ?></p>
                                        </div>
                                    </div>
                                </label>
                                <input type="text" class="color" id="slider_title_color"
                                       value="#<?php echo Hugeit_Slider_Options::get_title_color(); ?>" size="10"/>
                            </div>
                            <div class="has-background">
                                <label for="slider_title_text_align"><?php _e('Title Text Align', 'hugeit-slider'); ?>
                                    <div class="help">?
                                        <div class="help-block">
                                            <span class="pnt"></span>
                                            <p><?php _e('Choose where to place the title', 'hugeit-slider'); ?></p>
                                        </div>
                                    </div>
                                </label>
                                <select id="slider_title_text_align">
                                    <option <?php if (Hugeit_Slider_Options::get_title_text_align() == 'justify') echo 'justify'; ?>
                                            value="justify"><?php _e('Full width', 'hugeit-slider'); ?></option>
                                    <option <?php if (Hugeit_Slider_Options::get_title_text_align() == 'center') echo 'selected'; ?>
                                            value="center"><?php _e('Center', 'hugeit-slider'); ?></option>
                                    <option <?php if (Hugeit_Slider_Options::get_title_text_align() == 'left') echo 'selected'; ?>
                                            value="left"><?php _e('Left', 'hugeit-slider'); ?></option>
                                    <option <?php if (Hugeit_Slider_Options::get_title_text_align() == 'right') echo 'selected'; ?>
                                            value="right"><?php _e('Right', 'hugeit-slider'); ?></option>
                                </select>
                            </div>
                            <div>
                                <label for="title-background-transparency"><?php _e('Title Background Opacity', 'hugeit-slider'); ?>
                                    <div class="help">?
                                        <div class="help-block">
                                            <span class="pnt"></span>
                                            <p><?php _e('Set the level of transparency for the title', 'hugeit-slider'); ?></p>
                                        </div>
                                    </div>
                                </label>
                                <div class="slider-container">
                                    <input id="title-background-transparency" data-slider-highlight="true"
                                           data-slider-values="0,10,20,30,40,50,60,70,80,90,100" type="text"
                                           data-slider="true"
                                           value="<?php echo 100 * Hugeit_Slider_Options::get_title_background_transparency(); ?>"/>
                                    <span><?php echo 100 * Hugeit_Slider_Options::get_title_background_transparency(); ?>
                                        %</span>
                                </div>
                            </div>
                            <div class="has-background">
                                <label for="slider_title_background_color"><?php _e('Title Background color', 'hugeit-slider'); ?>
                                    <div class="help">?
                                        <div class="help-block">
                                            <span class="pnt"></span>
                                            <p><?php _e('Choose the color for the cell containing the title', 'hugeit-slider'); ?></p>
                                        </div>
                                    </div>
                                </label>
                                <input type="text" class="color" id="slider_title_background_color"
                                       value="#<?php echo Hugeit_Slider_Options::get_title_background_color(); ?>"
                                       size="10"/>
                            </div>
                            <div>
                                <label for="slider_title_border_size"><?php _e('Title Border Size', 'hugeit-slider'); ?>
                                    <div class="help">?
                                        <div class="help-block">
                                            <span class="pnt"></span>
                                            <p><?php _e('Set the border size for the title', 'hugeit-slider'); ?></p>
                                        </div>
                                    </div>
                                </label>
                                <input type="text" id="slider_title_border_size"
                                       value="<?php echo Hugeit_Slider_Options::get_title_border_size(); ?>"
                                       class="text"/>
                                <span><?php _e('px', 'hugeit-slider'); ?></span>
                            </div>
                            <div class="has-background">
                                <label for="slider_title_border_color"><?php _e('Title Border Color', 'hugeit-slider'); ?>
                                    <div class="help">?
                                        <div class="help-block">
                                            <span class="pnt"></span>
                                            <p><?php _e('Select the border color for the title', 'hugeit-slider'); ?></p>
                                        </div>
                                    </div>
                                </label>
                                <input type="text" class="color" id="slider_title_border_color"
                                       value="#<?php echo Hugeit_Slider_Options::get_title_border_color(); ?>"
                                       size="10">
                            </div>
                            <div>
                                <label for="slider_title_border_radius"><?php _e('Title Border Radius', 'hugeit-slider'); ?>
                                    <div class="help">?
                                        <div class="help-block">
                                            <span class="pnt"></span>
                                            <p><?php _e('Set the border radius for the title', 'hugeit-slider'); ?></p>
                                        </div>
                                    </div>
                                </label>
                                <input type="text" id="slider_title_border_radius"
                                       value="<?php echo Hugeit_Slider_Options::get_title_border_radius(); ?>"
                                       class="text"/>
                                <span><?php _e('px', 'hugeit-slider'); ?></span>
                            </div>
                            <div class="has-height has-background">
                                <label for=""><?php _e('Title Position', 'hugeit-slider'); ?>
                                    <div class="help">?
                                        <div class="help-block">
                                            <span class="pnt"></span>
                                            <p><?php _e('Define the position of the title using the view graph', 'hugeit-slider'); ?></p>
                                        </div>
                                    </div>
                                </label>
                                <div>
                                    <table class="bws_position_table">
                                        <tbody>
                                        <tr>
                                            <td><input type="radio" value="13"
                                                       id="slideshow_title_top-left" <?php if (Hugeit_Slider_Options::get_title_position() == 13) echo 'checked="checked"'; ?> />
                                            </td>
                                            <td><input type="radio" value="23"
                                                       id="slideshow_title_top-center" <?php if (Hugeit_Slider_Options::get_title_position() == 23) echo 'checked="checked"'; ?> />
                                            </td>
                                            <td><input type="radio" value="33"
                                                       id="slideshow_title_top-right" <?php if (Hugeit_Slider_Options::get_title_position() == 33) echo 'checked="checked"'; ?> />
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><input type="radio" value="12"
                                                       id="slideshow_title_middle-left" <?php if (Hugeit_Slider_Options::get_title_position() == 12) echo 'checked="checked"'; ?> />
                                            </td>
                                            <td><input type="radio" value="22"
                                                       id="slideshow_title_middle-center" <?php if (Hugeit_Slider_Options::get_title_position() == 22) echo 'checked="checked"'; ?> />
                                            </td>
                                            <td><input type="radio" value="32"
                                                       id="slideshow_title_middle-right" <?php if (Hugeit_Slider_Options::get_title_position() == 32) echo 'checked="checked"'; ?> />
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><input type="radio" value="11"
                                                       id="slideshow_title_bottom-left" <?php if (Hugeit_Slider_Options::get_title_position() == 11) echo 'checked="checked"'; ?> />
                                            </td>
                                            <td><input type="radio" value="21"
                                                       id="slideshow_title_bottom-center" <?php if (Hugeit_Slider_Options::get_title_position() == 21) echo 'checked="checked"'; ?> />
                                            </td>
                                            <td><input type="radio" value="31"
                                                       id="slideshow_title_bottom-right" <?php if (Hugeit_Slider_Options::get_title_position() == 31) echo 'checked="checked"'; ?> />
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="options-block" id="options-block-description">
                            <h3>Description Styles</h3>
                            <div class="has-background">
                                <label for="description-container-width"><?php _e('Description Width', 'hugeit-slider'); ?>
                                    <div class="help">?
                                        <div class="help-block">
                                            <span class="pnt"></span>
                                            <p><?php _e('Set the width for the description text', 'hugeit-slider'); ?></p>
                                        </div>
                                    </div>
                                </label>
                                <div class="slider-container">
                                    <input id="description-container-width" data-slider-range="1,100" type="text"
                                           data-slider="true" data-slider-highlight="true"
                                           value="<?php echo Hugeit_Slider_Options::get_description_width(); ?>"/>
                                    <span><?php echo Hugeit_Slider_Options::get_description_width(); ?>%</span>
                                </div>
                                <div style="clear:both;"></div>
                            </div>
                            <div>
                                <label for="slider_description_has_margin"><?php _e('Description Has Margin', 'hugeit-slider'); ?>
                                    <div class="help">?
                                        <div class="help-block">
                                            <span class="pnt"></span>
                                            <p><?php _e('Choose the margin for description text', 'hugeit-slider'); ?></p>
                                        </div>
                                    </div>
                                </label>
                                <input type="hidden" value="0"/>
                                <input type="checkbox"
                                       id="slider_description_has_margin" <?php if (Hugeit_Slider_Options::get_description_has_margin() == '1') {
                                    echo 'checked="checked"';
                                } ?> value="1"/>
                            </div>
                            <div class="has-background">
                                <label for="slider_description_font_size"><?php _e('Description Font Size', 'hugeit-slider'); ?>
                                    <div class="help">?
                                        <div class="help-block">
                                            <span class="pnt"></span>
                                            <p><?php _e('Specify the font size for the image description', 'hugeit-slider'); ?></p>
                                        </div>
                                    </div>
                                </label>
                                <input type="text" id="slider_description_font_size"
                                       value="<?php echo Hugeit_Slider_Options::get_description_font_size(); ?>"
                                       class="text"/>
                                <span><?php _e('px', 'hugeit-slider'); ?></span>
                            </div>
                            <div>
                                <label for="slider_description_color"><?php _e('Description Text Color', 'hugeit-slider'); ?>
                                    <div class="help">?
                                        <div class="help-block">
                                            <span class="pnt"></span>
                                            <p><?php _e('Set the color for the image description', 'hugeit-slider'); ?></p>
                                        </div>
                                    </div>
                                </label>
                                <input type="text" class="color" id="slider_description_color"
                                       value="#<?php echo Hugeit_Slider_Options::get_description_color(); ?>"
                                       size="10"/>
                            </div>
                            <div class="has-background">
                                <label for="slider_description_text_align"><?php _e('Description Text Align', 'hugeit-slider'); ?>
                                    <div class="help">?
                                        <div class="help-block">
                                            <span class="pnt"></span>
                                            <p><?php _e('choose where to place the description text', 'hugeit-slider'); ?></p>
                                        </div>
                                    </div>
                                </label>
                                <select id="slider_description_text_align">
                                    <option <?php if (Hugeit_Slider_Options::get_description_text_align() == 'justify') echo 'selected'; ?>
                                            value="justify"><?php _e('Full width', 'hugeit-slider'); ?></option>
                                    <option <?php if (Hugeit_Slider_Options::get_description_text_align() == 'center') echo 'selected'; ?>
                                            value="center"><?php _e('Center', 'hugeit-slider'); ?></option>
                                    <option <?php if (Hugeit_Slider_Options::get_description_text_align() == 'left') echo 'selected'; ?>
                                            value="left"><?php _e('Left', 'hugeit-slider'); ?></option>
                                    <option <?php if (Hugeit_Slider_Options::get_description_text_align() == 'right') echo 'selected'; ?>
                                            value="right"><?php _e('Right', 'hugeit-slider'); ?></option>
                                </select>
                            </div>
                            <div>
                                <label for="description-background-transparency"><?php _e('Description Background Opacity', 'hugeit-slider'); ?>
                                    <div class="help">?
                                        <div class="help-block">
                                            <span class="pnt"></span>
                                            <p><?php _e('Set the level of description background transparency', 'hugeit-slider'); ?></p>
                                        </div>
                                    </div>
                                </label>
                                <div class="slider-container">
                                    <input id="description-background-transparency" data-slider-highlight="true"
                                           data-slider-values="0,10,20,30,40,50,60,70,80,90,100" type="text"
                                           data-slider="true"
                                           value="<?php echo 100 * Hugeit_Slider_Options::get_description_background_transparency(); ?>"/>
                                    <span><?php echo 100 * Hugeit_Slider_Options::get_description_background_transparency(); ?>
                                        %</span>
                                </div>
                            </div>
                            <div class="has-background">
                                <label for="slider_description_background_color"><?php _e('Description Background Color', 'hugeit-slider'); ?>
                                    <div class="help">?
                                        <div class="help-block">
                                            <span class="pnt"></span>
                                            <p><?php _e('Choose the color for description\'s background', 'hugeit-slider'); ?></p>
                                        </div>
                                    </div>
                                </label>
                                <input type="text" class="color" id="slider_description_background_color"
                                       value="#<?php echo Hugeit_Slider_Options::get_description_background_color(); ?>"
                                       size="10">
                            </div>
                            <div>
                                <label for="slider_description_border_size"><?php _e('Description Border Size', 'hugeit-slider'); ?>
                                    <div class="help">?
                                        <div class="help-block">
                                            <span class="pnt"></span>
                                            <p><?php _e('Specify the border for the image description', 'hugeit-slider'); ?></p>
                                        </div>
                                    </div>
                                </label>
                                <input type="text" id="slider_description_border_size"
                                       value="<?php echo Hugeit_Slider_Options::get_description_border_size(); ?>"
                                       class="text"/>
                                <span><?php _e('px', 'hugeit-slider'); ?></span>
                            </div>
                            <div class="has-background">
                                <label for="slider_description_border_color"><?php _e('Description Border Color', 'hugeit-slider'); ?>
                                    <div class="help">?
                                        <div class="help-block">
                                            <span class="pnt"></span>
                                            <p><?php _e('Select the border color for the image description', 'hugeit-slider'); ?></p>
                                        </div>
                                    </div>
                                </label>
                                <input type="text" class="color" id="slider_description_border_color"
                                       value="#<?php echo Hugeit_Slider_Options::get_description_border_color(); ?>"
                                       size="10">
                            </div>
                            <div>
                                <label for="slider_description_border_radius"><?php _e('Description Border Radius', 'hugeit-slider'); ?>
                                    <div class="help">?
                                        <div class="help-block">
                                            <span class="pnt"></span>
                                            <p><?php _e('Set the border radius for the image description cell', 'hugeit-slider'); ?></p>
                                        </div>
                                    </div>
                                </label>
                                <input type="text" id="slider_description_border_radius"
                                       value="<?php echo Hugeit_Slider_Options::get_description_border_radius(); ?>"
                                       class="text"/>
                                <span><?php _e('px', 'hugeit-slider'); ?></span>
                            </div>
                            <div class="has-height has-background">
                                <label for="params[description_position]"><?php _e('Description Position', 'hugeit-slider'); ?>
                                    <div class="help">?
                                        <div class="help-block">
                                            <span class="pnt"></span>
                                            <p><?php _e('Select the positioning of the description. Please make sure it does not coincide with the title position avoiding overloading', 'hugeit-slider'); ?></p>
                                        </div>
                                    </div>
                                </label>
                                <div>
                                    <table class="bws_position_table">
                                        <tbody>
                                        <tr>
                                            <td><input type="radio" value="13"
                                                       id="slideshow_description_top-left" <?php if (Hugeit_Slider_Options::get_description_position() == 13) echo 'checked="checked"'; ?> />
                                            </td>
                                            <td><input type="radio" value="23"
                                                       id="slideshow_description_top-center" <?php if (Hugeit_Slider_Options::get_description_position() == 23) echo 'checked="checked"'; ?> />
                                            </td>
                                            <td><input type="radio" value="33"
                                                       id="slideshow_description_top-right" <?php if (Hugeit_Slider_Options::get_description_position() == 33) echo 'checked="checked"'; ?> />
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><input type="radio" value="12"
                                                       id="slideshow_description_middle-left" <?php if (Hugeit_Slider_Options::get_description_position() == 12) echo 'checked="checked"'; ?> />
                                            </td>
                                            <td><input type="radio" value="22"
                                                       id="slideshow_description_middle-center" <?php if (Hugeit_Slider_Options::get_description_position() == 22) echo 'checked="checked"'; ?> />
                                            </td>
                                            <td><input type="radio" value="32"
                                                       id="slideshow_description_middle-right" <?php if (Hugeit_Slider_Options::get_description_position() == 32) echo 'checked="checked"'; ?> />
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><input type="radio" value="11"
                                                       id="slideshow_description_bottom-left" <?php if (Hugeit_Slider_Options::get_description_position() == 11) echo 'checked="checked"'; ?> />
                                            </td>
                                            <td><input type="radio" value="21"
                                                       id="slideshow_description_bottom-center" <?php if (Hugeit_Slider_Options::get_description_position() == 21) echo 'checked="checked"'; ?> />
                                            </td>
                                            <td><input type="radio" value="31"
                                                       id="slideshow_description_bottom-right" <?php if (Hugeit_Slider_Options::get_description_position() == 31) echo 'checked="checked"'; ?> />
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="options-block" id="options-block-thumbnail" style="bottom: 270px;">
                            <h3><?php _e('Navigation Thumbnails Styles', 'hugeit-slider'); ?></h3>
                            <div class="has-background">
                                <label for="slider_thumb_count_slides"><?php _e('Count of Thumbs Slides', 'hugeit-slider'); ?>
                                    <div class="help">?
                                        <div class="help-block">
                                            <span class="pnt"></span>
                                            <p><?php _e('Set the count of slides in thumbnail slider', 'hugeit-slider'); ?></p>
                                        </div>
                                    </div>
                                </label>
                                <input type="text" id="slider_thumb_count_slides"
                                       value="<?php echo Hugeit_Slider_Options::get_thumb_count_slides(); ?>"
                                       class="text"/>
                            </div>
                            <div>
                                <label for="slider_thumb_height"><?php _e('Slider Thumb Height', 'hugeit-slider'); ?>
                                    <div class="help">?
                                        <div class="help-block">
                                            <span class="pnt"></span>
                                            <p><?php _e('Set the thumbnail height', 'hugeit-slider'); ?></p>
                                        </div>
                                    </div>
                                </label>
                                <input type="text" id="slider_thumb_height"
                                       value="<?php echo Hugeit_Slider_Options::get_thumb_height(); ?>" class="text"/>
                                <span><?php _e('px', 'hugeit-slider'); ?></span>
                            </div>
                            <div class="has-background">
                                <label for="slider_thumb_back_color"><?php _e('Thumbnails Background Color', 'hugeit-slider'); ?>
                                    <div class="help">?
                                        <div class="help-block">
                                            <span class="pnt"></span>
                                            <p><?php _e('Select the background color for the thumbnails', 'hugeit-slider'); ?></p>
                                        </div>
                                    </div>
                                </label>
                                <input type="text" class="color" id="slider_thumb_back_color"
                                       value="#<?php echo Hugeit_Slider_Options::get_thumb_background_color(); ?>"
                                       size="10">
                            </div>
                            <div>
                                <label for="slider_thumb_passive_color"><?php _e('Passive Thumbnail Overlay Color', 'hugeit-slider'); ?>
                                    <div class="help">?
                                        <div class="help-block">
                                            <span class="pnt"></span>
                                            <p><?php _e('Select the background color for the thumbnails', 'hugeit-slider'); ?></p>
                                        </div>
                                    </div>
                                </label>
                                <input type="text" class="color" id="slider_thumb_passive_color"
                                       value="#<?php echo Hugeit_Slider_Options::get_thumb_passive_color(); ?>"
                                       size="10">
                            </div>
                            <div class="has-background">
                                <label for="slider_thumb_passive_color_trans"><?php _e('Passive Thumbnail Overlay Opacity`', 'hugeit-slider'); ?>
                                    <div class="help">?
                                        <div class="help-block">
                                            <span class="pnt"></span>
                                            <p><?php _e('Set the level of transparency for the title', 'hugeit-slider'); ?></p>
                                        </div>
                                    </div>
                                </label>
                                <div class="slider-container">
                                    <input id="slider_thumb_passive_color_trans" data-slider-highlight="true"
                                           data-slider-values="0,10,20,30,40,50,60,70,80,90,100" type="text"
                                           data-slider="true"
                                           value="<?php echo 100 * Hugeit_Slider_Options::get_thumb_passive_color_transparency(); ?>"/>
                                    <span><?php echo 100 * Hugeit_Slider_Options::get_thumb_passive_color_transparency(); ?>
                                        %</span>
                                </div>
                            </div>
                        </div>
                        <div class="options-block" id="options-block-navigation">
                            <h3><?php _e('Navigation Dots Styles', 'hugeit-slider'); ?></h3>

                            <div class="has-background">
                                <label for="slider_dots_position_new"><?php _e('Navigation Dots Position', 'hugeit-slider'); ?>
                                    <div class="help">?
                                        <div class="help-block">
                                            <span class="pnt"></span>
                                            <p><?php _e('Choose the navigation dots position', 'hugeit-slider'); ?></p>
                                        </div>
                                    </div>
                                </label>
                                <select id="slider_dots_position_new">
                                    <option <?php if (Hugeit_Slider_Options::get_navigation_position() == 'top') echo 'selected="selected"'; ?>
                                            value="top">Top
                                    </option>
                                    <option <?php if (Hugeit_Slider_Options::get_navigation_position() == 'bottom') echo 'selected="selected"'; ?>
                                            value="bottom">Bottom
                                    </option>
                                </select>
                            </div>
                            <div>
                                <label for="slider_dots_color"><?php _e('Navigation Dots Color', 'hugeit-slider'); ?>
                                    <div class="help">?
                                        <div class="help-block">
                                            <span class="pnt"></span>
                                            <p><?php _e('Select the dot color for the navigation', 'hugeit-slider'); ?></p>
                                        </div>
                                    </div>
                                </label>
                                <input type="text" class="color" id="slider_dots_color"
                                       value=#"<?php echo Hugeit_Slider_Options::get_dots_color(); ?>"/>
                            </div>
                            <div class="has-background">
                                <label for="slider_active_dot_color"><?php _e('Navigation Active Dot Color', 'hugeit-slider'); ?>
                                    <div class="help">?
                                        <div class="help-block">
                                            <span class="pnt"></span>
                                            <p><?php _e('Specify the color for the dot for the currently displayed image', 'hugeit-slider'); ?></p>
                                        </div>
                                    </div>
                                </label>
                                <input type="text" class="color" id="slider_active_dot_color"
                                       value="<?php echo Hugeit_Slider_Options::get_active_dot_color(); ?>"/>
                            </div>
                        </div>
                        <div class="options-block2" id="options-block-thumbnail">
                            <div>
                                <div>
                                    <label for="slider_show_arrows"><?php _e('Show Navigation Arrows', 'hugeit-slider'); ?>
                                        <div class="help">?
                                            <div class="help-block">
                                                <span class="pnt"></span>
                                                <p><?php _e('Choose whether to show navigation arrows', 'hugeit-slider'); ?></p>
                                            </div>
                                        </div>
                                    </label>
                                    <input type="hidden" value="0"/>
                                    <input type="checkbox"
                                           id="slider_show_arrows" <?php if (Hugeit_Slider_Options::get_show_arrows() == 1) echo 'checked="checked"'; ?>
                                           value="1"/>
                                </div>
                                <div class="has-height " style="padding-top:20px;">
                                    <label for=""><?php _e('Navigation Arrows Style', 'hugeit-slider'); ?>
                                        <div class="help">?
                                            <div class="help-block">
                                                <span class="pnt"></span>
                                                <p><?php _e('Select the type of the navigation arrows to be used for the website', 'hugeit-slider'); ?></p>
                                            </div>
                                        </div>
                                    </label>
                                </div>

                                <ul id="arrows-type">
                                    <?php
                                    $image_names = array(1 => 'arrows1', 2 => 'arrows2', 3 => 'arrows3', 4 => 'arrows4', 5 => 'arrows5', 6 => 'arrows6', 7 => 'arrows7', 8 => 'arrows8', 9 => 'arrows9', 10 => 'arrows10', 11 => 'arrows11', 12 => 'arrows12', 13 => 'arrows13', 14 => 'arrows14', 15 => 'arrows15', 16 => 'arrows16', 17 => 'arrows17', 18 => 'arrows18', 19 => 'arrows19', 20 => 'arrows20', 21 => 'arrows21',);
                                    foreach ($image_names as $index => $name) : ?>
                                        <li <?php if (Hugeit_Slider_Options::get_navigation_type() == $index) echo 'class="active"'; ?>>
                                            <div class="image-block">
                                                <img src="<?php echo HUGEIT_SLIDER_FRONT_IMAGES_URL . '/arrows/' . $name . '.png'; ?>"
                                                     alt=""/>
                                            </div>
                                            <input type="radio"
                                                   value="<?php echo $index; ?>>" <?php if (Hugeit_Slider_Options::get_navigation_type() == $index) echo 'checked="checked"'; ?>>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                        <div id="post-body-footer">
                            <a onclick="document.getElementById('adminForm').submit()"
                               class="save-slider-options button-primary">Save</a>
                            <div class="clear"></div>
                            <script>
                                jQuery(document).ready(function () {
                                    jQuery("#slideup<?php echo isset($key) ? $key : ''; ?>").click(function () {
                                        window.parent.uploadID = jQuery(this).prev('input');
                                        formfield = jQuery('.upload').attr('name');
                                        tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
                                        return false;
                                    });
                                    window.send_to_editor = function (html) {
                                        var imgurl = jQuery('img', html).attr('src');
                                        window.parent.uploadID.val(imgurl);
                                        tb_remove();
                                    };
                                });
                            </script>
                        </div>
                        <?php wp_nonce_field('save_general_options', 'hugeit_slider_save_general_options_nonce'); ?>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
