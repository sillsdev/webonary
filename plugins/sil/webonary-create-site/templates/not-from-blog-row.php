<?php /** @noinspection PhpUndefinedVariableInspection */
?>
<tr class="form-required">
	<th scope='row'><?php _e('Choose Source Site to Copy', $this->_domain); ?></th>
	<td>
		<?php
		$template = 'template-english';
		if (isset($application['template-to-use']->field_value)) {
			$found = preg_match('!https?://.+/(\S+)!', $application['template-to-use']->field_value, $hrefs);
			if ($found !== false)
				$template = $hrefs[1];
		}
		?>
		<select name="source_blog" title="">
			<option value=""></option>
			<?php foreach ($blogs as $blog) { ?>
				<option
					value="<?php echo $blog->blog_id; ?>" <?php selected($blog->blog_id, $from_blog_id); ?> <?php if ($blog->path == $template) {
					echo 'selected';
				} ?>><?php echo $blog->path; ?></option>
			<?php } ?>
		</select>
	</td>
</tr>
<tr>
	<td>Other template:</td>
	<td><?php echo $application['other-template']->field_value; ?></td>
</tr>
