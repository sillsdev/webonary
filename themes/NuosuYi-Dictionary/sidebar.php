<?php
/** @noinspection PhpMultipleClassDeclarationsInspection */

class My_Page_Walker extends Walker_Page
{
    /**
     * Filter in the classes for parents.
     */
    function _filterClass( $class )
    {
        $class[] = 'parent'; // change this to whatever classe(s) you require
        return $class;
    }

	public function start_el(&$output, $data_object, $depth = 0, $args = array(), $current_object_id = 0): void
	{
		if (!empty($args['has_children']))
			add_filter('page_css_class', array(&$this, '_filterClass'));

		parent::start_el($output, $data_object, $depth, $args, $current_object_id);

		if (!empty($args['has_children']))
			remove_filter('page_css_class', array(&$this, '_filterClass'));
	}
}
?>
<!-- menu -->
	 <div id="menu">
	<?php //if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar('leftsidebar')) : ?>
			<img src="http://nuosuyi.webonary.org/files/logo.png" alt="logo">
			<p>
			<?php
			$pages = "";
			$pages = get_id_by_slug('home');
			$pages .= "," . get_id_by_slug('copyright');

			if (function_exists('qtrans_init'))
				$qtransLanguage = qtrans_getLanguage();
			?>

			<ul id="top-menu" class="nav sf-vertical sf-menu <?php if($qtransLanguage ?? '' == "ii") {?>nuosumenu<?php }?>">
				<li><a href="<?php echo get_option('home'); ?>"><?php _e('Home', 'dictrans'); ?></a></li>
			<?php
			//the function "remove_title" gets called in function.php to remove the tooltips
			//wp_list_pages('exclude=' . $pages . '&title_li=');
			wp_list_pages(array(
			    'walker'   => new My_Page_Walker,
				'exclude' => $pages,
			    'title_li' => ''
			));
			?>
			</ul>
			<br />

	<?php //endif; ?>
	</div>
	<div class=menubottom><img src="http://nuosuyi.webonary.org/files/partners.png" alt="partners"></div>
<!-- end menu -->
<?php
global $user_ID;
if( $user_ID ){
	if( current_user_can('level_10') )
	{
?>
		<br>
		<div align=center>
			<a href="<?php echo get_option('siteurl');?>/wp-admin/" style="font-size:12px">Admin Dashboard</a>
		</div>
<?php
	}
}
