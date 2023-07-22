<?php
/**
 * WP-Click-Tracker Template Functions
 *
 * Contains all the click tracker functions for the administration module.
 *
 * @author Eric Lamb <eric@ericlamb.net>
 * @package    wp-click-tracker
 * @version 0.6
 * @filesource
 * @copyright 2009 Eric Lamb.
 */

/**
 * Returns the pagination links based on the passed options
 *
 * @param   string  $numofpages			the total number of pages
 * @param   string  $ref_paged			the current page
 * @param   string  $link_page_total	the total number of items
 * @param   string  $ref_paged	the current page
 * @param   string  $ref_paged	the current page
 * @return  string				The completed 
 */
function wpct_pagination($numofpages = 1, $ref_paged = 1, $link_page_total = 1, $total = 1, $anchor = FALSE, $paging_var = 'ref_paged'){

	$page_links = paginate_links( array(
		'base' => add_query_arg( $paging_var, '%#%#'.$anchor ),
		'format' => '',
		'prev_text' => __('&laquo;'),
		'next_text' => __('&raquo;'),
		'total' => $numofpages,
		'current' => $ref_paged
	));

	if ( $page_links ) { 
		$page_links_text = sprintf( '<span class="displaying-num">' . __( 'Displaying %s&#8211;%s of %s' ) . '</span>%s',
		number_format_i18n( ( $ref_paged - 1 ) * $link_page_total + 1 ),
		number_format_i18n( min( $ref_paged * $link_page_total, $total ) ),
		number_format_i18n( $total ),
		$page_links
		); 
		
		return "<div class='tablenav'><div class='tablenav-pages'>$page_links_text</div></div>";
	}

}

/**
 * Generates the table for the admin module
 *
 * @param  array	$cols		colum headers for the table
 * @param  array	$rows		multidemensional array containing all the rows
 * @param  mixed	$msg		the message, if any, to add to bottom of the table
 * @param  bool		$tfoot		whether to display the tfooter
 * @param  bool		$add_break	whether to add a br tag at the bottom of the table
 * @return string	$table
 */
function wpct_admin_table($cols, $rows, $msg = FALSE, $tfoot = TRUE, $add_break = FALSE){

	$total_cols = count($cols);
	$total_rows = count($rows);

	$table = '<table class="widefat comments-box " cellspacing="0"><thead><tr>';
	$table_cols = '';
	for($i=0;$i<$total_cols;$i++){
		$table_cols .='<th nowrap>'.$cols[$i].'</th>';
	}

	$table .= $table_cols.'</tr></thead>';
	if($tfoot){
		$table .= '<tfoot><tr>'.$table_cols.'</tr></tfoot>';
	}

	if($total_rows == 0){
		$table .= '<tr><td colspan="'.$total_cols.'" align="center">'.__('Nothing Found','wp-click-track').'</td></tr>';
	} else {

		for($i=0;$i<$total_rows;$i++){
			
			$table .= '<tr>';
			$total_cols = count($rows[$i]);
			for($k=0;$k<$total_cols;$k++){
				$table .='<td>'.$rows[$i][$k].'</td>';
			}
			$table .= '<tr>';
		}
	}

	$table .= '<tbody id="the-comment-list" class="list:comment"></tbody></table>';

	if($add_break){
		$table .= '<br />';
	}
	return $table;
}

/**
 * Displays the message on admin page
 *
 * @return  void
 */
function wpct_admin_message($message){
?>
	<div class="updated">
		<strong><p><?php echo $message; ?></p></strong>
	</div>
<?php
}

/**
 * Displays the click tracker administration module
 *
 * @return  void
 */
function wpct_admin_module_template($content) {
	global $click_tracker_db_version,$wpdb,$link_page_total;

	$new_version = TRUE;
	$plugin_data = get_plugin_data(wpct_path_info(__FILE__));
	$current = get_option( 'update_plugins' );

	if (!current_user_can('manage_links'))
		wp_die(__("You do not have sufficient permissions to edit the links for this blog."));

	$req = (isset($_REQUEST['req']) ? $_REQUEST['req'] : FALSE);
	$link_id = (isset($_REQUEST['link']) ? (int)$_REQUEST['link'] : FALSE);
	$page = (isset($_REQUEST['page']) ? $_REQUEST['page'] : FALSE);
	$paged = (isset($_REQUEST['paged']) ? (int)$_REQUEST['paged'] : '1');
	$ref_paged = (isset($_REQUEST['ref_paged']) ? (int)$_REQUEST['ref_paged'] : '1');
	$ref_paged = (isset($_REQUEST['ref_paged']) ? (int)$_REQUEST['ref_paged'] : '1');
	$ip_addr = (isset($_REQUEST['ip']) && long2ip($_REQUEST['ip']) ? long2ip($_REQUEST['ip']) : FALSE);

	$file = plugin_basename(__FILE__);
	?>
	<div class="wrap" id="sm_div">
	<?php
	$page_urls = wpct_get_page_values();
	if($page == $page_urls['configure']){
	?>
			<div id="icon-options-general" class="icon32"><br /></div>

				<h2><?php _e('Configure', 'wp-click-track'); ?> <?php echo $plugin_data['Name']; ?> <?php echo $plugin_data['Version']; ?></h2>
	<?php
	} else {
	?>
		<div id="icon-link-manager" class="icon32"><br /></div>
			<h2><?php echo $plugin_data['Name']; ?> <?php echo $plugin_data['Version']; ?></h2>
	<?php 
	}
	//check if an update has been issued

	$r = $current->response[$file];
	if(isset($current->response[$file])) {
		$r = $current->response[$file];
		if ( !current_user_can('edit_plugins') || version_compare($wp_version,"2.5","<") ) {
			wpct_admin_message( __(sprintf('There is a new version of %s available. <a href="%s">Download version %s here</a>.',$plugin_data['Name'], $r->url, $r->new_version),'wp-click-track'));
		} elseif ( empty($r->package) ) {
			wpct_admin_message( __(sprintf('There is a new version of %s available. <a href="%s">Download version %s here</a> <em>automatic upgrade unavailable for this plugin</em>.',$plugin_data['Name'], $r->url, $r->new_version),'wp-click-track'));
		} else {
			$update_url = wp_nonce_url("update.php?action=upgrade-plugin&amp;plugin=$file", 'upgrade-plugin_' . $file);
			wpct_admin_message( __(sprintf('There is a new version of %s available. <a href="%s">Download version %s here</a> or <a href="%s">upgrade automatically</a>.', $plugin_data['Name'], $r->url, $r->new_version, $update_url),'wp-click-track'));
		}
	}

	//check if the blog has GoogleAnalyticsPP
	//warn about incompatibility...
	$google_options = get_option('GoogleAnalyticsPP');
	if(isset($google_options['trackoutbound']) && $google_options['trackoutbound'] == '1'){
		wpct_admin_message( __('It looks like you have Google Analytics for WordPress installed with Track Outbound links enabled. This can cause issues with '.$plugin_data['Name'].' '.$plugin_data['Version'].'. <p>Click <a href="'.$_SERVER['PHP_SELF'].'?page='.$_REQUEST['page'].'&req=ga_outbound">here</a> to disable.','default'));
	}

	if(isset($_REQUEST['msg'])){

		switch($_REQUEST['msg']){

			case 'success_add':
				wpct_admin_message( __('Link Added','wp-click-track'));
			break;

			case 'success_update':
				wpct_admin_message( __('Link Modified','wp-click-track'));
			break;

			case 'fail_update':
				wpct_admin_message( __('Couldn\'t Update Link','wp-click-track'));
			break;

			case 'link_deleted':
				wpct_admin_message( __('Link Deleted!','wp-click-track'));
			break;
		}
	}

	$total_links = wpct_get_link_count(); 
	?>

	<ul class="subsubsub">
		<li><a href='<?php echo $_SERVER['PHP_SELF'].'?page='.$page_urls['default']; ?>'  <?php echo ($page_urls['default'] == $page ? 'class="current"' : ''); ?>><?php _e('Reports', 'wp-click-track'); ?></a> |</li>
		<li><a href='<?php echo $_SERVER['PHP_SELF'].'?page='.$page_urls['list_all']; ?>' <?php echo ($page_urls['list_all'] == $page ? 'class="current"' : ''); ?>><?php _e('List All', 'wp-click-track'); ?> <span class="count">(<?php echo $total_links; ?>)</span></a> |</li>
		<?php

		if($link_id){
			echo ' <li><a href="'.$_SERVER['PHP_SELF'].'?page='.$page_urls['manage'].'&link='.$link_id.'&req=edit" '.($page_urls['manage'] == $page && 'edit' == $req ? 'class="current"' : '').'>'.__('Edit Link', 'wp-click-track').'</a> | </li>';
		}

		if($ip_addr || 'edit' == $req) {
			echo ' <li><a href="'.$_SERVER['PHP_SELF'].'?page='.$page_urls['list_all'].'&link='.$link_id.'">'.__('Back to Link', 'wp-click-track').'</a> | </li>';
		}
		?>
		<li><a href='<?php echo $_SERVER['PHP_SELF'].'?page='.$page_urls['manage']; ?>' <?php echo ($page_urls['manage'] == $page && 'edit' != $req ? 'class="current"' : ''); ?>><?php _e('Add Link', 'wp-click-track'); ?></a> |</li>
		<li><a href='<?php echo $_SERVER['PHP_SELF'].'?page='.$page_urls['configure']; ?>' <?php echo ($page_urls['configure'] == $page ? 'class="current"' : ''); ?>><?php _e('Configure', 'wp-click-track'); ?></a></li>
	</ul>


	<?php

	echo '<div align="right">';

	echo ' </div>';

	?>
	<br />

	<form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>?page=<?php echo $page_urls['list_all']; ?>">
	<p class="search-box">
		<label class="screen-reader-text" for="link-search-input"><?php _e('Search Links', 'wp-click-track'); ?>:</label>

		<input type="text" id="link-search-input" name="s" value="<?php echo (isset($_REQUEST['s']) && $_REQUEST['s'] != '' ? $_REQUEST['s'] : ''); ?>" />
		<input type="submit" value="<?php _e('Search Links', 'wp-click-track'); ?>" class="button" />
	</p>
	</form>
	<br />

	<?php

	switch($req){

		case 'ga_outbound':
			$options = get_option('GoogleAnalyticsPP');
			$options['trackoutbound'] = 0;
			update_option('GoogleAnalyticsPP', $options);
		break;

	}

	switch($page){

		case $page_urls['manage']:
			
			if($link_id){
				echo '<h3>'.__('Edit Click Track Link', 'wp-click-track').'</h3>';
				wpct_mod_link_form($link_id, $req);
			} else {
				echo '<h3>'.__('Add Click Track Link', 'wp-click-track').'</h3>';
				wpct_mod_link_form(FALSE,$req);
			}

		break;

		case $page_urls['configure']:
			wpct_options();
		break;


		case $page_urls['list_all']:

			if($link_id){
				
				if($ip_addr){

					wpct_admin_list_ip_page($ip_addr, $link_id, $req, $page, $paged, $ref_paged);

				} else {

					wpct_admin_list_link_page($link_id, $req, $page, $paged, $ref_paged);
					
				}

			} else {
				
				wpct_admin_list_links($req, $page, $paged, $ref_paged);
				
			}
			
		break;

		default:
		case $page_urls['default']:
				
			wpct_admin_main($req, $page, $paged, $ref_paged);

		break;
	}

	echo '<div style="height:100px;"></div>';
}


function wpct_calendar_image()
{
	return '<img src="'.wpct_get_siteurl().'/wp-content/plugins/wp-click-track/images/calendar.png" border="0"/>';
}

/**
 * Adds the javascript embed to the admin header
 *
 * @return  void
 */
function wpct_admin_head(){

	if(substr($_SERVER['PHP_SELF'],-18) == 'wp-admin/admin.php' || isset($_REQUEST['page']) && $_REQUEST['page'] == 'wp-click-track/configure.php'){
		echo "<link rel=\"stylesheet\" href=\"".wpct_get_siteurl()."/wp-content/plugins/".basename(dirname(__FILE__))."/css/config.ui.all.css\" type=\"text/css\" media=\"screen\" />\n";
		echo "<link rel=\"stylesheet\" href=\"".wpct_get_siteurl()."/wp-content/plugins/".basename(dirname(__FILE__))."/css/main.css\" type=\"text/css\" media=\"screen\" />\n";
		$page_vars = wpct_get_page_values();
		$page_url = wpct_get_siteurl().$_SERVER['PHP_SELF'].'?page='.$page_vars['default'];
		$date = (isset($_GET['date']) && strtotime($_GET['date']) ? $_GET['date'] : date('Y-m-d'));
		?>
		<style type="text/css">
		@import "<?php echo wpct_get_siteurl()."/wp-content/plugins/".basename(dirname(__FILE__)); ?>/css/datepicker.css";
		</style>
		
		<script type="text/javascript" src="<?php echo wpct_get_siteurl()."/wp-content/plugins/".basename(dirname(__FILE__)); ?>/js/datepicker.js"></script>
		<script type="text/javascript">
		var start_date = '<?php echo $date; ?>';
		jQuery(document).ready(function($){
			$('#inlineDatepicker').DatePicker({
				flat: false,
				format:'Y-m-d',
				date: '<?php echo $date; ?>',
				current: '<?php echo $date; ?>',
				calendars: 1,
				starts: 1,
				onChange: function(date, fdsa, hh){

					if(start_date != date)
					{
						//alert('start_date=' + start_date + '&date=' +date);
						//alert('<?php echo $page_url.'&date='; ?>' + date + 'fdsa=' + fdsa + 'hh=' + hh);
						window.location = '<?php echo $page_url.'&date='; ?>' + date;
					}
					
					
				}
	
			});
		});
		</script>
		<?php 		
	}
}