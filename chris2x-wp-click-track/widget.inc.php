<?php
/**
 * WP-Click-Tracker Sidebar Widget Module
 *
 * Contains all the widget functions.
 *
 * @author Eric Lamb <eric@ericlamb.net>
 * @package    wp-click-tracker
 * @version 0.6
 * @filesource
 * @copyright 2009 Eric Lamb.
 */	

/**
 * Displays the actual sidebar header
 *
 * @param   string  $args
 * @return  array
 */
function wpct_sidebar_header($args)
{
	return;
	//echo "\n<!-- wp-click-track Sidebar widget -->\n<link rel=\"stylesheet\" href=\"".wpct_get_siteurl()."/wp-content/plugins/".basename(dirname(__FILE__))."/css/wp_click_track_sidebar.css\" type=\"text/css\" media=\"screen\" />\n";
}

/**
 * Displays "today's clicks" sidebar data
 *
 * @return  void
 */
function wpct_get_todays_sidebar_widget()
{
	$options = get_option('click_tracker');
	$result = wpct_get_todays_links($options['sidebar_todays_click_limit']);
	$count = count($result);
	echo '<ul>';
	for($i=0;$i<$count;$i++){
		echo '<li><a href="'.$result[$i]->link_destination.'" target="_blank" title="'.$result[$i]->link_title.' :: '.number_format_i18n($result[$i]->click_count).' '.__('Clicks', 'wp-click-track').'" >'.$result[$i]->link_title.' ('.number_format_i18n($result[$i]->click_count).')</a></li>';
		echo "\n";
	}
	echo '</ul>';
}

/**
 * Displays the "top links" sidebar data
 *
 * @return  void
 */
function wpct_get_top_links_sidebar_widget()
{
	$options = get_option('click_tracker');
	$result = wpct_get_link_data(" ORDER BY link_total_clicks DESC, link_unique_clicks DESC LIMIT ". $options['sidebar_top_click_limit']);
	$count = count($result);
	echo '<ul>';
	for($i=0;$i<$count;$i++){
		echo '<li><a href="'.$result[$i]->link_destination.'" target="_blank" title="'.$result[$i]->link_title.' :: '.number_format_i18n($result[$i]->link_total_clicks).' '.__('Clicks', 'wp-click-track').'" >'.$result[$i]->link_title.' ('.number_format_i18n($result[$i]->link_total_clicks).')</a></li>';
		echo "\n";
	}
	echo '</ul>';
}

/**
 * Inititialize the sidebars
 *
 * @return  void
 */
function wpct_sidebar_init(){
	global $wpdb;

	if( !function_exists('register_sidebar_widget') || !function_exists('register_widget_control') )
		return;

	register_sidebar_widget(__('Todays Clicks', 'wp-click-track'), 'wpct_get_todays_clicks_sidebar');
	register_widget_control(__('Todays Clicks', 'wp-click-track'), 'widget_wpct_todays_clicks_control');

	register_sidebar_widget(__('Top Links', 'wp-click-track'), 'wpct_get_top_clicks_sidebar');
	register_widget_control(__('Top Links', 'wp-click-track'), 'wpct_top_links_control');
}

/**
 * Today's clicks sidebar control
 *
 * @return  void
 */
function widget_wpct_todays_clicks_control(){

	$options = get_option('click_tracker');
	if(isset($_POST['sidebar_todays_click_limit'])){
		$sidebar_top_click_limit = (isset($_POST['sidebar_todays_click_limit']) ? (int)$_POST['sidebar_todays_click_limit'] : (int)$options['sidebar_todays_click_limit']);
		$options['sidebar_todays_click_limit'] = $sidebar_top_click_limit;
		update_option('click_tracker', $options);
	}
	echo '<p><label for="sidebar_top_click_limit">'.__('# To Display:', 'wp-click-track').' <input class="widefat" id="sidebar_todays_click_limit" name="sidebar_todays_click_limit" value="'.$options['sidebar_todays_click_limit'].'" type="text" style="width:60px"></label></p>';
}

/**
 * Today's clicks sidebar sidebar wrapper
 *
 * @param   string  $args	amount of rows to return
 * @return  void
 */
function wpct_get_todays_clicks_sidebar($args)
{
	extract($args);
	echo "<!-- Start wp-click-track widget -->\n";
	echo $before_widget . $before_title . $widget_name . $after_title;
	wpct_get_todays_sidebar_widget();
	echo $after_widget;
	echo "\t<!-- End wp-click-track widget -->\n";
}

/**
 * Top Links sidebar control
 *
 * @return  array
 */
function wpct_top_links_control(){

	$options = get_option('click_tracker');
	if(isset($_POST['sidebar_top_click_limit'])){
		$sidebar_top_click_limit = (isset($_POST['sidebar_top_click_limit']) ? (int)$_POST['sidebar_top_click_limit'] : (int)$options['sidebar_top_click_limit']);
		$options['sidebar_top_click_limit'] = $sidebar_top_click_limit;
		update_option('click_tracker', $options);
	}
	echo '<p><label for="sidebar_top_click_limit">'.__('# To Display:', 'wp-click-track').' <input class="widefat" id="sidebar_top_click_limit" name="sidebar_top_click_limit" value="'.$options['sidebar_top_click_limit'].'" type="text" style="width:60px"></label></p>';
}

function wpct_get_top_clicks_sidebar($args)
{
	extract($args);
	echo "<!-- Start wp-click-track widget -->\n";
	echo $before_widget . $before_title . $widget_name . $after_title;
	wpct_get_top_links_sidebar_widget();
	echo $after_widget;
	echo "\t<!-- End wp-click-track widget -->\n";
}
