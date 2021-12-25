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
		// Change site-title & description to postMessage.
		$wp_customize->get_setting('blogname')->transport = 'postMessage';
		$wp_customize->get_setting('blogdescription')->transport = 'postMessage';

		// Add partial for blogname.
		$wp_customize->selective_refresh->add_partial(
			'blogname',
			array(
				'selector'        => '.site-title',
				'render_callback' => array(__CLASS__, 'PartialBlogName'),
			)
		);

		// Add partial for blogdescription.
		$wp_customize->selective_refresh->add_partial(
			'blogdescription',
			array(
				'selector'        => '.site-description',
				'render_callback' => array(__CLASS__, 'PartialBlogDescription'),
			)
		);

		// Add "display_title_and_tagline" setting for displaying the site-title & tagline.
		$wp_customize->add_setting(
			'display_title_and_tagline',
			array(
				'capability'        => 'edit_theme_options',
				'default'           => true,
				'sanitize_callback' => array(__CLASS__, 'SanitizeCheckbox'),
			)
		);

		// Add control for the "display_title_and_tagline" setting.
		$wp_customize->add_control(
			'display_title_and_tagline',
			array(
				'type'    => 'checkbox',
				'section' => 'title_tagline',
				'label'   => esc_html__('Display Site Title & Tagline', WEBONARY_THEME_DOMAIN),
			)
		);




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
	public static function UnqueueJquery()
	{
		wp_deregister_script( 'jquery-core' );
		wp_deregister_script( 'jquery-migrate' );
	}
}
