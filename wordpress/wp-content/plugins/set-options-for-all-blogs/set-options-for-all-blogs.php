<?php
/*
 Plugin Name: Multisite Set Options
 Plugin URI: http://www.webonary.org
 Description: Sets Options for all Blogs
 Author: adapted from https://gist.github.com/davejamesmiller
 Author URI: http://www.sil.org/
 Text Domain: multisite-set-options
 Version: 0.2
 Stable tag: 0.1
 License: GPL v2 - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */
// I used this code snippet as part of a WordPress Multisite plugin, to allow me
// to quickly set some options for all the blogs on a WordPress Multisite
// installation.

// Also see: Automatically enable plugins in new WordPress Multisite blogs -
// https://gist.github.com/1966425


// This function does the actual work
function djm_update_blog($blog_id = null, $updateAll = false)
{
	global $wpdb;

    if ($blog_id) {
        switch_to_blog($blog_id);
    }
    //================================================================================

    // Put the update code here
    // For example:
    /*
    $contactOpt = get_option('fs_contact_form1');
    $contactOpt['email_from'] = "webonary@sil.org";
	$contactOpt['email_from_enforced'] = true;
	update_option('fs_contact_form1', $contactOpt);
	*/

    /*
    $themeOptions = get_option('themezee_options');
    $themeOptions['themeZee_logo'] = "https://www.webonary.org/wp-content/uploads/webonary.png";
    update_option('themezee_options', $themeOptions);
    */

    /*
    $sql = "UPDATE wp_" . $blog_id . "_posts " .
      		" SET post_content = replace([post_content],'httpss:','https:')";

   	$wpdb->query( $sql );
    */

    /*
    $recaptchaOptions = get_option('wpcf7');
    $recaptchaKey = array_keys($recaptchaOptions['recaptcha']);
	$recaptchaOptions['recaptcha'] = array( "6LcrT4IUAAAAAGQ0gUJ_MbnBlAvAA5op6J_8GQwk" => "6LcrT4IUAAAAABQ6xN0N_4cP6f2nnapOZHIh4hyH");
    update_option('wpcf7', $recaptchaOptions);
	*/

    $sql = "UPDATE wp_" . $blog_id . "_postmeta " .
    		" SET meta_value = replace(meta_value,'[text* your-name]','[text* your-name akismet:author]')
    		WHERE meta_key = '_form'";

    $wpdb->query( $sql );

    echo $sql . "<br>";

    $sql = "UPDATE wp_" . $blog_id . "_postmeta " .
    		" SET meta_value = replace(meta_value,'[email* your-email]','[email* your-email akismet:author_email]')
    		WHERE meta_key = '_form'";

    $wpdb->query( $sql );

    echo $sql . "<br>";


    //================================================================================
    if ($blog_id) {
        restore_current_blog();
    }
}

// This creates a page in the admin area to run this for either the current blog
// (for testing) or every blog
add_action('admin_menu', 'djm_admin_menu');

function djm_admin_menu()
{
    add_submenu_page(
        'ms-admin.php',
        'Update All Blogs',
        'Update All Blogs',
        'manage_network',
        'update-all-blogs',
        'djm_update_page'
    );
}

function djm_update_page()
{
    global $wpdb;

    if (!empty($_POST['update_this'])) {

        // Update This Blog
        djm_update_blog(get_current_blog_id());
        $message = __('Blog updated.');

    } elseif (!empty($_POST['update_all'])) {

        // Update All Blogs
        $blogs = $wpdb->get_results("
            SELECT blog_id
            FROM {$wpdb->blogs}
            WHERE site_id = '{$wpdb->siteid}'
            AND archived = '0'
            AND spam = '0'
            AND deleted = '0'
            ORDER BY blog_id ASC
        ");

        foreach ($blogs as $blog) {
            djm_update_blog($blog->blog_id, true);
        }

        $message = __('All blogs updated.');

    }
    ?>

    <div class="wrap">
        <h2>Update All Blogs</h2>

        <?php if ($message) { ?>
            <div class="updated"><p><strong><?php echo $message ?></strong></p></div>
        <?php } ?>

        <form action="" method="post">
            <p>Use this form to run the update script for this blog or all network blogs.</p>
            <p><input type="submit" name="update_this" class="button" value="<?php esc_attr_e('Update This Blog') ?>" /></p>
            <p><input type="submit" name="update_all" class="button" value="<?php esc_attr_e('Update All Blogs') ?>" onclick="return confirm('Are you sure you want to run the update for all blogs?');" /></p>
        </form>
    </div>

    <?php
}