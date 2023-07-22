<?php
/*
Plugin Name: Chris2x Recent Revisions
Plugin URI: http://chris2x.com
Description: A revisions widget
Author: Chris Christensen
Version: 1.0
Author URI: http://chris2x.com

*/

function chris2x_revisions_settings_page() {
	global $wpdb;

	  if (array_key_exists('date', $_POST) == 1) {
  		$date = $_POST['date'];  
    }
    else {
      $date = get_the_time(__( 'Y-m-d'));   
    }
  	list($year, $month, $day) = explode('[/.-]', $date);

?>
<div class="wrap">
<h2>Recent revisions</h2>
	
<form method="post" name="options" target="_self">
    <table class="form-table">
        <tr valign="top">
        <th scope="row">Date (e.g. 2013-12-31)</th>
        <td><input type="text" name="date" value="<?php echo $date ?>" /></td>
        </tr>
    </table>
    
    <?php echo $date ?> <?php echo array_key_exists('date', $_POST) ?>
    
    <p class="submit">
    <input type="submit" class="button-primary" value="Submit" />
    </p>
</form>
</div>
<?php

  echo "<table>";

$querystr = "
    SELECT $wpdb->posts.* 
    FROM $wpdb->posts
    WHERE  
      $wpdb->posts.guid like '%revision%'
    ORDER BY $wpdb->posts.post_date DESC limit 50
 ";

 $pageposts = $wpdb->get_results($querystr, OBJECT);
  
  global $post;
  foreach ($pageposts as $post) {
    setup_postdata($post); 
    echo '<tr><td>'.get_the_date('F j, Y g:ia').'<td>' .'<a href="'.get_edit_post_link().'">' . get_the_title() . '</a></td></tr>';
  }
    
  echo "</table>";    
}

function chris2x_revisions_create_menu() {
	//create new top-level menu
	add_options_page('Chris2x revisions Options', 'Chris2x revisions', 'administrator', 'chris2x-revisions', 'chris2x_revisions_settings_page');
}

add_action('admin_menu', 'chris2x_revisions_create_menu');

?>
