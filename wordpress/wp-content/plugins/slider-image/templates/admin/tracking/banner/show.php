<?php
/**
 * @var $optin_url string
 * @var $optout_url string
 */
?>
<div class="hugeit-tracking-optin">
    <div class="hugeit-tracking-optin-left">
        <div class="hugeit-tracking-optin-icon"><img
                src="<?php echo HUGEIT_SLIDER_ADMIN_IMAGES_URL . '/tracking/plugin-icon.png'; ?>"
                alt="<?php echo Hugeit_Slider()->get_slug() ?>"/></div>
        <div class="hugeit-tracking-optin-info">
            <div class="hugeit-tracking-optin-header"><?php _e('Let us know how you wish to better this plugin! ', 'hugeit-slider'); ?></div>
            <div class="hugeit-tracking-optin-description"><?php _e('Allow us to email you and ask how you like our plugin and what issues we may fix or add in the future. We collect <a href="http://huge-it.com/privacy-policy/#collected_data_from_plugins" target="_blank">basic data</a>, in order to help the community to improve the quality of the plugin for you. Data will never be shared with any third party.', 'hugeit-slider'); ?></div>
            <div>
                <a href="<?php echo $optin_url; ?>"
                   class="hugeit-tracking-optin-button"><?php _e('Yes, sure', 'hugeit-slider'); ?></a><a
                    href="<?php echo $optout_url; ?>"
                    class="hugeit-tracking-optout-button"><?php _e('No, thanks', 'hugeit-slider'); ?></a>
            </div>
        </div>
    </div>
    <div class="hugeit-tracking-optin-right">
        <div class="hugeit-tracking-optin-logo">
            <img src="<?php echo HUGEIT_SLIDER_ADMIN_IMAGES_URL . '/tracking/logo.png'; ?>" alt="Huge-IT"/>
        </div>
        <div class="hugeit-tracking-optin-links">
            <a href="http://huge-it.com/privacy-policy/#collected_data_from_plugins"
               target="_blank"><?php _e('What data We Collect', 'hugeit-slider'); ?></a>
            <a href="https://huge-it.com/privacy-policy"
               target="_blank"><?php _e('Privacy Policy', 'hugeit-slider'); ?></a>
        </div>
    </div>
</div>