<?php

/*
Plugin Name: Huge IT Slider
Plugin URI: http://huge-it.com/slider
Description: Huge IT slider is a convenient tool for organizing the images represented on your website into sliders. Each product on the slider is assigned with a relevant slider, which makes it easier for the customers to search and identify the needed images within the slider.
Version: 4.0.6
Author: Huge-IT
Author URI: https://huge-it.com/
License: GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

final class Hugeit_Slider {

	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	private $version = '4.0.6';

    /**
     * @var int
     */
	private $project_id = 1;

    /**
     * @var string
     */
	private $project_plan = 'free';

    /**
     * @var string
     */
	private $slug = 'slider-image';

	/**
	 * Hugeit_Slider slider's table name.
	 *
	 * @var string
	 */

	private $slider_table_name;

	/**
	 * Hugeit_Slider slide's table name.
	 *
	 * @var string
	 */
	private $slide_table_name;

	/**
	 * The instance of current class.
	 *
	 * @var Hugeit_Slider
	 */
	private static $instance;

	/**
	 * Instance of Hugeit_Slider_Template_Loader.
	 *
	 * @var Hugeit_Slider_Template_Loader
	 */
	public $template_loader;

	/**
	 * @var Hugeit_Slider_Admin
	 */
	public $admin;

    /**
     * @var Hugeit_Slider_Tracking
     */
	public $tracking;

	/**
	 * Hugeit_Slider constructor.
	 */
	private function __construct() {
		$this->slide_table_name  = $GLOBALS['wpdb']->prefix . 'hugeit_slider_slide';
		$this->slider_table_name = $GLOBALS['wpdb']->prefix . 'hugeit_slider_slider';

        require_once "includes/tracking/class-hugeit-slider-tracking.php";
        $this->tracking = new Hugeit_Slider_Tracking();

		$this->define_constants();
		$this->includes();
		$this->init_hooks();

		do_action( 'hugeit_slider_loaded' );
	}

	/**
	 * Hook into actions and filters.
	 */
	private function init_hooks() {
		register_activation_hook( __FILE__, array( 'Hugeit_Slider_Install', 'init' ) );

		add_action( 'init', array( $this, 'init' ), 1, 0 );
		add_action( 'init', array( 'Hugeit_Slider_Install', 'init' ) );
		add_action( 'before_hugeit_slider_init', array( $this, 'before_init' ), 1, 0 );
		add_action( 'widgets_init', array($this, 'register_widgets'));
		add_action('init',array($this,'schedule_tracking'),0);
		add_filter('cron_schedules',array($this,'custom_cron_job_recurrence'));
	}

	public function before_init() {
		if (isset($_GET['page'], $_GET['task']) && 'hugeit_slider' === $_GET['page'] && 'add' === $_GET['task']) {
			ob_start();
		}
	}

	public function init() {
		do_action('before_hugeit_slider_init');

        new Hugeit_Slider_Deactivation_Feedback();

		$this->template_loader = new Hugeit_Slider_Template_Loader();

		Hugeit_Slider_Install::init();


		if ( $this->is_request( 'admin' ) ) {
			$this->admin = new Hugeit_Slider_Admin();
		} elseif ($this->is_request('frontend')) {
			new Hugeit_Slider_Frontend_Scripts();
		}

		do_action('after_hugeit_slider_init');
	}

	public function register_widgets(){
        register_widget('Hugeit_Slider_Widget');
    }

	/**
	 * Defines plugin basic constants.
	 */
	private function define_constants() {
		define('HUGEIT_SLIDER_VERSION', $this->get_version());

		define('HUGEIT_SLIDER_PLUGIN_URL', untrailingslashit(plugin_dir_url(__FILE__)));
		define('HUGEIT_SLIDER_PLUGIN_PATH', untrailingslashit(plugin_dir_path(__FILE__)));

		define('HUGEIT_SLIDER_ADMIN_IMAGES_URL', HUGEIT_SLIDER_PLUGIN_URL . '/assets/images/admin');
		define('HUGEIT_SLIDER_ADMIN_IMAGES_PATH', HUGEIT_SLIDER_PLUGIN_PATH . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'admin');

		define('HUGEIT_SLIDER_FRONT_IMAGES_URL', HUGEIT_SLIDER_PLUGIN_URL . '/assets/images/front');
		define('HUGEIT_SLIDER_FRONT_IMAGES_PATH', HUGEIT_SLIDER_PLUGIN_PATH . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'front');

		define('HUGEIT_SLIDER_ADMIN_TEMPLATES_URL', HUGEIT_SLIDER_PLUGIN_URL . '/templates/admin');
		define('HUGEIT_SLIDER_ADMIN_TEMPLATES_PATH', HUGEIT_SLIDER_PLUGIN_PATH . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'admin');

		define('HUGEIT_SLIDER_FRONT_TEMPLATES_URL', HUGEIT_SLIDER_PLUGIN_URL . '/templates/front');
		define('HUGEIT_SLIDER_FRONT_TEMPLATES_PATH', HUGEIT_SLIDER_PLUGIN_PATH . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'front');

		define('HUGEIT_SLIDER_STYLESHEETS_URL', HUGEIT_SLIDER_PLUGIN_URL . '/assets/style');
		define('HUGEIT_SLIDER_STYLESHEETS_PATH', HUGEIT_SLIDER_PLUGIN_PATH . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'style');

		define('HUGEIT_SLIDER_SCRIPTS_URL', HUGEIT_SLIDER_PLUGIN_URL . '/assets/js');
		define('HUGEIT_SLIDER_SCRIPTS_PATH', HUGEIT_SLIDER_PLUGIN_PATH . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'js');
	}

	/**
	 * Includes plugin related files.
	 */
	private function includes() {
		require_once "includes/admin/class-hugeit-slider-html-loader.php";

		require_once "includes/interfaces/interface-hugeit-slider-slider-interface.php";
		require_once "includes/interfaces/interface-hugeit-slider-slide-interface.php";
		require_once "includes/interfaces/interface-hugeit-slider-slide-image-interface.php";
		require_once "includes/interfaces/interface-hugeit-slider-slide-video-interface.php";
		require_once "includes/interfaces/interface-hugeit-slider-slide-post-interface.php";
		require_once "includes/interfaces/interface-hugeit-slider-options-interface.php";

		require_once "includes/class-hugeit-slider-slider.php";

		require_once "includes/class-hugeit-slider-slide.php";
		require_once "includes/class-hugeit-slider-slide-image.php";



        require_once "includes/class-hugeit-slider-migrate.php";
		require_once "includes/class-hugeit-slider-install.php";
		require_once "includes/class-hugeit-slider-template-loader.php";
		require_once "includes/class-hugeit-slider-options.php";
		require_once "includes/class-hugeit-slider-ajax.php";

        require_once "includes/class-hugeit-slider-helpers.php";

		if ($this->is_request('admin')) {
			require_once( ABSPATH . '/wp-admin/includes/media.php' );
			require_once( ABSPATH . '/wp-admin/includes/file.php' );
			require_once( ABSPATH . '/wp-admin/includes/image.php' );
			require_once( ABSPATH . '/wp-includes/pluggable.php' );


			require_once "includes/admin/class-hugeit-slider-general-options.php";
			require_once "includes/admin/class-hugeit-slider-admin.php";
			require_once "includes/admin/class-hugeit-slider-admin-assets.php";
			require_once "includes/admin/class-hugeit-slider-sliders.php";


		}

		require_once "includes/class-hugeit-slider-widget.php";
		require_once "includes/class-hugeit-slider-shortcode.php";
		require_once "includes/class-hugeit-slider-frontend-scripts.php";

		require_once "includes/tracking/class-hugeit-slider-deactivation-feedback.php";


	}

    public function schedule_tracking()
    {
        if ( ! wp_next_scheduled( 'hugeit_slider_opt_in_cron' ) ) {
            $this->tracking->track_data();
            wp_schedule_event( current_time( 'timestamp' ), 'hugeit-slider-weekly', 'hugeit_slider_opt_in_cron' );
        }
	}

    public function custom_cron_job_recurrence($schedules)
    {
        $schedules['hugeit-slider-weekly'] = array(
            'display' => __( 'Once per week', 'hugeit-slider' ),
            'interval' => 604800
        );
        return $schedules;
	}

	/**
	 * @param $type
	 *
	 * @return bool
	 */
	private function is_request( $type ) {
		switch ( $type ) {
			case 'admin' :
				return is_admin();
			case 'ajax' :
				return defined( 'DOING_AJAX' );
			case 'cron' :
				return defined( 'DOING_CRON' );
			case 'frontend' :
				return  ! is_admin() && ! defined( 'DOING_CRON' );
			default :
				return false;
		}
	}

	/**
	 * No cloning.
	 */
	private function __clone() {}

	private function __sleep() {}

	private function __wakeup()	{}

	/**
	 * Get plugin version.
	 *
	 * @return string
	 */
	public function get_version() {
		return $this->version;
	}

    /**
     * @return int
     */
    public function get_project_id()
    {
        return $this->project_id;
	}

    /**
     * @return string
     */
    public function get_project_plan()
    {
        return $this->project_plan;
	}

    public function get_slug()
    {
        return $this->slug;
	}

	/**
	 * @return string
	 */
	public function get_slider_table_name() {
		return $this->slider_table_name;
	}

	/**
	 * @return string
	 */
	public function get_slide_table_name() {
		return $this->slide_table_name;
	}
	/**
	 * Get Hugeit_Slider class instance.
	 *
	 * @return Hugeit_Slider
	 */
	public static function get_instance() {
		if ( ! ( self::$instance instanceof self ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}

$GLOBALS['Hugeit_Slider'] = Hugeit_Slider::get_instance();

/**
 * @return Hugeit_Slider
 */
function Hugeit_Slider() {
	return $GLOBALS['Hugeit_Slider'];
}
