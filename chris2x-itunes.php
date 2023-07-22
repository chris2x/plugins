<?php
/*
Plugin Name: Play iTunes enhanced .m4a audio files
Plugin URI: http://amateurtraveler.com/wordpress-plugins/
Description: This plugin provides a shortcode to insert a player to play iTunes enhanced .m4a audio files
Author: Chris Christensen
Version: 1.0
Author URI: http://amateurtraveler.com/

*/

function is_ipad() {
	$is_ipad = (bool) strpos($_SERVER['HTTP_USER_AGENT'],'iPad');
	if ($is_ipad)
		return true;
	else return false;
}
function is_iphone() {
	$cn_is_iphone = (bool) strpos($_SERVER['HTTP_USER_AGENT'],'iPhone');
	if ($cn_is_iphone)
		return true;
	else return false;
}

function chris2x_itunes_filter($text) {
	preg_match("/\[itunes: ([^\]]+)\]/", $text, $matches);

	foreach ($matches as $val) {
      $text = str_replace("[itunes: ".$val."]", "<!--<audio controls><source src=\"$val\" type=\"audio/aac\"></audio>-->", $text);
	}

	return $text;
}

add_filter("the_content", "chris2x_itunes_filter", 1);

?>
