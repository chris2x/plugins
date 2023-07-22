<?php

if(!isset($reporting_included)){
	exit;
}

/**
 * Returns the day of week from a MySQL date number
 *
 * @param   aray  $result	array() of clicks
 * @return  string
 */
function wpct_date_by_num($num){
	
	switch($num){
		case '0':
			return __('Monday');
		break;
		case '1':
			return __('Tuesday');
		break;
		case '2':
			return __('Wednesday');
		break;
		case '3':
			return __('Thursday');
		break;
		case '4':
			return __('Friday');
		break;
		case '5':
			return __('Saturday');
		break;
		case '6':
			return __('Sunday');
		break;
	}
}

include_once dirname(__FILE__).'/open-flash-chart/php-ofc-library/open-flash-chart.php';

$wp_ofc = (isset($_GET['wp_ofc']) ? $_GET['wp_ofc'] : FALSE);
$link_id = (isset($_GET['link']) ? (int)$_GET['link'] : FALSE);
$site_url = get_option( 'siteurl' );

$page_vars = wpct_get_page_values();

switch($wp_ofc){

	case 'pie':

		$type = (isset($_GET['type']) ? $_GET['type'] : 'hour');

		switch($type){

			case 'hour':
			default:
				if($link_id){
					$BuildWhere = " WHERE link_id = '".$wpdb->escape($link_id)."'";
				}
				$sql = "SELECT HOUR(click_date) AS Hour,COUNT(HOUR(click_date)) AS Clicks FROM ".$wpdb->prefix."tracking_clicks $BuildWhere GROUP BY Hour ORDER BY Hour";
				$result = $wpdb->get_results($sql);
				$chart_title = __('Clicks by Hour', 'wp-click-track');

				$total = count($result);
				$labels = array();
				$data = array();
				for($i=0;$i<$total;$i++){

					//if($result[$i]->Hour
					$Hour = ($result[$i]->Hour > 12 ? ($result[$i]->Hour-12).' PM' : $result[$i]->Hour.' AM');
					if($Hour == '12 AM'){ $Hour = '12 PM'; }
					if($Hour == '0 AM'){ $Hour = '12 AM'; }

					$labels[] = $Hour;
					$data[] = $result[$i]->Clicks;
				}



			break;

			case 'day':
				if($link_id){
					$BuildWhere = " WHERE link_id = '".$wpdb->escape($link_id)."'";
				}
				$sql = "SELECT weekday(click_date) AS Day,COUNT(weekday(click_date)) AS CLICKCOUNT FROM ".$wpdb->prefix."tracking_clicks $BuildWhere GROUP BY Day ORDER BY Day";
				$result = $wpdb->get_results($sql);

				$chart_title = __('Clicks by Day', 'wp-click-track');

				$total = count($result);
				$labels = array();
				$data = array();
				for($i=0;$i<$total;$i++){
					if(!array_key_exists($i,$result)){
						continue;
					}

					$Day = wpct_date_by_num($i);
					$labels[] = wpct_date_by_num($result[$i]->Day);
					$data[] = $result[$i]->CLICKCOUNT;
				}

			break;
		}
		
		$g = new graph();

		$g->bg_colour = '#FFFFFF';

		//
		// PIE chart, 60% alpha
		//
		$g->pie(60,'#505050','{font-size: 12px; color: #404040;');
		//
		// pass in two arrays, one of data, the other data labels

		//
		$g->pie_values( $data, $labels );
		//
		// Colours for each slice, in this case some of the colours
		// will be re-used (3 colurs for 5 slices means the last two
		// slices will have colours colour[0] and colour[1]):
		//
		$g->pie_slice_colours( array('#d01f3c','#356aa0','#C79810') );

		$g->set_tool_tip( '#val# '.__('Clicks', 'wp-click-track') );

		//$g->title( $chart_title, '{font-size:18px; color: #d01f3c}' );
		echo $g->render();

	break;

	case 'bar_3d':

		$BuildLinkWhere = "";
		if($link_id){
			//kind of "hacky" I know  
			$BuildLinkWhere = " AND link_id = '".$wpdb->escape($link_id)."'";
		}

		$bar_red = new bar_3d( 75, '#FF0000' );
		$bar_red->key( __('Clicks per day', 'wp-click-track'), 10 );

		$sql = "SELECT COUNT(click_id) AS click_count, date_format(click_date,\"%M\") AS click_month, date_format(click_date,\"%m\") AS first_click_num FROM ".$wpdb->prefix."tracking_clicks WHERE click_id != '0'  $BuildLinkWhere GROUP BY click_month ORDER BY first_click_num";
		$result = $wpdb->get_results($sql);
		$total = count($result);


		// add random height bars:
		$labels = array();
		$max = 0;
		$min = 0;
		for( $i=0; $i<$total; $i++ ) {
			$labels[] = $result[$i]->click_month;
			$bar_red->data[] = $result[$i]->click_count;
			if($max < $result[$i]->click_count){
				$max = $result[$i]->click_count;
			}
		}

		//
		// create a 2nd set of bars:
		//
		$bar_blue = new bar_3d( 75, '#14568a' );
		$bar_blue->key( __('Uniques per day', 'wp-click-track'), 10 );

		$sql = "SELECT COUNT(click_id) AS click_count, date_format(click_date,\"%M\") AS click_month, date_format(click_date,\"%m\") AS first_click_num FROM ".$wpdb->prefix."tracking_clicks WHERE click_id != '0' $BuildLinkWhere AND unique_click = '1' GROUP BY click_month ORDER BY first_click_num";
		$result = $wpdb->get_results($sql);
		$total = count($result);
		for( $i=0; $i<$total; $i++ ) {
			$bar_blue->data[] = $result[$i]->click_count;
		}

		// create the graph object:
		$g = new graph();
		$g->set_num_decimals( 0 );

		//$g->set_data( $data_1 );
		//$g->bar_3D( 75, '#D54C78', '2006', 10 );

		//$g->set_data( $data_2 );
		//$g->bar_3D( 75, '#3334AD', '2007', 10 );

		$g->data_sets[] = $bar_red;
		$g->data_sets[] = $bar_blue;

		$g->set_x_axis_3d( 12 );
		$g->x_axis_colour( '#909090', '#ADB5C7' );
		$g->y_axis_colour( '#909090', '#ADB5C7' );

		$g->set_inner_background( '#ffffff', '#e2e2e2', 90 );
		$g->bg_colour = '#FFFFFF';

		$g->set_x_labels( $labels );
		$g->set_y_max( $max );

		$steps = '6';
		if($max < $steps){
			$steps = $max;
		}
		$g->y_label_steps( $steps );
		$g->set_y_legend( 'wp-click-tracker', 12, '#14568a' );
		echo $g->render();

	break;

	default:

		
		$range = (isset($_GET['range']) ? (int)$_GET['range'] : 14);
		$date = date("Y-m-d",mktime(0, 0, 0, date("m")  , date("d")-$range, date("Y")));
		
		$BuildWhere = " WHERE tc.link_id = tl.link_id AND date_format(click_date,\"%Y-%m-%d\") >= '$date' AND click_date < NOW() ";
		$BuildLinkWhere = "";
		if($link_id){
			//kind of "hacky" I know  
			$BuildLinkWhere = " AND tl.link_id = '".$wpdb->escape($link_id)."'";
			$BuildWhere = "$BuildWhere $BuildLinkWhere";
		}

		//get start date and see if we need to use a seed
		$sql = "SELECT date_format(click_date,\"%Y-%m-%d\") AS first_click, date_format(click_date,\"%j\") AS day_of_year FROM ".$wpdb->prefix."tracking_clicks tc, ".$wpdb->prefix."tracking_links tl WHERE tc.click_id != '0' AND tc.link_id = tl.link_id $BuildLinkWhere GROUP BY first_click ORDER BY first_click DESC LIMIT 1";
		$result = $wpdb->get_results($sql);
		if(count($result) >= '1'){
			$first_click = $result['0']->first_click;
			$first_click_doy = (int)$result['0']->day_of_year;
			
			
			$check_date = strtotime($date);
			$check_date = date('z', $check_date);
			if($first_click_doy > $check_date){
				$range = ($first_click_doy-$check_date);
				$date = $first_click;
			}

		}

		//now create the labels
		$first_done = FALSE;
		for($i=0;$i<$range;$i++){
			$date_range[] = date('Y-m-d',mktime(0, 0, 0, date("m"),   date("d")-$i,   date("Y")));
		}

		$date_range = array_reverse($date_range);
		
		//now grab all the data
		$sql = "SELECT COUNT(tc.link_id) AS click_count, date_format(click_date,\"%Y-%m-%d\") AS click_date_f FROM ".$wpdb->prefix."tracking_clicks tc, ".$wpdb->prefix."tracking_links tl $BuildWhere GROUP BY click_date_f ORDER BY click_date_f ASC LIMIT $range";
		
		$result = $wpdb->get_results($sql);
		$total = count($result);
		$total = ($total <= $range ? $total : $range);

		$data_1 = new line_hollow( 3, 4, '#FF0000' );
		$data_1->key( __('Clicks per day', 'wp-click-track'), 10 );

		$data_2 = new line_hollow( 3, 4, '#14568a' );
		$data_2->key( __('Uniques per day', 'wp-click-track'), 10 );

		$dates = array();

		$max = 0;
		$min = 0;
		$total_clicks = array();
		for($i=0;$i<$total;$i++){

			if($max < $result[$i]->click_count){
				$max = $result[$i]->click_count;
			}

			if($min > $result[$i]->click_count){
				$min = $result[$i]->click_count;
			}

			$total_clicks[$i]['click'] = $result[$i]->click_count;
			$total_clicks[$i]['date'] = $result[$i]->click_date_f;
		}

		$sql = "SELECT COUNT(tc.link_id) AS click_count, date_format(click_date,\"%Y-%m-%d\") AS click_date_f FROM ".$wpdb->prefix."tracking_clicks tc, ".$wpdb->prefix."tracking_links tl $BuildWhere  AND unique_click = '1' GROUP BY click_date_f ORDER BY click_date ASC LIMIT $range";
		$result = $wpdb->get_results($sql);

		$unique_clicks = array();
		for($i=0;$i<$total;$i++){
			$unique_clicks[$i]['click'] = $result[$i]->click_count;
		}

		
		$dates = array();
		for($i=0;$i<$range;$i++){ //loop over every date in $date_range

			$dates[] = mysql2date('m-d',$date_range[$i]);
			$found = FALSE;
			$url = $site_url.'/wp-admin/admin.php?page='.$page_vars['default'].'&req=main&date='.$date_range[$i];
			for($k=0;$k<$total;$k++){
				if($total_clicks[$k]['date'] == $date_range[$i]){
					$found = TRUE;
					$data_1->add_link( $total_clicks[$k]['click'] , $url);
					$data_2->add_link( $unique_clicks[$k]['click'] , $url);
					break;
				}
			}

			if(!$found){
				$data_1->add_link( 0 , $url);
				$data_2->add_link( 0 , $url);
			}
		}

		$g = new graph();
		$g->set_tool_tip( __('Clicks per day:', 'wp-click-track').' #val#' );

		$g->set_num_decimals( 0 );

		// we add 3 sets of data:
		//$g->set_data( $clicks );
		$g->data_sets[] = $data_1;
		$g->data_sets[] = $data_2;

		$g->set_x_labels( $dates );
		$g->set_x_label_style( 8, '0x1a1a1a', 2, 0 );

		$g->x_axis_colour( '#1a1a1a', '#e6f0ff' );
		$g->y_axis_colour( '#1a1a1a', '#e6f0ff' );

		$g->set_inner_background( '#ffffff', '#e2e2e2', 90 );

		$g->bg_colour = '#FFFFFF';

		$g->set_y_max( ($max) );
		$g->set_y_min( $min );

		$steps = '6';
		if($max < $steps){
			$steps = $max;
		}

		$g->y_label_steps( $steps );
		$g->set_y_legend( 'wp-click-tracker', 12, '#14568a' );
		echo $g->render();

		//exit;

	break;
}
?>