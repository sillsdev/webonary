<?php

class Hugeit_Slider_Deactivation_Feedback
{

    public function __construct()
    {
        if(Hugeit_Slider()->tracking->is_opted_in()){
            add_action('current_screen', array($this, 'init'));
            add_action('wp_ajax_hugeit_slider_deactivation_feedback', array($this, 'send'));
        }
    }

    public function init()
    {
        $screen = get_current_screen();

        if('plugins' === $screen->id){
            add_action('admin_footer',array($this,'render_footer'));
        }
    }

    public function render_footer()
    {
        $slug = Hugeit_Slider()->get_slug();
        echo Hugeit_Slider_Template_Loader::render(HUGEIT_SLIDER_ADMIN_TEMPLATES_PATH.'/tracking/deactivation-feedback/show.php', compact('slug'));
    }

    public function send()
    {
        if(!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'],'hugeit-slider-deactivation-feedback')){
            die(0);
        }

        Hugeit_Slider()->tracking->track_data();

        if (!Hugeit_Slider()->tracking->is_opted_in()) {
            die(0);
        }

        $data = array(
            'project_id' => Hugeit_Slider()->get_project_id(),
            'project_version' => Hugeit_Slider()->get_version(),
            'deactivation_reason' => sanitize_text_field($_POST['value']),
            'comment' => sanitize_text_field($_POST['comment']),
            'site_url' => home_url(),
            'email' => get_option('admin_email'),
        );



        wp_remote_post('https://huge-it.com/track-user-data/deactivation-feedback.php', array(
            'method' => 'POST',
            'timeout' => 45,
            'redirection' => 5,
            'blocking' => true,
            'headers' => array(),
            'body' => $data,
        ));

        echo 'ok';
        die;
    }

}