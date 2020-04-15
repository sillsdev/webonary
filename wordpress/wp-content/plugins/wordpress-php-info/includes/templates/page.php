<?php

/**
 * Display template for our settings page
 *
 * Included in Class PHPInfo_Page located at classes/phpinfo-page.php
 *
 * @since 1.0
 */

?>
<div class="wrap">
    <h1>
        <?php _e( 'PHP Info', PHPINFO_TD ); ?> by <a href="https://whoischris.com" target="_blank">Chris Flannagan</a> -
        <a href="https://whoischris.com/donate" target="_blank">Donations Appreciated :)</a>
    </h1>

    <?php if ( isset( $_GET['phpinfo'] ) && $_GET['phpinfo'] == 'emailsent' ) : ?>
        <div id="message" class="updated notice notice-success is-dismissible">
            <p>Email sent successfully.</p>
            <button type="button" class="notice-dismiss">
                <span class="screen-reader-text">Dismiss this notice.</span>
            </button>
        </div>
    <?php endif; ?>

    <div class="card">
        <table class='wp-list-table widefat fixed striped'>
            <tr>
                <td>PHP Version: </td>
                <td><?php echo phpversion(); ?></td>
            </tr>
            <tr>
                <td>WordPress Version: </td>
                <td><?php echo $GLOBALS['wp_version']; ?></td>
            </tr>
            <tr>
                <td>Server Software: </td>
                <td><?php echo $_SERVER['SERVER_SOFTWARE']; ?></td>
            </tr>
        </table>
    </div>

    <div class="card tabletop">
        <form action="/wp-admin/admin-post.php" method="post">
	        <?php wp_nonce_field( PHPINFO_PREFIX . '_email_phpinfo', PHPINFO_PREFIX . '_nonce' ); ?>
            <input type="hidden" name="action" value="<?php echo PHPINFO_PREFIX; ?>_submit_phpinfo_form_action" />
            <input placeholder="Email Address(es) - Separate with commas"
                   type="text" name="emails" class="large-text" />
            <input type="submit" value="Email This Information" class="button action" />
            <input type="button" value="Copy This Information" id="copy-php-info" class="button action right" />
        </form>
    </div>

    <?php phpinfo_content(); ?>

</div>
<textarea style="height: 1px; width: 1px;" id="php-info-hidden"><?php

    $info = get_phpinfo_content();
    $info = str_replace( '</tr>', "\n", $info );
    $info = str_replace( '</td>', ' - ', $info );
    echo strip_tags( $info );

    ?></textarea>
