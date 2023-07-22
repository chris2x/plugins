<?php
/**
 * WP-Click-Tracker Admin Module
 *
 * Contains all the click tracker functions for the administration module.
 *
 * @author Eric Lamb <eric@ericlamb.net>
 * @package    wp-click-tracker
 * @version 0.6
 * @filesource
 * @copyright 2009 Eric Lamb.
 */

include 'template.inc.php';

function wpct_validate_mod_link($data){

	$errors = array();
	if(!isset($data['link_name']) || $data['link_name'] == ''){
		$errors['link_name'] = TRUE;
	}

	if(!isset($data['link_destination']) || $data['link_destination'] == ''){
		$errors['link_destination'] = TRUE;
	}

	return $errors;
}

function wpct_mod_link_form($link_id = FALSE, $req = 'add'){
	global $wpdb;
	
	$page = $_REQUEST['page'];
	$errors = FALSE;
	if($link_id){
		$link_data = wpct_get_link_data(" WHERE link_id = '$link_id'");
		$submit_value = __('Edit Link', 'wp-click-track');
		$def_name = (isset($_POST['link_name']) ? $_POST['link_name'] : $link_data['0']->link_title);
		$def_desc = (isset($_POST['description']) ? $_POST['description'] : $link_data['0']->link_desc);
		$def_dest = (isset($_POST['link_destination']) ? $_POST['link_destination'] : $link_data['0']->link_destination);

	} else {
		$submit_value = __('Add Link', 'wp-click-track');
		$def_name = (isset($_POST['link_name']) ? $_POST['link_name'] : FALSE);
		$def_desc = (isset($_POST['description']) ? $_POST['description'] : FALSE);
		$def_dest = (isset($_POST['link_destination']) ? $_POST['link_destination'] : FALSE);
	}

	if(isset($_POST['link_name'])){
		$errors = wpct_validate_mod_link($_POST);
		if(count($errors) == '0'){
			$errors = FALSE;
		} else {
			echo '<div class="error">'.__('Please fill out all required fields.', 'wp-click-track').'</div>';
		}
	}

?>

	<form name="add_click_track" id="add_click_track" method="post" action="<?php echo $_SERVER['PHP_SELF'];?>?page=wp-click-track/manage.php">
	<?php
		if($link_id){
			echo '<input type="hidden" name="link" value="'.$link_id.'">';
		}
	?>
	<input type="hidden" name="req" value="<?php echo $req; ?>" />
	<input type="hidden" name="page" value="<?php echo $page; ?>" />
	<input type="hidden" name="mod_wpct_link" value="yes" />
	<div>

	<?php
	$link_error = '';
	if(isset($errors['link_name'])){
		$link_error = '<div class="error">'.__('Required', 'wp-click-track').'</div>';
	}

	$cols = array('<label for="link_name">'.__('Link Name (required)', 'wp-click-track').'</label>');
	$rows = array(
				array(
				'<div class="form-field">'.$link_error.'<input name="link_name" id="link_name" type="text" value="'.$def_name.'" size="40"/></div>'.__('Example: Download WP-Click-Tracker', 'wp-click-track')
				)
			);
	echo wpct_admin_table($cols, $rows, $msg = FALSE, $tfoot = FALSE, $add_break = TRUE);

	if(isset($errors['link_destination'])){
		echo '<div class="error">'.__('Required').'</div>';
	}

	$cols = array('<label for="link_destination">'.__('Link Destination (required)', 'wp-click-track').'</label>');
	$rows = array(
				array(
				'<div class="form-field"><input name="link_destination" id="link_destination" type="text" value="'.$def_dest.'" size="40" /></div>'.__('Example:', 'wp-click-track').'<code>http://'.$_SERVER['HTTP_HOST'].'/</code> &#8212; '.__('don&#8217;t forget the', 'wp-click-track').'<code>http://</code>'
				)
			);
	echo wpct_admin_table($cols, $rows, $msg = FALSE, $tfoot = FALSE, $add_break = TRUE);

	$cols = array('<label for="description">'.__('Description (optional)', 'wp-click-track').'</label>');
	$rows = array(
				array(
				'<div class="form-field"><textarea name="description" id="description" rows="5" cols="40">'.$def_desc.'</textarea></div>'.__('This will be shown when someone hovers over the link.', 'wp-click-track')
				)
			);
	echo wpct_admin_table($cols, $rows, $msg = FALSE, $tfoot = FALSE, $add_break = TRUE);
	?>

	<p class="submit"><input type="submit" class="button" name="submit" value="<?php echo $submit_value; ?>" /></p>
	</div>
	</form>
<?php
}

/**
 * Lists all the links for a situation
 *
 * @return  void
 */
function wpct_list_links($result, $total, $date_ranges = FALSE, $display_message = TRUE){

	$cols = array(__('Title', 'wp-click-track'),__('Destination', 'wp-click-track'),__('Total Clicks', 'wp-click-track'),__('Unique Clicks', 'wp-click-track'));
	$rows = array();
	if($date_ranges){
		$cols[] = __('First Click', 'wp-click-track');
		$cols[] = __('Last Click', 'wp-click-track');
	}

	$page_vars = wpct_get_page_values();

	for($i=0;$i<$total;$i++){
		$link_destination_title = (strlen($result[$i]->link_destination) >= 50 ? substr($result[$i]->link_destination,0,50).'...' : $result[$i]->link_destination);
		$rows[$i] = array('<a href="'.$_SERVER['PHP_SELF'].'?page='.$page_vars['list_all'].'&link='.$result[$i]->link_id.'">'.$result[$i]->link_title.'</a>',
						'<a href="'.$result[$i]->link_destination.'" target="_blank" title="'.$result[$i]->link_destination.'">'.$link_destination_title.'</a></td>',
						number_format_i18n($result[$i]->link_total_clicks),
						number_format_i18n($result[$i]->link_unique_clicks)
					);

		if($date_ranges){
			$rows[$i][] = $date_ranges['first_click'];
			$rows[$i][] = $date_ranges['last_click'];
		}
	}

	echo wpct_admin_table($cols,$rows,__('NOTE: Only links that have been clicked are above.', 'wp-click-track'));
}

function wpct_admin_list_ip_page($ip_addr, $link_id, $req, $page, $paged, $ref_paged){
	global $wpdb, $click_tracker_dist_get;

	//base url for the admin page (build new links on top of)
	$page_url = $_SERVER['PHP_SELF'].'?page='.$page.'&link='.$link_id.'&ip='.$ip_addr;

	echo '<table width="100%"><tr><td><a name="in_refer"></a><h3>'.__(sprintf(__('Click Path for %s'), $ip_addr), 'wp-click-track').'</h3></td><td>';

	$sql = "SELECT * FROM ".$wpdb->prefix."tracking_clicks tc,".$wpdb->prefix."tracking_links tl WHERE tc.click_ip = '$ip_addr' and tc.link_id = tl.link_id ORDER BY click_date DESC";
	$result = $wpdb->get_results($sql);
	$total_refer = count($result);
	unset($result);

	$plugin_options = get_option('click_tracker');
	$link_page_total = $plugin_options['referrer_count'];

	$limitvalue = $ref_paged * $link_page_total - ($link_page_total);
	$numofpages = ceil($total_refer / $link_page_total);

	$sql = "SELECT * FROM ".$wpdb->prefix."tracking_clicks tc,".$wpdb->prefix."tracking_links tl WHERE tc.click_ip = '$ip_addr' and tc.link_id = tl.link_id ORDER BY click_date DESC LIMIT $limitvalue,$link_page_total"; 
	$referr_data = $wpdb->get_results($sql);
	$count = count($referr_data);
	
	echo wpct_pagination($numofpages, $ref_paged, $link_page_total, $total_refer, 'refer_data', 'ref_paged');

	?>
	</td></tr></table>

	<table class="widefat comments-box " cellspacing="0">
	<thead>
		<tr>
		<th><?php _e('Link', 'wp-click-track'); ?></th>
		<th><?php _e('Referrer', 'wp-click-track'); ?></th>
		<th><?php _e('Date', 'wp-click-track'); ?></th>
<?php
	if($count >= '1'){
		for($i=0;$i<$count;$i++){
	?>
	  </tr>
		<tr>
		<td><a href=""><?php echo $referr_data[$i]->link_title;?></a></td>
		<td><a href="<?php echo $referr_data[$i]->click_refer;?>" target="_blank" title=""><?php echo (strlen($referr_data[$i]->click_refer) >= 100 ? substr($referr_data[$i]->click_refer,0,100).'...' : $referr_data[$i]->click_refer);?></a></a></td>
		<td><?php echo mysql2date(get_option('date_format').' '.get_option('time_format'),$referr_data[$i]->click_date);?></td>
	<?php
		}
	} else {
		echo '</tr><tr><td colspan="3">'.__('No Referrers Found', 'wp-click-track').'</td></tr>';
	}
?>
	<tbody id="the-comment-list" class="list:comment">
	</tbody>
	</table>

	<?php
	echo wpct_pagination($numofpages, $ref_paged, $link_page_total, $total_refer, 'refer_data', 'ref_paged');

}

function wpct_admin_list_link_page($link_id, $req, $page, $paged, $ref_paged) {
	global $wpdb, $click_tracker_dist_get;

	$message = FALSE;
	$site_url = wpct_get_siteurl();

	if(isset($_POST['reset_link_clicks']) && $_POST['reset_link_clicks'] == 'yes' && $link_id){

		$sql = "DELETE FROM ".$wpdb->prefix . "tracking_clicks WHERE link_id = '$link_id'";
		$wpdb->query($sql);

		$sql = "UPDATE ".$wpdb->prefix . "tracking_links SET link_total_clicks = '0', link_unique_clicks = '0', last_modified = NOW() WHERE link_id = '$link_id'";
		$wpdb->query($sql);

		$form_updated = TRUE;
		$message = __('Clicks Deleted!', 'wp-click-track');
	}

	if(isset($_POST['reset_single_link']) && $_POST['reset_single_link'] == 'yes' && $link_id){
		
		$sql = "DELETE FROM ".$wpdb->prefix . "tracking_links WHERE link_id = '$link_id'";
		$wpdb->query($sql);

		$sql = "DELETE FROM ".$wpdb->prefix . "tracking_clicks WHERE link_id = '$link_id'";
		$wpdb->query($sql);

		$form_updated = TRUE;
		$message = __('Link Deleted!','default');
		
		echo '<script type="text/javascript">window.location.replace("'.$site_url.$_SERVER['PHP_SELF'].'?page='.$_GET['page'].'&link_deleted=yes"); </script>';
		exit;
	}

	//base url for the admin page (build new links on top of)
	$page_url = $_SERVER['PHP_SELF'].'?page='.$page.'&link='.$link_id;

	$BuildWhere = " WHERE link_id = '".$wpdb->escape($link_id)."' LIMIT 1";
	$result = wpct_get_link_data($BuildWhere);

	//get the first and last click dates
	$sql = "SELECT MIN(click_date) as first_date,MAX(click_date) as last_date FROM ".$wpdb->prefix."tracking_clicks WHERE link_id='".$wpdb->escape($link_id)."' LIMIT 1";
	$click_dates = $wpdb->get_results($sql);

	$date_ranges['first_click'] = ($click_dates['0']->first_date == '' ? 'N/A' : mysql2date(get_option('date_format').' '.get_option('time_format'),$click_dates['0']->first_date));
	$date_ranges['last_click'] = ($click_dates['0']->last_date == '' ? 'N/A' : mysql2date(get_option('date_format').' '.get_option('time_format'),$click_dates['0']->last_date));

	$total = count($result);
	$dist_link = $site_url.'?'.$click_tracker_dist_get.'='.$link_id;

	if($form_updated && $message){
		wpct_admin_message( $message );
	}
	
	echo '<h3>'.__('Distributable Link', 'wp-click-track').'</h3>';
	$cols = array(__('Embed Code', 'wp-click-track'), __('URL', 'wp-click-track'));
	$rows = array(array('<code> &lt;a href="'.$dist_link.'"&gt;'.$result['0']->link_title.'&lt;/a&gt;</code>',
				$dist_link
				)
			);
	echo wpct_admin_table($cols,$rows,$msg = FALSE);

	echo '<h3>'.__('Link Data', 'wp-click-track').'</h3>';
	wpct_list_links($result, $total, $date_ranges, FALSE);

	wpct_admin_stats($link_id, 30);

	echo '<table width="100%"><tr><td><a name="in_refer"></a><h3>'.__('Top Referrers', 'wp-click-track').'</h3></td><td>';

	$sql = "SELECT COUNT(click_refer) AS REFERCNT FROM ".$wpdb->prefix."tracking_clicks WHERE link_id = '".$wpdb->escape($link_id)."' AND click_refer != '' GROUP BY click_refer ORDER BY REFERCNT DESC ";
	$result = $wpdb->get_results($sql);
	$total_refer = count($result);
	unset($result);

	$plugin_options = get_option('click_tracker');
	$link_page_total = $plugin_options['referrer_count'];

	$limitvalue = $ref_paged * $link_page_total - ($link_page_total);
	$numofpages = ceil($total_refer / $link_page_total);

	$sql = "SELECT COUNT(click_refer) AS REFERCNT, click_refer FROM ".$wpdb->prefix."tracking_clicks WHERE link_id = '".$wpdb->escape($link_id)."' AND click_refer != '' GROUP BY click_refer ORDER BY REFERCNT DESC LIMIT $limitvalue,$link_page_total"; 
	$referr_data = $wpdb->get_results($sql);
	$count = count($referr_data);

	echo wpct_pagination($numofpages, $ref_paged, $link_page_total, $total_refer, 'in_refer', 'ref_paged');

	echo '</td></tr></table>';

	$cols = array(__('Count', 'wp-click-track'), __('Referrer', 'wp-click-track'));
	$rows = array();
	if($count >= '1'){
		for($i=0;$i<$count;$i++){
			$rows[] = array($referr_data[$i]->REFERCNT,'<a href="'.$referr_data[$i]->click_refer.'" target="_blank" title="">'.(strlen($referr_data[$i]->click_refer) >= 125 ? substr($referr_data[$i]->click_refer,0,125).'...' : $referr_data[$i]->click_refer).'</a>');
		}
	} else {
		$rows[] = array('', __('No Referrers Found', 'wp-click-track'));
	}

	echo wpct_admin_table($cols,$rows,$msg = FALSE);

	echo wpct_pagination($numofpages, $ref_paged, $link_page_total, $total_refer, 'in_refer', 'ref_paged');
	?>

	<table width="100%"><tr><td>
	<?php
	echo '<a name="in_clicks"></a><h3>'.__('Individual Clicks', 'wp-click-track').'</h3>';

	echo '</td><td align="right">';

	$sql = "SELECT COUNT(click_ip) AS CLICKCNT FROM ".$wpdb->prefix."tracking_clicks WHERE link_id = '".$wpdb->escape($link_id)."' GROUP BY click_ip";
	$result = $wpdb->get_results($sql);
	$total_clicks = count($result);
	unset($result);

	$plugin_options = get_option('click_tracker');
	$link_page_total = $plugin_options['click_count'];

	$limitvalue = $paged * $link_page_total - ($link_page_total);
	$numofpages = ceil($total_clicks / $link_page_total);

	$sql = "SELECT COUNT(click_ip) AS IPCNT,click_refer,MIN(click_date) AS min_date, MAX(click_date) AS max_date,click_ip FROM ".$wpdb->prefix."tracking_clicks WHERE link_id = '".$wpdb->escape($link_id)."' GROUP BY click_ip ORDER BY IPCNT DESC LIMIT $limitvalue,$link_page_total";
	$referr_data = $wpdb->get_results($sql);
	$count = count($referr_data);


	echo wpct_pagination($numofpages, $paged, $link_page_total, $total_clicks, 'in_clicks', 'paged');
	echo '</td></tr></table>';

	$cols = array(__('Clicks', 'wp-click-track'), __('IP Address', 'wp-click-track'), __('First Click', 'wp-click-track'), __('Last Click', 'wp-click-track'), __('Referrer', 'wp-click-track'));
	$rows = array();

	if($count >= '1'){
		for($i=0;$i<$count;$i++){
			if($referr_data[$i]->click_refer == ''){ 
				$click_refer =  __('N/A', 'wp-click-track'); 
			} else { 
				$click_refer = '<a href="'.$referr_data[$i]->click_refer.'" target="_blank" title="">'.(strlen($referr_data[$i]->click_refer) >= 70 ? substr($referr_data[$i]->click_refer,0,70).'...' : $referr_data[$i]->click_refer).'</a>';
			}
			$rows[] = array($referr_data[$i]->IPCNT,
						'<a href="'.$page_url.'&ip='.ip2long($referr_data[$i]->click_ip).'">'.$referr_data[$i]->click_ip.'</a>',
						mysql2date(get_option('date_format').' '.get_option('time_format'),$referr_data[$i]->min_date),
						mysql2date(get_option('date_format').' '.get_option('time_format'),$referr_data[$i]->max_date),
						$click_refer
			);
		}
	} else {
		$rows[] = array(__('No Clicks Found', 'wp-click-track'));
	}

	echo wpct_admin_table($cols,$rows,$msg = FALSE);

	echo wpct_pagination($numofpages, $paged, $link_page_total, $total_clicks, 'in_clicks', 'paged');

	?>
		<br />
		<table width="200" ><tr><td>
			
			<form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>?page=<?php echo $_REQUEST['page'];?>&link=<?php echo $link_id; ?>">
			<input type="hidden" name="reset_link_clicks" value="yes" \>
			<input type="hidden" name="link" value="<?php echo $link_id; ?>" \>
			<input type="submit" name="Submit" class="button-primary" value="<?php _e('Reset All Clicks', 'wp-click-track'); ?>" onclick="javascript: return confirm('<?php _e('Are you sure you want to reset all the clicks for this link? \nThis deletes all click records...', 'wp-click-track'); ?>')"/>
			</form>
		</td>
		<td>

			<form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>?page=<?php echo $_REQUEST['page'];?>">
			<input type="hidden" name="reset_single_link" value="yes" \>
			<input type="hidden" name="link" value="<?php echo $link_id; ?>" \>
			<input type="submit" name="Submit" class="button-primary" value="<?php _e('Delete Link', 'wp-click-track'); ?>" onclick="javascript: return confirm('<?php _e('Are you sure you want to delete this link? \nThis can not be undone...', 'wp-click-track'); ?>')"/>
			</form>
		</td>
		</tr>
		</table>
		<br />
	<?php
}


function wpct_admin_list_links($req, $page, $paged, $ref_paged){
	global $wpdb;

	//setup search
	$BuildWhere = '';
	$_search = FALSE;
	$page_query = 'paged';
	if(isset($_REQUEST['s']) && trim($_REQUEST['s']) != ''){
		$_search = $_REQUEST['s'];
		$page_query = 's='.$_search.'&'.$page_query;

		//first check if the table is setup for fulltext searching
		$sql = " SHOW INDEXES FROM ".$wpdb->prefix."tracking_links";
		$result = $wpdb->get_results($sql);
		$count = count($result);
		$full_text = FALSE;
		for($i=0;$i<$count;$i++){
			if(strtoupper($result[$i]->Index_type) == 'FULLTEXT' && $result[$i]->Key_name == 'search_index'){
				$full_text = TRUE;
				break;
			}
		}

		if($full_text == TRUE){
			$BuildWhere = " WHERE MATCH(link_title,link_desc,link_destination) AGAINST('$_search' IN BOOLEAN MODE) ";
		} else {
			$BuildWhere = " WHERE link_title LIKE '%$_search%' ";
		}
	}

	$total_links = wpct_get_link_count($BuildWhere);

	$plugin_options = get_option('click_tracker');
	$link_page_total = $plugin_options['link_list_count'];

	$limitvalue = $paged * $link_page_total - ($link_page_total);

	$numofpages = ceil($total_links / $link_page_total);

	$result = wpct_get_link_data(" $BuildWhere ORDER BY link_total_clicks DESC LIMIT $limitvalue,$link_page_total");
	$select_total = count($result);

	echo '<table width="100%"><tr><td>';
	if($_search) {
		echo '<h3>'.__('Search Results For', 'wp-click-track').': <em>'.$_search.'</em></h3>';
	} else {
		echo '<h3>'.__('Click Tracked Links', 'wp-click-track').'</h3>';
	}
	
	echo '</td><td align="right">';

	echo wpct_pagination($numofpages, $paged, $link_page_total, $total_links, '', $page_query);
	echo '</td></tr></table>';

	wpct_list_links($result, $select_total,FALSE,FALSE);

	echo wpct_pagination($numofpages, $paged, $link_page_total, $total_links, '', $page_query);

}

// Create the function to output the contents of our Dashboard Widget

function wpct_admin_dashboard_widget() {
	// Display whatever it is you want to show
	global $wpdb;


	$site_url = wpct_get_siteurl();
	include_once dirname(__FILE__).'/open-flash-chart/php-ofc-library/open_flash_chart_object.php';
	open_flash_chart_object( '100%', 225, $site_url .'/wp-admin/index.php?wp_ofc=true&range=30', FALSE, $site_url.'/wp-content/plugins/wp-click-track/open-flash-chart/');
	?>

	<div id="stats-info">
	<br />

	<table width="100%" border="0" cellspacing="0" cellpadding="0">
	  <tr>
		<td width="50%" valign="top"><h4 class="heading"><?php _e('Top Links', 'wp-click-track');?></h4>
		<?php
		$date = date("Y-m-d");
		$top_links = wpct_get_link_data(" ORDER BY link_total_clicks DESC, link_unique_clicks DESC LIMIT 8 ");
		//print_r($top_links);
		$total = count($top_links);
		$site_url = wpct_get_siteurl();
		$page_urls = wpct_get_page_values();
		for($i=0;$i<$total;$i++){
			echo '<a href="'.$site_url.'/wp-admin/admin.php?page='.$page_urls['list_all'].'&link='.$top_links[$i]->link_id.'" title="'.$top_links[$i]->link_title.'">'.$top_links[$i]->link_title.'</a><br />';
			echo 'Total: '.$top_links[$i]->link_total_clicks.' Unique: '.$top_links[$i]->link_unique_clicks.'<br>';
		}
		?>
		</td>
		<td width="50%" valign="top"><h4 class="heading"><?php _e('Todays Clicks', 'wp-click-track');?></h4>
		<?php

		$result = wpct_get_todays_links(8);
		$total = count($result);
		if($total == '0'){
			_e('No Clicks Today', 'wp-click-track');
		} else {

			for($i=0;$i<$total;$i++){
				echo '<a href="'.$site_url.'/wp-admin/admin.php?page='.$page_urls['list_all'].'&link='.$result[$i]->link_id.'" title="'.$result[$i]->link_title.'">'.$result[$i]->link_title.'</a><br />';
				echo 'Total: '.$result[$i]->click_count.'<br>';
			}
		}
		?>
		</td>
	  </tr>
	</table>

	<div align="right">
	<a class="button" href="<?php echo $site_url;?>/wp-admin/admin.php?page=<?php echo $page_urls['list_all']; ?>"><?php _e('View All', 'wp-click-track'); ?></a>
	</div>
	</div>
	<?php
}

/**
 * Sets up the admin widget
 * @return void
 */
function wpct_admin_add_dashboard_widgets() {
	wp_add_dashboard_widget('wpct_admin_dashboard_widget', __('Click Tracker', 'wp-click-track'), 'wpct_admin_dashboard_widget');	
}


/**
 * Displays administration quick view 
 *
 * @return  void
 */
function wpct_link_data(){
	global $wpdb;

	$post_id = (isset($_REQUEST['post']) ? $_REQUEST['post'] : FALSE);

	if(!$post_id){
		return FALSE;
	}

	$sql = "SELECT post_content,post_excerpt FROM ".$wpdb->prefix."posts WHERE ID = '".$wpdb->escape($post_id)."'";
	$result = $wpdb->get_results($sql);
	$select_total = count($result);
	?>

<div id='normal-sortables' class='meta-box-sortables '>
<div id="postexcerpt" class="postbox " >
<div class="handlediv" title="Click to toggle"><br /></div><h3 class='hndle'><span><?php _e('Click Tracker', 'wp-click-track'); ?></span></h3>
<div class="inside">

<?php
	$post_links = wpct_extract_urls($result['0']->post_content);
	$_check_urls = array();
	$total = count($post_links);
	for($i=0;$i<$total;$i++){
		$_check_urls[] = $wpdb->escape($post_links[$i]['href']);
	}
	$_check_urls = "'".implode("','",$_check_urls)."'";
	$sql = "SELECT link_title,link_destination,link_total_clicks,link_unique_clicks FROM ".$wpdb->prefix."tracking_links WHERE link_destination IN($_check_urls)";
	$result = $wpdb->get_results($sql);

	$select_total = count($result);
	if($select_total >= '1'){

		wpct_list_links($result, $select_total, FALSE, TRUE);

	} else {

		echo __('No Links Found', 'wp-click-track');
		
	}
?>
</div>
</div>

	<?php
}

/**
 * Displays the main report page
 * @param $req
 * @param $page
 * @param $paged
 * @param $ref_paged
 * @return void
 */
function wpct_admin_main($req, $page, $paged, $ref_paged){
	global $wpdb;

	wp_enqueue_script('jquery');
	$date = (isset($_GET['date']) ? $_GET['date'] : date("Y-m-d"));
	?>
	<div class="head-label">
		<h3 class="head-label"><?php echo __('Clicks on ', 'wp-click-track').mysql2date(get_option('date_format'),$date); ?></h3>
		<div class="head-calendar">
			<a href="javascript:;" onclick="jQuery(document).ready(function($){$('#inlineDatepicker').DatePickerShow()});;"><?php echo wpct_calendar_image(); ?></a> 
			<div id="inlineDatepicker"></div>
		</div>
	</div>
	
	<?php 
	$sql = "SELECT COUNT(click_id) AS link_total_clicks,".$wpdb->prefix."tracking_clicks.link_id AS link_id, date_format(click_date,\"%Y-%m-%d\") AS click_date, link_title, link_destination, click_refer FROM ".$wpdb->prefix."tracking_links,".$wpdb->prefix."tracking_clicks WHERE ".$wpdb->prefix."tracking_links.link_id = ".$wpdb->prefix."tracking_clicks.link_id AND date_format(click_date,\"%Y-%m-%d\") = '$date' GROUP BY ".$wpdb->prefix."tracking_clicks.link_id ORDER BY link_total_clicks DESC ";

	$result = $wpdb->get_results($sql);
	$total = count($result);

	$sql = "SELECT COUNT(click_id) AS link_unique_clicks FROM ".$wpdb->prefix."tracking_clicks WHERE date_format(click_date,\"%Y-%m-%d\") = '$date' AND unique_click = '1' GROUP BY ".$wpdb->prefix."tracking_clicks.link_id ORDER BY link_unique_clicks DESC ";

	$uniques = $wpdb->get_results($sql);
	for($i=0;$i<$total;$i++){
		$result[$i]->link_unique_clicks = $uniques[$i]->link_unique_clicks;
	}

	wpct_list_links($result, $total, FALSE, FALSE);

	wpct_admin_stats(FALSE, $range = 30);
}

function wpct_admin_stats($link_id = FALSE, $range = 30) {

	echo '<h3>'.__('Statistics', 'wp-click-track').'</h3>';

	$site_url = wpct_get_siteurl();
	include_once dirname(__FILE__).'/open-flash-chart/php-ofc-library/open_flash_chart_object.php';
	$cols = array(__('Recent'));
	$rows = array(array(open_flash_chart_object_str( '100%', 225, $site_url .'/wp-admin/index.php?wp_ofc=true&range='.$range.'&link='.$link_id, FALSE, $site_url.'/wp-content/plugins/wp-click-track/open-flash-chart/')));

	echo wpct_admin_table($cols,$rows);

	echo '<br />';

	$cols = array(__('Clicks By Hour', 'wp-click-track'),__('Clicks By Day', 'wp-click-track'));

	$rows = array(array(
				open_flash_chart_object_str( '100%', '400', $site_url .'/wp-admin/index.php?wp_ofc=pie&type=hour&link='.$link_id, FALSE, $site_url.'/wp-content/plugins/wp-click-track/open-flash-chart/'),
				open_flash_chart_object_str( '100%', '400', $site_url .'/wp-admin/index.php?wp_ofc=pie&type=day&link='.$link_id, FALSE, $site_url.'/wp-content/plugins/wp-click-track/open-flash-chart/')
				)
			);
	echo wpct_admin_table($cols,$rows);

	echo '<br />';

	$cols = array(__('History', 'wp-click-track'));
	$rows = array(array(open_flash_chart_object_str( '100%', 225, $site_url .'/wp-admin/index.php?wp_ofc=bar_3d&range='.$range.'&link='.$link_id, FALSE, $site_url.'/wp-content/plugins/wp-click-track/open-flash-chart/')));

	echo wpct_admin_table($cols,$rows);
}