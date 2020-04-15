<?php

if (!function_exists('current_user_can')) {
    die('Access Denied');
}

if (!current_user_can('delete_pages')) {
    die('Access Denied');
}

/**
 * @var string $add_slider_nonce
 */

?>

<div class="wrap">
    <?php echo Hugeit_Slider_Template_Loader::render(HUGEIT_SLIDER_ADMIN_TEMPLATES_PATH . DIRECTORY_SEPARATOR . 'free-banner.php'); ?>
    <div id="sliders-list-page">
        <h2>
            <?php _e('Huge Sliders', 'hugeit-slider'); ?><a class="add-new-h2"
                                                            href="<?php echo $add_slider_nonce; ?>"><?php _e('Add New Slider', 'hugeit-slider'); ?></a>
        </h2>
        <form id="sliders" method="post" action="<?php echo 'admin.php?page=hugeit_slider'; ?>">
            <div class="alignleft actions">
                <label for="search" style="font-size:14px"><?php _e('Filter', 'hugeit-slider'); ?>: </label>
                <input type="text" name="search" value="<?php if (!empty($search)) echo $search; ?>" id="search"/>
            </div>
            <div class="alignleft actions">
                <input type="submit" value="<?php _e('Search', 'hugeit-slider'); ?>" class="button-secondary action">
                <input type="button" value="<?php _e('Reset', 'hugeit-slider'); ?>" id="reset"
                       class="button-secondary action">
            </div>
            <?php if (!empty($pagination_html)) : ?>
                <div class="pagination" style="float: right">
                    <?php echo $pagination_html; ?>
                </div>
            <?php endif; ?>
            <table class="wp-list-table widefat fixed pages">
                <thead>
                <tr>
                    <th scope="col" id="id" style="width:30px"><span><?php _e('ID', 'hugeit-slider'); ?></span></th>
                    <th scope="col" id="name" style="width:85px"><span><?php _e('Name', 'hugeit-slider'); ?></span></th>
                    <th scope="col" id="slide_count" style="width:75px;">
                        <span><?php _e('Images', 'hugeit-slider'); ?></span></th>
                    <th scope="col" id="slide_count" style="width:75px;">
                        <span><?php _e('Shortcode', 'hugeit-slider'); ?></span>
                    </th>
                    <th scope="col" id="duplicate" style="width: 75px"><?php _e('Duplicate', 'hugeit-slider'); ?></th>
                    <th style="width:40px"><?php _e('Delete', 'hugeit-slider'); ?></th>
                </tr>
                </thead>
                <tbody>
                <?php
                /** @var Hugeit_Slider_Slider[] $sliders */

                if (count($sliders)) :
                    $has_background = true;

                    foreach ($sliders as $slider) :

                        $edit_nonce_url = wp_nonce_url('admin.php?page=hugeit_slider&task=edit&id=' . $slider->get_id(), 'edit_slider_' . $slider->get_id(), 'hugeit_slider_edit_slider_nonce');
                        $delete_nonce = wp_create_nonce('delete_slider_' . $slider->get_id());
                        $duplicate_nonce = wp_create_nonce('duplicate_slider_' . $slider->get_id());

                        ?>
                        <tr <?php if (Hugeit_Slider_Helpers::has_background($has_background)) echo 'class="has-background"'; ?>>
                            <td><?php echo $slider->get_id(); ?></td>
                            <td><a href="<?php echo $edit_nonce_url; ?>"><?php echo $slider->get_name(); ?></a></td>
                            <td>(<?php echo $slider->get_slides_count(); ?>)</td>
                            <td>[huge_it_slider id="<?php echo $slider->get_id(); ?>"]</td>
                            <td><a href="#" class="hugeit_slider_duplicate_slider"
                                   data-slider-id="<?php echo $slider->get_id(); ?>"
                                   data-nonce="<?php echo $duplicate_nonce; ?>"><span
                                            class="hugeit-slider-duplicate-slider" aria-hidden="true"></span></a>
                            </td>
                            <td><a href="#" class="hugeit_slider_delete_slider"
                                   data-slider-id="<?php echo $slider->get_id(); ?>"
                                   data-nonce="<?php echo $delete_nonce; ?>"><span
                                            class="hugeit-slider-remove-slider" aria-hidden="true"></span></a></td>
                        </tr>
                    <?php endforeach;
                else : ?>
                    <tr>
                        <td rowspan="5"><?php _e('No slider', 'hugeit-slider'); ?></td>
                    </tr>
                <?php endif; ?>
                </tbody>
                <tfoot>
                <tr>
                    <td scope="col" id="id" style="width:30px"><span><?php _e('ID', 'hugeit-slider'); ?></span><span
                                class="sorting-indicator"></span></td>
                    <td scope="col" id="name" style="width:85px"><span><?php _e('Name', 'hugeit-slider'); ?></span><span
                                class="sorting-indicator"></span></td>
                    <td scope="col" id="slide_count" style="width:75px;">
                        <span><?php _e('Images', 'hugeit-slider'); ?></span><span class="sorting-indicator"></span></td>
                    <td scope="col" id="slide_count" style="width:75px;">
                        <span><?php _e('Shortcode', 'hugeit-slider'); ?></span>
                    </td>
                    <td scope="col" id="duplicate"><?php _e('Duplicate', 'hugeit-slider'); ?></td>
                    <td style="width:40px"><?php _e('Delete', 'hugeit-slider'); ?></td>
                </tr>
                </tfoot>
            </table>
        </form>
    </div>
</div>
