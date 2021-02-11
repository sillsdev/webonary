<?php


class Hugeit_Slider_Tracking
{
    /**
     * Hugeit_Slider_Tracking constructor.
     */
    public function __construct()
    {
        if (isset($_GET['hugeit_slider_tracking_opt_in'])) {
            add_action('admin_init', array($this, 'maybe_opt_in'));
        }

        add_action('admin_notices', array($this, 'admin_notice'));
        add_action('hugeit_slider_opt_in_cron', array($this, 'track_data'));
    }

    /**
     *
     */
    public function maybe_opt_in()
    {
        if (!$this->can_opt_in()) {
            return;
        }

        $opt_in_action = htmlspecialchars($_GET['hugeit_slider_tracking_opt_in']);

        if (1 == $opt_in_action) {
            $this->opt_in();
        } elseif (0 == $opt_in_action) {
            $this->opt_out();
        }

        header('Location: ' . admin_url('admin.php?page=hugeit_slider'));

    }

    /**
     * Check if current user is capable for opting in/out to track user data
     *
     * @return bool
     */
    public function can_opt_in()
    {
        return current_user_can('manage_options');
    }

    /**
     * Print out the admin notice for opting in/out to track user data
     */
    public function admin_notice()
    {
        $screen = get_current_screen();
        $screen_id = $screen->id;

        if (!in_array($screen_id, Hugeit_Slider()->admin->get_pages())) return;

        if (!$this->can_opt_in()) return;

        if ($this->is_opted_in() || $this->is_opted_out()) return;

        $optin_url = $this->get_opt_in_url();
        $optout_url = $this->get_opt_out_url();
        echo Hugeit_Slider_Template_Loader::render(HUGEIT_SLIDER_ADMIN_TEMPLATES_PATH . '/tracking/banner/show.php', compact('optin_url', 'optout_url'));
    }

    /**
     * Get url for opting out from tracking data
     *
     * @return string
     */
    public function get_opt_in_url()
    {
        return add_query_arg('hugeit_slider_tracking_opt_in', 1, admin_url('admin.php?page=hugeit_slider'));
    }

    /**
     * Get url for opting out from tracking data
     *
     * @return string
     */
    public function get_opt_out_url()
    {
        return add_query_arg('hugeit_slider_tracking_opt_in', 0, admin_url('admin.php?page=hugeit_slider'));
    }

    /**
     * Check if user has opted in to track data
     *
     * @return bool
     */
    public function is_opted_in()
    {
        return ('opted_in' === get_option('hugeit_slider_allow_tracking', false));
    }

    /**
     * Check if the user has opted out from tracking data
     *
     * @return bool
     */
    public function is_opted_out()
    {
        return ('opted_out' === get_option('hugeit_slider_allow_tracking', false));
    }

    /**
     * Opt in to send data
     */
    public function opt_in()
    {
        update_option('hugeit_slider_allow_tracking', 'opted_in');
    }

    /**
     * Opt out from sending data
     */
    public function opt_out()
    {
        update_option('hugeit_slider_allow_tracking', 'opted_out');
    }

    /**
     * If the user has opted id for data tracking
     * than send the data to http://huge-it.com
     *
     * @return bool
     */
    public function track_data()
    {
        if (!$this->is_opted_in()) {
            return false;
        }

        $all_plugins = array();
        $plugins = get_plugins();
        foreach ($plugins as $plugin_slug => $plugin_info) {
            $plugin = array(
                "Name" => $plugin_info["Name"],
                "PluginURI" => $plugin_info["PluginURI"],
                "Author" => $plugin_info["Author"],
                "AuthorURI" => $plugin_info["AuthorURI"]
            );
            $all_plugins[$plugin_slug] = $plugin;
        }

        $data = array();
        $data["site_url"] = home_url();
        $data["email"] = get_option('admin_email');

        $user = wp_get_current_user();

        $first_name = get_user_meta($user->ID, "first_name", true);
        $last_name = get_user_meta($user->ID, "last_name", true);

        $data["name"] = $first_name || $last_name ? $first_name . " " . $last_name : $user->data->user_login;

        $data["wp_version"] = get_bloginfo('version');
        $data["project_id"] = Hugeit_Slider()->get_project_id();
        $data["project_plan"] = Hugeit_Slider()->get_project_plan();
        $data["project_version"] = Hugeit_Slider()->get_version();
        $data["all_plugins"] = $all_plugins;


        wp_remote_post("https://huge-it.com/track-user-data/", array(
                'method' => 'POST',
                'timeout' => 45,
                'redirection' => 5,
                'blocking' => true,
                'headers' => array(),
                'body' => $data,
            )
        );
    }

}