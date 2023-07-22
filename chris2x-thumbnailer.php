<?php
/*
Plugin Name: Chris2x Thumbnailer
Plugin URI: http://chris2x.com
Description: Set the thumbnail property with an imgae in the post if it is not set
Author: Chris Christensen
Author URI: http://chris2x.com 

	Copyright (c) 2009 Chris Christensen
	Chris2x Thumbnailer is released under the GNU General Public License (GPL)
	http://www.gnu.org/licenses/gpl.txt

	This is a WordPress 2 plugin (http://wordpress.org).
*/

				
function find_thumbnails() {	
	global $post;
	
	$thumbnail = get_post_meta($post->ID, 'thumbnail'); 
	$nothumbnail = get_post_meta($post->ID, 'nothumbnail'); 
	
	// no thumbnail set
	if (empty($thumbnail) && !empty($nothumbnail)) {		
error_log('find_thumbnails '.$post->ID );
		// find an image from the post
		if (preg_match('/<img [^<]+src="(.*?)"/i',$post->post_content,$matches)) {
			$thumbnail = $matches[1];

			if (!empty($thumbnail)) {
				add_post_meta($post->ID, 'thumbnail', $thumbnail );			
			}
			else {
				add_post_meta($post->ID, 'nothumbnail', "true" );			
			}
		}
	}
}

add_action("loop_start", "find_thumbnails" );

?>
