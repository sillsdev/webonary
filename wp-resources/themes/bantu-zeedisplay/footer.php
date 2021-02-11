<div class="clear"></div>
		
	<?php if(is_active_sidebar('sidebar-footer')) : ?>
	<div id="bottombar">
		<ul>
			<?php dynamic_sidebar('sidebar-footer'); ?>
		</ul>
		<div class="clear"></div>
	</div>
	<?php endif; ?>
		
	<div id="footer">
		<?php
		$options = get_option('themezee_options');
		if ( isset($options['themeZee_footer']) and $options['themeZee_footer'] <> "" ) {
			echo  esc_attr($options['themeZee_footer']); }
		if ( isset($options['themeZee_footer_right']) and $options['themeZee_footer_right'] <> "" ) {
			?>
			<!-- Original code: <div class="credit_link">Theme by <a href="<?php /*echo THEME_INFO; */ ?>">ThemeZee.com</a></div>-->
			<div class="credit_link"><?php echo  esc_attr($options['themeZee_footer_right']); ?></div>
			<div class="clear"></div><?php
		} ?>
	</div>
	
	<?php wp_footer(); ?>
</body>
</html>