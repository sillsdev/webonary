<?php

class WebonaryBlogCopier
{
	private ?string $_name;
	private string $_domain = 'create-webonary-site';


	/**
	 * Main constructor function
	 */
	public function __construct()
	{
		add_action('network_admin_menu', array($this, 'ms_add_page'));
		add_filter('manage_sites_action_links', array($this, 'add_site_action'), 10, 2);
	}

	/**
	 * Add admin page to network admin menu
	 */
	public function ms_add_page(): void
	{
		$this->setup_localization();
		add_submenu_page('sites.php', $this->_name, $this->_name, 'manage_sites', $this->_domain, array($this, 'admin_page'));
	}

	/**
	 * Load the localization file
	 */
	private function setup_localization(): void
	{
		if (!isset($this->_name)) {
			load_plugin_textdomain($this->_domain, false, trailingslashit(dirname(__FILE__)) . 'lang/');
			$this->_name = __('Create Webonary Site', $this->_domain);
		}
	}

	/**
	 * Add "Copy Blog" link under each site in the sites list view.
	 *
	 * @param array $actions
	 * @param int $blog_id
	 * @return array $actions
	 */
	public function add_site_action(array $actions, int $blog_id): array
	{
		if (is_main_site($blog_id))
			return $actions;

		$this->setup_localization();
		$url = add_query_arg(array(
			'page' => $this->_domain,
			'blog' => $blog_id
		), network_admin_url('sites.php'));
		$nonce_string = sprintf('%s-%s', $this->_domain, $blog_id);
		$actions[$this->_domain] = '<a href="' . esc_url(wp_nonce_url($url, $nonce_string)) . '">' . __('Copy', $this->_domain) . '</a>';

		return $actions;
	}

	private function get_desired_site_name(string $desired_url): string
	{
		$desired_url = preg_replace('/(https?:\/\/(www\.)?)/', '', $desired_url);
		$desired_url = str_replace('webonary.org', '', $desired_url);
		return trim($desired_url, "./ \t\n\r\0\x0B");
	}

	/**
	 * Shows a list of applications that can be processed
	 *
	 * @return void
	 */
	private function show_applications(): void
	{
		global $wpdb;

		$sql = <<<SQL
SELECT submit_time
FROM wp_cf7dbplugin_submits
WHERE field_name = 'newapplication'
SQL;
		$newApplications = $wpdb->get_results($sql);

		$lines = [];

		if (count($newApplications) == 0) {
			$lines[] = '<p>All applications have been processed.</p>';
		}
		else {
			$lines[] = '<p>Click on a link to populate the "Create Webonary Site" with the application data.</p>';
			$lines[] = '<ul>';

			$blog_url = get_bloginfo('wpurl');
			/** @noinspection HtmlUnknownTarget */
			$li_template = <<<'HTML'
<li>%1$s
<a href="sites.php?page=create-webonary-site&applicationid=%2$s">%3$s</a> %4$s (%5$s)
<a href="sites.php?page=create-webonary-site&applicationid=%2$s&remove=yes"><img src="%6$s/wp-content/plugins/webonary-create-site/delete.gif" style="vertical-align:text-bottom" alt=""></a>
</li>
HTML;

			foreach ($newApplications as $newApplication) {
				$sql = <<<SQL
SELECT field_name, field_value FROM wp_cf7dbplugin_submits
WHERE submit_time = $newApplication->submit_time
SQL;
				$newSite = $wpdb->get_results($sql, OBJECT_K);
				$timestamp = $newSite['date_time']->field_value ?? date('D, j M Y H:i:s', $newApplication->submit_time);
				$desired_name = $this->get_desired_site_name($newSite['desired-url']->field_value ?? '');

				$lines[] = sprintf(
					$li_template,
                    $timestamp,
					$newApplication->submit_time,
					$newSite['language-name']->field_value ?? '',
					$desired_name,
					$newSite['from_email']->field_value ?? '',
					$blog_url
				);
			}

			$lines[] = '</ul>';
		}

		$lines_html = implode(PHP_EOL, $lines);

		echo <<<HTML
<div class="wrap">
    <h2>New Applications</h2>
    $lines_html
</div>
HTML;
	}

    private function get_no_blog_selected(): string
    {
        $oops_h3 = __('Oops!', $this->_domain);
		/** @noinspection HtmlUnknownTarget */
		$oops_msg = printf(__('This plugin only works on subblogs. To use this you\'ll need to <a href="%s">create at least one subblog</a>.', $this->_domain), network_admin_url('site-new.php'));

        return <<<HTML
<div class="wrap">
	<h3>$oops_h3</h3>
	<p>$oops_msg</p>
</div>
HTML;
    }

	private function get_messages(array $msg): string
    {
		if (empty($msg))
			return '';

		$msg_html = '<p>' . implode('<br>' . PHP_EOL, $msg) . '</p>';

		return <<<HTML
<div class='wrap'>
	$msg_html
</div>
HTML;
	}

	private function get_from_blog_row($from_blog, $copy_id): string
	{
		$source_msg = __('Source Blog to Copy', $this->_domain);
		/** @noinspection HtmlUnknownTarget */
		$source_desc = sprintf('<a href="%s" target="_blank">%s</a>', $from_blog->siteurl, $from_blog->blogname);

		return <<<HTML
<tr>
	<th scope='row'>$source_msg</th>
	<td>
	    <strong>$source_desc</strong>
	    <input type="hidden" name="source_blog" value="$copy_id">
	</td>
</tr>
HTML;
	}

	private function get_choose_site_to_copy(array $blogs, $application, $from_blog_id): string
	{
		$choose_msg = __('Choose Source Site to Copy', $this->_domain);

		$template = 'template-english';
		if (isset($application['template-to-use']->field_value)) {
			$found = preg_match('!https?://.+/(\S+)!', $application['template-to-use']->field_value, $hrefs);
			if ($found !== false)
				$template = $hrefs[1];
		}

		$blogs_html = '';
		/** @noinspection HtmlUnknownAttribute */
		$option_template = '<option value="%1$s" %2$s>%3$s</option>';
		foreach ($blogs as $blog) {

			$selected = selected($blog->blog_id, $from_blog_id, false);
			if (!$selected)
				$selected = ($blog->path == $template) ? 'selected' : '';

			$blogs_html .= sprintf($option_template, $blog->blog_id, $selected, $blog->path) . PHP_EOL;
		}

		return <<<HTML
<tr class="form-required">
    <th scope='row'>$choose_msg</th>
    <td>
		<select name="source_blog">
			<option value=""></option>
			$blogs_html
		</select>
	</td>
</tr>
<tr>
    <th>Other template</th>
    <td>{$application['other-template']->field_value}</td>
</tr>
HTML;
	}

	private function get_desired_url(array $appPost, array $application, string $current_site_path): string
	{
		$label = __('New Site Address', $this->_domain);

		$desired_url = '';
		if(!empty($appPost))
			$desired_url = $appPost['domain'];
		elseif(isset($application['desired-url']->field_value))
			$desired_url = $this->get_desired_site_name($application['desired-url']->field_value);

		$sub_domain = __('Subdomain', $this->_domain);
		$td_content = sprintf('<input name="blog[domain]" size="40" type="text" title="%1$s" value="%2$s">', $sub_domain, $desired_url);
		if (is_subdomain_install()) {
			$td_content .= '.' . $_SERVER['HTTP_HOST'];
		}
		else {
			$td_content = $_SERVER['HTTP_HOST'] . $current_site_path . $td_content;
		}

		return <<<HTML
<tr class="form-required">
    <th scope='row'>$label</th>
    <td>$td_content</td>
</tr>
HTML;
	}

	private function get_new_site_title(string $site_title): string
	{
		$site_title_label = __('New Site Title', $this->_domain);
		$title_label = __('Title', $this->_domain);

		return <<<HTML
<tr class="form-required">
    <th scope='row'>$site_title_label</th>
    <td>
		<input name="blog[title]" size="50" type="text" title="$title_label" value="$site_title"/>
	</td>
</tr>
HTML;
	}

	private function get_ethnologue_code(array $appPost, array $application): string
	{
		$label = __('Ethnologue Code', $this->_domain);

		$ethnologueCode = '';
		if (isset($_POST['blog']))
			$ethnologueCode = $appPost['ethnologueCode'];
		elseif (isset($application['language-iso-code']->field_value))
			$ethnologueCode = $application['language-iso-code']->field_value;

		return <<<HTML
<tr class="form-required">
	<th scope='row'>$label</th>
	<td>
	    <input name="blog[ethnologueCode]" type="text" title="$label" value="$ethnologueCode"/>
	    <p>The following menu links will be created:
		<ul style="list-style: unset; padding-left: 20px; margin: 4px 0 0">
		    <li><a href="https://www.ethnologue.com/language/$ethnologueCode" target="_blank">https://www.ethnologue.com/language/$ethnologueCode</a></li>
		    <li><a href="https://www.sil.org/search/node/$ethnologueCode" target="_blank">https://www.sil.org/search/node/$ethnologueCode</a></li>
        </ul></p>
	</td>
</tr>
HTML;
	}

	private function get_country(array $appPost, array $application): string
	{
		$label = __('Country', $this->_domain);

		$countryName = '';
		if (isset($_POST['blog']))
			$countryName = $appPost['countryName'];
		elseif (isset($application['country-name']->field_value))
			$countryName = $application['country-name']->field_value;

		return <<<HYML
<tr class="form-required">
	<th scope='row'>$label</th>
	<td><input name="blog[countryName]" type="text" value="$countryName"/></td>
</tr>
HYML;
	}

	private function get_copyright(array $appPost, array $application, string $site_title): string
	{
		$label = __( 'Copyright footer text:', $this->_domain );
		$label_footer = __( 'Copyright footer', $this->_domain );

		$copyright = '';
		if(isset($_POST['blog']))
			$copyright = $appPost['copyright'];
		elseif(isset($application['copyright-holder']->field_value))
			$copyright =  $site_title . ' © ' . date('Y') . ' ' . $application['copyright-holder']->field_value . '<sup>®</sup>';


		return <<<HTML
<tr class="form-required">
	<th scope='row'>$label</th>
	<td><input name="blog[copyright]" size="70" type="text" title="$label_footer" value="$copyright"/></td>
</tr>
HTML;

	}

	private function get_publication_status(array $appPost, array $application): string
    {
        $label = __('Publication status:', $this->_domain);

        $publication_status = 0;
        if (isset($_POST['blog']))
            $publication_status = $appPost['publicationStatus'];
		elseif (isset($application['the-publication-status-of-the-dictionary']->field_value))
            $publication_status = $application['the-publication-status-of-the-dictionary']->field_value;

		$options = [
			'',
			'Rough draft',
			'Self-reviewed draft',
			'Community-reviewed draft',
			'Consultant approved',
			'Finished (no formal publication)',
			'Formally published'
		];

        if (is_numeric($publication_status))
			$index = intval($publication_status);
		else
            $index = array_search($publication_status, $options) ?: 0;

		$option_tags = [];
		for ($i = 0; $i < count($options); $i++) {
			$selected = ($i == $index ? 'selected' : '');
            /** @noinspection HtmlUnknownAttribute */
            $option_tags[] = sprintf('<option value="%s" %s>%s</option>', $i, $selected, $options[$i]);
		}
		$options_html = implode(PHP_EOL, $option_tags);

		return <<<HTML
<tr class="form-required">
	<th scope='row'>$label</th>
	<td>
	<select name="blog[publicationStatus]">
		$options_html
	</select></td>
</tr>
HTML;

	}

	private function get_allow_comments(array $appPost, array $application): string
    {
        $label = __('Allow comments:', $this->_domain);

        $comments = 'yes';
        if (isset($_POST['blog']) && !isset($appPost['comments']))
        	$comments = 'no';
        elseif (isset($application['allow-comments']->field_value))
            $comments = strtolower($application['allow-comments']->field_value);

		$checked = ($comments == 'yes' ? 'checked' : '');

		return <<<HTML
<tr class="form-required">
	<th scope='row'>$label</th>
	<td>
	<input name="blog[comments]" type="checkbox" value="$comments" $checked></td>
</tr>
HTML;
	}

	private function get_email(string $admin_email): string
    {
        $label = __('Admin Email', $this->_domain);
        $tag_title = __('Email', $this->_domain);

		return <<<HTML
<tr class="form-required">
	<th scope='row'>$label</th>
	<td><input name="blog[email]" size="40" type="text" title="$tag_title" value="$admin_email"></td>
</tr>
HTML;
	}

	private function get_user_name(array $appPost, array $application, string $admin_email): string
    {
		global $wpdb;

        $label = __('Username', $this->_domain);

        $username = '';
        if (isset($_POST['blog'])) {
            $username = $appPost['username'];
        } elseif (isset($application['from_name']->field_value)) {
            $username = $wpdb->get_var("SELECT user_login FROM wp_users WHERE user_email = '" . $admin_email . "'");

            if ($username == NULL) {
                $username = str_replace(' ', '', strtolower($application['from_name']->field_value));

                $otherEmail = $wpdb->get_var("SELECT user_email FROM wp_users WHERE user_login = '" . $username . "'");
            }
        }

		if (!empty($otherEmail))
			$msg = sprintf('<p style="color: #aa0000">This username already exists with another email address: %s<br>Please change the username.</p>', $otherEmail);
		else
			$msg = '';

		return <<<HTML
<tr class="form-required">
	<th scope='row'>$label</th>
	<td>
	$msg
	<input name="blog[username]" size="40" type="text" title="$label" value="$username">
</td>
</tr>
HTML;

	}

	private function show_form($from_blog, array $blogs, $copy_id, array $application, $from_blog_id, array $appPost, $current_site, $copy_files): string
    {
		$site_title = '';

		if (isset($_POST['blog'])) {
			$site_title = $appPost['title'];
		} else if (isset($application['language-name']->field_value)) {
			if (str_contains($application['template-to-use']->field_value, 'french'))
				$site_title = 'Dictionnaire ' . $application['language-name']->field_value;
			elseif (str_contains($application['template-to-use']->field_value, 'spanish'))
				$site_title = 'Diccionario ' . $application['language-name']->field_value;
			else
				$site_title = $application['language-name']->field_value . ' Dictionary';
		}

        $admin_email = '';
        if (isset($_POST['blog']))
            $admin_email = $appPost['email'];
		elseif (isset($application['from_email']->field_value))
            $admin_email = $application['from_email']->field_value;

		$content = [];
		if ($from_blog) {
			$content[] = $this->get_from_blog_row($from_blog, $copy_id);
		}
		else {
			$content[] = $this->get_choose_site_to_copy($blogs, $application, $from_blog_id);
			$content[] = $this->get_desired_url($appPost, $application, $current_site->path);
			$content[] = $this->get_new_site_title($site_title);
			$content[] = $this->get_ethnologue_code($appPost, $application);
			$content[] = $this->get_country($appPost, $application);
			$content[] = $this->get_copyright($appPost, $application, $site_title);
			$content[] = $this->get_publication_status($appPost, $application);
			$content[] = $this->get_allow_comments($appPost, $application);
			$content[] = $this->get_email($admin_email);
			$content[] = $this->get_user_name($appPost, $application, $admin_email);
		}

		$create_now_label = __( 'Create Now', $this->_domain );

        $copy_files_checked = checked( $copy_files, true, false );

		$content_html = implode(PHP_EOL, $content);

        return <<<HTML
<div class="wrap">
	<form method="POST">
		<input type="hidden" name="action" value="$this->_domain">
		<table class="form-table">
            $content_html
		</table>
		<hr>
		<p><b>Comments:</b> {$application['message']->field_value}</p>
		<p><b>Display Languages (need to be enabled manually):</b> {$application['ui-languages']->field_value}</p>
		<hr>
		<p>An email will be sent to the user informing him about his new site. See <a href="/wp-admin/network/settings.php">Welcome Email</a>.</p>
		<p class="submit"><input class="button" type="submit" value="$create_now_label"></p>
		<input type="checkbox" name="copy_files" value="1" style="display: none" $copy_files_checked>
	</form>
</div>
HTML;
	}

	/**
	* Admin page
	*/
	public function admin_page(): void
	{
		global $wpdb, $current_site;

		if (!current_user_can('manage_sites'))
			wp_die(__("Sorry, you don't have permissions to use this page.", $this->_domain));


		if (isset($_GET['remove'])) {
			$sql = <<<SQL
UPDATE wp_cf7dbplugin_submits
SET field_name = 'removed'
WHERE field_name LIKE 'newapplication' AND submit_time = %f
SQL;

			$sql = $wpdb->prepare($sql, $_GET['applicationid']);
			$wpdb->query($sql);
		}

		if (isset($_GET['applicationid']) && !isset($_GET['remove'])) {
			$query = <<<SQL
SELECT field_name, field_value
FROM wp_cf7dbplugin_submits
WHERE submit_time = {$_GET['applicationid']}
SQL;
			$application = $wpdb->get_results($query, OBJECT_K);
		}
		else {
			$this->show_applications();
			$application = [];
		}

		$from_blog = false;
		$copy_id = 0;
		$nonce_string = sprintf('%s-%s', $this->_domain, $copy_id);

		if (isset($_GET['blog']) && wp_verify_nonce($_GET['_wpnonce'], $nonce_string)) {

			$copy_id = (int)$_GET['blog'];
			$from_blog = get_blog_details($copy_id);

			if ($from_blog->site_id != $current_site->id)
				$from_blog = false;
		}

		$from_blog_id = (int)($_POST['source_blog'] ?? -1);

        $msg = [];

        if (isset($_POST['action']) && $_POST['action'] == $this->_domain) {

            //check_admin_referer( $this->_domain );
            $appPost = $_POST['blog'];
            $domain = sanitize_user(str_replace('/', '', $appPost['domain']));
            $title = $appPost['title'];
            $copy_files = isset($_POST['copy_files']) && $_POST['copy_files'] == '1';

            if (!$from_blog_id)
                $msg[] = __('Please select a source blog.', $this->_domain);

            if (empty($domain))
                $msg[] = __('Please enter a "New Site Address".', $this->_domain);

			if (empty($title))
                $msg[] = __('Please enter a "New Site Title".', $this->_domain);

            if (empty($msg)) {
                $applicationid = $_GET['applicationid'] ?? '';

                $msg[] = $this->copy_blog($domain, $title, $from_blog_id, $copy_files, $appPost['email'], $appPost['username'], $applicationid, $appPost['ethnologueCode'], $appPost['countryName'], $appPost['copyright'], $appPost['comments'], $appPost['publicationStatus']);
            }
        } else {
            $copy_files = true; // set the default for first page load
			$appPost = [];
        }

		$blogs = [];
        if (!$from_blog) {

            /** @noinspection SqlResolve */
            $query = <<<SQL
SELECT b.blog_id, b.domain, TRIM(BOTH '/' FROM b.path) AS path FROM $wpdb->blogs AS b
WHERE b.site_id = $current_site->id && b.blog_id > 1 ORDER BY path LIMIT 10000
SQL;
            $blogs = $wpdb->get_results($query);
        }

		if (!$from_blog && empty($blogs)) {
			$content_html = $this->get_no_blog_selected();
		}
		else {
			$content_html = $this->show_form($from_blog, $blogs, $copy_id, $application, $from_blog_id, $appPost, $current_site, $copy_files);
		}

		// put it together
		$msg_html = $this->get_messages($msg);

		echo <<<HTML
<div class='wrap'>
    <h2>$this->_name</h2>
    $msg_html
    $content_html
</div>
HTML;
	}

    /**
     * @param $domain
     * @param $title
     * @param $from_blog_id
     * @param $copy_files
     * @param $email
     * @param $username
     * @param $applicationid
     * @param $ethnologueCode
     * @param $countryName
     * @param $copyright
     * @param $allow_comments
     * @param $publicationStatus
     * @return string
	 * @noinspection SqlResolve
	 */
    public function copy_blog($domain, $title, $from_blog_id, $copy_files, $email, $username, $applicationid, $ethnologueCode, $countryName, $copyright, $allow_comments, $publicationStatus): string
    {
        global $wpdb, $current_site, $base;

		// The user id of the user that will become the blog admin of the new blog.
		//$user_id = apply_filters('copy_blog_user_id', $user_id, $from_blog_id);
		$user_id = $wpdb->get_var($wpdb->prepare('SELECT ID FROM wp_users WHERE user_login = %s', $username));

		$password = 'Your existing password';
		if ($user_id == NULL) {
			do_action('pre_network_site_new_created_user', $email);
			$password = wp_generate_password(12, false);
			$user_id = wpmu_create_user($username, $password, $email);
			do_action('network_site_new_created_user', $user_id);
		}

		if (is_subdomain_install()) {
			$new_domain = $domain . '.webonary.org';
			$path = $base;
		} else {
			$new_domain = $_SERVER['HTTP_HOST'];
			$path = '/' . $domain . '/';
		}

		// The new domain that will be created for the destination blog.
		$new_domain = apply_filters('copy_blog_domain', $new_domain, $domain);

		// The new path that will be created for the destination blog.
		$path = apply_filters('copy_blog_path', $path, $domain);

		$wpdb->hide_errors();
		$to_blog_id = wpmu_create_blog($new_domain, $path, $title, $user_id, ['public' => 1], $current_site->id);
		$wpdb->show_errors();

		$msg = [];
		if (!is_wp_error($to_blog_id)) {
			wpmu_welcome_notification($to_blog_id, $user_id, $password, $title, ['public' => 1]);

			$dashboard_blog = get_site();
			if (!is_super_admin() && get_user_option('primary_blog', $user_id) == $dashboard_blog->blog_id)
				update_user_option($user_id, 'primary_blog', $to_blog_id, true);

			// now copy
			if ($from_blog_id) {

				$this->copy_blog_data($from_blog_id, $to_blog_id);

				if ($copy_files) {
					$this->copy_blog_files($from_blog_id, $to_blog_id);
					$this->replace_content_urls($from_blog_id, $to_blog_id);
				}

				switch_to_blog($to_blog_id);

				$user = new WP_User($user_id);
				$user->set_role('editor');

				//Set site admin email address (comments will be sent to that email)
				$sql = "UPDATE  $wpdb->options SET option_value = '" . $email . "' WHERE option_name = 'admin_email'";
				$wpdb->query($sql);

				//Sets domain to https
				$sql = "UPDATE  $wpdb->options SET option_value = 'https://$new_domain$path' WHERE option_name = 'siteurl'";
				$wpdb->query($sql);


				$siteurl = get_blog_option($to_blog_id, 'siteurl');

				$body = <<<TXT
From: [your-name] <[your-email]>
Subject: [your-subject]

Message Body:
[your-message]

--
This e-mail was sent from a contact form on $siteurl

TXT;
				$contact_options = [
					'active' => true,
					'subject' => '[text* your-subject]',
					'sender' => '[your-name] <wordpress@webonary.org>',
					'recipient' => $email,
					'body' => $body,
					'additional_headers' => 'Reply-To: [your-email]',
					'attachments' => '',
					'use_html' => false,
					'exclude_blank' => false
				];

				$sql = $wpdb->prepare("UPDATE wp_{$to_blog_id}_postmeta SET meta_value = %s WHERE meta_key = '_mail'", maybe_serialize($contact_options));

				$wpdb->query($sql);

				//Set ethnologue link
				$meta_id = $wpdb->get_var("SELECT meta_id FROM $wpdb->postmeta WHERE meta_key = '_menu_item_url' AND meta_value LIKE '%www.ethnologue.com/language%'");

				//Set footer (copyright)
				$themeZeeOpt = get_option('themezee_options');
				$themeZeeOpt['themeZee_footer'] = $copyright;
				update_option('themezee_options', $themeZeeOpt);

				//Set the ethnologue menu link
				$setEthnologueCode = false;
				if ($meta_id != NULL) {
					$sql = <<<SQL
UPDATE $wpdb->postmeta
SET meta_value = 'https://www.ethnologue.com/language/$ethnologueCode'
WHERE meta_id = $meta_id
SQL;

					$wpdb->query($sql);
					$setEthnologueCode = true;
				}

				//Set Bibliography Link
				//Set ethnologue link
				$bibliography_link_id = $wpdb->get_var("SELECT meta_id FROM $wpdb->postmeta WHERE meta_key = '_menu_item_url' AND meta_value LIKE '%www.sil.org/search/node%'");
				$setBibliography = false;
				if ($bibliography_link_id != NULL) {
					$sql = "UPDATE $wpdb->postmeta SET meta_value = 'https://www.sil.org/search/node/$ethnologueCode' WHERE meta_id = $bibliography_link_id";

					$wpdb->query($sql);
					$setBibliography = true;
				}

				//Set country name
				update_option('countryName', $countryName);

				//Set publication status
				update_option('publicationStatus', $publicationStatus);

				//Set allow comments
				if (strtolower($allow_comments) == 'yes') {
					update_option('default_comment_status', 'open');
				} else {
					update_option('default_comment_status', 'closed');
					$allow_comments = 'no';
				}

				// 30 Oct 2023: default all new sites to cloud backend
				update_option('useCloudBackend', 1);

				//20200207 chungh: Webonary.org reconfig to use subdirectory
				$msg[] = sprintf(__('Copied: %s in %s seconds', $this->_domain), '<a href="' . $siteurl . '" target="_blank">' . $title . '</a>', number_format_i18n(timer_stop()));
				$msg[] = "- Welcome email sent to: $email";
				$msg[] = '- Set this email as contact person for contact form.';

				if ($setEthnologueCode)
					$msg[] = "- Set ethnologue link to https://www.ethnologue.com/language/$ethnologueCode";

				if ($setBibliography)
					$msg[] = "- Set bibliography link to https://www.sil.org/search/node/$ethnologueCode";

				$msg[] = '- Set the copyright text in footer';
				$msg[] = '- Set the Publication status';
				$msg[] = "- Allow comments was set to '$allow_comments'";

				do_action('log', __('Copy Complete!', $this->_domain), $this->_domain, implode('<br>', $msg));
				do_action('copy_blog_complete', $from_blog_id, $to_blog_id);


				$sql = $wpdb->prepare(
					"UPDATE wp_cf7dbplugin_submits
							SET field_name = 'created'
							WHERE field_name = 'newapplication' AND submit_time = %f",
					$applicationid);

				$wpdb->query($sql);
            }
        } else {
            $msg[] = $to_blog_id->get_error_message();
        }

        return implode('<br>', $msg);
    }

	/**
	 * Copy blog data from one blog to another
	 *
	 * @param int $from_blog_id ID of the blog being copied from.
	 * @param int $to_blog_id ID of the blog being copied to.
	 * @noinspection SqlResolve
	 */
	private function copy_blog_data(int $from_blog_id, int $to_blog_id ): void
	{
		global $wpdb;

		if ($from_blog_id) {
			$from_blog_prefix = $this->get_blog_prefix($from_blog_id);
			$to_blog_prefix = $this->get_blog_prefix($to_blog_id);
			$from_blog_prefix_length = strlen($from_blog_prefix);
			$from_blog_escaped_prefix = str_replace('_', '\_', $from_blog_prefix);

			// Grab key options from new blog.
			$saved_options = [
				'siteurl' => '',
				'home' => '',
				'upload_path' => '',
				'fileupload_url' => '',
				'upload_url_path' => '',
				'admin_email' => '',
				'blogname' => ''
			];

			// Options that should be preserved in the new blog.
			$saved_options = apply_filters('copy_blog_data_saved_options', $saved_options);
			foreach ($saved_options as $option_name => $option_value) {
				$saved_options[$option_name] = get_blog_option($to_blog_id, $option_name);
			}

			// Copy over ALL the tables.
			$query = $wpdb->prepare('SHOW TABLES LIKE %s', $from_blog_escaped_prefix . '%');
			do_action('log', $query, $this->_domain);
			$old_tables = $wpdb->get_col($query);

			foreach ($old_tables as $table) {
				$raw_table_name = substr($table, $from_blog_prefix_length);
				$new_table = $to_blog_prefix . $raw_table_name;

				$query = "DROP TABLE IF EXISTS $new_table";
				do_action('log', $query, $this->_domain);
				$wpdb->get_results($query);

				$query = "CREATE TABLE IF NOT EXISTS $new_table LIKE $table";
				do_action('log', $query, $this->_domain);
				$wpdb->get_results($query);

				$query = "INSERT $new_table SELECT * FROM $table";
				do_action('log', $query, $this->_domain);
				$wpdb->get_results($query);
			}

			switch_to_blog($to_blog_id);

			// caches will be incorrect after direct DB copies
			wp_cache_delete('notoptions', 'options');
			wp_cache_delete('alloptions', 'options');

			// apply key options from new blog.
			foreach ($saved_options as $option_name => $option_value) {

				//if using update_option function for admin_email it will send an email to the original site owner
				if ($option_name == 'admin_email') {
					$sql = "UPDATE wp_{$to_blog_id}_options SET option_value = %s WHERE option_name = 'admin_email'";
					$wpdb->query($wpdb->prepare($sql, $option_value));
				} else {
					update_option($option_name, $option_value);
				}
			}

			/// fix all options with the wrong prefix...
			$query = $wpdb->prepare("SELECT * FROM $wpdb->options WHERE option_name LIKE %s", $from_blog_escaped_prefix . '%');
			$options = $wpdb->get_results($query);
			do_action('log', $query, $this->_domain, count($options) . ' results found.');
			if ($options) {
				foreach ($options as $option) {
					$raw_option_name = substr($option->option_name, $from_blog_prefix_length);
					$wpdb->update($wpdb->options, ['option_name' => $to_blog_prefix . $raw_option_name], ['option_id' => $option->option_id]);
				}

				// caches will be incorrect after direct DB copies
				wp_cache_delete('notoptions', 'options');
				wp_cache_delete('alloptions', 'options');
			}

			// Fix GUIDs on copied posts
			$this->replace_guid_urls($from_blog_id, $to_blog_id);

			restore_current_blog();
		}
	}

	/**
	 * Copy files from one blog to another.
	 *
	 * @param int $from_blog_id ID of the blog being copied from.
	 * @param int $to_blog_id ID of the blog being copied to.
	 */
	private function copy_blog_files(int $from_blog_id, int $to_blog_id): void
	{
		set_time_limit(2400); // 60 seconds x 40 minutes
		@ini_set('memory_limit', '2048M');

		// Path to source blog files.
		switch_to_blog($from_blog_id);
		$dir_info = wp_upload_dir();
		$from = str_replace(' ', "\\ ", trailingslashit($dir_info['basedir']) . '*'); // * necessary with GNU cp, doesn't hurt anything with BSD cp
		restore_current_blog();
		$from = apply_filters('copy_blog_files_from', $from, $from_blog_id);

		// Path to destination blog files.
		switch_to_blog($to_blog_id);
		$dir_info = wp_upload_dir();
		$to = str_replace(' ', "\\ ", trailingslashit($dir_info['basedir']));
		restore_current_blog();
		$to = apply_filters('copy_blog_files_to', $to, $to_blog_id);

		// Shell command used to copy files.
		$command = apply_filters('copy_blog_files_command', sprintf("cp -Rfp %s %s", $from, $to), $from, $to);
		exec($command);
	}

	/**
	 * Replace URLs in post content
	 *
	 * @param int $from_blog_id ID of the blog being copied from.
	 * @param int $to_blog_id ID of the blog being copied to.
	 * @noinspection SqlResolve
	 */
	private function replace_content_urls(int $from_blog_id, int $to_blog_id): void
	{
		global $wpdb;
		$to_blog_prefix = $this->get_blog_prefix($to_blog_id);
		$from_blog_url = get_blog_option($from_blog_id, 'siteurl');
		$to_blog_url = get_blog_option($to_blog_id, 'siteurl');
		$query = $wpdb->prepare("UPDATE {$to_blog_prefix}posts SET post_content = REPLACE(post_content, %s, %s) WHERE ID > 0", $from_blog_url, $to_blog_url);
		do_action('log', $query, $this->_domain);
		$wpdb->query($query);
	}

	/**
	 * Get the database prefix for a blog
	 *
	 * @param int $blog_id ID of the blog.
	 * @return string prefix
	 */
	private function get_blog_prefix(int $blog_id): string
	{
		global $wpdb;
		if (is_callable(array(&$wpdb, 'get_blog_prefix'))) {
			$prefix = $wpdb->get_blog_prefix($blog_id);
		} else {
			$prefix = $wpdb->base_prefix . $blog_id . '_';
		}
		return $prefix;
	}

	/**
	 * Replace URLs in post GUIDs
	 *
	 * @param int $from_blog_id ID of the blog being copied from.
	 * @param int $to_blog_id ID of the blog being copied to.
	 * @noinspection SqlResolve
	 */
	private function replace_guid_urls(int $from_blog_id, int $to_blog_id): void
	{
		global $wpdb;
		$to_blog_prefix = $this->get_blog_prefix($to_blog_id);
		$from_blog_url = get_blog_option($from_blog_id, 'siteurl');
		$to_blog_url = get_blog_option($to_blog_id, 'siteurl');
		$query = $wpdb->prepare("UPDATE {$to_blog_prefix}posts SET guid = REPLACE(guid, %s, %s) WHERE ID > 0", $from_blog_url, $to_blog_url);
		do_action('log', $query, $this->_domain);
		$wpdb->query($query);
	}
}
