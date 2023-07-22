<?php
/**
 * WP-Click-Tracker Contextual Help
 *
 * Contains the contextual help function.
 *
 * @author Eric Lamb <eric@ericlamb.net>
 * @package    wp-click-tracker
 * @version 0.6
 * @filesource
 * @copyright 2009 Eric Lamb.
 */

/**
 * Determines what to display for the contextual help.
 *
 * @param   string  $content	The initial contextual help string.
 * @return  string
 */
function wpct_contextual_help($content){

	$plugin_data = get_plugin_data(__FILE__);
	$plugin_name = $plugin_data['Name'];
	$plugin_description = $plugin_data['Description'];
	$page = $_REQUEST['page'];
	$page_urls = wpct_get_page_values();

	if(substr($_SERVER['PHP_SELF'],-18) == 'wp-admin/index.php'){

		$content .= '<h5>'.__('Click Tracker', 'wp-click-track').'</h5>';
		$content .= __('The Click Tracker widget shows a snapshot of recent activity (last 30 days).', 'wp-click-track');
		return $content;
	}
	
	if(substr($_SERVER['PHP_SELF'],-18) != 'wp-admin/admin.php'){
		return $content;
	}	

	$search_copy = '<h5>'.__('Search Links', 'wp-click-track').'</h5>';
	$search_copy .= __('Accepts full boolean searching. Try using +, -, * to refine the query', 'wp-click-track');

	$report_copy = __('Line chart of total and unique clicks for the last 30 days. Clicking on a node in the chart will show the history for that date.', 'wp-click-track');
	$report_copy .= '<br />'.__('Pie chart of the clicks by hour of the day', 'wp-click-track');
	$report_copy .= '<br />'.__('Pie chart of the clicks by day of the week', 'wp-click-track');
	$report_copy .= '<br />'.__('Bar chart of total clicks by month.', 'wp-click-track');

	switch($page){

		case $page_urls['default']:
		default:

			$content = $search_copy;
			$content .= '<h5>'.__('Clicks on ---', 'wp-click-track').'</h5>';
			$content .= __('Shows the links that were clicked on for the provided date; today if none given.', 'wp-click-track');
			$content .= '<h5>'.__('Statistics', 'wp-click-track').'</h5>';
			$content .= $report_copy;
			return $content;

		break;

		case $page_urls['configure']:
			$content = $search_copy;
			return $content;
		break;

		case $page_urls['manage']:

			$content = $search_copy;
			if($_REQUEST['req'] == 'edit'){

				$content .= '<h5>'.__('Edit Click Track Link', 'wp-click-track').'</h5>';
				$content .= __('You can change the attributes for the link below.', 'wp-click-track');

			} else {

				$content .= '<h5>'.__('Add Click Track Link', 'wp-click-track').'</h5>';
				$content .= __('Use the form below to modify an existing, or add a new, click tracked link. These are useful for placing on an external website.', 'wp-click-track');
			}

			$content .= '<br />'.__('Link Name and Link URL are required fields.', 'wp-click-track');

			return $content;

		break;

		case $page_urls['list_all']:

			$content = $search_copy;
			if(isset($_REQUEST['link'])){

				if(isset($_REQUEST['ip'])){
					
					$content .= '<h5>'.__('Click Path for ---', 'wp-click-track').'</h5>';
					$content .= __('Lists all the clicks for a provided IP address; ordered by date.', 'wp-click-track');

				} else {

					$content .= '<h5>'.__('Distributable Link', 'wp-click-track').'</h5>';
					$content .= __('The Distributable Link is useful for adding links to external websites. Just copy the URL type you want to use from the below.', 'wp-click-track');
					$content .= '<h5>'.__('Link Data', 'wp-click-track').'</h5>';
					$content .= __('Link Data contains a basic list of basic stats.', 'wp-click-track');
					$content .= '<h5>'.__('Statistics', 'wp-click-track').'</h5>';
					$content .= $report_copy;
					$content .= '<h5>'.__('Top Referrers', 'wp-click-track').'</h5>';
					$content .= __('Lists what pages the link clicks came from.', 'wp-click-track');
					$content .= '<h5>'.__('Individual Clicks', 'wp-click-track').'</h5>';
					$content .= __('Lists a complete list of all clicks. If you click on the IP address you can view the click path for that IP address. This\'ll allow you to see all the links a particular IP address has clicked on.', 'wp-click-track');
				}

			} else {

				$content .= '<h5>'.__('Click Tracked Links', 'wp-click-track').'</h5>';
				$content .= __('Displays all links ordered from highest click count.', 'wp-click-track');

			}
			return $content;

		break;
	}

	return $content;
}

add_filter('contextual_help', 'wpct_contextual_help', 11, 2);
?>