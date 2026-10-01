<?php


class Hugeit_Slider_Tracking
{
    /**
     * Hugeit_Slider_Tracking constructor.
     */
    public function __construct()
    {

    }

    /**
     *
     */
    public function maybe_opt_in()
    {
    }

    /**
     * Check if current user is capable for opting in/out to track user data
     *
     * @return bool
     */
    public function can_opt_in()
    {
        return false;
    }

    /**
     * Print out the admin notice for opting in/out to track user data
     */
    public function admin_notice()
    {
	}

    /**
     * Get url for opting out from tracking data
     *
     * @return string
     */
    public function get_opt_in_url()
    {
        return '';
    }

    /**
     * Get url for opting out from tracking data
     *
     * @return string
     */
    public function get_opt_out_url()
    {
        return '';
    }

    /**
     * Check if user has opted in to track data
     *
     * @return bool
     */
    public function is_opted_in()
    {
        return false;
    }

    /**
     * Check if the user has opted out from tracking data
     *
     * @return bool
     */
    public function is_opted_out()
    {
        return true;
    }

    /**
     * Opt in to send data
     */
    public function opt_in()
    {
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
		return false;
    }
}
