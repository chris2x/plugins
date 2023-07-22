<?php
/*
Plugin Name: Chris2x TagAds
Plugin URI: http://chris2x.com
Description: A tag ads widget
Author: Chris Christensen
Version: 1.2
Author URI: http://chris2x.com

*/

function strnpos($haystack, $needle, $nth=1, $offset=0) {
  if ($nth < 1) 
  	$nth = 1;
  $loop1 = TRUE;
  while ($nth > 0) {
    $offset = strpos($haystack, $needle, $loop1 ? $offset : $offset+1);
    if ($offset === FALSE) 
    	break;
    $nth--;
    $loop1 = FALSE;
  }
  return $offset;
}

function replace_nth($search, $replace, $data, $n) {
    $res = strnpos($data, $search, $n);
    if ($res === false) {
        return $data;
    } else {
        // There is data to be replaced
        $left_seg = substr($data, 0, $res);
        $right_seg = substr($data, ($res + strlen($search)));
        return $left_seg . $replace . $right_seg;
    }
}  

class chris2XTagAds {
	function get() {
		$saved_options = maybe_unserialize(get_option('chris2XTagAdsOptions'));
		
		if (!empty($saved_options) && is_object($saved_options)) {
			foreach ($saved_options as $option_name => $value)
				$this->$option_name = $value;
		}
		else {
			$this->version = 1;
			$this->ads['travel'][0] = '<a href="http://AmateurTraveler.com">Amateur Traveler - the best places to travel</a>';
									
			update_option('chris2XTagAdsOptions', $this);
		}
	}
	
	function save() {
		update_option('chris2XTagAdsOptions', $this);
	}
}

function chris2x_log($msg, $path = "") {
  if(empty($path)){
    $path = dirname(__FILE__)."/";
  }
  if(!is_string($msg)){
    $msg = print_r($msg,true);
  }
  $fp = fopen($path.'chris2x.log', 'a+');
  $date = gmdate( 'Y-m-d H:i:s' );
  fwrite($fp, "$date - $msg\n");
  fclose($fp);
 }

function chris2x_tagads_filter($text) {
	global $wpdb;
	$maxads = 2;
	$table_name = $wpdb->prefix . "chris2x_tagads";
	$tag_table_name = $wpdb->prefix . "chris2x_tagads_tags";
	
	if (is_page()) {
		return $text;
	}
	$tags = (array) null;
	
	$posttags = get_the_tags();
	if ($posttags) {	
    foreach($posttags as $tag) {
      array_push($tags, $tag->name);
    }
  }
  
  $postcats = get_the_category();
  if ($postcats) {
    foreach($postcats as $cat) {
      array_push($tags, $cat->name);
    }
  }
  
  # make it 2x as likely to find an ad from the targeted tag or category
  array_push($tags, 'global');
	$tags_list = "('".join("','", $tags)."')";
  $results = $wpdb->get_results("SELECT ad, priority FROM $table_name a, $tag_table_name t WHERE t.tag_name = a.tag_name and (expires = '0000-00-00' or expires is null or expires >= curdate()) and short_code = '' and a.tag_name in ".$tags_list, ARRAY_A);
	
	if (!empty($results)) {
	  # make buckets by priority
		$buckets = (array) null;
  	$priorities =  $wpdb->get_results("SELECT DISTINCT priority from $tag_table_name  order by priority ASC");

		foreach ($priorities as $priority) {
		  $p = $priority->priority;
		  $buckets[$p] = (array) null; 
		}
  	
  	# sort ads by priority
		foreach ($results as $ad) {
		  array_push($buckets[$ad['priority']], $ad['ad']);
    }
    
		$ads = "";
		$n = 0;

    foreach ($priorities as $priority) {
		  $p = $priority->priority;

			if ($n >= $maxads)
				break;

      shuffle($buckets[$p]);
      
      foreach ($buckets[$p] as $ad) {
        if ($n >= $maxads)
          break;
			  $ads .= "<li>". stripslashes($ad) . "</li>";			
			  $n++;        
      }
    }
		
		if (strpos($ads,'%RANDOM%') !== false) {
		  $ads = str_replace("%RANDOM%", rand(), $ads);
		}
	
	  if (substr_count($text, "<!--tagads-->") > 0) {
      $text = replace_nth("<!--tagads-->", "<!-- tagads $tags_list --><div class=\"infobox\"><ul>".$ads."</ul></div>", $text, 1);
	  }
	  else {
      $n = substr_count($text, "</p>");
      if ($n >= 3) {
        $j = rand(3, $n);
        $text = replace_nth("</p>", "</p>\n<!-- tagads $tags_list --><div class=\"infobox\"><ul>".$ads."</ul></div>", $text, $j);
      }
      else {
        $text = $text."<div class=\"infobox\"><ul>".$ads."</ul></div>";
      }
		}
	}
	return $text;
}

function chris2x_tagads_settings_page() {
	global $wpdb;
	$table_name = $wpdb->prefix . "chris2x_tagads";
	$tag_table_name = $wpdb->prefix . "chris2x_tagads_tags";
	$id = "";

	if (array_key_exists('action', $_POST)) {
		$action = $_POST['action'];
	}
	if (array_key_exists('function', $_POST)) {
		$action = $_POST['function'];
	}
	if (array_key_exists('chris2x-tagads-command', $_POST)) {
		$action = $_POST['chris2x-tagads-command'];
	}
	$prompt = "";

	if ($action == 'Delete') {
		$id = $_POST['id'];
		$tag_name = $_POST['tag_name'];
		
		
		if ($id != "") {
			$result = $wpdb->delete( $table_name , array( "id" => $id ));
      	}
	}

	if ($action == 'create-ad') {
		$ad = $_POST['ad'];
		$tag_name = $_POST['tag_name'];
		$priority = $_POST['priority'];
		$short_code = $_POST['short_code'];
		$expires = $_POST['expires'];
		$advertiser = $_POST['advertiser'];
		
		if (($ad != "") && ($tag_name != "")) {		
			$wpdb->insert( $table_name, array( 'tag_name' => $tag_name, 'short_code' => $short_code, 'ad' => $ad, 'advertiser' => $advertiser, 'expires' => $expires));				
			
			$sql = "REPLACE INTO $tag_table_name (tag_name, priority) VALUES('$tag_name', $priority)";
      $results = $wpdb->get_results($sql);
			$prompt = "One ad inserted";
      	}
	}
	
	if ($action == 'update-ad') {
		$id = $_POST['id'];
		$ad = $_POST['ad'];
		$tag_name = $_POST['tag_name'];
		$short_code = $_POST['short_code'];
		$priority = $_POST['priority'];
		$expires = $_POST['expires'];
		$advertiser = $_POST['advertiser'];
		if ($expires."x" == "x") {
			$expires = NULL;
		}
		
		if (($ad != "") && ($tag_name != "")) {		
			$wpdb->update( $table_name, array( 'tag_name' => $tag_name, 'short_code' => $short_code, 'ad' => $ad, 'advertiser' => $advertiser, 'expires' => $expires), array( 'ID' => $id ));
			$sql = "REPLACE INTO $tag_table_name (tag_name, priority) VALUES('$tag_name', $priority)";
      $results = $wpdb->get_results($sql);
			$prompt = "One ad updated";
    }
	}	

?>
<div class="wrap">
<h2>Chris2x Tagads</h2>

<p><?php echo $prompt ?></p>

<?php 
	if ($action == 'Edit') {
		$id = $_POST['id'];
		
		$results = $wpdb->get_row("SELECT * from $table_name WHERE id = '".$id."'", ARRAY_A);		
		$tag_name = $results['tag_name'];
		$short_code = $results['short_code'];
		$advertiser = $results['advertiser'];
		$expires = $results['expires'];
		$ad = $results['ad'];
		$results = $wpdb->get_row("SELECT * from $tag_table_name WHERE tag_name = '".$tag_name."'", ARRAY_A);	
		$priority = $results['priority'];
	}	
	else {
		$id = "";
		$advertiser = "";
		$short_code = "";
		$expires = "";
		$ad = "";
		$priorty = "";
	}	

  if ($id == "") { ?>
  <h3>Create an Ad</h3>
<?php } else { ?>
  <h3>Update an Ad</h3>
<?php } ?>

<form method="post" name="options" target="_self">
	<input type="hidden" name="id" value="<?php echo $id ?>" />    
    <?php settings_fields( 'chris2x-tagads-settings-group' ); ?>
    <table class="form-table">
        <tr valign="top">
        <th scope="row">Id</th>
        <td><?php echo $id ?></td>
        </tr>

        <tr valign="top">
        <th scope="row">Tag</th>
        <td><input type="text" name="tag_name" value="<?php echo $tag_name ?>" /></td>
        </tr>

        <tr valign="top">
        <th scope="row">Priority</th>
        <td><input type="text" name="priority" value="<?php echo $priority ?>" /></td>
        </tr>

        <tr valign="top">
        <th scope="row">Short Code</th>
        <td><input type="text" name="short_code" value="<?php echo $short_code ?>" /></td>
        </tr>

        <tr valign="top">
        <th scope="row">Advertiser</th>
        <td><input type="text" name="advertiser" value="<?php echo $advertiser ?>" /></td>
        </tr>

        <tr valign="top">
        <th scope="row">Expires (e.g. 2013-12-31)</th>
        <td><input type="text" name="expires" value="<?php echo $expires ?>" /></td>
        </tr>
         
        <tr valign="top">
        <th scope="row">Ad</th>
        <td><textarea name="ad" rows=4 cols=60><?php echo stripslashes($ad) ?></textarea></td>
        </tr>        
    </table>
    
    <p class="submit">
    <?php if ($id == "") { ?>
	<input type="hidden" name="chris2x-tagads-command" value="create-ad" />
    <input type="submit" class="button-primary" value="<?php _e('Create Ad') ?>" />
	<?php } else { ?>
	<input type="hidden" name="chris2x-tagads-command" value="update-ad" />
    <input type="submit" class="button-primary" value="<?php _e('Update Ad') ?>" />
	<?php } ?>
    </p>
</form>

<?php
	$tag_name = $_REQUEST['tag_name'];

	if ($tag_name != "") {
?>
	<h3>Ads for tag / category '<?php echo $tag_name ?>'</h3>
<?php	
	$results = $wpdb->get_results("SELECT * FROM $table_name WHERE tag_name = '".$tag_name."'", ARRAY_A);
		
?>	
    <table class="form-table">
<?php	
	foreach ($results as $ad) {
		$expires = $ad['expires'];
		if ($expires == "0000-00-00") {
			$expires = "";
		}
?>	
<form method="post" name="options" target="_self">
<?php settings_fields( 'chris2x-tagads-settings-group' ); ?>
        <tr valign="top">
        <td width=60 ><input type="submit" class="button-primary" name="function" value="<?php _e('Delete') ?>" /></td>
        <td width=60 ><input type="submit" class="button-primary" name="function" value="<?php _e('Edit') ?>" /></td>
		<td><?php echo $ad['advertiser'] ?></td>
		<td><?php echo $ad['short_code'] ?></td>
		<td><?php echo $expires ?></td>
		<td align=left><?php echo stripslashes($ad['ad']) ?></td>
        </tr>
	<input type="hidden" name="tag_name" value="<?php echo $tag_name ?>" />    
	<input type="hidden" name="id" value="<?php echo $ad['id'] ?>" />    
</form>
<?php  
		}
?>
</table>
<?php  		
	}
	
?>

<h3>Show Ads</h3>

<form method="post" name="options" target="_self">
<?php settings_fields( 'chris2x-tagads-settings-group' ); ?>
    <table class="form-table">
		<tr valign="top">
		<th scope="row">Tag</th>
		<td><input type="text" name="tag_name" value="" /></td>
		</tr>
    </table>

	<input type="hidden" name="chris2x-tagads-command" value="show-ads" />
    <p class="submit">
    <input type="submit" class="button-primary" name="function" value="<?php _e('Show Ads') ?>" />
    </p>
</form>
</div>

<?php
	$results = $wpdb->get_results("SELECT tag_name,count(*) FROM $table_name group by tag_name order by count(*) desc", ARRAY_A);
	$bins = $results[0]['count(*)'] / 20;	
	$results = $wpdb->get_results("SELECT tag_name,count(*) FROM $table_name group by tag_name order by tag_name", ARRAY_A);
	foreach ($results as $tag) {
	  $count = $tag['count(*)'];
		$size = strlen("$count") * 12;
?>
<div style="float: left; padding: 5px;"><a href="?page=chris2x-tagads&tag_name=<?php echo $tag['tag_name'] ?>"><span style="font-size: <?php echo $size ?>pt; padding: 5px;"><?php echo $tag['tag_name'] ?></a></span></div>&nbsp;
<?php
	}	
}

function register_chris2x_tagads_settings() {
	//register our settings
	register_setting( 'chris2x-tagads-settings-group', 'new_option_name' );
	register_setting( 'chris2x-tagads-settings-group', 'some_other_option' );
}

function chris2x_tagads_create_menu() {
	//create new top-level menu
	add_options_page('Chris2x TagAds Options', 'Chris2x TagAds', 'administrator', 'chris2x-tagads', 'chris2x_tagads_settings_page');

	//call register settings function
	add_action( 'admin_init', 'register_chris2x_tagads_settings' );
}


function chris2x_tagads_install () {
	global $wpdb;

	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	error_log("chris2x_tagads_install");

	$table_name = $wpdb->prefix . "chris2x_tagads";
      
  $result =  $wpdb->get_results("SHOW TABLES LIKE '".$table_name."'");
  if($result[0] == 1) {
	  error_log("$table_name exists");
  }
  else {
	  error_log("create $table_name");
    $sql = "CREATE TABLE " . $table_name . " (
      id mediumint(9) NOT NULL UNIQUE AUTO_INCREMENT,
      tag_name varchar(64) NOT NULL,
      short_code varchar(64) NOT NULL,
      ad text NOT NULL,
      expires date,
      advertiser varchar(64), 
      INDEX (tag_name)
    );";
    dbDelta($sql);
  }

	$tag_table_name = $wpdb->prefix . "chris2x_tagads_tags";      
	
  $result =  $wpdb->get_results("SHOW TABLES LIKE '".$tag_table_name."'");
  if($result[0] == 1) {
	  error_log("$tag_table_name exists");
  }
  else {
	  error_log("create $tag_table_name");
    $sql = "CREATE TABLE " . $tag_table_name . " (
      priority mediumint(9) NOT NULL,
      tag_name varchar(64) NOT NULL UNIQUE
    );";
    dbDelta($sql);
  }	

/*
	$tagAds = new chris2XTagAds;
	$tagAds->get();
	foreach ($tagAds->ads as $k => $v) {
		foreach ($tagAds->ads[$k] as $ad) {
			$wpdb->insert( $table_name, array( 'tag_name' => $k, 'ad' => $ad));
		}
	}
*/	
	
	add_option("chris2x_tagads_db_version", "1.0");
}

function chris2x_tagads_shortcode($atts) {
	global $wpdb;
	$table_name = $wpdb->prefix . "chris2x_tagads";
	
  $a = shortcode_atts( array(
    'ad' => 'something',
  ), $atts, 'tagads' );
  $short_code = $a['ad'];
    
  if ("$short_code" == "") {
    return "no ad";
  }
  
	$tags = (array) null;
	
	$posttags = get_the_tags();	
	if ($posttags) {
  foreach($posttags as $tag) {
    array_push($tags, $tag->name);
  }
}	

  $postcats = get_the_category();
  if ($postcats) {
    foreach($postcats as $cat) {
      array_push($tags, $cat->name);
    }
  }
	$tags_list = "('".join("','", $tags)."','global')";
  $ads = $wpdb->get_results("SELECT ad FROM $table_name WHERE (expires = '0000-00-00' or expires >= curdate()) and short_code = '".$short_code."' and tag_name in ".$tags_list, ARRAY_A);

	if (!empty($ads)) {
		shuffle($ads);
		return stripslashes($ads[0]['ad']);
	}
	else {
	  return "no ad";
	}
}

add_filter('widget_text', 'do_shortcode');
add_shortcode('tagads' , "chris2x_tagads_shortcode");
add_filter("the_content", "chris2x_tagads_filter");
add_action('admin_menu', 'chris2x_tagads_create_menu');
register_activation_hook(__FILE__,'chris2x_tagads_install');

?>
