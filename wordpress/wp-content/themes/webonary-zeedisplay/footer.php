<?php
global $blog_id;
?>
<script language="javascript" type="text/javascript">
<!--
function popitup(url) {
	newwindow=window.open(url,'name','height=600,width=500,scrollbars=yes');
	if (window.focus) {newwindow.focus()}
	return false;
}
// -->
</script>
	<div style="clear: both;"></div>
	<?php wp_footer();?>
		<?php
		$options = get_option('themezee_options');
		if(is_active_sidebar('sidebar-footer')) : ?>
		<div id="bottombar">
			<ul>
				<?php dynamic_sidebar('sidebar-footer'); ?>
			</ul>
			<div style="clear: both;"></div>
		</div>
		<?php endif; ?>
		
		<?php
		
		if (has_nav_menu('top_navi'))
		{
			$color="pink";
		}
		else
		{
			$color="blue";
		}
		?>
		<div id="footer" class=<?php echo $color; ?> style="text-align:center; padding-bottom: 8px; font-size:12px;">
			<div style="float:left;"><?php echo  $options['themeZee_footer']; ?></div>
			<a href="http://creativecommons.org/licenses/by-nc-sa/3.0/" target="_blank"><img src="<?php echo  get_template_directory_uri(); ?>/images/creative-commons.png" style="float:right; vertical-align:text-bottom"></a>
			<hr style="font-size:5px; margin-bottom: 4px; clear:both;">
			<div style="float:left; width: 230px; text-align:left;"><img src="<?php echo  get_template_directory_uri(); ?>/images/sil-icon.gif" style="vertical-align:middle;"> <span style="width: 20%; margin-left:10px;">© <?php echo "2013 - " . date("Y"); ?> <a href="http://www.sil.org" target="_blank">SIL International</a><sup>®</sup></span></div>
			<img src="<?php echo  get_template_directory_uri(); ?>/images/webonary-icon.png" style="vertical-align:middle;"> <span style="margin-right:30px;"><a href="http://www.webonary.org" target="_blank">Webonary.org</a></span>
			<div style="float:right; width: 200px; text-align:right;"><a href="http://webonary.org/sil-international-terms-of-service-for-webonary-org/" style="width:20%;">Terms of Service</a></div>
		</div>
	</div>
</div>
</body>
</html>
<?php //echo var_dump($wpdb->queries); ?>