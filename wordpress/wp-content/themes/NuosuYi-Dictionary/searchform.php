<table width="100%">
<tr>
	<td>
	<h1 <?php if($qtransLanguage == "ii") {?> class="nuosu" <?php }?>><?php _e('Search for a word in Yi, Chinese, or English', 'dictrans'); ?></h1>
	</td>
	<td align=right>
	<table cellspacing=0 cellpadding=2>
		<tr valign=top>
			<td><?php echo qtrans_getLanguageLinks("text"); ?></td>
		</tr>
	</table>

	</td>
</tr>
</table>
<br>
<table class=tblSearchform width="100%">
<?php 
/*
<tr>
	<td style="padding-top: 5px;"><!-- Search Bar Popups --> <?php !dynamic_sidebar( 'topsearchbar' ); ?>
	<!-- end Search Bar Popups --></td>
</tr>
*/
?>
<tr>
	<td>
		<?php
		/*
		 * searchform supplied by plugin sil-dictionary
		 */
		if (function_exists('webonary_searchform'))
			webonary_searchform();
		
		/*
		 * Default searchform. Code from get_search_form() in WordPress's general-template.php.
		 */
		else {
			$form .= '<br><form role="search" method="get" id="searchform" action="' . home_url( '/' ) . '" >
				<div><label class="screen-reader-text" for="s">' . __('Search for:') . '</label>
				<input type="text" value="' . get_search_query() . '" name="s" id="s" />
				<input type="submit" id="searchsubmit" value="'. esc_attr__('Search') .'" />
				</div>
				</form>';		
			echo apply_filters('get_search_form', $form);
		}
		?>
	</td>
</tr>
</table>
		