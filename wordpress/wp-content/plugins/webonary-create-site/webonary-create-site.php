<?php

/**
 * Webonary Create Site
 *
 * see readme.txt for further information
 *
 * PHP version 5.2
 *
 * LICENSE GPL v2
 *
 * @package WordPress
 * @since 3.1
 */

/*
Plugin Name: Webonary Create Site
Plugin URI: http://www.webonary.org
Description: This plugin helps with automating things when creating a new webonary site
Author: SIL International
Author URI: http://www.sil.org/
Text Domain: webonary-create-site
Version: 0.2
Stable tag: 0.1
License: GPL v2 - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/

/* @todo Change the above Plugin URI */
/* @todo Change the licensing above and below. If GPL2, see WP plugin doc about license. */

// don't load directly
if ( ! defined('ABSPATH') )
	die( '-1' );

if ( !class_exists('BlogCopier') ) {

	/**
	 * Blog Copier
	 *
	 * @package BlogCopier
	 */
	class BlogCopier {

		private $_name;
		private $_domain = 'create-webonary-site';

		/**
		 * Main constructor function
		 */
		public function __construct() {
			add_action( 'network_admin_menu', array( $this, 'ms_add_page' ) );
			add_filter( 'manage_sites_action_links', array( $this, 'add_site_action' ), 10, 2 );
		}

		/**
		 * Add admin page to network admin menu
		 */
		public function ms_add_page() {
			$this->setup_localization();
			add_submenu_page( 'sites.php', $this->_name, $this->_name, 'manage_sites', $this->_domain, array( $this, 'admin_page' ) );
		}

		/**
		 * Add "Copy Blog" link under each site in the sites list view.
		 *
		 * @param array $actions
		 * @param int $blog_id
		 * @return array $actions
		 */
		public function add_site_action( $actions, $blog_id ) {
			if( !is_main_site( $blog_id ) ) {
				$this->setup_localization();
				$url = add_query_arg( array(
						'page' => $this->_domain,
						'blog' => $blog_id
				), network_admin_url( 'sites.php' ) );
				$nonce_string = sprintf( '%s-%s', $this->_domain, $blog_id );
				$actions[$this->_domain] = '<a href="' . esc_url( wp_nonce_url( $url, $nonce_string ) ) . '">' . __( 'Copy', $this->_domain ) . '</a>';
			}

			return $actions;
		}

		/**
		 * Admin page
		 */
		public function admin_page() {
			global $wpdb, $current_site;

			if( !current_user_can( 'manage_sites' ) )
				wp_die( __( "Sorry, you don't have permissions to use this page.", $this->_domain ) );


			if(isset($_GET['remove']))
			{
				$sql = $wpdb->prepare(
						"UPDATE wp_cf7dbplugin_submits
							SET field_name = 'removed'
							WHERE field_name LIKE 'newapplication' AND submit_time = %f",
						$_GET['applicationid']);

				$wpdb->query( $sql );
			}

			if(isset($_GET['applicationid']) && !isset($_GET['remove']))
			{
				$query = "SELECT field_name, field_value FROM wp_cf7dbplugin_submits " .
				" WHERE submit_time = " . $_GET['applicationid'];

				$application = $wpdb->get_results( $query, OBJECT_K );
			}
			else
			{
				$sql = "SELECT submit_time FROM wp_cf7dbplugin_submits WHERE field_name = 'newapplication'";
				$newApplications = $wpdb->get_results($sql);

				echo "<div class='wrap'><h2>New Applications</h2></div>";

				if(count($newApplications) > 0)
				{
					echo "Click on a link to populate the 'Create Webonary Site' with the application data.<br>";

					echo "<ul>";

					foreach($newApplications as $newApplication)
					{
						$sql = "SELECT field_name, field_value FROM wp_cf7dbplugin_submits " .
								" WHERE submit_time = " . $newApplication->submit_time;

						$newSite = $wpdb->get_results( $sql, OBJECT_K);

						echo "<li>";
						echo $newSite['date_time']->field_value . " ";
						echo "<a href=\"sites.php?page=create-webonary-site&applicationid=" . $newApplication->submit_time . "\">" . $newSite['language-name']->field_value . " " . $newSite['desired-url']->field_value . "</a> (" . $newSite['from_email']->field_value . ") ";
						echo "<a href=\"sites.php?page=create-webonary-site&applicationid=" . $newApplication->submit_time . "&remove=yes\"><img src=\"" . get_bloginfo('wpurl') . "/wp-content/plugins/webonary-create-site/delete.gif\" style=\"vertical-align:text-bottom\"></a>";
						echo "</li>";
					}

					echo "</ul>";
				}
				else
				{
					echo "All applications have been processed.";
				}
			}

			$from_blog = false;
			$copy_id = 0;
			$nonce_string = sprintf( '%s-%s', $this->_domain, $copy_id );
			if( isset($_GET['blog']) && wp_verify_nonce( $_GET['_wpnonce'], $nonce_string ) ) {
				$copy_id = (int)$_GET['blog'];
				$from_blog = get_blog_details( $copy_id );
				if( $from_blog->site_id != $current_site->id ) {
					$from_blog = false;
				}
			}
			$from_blog_id = ( isset( $_POST['source_blog'] ) ) ? (int) $_POST['source_blog'] : -1;

			$created = false;

			if( isset($_POST[ 'action' ]) && $_POST[ 'action' ] == $this->_domain ) {

				//check_admin_referer( $this->_domain );
				$appPost = $_POST['blog'];
				$domain = sanitize_user( str_replace( '/', '', $appPost[ 'domain' ] ) );
				$title = $appPost[ 'title' ];
				$copy_files = (isset($_POST['copy_files']) && $_POST['copy_files'] == '1') ? true : false;

				if ( !$from_blog_id ) {
					$msg = __( 'Please select a source blog.', $this->_domain );
				} elseif ( empty( $domain ) ) {
					$msg = __( 'Please enter a "New Site Address".', $this->_domain );
				} elseif ( empty( $title ) ) {
					$msg = __( 'Please enter a "New Site Title".', $this->_domain );
				} else {
					$applicationid = "";
					if(isset($_GET['applicationid']))
					{
						$applicationid = $_GET['applicationid'];
					}
					$msg = $this->copy_blog( $domain, $title, $from_blog_id, $copy_files, $appPost['email'], $appPost['username'], $applicationid, $appPost['ethnologueCode'],  $appPost['countryName'], $appPost['copyright'], $appPost['comments'], $appPost['publicationStatus']);
					$created = true;
				}
			} else {
				$copy_files = true; // set the default for first page load
			} ?>
			<div class='wrap'><h2><?php echo $this->_name; ?></h2><?php

			if( isset( $msg ) ) { ?>
			<div id="message" class="updated fade"><p><strong><?php echo $msg; ?>
			</strong></p></div><?php
			}
			/*
			if($created == true)
			{
				break;
			}
			*/
			if( !$from_blog ) {
				$query = "SELECT b.blog_id, CONCAT(b.domain, b.path) as domain_path FROM {$wpdb->blogs} b " .
					"WHERE b.site_id = {$current_site->id} && b.blog_id > 1 ORDER BY domain_path ASC LIMIT 10000";

				$blogs = $wpdb->get_results( $query );
			}
			if( $from_blog || $blogs ) { ?>
			<div class="wrap">
				<form method="POST">
					<input type="hidden" name="action" value="<?php echo $this->_domain; ?>" />
						<table class="form-table">

							<?php if( $from_blog ) { ?>
							<tr>
								<th scope='row'><?php _e( 'Source Blog to Copy', $this->_domain ); ?></th>
								<td><strong><?php printf( '<a href="%s" target="_blank">%s</a>', $from_blog->siteurl, $from_blog->blogname ); ?></strong>
								<input type="hidden" name="source_blog" value="<?php echo $copy_id; ?>" />
								</td>
							</tr>
							<?php } else { ?>
							<tr class="form-required">
								<th scope='row'><?php _e( 'Choose Source Site to Copy', $this->_domain ); ?></th>
								<td>
									<?php
									$template = "template-english.webonary.org";
									if(isset($application['template-to-use']->field_value))
									{
										preg_match('!https?://\S+!', $application['template-to-use']->field_value, $hrefs);
										$template = $hrefs[0];

										$template = str_replace("http://", "", $template);
										$template = str_replace("https://", "", $template);
									}
									?>
									<select name="source_blog">
										<option value=""></option>
									<?php foreach( $blogs as $blog ) { ?>
										<option value="<?php echo $blog->blog_id; ?>"  <?php selected( $blog->blog_id, $from_blog_id ); ?> <?php if(substr($blog->domain_path, 0, -1) == $template) { echo "selected"; } ?>><?php echo substr($blog->domain_path, 0, -1); ?></option>
									<?php } ?>
									</select>
								</td>
							</tr>
							<tr>
								<td>Other template: </td>
								<td><?php echo $application['other-template']->field_value; ?></td>
							<?php } ?>

							<tr class="form-required">
								<th scope='row'><?php _e( 'New Site Address', $this->_domain ); ?></th>
								<td>
								<?php
								$desired_url = "";
								if(isset($_POST['blog']))
								{
									$desired_url = $appPost['domain'];
								}
								else if(isset($application['desired-url']->field_value))
								{
									$desired_url = str_replace("http://", "", $application['desired-url']->field_value);
									$desired_url = str_replace("https://", "", $desired_url);
									$desired_url = str_replace(".webonary.org", "", $desired_url);
								}
								if( is_subdomain_install() ) { ?>
									<input name="blog[domain]" size="40" type="text" title="<?php _e( 'Subdomain', $this->_domain ); ?>" value="<?php echo $desired_url; ?>"/>.<?php echo "webonary.org";?>
								<?php } else {
									echo $_SERVER['HTTP_HOST'] . $current_site->path ?><input name="blog[domain]" size="40" type="text" title="<?php _e( 'Domain', $this->_domain ); ?>" value="<?php echo $desired_url; ?>"/>
								<?php } ?>
								</td>
							</tr>

							<tr class="form-required">
								<th scope='row'><?php _e( 'New Site Title', $this->_domain ); ?></th>
								<td>
								<?php
								$site_title = "";
								if(isset($_POST['blog']))
								{
									$site_title = $appPost['title'];
								}
								else if(isset($application['language-name']->field_value))
								{
									if(strpos($application['template-to-use']->field_value, "french") > 0)
									{
										$site_title = "Dictionnaire " . $application['language-name']->field_value;
									}
									else if(strpos($application['template-to-use']->field_value, "spanish") > 0)
									{
										$site_title = "Diccionario " . $application['language-name']->field_value;
									}
									else
									{
										$site_title = $application['language-name']->field_value . " Dictionary";
									}
								}
								?>
								<input name="blog[title]" size="50" type="text" title="<?php _e( 'Title', $this->_domain ); ?>" value="<?php echo $site_title; ?>"/></td>
							</tr>
							<?php
							$ethnologueCode = "";
							if(isset($_POST['blog']))
							{
								$ethnologueCode = $appPost['ethnologueCode'];
							}
							else if(isset($application['language-iso-code']->field_value))
							{
								$ethnologueCode =  $application['language-iso-code']->field_value;
							}
							?>
							<tr class="form-required">
								<th scope='row'><?php _e( 'Ethnologue Code', $this->_domain ); ?></th>
								<td><input name="blog[ethnologueCode]" type="text" title="<?php _e( 'Ethnologue Code', $this->_domain ); ?>" value="<?php echo $ethnologueCode; ?>"/>
								<br>
								The following menu links will be created: <a href="https://www.ethnologue.com/language/<?php echo $ethnologueCode; ?>" target="_blank">https://www.ethnologue.com/language/<?php echo $ethnologueCode; ?></a> /
								<a href="https://www.sil.org/search/node/<?php echo $ethnologueCode; ?>" target="_blank">https://www.sil.org/search/node/<?php echo $ethnologueCode; ?></a>
								</td>
							</tr>

							<?php
							$countryName = "";
							if(isset($_POST['blog']))
							{
								$countryName = $appPost['countryName'];
							}
							else if(isset($application['country-name']->field_value))
							{
								$countryName =  $application['country-name']->field_value;
							}
							?>
							<tr class="form-required">
								<th scope='row'><?php _e( 'Country', $this->_domain ); ?></th>
								<td><input name="blog[countryName]" type="text" value="<?php echo $countryName; ?>"/></td>
							</tr>

							<?php
							$copyright = "";
							if(isset($_POST['blog']))
							{
								$copyright = $appPost['copyright'];
							}
							else if(isset($application['copyright-holder']->field_value))
							{
								$copyright =  $site_title . " © " . date('Y') . " " . $application['copyright-holder']->field_value . "<sup>®</sup>";
							}
							?>
							<tr class="form-required">
								<th scope='row'><?php _e( 'Copyright footer text:', $this->_domain ); ?></th>
								<td><input name="blog[copyright]" size="70" type="text" title="<?php _e( 'Copyright footer', $this->_domain ); ?>" value="<?php echo $copyright; ?>"/></td>
							</tr>

							<?php
							$publicationStatus = 0;
							if(isset($_POST['blog']))
							{
								$publicationStatus = $appPost['publicationStatus'];
							}
							else if(isset($application['the-publication-status-of-the-dictionary']->field_value))
							{
								$publicationStatus = $application['the-publication-status-of-the-dictionary']->field_value;
							}
							?>
							<tr class="form-required">
								<th scope='row'><?php _e( 'Publication status:', $this->_domain ); ?></th>
								<td>
								<select name="blog[publicationStatus]">
									<option value="0" <?php if($publicationStatus == 0 || $publicationStatus == "") { echo "selected"; }?>></option>
									<option value="1" <?php if($publicationStatus == 1 || $publicationStatus == "Rough draft") { echo "selected"; }?>>Rough draft</option>
									<option value="2" <?php if($publicationStatus == 2 || $publicationStatus == "Self-reviewed draft") { echo "selected"; }?>>Self-reviewed draft</option>
									<option value="3" <?php if($publicationStatus == 3 || $publicationStatus == "Community-reviewed draft") { echo "selected"; }?>>Community-reviewed draft</option>
									<option value="4" <?php if($publicationStatus == 4 || $publicationStatus == "Consultant approved") { echo "selected"; }?>>Consultant approved</option>
									<option value="5" <?php if($publicationStatus == 5 || $publicationStatus == "Finished (no formal publication)") { echo "selected"; }?>>Finished (no formal publication)</option>
									<option value="6" <?php if($publicationStatus == 6 || $publicationStatus == "Formally published") { echo "selected"; }?>>Formally published</option>
								</select></td>
							</tr>

							<?php
							$comments = "yes";
							if(isset($_POST['blog']))
							{
								if(!isset($appPost['comments']))
								{
									$comments = "no";
								}
							}
							else if(isset($application['allow-comments']->field_value))
							{
								$comments = $application['allow-comments']->field_value;
							}
							?>
							<tr class="form-required">
								<th scope='row'><?php _e( 'Allow comments:', $this->_domain ); ?></th>
								<td>
								<input name="blog[comments]" type="checkbox" value="<?php echo $comments; ?>" <?php if(strtolower($comments) == "yes") { echo "checked=\"checked\""; }?>/></td>
							</tr>

							<?php
							$admin_email = "";
							if(isset($_POST['blog']))
							{
								$admin_email = $appPost['email'];
							}
							else if(isset($application['from_email']->field_value))
							{
								$admin_email =  $application['from_email']->field_value;
							}
							?>
							<tr class="form-required">
								<th scope='row'><?php _e( 'Admin Email', $this->_domain ); ?></th>
								<td><input name="blog[email]" size="40" type="text" title="<?php _e( 'Email', $this->_domain ); ?>" value="<?php echo $admin_email; ?>"/></td>
							</tr>
							<?php
							$username = "";
							if(isset($_POST['blog']))
							{
								$username = $appPost['username'];
							}
							else if(isset($application['from_name']->field_value))
							{
								$username = $wpdb->get_var("SELECT user_login FROM wp_users WHERE user_email = '" . $admin_email . "'");

								if($username == NULL)
								{
									$username =  str_replace(' ', '', strtolower($application['from_name']->field_value));

									$otherEmail = $wpdb->get_var("SELECT user_email FROM wp_users WHERE user_login = '" . $username . "'");
								}
							}
							?>
							<tr class="form-required">
								<th scope='row'><?php _e( 'Username', $this->_domain ); ?></th>
								<td>
								<?php
								if(isset($otherEmail))
								{
									echo "<span style=\"color:red\">This username already exists with another email address: " . $otherEmail . "<br>Please change the username.</span><br>";
								}
								?>
								<input name="blog[username]" size="40" type="text" title="<?php _e( 'Username', $this->_domain ); ?>" value="<?php echo $username; ?>"/></td>
							</tr>
						</table>
						<hr>
						<b>Comments:</b> <?php echo $application['message']->field_value; ?>
						<p>
						<b>Display Languages (need to be enabled manually):</b> <?php echo $application['ui-languages']->field_value; ?>
						<hr>
						<p>
						An email will be sent to the user informing him about his new site. See <a href="/wp-admin/network/settings.php">Welcome Email</a>.
					<p class="submit"><input class='button' type='submit' value='<?php _e( 'Create Now', $this->_domain ); ?>' /></p>
					<input type="hidden" name="copy_files" value="1" <?php checked( $copy_files ); ?>/>
				</form></div>
			<?php } else { ?>
				<div class="wrap">
					<h3><?php _e( 'Oops!', $this->_domain ); ?></h3>
					<p><?php
					printf( __( 'This plugin only works on subblogs. To use this you\'ll need to <a href="%s">create at least one subblog</a>.', $this->_domain ), network_admin_url( 'site-new.php' ) );
					?></p>
				</div>
			<?php }
		}

		/**
		 * Copy the blog
		 *
		 * @param string $domain url of the new blog
		 * @param string $title title of the new blog
		 * @param int $from_blog_id ID of the blog being copied from.
		 * @param bool $copy_files true if files should be copied
		 * @return string status message
		 */
		public function copy_blog($domain, $title, $from_blog_id = 0, $copy_files = true, $email, $username, $applicationid, $ethnologueCode, $countryName, $copyright, $allow_comments, $publicationStatus) {
			global $wpdb, $current_site, $base;

			$admin_email = get_blog_option( $from_blog_id, 'admin_email' );
			$admin_user_id = email_exists( sanitize_email( $admin_email ) );
			if( !$admin_user_id ) {
				// Use current user instead
				$admin_user_id = get_current_user_id();
			}

			// The user id of the user that will become the blog admin of the new blog.
			//$user_id = apply_filters('copy_blog_user_id', $user_id, $from_blog_id);
			$user_id = $wpdb->get_var("SELECT ID FROM wp_users WHERE user_login = '" . $username . "'");

			$password = "Your existing password";
			if($user_id == NULL)
			{
				do_action( 'pre_network_site_new_created_user', $email );
				$password = wp_generate_password( 12, false );
				$user_id = wpmu_create_user( $username, $password, $email );
				do_action( 'network_site_new_created_user', $user_id );
			}

			if( is_subdomain_install() ) {
				$newdomain = $domain."."."webonary.org";
				$path = $base;
			} else {
				$newdomain = $_SERVER['HTTP_HOST'];
				//$path = trailingslashit( $base ) . trailingslashit( $domain );
				$path = "/" . $domain . "/";
			}

			// The new domain that will be created for the destination blog.
			$newdomain = apply_filters('copy_blog_domain', $newdomain, $domain);

			// The new path that will be created for the destination blog.
			$path = apply_filters('copy_blog_path', $path, $domain);

			$wpdb->hide_errors();
			$to_blog_id = wpmu_create_blog( $newdomain, $path, $title, $user_id , array( "public" => 1 ), $current_site->id );
			$wpdb->show_errors();

			$msg = "";
			if( !is_wp_error( $to_blog_id ) ) {
				wpmu_welcome_notification( $to_blog_id, $user_id, $password, $title, array( 'public' => 1 ) );

				$dashboard_blog = get_dashboard_blog();
				if( !is_super_admin() && get_user_option( 'primary_blog', $user_id ) == $dashboard_blog->blog_id )
				{
					update_user_option( $user_id, 'primary_blog', $to_blog_id, true );
				}

				// now copy
				if( $from_blog_id ) {

					$this->copy_blog_data( $from_blog_id, $to_blog_id );

					if ($copy_files) {

						$this->copy_blog_files( $from_blog_id, $to_blog_id );
						$this->replace_content_urls( $from_blog_id, $to_blog_id );

					}

					switch_to_blog( $to_blog_id );

					$user = new WP_User($user_id);
					$user->set_role('editor');

					//Set site admin email address (comments will be sent to that email)
					$sql = "UPDATE  $wpdb->options SET option_value = '". $email . "' WHERE option_name = 'admin_email'";
					$wpdb->query( $sql );

					//Sets domain to https
					//20200207 chungh: Webonary.org reconfig to use subdirectory
					//$sql = "UPDATE  $wpdb->options SET option_value = 'https://". $domain . ".webonary.org' WHERE option_name = 'siteurl'";
					$sql = "UPDATE  $wpdb->options SET option_value = 'https://". $newdomain . $path . "' WHERE option_name = 'siteurl'";
					$wpdb->query( $sql );


					//Set contact form email address
					/*
					$contactOpt = get_option('fs_contact_form1');
					$contactOpt['email_to'] = $email;
					update_option('fs_contact_form1', $contactOpt);
					*/

					$sql = "SELECT ID FROM  wp_" . $to_blog_id . "_posts WHERE post_type = 'wpcf7_contact_form' ORDER BY ID DESC";

					$insert_id =  $wpdb->get_var($sql);

					$siteurl = get_blog_option( $to_blog_id, 'siteurl' );

					//20200207 chungh: use wordpres@webonary.org as the email To address, rather than dictionary subdomain
					//$siteshort = str_replace(("https://"), "", $siteurl);
					//$siteshort = str_replace(".webonary.org", "", $siteshort);

					$contactOpt = "a:9:{s:6:\"active\";b:1;s:7:\"subject\";s:20:\"[text* your-subject]\";s:6:\"sender\";s:" .strlen("[your-name] <wordpress@webonary.org>") . ":\"[your-name] <wordpress@webonary.org>\";s:9:\"recipient\";s:" . strlen($email) . ":\"" . $email . "\";s:4:\"body\";s:" . strlen("From: [your-name] <[your-email]>\nSubject: [your-subject]\n\nMessage Body:\n[your-message]\n\n-- \nThis e-mail was sent from a contact form on " . $siteurl . "") . ":\"From: [your-name] <[your-email]>\nSubject: [your-subject]\n\nMessage Body:\n[your-message]\n\n-- \nThis e-mail was sent from a contact form on " . $siteurl . "\";s:18:\"additional_headers\";s:22:\"Reply-To: [your-email]\";s:11:\"attachments\";s:0:\"\";s:8:\"use_html\";b:0;s:13:\"exclude_blank\";b:0;}";

					$sql = "UPDATE wp_" . $to_blog_id . "_postmeta SET meta_value = '" . $contactOpt . "' WHERE meta_key = '_mail'";

					$wpdb->query( $sql );

					//Set ethnologue link
					$meta_id = $wpdb->get_var("SELECT meta_id FROM " . $wpdb->postmeta . " WHERE meta_key = '_menu_item_url' AND meta_value LIKE '%www.ethnologue.com/language%'");

					//Set footer (copyright)
					$themeZeeOpt = get_option('themezee_options');
					$themeZeeOpt['themeZee_footer'] = $copyright;
					update_option('themezee_options', $themeZeeOpt);

					//Set the ethnologue menu link
					$setEthnologueCode = false;
					if($meta_id != NULL)
					{
						$sql = "UPDATE " . $wpdb->postmeta .
						   " SET meta_value = 'https://www.ethnologue.com/language/" . $ethnologueCode . "' " .
						   " WHERE meta_id = " . $meta_id ;

						$wpdb->query( $sql );
						$setEthnologueCode = true;
					}

					//Set Bibliography Link
					//Set ethnologue link
					$bibliography_linkid = $wpdb->get_var("SELECT meta_id FROM " . $wpdb->postmeta . " WHERE meta_key = '_menu_item_url' AND meta_value LIKE '%www.sil.org/search/node%'");
					$setBibliography = false;
					if($bibliography_linkid != NULL)
					{
						$sql = "UPDATE " . $wpdb->postmeta .
						" SET meta_value = 'https://www.sil.org/search/node/" . $ethnologueCode . "' " .
						" WHERE meta_id = " . $bibliography_linkid ;

						$wpdb->query( $sql );
						$setBibliography = true;
					}

					//Set country name
					update_option('countryName', $countryName);

					//Set publication status
					update_option('publicationStatus', $publicationStatus);

					//Set allow comments
					$commentStatus = "";
					if(strtolower($allow_comments) == "yes")
					{
						update_option('default_comment_status', "open");
					}
					else
					{
						update_option('default_comment_status', "closed");
						$allow_comments = "no";
					}

					//20200207 chungh: Webonary.org reconfig to use subdirectory
										//$msg .= sprintf(__( 'Copied: %s in %s seconds', $this->_domain ),'<a href="https://'.$newdomain.'" target="_blank">'.$title.'</a>', number_format_i18n(timer
					$msg .= sprintf(__( 'Copied: %s in %s seconds', $this->_domain ),'<a href="'.$siteurl.'" target="_blank">'.$title.'</a>', number_format_i18n(timer_stop())) . "<br>";
					$msg .= "- Welcome email sent to: " . $email . "<br>";
					$msg .= "- Set this email as contact person for contact form.<br>";
					if($setEthnologueCode)
					{
						$msg .= "- Set ethnologue link to https://www.ethnologue.com/language/" . $ethnologueCode . "<br>";
					}
					if($setBibliography)
					{
						$msg .= "- Set bibliography link to https://www.sil.org/search/node/" . $ethnologueCode . "<br>";
					}
					$msg .= "- Set the copyright text in footer<br>";
					$msg .= "- Set the Publication status<br>";
					$msg .= "- Allow comments was set to '" . $allow_comments . "'<br>";

					do_action( 'log', __( 'Copy Complete!', $this->_domain ), $this->_domain, $msg );
					do_action( 'copy_blog_complete', $from_blog_id, $to_blog_id );


					$sql = $wpdb->prepare(
							"UPDATE wp_cf7dbplugin_submits
							SET field_name = 'created'
							WHERE field_name = 'newapplication' AND submit_time = %f",
							$applicationid);

					$wpdb->query( $sql );
				}
			} else {
				$msg = $to_blog_id->get_error_message();
			}
			return $msg;
		}

		/**
		 * Copy blog data from one blog to another
		 *
		 * @param int $from_blog_id ID of the blog being copied from.
		 * @param int $to_blog_id ID of the blog being copied to.
		 */
		private function copy_blog_data( $from_blog_id, $to_blog_id ) {
			global $wpdb, $wp_version;
			if( $from_blog_id ) {
				$from_blog_prefix = $this->get_blog_prefix( $from_blog_id );
				$to_blog_prefix = $this->get_blog_prefix( $to_blog_id );
				$from_blog_prefix_length = strlen($from_blog_prefix);
				$to_blog_prefix_length = strlen($to_blog_prefix);
				$from_blog_escaped_prefix = str_replace( '_', '\_', $from_blog_prefix );

				// Grab key options from new blog.
				$saved_options = array(
					'siteurl'=>'',
					'home'=>'',
					'upload_path'=>'',
					'fileupload_url'=>'',
					'upload_url_path'=>'',
					'admin_email'=>'',
					'blogname'=>''
				);
				// Options that should be preserved in the new blog.
				$saved_options = apply_filters('copy_blog_data_saved_options', $saved_options);
				foreach($saved_options as $option_name => $option_value) {
					$saved_options[$option_name] = get_blog_option( $to_blog_id, $option_name );
				}

				// Copy over ALL the tables.
				$query = $wpdb->prepare('SHOW TABLES LIKE %s',$from_blog_escaped_prefix.'%');
				do_action( 'log', $query, $this->_domain);
				$old_tables = $wpdb->get_col($query);

				foreach ($old_tables as $k => $table) {
					$raw_table_name = substr( $table, $from_blog_prefix_length );
					$newtable = $to_blog_prefix . $raw_table_name;

					$query = "DROP TABLE IF EXISTS {$newtable}";
					do_action( 'log', $query, $this->_domain);
					$wpdb->get_results($query);

					$query = "CREATE TABLE IF NOT EXISTS {$newtable} LIKE {$table}";
					do_action( 'log', $query, $this->_domain);
					$wpdb->get_results($query);

					$query = "INSERT {$newtable} SELECT * FROM {$table}";
					do_action( 'log', $query, $this->_domain);
					$wpdb->get_results($query);
				}

				switch_to_blog( $to_blog_id );

				// caches will be incorrect after direct DB copies
				wp_cache_delete( 'notoptions', 'options' );
				wp_cache_delete( 'alloptions', 'options' );

				// apply key options from new blog.
				foreach( $saved_options as $option_name => $option_value ) {
					//if using update_option function for admin_email it will send an email to the original site owner
					if($option_name == "admin_email")
					{
						$sql = "UPDATE wp_" . $to_blog_id . "_options " .
							" SET option_value = '" . $option_value . "' " .
							" WHERE option_name = 'admin_email'";

						$wpdb->query( $sql );

					}
					else
					{
						update_option( $option_name, $option_value );
					}
				}

				/// fix all options with the wrong prefix...
				$query = $wpdb->prepare("SELECT * FROM {$wpdb->options} WHERE option_name LIKE %s",$from_blog_escaped_prefix.'%');
				$options = $wpdb->get_results( $query );
				do_action( 'log', $query, $this->_domain, count($options).' results found.');
				if( $options ) {
					foreach( $options as $option ) {
						$raw_option_name = substr($option->option_name,$from_blog_prefix_length);
						$wpdb->update( $wpdb->options, array( 'option_name' => $to_blog_prefix . $raw_option_name ), array( 'option_id' => $option->option_id ) );
					}

					// caches will be incorrect after direct DB copies
					wp_cache_delete( 'notoptions', 'options' );
					wp_cache_delete( 'alloptions', 'options' );
				}

				// Fix GUIDs on copied posts
				$this->replace_guid_urls( $from_blog_id, $to_blog_id );

				restore_current_blog();
			}
		}

		/**
		 * Copy files from one blog to another.
		 *
		 * @param int $from_blog_id ID of the blog being copied from.
		 * @param int $to_blog_id ID of the blog being copied to.
		 */
		private function copy_blog_files( $from_blog_id, $to_blog_id ) {
			set_time_limit( 2400 ); // 60 seconds x 10 minutes
			@ini_set('memory_limit','2048M');

			// Path to source blog files.
			switch_to_blog($from_blog_id);
			$dir_info = wp_upload_dir();
			$from = str_replace(' ', "\\ ", trailingslashit($dir_info['basedir']).'*'); // * necessary with GNU cp, doesn't hurt anything with BSD cp
			restore_current_blog();
			$from = apply_filters('copy_blog_files_from', $from, $from_blog_id);

			// Path to destination blog files.
			switch_to_blog($to_blog_id);
			$dir_info = wp_upload_dir();
			$to = str_replace(' ', "\\ ", trailingslashit($dir_info['basedir']));
			restore_current_blog();
			$to = apply_filters('copy_blog_files_to', $to, $to_blog_id);

			// Shell command used to copy files.
			$command = apply_filters('copy_blog_files_command', sprintf("cp -Rfp %s %s", $from, $to), $from, $to );
			exec($command);
		}

		/**
		 * Replace URLs in post content
		 *
		 * @param int $from_blog_id ID of the blog being copied from.
		 * @param int $to_blog_id ID of the blog being copied to.
		 */
		private function replace_content_urls( $from_blog_id, $to_blog_id ) {
			global $wpdb;
			$to_blog_prefix = $this->get_blog_prefix( $to_blog_id );
			$from_blog_url = get_blog_option( $from_blog_id, 'siteurl' );
			$to_blog_url = get_blog_option( $to_blog_id, 'siteurl' );
			$query = $wpdb->prepare( "UPDATE {$to_blog_prefix}posts SET post_content = REPLACE(post_content, '%s', '%s')", $from_blog_url, $to_blog_url );
			do_action( 'log', $query, $this->_domain);
			$wpdb->query( $query );
		}

		/**
		 * Replace URLs in post GUIDs
		 *
		 * @param int $from_blog_id ID of the blog being copied from.
		 * @param int $to_blog_id ID of the blog being copied to.
		 */
		private function replace_guid_urls( $from_blog_id, $to_blog_id ) {
			global $wpdb;
			$to_blog_prefix = $this->get_blog_prefix( $to_blog_id );
			$from_blog_url = get_blog_option( $from_blog_id, 'siteurl' );
			$to_blog_url = get_blog_option( $to_blog_id, 'siteurl' );
			$query = $wpdb->prepare( "UPDATE {$to_blog_prefix}posts SET guid = REPLACE(guid, '%s', '%s')", $from_blog_url, $to_blog_url );
			do_action( 'log', $query, $this->_domain);
			$wpdb->query( $query );
		}

		/**
		 * Get the database prefix for a blog
		 *
		 * @param int $blog_id ID of the blog.
		 * @return string prefix
		 */
		private function get_blog_prefix( $blog_id ) {
			global $wpdb;
			if( is_callable( array( &$wpdb, 'get_blog_prefix' ) ) ) {
				$prefix = $wpdb->get_blog_prefix( $blog_id );
			} else {
				$prefix = $wpdb->base_prefix . $blog_id . '_';
			}
			return $prefix;
		}

		/**
		 * Load the localization file
		 */
		private function setup_localization() {
			if ( !isset( $this->_name ) ) {
				load_plugin_textdomain( $this->_domain, false, trailingslashit(dirname(__FILE__)) . 'lang/');
				$this->_name = __( 'Create Webonary Site', $this->_domain );
			}
		}

	}

	global $BlogCopier;
	$BlogCopier = new BlogCopier();
}

function add_link_action($linkid)
{
	global $wpdb;

	$sql = "SELECT blog_id, link_url, link_name
	FROM wp_links
	INNER JOIN wp_term_relationships ON  wp_term_relationships.object_id = wp_links.link_id
	INNER JOIN wp_blogs ON wp_blogs.domain = replace(replace(wp_links.link_url, 'https://',''),'/','')
	WHERE link_id = " . $linkid . " AND wp_term_relationships.term_taxonomy_id = 8";

	$blog = $wpdb->get_results ($sql, ARRAY_A);

	$query = "SELECT option_value FROM wp_" . $blog[0]['blog_id'] . "_options WHERE option_name LIKE 'admin_email'";

	$admin_email = $wpdb->get_var( $query );

	$msg = "Congratulations! The " . $blog[0]['link_name'] . " dictionary has been published on https://www.webonary.org/. In a few days it will appear in the Open Language Archives Community catalogue, http://www.language-archives.org/archive/webonary.org.\n\n" .
	"If you have any questions or concerns, please reply to this email.\n\n" .
	"Thank you for giving us the privilege of serving you.\n\n" .
	"The Webonary team\n";

	$headers[] = 'From: Webonary <webonary@sil.org>';
	$headers[] = 'Bcc: Webonary <webonary@sil.org>';
	wp_mail($admin_email, 'Webonary Dictionary got published', $msg, $headers);
}
add_action( 'add_link', 'add_link_action');

//20200207 chungh: validate subdirectory name
add_action( 'wpcf7_init', 'custom_add_form_tag_subdirectory' );
 
function custom_add_form_tag_subdirectory() {
	wpcf7_add_form_tag( 'subdirectory*', 'custom_subdirectory_form_tag_handler', array( 'name-attr' => true ) );
}

function custom_subdirectory_form_tag_handler( $tag ) {
	if ( empty( $tag->name ) ) {
		return '';
	}

	$validation_error = wpcf7_get_validation_error( $tag->name );

	$class = wpcf7_form_controls_class( $tag->type );

	if ( $validation_error ) {
		$class .= ' wpcf7-not-valid';
	}

	$atts = array();

	$atts['class'] = $tag->get_class_option( $class );
	$atts['id'] = $tag->get_id_option();
	$atts['tabindex'] = $tag->get_option( 'tabindex', 'signed_int', true );
	$atts['min'] = $tag->get_option( 'min', 'signed_int', true );
	$atts['max'] = $tag->get_option( 'max', 'signed_int', true );
	$atts['step'] = $tag->get_option( 'step', 'int', true );

	if ( $tag->is_required() ) {
		$atts['aria-required'] = 'true';
	}

	$atts['aria-invalid'] = $validation_error ? 'true' : 'false';

	$value = (string) reset( $tag->values );

	if ( $tag->has_option( 'placeholder' )
	or $tag->has_option( 'watermark' ) ) {
		$atts['placeholder'] = $value;
		$value = '';
	}

	$value = $tag->get_default_option( $value );

	$value = wpcf7_get_hangover( $tag->name, $value );

	$atts['value'] = $value;

	$atts['type'] = 'text';

	$atts['name'] = $tag->name;

	$atts = wpcf7_format_atts( $atts );

	$html = sprintf(
		'<span class="wpcf7-form-control-wrap %1$s"><input %2$s />%3$s</span>',
		sanitize_html_class( $tag->name ), $atts, $validation_error );

	return $html;
}

add_filter( 'wpcf7_validate_subdirectory*', 'custom_subdirectory_validation_filter', 20, 2 );
function custom_subdirectory_validation_filter($result, $tag)
{

	if( !preg_match('/^[a-z0-9_-]+$/' , $_POST[$tag->name]) ) {
		$result->invalidate( $tag, "Please use only lowercase letters a through z, numbers, dashes, or underscores." );
	}

	return $result;
}

// overwrites the wp_new_user_notification in includes/pluggable, so that no email with password reset gets sent out
if( !function_exists ( 'wp_new_user_notification')){
	function wp_new_user_notification ( $user_id, $notify = '' ) { }
}

?>
