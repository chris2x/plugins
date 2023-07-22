<?php
/**
 * WP-Click-Tracker Install Module
 *
 * Contains all the click tracker functions for the installation routine.
 *
 * @author Eric Lamb <eric@ericlamb.net>
 * @package    wp-click-tracker
 * @version 0.5.1
 * @filesource
 * @copyright 2009 Eric Lamb.
 */

/**
 * Installs the database tables and settings
 *
 * @return  void
 */
function wpct_install () {
	global $wpdb;
	global $click_tracker_db_version;
	global $click_tracker_version;

	//install tracking_clicks table
	$table_name = $wpdb->prefix . "tracking_links";
	$new_keys = array('link_destination');
	if($wpdb->get_var("show tables like '$table_name'") != $table_name) {

		$sql = "CREATE TABLE " . $table_name . " (
		`link_id` int(10) NOT NULL auto_increment,
		`link_title` varchar(255) NOT NULL default '',
		`link_desc` text NOT NULL,
		`link_destination` varchar(255) NOT NULL default '',
		`link_total_clicks` int(10) NOT NULL default '0',
		`link_unique_clicks` int(10) NOT NULL default '0',
		`creation_date` datetime NOT NULL default '0000-00-00 00:00:00',
		`last_modified` datetime NOT NULL default '0000-00-00 00:00:00',
		PRIMARY KEY  (`link_id`)
		)";

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);

		$total = count($new_keys);
		for($i=0;$i<$total;$i++){
			$sql = "ALTER TABLE " . $table_name . " ADD INDEX ( `".$new_keys[$i]."` )  ";
			$wpdb->query($sql);
		}

		$sql = "ALTER TABLE " . $table_name . "  ENGINE = MYISAM";
		$wpdb->query($sql);

		$sql = "ALTER TABLE " . $table_name . " ADD FULLTEXT search_index (link_title,link_desc,link_destination)";
		$wpdb->query($sql);

	} else {

		//add the keys
		$existing_keys = wp_get_table_indexes($table_name);
		$total = count($new_keys);
		for($i=0;$i<$total;$i++){
			if(!in_array($new_keys[$i],$existing_keys)){
				$sql = "ALTER TABLE " . $table_name . " ADD INDEX ( `".$new_keys[$i]."` )  ";
				$wpdb->query($sql);
			}
		}

		$sql = "ALTER TABLE " . $table_name . "  ENGINE = MYISAM";
		$wpdb->query($sql);

		$sql = "ALTER TABLE " . $table_name . " ADD FULLTEXT search_index (link_title,link_desc,link_destination)";
		$wpdb->query($sql);
	}

	//install tracking_clicks table
	$table_name = $wpdb->prefix . "tracking_clicks";
	$new_keys = array('link_id','click_refer','click_ip');
	if($wpdb->get_var("show tables like '$table_name'") != $table_name) {

		$sql = "CREATE TABLE " . $table_name . " (
		`click_id` int(10) NOT NULL auto_increment,
		`link_id` int(10) NOT NULL default '0',
		`click_refer` varchar(255) NOT NULL default '',
		`click_date` datetime NOT NULL default '0000-00-00 00:00:00',
		`unique_click` int(1) NOT NULL default '0',
		`click_ip` varchar(255) NOT NULL default '',
		PRIMARY KEY  (`click_id`)
		)";

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);

		$total = count($new_keys);
		for($i=0;$i<$total;$i++){
			$sql = "ALTER TABLE " . $table_name . " ADD INDEX ( `".$new_keys[$i]."` )  ";
			$wpdb->query($sql);
		}

	} else {

		//add the keys
		$existing_keys = wp_get_table_indexes($table_name);
		$total = count($new_keys);
		for($i=0;$i<$total;$i++){
			if(!in_array($new_keys[$i],$existing_keys)){
				$sql = "ALTER TABLE " . $table_name . " ADD INDEX ( `".$new_keys[$i]."` )  ";
				$wpdb->query($sql);
			}
		}
	}

	
	//set the defaults
	$options['version'] = '0.6';
	$options['db_version'] = '0.3';

	$options['click_count'] = '10';
	$options['link_list_count'] = '10';
	$options['referrer_count'] = '10';
	$options['link_list_count'] = '10';

	$options['parse_the_content'] = '1';
	$options['parse_archive'] = '1';
	$options['parse_blog_roll'] = '1';
	$options['parse_comment_author_link'] = '1';
	$options['parse_the_excerpt'] = '1';
	$options['parse_the_meta'] = '1';
	$options['parse_comment_text'] = '1';
	$options['parse_next_prev'] = '1';
	$options['parse_the_tags'] = '1';
	$options['parse_the_category'] = '1';

	$options['track_user_clicks'] = '1';
	$options['track_internal_links'] = '1';

	$options['admin_menu_style'] = 'good';

	$options['bypass_site_url'] = '';

	$options['sidebar_todays_click_limit'] = '10';
	$options['sidebar_top_click_limit'] = '10';

	add_option("click_tracker", $options);

	//send activation notice

	include 'activation-client.php';
	activation_counter_send_notice('http://blog.ericlamb.net/wp-plugin-activation-post','click_tracker',$options['version'],$options['db_version'],1);
}

/**
 * Returns array of indexes for a given table
 *
 * @param   string  $tablename	The table to check
 * @return  array	$keys		The keys
 */
function wp_get_table_indexes($tablename){
	global $wpdb;
	$sql = "DESCRIBE " . $tablename;
	$result = $wpdb->get_results($sql);
	$keys = array();
	if($result){
		$total = count($result);
		for($i=0;$i<$total;$i++){
			if($result[$i]->Key != '' && $result[$i]->Key != 'PRI'){
				$keys[] = $result[$i]->Field;
			}
		}
	}
	return $keys;
}

/**
 * Handles the removal of a plugin
 *
 * @return  void
 */
function wpct_deactivate(){
	global $click_tracker_db_version;
	global $click_tracker_version;
	global $wpdb;

	/*
	$table_name = $wpdb->prefix . "tracking_clicks";
	$sql = "DROP TABLE " . $table_name;
	$wpdb->query($sql);

	$table_name = $wpdb->prefix . "tracking_links";
	$sql = "DROP TABLE " . $table_name;
	$wpdb->query($sql);
	*/
	include 'activation-client.php';
	activation_counter_send_notice('http://blog.ericlamb.net/wp-plugin-activation-post','click_tracker',$click_tracker_version,$click_tracker_db_version,0);
}

?>