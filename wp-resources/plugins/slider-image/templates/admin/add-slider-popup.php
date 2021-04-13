<?php
/**
 * @var stdClass $sliders
 */
?>
<div id="hugeit_slider_media_popup" class="post-slider" style="display: none">
    <div class="post-slider">
        <div class="post-slider-block">
            <div class="slider-body">
                <div class="select-slider">
                    <h3><?php _e('Select the Slider', 'hugeit-slider'); ?></h3>
                    <div class="select-block">
                        <select>
							<?php if (!empty($sliders)) : ?>
                                <?php foreach ( $sliders as $id => $slider ): ?>
                                    <option value="<?php echo $id; ?>"><?php echo $slider->name; ?></option>
							    <?php endforeach; ?>
                            <?php else : ?>
                                <option value=""><?php _e('You have no created slider', 'hugeit-slider'); ?></option>
							<?php endif; ?>
                        </select>
                        <button <?php disabled(empty($sliders)) ?> id="hugeit_slider_insert_slider_to_post"><?php _e('Insert Slider', 'hugeit-slider'); ?></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>