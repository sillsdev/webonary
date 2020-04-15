<?php
/**
 * @var WP_Term $categories[]
 * @var int $max_post_count
 * @var int $show_title
 * @var int $show_description
 * @var int $in_new_tab
 * @var int $go_to_post
 */
?>
<div class="image-block">
	<img src="<?php echo HUGEIT_SLIDER_ADMIN_IMAGES_URL . '/ping.png'; ?>" alt="">
</div>
<div class="slider-option">
	<table>
		<tr>
			<td><?php _e('Show Posts From', 'hugeit-slider'); ?>:</td>
			<td>
				<select class="hugeit-slider-post-slide-category-id">
				<?php foreach ($categories as $category) : ?>
					<option value="<?php echo $category->term_id; ?>" <?php selected($term_id, $category->term_id); ?>><?php echo $category->name; ?></option>
				<?php endforeach; ?>
				</select>
				<label>
					<?php _e('Showing Posts Count', 'hugeit-slider'); ?>:
					<input type="number" min="1" value="<?php echo $max_post_count; ?>" class="hugeit-slider-post-slide-max-post-count">
				</label>
			</td>
		</tr>
		<tr>
			<td><?php _e('Show Title', 'hugeit-slider'); ?>:</td>
			<td>
				<label>
					<input type="checkbox" <?php checked($show_title); ?> class="hugeit-slider-post-slide-show-title">
					<span></span>
				</label>
			</td>
		</tr>
		<tr>
			<td><?php _e('Show Description', 'hugeit-slider'); ?>:</td>
			<td>
				<label>
					<input type="checkbox" <?php checked($show_description); ?> class="hugeit-slider-post-slide-show-description">
					<span></span>
				</label>
			</td>
		</tr>
		<tr>
			<td>
                <label><?php _e('Use Post Link', 'hugeit-slider'); ?>:
                    <input type="checkbox" <?php checked($go_to_post) ?> class="hugeit-slider-post-slide-go-to-post">
                    <span></span>
                </label>
            </td>
			<td>
				<label><?php _e('Open Link In New Tab', 'hugeit-slider'); ?>:
					<input type="checkbox" <?php checked($in_new_tab) ?> class="hugeit-slider-post-slide-in-new-tab">
					<span></span>
				</label>
			</td>
		</tr>
		<tr>
			<td colspan="2"><a href="#" class="remove-image"><?php _e('Remove Image', 'hugeit-slider'); ?></a></td>
		</tr>
	</table>
</div>