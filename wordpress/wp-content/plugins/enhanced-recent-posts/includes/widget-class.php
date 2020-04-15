<?php
/*  Copyright 2006 MarvinLabs / Vincent Mimoun-Prat

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/


//#################################################################
// Stop direct call
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { 
	die('You are not allowed to call this page directly.'); 
}
//#################################################################

//#################################################################
// Some constants 
//#################################################################

//#################################################################
// The Widget class
if (!class_exists("EnhancedRecentPostsWidget")) {

class EnhancedRecentPostsWidget {
	var $options;
	
	/**
	* Constructor
	*/
	function EnhancedRecentPostsWidget() {
		$this->load_options();
	}
		
	/**
	* Function to register the Widget functions
	*/
	function register_widget() {
		$name = __('Enhanced Recent Posts', ENHANCED_RECENT_POSTS_I18N_DOMAIN);
		$control_ops = array(
			'width' => 400, 'height' => 350, 
			'id_base' => 'enh-rp');
		$widget_ops = array(
			'classname' => 'enh_rp', 
			'description' => __('Widget that improves the built-in WordPress recent posts widget.', 
								ENHANCED_RECENT_POSTS_I18N_DOMAIN));

		if (!is_array($this->options)) {
			$this->options = array();
		}
								
		$registered = false;
		foreach (array_keys($this->options) as $o) {
			// Old widgets can have null values for some reason
			//--
			if (	!isset($this->options[$o]['posts_to_show']))
				continue;
			
			// $id should look like {$id_base}-{$o}
			//--
			$id = "enh-rp-$o";
			$registered = true;
			wp_register_sidebar_widget( 
				$id, $name, 
				array(&$this, 'render_widget'), 
				$widget_ops, array( 'number' => $o ) );
			wp_register_widget_control( 
				$id, $name, 
				array(&$this, 'render_control_panel'), 
				$control_ops, array( 'number' => $o ) );
		}

		// If there are none, we register the widget's existance with a generic template
		//--
		if (!$registered) {
			wp_register_sidebar_widget( 
				'enh-rp-1', $name, 
				array(&$this, 'render_widget'), 
				$widget_ops, array( 'number' => -1 ) );
			wp_register_widget_control( 
				'enh-rp-1', $name, 
				array(&$this, 'render_control_panel'), 
				$control_ops, array( 'number' => -1 ) );
		}
	}
	
	/**
	* Function to render the widget control panel
	*/
	function render_control_panel($widget_args=1) {
		global $wp_registered_widgets;
		static $updated = false;
		
		// Get the widget ID
		//--
		if (is_numeric($widget_args)) {
			$widget_args = array('number' => $widget_args);
		}
		$widget_args = wp_parse_args($widget_args, array('number' => -1));
		extract($widget_args, EXTR_SKIP);
	
		if (!$updated && !empty($_POST['sidebar'])) {
			$sidebar = (string) $_POST['sidebar'];

			$sidebars_widgets = wp_get_sidebars_widgets();
			if ( isset($sidebars_widgets[$sidebar]) )
				$this_sidebar = &$sidebars_widgets[$sidebar];
			else
				$this_sidebar = array();

			foreach ( $this_sidebar as $_widget_id ) {
				// Remove all widgets of this type from the sidebar.  We'll add the new data in a second.  This makes sure we don't get any duplicate data
				// since widget ids aren't necessarily persistent across multiple updates
				//--
				if (	'enh_rp' == $wp_registered_widgets[$_widget_id]['classname'] 
					&& 	isset($wp_registered_widgets[$_widget_id]['params'][0]['number']) ) {
					
					$widget_number = $wp_registered_widgets[$_widget_id]['params'][0]['number'];
					if (!in_array( "enh-rp-$widget_number", $_POST['widget-id'])) // the widget has been removed.
						unset($this->options[$widget_number]);
				}
			}

			foreach ( (array) $_POST['widget_enh_rp'] as $widget_number => $widget_enh_rp ) {
				if ( !isset($widget_enh_rp['posts_to_show']) && isset($this->options[$widget_number]) ) // user clicked cancel
					continue;
					
				$this->options[$widget_number]['widget_title'] 	= strip_tags(stripslashes($widget_enh_rp['widget_title']));
				$this->options[$widget_number]['include_cat']	= strip_tags(stripslashes($widget_enh_rp['include_cat']));
				$this->options[$widget_number]['exclude_cat']	= strip_tags(stripslashes($widget_enh_rp['exclude_cat']));
				$this->options[$widget_number]['show_select']	= $widget_enh_rp['show_select'];
				$this->options[$widget_number]['posts_to_show']	= $widget_enh_rp['posts_to_show'];
				$this->options[$widget_number]['orderby']		= $widget_enh_rp['orderby'];
				$this->options[$widget_number]['show_date']  	= $widget_enh_rp['show_date'];
			}

			$this->save_options();
			$updated = true;
		}

		if ( -1 == $number ) {
			$widget_title 	= '';
			$posts_to_show 	= 5;
			$show_select 	= 'show-all';
			$include_cat 	= '';
			$exclude_cat 	= '';
			$number 		= '__i__';
			$show_date 		= 'off';
			$orderby 		= 'date';
		} else {
			$widget_title 	= esc_attr($this->options[$number]['widget_title']);
			$posts_to_show 	= $this->options[$number]['posts_to_show'];
			$show_select 	= $this->options[$number]['show_select'];
			$include_cat 	= $this->options[$number]['include_cat'];
			$exclude_cat 	= $this->options[$number]['exclude_cat'];
			$orderby 		= $this->options[$number]['orderby'];
			$show_date 		= $this->options[$number]['show_date'];
		}
		
		if ($posts_to_show<1) {
			$posts_to_show = 1;
		}

		// The widget control
		//--
		
	?>
	
<input type="hidden" id="enh_rp-submit-<?php echo $number; ?>" name="widget_enh_rp[<?php echo $number; ?>][submit]" value="1" />
<p>
	<label><?php _e('Title:', ENHANCED_RECENT_POSTS_I18N_DOMAIN); ?><br/>
	<input style="width: 250px;" id="enh_rp-widget_title-<?php echo $number; ?>" name="widget_enh_rp[<?php echo $number; ?>][widget_title]" type="text" value="<?php echo $widget_title; ?>" /></label>
</p>

<br/>

<p>
	<label><?php _e('Number of posts to show:', ENHANCED_RECENT_POSTS_I18N_DOMAIN); ?>
	<br/>
	<input style="width: 250px;" id="enh_rp-posts_to_show-<?php echo $number; ?>" name="widget_enh_rp[<?php echo $number; ?>][posts_to_show]" type="text" value="<?php echo $posts_to_show; ?>" /></label>
</p>

<p>
	<label><?php _e('Order posts by:', ENHANCED_RECENT_POSTS_I18N_DOMAIN); ?><br/>
	<select id="enh_rp-orderby-<?php echo $number; ?>" name="widget_enh_rp[<?php echo $number; ?>][orderby]">
		<option value="date" <?php $this->render_selected($orderby=='date'); ?>><?php _e('Publication date', ENHANCED_RECENT_POSTS_I18N_DOMAIN); ?></option>
		<option value="modified" <?php $this->render_selected($orderby=='modified'); ?>><?php _e('Modification date', ENHANCED_RECENT_POSTS_I18N_DOMAIN); ?></option> 
		<option value="rand" <?php $this->render_selected($orderby=='rand'); ?>><?php _e('Random', ENHANCED_RECENT_POSTS_I18N_DOMAIN); ?></option>
	</select>
	</label>
</p>

<script type="text/javascript">
jQuery(document).ready(function($){
	$('#enh_rp-show-select-<?php echo $number; ?>').change(function() {
		if ($(this).val() == 'show-all') {
			$('#include-tab-<?php echo $number; ?>').filter(':visible').slideUp();
			$('#exclude-tab-<?php echo $number; ?>').filter(':visible').slideUp();
		} else if ($(this).val() == 'show-include') {
			$('#include-tab-<?php echo $number; ?>').filter(':hidden').slideDown();
			$('#exclude-tab-<?php echo $number; ?>').filter(':visible').slideUp();
		} else {
			$('#include-tab-<?php echo $number; ?>').filter(':visible').slideUp();
			$('#exclude-tab-<?php echo $number; ?>').filter(':hidden').slideDown();
		}
	}).change();
	
	$('#enh_rp-show-select-<?php echo $number; ?>').val('<?php echo $show_select; ?>');
});
</script>

<p>
	<label><?php _e('Extract posts from:', ENHANCED_RECENT_POSTS_I18N_DOMAIN); ?><br/>
	<select id="enh_rp-show-select-<?php echo $number; ?>" name="widget_enh_rp[<?php echo $number; ?>][show_select]">
		<option value="show-all" <?php $this->render_selected($show_select=='show-all'); ?>><?php _e('All categories', ENHANCED_RECENT_POSTS_I18N_DOMAIN); ?></option>
		<option value="show-include" <?php $this->render_selected($show_select=='show-include'); ?>><?php _e('Some categories', ENHANCED_RECENT_POSTS_I18N_DOMAIN); ?></option> 
		<option value="show-exclude" <?php $this->render_selected($show_select=='show-exclude'); ?>><?php _e('All but some categories', ENHANCED_RECENT_POSTS_I18N_DOMAIN); ?></option> 		
	</select>
	</label>
</p>

<p id="include-tab-<?php echo $number; ?>">
	<label><?php _e('Show posts only from these categories (list of comma-separated IDs):', ENHANCED_RECENT_POSTS_I18N_DOMAIN); ?>
	<br/>
	<input style="width: 250px;" id="enh_rp-include_cat-<?php echo $number; ?>" name="widget_enh_rp[<?php echo $number; ?>][include_cat]" type="text" value="<?php echo $include_cat; ?>" /></label>
</p>

<p id="exclude-tab-<?php echo $number; ?>">
	<label><?php _e('Hide posts from these categories (list of comma-separated IDs):', ENHANCED_RECENT_POSTS_I18N_DOMAIN); ?>
	<br/>
	<input style="width: 250px;" id="enh_rp-exclude_cat-<?php echo $number; ?>" name="widget_enh_rp[<?php echo $number; ?>][exclude_cat]" type="text" value="<?php echo $exclude_cat; ?>" /></label>
</p>
<p>
	<label><?php _e('Show date?:', ENHANCED_RECENT_POSTS_I18N_DOMAIN); ?>&nbsp;
	<input id="enh_rp-show_date-<?php echo $number; ?>" name="widget_enh_rp[<?php echo $number; ?>][show_date]" type="checkbox" <?php $this->render_checked($show_date=='on'); ?>  /></label>
</p>
<?php
	}
	
	/**
	* Function to render the widget
	*/
	function render_widget($args, $widget_args=1) {
		global $enh_rp_plugin;
		
		// Get the options
		//--
		extract($args, EXTR_SKIP);	
		if (is_numeric($widget_args)) {
			$widget_args = array('number' => $widget_args);
		}
		$widget_args = wp_parse_args($widget_args, array('number' => -1));
		extract($widget_args, EXTR_SKIP);
		
		$title = empty($this->options[$number]['widget_title']) 
					? __('Recent Posts', ENHANCED_RECENT_POSTS_I18N_DOMAIN) 
					: $this->options[$number]['widget_title'];

		echo '<!-- Enhanced Recent Posts ' . $enh_rp_plugin->options['active_version'] . ' -->';	
		
		echo $before_widget; 
			echo $before_title . $title . $after_title;
			$enh_rp_plugin->list_recent_posts($this->options[$number]); 
		echo $after_widget;
		
		echo '<!-- Enhanced Recent Posts ' . $enh_rp_plugin->current_version . ' -->';
	}
	
	/**
	* Load the options from database (set default values in case options are not set)
	*/
	function load_options() {
		$this->options = get_option(ENHANCED_RECENT_POSTS_WIDGET_OPTIONS);
		
		if ( !is_array($this->options) ) {
			$this->options = array();
		}
	}
	
	/**
	* Save options to database
	*/
	function save_options() {
		update_option(ENHANCED_RECENT_POSTS_WIDGET_OPTIONS, $this->options);
	}
	
	/**
	* Helper function to output the checked attribute of a checkbox
	*/
	function render_checked($var) {
		if ($var==1 || $var==true) {
			echo 'checked="checked"';
		}
	}
	
	/**
	* Helper function to output the selected attribute of an option
	*/
	function render_selected($var) {
		if ($var==1 || $var==true) {
			echo 'selected="selected"';
		}
	}
} // class EnhancedRecentPostsWidget

} // if (!class_exists("EnhancedRecentPostsWidget"))
//#################################################################



?>