<?php
/*
Plugin Name: Child News Widget
Plugin URI: http://www.itscanfixthat.com/web-design/wordpress/custom-plugins/child-news-widget/
Description: This plugin will display links to posts from a category in a widget that will be assigned to a page, and will display on that page and on all child pages.
Version: 2.0
Author: Michael Flynn, ITS Alaska
Author URI: http://www.itscanfixthat.com
License: GPL2
*/
add_action('init', 'child_news_widget_multi_register');
function child_news_widget_multi_register() {
	$prefix = 'child-news-widget';
	$widget_name = "child_news_widget";
	$name = __('Child News');
	$widget_ops = array('classname' => $widget_name.'_multi', 'description' => __('This plugin will display links to posts from a category in a widget that will be assigned to a page, and will display on that page and on all child pages.'));
	$control_ops = array('cat' => 0, 'parent' => 0, 'id_base' => $prefix);
	$options = get_option($widget_name.'_multi');
	if(isset($options[0])) unset($options[0]); 
	if(!empty($options)){
		foreach(array_keys($options) as $widget_number){
			wp_register_sidebar_widget($prefix.'-'.$widget_number, $name.": ". $options[$widget_number]['title'], $widget_name.'_multi', $widget_ops, array( 'number' => $widget_number ));
			wp_register_widget_control($prefix.'-'.$widget_number, $name.": ". $options[$widget_number]['title'], $widget_name.'_multi_control', $control_ops, array( 'number' => $widget_number ));
		}
	} else{
		$options = array();
		$widget_number = 1;
		wp_register_sidebar_widget($prefix.'-'.$widget_number, $name.": ". $options[$widget_number]['title'], $widget_name.'_multi', $widget_ops, array( 'number' => $widget_number ));
		wp_register_widget_control($prefix.'-'.$widget_number, $name.": ". $options[$widget_number]['title'], $widget_name.'_multi_control', $control_ops, array( 'number' => $widget_number ));
	}
}

function child_news_widget_multi($args, $vars = array()) {
	extract($args);
	$prefix = 'child-news-widget';
	$widget_name = "child_news_widget";
	$widget_number = (int)str_replace($prefix.'-', '', @$widget_id);
	$options = get_option($widget_name.'_multi');
	if(!empty($options[$widget_number])){
		$vars = $options[$widget_number];
	}
	$dpage = @get_page();
	$ancestors = (array) $dpage->ancestors;
	$show = false;
	if($dpage->ID == $vars['parent']) $show = true;
	foreach($ancestors as $key =>  $id){
		if($id == $vars['parent']) $show = true;	
	}
	if($show){
		echo $before_widget;
		if(!empty($vars['title'])){
			$vars['title'] = apply_filters( 'widget_title', empty( $vars['title'] ) ? '' : $vars['title'] );
			echo $before_title . $vars['title'] . $after_title;
		}
		echo "<div class='childnewswidget'>";
		$cat = $vars['cat'];
		$lim = $vars['lim'];
		
		global $post;
		$tmp_post = $post;
		$the_posts = get_posts('numberposts='.$lim.'&orderby=date&order=DESC&category='.$cat);
		foreach($the_posts as $post){
			setup_postdata($post);
			$f .= "<li><a href='".get_permalink()."'>".get_the_title()."</a></li>";
		}
		$post = $tmp_post;
		if(empty($f)) echo "There are no posts to display"; 
		else echo "<ul>".$f."</ul>";
		echo "</div>";
		echo $after_widget;
	}
}
function child_news_widget_multi_control($args) { 
	$prefix = 'child-news-widget';
	$widget_name = "child_news_widget";
	$options = get_option($widget_name.'_multi');
	if(empty($options)) $options = array();
	if(isset($options[0])) unset($options[0]);
	if(!empty($_POST[$prefix]) && is_array($_POST)){
		foreach($_POST[$prefix] as $widget_number => $values){
			if(empty($values) && isset($options[$widget_number]))
				continue;
			if(!isset($options[$widget_number]) && $args['number'] == -1){
				$args['number'] = $widget_number;
				$options['last_number'] = $widget_number;
			}
			$options[$widget_number] = $values;
			foreach($options[$widget_number] as $key => $val){
				if(!is_array($val)) $options[$widget_number][$key] == stripslashes(stripslashes(stripslashes($val)));
			} 
		}
		if($args['number'] == -1 && !empty($options['last_number'])){
			$args['number'] = $options['last_number'];
		}
		$options = bf_smart_multiwidget_update($prefix, $options, $_POST[$prefix], $_POST['sidebar'], $widget_name.'_multi');
	}
	$number = ($args['number'] == -1)? '%i%' : $args['number'];
	$opts = @$options[$number]; 
	$title = @$opts['title'];
	$parent = @$opts['parent'];
	$category = @$opts['cat'];
	$lim = @$opts['lim'];
	
	?>
    <b>Title</b><br />
		<input type="text" style="width:100%;" name="<?php echo $prefix; ?>[<?php echo $number; ?>][title]" value="<?php echo $title; ?>" /><br>
	  <b>Parent</b><br />
	  <style>.fixthisshit select{width:100%;}</style>
	  <div style="width:100%;" class="fixthisshit">
			<?php wp_dropdown_pages(array('selected' => $parent, 'echo' => 1, 'name' => $prefix."[".$number."][parent]")); ?><br>
		</div>
		<b>Category</b><br />
	  <div style="width:100%;" class="fixthisshit">
			<?php wp_dropdown_categories(array('selected' => $category, 'echo' => 1, 'name' => $prefix."[".$number."][cat]")); ?><br>
		</div>
		<b>Number of Posts to Show</b><br />
	  <div style="width:100%;" class="fixthisshit">
			<select name='<?php echo $prefix; ?>[<?php echo $number; ?>][lim]'>
				<option value='1' <?php echo ($lim == 1 ? "SELECTED" : "");?> >One</option>
				<option value='2' <?php echo ($lim == 2 ? "SELECTED" : "");?> >Two</option>
				<option value='3' <?php echo ($lim == 3 ? "SELECTED" : "");?> >Three</option>
				<option value='4' <?php echo ($lim == 4 ? "SELECTED" : "");?> >Four</option>
				<option value='5' <?php echo ($lim == 5 ? "SELECTED" : "");?> >Five</option>
				<option value='6' <?php echo ($lim == 6 ? "SELECTED" : "");?> >Six</option>
				<option value='7' <?php echo ($lim == 7 ? "SELECTED" : "");?> >Seven</option>
				<option value='8' <?php echo ($lim == 8 ? "SELECTED" : "");?> >Eight</option>
				<option value='9' <?php echo ($lim == 9 ? "SELECTED" : "");?> >Nine</option>
				<option value='10' <?php echo ($lim == 10 ? "SELECTED" : "");?> >Ten</option>
				<option value='11' <?php echo ($lim == 11 ? "SELECTED" : "");?> >Eleven</option>
				<option value='12' <?php echo ($lim == 12 ? "SELECTED" : "");?> >Twelve</option>
				<option value='13' <?php echo ($lim == 13 ? "SELECTED" : "");?> >Thirteen</option>
				<option value='14' <?php echo ($lim == 14 ? "SELECTED" : "");?> >Fourteen</option>
				<option value='15' <?php echo ($lim == 15 ? "SELECTED" : "");?> >Fifteen</option>
			</select>
		</div>
	<?php
}
if(!function_exists('bf_smart_multiwidget_update')){
	function bf_smart_multiwidget_update($id_prefix, $options, $post, $sidebar, $option_name = ''){
		global $wp_registered_widgets;
		static $updated = false;
		$sidebars_widgets = wp_get_sidebars_widgets();
		if ( isset($sidebars_widgets[$sidebar]) )
			$this_sidebar =& $sidebars_widgets[$sidebar];
		else
			$this_sidebar = array();
		foreach ( $this_sidebar as $_widget_id ) {
			if(preg_match('/'.$id_prefix.'-([0-9]+)/i', $_widget_id, $match)){
				$widget_number = $match[1];
				if(!in_array($match[0], $_POST['widget-id'])){
					unset($options[$widget_number]);
				}
			}
		}
		if(!empty($option_name)){
			update_option($option_name, $options);
			$updated = true;
		}
		return $options;
	}
}
?>
