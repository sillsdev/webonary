<?php



class Webonary2_Customize
{
	public static function Init()
	{
		add_action('customize_register', array(__CLASS__, 'register'));
	}

	/**
	 * Register customizer options.
	 *
	 *
	 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
	 *
	 * @return void
	 */
	public static function Register( WP_Customize_Manager $wp_customize)
	{
		/**
		 * webonary settings
		 */
		$wp_customize->add_section(
			'webonary_settings',
			array(
				'title'    => esc_html__('Webonary Settings', WEBONARY_THEME_DOMAIN),
				'priority' => 30
			)
		);

		$wp_customize->add_setting(
			'webonary_logo',
			array(
				'capability' => 'edit_theme_options',
				'default'    => 'https://www.webonary.org/wp-content/uploads/webonary.png',
				'transport'  => 'refresh'
			)
		);

		$wp_customize->add_control(
			new WP_Customize_Image_Control(
				$wp_customize,
				'logo',
				array(
					'label'    => esc_html__('Upload a logo', WEBONARY_THEME_DOMAIN),
					'section'  => 'webonary_settings',
					'settings' => 'webonary_logo',
					'context'  => 'webonary_options'
				)
			)
		);

		$wp_customize->add_setting(
			'webonary_copyright',
			array(
				'capability' => 'edit_theme_options',
				'default'    => '© [year] SIL International<sup>®</sup>',
				'transport'  => 'refresh'
			)
		);

		$wp_customize->add_control(
			'copyright_field',
			array(

				'type'     => 'text',
				'section'  => 'webonary_settings',
				'settings' => 'webonary_copyright',
				'label'    => esc_html__('Site Copyright', WEBONARY_THEME_DOMAIN),
				'description' => esc_html__('[year] will be replaced with the current year', WEBONARY_THEME_DOMAIN)
			)
		);

		// homepage_sidebar setting
		$wp_customize->add_setting(
			'display_frontpage_sidebar',
			array(
				'capability'        => 'edit_theme_options',
				'default'           => false,
				'sanitize_callback' => array(__CLASS__, 'SanitizeCheckbox'),
			)
		);

		$wp_customize->add_control(
			'frontpage_sidebar',
			array(
				'type'    => 'checkbox',
				'section'  => 'webonary_settings',
				'settings' => 'display_frontpage_sidebar',
				'label'   => esc_html__('Display Front Page Sidebar', WEBONARY_THEME_DOMAIN),
			)
		);

		// page_sidebar setting
		$wp_customize->add_setting(
			'display_page_sidebar',
			array(
				'capability'        => 'edit_theme_options',
				'default'           => false,
				'sanitize_callback' => array(__CLASS__, 'SanitizeCheckbox'),
			)
		);

		$wp_customize->add_control(
			'page_sidebar',
			array(
				'type'    => 'checkbox',
				'section'  => 'webonary_settings',
				'settings' => 'display_page_sidebar',
				'label'   => esc_html__('Display Page Sidebar', WEBONARY_THEME_DOMAIN),
			)
		);

		// post_sidebar setting
		$wp_customize->add_setting(
			'display_post_sidebar',
			array(
				'capability'        => 'edit_theme_options',
				'default'           => false,
				'sanitize_callback' => array(__CLASS__, 'SanitizeCheckbox'),
			)
		);

		$wp_customize->add_control(
			'post_sidebar',
			array(
				'type'    => 'checkbox',
				'section'  => 'webonary_settings',
				'settings' => 'display_post_sidebar',
				'label'   => esc_html__('Display Post Sidebar', WEBONARY_THEME_DOMAIN),
			)
		);
	}

	/**
	 * Sanitize boolean for checkbox.
	 *
	 * @param bool|null $checked Whether or not a box is checked.
	 *
	 * @return bool
	 */
	public static function SanitizeCheckbox( ?bool $checked = null ): bool
	{
		return isset( $checked ) && true === $checked;
	}

	/**
	 * Render the site title for the selective refresh partial.
	 *
	 * @return void
	 */
	public function PartialBlogName() {
		bloginfo( 'name' );
	}

	/**
	 * Render the site tagline for the selective refresh partial.
	 *
	 * @since Twenty Twenty-One 1.0
	 *
	 * @return void
	 */
	public function PartialBlogDescription() {
		bloginfo( 'description' );
	}

	/**
	 * We will handle jQuery and Bootstrap ourselves in the index.php file
	 *
	 * @return void
	 */
	public static function EnqueueScripts()
	{
		wp_register_script('webonary2_jquery-cycle', get_template_directory_uri() .'/lib/jquery.cycle.all.min.js', array('jquery'));
		wp_enqueue_script('webonary2_jquery-cycle');
	}

	public static function FilterDataTablesScriptsBootstrap5(array $scripts): array {

		static $subs = [
			'jquery-datatables' => ['https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js', 'https://cdn.datatables.net/1.11.3/js/dataTables.bootstrap5.min.js'],
			'datatables-buttons' => ['https://cdn.datatables.net/buttons/2.1.1/js/dataTables.buttons.min.js', 'https://cdn.datatables.net/buttons/2.1.1/js/buttons.bootstrap5.min.js'],
			'datatables-buttons-colvis' => 'https://cdn.datatables.net/buttons/2.1.0/js/buttons.colVis.min.js',
			'datatables-buttons-print' => 'https://cdn.datatables.net/buttons/2.1.0/js/buttons.print.min.js',
			'pdfmake' => 'https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.4/pdfmake.min.js',
			'pdfmake-fonts' => 'https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.4/vfs_fonts.min.js',
			'jszip' => 'https://cdnjs.cloudflare.com/ajax/libs/jszip/3.7.1/jszip.min.js',
			'datatables-buttons-html5' => 'https://cdn.datatables.net/buttons/2.1.0/js/buttons.html5.min.js',
			'datatables-select' => 'https://cdn.datatables.net/select/1.3.3/js/dataTables.select.min.js',
			'datatables-fixedheader' => 'https://cdn.datatables.net/fixedheader/3.2.0/js/dataTables.fixedHeader.min.js',
			'datatables-fixedcolumns' => 'https://cdn.datatables.net/fixedcolumns/4.0.1/js/dataTables.fixedColumns.min.js',
			'datatables-responsive' => 'https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js'
		];

		return self::MakeSubstitutions($scripts, $subs);
	}

	public static function FilterDataTablesStylesBootstrap5(array $styles): array {

		static $subs = [
			'jquery-datatables' => 'https://cdn.datatables.net/1.11.3/css/dataTables.bootstrap5.min.css',
			'datatables-buttons' => 'https://cdn.datatables.net/buttons/2.1.1/css/buttons.bootstrap5.min.css',
			'datatables-select' => 'https://cdn.datatables.net/select/1.3.3/css/select.dataTables.min.css',
			'datatables-fixedheader' => 'https://cdn.datatables.net/fixedheader/3.2.0/css/fixedHeader.dataTables.min.css',
			'datatables-fixedcolumns' => 'https://cdn.datatables.net/fixedcolumns/4.0.1/css/fixedColumns.dataTables.min.css',
			'datatables-responsive' => 'https://cdn.datatables.net/responsive/2.2.9/css/responsive.dataTables.min.css'
		];

		return self::MakeSubstitutions($styles, $subs);
	}

	private static function MakeSubstitutions($target, $substitutions): array {

		foreach ($substitutions as $key => $src) {

			if (!array_key_exists($key, $target))
				continue;

			if (is_array($src)) {
				for ($i = 0; $i < count($src); $i++) {
					if ($i == 0) {
						$target[$key]['src'] = $src[$i];
					}
					else {
						$new_key = $key . '-' . $i;
						$target[$new_key]['src'] = $src[$i];

						if (isset($target[$key]['deps']))
							$target[$new_key]['deps'] = $target[$key]['deps'];
					}

				}
			}
			else {
				$target[$key]['src'] = $src;
			}
		}

		return $target;
	}
}
