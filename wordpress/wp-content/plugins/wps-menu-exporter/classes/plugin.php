<?php

namespace WPS\WPS_Menu_Exporter;

class Plugin {

    use Singleton;

	protected function init() {
		add_action( 'load-export.php', array( __CLASS__, 'wps_menu_exporter_show_menu_post_type_in_export_options' ) );
		add_action( 'load-export.php', array( __CLASS__, 'wps_menu_exporter_menu_export' ), 1 );
		add_filter( 'plugin_action_links_' . WPS_MENU_EXPORTER_BASENAME, array( __CLASS__, 'wps_menu_exporter_plugin_action_links' ) );
	}

	/**
	 * Temporarily hacks the nav_menu_item post type to set builtin to false to it shows up under eport options.
	 *
	 */
	public static function wps_menu_exporter_show_menu_post_type_in_export_options() {
		global $wp_post_types;
		$wp_post_types['nav_menu_item']->_builtin = false;
	}

	/**
	 * Exporter menu
	 */
	public static function wps_menu_exporter_menu_export() {

		if ( ! isset( $_GET['download'] ) || empty( $_GET['content'] ) || $_GET['content'] !== 'nav_menu_item' ) {
			return;
		}

		self::wps_menu_exporter_export_wp();

		unset( $_GET['download'] );
		die();

	}

	/**
	 *
	 * Export menus
	 *
	 * @param $links
	 *
	 * @return mixed
	 */
	public static function wps_menu_exporter_plugin_action_links( $links ) {

		array_unshift( $links, '<a href="' . admin_url( 'export.php' ) . '">' . __( 'Export' ) . '</a>' );

		return $links;
	}

	/**
	 * Generates the WXR export file for download - This is a rip of export_wp but supports only exporting menus and it's terms
	 *
	 *
	 * @param array $args Filters defining what should be included in the export
	 */
	public static function wps_menu_exporter_export_wp( $args = array() ) {
		global $wpdb, $post;

		$sitename = sanitize_key( get_bloginfo( 'name' ) );
		if ( ! empty( $sitename ) ) {
			$sitename .= '.';
		}
		$filename = $sitename . 'wordpress.' . date( 'Y-m-d' ) . '.xml';

		header( 'Content-Description: File Transfer' );
		header( 'Content-Disposition: attachment; filename=' . $filename );
		header( 'Content-Type: text/xml; charset=' . get_option( 'blog_charset' ), true );

		$where = $wpdb->prepare( "{$wpdb->posts}.post_type = %s", 'nav_menu_item' );

		// grab a snapshot of post IDs, just in case it changes during the export
		$post_ids = $wpdb->get_col( "SELECT ID FROM {$wpdb->posts} WHERE $where" );

		/**
		 * Wrap given string in XML CDATA tag.
		 *
		 * @param $str
		 *
		 * @return string
		 */
		function wps_wxr_cdata( $str ) {
			if ( ! seems_utf8( $str ) ) {
				$str = utf8_encode( $str );
			}
			// $str = ent2ncr(esc_html($str));
			$str = '<![CDATA[' . str_replace( ']]>', ']]]]><![CDATA[>', $str ) . ']]>';

			return $str;
		}

		/**
		 * Return the URL of the site
		 *
		 * @since 2.5.0
		 *
		 * @return string Site URL.
		 */
		function wps_wxr_site_url() {
			// ms: the base url
			if ( is_multisite() ) {
				return network_home_url();
			} // wp: the blog url
			else {
				return get_bloginfo_rss( 'url' );
			}
		}

		/**
		 * Output a term_name XML tag from a given term object
		 *
		 * @since 2.9.0
		 *
		 * @param object $term Term Object
		 */
		function wps_wxr_term_name( $term ) {
			if ( empty( $term->name ) ) {
				return;
			}

			echo '<wp:term_name>' . wps_wxr_cdata( $term->name ) . '</wp:term_name>';
		}

		/**
		 * Ouput all navigation menu terms
		 *
		 * @since 3.1.0
		 */
		function wps_wxr_nav_menu_terms() {
			$nav_menus = wp_get_nav_menus();
			if ( empty( $nav_menus ) || ! is_array( $nav_menus ) ) {
				return;
			}

			foreach ( $nav_menus as $menu ) {
				echo "\t<wp:term><wp:term_id>{$menu->term_id}</wp:term_id><wp:term_taxonomy>nav_menu</wp:term_taxonomy><wp:term_slug>{$menu->slug}</wp:term_slug>";
				wps_wxr_term_name( $menu );
				echo "</wp:term>\n";
			}
		}

		function wps_wxr_nav_menu_item_terms_and_posts( &$post_ids ) {

			$posts_to_add = array();

			foreach ( $post_ids as $post_id ) {

				if ( ( $type = get_post_meta( $post_id, '_menu_item_type', true ) ) == 'taxonomy' ) {
					$term = get_term( get_post_meta( $post_id, '_menu_item_object_id', true ), ( $tax = get_post_meta( $post_id, '_menu_item_object', true ) ) );

					echo "\t<wp:term><wp:term_id>{$term->term_id}</wp:term_id><wp:term_taxonomy>{$tax}</wp:term_taxonomy><wp:term_slug>{$term->slug}</wp:term_slug>";
					wps_wxr_term_name( $term );
					echo "</wp:term>\n";
				} elseif ( $type == 'post_type' && in_array( get_post_meta( $post_id, '_menu_item_object', true ), array(
						'post',
						'page'
					) ) ) {

					$posts_to_add[] = get_post_meta( $post_id, '_menu_item_object_id', true );
				}


			}
			$post_ids = array_merge( $posts_to_add, $post_ids );
		}

		/**
		 * Output list of taxonomy terms, in XML tag format, associated with a post
		 *
		 * @since 2.3.0
		 */
		function wps_wxr_post_taxonomy() {
			global $post;

			$taxonomies = get_object_taxonomies( $post->post_type );
			if ( empty( $taxonomies ) ) {
				return;
			}
			$terms = wp_get_object_terms( $post->ID, $taxonomies );

			foreach ( (array) $terms as $term ) {
				echo "\t\t<category domain=\"{$term->taxonomy}\" nicename=\"{$term->slug}\">" . wps_wxr_cdata( $term->name ) . "</category>\n";
			}
		}

		echo '<?xml version="1.0" encoding="' . get_bloginfo( 'charset' ) . "\" ?>\n";

		?>
		<!-- This is a WordPress eXtended RSS file generated by WordPress as an export of your site. -->
		<!-- It contains information about your site's posts, pages, comments, categories, and other content. -->
		<!-- You may use this file to transfer that content from one site to another. -->
		<!-- This file is not intended to serve as a complete backup of your site. -->

		<!-- To import this information into a WordPress site follow these steps: -->
		<!-- 1. Log in to that site as an administrator. -->
		<!-- 2. Go to Tools: Import in the WordPress admin panel. -->
		<!-- 3. Install the "WordPress" importer from the list. -->
		<!-- 4. Activate & Run Importer. -->
		<!-- 5. Upload this file using the form provided on that page. -->
		<!-- 6. You will first be asked to map the authors in this export file to users -->
		<!--    on the site. For each author, you may choose to map to an -->
		<!--    existing user on the site or to create a new user. -->
		<!-- 7. WordPress will then import each of the posts, pages, comments, categories, etc. -->
		<!--    contained in this file into your site. -->

		<?php the_generator( 'export' ); ?>
		<rss version="2.0"
		     xmlns:excerpt="http://wordpress.org/export/<?php echo '1.2'; ?>/excerpt/"
		     xmlns:content="http://purl.org/rss/1.0/modules/content/"
		     xmlns:wfw="http://wellformedweb.org/CommentAPI/"
		     xmlns:dc="http://purl.org/dc/elements/1.1/"
		     xmlns:wp="http://wordpress.org/export/<?php echo '1.2'; ?>/"
		>

			<channel>
				<title><?php bloginfo_rss( 'name' ); ?></title>
				<link><?php bloginfo_rss( 'url' ); ?></link>
				<description><?php bloginfo_rss( 'description' ); ?></description>
				<pubDate><?php echo date( 'D, d M Y H:i:s +0000' ); ?></pubDate>
				<language><?php echo get_option( 'rss_language' ); ?></language>
				<wp:wxr_version><?php echo '1.2'; ?></wp:wxr_version>
				<wp:base_site_url><?php echo wps_wxr_site_url(); ?></wp:base_site_url>
				<wp:base_blog_url><?php bloginfo_rss( 'url' ); ?></wp:base_blog_url>

				<?php wps_wxr_nav_menu_terms(); ?>
				<?php wps_wxr_nav_menu_item_terms_and_posts( $post_ids ) ?>

				<?php do_action( 'rss2_head' ); ?>

				<?php if ( $post_ids ) {
					/**
					 * @global WP_Query $wp_query
					 */
					global $wp_query;

					// Fake being in the loop.
					$wp_query->in_the_loop = true;

					// Fetch 20 posts at a time rather than loading the entire table into memory.
					while ( $next_posts = array_splice( $post_ids, 0, 20 ) ) {
						$where = 'WHERE ID IN (' . join( ',', $next_posts ) . ')';
						$posts = $wpdb->get_results( "SELECT * FROM {$wpdb->posts} $where" );

						// Begin Loop.
						foreach ( $posts as $post ) {
							setup_postdata( $post );
							$is_sticky = is_sticky( $post->ID ) ? 1 : 0;
							?>
							<item>
								<title><?php
									/** This filter is documented in wp-includes/feed.php */
									echo apply_filters( 'the_title_rss', $post->post_title );
									?></title>
								<link><?php the_permalink_rss() ?></link>
								<pubDate><?php echo mysql2date( 'D, d M Y H:i:s +0000', get_post_time( 'Y-m-d H:i:s', true ), false ); ?></pubDate>
								<dc:creator><?php echo wps_wxr_cdata( get_the_author_meta( 'login' ) ); ?></dc:creator>
								<guid isPermaLink="false"><?php the_guid(); ?></guid>
								<description></description>
								<content:encoded><?php
									/**
									 * Filters the post content used for WXR exports.
									 *
									 * @since 2.5.0
									 *
									 * @param string $post_content Content of the current post.
									 */
									echo wps_wxr_cdata( apply_filters( 'the_content_export', $post->post_content ) );
									?></content:encoded>
								<excerpt:encoded><?php
									/**
									 * Filters the post excerpt used for WXR exports.
									 *
									 * @since 2.6.0
									 *
									 * @param string $post_excerpt Excerpt for the current post.
									 */
									echo wps_wxr_cdata( apply_filters( 'the_excerpt_export', $post->post_excerpt ) );
									?></excerpt:encoded>
								<wp:post_id><?php echo intval( $post->ID ); ?></wp:post_id>
								<wp:post_date><?php echo wps_wxr_cdata( $post->post_date ); ?></wp:post_date>
								<wp:post_date_gmt><?php echo wps_wxr_cdata( $post->post_date_gmt ); ?></wp:post_date_gmt>
								<wp:comment_status><?php echo wps_wxr_cdata( $post->comment_status ); ?></wp:comment_status>
								<wp:ping_status><?php echo wps_wxr_cdata( $post->ping_status ); ?></wp:ping_status>
								<wp:post_name><?php echo wps_wxr_cdata( $post->post_name ); ?></wp:post_name>
								<wp:status><?php echo wps_wxr_cdata( $post->post_status ); ?></wp:status>
								<wp:post_parent><?php echo intval( $post->post_parent ); ?></wp:post_parent>
								<wp:menu_order><?php echo intval( $post->menu_order ); ?></wp:menu_order>
								<wp:post_type><?php echo wps_wxr_cdata( $post->post_type ); ?></wp:post_type>
								<wp:post_password><?php echo wps_wxr_cdata( $post->post_password ); ?></wp:post_password>
								<wp:is_sticky><?php echo intval( $is_sticky ); ?></wp:is_sticky>
								<?php	if ( $post->post_type == 'attachment' ) : ?>
									<wp:attachment_url><?php echo wps_wxr_cdata( wp_get_attachment_url( $post->ID ) ); ?></wp:attachment_url>
								<?php 	endif; ?>
								<?php 	wps_wxr_post_taxonomy(); ?>
								<?php	$postmeta = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpdb->postmeta WHERE post_id = %d", $post->ID ) );
								foreach ( $postmeta as $meta ) :
									/**
									 * Filters whether to selectively skip post meta used for WXR exports.
									 *
									 * Returning a truthy value to the filter will skip the current meta
									 * object from being exported.
									 *
									 * @since 3.3.0
									 *
									 * @param bool   $skip     Whether to skip the current post meta. Default false.
									 * @param string $meta_key Current meta key.
									 * @param object $meta     Current meta object.
									 */
									if ( apply_filters( 'wxr_export_skip_postmeta', false, $meta->meta_key, $meta ) )
										continue;
									?>
									<wp:postmeta>
										<wp:meta_key><?php echo wps_wxr_cdata( $meta->meta_key ); ?></wp:meta_key>
										<wp:meta_value><?php echo wps_wxr_cdata( $meta->meta_value ); ?></wp:meta_value>
									</wp:postmeta>
								<?php	endforeach;

								$_comments = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpdb->comments WHERE comment_post_ID = %d AND comment_approved <> 'spam'", $post->ID ) );
								$comments = array_map( 'get_comment', $_comments );
								foreach ( $comments as $c ) : ?>
									<wp:comment>
										<wp:comment_id><?php echo intval( $c->comment_ID ); ?></wp:comment_id>
										<wp:comment_author><?php echo wps_wxr_cdata( $c->comment_author ); ?></wp:comment_author>
										<wp:comment_author_email><?php echo wps_wxr_cdata( $c->comment_author_email ); ?></wp:comment_author_email>
										<wp:comment_author_url><?php echo esc_url_raw( $c->comment_author_url ); ?></wp:comment_author_url>
										<wp:comment_author_IP><?php echo wps_wxr_cdata( $c->comment_author_IP ); ?></wp:comment_author_IP>
										<wp:comment_date><?php echo wps_wxr_cdata( $c->comment_date ); ?></wp:comment_date>
										<wp:comment_date_gmt><?php echo wps_wxr_cdata( $c->comment_date_gmt ); ?></wp:comment_date_gmt>
										<wp:comment_content><?php echo wps_wxr_cdata( $c->comment_content ) ?></wp:comment_content>
										<wp:comment_approved><?php echo wps_wxr_cdata( $c->comment_approved ); ?></wp:comment_approved>
										<wp:comment_type><?php echo wps_wxr_cdata( $c->comment_type ); ?></wp:comment_type>
										<wp:comment_parent><?php echo intval( $c->comment_parent ); ?></wp:comment_parent>
										<wp:comment_user_id><?php echo intval( $c->user_id ); ?></wp:comment_user_id>
										<?php		$c_meta = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpdb->commentmeta WHERE comment_id = %d", $c->comment_ID ) );
										foreach ( $c_meta as $meta ) :
											/**
											 * Filters whether to selectively skip comment meta used for WXR exports.
											 *
											 * Returning a truthy value to the filter will skip the current meta
											 * object from being exported.
											 *
											 * @since 4.0.0
											 *
											 * @param bool   $skip     Whether to skip the current comment meta. Default false.
											 * @param string $meta_key Current meta key.
											 * @param object $meta     Current meta object.
											 */
											if ( apply_filters( 'wxr_export_skip_commentmeta', false, $meta->meta_key, $meta ) ) {
												continue;
											}
											?>
											<wp:commentmeta>
												<wp:meta_key><?php echo wps_wxr_cdata( $meta->meta_key ); ?></wp:meta_key>
												<wp:meta_value><?php echo wps_wxr_cdata( $meta->meta_value ); ?></wp:meta_value>
											</wp:commentmeta>
										<?php		endforeach; ?>
									</wp:comment>
								<?php	endforeach; ?>
							</item>
							<?php
						}
					}
				} ?>
			</channel>
		</rss>
		<?php
	}


}