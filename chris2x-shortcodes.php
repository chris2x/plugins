<?php
/*
Plugin Name: chris2x shortcodes
Plugin URI: http://amateurtraveler.com/wordpress-plugins/
Description: this plugin turns a shortcodes into useful features
Author: Chris Christensen
Version: 1.0
Author URI: http://amateurtraveler.com/

*/

function chris2x_tour_shortcode($atts = [], $content = null, $tag = '') {
  $output = '';
  
  $output .= "<div class=\"tour\">";
  $output .= "<p><img src=\"" . $atts['image'] . "\"/></p>";
  $output .= "<p>$content</p>";
  $output .= "<h3>US $". $atts['price'] . "</h3>";
  $output .= "<p><a rel=nofollow target=\"_blank\" href=\"" . $atts['url'] . "\" class=\"buy_button\">Buy</a></p>";
  $output .= "</div>";
  
  return $output;
}

function chris2x_one($atts = [], $content = null, $tag = '') {
  static $already_run = false;
  $output = '';
  
  if ( $already_run !== true ) {
    $output .= "$content";
  }
  $already_run = true;
  
  return $output;
}

function chris2x_another_one($atts = [], $content = null, $tag = '') {
  static $already_run = false;
  $output = '';
  
  if ( $already_run !== true ) {
    $output .= "$content";
  }
  $already_run = true;
  
  return $output;
}

function chris2x_tour_widget($atts = [], $content = null, $tag = '') {
  static $already_run = false;
  $output = '';
  
  $count = 3;
  if (isset($atts['count'])) {
    $count = $atts['count'];
  }
  
  $output .= "<div data-gyg-partner-id=\"PAKTIGU\" data-gyg-number-of-items=\"$count\" data-gyg-currency=\"USD\" data-gyg-locale-code=\"en-US\" data-gyg-placement=\"content-middle\" data-gyg-see-more=\"true\" data-gyg-id=\"$content\" data-gyg-q=\"$content\" data-gyg-widget=\"activites\" data-gyg-href=\"https://widget.getyourguide.com/PAKTIGU/activities.frame\"></div>";
  if ( $already_run !== true ) {
    $output .= "<script async defer src=\"https://widget.getyourguide.com/v2/widget.js\"></script>";
  }
  $already_run = true;
  
  return $output;
}

function chris2x_site22($atts = [], $content = null, $tag = '') {  
  return "<iframe src=\"https://www.stay22.com/embed/gm?aid=5ffd2efdf7caed0017446b8d&markerimage=http://amateurtraveler.com/favicon-32x32.png&maincolor=222222&address=".$content."\" id=\"stay22-widget\" width=\"100%\" height=\"500\" frameborder=\"0\"></iframe><br/><br/>";
}

function chris2x_map($atts = [], $content = null, $tag = '') {
  $width = "100%";
  if (isset($atts['width'])) {
    $width = $atts['width'];
  }  
  
  $height = "400px";
  if (isset($atts['height'])) {
    $height = $atts['height'];
  }
  
  $zoom = "5";
  if (isset($atts['zoom'])) {
    $zoom = $atts['zoom'];
  }
  
  $output = <<<EOT
  <div id="map" style="width: $width; height: $height;"></div>
<script>          
  var map;    
  var src = "$content";
  function initMap() {        
    map = new google.maps.Map(document.getElementById('map'), {          
    });        
    var kmlLayer = new google.maps.KmlLayer(src, {
     map: map
    });
    google.maps.event.addListenerOnce(map, 'zoom_changed', function() {
      map.setZoom($zoom);
    });
  }
</script>
<script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDXaVZdcSky-vLVPfXvp-e-_Ga4dYGFu-A&callback=initMap"></script>
EOT;
  
  return $output;
}

function chris2x_featured($atts = [], $content = null, $tag = '') {
  $title = $atts['title'];
  $delayed = $atts['delayed'];
  $output = '';
  
  $args = array(
    'post_status'=>'publish',
    'posts_per_page' => 16
  );

  if (is_category()) {
		$category = get_category(get_query_var('cat'));
  	$category_id = $category->cat_ID;
		$args['cat'] = $category_id;
	}

  if (is_tag()) {
		$tags = explode("\+", get_query_var('tag'));
		array_push($tags, $content);
		$args['tag_slug__and'] = $tags;
	} else {
	  $args['tag'] = $content; 
	}
  
  $query = new WP_Query($args);
    
  // The Loop
  if ($query->have_posts()) { 
    $output .= <<<EOT
    <div class="feature-outer" id="$content">
    <div class="feature-row">
    <h2>$title</h2>
EOT;
    while ($query->have_posts()) {
      $query->the_post();
			$count++;
			$id = get_the_ID();
			$thumb = get_post_meta($id, 'thumbnail', $single = true);
			$title = esc_attr(get_the_title($post->ID));
			$link = get_the_permalink($post->ID);
			$output .= <<<EOT
		<div class="category-post">
EOT;
			if ($thumb !== '') { 
			  if ($delayed != "") {
  				$output .= <<<EOT
			<div class="thumbnail"><a href="$link"><img src="$thumb" border=0 alt="$title" width=250 height=250 /></a></div>
EOT;
			  }
			  else {
	  			$output .= <<<EOT
			<div class="thumbnail"><a href="$link"><img src="$thumb" border=0 alt="$title" width=250 height=250 /></a></div>
EOT;
			  }
			} 
			$output .= <<<EOT
			<div class="category-post-desc">
			 	<a href="$link">$title</a>
			</div>
		</div>
EOT;
		}
    $output .= <<<EOT
    </div></div><div class="clear"></div>
EOT;
  } 
  return $output;
}

function chris2x_toc($atts = [], $content = null, $tag = '') {
  $output = <<<EOT
<p>
<strong>Table of contents</strong>: (<span onclick="HideShowTOC()" id="button" style="text-decoration: underline;">Hide</span>)
<script type="text/javascript">
function HideShowTOC() {
    var toc = document.getElementById("toc");
    var button = document.getElementById("button");
    if (toc.style.display === "none") {
        toc.style.display = "block";
        button.textContent="Hide";
    } else {
        toc.style.display = "none";
        button.textContent="Show";
    }
}
</script>
<div id="toc">
EOT;

  $output .= $content;
  
  $output .= <<<EOT
</div></p>    
EOT;
  
  return $output;
}


function chris2x_before($atts = [], $content = null, $tag = '') {
  $output = '';
  
  $date = $atts['date'];
  $date = strtotime($date);
  # 8 hours off for PST
  if ((time() - 28800) <= $date) {
    $output .= $content;
  }

  return $output;
}


function chris2x_after($atts = [], $content = null, $tag = '') {
  $output = '';
  
  $date = $atts['date'];
  $date = strtotime($date);
  # 8 hours off for PST
  if ((time() - 28800) > $date) {
    $output .= $content;
  }
  
  return $output;
}


add_shortcode('tour', 'chris2x_tour_shortcode');
add_shortcode('tour_widget', 'chris2x_tour_widget');
add_shortcode('map', 'chris2x_map');
add_shortcode('one', 'chris2x_one');
add_shortcode('another_one', 'chris2x_another_one');
add_shortcode('featured', 'chris2x_featured');
add_shortcode('toc', 'chris2x_toc');
add_shortcode('before', 'chris2x_before');
add_shortcode('after', 'chris2x_after');
add_shortcode('site22', 'chris2x_site22');

?>

