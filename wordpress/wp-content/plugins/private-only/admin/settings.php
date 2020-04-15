<?php
/*
Private Only 3.5.1
Website: http://pixert.com
*/
?>
<div style="float:right; width:33%;">

<div class="postbox open">

<h3>About The Author</h3>

<div class="inside">

	<ul>
    
    <li style="margin-bottom: 40px;">
      <h4><?php _e('This is Private Only plugin for WordPress','private-only'); ?></h4>
      <h5>Coded by Kate Mag (Pixel Insert)</h5>
      <p><?php _e('If you disable or enable feed, DO NOT FORGET TO REFRESH YOUR BROWSER CACHE AFTER ACTIVATE PRIVATE ONLY or DISABLE FEED','private-only'); ?></p>
    </li> 
    <li>
    <h4>Thank you for using this plugin on your site</h4>
   <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=L3J4LBDGP533Q">
<img src="https://www.paypal.com/en_US/i/btn/x-click-but21.gif" alt="" /></a>
    </li>   
        
		<li><iframe src="//www.facebook.com/plugins/like.php?href=http%3A%2F%2Fwww.facebook.com%2FPixelInsert&amp;send=false&amp;layout=standard&amp;width=300&amp;show_faces=false&amp;action=like&amp;colorscheme=light&amp;font&amp;height=35" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:300px; height:35px;" allowTransparency="true"></iframe></li>
		
		<li><a href="https://twitter.com/share" class="twitter-share-button" data-url="http://pixert.com" data-text="Pixel Insert/Pixert" data-related="pixert" data-count="none">Tweet</a>
<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
<a href="https://twitter.com/pixert" class="twitter-follow-button" data-show-count="false">Follow @pixert</a>
<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script></li>
        
		<li>Visit Our <a href="http://pixert.com/blog">Blog</a></li>
	</ul>
    
</div>
</div>

</div> <!-- /float:right -->

<div style="float:left; width:66%;">

<div class="postbox open">
<?php $visibility = get_option('blog_public'); 
$visibilityadminpage = get_admin_url('', 'options-reading.php');
if ($visibility == 0) { ?>
<div class="error">
<h3><?php _e('This site is hidden from Search Engine. <a href="'.$visibilityadminpage.'">WordPress block access to Robots</a>','private-only'); ?></h3>
</div>
<?php }  ?>
<?php if ($visibility == 1)  { ?>
<div class="error">
<h3><?php _e('This site is visible to search engines, go to the <a href="'.$visibilityadminpage.'">reading settings</a> page and select the "Discourage search engines from indexing this site" box to hide this site.','private-only'); ?></h3>
</div>
<?php } ?>
</div>

<div class="postbox open">

<h3><?php _e('Custom Login','private-only'); ?></h3>

</div>

<div class="postbox open">
<div class="inside">
	<table class="form-table">
   		<tr>
            <th>
            	<label for="<?php echo $data['public_pages']; ?>"><?php _e('Public Page:','private-only'); ?></label> 
            </th>
            <td>
            	<?php
            		$settings = get_option( 'po_login_settings' );
            		$selected = $settings['public_pages'];
            		$args = array(
            			'id' => $data['public_pages'],
            			'name' => $data['public_pages'],
            			'selected' => $selected,
            			'show_option_none' => '-',
            			'option_none_value' => ''
            		);
            		wp_dropdown_pages( $args ); 
            	?>
                <br /><?php _e('Define the public page','private-only'); ?><br />
            </td>
   		</tr>
    </table>

</div>
</div>

<div class="postbox open">
<div class="inside">
	<table class="form-table">
   		<tr>
            <th>
            	<label for="<?php echo $data['po_logo']; ?>"><?php _e('Logo:','private-only'); ?></label> 
            </th>
            <td>
               <input id="<?php echo $data['po_logo']; ?>" name="<?php echo $data['po_logo']; ?>" value="<?php if (isset($val['po_logo']) && !empty($val['po_logo'])) echo $val['po_logo']; ?>" size="40" /><br />
                <?php _e('Upload an image with Media Library or FTP and put the full path here, http://yourdomainname.com/logo.jpg','private-only'); ?><br />
                <?php _e('We do not provide upload tool here, because it is free for you upload it wherever you want to','private-only'); ?><br />
            </td>
   		</tr>
   	   	<tr>
            <th>
            	<label for="<?php echo $data['po_logo_height']; ?>"><?php _e('Logo Height:','private-only'); ?></label> 
            </th>
            <td>
               <input id="<?php echo $data['po_logo_height']; ?>" name="<?php echo $data['po_logo_height']; ?>" value="<?php if (isset($val['po_logo_height']) && !empty($val['po_logo_height'])) echo $val['po_logo_height']; ?>" size="40" /><br />
                <?php _e('What is your logo height','private-only'); ?>
            </td>
   		</tr>
    </table>

</div>
</div>

<div class="postbox open">
<div class="inside">
	<table class="form-table">
   		<tr>
            <th>
            	<label for="<?php echo $data['use_wp_logo']; ?>"><?php _e('Use WordPress logo','private-only'); ?>:</label> 
            </th>
            <td>
                <input name="<?php echo $data['use_wp_logo']; ?>" type="hidden" value="no" />
                <input id="<?php echo $data['use_wp_logo']; ?>" name="<?php echo $data['use_wp_logo']; ?>" type="checkbox" <?php if (isset($val['use_wp_logo']) && $val['use_wp_logo'] == "true") echo 'checked="checked"'; ?> value="true" /><br />
                <?php _e('Check this box to use WordPress Logo, leave unchecked to disable it.','private-only'); ?>
            </td>
   		</tr>
   		<tr>
            <th>
            	<label for="<?php echo $data['logo_url']; ?>"><?php _e('Change WordPress logo link','private-only'); ?>:</label> 
            </th>
            <td>
               <input id="<?php echo $data['logo_url']; ?>" name="<?php echo $data['logo_url']; ?>" value="<?php if (isset($val['logo_url']) && !empty($val['logo_url'])) echo $val['logo_url']; ?>" size="40" /><br />
                <?php _e('Change WordPress logo link from wordpress.org to your domain, http://yourdomainname.com','private-only'); ?><br />
            </td>

   		</tr>
   		<tr>
            <th>
            	<label for="<?php echo $data['login_message']; ?>"><?php _e('Your Custom Login Message','private-only'); ?>:</label> 
            </th>
            <td>
               <textarea id="<?php echo $data['login_message']; ?>" name="<?php echo $data['login_message']; ?>" rows="3" cols="50"><?php if (isset($val['login_message']) && !empty($val['login_message'])) echo $val['login_message']; ?></textarea><br />
                <?php _e('Change default login message to your own','private-only'); ?><br />
            </td>

   		</tr>
   		<tr>
            <th>
            	<label for="<?php echo $data['remove_lost_password']; ?>"><?php _e('Remove Register and Lost Your Password? text','private-only'); ?>:</label> 
            </th>
            <td>
                <input name="<?php echo $data['remove_lost_password']; ?>" type="hidden" value="no" />
                <input id="<?php echo $data['remove_lost_password']; ?>" name="<?php echo $data['remove_lost_password']; ?>" type="checkbox" <?php if (isset($val['remove_lost_password']) && $val['remove_lost_password'] == "true") echo 'checked="checked"'; ?> value="true" /><br />
                <?php _e('Check this box to remove Register and Lost Your Password? text on WP-Admin login, leave unchecked to disable it.','private-only'); ?>
            </td>
   		</tr>
   		<tr>
            <th>
            	<label for="<?php echo $data['remove_backtoblog']; ?>"><?php _e('Remove Back to Blog link?','private-only'); ?>:</label> 
            </th>
            <td>
                <input name="<?php echo $data['remove_backtoblog']; ?>" type="hidden" value="no" />
                <input id="<?php echo $data['remove_backtoblog']; ?>" name="<?php echo $data['remove_backtoblog']; ?>" type="checkbox" <?php if (isset($val['remove_backtoblog']) && $val['remove_backtoblog'] == "true") echo 'checked="checked"'; ?> value="true" /><br />
                <?php _e('Check this box to remove Back to Blog link on WP-Admin login, leave unchecked to disable it.','private-only'); ?>
            </td>
   		</tr>
    </table>
</div>
</div>

<div class="postbox open">
<div class="inside">
	<table class="form-table">
   		<tr>
            <th>
            	<label for="<?php echo $data['use_custom_css']; ?>"><?php _e('Use Custom CSS','private-only'); ?>:</label> 
            </th>
            <td>
                <input name="<?php echo $data['use_custom_css']; ?>" type="hidden" value="no" />
                <input id="<?php echo $data['use_custom_css']; ?>" name="<?php echo $data['use_custom_css']; ?>" type="checkbox" <?php if (isset($val['use_custom_css']) && $val['use_custom_css'] == "true") echo 'checked="checked"'; ?> value="true" /><br />
                <?php _e('Check this box to use Custom CSS, leave unchecked to disable it. You should have custom css in your active theme','private-only'); ?>
            </td>
   		</tr>
    </table>
</div>
</div>


</div> <!-- /float:left -->

<br style="clear:both;" />
