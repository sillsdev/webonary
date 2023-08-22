<?php /** @noinspection PhpUndefinedVariableInspection */
?>
<tr>
    <th scope='row'><?php _e('Source Blog to Copy', $this->_domain); ?></th>
    <td>
        <strong><?php printf('<a href="%s" target="_blank">%s</a>', $from_blog->siteurl, $from_blog->blogname); ?></strong>
        <input type="hidden" name="source_blog" value="<?php echo $copy_id; ?>"/>
    </td>
</tr>
