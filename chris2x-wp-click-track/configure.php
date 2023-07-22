<?php

/**
 * Displays the click tracker options module
 *
 * @return  void
 */

wp_enqueue_script('jquery-ui-tabs');
//wp_enqueue_style('jquery-ui-tabs-wpct-css', WP_PLUGIN_URL.'/'.dirname(plugin_basename(__file__)).'/config.ui.all.css');

function wpct_options(){
	global $click_tracker_page_default, $wpdb;

	$plugin_data = get_plugin_data(wpct_path_info(__FILE__));
	$plugin_options = get_option('click_tracker');

	$default = $click_tracker_page_default;
	$form_updated = FALSE;
	$reset_clicks = FALSE;
	$reset_links = FALSE;

	//exit;
	$page = (isset($_REQUEST['page']) ? $_REQUEST['page'] : FALSE);

	if(isset($_POST['go']) && $_POST['go'] == 'yes'){
		
		$plugin_options['parse_the_content'] = (int)(isset($_POST['parse_the_content']) && $_POST['parse_the_content'] == '1' ? $_POST['parse_the_content'] : '0');
		$plugin_options['parse_archive'] = (int)(isset($_POST['parse_archive']) && $_POST['parse_archive'] == '1' ? $_POST['parse_archive'] : '0');
		$plugin_options['parse_blog_roll'] = (int)(isset($_POST['parse_blog_roll']) && $_POST['parse_blog_roll'] == '1' ? $_POST['parse_blog_roll'] : '0');
		$plugin_options['parse_comment_author_link'] = (int)(isset($_POST['parse_comment_author_link']) && $_POST['parse_comment_author_link'] == '1' ? $_POST['parse_comment_author_link'] : '0');
		$plugin_options['parse_the_excerpt'] = (int)(isset($_POST['parse_the_excerpt']) && $_POST['parse_the_excerpt'] == '1' ? $_POST['parse_the_excerpt'] : '0');
		$plugin_options['parse_the_meta'] = (int)(isset($_POST['parse_the_meta']) && $_POST['parse_the_meta'] == '1' ? $_POST['parse_the_meta'] : '0');
		$plugin_options['parse_comment_text'] = (int)(isset($_POST['parse_comment_text']) && $_POST['parse_comment_text'] == '1' ? $_POST['parse_comment_text'] : '0');
		$plugin_options['parse_next_prev'] = (int)(isset($_POST['parse_next_prev']) && $_POST['parse_next_prev'] == '1' ? $_POST['parse_next_prev'] : '0');
		$plugin_options['parse_the_tags'] = (int)(isset($_POST['parse_the_tags']) && $_POST['parse_the_tags'] == '1' ? $_POST['parse_the_tags'] : '0');
		$plugin_options['parse_the_category'] = (int)(isset($_POST['parse_the_category']) && $_POST['parse_the_category'] == '1' ? $_POST['parse_the_category'] : '0');

		$plugin_options['click_count'] = (int)(isset($_POST['click_count']) ? $_POST['click_count'] : $default);
		$plugin_options['link_list_count'] = (int)(isset($_POST['link_list_count']) ? $_POST['link_list_count'] : $default);
		$plugin_options['referrer_count'] = (int)(isset($_POST['referrer_count']) ? $_POST['referrer_count'] : $default);

		$plugin_options['sidebar_top_click_limit'] = (int)(isset($_POST['sidebar_top_click_limit']) ? $_POST['sidebar_top_click_limit'] : $default);
		$plugin_options['sidebar_todays_click_limit'] = (int)(isset($_POST['sidebar_todays_click_limit']) ? $_POST['sidebar_todays_click_limit'] : $default);

		$plugin_options['bypass_site_url'] = (isset($_POST['bypass_site_url']) ? $_POST['bypass_site_url'] : FALSE);
		$plugin_options['track_user_clicks'] = (int)(isset($_POST['track_user_clicks']) ? $_POST['track_user_clicks'] : '0');
		$plugin_options['track_internal_links'] = (int)(isset($_POST['track_internal_links']) ? $_POST['track_internal_links'] : '0');
		$plugin_options['exclude_ips'] = (isset($_POST['exclude_ips']) ? $_POST['exclude_ips'] : FALSE);

		update_option('click_tracker', $plugin_options);
		$plugin_options = get_option('click_tracker');
		$form_updated = TRUE;
		$message = __(sprintf('%s settings updated!',$plugin_data['Name']),'wp-click-track');
	}

	if(isset($_POST['reset_clicks']) && $_POST['reset_clicks'] == 'yes'){

		$sql = "TRUNCATE ".$wpdb->prefix . "tracking_clicks";
		$wpdb->query($sql);

		$sql = "UPDATE ".$wpdb->prefix . "tracking_links SET link_total_clicks = '0', link_unique_clicks = '0', last_modified = NOW()";
		$wpdb->query($sql);

		$form_updated = TRUE;
		$message = __('Clicks Reset!','wp-click-track');
	}

	if(isset($_POST['reset_links']) && $_POST['reset_links'] == 'yes'){

		$sql = "TRUNCATE ".$wpdb->prefix . "tracking_links";
		$wpdb->query($sql);

		$sql = "TRUNCATE ".$wpdb->prefix . "tracking_clicks";
		$wpdb->query($sql);

		$form_updated = TRUE;
		$message = __('Links Reset!','wp-click-track');
	}

	?>

	<form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>?page=<?php echo $_REQUEST['page'];?>">


	<div class="wrap" id="sm_div">
		


			<?php 
			if($form_updated && $message){
				wpct_admin_message( $message);
			}
			?>

		<div id="wpct-tabs" class="inside">
			<ul>
				<li><a href="#wpct-parse"><?php _e('Parse Settings', 'wp-click-track'); ?></a></li>
				<li><a href="#wpct-admin"><?php _e('Admin Limits', 'wp-click-track'); ?></a></li>
				<li><a href="#wpct-tracking"><?php _e('Tracking Settings', 'wp-click-track'); ?></a></li>
				<li><a href="#wpct-misc"><?php _e('Miscellaneous Settings', 'wp-click-track'); ?></a></li>
				<li><a href="#wpct-sidebar"><?php _e('Sidebar Limits', 'wp-click-track'); ?></a></li>
			</ul>
			<div id="wpct-parse">

				<?php _e(sprintf('You can set %s to automatically parse your posts and convert all links to click trackable links.',$plugin_data['Name']), 'wp-click-track'); ?>
				
				<p><?php _e('You can choose the individual areas you want to convert below:', 'wp-click-track'); ?></p>

				<table class="form-table">
				<tr valign="top">
				<th scope="row"><label for="parse_the_content"><?php _e('Post', 'wp-click-track'); ?> </label></th>
				<td><input name="parse_the_content" type="checkbox" id="parse_the_content" value="1"  <?php echo ($plugin_options['parse_the_content'] == '1' ? 'checked="checked"' : ''); ?> /> <label for="parse_the_content"><?php _e('Parse the actual content of a post.', 'wp-click-track'); ?></label></td>
				</tr>

				<tr valign="top">
				<th scope="row"><label for="parse_the_excerpt"><?php _e('Post Excerpt', 'wp-click-track'); ?></label></th>
				<td><input name="parse_the_excerpt" type="checkbox" id="parse_the_excerpt" value="1"  <?php echo ($plugin_options['parse_the_excerpt'] == '1' ? 'checked="checked"' : ''); ?> /> <label for="parse_the_excerpt"><?php _e('Parse the links used in the excerpt portion of posts', 'wp-click-track'); ?></label></td>
				</tr>

				<tr valign="top">
				<th scope="row"><label for="parse_the_meta"><?php _e('Post Meta', 'wp-click-track'); ?></label></th>
				<td><input name="parse_the_meta" type="checkbox" id="parse_the_meta" value="1"  <?php echo ($plugin_options['parse_the_meta'] == '1' ? 'checked="checked"' : ''); ?> /> <label for="parse_the_meta"><?php _e('Parse the links used in the meta portion of posts', 'wp-click-track'); ?></label></td>
				</tr>

				<tr valign="top">
				<th scope="row"><label for="parse_comment_text"><?php _e('Comment Body', 'wp-click-track'); ?></label></th>
				<td><input name="parse_comment_text" type="checkbox" id="parse_comment_text" value="1"  <?php echo ($plugin_options['parse_comment_text'] == '1' ? 'checked="checked"' : ''); ?> /><label for="parse_comment_text"> <?php _e('Parse the links used in post comments', 'wp-click-track'); ?></label></td>
				</tr>

				<tr valign="top">
				<th scope="row"><label for="parse_comment_author_link"><?php _e('Comment Author Link', 'wp-click-track'); ?></label></th>
				<td><input name="parse_comment_author_link" type="checkbox" id="parse_comment_author_link" value="1"  <?php echo ($plugin_options['parse_comment_author_link'] == '1' ? 'checked="checked"' : ''); ?> /> <label for="parse_comment_author_link"><?php _e('Parse the links used in the comment author name (in comments)', 'wp-click-track'); ?></label></td>
				</tr>

				<tr valign="top">
				<th scope="row"><label for="parse_archive"><?php _e('Archive Widget', 'wp-click-track'); ?></label></th>
				<td><input name="parse_archive" type="checkbox" id="parse_archive" value="1"  <?php echo ($plugin_options['parse_archive'] == '1' ? 'checked="checked"' : ''); ?> /> <label for="parse_archive"><?php _e('Parse the links displayed in the Archive Sidebar Widget', 'wp-click-track'); ?> </label></td>
				</tr>

				<tr valign="top">
				<th scope="row"><label for="parse_blog_roll"><?php _e('Blog Roll Widget', 'wp-click-track'); ?></label></th>
				<td><input name="parse_blog_roll" type="checkbox" id="parse_blog_roll" value="1"  <?php echo ($plugin_options['parse_blog_roll'] == '1' ? 'checked="checked"' : ''); ?> /> <label for="parse_blog_roll"><?php _e('Parse the links displayed in the Blog Roll Sidebar Widget', 'wp-click-track'); ?> </label></td>
				</tr>

				<tr valign="top">
				<th scope="row"><label for="parse_next_prev"><?php _e('Next / Prev', 'wp-click-track'); ?></label></th>
				<td><input name="parse_next_prev" type="checkbox" id="parse_next_prev" value="1"  <?php echo ($plugin_options['parse_next_prev'] == '1' ? 'checked="checked"' : ''); ?> /> <label for="parse_next_prev"><?php _e('Parse the Next and Previous links in the template (<code>previous_post_link()</code> and <code>next_post_link()</code> template functions)', 'wp-click-track'); ?></label></td>
				</tr>

				<tr valign="top">
				<th scope="row"><label for="parse_the_tags"><?php _e('Footer Tags', 'wp-click-track'); ?></label></th>
				<td><input name="parse_the_tags" type="checkbox" id="parse_the_tags" value="1"  <?php echo ($plugin_options['parse_the_tags'] == '1' ? 'checked="checked"' : ''); ?> /> <label for="parse_the_tags"><?php _e('Parse the tags links on posts and pages', 'wp-click-track'); ?></label></td>
				</tr>

				<tr valign="top">
				<th scope="row"><label for="parse_the_category"><?php _e('Category Links', 'wp-click-track'); ?></label></th>
				<td><input name="parse_the_category" type="checkbox" id="parse_the_category" value="1"  <?php echo ($plugin_options['parse_the_category'] == '1' ? 'checked="checked"' : ''); ?> /> <label for="parse_the_category"><?php _e('Parse the category links on posts and pages as well as sidebar widget', 'wp-click-track'); ?></label></td>
				</tr>			
				</table>			


			</div>
			<div id="wpct-admin" class="ui-tabs-hide">
			
				<p><?php _e(sprintf('Choose how many items you want displayed in the admin for %s. This REALLY helps when you have a lot of items to display.',$plugin_data['Name']), 'wp-click-track'); ?></p>

				<p><?php _e(sprintf('NOTE: Anything but a positive number will result in the default of %s being used.',$default), 'wp-click-track'); ?></p>

				<table class="form-table">
				<tr valign="top">
				<th scope="row"><label for="referrer_count"><?php _e('Referrer Count', 'wp-click-track'); ?></label></th>
				<td><input name="referrer_count" type="text" id="referrer_count" value="<?php echo $plugin_options['referrer_count']; ?>" class="small-text" /></td>
				</tr>

				<tr valign="top">
				<th scope="row"><label for="click_count"><?php _e('Click Count', 'wp-click-track'); ?></label></th>
				<td><input name="click_count" type="text" id="click_count" value="<?php echo $plugin_options['click_count']; ?>" class="small-text" /></td>
				</tr>

				<tr valign="top">
				<th scope="row"><label for="link_list_count"><?php _e('Link List Count', 'wp-click-track'); ?></label></th>
				<td><input name="link_list_count" type="text" id="link_list_count" value="<?php echo $plugin_options['link_list_count']; ?>" class="small-text" /></td>
				</tr>
				</table>	


			</div>
			<div id="wpct-tracking" class="ui-tabs-hide">
			
				<p><?php _e('The below settings only affect how the tracking system works.', 'wp-click-track'); ?></p>
				<table class="form-table">
				<tr valign="top">
				<th scope="row"><label for="track_user_clicks"><?php _e('User Clicks', 'wp-click-track'); ?></label></th>
				<td><input name="track_user_clicks" type="checkbox" id="track_user_clicks" value="1"  <?php echo ($plugin_options['track_user_clicks'] == '1' ? 'checked="checked"' : ''); ?> /> <label for="track_user_clicks"><?php _e('Track clicks of logged in members.', 'wp-click-track'); ?> </label></td>
				</tr>
				<tr valign="top">
				<th scope="row"><label for="track_internal_links"><?php _e('Internal Links', 'wp-click-track'); ?></label></th>
				<td><input name="track_internal_links" type="checkbox" id="track_internal_links" value="1"  <?php echo ($plugin_options['track_internal_links'] == '1' ? 'checked="checked"' : ''); ?> /> <label for="track_internal_links"><?php _e('Track links pointing to this domain.', 'wp-click-track'); ?> </label></td>
				</tr>
				<th scope="row"><label for="exclude_ips"><?php _e('Ignore IPs', 'wp-click-track'); ?></label></th>
				<td><textarea name="exclude_ips" id="exclude_ips" rows="8" cols="40"><?php echo $plugin_options['exclude_ips']; ?></textarea><br />
				<?php _e('Put 1 IP address per line. These IP addresses won\'t be tracked.', 'wp-click-track'); ?></td>
				</tr>
				</table>

			</div>
			<div id="wpct-misc" class="ui-tabs-hide">
			
				<p><?php _e('The below is useful to over-ride some of the settings for the tracker if your blog doesn\'t use the "default" setup for WordPress. If you require a special setting please let me know and I\'ll set this up ASAP.', 'wp-click-track'); ?></p>

				<table class="form-table">

				<tr valign="top">
				<th scope="row"><label for="bypass_site_url"><?php _e('Bypass Site URL', 'wp-click-track'); ?></label></th>
				<td><input name="bypass_site_url" type="text" id="bypass_site_url" value="<?php echo $plugin_options['bypass_site_url']; ?>" class="large-text" size="40"/>
				<br /><?php _e(sprintf('Example: <code>http://%s</code> &#8212; don&#8217;t forget the <code>http://</code>',$_SERVER['HTTP_HOST']), 'wp-click-track'); ?></td>
				</tr>
				</table>

			</div>
			<div id="wpct-sidebar" class="ui-tabs-hide">
			
				<p><?php _e(sprintf('Choose how many items you want displayed in the sidebars for %s. You\'ll still have to enable the sidebars under Appearance->Widget.',$plugin_data['Name']), 'wp-click-track'); ?></p>

				<p><?php _e(sprintf('NOTE: Anything but a positive number will result in the default of %s being used.',$default), 'wp-click-track'); ?></p>

				<table class="form-table">
				<tr valign="top">
				<th scope="row"><label for="sidebar_todays_click_limit"><?php _e('Todays Clicks', 'wp-click-track'); ?></label></th>
				<td><input name="sidebar_todays_click_limit" type="text" id="sidebar_todays_click_limit" value="<?php echo $plugin_options['sidebar_todays_click_limit']; ?>" class="small-text" /></td>
				</tr>

				<tr valign="top">
				<th scope="row"><label for="sidebar_top_click_limit"><?php _e('Top Links', 'wp-click-track'); ?></label></th>
				<td><input name="sidebar_top_click_limit" type="text" id="sidebar_top_click_limit" value="<?php echo $plugin_options['sidebar_top_click_limit']; ?>" class="small-text" /></td>
				</tr>
				</table>

			</div>
		</div>

		<script type="text/javascript">
		//<![CDATA[
		jQuery(document).ready( function($) {
			//jQuery UI 1.5.2 doesn�t expect tab ID�s at DIV, so we have to apply a hotfix instead
			var needs_jquery_hotfix = (($.ui.version === undefined) || !$.ui.version.match(/^(1\.[7-9]|[2-9]\.)/));
			$("#wpct-tabs"+(needs_jquery_hotfix ? ">ul" : "")).tabs({
				selected: 0
			}); 
			$('.wpct-sect-memory:last').show().removeClass('wpsh-sect-memory');
		});
		//]]>
		</script>


			<p class="submit">
				<input type="submit" name="Submit" class="button-primary" value="Save Changes" />
			</p>

			<input type="hidden" name="go" value="yes" />

		</form>

			<h3><?php _e('System Reset', 'wp-click-track'); ?></h3>
			<p><?php _e('Use these functions at your own risk; there is no way to recover the data if you change your mind later.', 'wp-click-track'); ?></p>
			<table width="200" ><tr><td>
				
				<form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>?page=<?php echo $_REQUEST['page'];?>">
				<input type="hidden" name="reset_clicks" value="yes" />
				<input type="submit" name="Submit" class="button-primary" value="<?php _e('Reset All Clicks', 'wp-click-track'); ?>" onclick="javascript: return confirm('<?php _e('Are you sure you want to reset all the clicks? \nThis deletes all click records for all links...', 'wp-click-track'); ?>')"/>
				</form>
			</td>
			<td>

				<form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>?page=<?php echo $_REQUEST['page'];?>">
				<input type="hidden" name="reset_links" value="yes" />
				<input type="submit" name="Submit" class="button-primary" value="<?php _e('Delete All Links', 'wp-click-track'); ?>" onclick="javascript: return confirm('<?php _e('Are you sure you want to delete all the links? \nThis deletes the clicks as well so you will lose everything...', 'wp-click-track'); ?>')"/>
				</form>
			</td>
			</tr>
			</table>

	</div>

	<?php
}
?>