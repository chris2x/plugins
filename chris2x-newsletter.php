<?php
/*
Plugin Name: Chris2x Newsletter
Plugin URI: http://chris2x.com
Description: A newsletter widget
Author: Chris Christensen
Version: 1.0
Author URI: http://chris2x.com

*/

// Create a new filtering function that will add our where clause to the query
function chris2x_newsletter_filter_where( $where = '', $date ) {
    $where .= " AND post_date >= \'$date\'";
    return $where;
}

function chris2x_newsletter_settings_page() {
	global $wpdb;

		$date = $_POST['date'];  

?>
<div class="wrap">
<h2>Chris2x Newsletter</h2>
	
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

	if (array_key_exists('date', $_POST) == 1) {
	  echo "<!--  clip here -->\r<hr><form><textarea rows=15 cols=70><h2>Recent Articles</h2>&#13;&#10;";

  	list($year, $month, $day) = explode('[/.-]', $date);

    $args = array(
      'tag' => 'article' ,
	    'date_query' => array(
        array(
          'after'    => array(
            'year'  => $year,
            'month' => $month,
            'day'   => $day,
          ),
          'inclusive' => true,
        ),
      ),
      'posts_per_page' => -1,
    );
    $query = new WP_Query($args);
    
    // The Loop
    if ($query->have_posts()) {   
      while ($query->have_posts()) {
        $query->the_post();
        echo '<li>' .'<a href="'.get_the_permalink().'">' . get_the_title() . '</a></li>&#13;&#10;';
      }
    }
    
	  echo "<h2>Recent Podcasts</h2>&#13;&#10;";

  	list($year, $month, $day) = explode('[/.-]', $date);

    $args = array(
      'tag' => 'travel-podcast' ,
	    'date_query' => array(
        array(
          'after'    => array(
            'year'  => $year,
            'month' => $month,
            'day'   => $day,
          ),
          'inclusive' => true,
        ),
      ),
      'posts_per_page' => -1,
      'post_status'=>'publish'
    );
    $query = new WP_Query($args);
    
    // The Loop
    if ($query->have_posts()) {   
      while ($query->have_posts()) {
        $query->the_post();
        echo '<li>' .'<a href="'.get_the_permalink().'">' . get_the_title() . '</a></li>&#13;&#10;';
      }
    }
	  echo "</textarea></form><hr>\r<!--  clip here -->\r";    
	}
}

function chris2x_newsletter_create_menu() {
	//create new top-level menu
	add_options_page('Chris2x Newsletter Options', 'Chris2x Newsletter', 'administrator', 'chris2x-Newsletter', 'chris2x_newsletter_settings_page');

	//call register settings function
	//add_action( 'admin_init', 'register_chris2x_newsletter_settings' );
}

add_action('admin_menu', 'chris2x_newsletter_create_menu');

?>
