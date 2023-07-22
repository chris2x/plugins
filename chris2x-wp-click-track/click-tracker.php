<?php
/**
 * WP-Click-Tracker
 *
 * Contains all the click tracker functions.
 *
 * @author Chris Christensen <chris2x@chris2x.com>
 * @package    chris2x-wp-click-tracker
 * @version 1.0
 * @filesource
 */

/*
Plugin Name: Chris2x-WP-Click-Tracker
Plugin URI: 
Description: Tracks all links in posts.
Version: 1.0
Author: Chris Christensen
Author URI: 
Text Domain: chris2x-wp-click-track
*/

/*  
    Based on the wp-clieck-track by Eric Lamb which apparently was no longer supported
    
    Copyright 2009  Eric Lamb  (email : eric@ericlamb.net)
 
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
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
///error_reporting(E_ALL);
/*

URL to grab stats:
http://dashboard.wordpress.com/wp-includes/charts/stats-data.php?blog=6019810
$blog comes from stats_options in options table
*/

/**
 * Version of wp-click-tracker code
 * @global string	$click_tracker_version 
 */
$click_tracker_version = "1.0";

/**
 * Version of wp-click-tracker database
 * @global string	$click_tracker_db_version 
 */
$click_tracker_db_version = "1.0";

/**
 * Default number of items to display per block
 * @global int	$click_tracker_page_default 
 */
$click_tracker_page_default = 10;

/**
 * Key to use for distributed links
 * @global string	$click_tracker_dist_get 
 */
$click_tracker_dist_get = 'wp_ct';

/**
 * The timzone setting from WP
 * @var string
 */
$tz = get_option('timezone_string');
if($tz != '')
{
	date_default_timezone_set($tz);
}

function wpct_get_todays_links($limit = 10){
	global $wpdb;

	$date = date("Y-m-d");
	$sql = "SELECT COUNT(click_id) AS click_count,".$wpdb->prefix."tracking_clicks.link_id AS link_id, date_format(click_date,\"%Y-%m-%d\") AS click_date, link_title, link_destination, click_refer FROM ".$wpdb->prefix."tracking_links,".$wpdb->prefix."tracking_clicks WHERE ".$wpdb->prefix."tracking_links.link_id = ".$wpdb->prefix."tracking_clicks.link_id AND date_format(click_date,\"%Y-%m-%d\") = '$date' GROUP BY ".$wpdb->prefix."tracking_clicks.link_id ORDER BY click_count DESC LIMIT $limit";
	return $wpdb->get_results($sql);
}

//print_r($_SERVER);
function wpct_path_info($filename){
	$info = pathinfo($filename);
	return $info['dirname'].'/click-tracker.php';
}

function wpct_get_page_values(){
	$page_values = array(
					'default'=>'wp-click-track/click-tracker.php',
					'configure'=>'wp-click-track/configure.php',
					'manage'=>'wp-click-track/manage.php',
					'list_all'=>'wp-click-track/list-all.php',
					);
	return $page_values;
}

function wpct_get_link_count($BuildWhere = FALSE) {
	global $wpdb;

	$sql = "SELECT COUNT(*) AS count FROM ".$wpdb->prefix."tracking_links ".$BuildWhere;
	$result = $wpdb->get_results($sql);
	return $result['0']->count;

}

function wpct_get_click_count($BuildWhere = FALSE) {
	global $wpdb;

	$sql = "SELECT COUNT(*) AS count FROM ".$wpdb->prefix."tracking_clicks ".$BuildWhere;
	$result = $wpdb->get_results($sql);
	return $result['0']->count;

}

include 'admin.inc.php';

/**
 * Wrapper for click track filter
 *
 * @param   string  $content	string to parse
 * @return  string				The completed 
 */
function wpct_filter($content)
{
	return wpct_replace_uris($content);
}

/**
 * Takes a string and extracts all the links (URIs)
 *
 * @param   string  $content     string to parse
 * @return  array	The links
 */
function wpct_extract_urls($content){

	preg_match_all('/<a (.*?)href=[\'\"](.*?)\/\/([^\'\"]+?)[\'\"](.*?)>(.*?)<\/a>/i',$content,$links_html);
	//print_r($links_html);
	$total = count($links_html['0']);
	$links = array();
	for($i=0;$i<$total;$i++){
		$links_html['0'][$i] = str_replace("'",'"',$links_html['0'][$i]);

		preg_match_all('/(href|title)=("[^"]*")/i',$links_html['0'][$i],$link_meta);

		$links[$i]['href'] = str_replace(array('"','\''),'',$link_meta['2']['0']);
		$links[$i]['title'] = str_replace(array('"','\''),'',$link_meta['2']['1']);

		//it's wierd; sometimes the keys on $link_meta['2'] aren't exact...
		//HACK to fix for now; sorry :(
		if(!preg_match("/http:\/\//", $links[$i]['href']) && !preg_match("/https:\/\//", $links[$i]['href'])){
			$links[$i]['href'] = str_replace(array('"','\''),'',$link_meta['2']['1']);
			$links[$i]['title'] = str_replace(array('"','\''),'',$link_meta['2']['0']);
		}

		//check if title is blank and try and extract the content 
		if(trim($links[$i]['title']) == ''){
			$links[$i]['title'] = strip_tags($links_html['0'][$i]);
		}
	}
	return $links;
}

/**
 * Replaces all the URLs in a string with newly created URLs for tracking
 *
 * @param   string  $content	string to parse
 * @return  string	$content	The filtered content
 */
function wpct_replace_uris($content){
	global $click_tracker_dist_get;

	$links = wpct_extract_urls($content);

	$_link_count = count($links);
	$_links = $links;
	$new_content = $content;
	unset($content);
	for($i=0;$i<$_link_count;$i++){

		//get rid of mailto: links
		$parse_ok = FALSE;
		if(strpos($_links[$i]['title'], 'mailto:') === FALSE && strpos($_links[$i]['href'], 'mailto:') === FALSE && strpos($_links[$i]['href'], 'javascript:') === FALSE){ #fix 0.4 for Joerg & Logan
			$parse_ok = TRUE;	
		}

		if(trim($_links[$i]['href']) == '' && trim($_links[$i]['title']) == ''){ //skip blank entries
			$parse_ok = FALSE;
		}

		if($_links[$i]['href']{0} == '#' || $_links[$i]['title']{0} == '#'){ //skip anchor links
			$parse_ok = FALSE;
		}

		if(strpos($_links[$i]['href'], $click_tracker_dist_get.'=') == TRUE){ //check if link is external click track
			$parse_ok = FALSE;
		}
		
		if($parse_ok){ 
			$_new_links = $_links[$i]['href'].'" onclick="return TrackClick(\''.urlencode(trim($_links[$i]['href'])).'\',\''.urlencode(trim($_links[$i]['title'])).'\')"';
			$new_content = str_replace($_links[$i]['href'].'"',$_new_links,$new_content);
			$new_content = str_replace($_links[$i]['href']."'",$_new_links,$new_content);
		}
	}

	$new_content = str_replace("href='", 'href="',$new_content);

	return $new_content;
}

include_once 'install.inc.php';

/**
 * Adds the javascript embed to the client header
 *
 * @return  void
 */
function wpct_head(){

	//get the full URL to the site (0.4 fix)
	
	$site_url = wpct_get_siteurl();
	//$file = plugin_basename(__FILE__);
	
	echo <<<HTML

		
	<script type="text/javascript" src="$site_url/wp-content/plugins/chris2x-wp-click-track/js/ajax.js"></script>
	<script type="text/javascript">

		var ajax = new Array();
		function TrackClick(link,title) {

			var index = ajax.length;
			ajax[index] = new sack();		
			ajax[index].requestFile = "$site_url?wp_click_tracked="+link+"***"+title+"***";	
			ajax[index].onCompletion = null;

			//FireFox 3 is a bitch; it doesn't track links which open in "self"
			var onunload_ran = 0;
			window.onunload = function () {
				onunload_ran = 1;
				ajax[index].runAJAX();
			}

			if(onunload_ran == 0){
				ajax[index].runAJAX();
			}

			return true;
		}

	</script>
HTML;
}

/**
 * Execution of click tracker 
 *
 * @return  void
 */
function wpct_go(){
	global $wpdb, $click_tracker_dist_get;

	//check the GET so sites built on a sub directory get tracked (0.4 fix) 
	//$_GET[$click_tracker_dist_get] is used for distributable links
	//$_GET['wp_click_tracked'] is used for internally created links
	$forward_to_url = FALSE;
	$track_click = TRUE;
	if(isset($_GET['wp_click_tracked']) || isset($_GET[$click_tracker_dist_get])){ //looks like we have a tracking link...
		
		if(isset($_GET[$click_tracker_dist_get]) ){

			$link_id = (int)$_GET[$click_tracker_dist_get];
			$sql = "SELECT link_id,link_destination FROM ".$wpdb->prefix."tracking_links WHERE link_id = '".$wpdb->escape($link_id)."'";
			$result = $wpdb->get_results($sql);
			$total = count($result);
			$link_id = ($total == '1' ? $result['0']->link_id : FALSE);
			$destination = $result['0']->link_destination;
			$forward_to_url = TRUE;

			if(!$link_id){
				return FALSE;
			}

		} else {
			//convert back to normal
			$_destination = $_GET['wp_click_tracked'];
			$_destination_parts = explode('***',$_destination);

			$destination = $_destination_parts['0'];
			
			//make sure we actually have something here 
			// 0.3 BUG FIX
			if($destination == ''){
				return FALSE;
			}
			$link_title = (strlen($_destination_parts['1']) >= '1' ? $_destination_parts['1'] : 'No Title Given');
			//check to see if it's an update or an insert
			$sql = "SELECT link_id FROM ".$wpdb->prefix."tracking_links WHERE link_destination = '".$wpdb->escape($destination)."'";
			$result = $wpdb->get_results($sql);

			$total = count($result);
			$link_id = ($total == '1' ? $result['0']->link_id : FALSE);
			
			if(!$link_id){
				//insert
				$link_id = wpct_add_link($link_title,$destination);
			}
		}

		$click_refer = (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : FALSE);
		$stat_ip  =  (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : FALSE);

		$plugin_options = get_option('click_tracker');

		if($plugin_options['track_user_clicks'] == '0'){ //check if we're supposed to track user clicks
			if(is_user_logged_in()) {
				$track_click = FALSE;
			}
		}

		if(!isset($_GET[$click_tracker_dist_get])) {
			if($plugin_options['track_internal_links'] == '0') { //check if we're tracking internal links
				$host_str = 'http://'.$_SERVER['HTTP_HOST'];
				if($host_str == substr($destination, 0, strlen($host_str)))
				{
					return FALSE;
				}
			}
		}

		//check the IP address white listing
		$wl_ips = array_map('trim',explode("\n", $plugin_options['exclude_ips']));
		if(in_array($stat_ip,$wl_ips)){
			$track_click = FALSE;
		}

		if($track_click){

			if(!isset($_COOKIE['el_wpct'])) {

				$_clicks = array();

			} else {
				$_clicks = explode(',',$_COOKIE['el_wpct']);
			}

			if(in_array($link_id,$_clicks)){

				$sql = "INSERT INTO ".$wpdb->prefix."tracking_clicks SET link_id = '".$wpdb->escape($link_id)."' , click_refer = '".$wpdb->escape($click_refer)."', unique_click = '0', click_date = now(), click_ip = '".$wpdb->escape($stat_ip)."'";
				$wpdb->query($sql);

				$sql = "UPDATE ".$wpdb->prefix."tracking_links SET link_total_clicks = link_total_clicks+1  WHERE link_id = '".$wpdb->escape($link_id)."' ";
				$wpdb->query($sql);
				
			} else {

				$_clicks[] = $link_id;

				setcookie("el_wpct", implode(',',$_clicks), time()+86400, SITECOOKIEPATH, COOKIE_DOMAIN, false, true);
				
				$sql = "UPDATE ".$wpdb->prefix."tracking_links SET link_total_clicks = link_total_clicks+1, link_unique_clicks = link_unique_clicks+1 WHERE link_id = '".$wpdb->escape($link_id)."' ";
				$wpdb->query($sql);

				$sql = "INSERT INTO ".$wpdb->prefix."tracking_clicks SET link_id = '".$wpdb->escape($link_id)."' , click_refer = '".$wpdb->escape($click_refer)."', unique_click = '1', click_date = now(), click_ip = '".$wpdb->escape($stat_ip)."'";
				$wpdb->query($sql);

			}
		}

		if($forward_to_url && $destination){
			header("Location: $destination");
		}
		exit;
	}
}

/**
 * Adds the click tracker options page to the administration module
 *
 * @return	void
 */
function wpct_menu() {

	$page_vars = wpct_get_page_values();
	add_menu_page( __( 'Click Tracker', 'wp-click-track' ), __( 'Click Tracker', 'wp-click-track' ),
		'edit_posts', $page_vars['default'], 'wpct_admin_module_template' );

	add_submenu_page( __FILE__, __( 'Click Tracker Report', 'wp-click-track' ), __( 'Reports', 'wp-click-track' ),
		'edit_posts', $page_vars['default'], 'wpct_admin_module_template' );

	add_submenu_page( __FILE__, __( 'List All Click Track Links', 'wp-click-track' ), __( 'List All', 'wp-click-track' ),
		'edit_posts', $page_vars['list_all'], 'wpct_admin_module_template' );

	add_submenu_page( __FILE__, __( 'Add Click Track', 'wp-click-track' ), __( 'Add Link', 'wp-click-track' ),
		'edit_posts', $page_vars['manage'], 'wpct_admin_module_template' );

	add_submenu_page( __FILE__, __( 'Configure Click Tracker', 'wp-click-track' ), __( 'Configure', 'wp-click-track' ),
		'edit_posts', $page_vars['configure'], 'wpct_admin_module_template' );

}

/**
 * Include the configuration stuff
 */
include 'configure.php';

/**
 * Returns the clicks 
 *
 * @param   string  $BuildWhere	customize sql
 * @return  object
 */
function wpct_get_clicks($BuildWhere){
	global $wpdb;

	$sql = "SELECT * FROM ".$wpdb->prefix."tracking_clicks ".$BuildWhere;
	$result = $wpdb->get_results($sql);
	return $result;
}

/**
 * Returns the links 
 *
 * @param   string  $BuildWhere	customize sql
 * @return  object
 */
function wpct_get_link_data($BuildWhere){
	global $wpdb;

	$sql = "SELECT * FROM ".$wpdb->prefix."tracking_links ".$BuildWhere;
	$result = $wpdb->get_results($sql);
	return $result;
}

/**
 * Wrapper for modifying link form processing
 *
 * @return  void
 */
function wpct_mod_go(){
	global $wpdb;

	if(isset($_POST['mod_wpct_link'])){ //check the form...
		$errors = wpct_validate_mod_link($_POST);

		if(count($errors) == '0') {

			if(isset($_POST['link'])){

				$link = (int)$_POST['link'];
				$link_data = wpct_get_link_data(" WHERE link_id = '".$wpdb->escape($link)."'");
				$msg = 'fail_update';

				if(count($link_data) == '1'){

					$title = $_POST['link_name'];
					$dest = $_POST['link_destination'];
					$sql = "UPDATE ".$wpdb->prefix."tracking_links SET link_title = '".$wpdb->escape($title)."', link_destination = '".$wpdb->escape($dest)."' WHERE link_id = '".$wpdb->escape($link)."'";
					$wpdb->query($sql);
					$msg = 'success_update';
				}

			} else {

				$title = $_POST['link_name'];
				$dest = $_POST['link_destination'];
				$link = wpct_add_link($title,$dest);
				$msg = 'success_add';
			}

			header('Location: '.$_SERVER['PHP_SELF'].'?page='.$_REQUEST['page'].'&link='.$link.'&msg='.$msg);
			exit;
		} else {
			return FALSE;
		}
	}
}

/**
 * Wrapper for the reporting functionality
 * @return void
 */
function wpct_get_graph_stats(){
	global $wpdb;

	if(isset($_GET['wp_ofc'])){
		$reporting_included = TRUE;
		include 'reporting.php';
		exit;
	}

}

/**
 * Wrapper for adding a link
 * @param $link_title
 * @param $destination
 * @return int
 */
function wpct_add_link($link_title,$destination){
	global $wpdb;

	$sql = "INSERT INTO ".$wpdb->prefix."tracking_links SET last_modified = now(), creation_date = now(), link_title = '".$wpdb->escape($link_title)."', link_desc = '', link_destination = '".$wpdb->escape($destination)."', link_total_clicks = '0', link_unique_clicks = '0'";
	$wpdb->query($sql);
	$link_id = $wpdb->insert_id;
	return $link_id;
}

/**
 * Wrapper for returning the site URL 
 * @return string
 */
function wpct_get_siteurl(){
	
	$plugin_options = get_option('click_tracker');
	if(array_key_exists('bypass_site_url',$plugin_options) && $plugin_options['bypass_site_url'] != ''){
		$url = $plugin_options['bypass_site_url'];
	} else {
		$url = get_option( 'siteurl' );
	}

	return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? str_replace('http://', 'https://', $url) : $url);
}

/**
 * Sets up the textdomain stuff
 * @return void
 */
function wpct_load_plugin_textdomain() { // l10n
	$plugin_dir = basename(dirname(__FILE__));
	load_plugin_textdomain( 'wpcf7', 'wp-content/plugins/'.$plugin_dir.'/languages', $plugin_dir.'/languages' );
}

/**
 * Ego Footer; probably not going to implement.
 * @return void
 */
function wpct_ego_footer() {
	?><p id='wpct_ego'><?php printf( __( 'Links tracked using %1$s ', 'wp-click-track' ), '<a href="http://blog.ericlamb.net?wp_ct=279" target="_blank">WP Click Track</a>' ); ?></p><?php
}

add_action( 'init', 'wpct_load_plugin_textdomain' );

register_activation_hook(__FILE__,'wpct_install');
register_deactivation_hook(__FILE__, 'wpct_deactivate');

add_action('edit_form_advanced', 'wpct_link_data');
add_action('edit_page_form', 'wpct_link_data');
//add_action('edit_link_form', 'wpct_link_data');

add_action('admin_head', 'wpct_admin_head');
add_action('admin_menu', 'wpct_menu');

add_action('wp_head', 'wpct_head');
add_action('init','wpct_go');
add_action('admin_init','wpct_mod_go');
add_action('admin_init','wpct_get_graph_stats');

include 'widget.inc.php';
add_action('wp_head', 'wpct_sidebar_header');
add_action('plugins_loaded', 'wpct_sidebar_init');

$ct_options = get_option('click_tracker');
if(array_key_exists('parse_archive',$ct_options) && $ct_options['parse_archive'] == '1'){
	add_filter('get_archives_link', 'wpct_filter', 1);
}

if(array_key_exists('parse_blog_roll',$ct_options) && $ct_options['parse_blog_roll'] == '1'){
	add_filter('wp_list_bookmarks', 'wpct_filter', 1);
}

if(array_key_exists('parse_comment_author_link',$ct_options) && $ct_options['parse_comment_author_link'] == '1'){
	add_filter('get_comment_author_link', 'wpct_filter', 1);
}

if(array_key_exists('parse_the_excerpt',$ct_options) && $ct_options['parse_the_excerpt'] == '1'){
	add_filter('the_excerpt', 'wpct_filter', 1);
}

if(array_key_exists('parse_the_content',$ct_options) && $ct_options['parse_the_content'] == '1'){
	add_filter('the_content', 'wpct_filter', 1);
}

if(array_key_exists('parse_the_meta',$ct_options) && $ct_options['parse_the_meta'] == '1'){
	add_filter('the_meta_key', 'wpct_filter', 1); #0.3 Adds to the_meta values
}

if(array_key_exists('parse_comment_text',$ct_options) && $ct_options['parse_comment_text'] == '1' && strpos($_SERVER['PHP_SELF'], 'wp-admin') === FALSE){
	add_filter('comment_text', 'wpct_filter', 9);
}

if(array_key_exists('parse_next_prev',$ct_options) && $ct_options['parse_next_prev'] == '1'){
	add_filter('next_post_link', 'wpct_filter', 9);
	add_filter('previous_post_link', 'wpct_filter', 9);
}

if(array_key_exists('parse_the_tags',$ct_options) && $ct_options['parse_the_tags'] == '1'){
	add_filter('the_tags', 'wpct_filter', 9);
}

if(array_key_exists('parse_the_category',$ct_options) && $ct_options['parse_the_category'] == '1'){
	add_filter('the_category', 'wpct_filter', 9);
	add_filter('wp_list_categories', 'wpct_filter', 9);
}

if(array_key_exists('enable_ego_link',$ct_options) && $ct_options['enable_ego_link'] == '1'){
	add_action( 'wp_footer', 'wpct_ego_footer' );
}

unset($ct_options);

include_once 'contextual_help.php';
add_action('wp_dashboard_setup', 'wpct_admin_add_dashboard_widgets' );

//add_filter('link_category', 'wpct_filter', 9); #0.2.1
//add_filter('author_feed_link', 'wpct_filter', 9); #0.3 Adds to the_meta values
//add_filter('the_title', 'wpct_filter', 9); #0.3 Adds to the_meta values
