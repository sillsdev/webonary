<?php
/**
 * @var WP_Post[] $posts
 * @var WP_Term[] $categories
*/
?>
<div>
    <span class="buy-pro"><?php _e('This feature is disabled in free version', 'hugeit-slider'); ?>. <br><?php _e('If you need this functionality, you need to', 'hugeit-slider'); ?> <a href="https://huge-it.com/slider/" target="_blank"><?php _e('buy the commercial version', 'hugeit-slider'); ?></a>.</span>
</div>
<div id="hugeit_slider_add_post_popup_tabs">
	<ul>
		<li><a href="#static_posts"><?php _e('Static Posts', 'hugeit-slider'); ?></a></li>
		<li><a href="#last_posts"><?php _e('Last Posts', 'hugeit-slider'); ?></a></li>
	</ul>
	<div id="static_posts" class="hugeit-slider-tab">
		<select id="hugeit_slider_add_post_slide_popup_categories_dropdown">
		<?php foreach ($categories as $category) : ?>
			<option value="<?php echo $category->term_id; ?>"><?php echo $category->name; ?></option>
		<?php endforeach; ?>
		</select>
		<table id="hugeit_slider_post_slide_popup_table">
			<thead>
				<tr>
					<th><?php _e('Image', 'hugeit-slider'); ?></th>
					<th><?php _e('Title', 'hugeit-slider'); ?></th>
					<th><?php _e('Excerpt', 'hugeit-slider'); ?></th>
					<th><?php _e('Link', 'hugeit-slider'); ?></th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th><?php _e('Image', 'hugeit-slider'); ?></th>
					<th><?php _e('Title', 'hugeit-slider'); ?></th>
					<th><?php _e('Description', 'hugeit-slider'); ?></th>
					<th><?php _e('Link', 'hugeit-slider'); ?></th>
				</tr>
			</tfoot>
			<tbody>
				<?php foreach ($posts as $post) :
					$src = get_the_post_thumbnail_url($post->ID);
					$title = $post->post_title;
					$excerpt = $post->post_excerpt;
					$link = get_the_permalink($post->ID);
				?>
				<tr class="hugeit-slider-post-popup-table-row hugeit-slider-not-selected-post-slide">
					<td class="post-id invisible"><input type="checkbox" name="id" value="<?php echo $post->ID; ?>" /></td>
					<td><img src="<?php echo $src; ?>" alt="" /></td>
					<td><?php echo $title; ?></td>
					<td><?php echo $excerpt; ?></td>
					<td><?php echo $link; ?></td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<button type="button" class="button button-primary" id="hugeit_slider_add_static_post_slide"><?php _e('Add Slide', 'hugeit-slider'); ?></button>
	</div>
	<div id="last_posts" class="hugeit-slider-tab">
		<form>
			<label><?php _e('Show Posts From', 'hugeit-slider'); ?>:
				<select id="hugeit_slider_dynamic_post_category_id">
                    <option value="0"><?php _e('All Categories', 'hugeit-slider'); ?></option>
					<?php foreach ($categories as $category) : ?>
						<option value="<?php echo $category->term_id; ?>"><?php echo $category->name; ?></option>
					<?php endforeach; ?>
				</select>
			</label>
			<br />
			<label><?php _e('Showing Posts Count', 'hugeit-slider'); ?>:
				<input type="number" min="1" id="hugeit_slider_dynamic_post_max_post_count" name="max_post_count" value="3" />
			</label>
			<br />
			<label><?php _e('Show Title', 'hugeit-slider'); ?>:
				<input type="checkbox" id="hugeit_slider_dynamic_post_show_title" name="show_title" />
			</label>
			<br />
			<label><?php _e('Show Description', 'hugeit-slider'); ?>:
				<input type="checkbox" id="hugeit_slider_dynamic_post_show_description" name="show_description" />
			</label>
			<br />
			<label><?php _e('Use Post Link', 'hugeit-slider'); ?>:
				<input type="checkbox" id="hugeit_slider_dynamic_post_use_post_link" name="use_post_link" />
			</label>
			<br />
			<label><?php _e('Open Link In New Tab', 'hugeit-slider'); ?>:
				<input type="checkbox" id="hugeit_slider_dynamic_post_in_new_tab" name="in_new_tab" />
			</label>
			<br />

			<button type="button" class="button button-primary" id="hugeit_slider_add_dynamic_post_slide"><?php _e('Add Slide', 'hugeit-slider'); ?></button>
		</form>
	</div>
</div>