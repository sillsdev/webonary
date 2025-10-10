<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Hugeit_Slider_Html_Loader {

	/**
	 * @param Hugeit_Slider_Slide_Image | Hugeit_Slider_Slide_Video | Hugeit_Slider_Slide_Post $slide
	 * @param array $args
	 *
	 * @return bool
	 */
	public static function get_slide_html($slide, $args = array()) {

		$id = $slide->get_id();
		$type = $slide->get_type();

		switch ($type) {
			case 'image' :
				/**
				 * @var Hugeit_Slider_Slide_Image $slide
				 */
				$attachment = isset($args['attachment']) ? $args['attachment'] : array();

				$html = self::get_image_slider_html($slide, $attachment);

				return $html;
			case 'video' :
				/**
				 * @var Hugeit_Slider_Slide_Video $slide
				 */
				$html = self::get_video_slide_html($slide);

				return $html;
			case 'post' :
				if (NULL === $id) {
					return false;
				}

				$html = self::get_post_slide_html($slide);

				return $html;
		}

		return false;
	}

	/**
	 * @param Hugeit_Slider_Slide_Image $slide
	 * @param array $attachment
	 *
	 * @return bool
	 */
	private static function get_image_slider_html( Hugeit_Slider_Slide_Image $slide, $attachment = array() ) {
		if (NULL === $slide->get_id() && empty($attachment)) {
			return false;
		}

		$id = $slide->get_id();
		$title = $slide->get_title() ? $slide->get_title() : '';
		$description = $slide->get_description() ? $slide->get_description() : '';
		$url = $slide->get_url() ? $slide->get_url() : '';
		$src = $slide->get_attachment_id() === NULL ? $attachment['sizes']['thumbnail']['url'] : wp_get_attachment_url($slide->get_attachment_id());
		$attachment_id = $slide->get_attachment_id() === NULL ? $attachment['id'] : $slide->get_attachment_id();
		$in_new_tab = (bool)$slide->get_in_new_tab();

		return Hugeit_Slider_Template_Loader::render(HUGEIT_SLIDER_ADMIN_TEMPLATES_PATH . DIRECTORY_SEPARATOR . 'single-slides' . DIRECTORY_SEPARATOR . 'image.php', array(
			'id' => $id,
			'title' => $title,
			'description' => $description,
			'url' => $url,
			'src' => $src,
			'attachment_id' => $attachment_id,
			'in_new_tab' => $in_new_tab,
		));
	}

	public static function get_add_video_popup() {
		return Hugeit_Slider_Template_Loader::render(HUGEIT_SLIDER_ADMIN_TEMPLATES_PATH . DIRECTORY_SEPARATOR . '_video-popup.php');
	}

	public static function get_video_slide_popup_html() {
		return Hugeit_Slider_Template_Loader::render(HUGEIT_SLIDER_ADMIN_TEMPLATES_PATH . DIRECTORY_SEPARATOR . '_video-popup.php');
	}

	public static function get_post_slide_popup_html() {
		$posts = get_posts(array(
			'post_type'  => 'post',
			'meta_query' => array(
				array(
					'key' => '_thumbnail_id',
					'compare' => 'EXISTS'
				),
			)
		));

		$categories = get_categories(array('orderby' => 'id'));

		return Hugeit_Slider_Template_Loader::render(HUGEIT_SLIDER_ADMIN_TEMPLATES_PATH . DIRECTORY_SEPARATOR . '_post-popup.php', array(
			'posts' => $posts,
			'categories' => $categories,
		));
	}

	public static function get_post_slide_popup_row( $id, $src, $title, $excerpt, $url ) {
		ob_start();
		?>
		<tr class="hugeit-slider-post-popup-table-row hugeit-slider-not-selected-post-slide">
			<td class="post-id invisible"><input type="checkbox" name="id" value="<?php echo $id ?>" /></td>
			<td><img src="<?php echo $src; ?>" alt=""></td>
			<td><?php echo $title; ?></td>
			<td><?php echo $excerpt; ?></td>
			<td><?php echo $url; ?></td>
		</tr>
		<?php
		return ob_get_clean();
	}

	public static function get_pagination_html($page, $totalPage) {
		ob_start(); ?>

		<div><span style="margin-right: 20px">Page <?php echo $page; ?> of <?php echo $totalPage; ?></span>
			<?php echo paginate_links(array('base' => add_query_arg( 'cpage', '%#%' ), 'format' => '', 'prev_text' => __('&laquo;'), 'next_text' => __('&raquo;'), 'total' => $totalPage, 'current' => $page)); ?>
		</div>

		<?php return ob_get_clean();
	}
}