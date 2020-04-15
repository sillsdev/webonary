<?php

/**
 * @var Hugeit_Slider_Slide_Image $slide
 * @var int $id
 * @var array $attachment
 * @var string $src
 * @var string $title
 * @var int $attachment_id
 * @var bool $in_new_tab
 */

?>

<input type="hidden" value="<?php echo $attachment_id; ?>" class="attachment-id"/>
<div class="image-block">
    <div class="centering">
        <img src="<?php echo $src; ?>" alt="" class="slide-thumbnail"/>
    </div>

</div>
<div class="slider-option">
    <table>
        <tr>
            <td><input type="text" name="title_<?php echo $id; ?>" value="<?php echo $title; ?>" class="title title"
                       placeholder="<?php _e('Title', 'hugeit-slider'); ?>"/></td>
        </tr>
        <tr>
            <td><textarea name="description_<?php echo $id; ?>" id="description" class="description"
                          placeholder="<?php _e('Description', 'hugeit-slider'); ?>"><?php echo $description; ?></textarea>
            </td>
        </tr>
        <tr>
            <td>
                <input
                        type="text"
                        name="url_<?php echo $id; ?>"
                        value="<?php echo $url; ?>"
                        class="url"
                        placeholder="<?php _e('URL', 'hugeit-slider'); ?>"
                />

                <label>
                    <input
                            type="checkbox"
                            id="in_new_tab_<?php echo $id; ?>"
                            name="in_new_tab_<?php echo $id; ?>"
                            class="in-new-tab"
                        <?php checked($in_new_tab); ?> />

                    <?php _e('Open in new tab', 'hugeit-slider'); ?><span></span>
                </label>
            </td>
        </tr>
        <tr>
            <td colspan="2"><a href="#" class="remove-image"><?php _e('Remove Image', 'hugeit-slider'); ?></a></td>
        </tr>
    </table>
</div>
<div class="edit-image">
    <a href="#" class="edit"><img src="<?php echo HUGEIT_SLIDER_ADMIN_IMAGES_URL . "/edit_icon.png" ?>"
                                  title="edit image"></a>
</div>