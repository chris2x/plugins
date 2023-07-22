<?php

//--- Initial setup
function wps_init () {	
	if (!is_admin()) {
		// Stuff changed in WP 2.8
		if (function_exists('set_transient')) {
			set_transient('rewrite_rules', "");
			update_option('rewrite_rules', "");
		} else {
			update_option('rewrite_rules', "");
		}
    
	  load_plugin_textdomain( 'wp-subdomains-revisited', false, WPS_BASE.'/languages/' );
	}
}


//--- Check if we need to do any page redirection
function wps_redirect () {
	global $wp_query, $wps_this_subdomain, $wps_subdomains;

	$redirect = false;

	$wp_url = parse_url( get_bloginfo('url') );
	$url_request_uri = $_SERVER['HTTP_HOST'];
	
	if (!$wps_this_subdomain && $wp_url['host'] != $url_request_uri) {
		$redirect = get_bloginfo('url');
	}

	// Check if Redirecting is turned on
	if (get_option(WPS_OPT_REDIRECTOLD) != "") {
		
		if (!$wps_this_subdomain) {
			// Check if it's a category
			if ($wp_query->is_category) {
				$catID = $wp_query->query_vars['cat'];
				
				if ($subdomain = $wps_subdomains->getCategorySubdomain($catID)) {
					$redirect = $wps_subdomains->cats[$subdomain]->changeCategoryLink($catID, '');
				}
			}
			
			// Check if it's a page
			if ($wp_query->is_page) {
				$pageID = $wp_query->post->ID;
				
				// Check if it's a subdomain page or a tied page
				if ($subdomain = $wps_subdomains->getPageSubdomain($pageID)) {
					$redirect = $wps_subdomains->pages[$subdomain]->changePageLink($pageID, '');
				} else if ($catID = $wps_subdomains->findTiedPage($pageID)) {
					$redirect = $wps_subdomains->cats[$catID]->changeCategoryLink($catID).$wp_query->query['pagename'];
				}
			}
			
		}
				
	}

	// Check if Canonical Redirect is turned on
	if ($wp_query->is_single && get_option(WPS_OPT_REDIRECT_CANONICAL) != "") {
		    $canonical = get_permalink($wp_query->post->ID);
		    $uri = 'http' . (empty($_SERVER["HTTPS"]) ? '' : ($_SERVER["HTTPS"] == "on") ? "s" : "") . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		    $request = parse_url($uri);
		    $permalink = parse_url($canonical);
		    $redirect = ( $request['scheme'] != $permalink['scheme'] || $request['host'] != $permalink['host'] ) ? str_replace($request['host'], $permalink['host'], $uri) : false;
	}
	
	// If a redirect is found then do it
	if ($redirect) {
			wp_redirect($redirect, 301);
			exit();
	}
}


//--- Save Category settings
function wps_edit_category($cat_ID) {
	global $wpdb;
	
	$table_name = $wpdb->prefix . "category_subdomains";
	
	$is_subdomain = ('true' == $_REQUEST['csd_include']) ? '1' : '0';
	
	$not_subdomain = ('true' == $_REQUEST['csd_exclude']) ? '1' : '0';
	
	$cat_theme = addslashes($_REQUEST['csd_cat_theme']);
	if ($cat_theme == "(none)") {
		$cat_theme = "";
	}
	
	$link_title = addslashes(trim($_REQUEST['csd_link_title']));
	
	$filter_pages = ('true' == $_REQUEST['csd_filterpages']) ? '1' : '0';
	
	if ($wpdb->get_var("SELECT cat_ID FROM {$table_name} WHERE cat_ID = '{$cat_ID}'")) {
		$querystr = "UPDATE {$table_name} SET is_subdomain={$is_subdomain}, not_subdomain={$not_subdomain}, cat_theme='{$cat_theme}', filter_pages={$filter_pages}, cat_link_title='{$link_title}' WHERE cat_ID = '{$cat_ID}'"; 
	} else {
		$querystr = "INSERT INTO {$table_name} (cat_ID, is_subdomain, not_subdomain, cat_theme, filter_pages, cat_link_title) VALUES ('{$cat_ID}', '{$is_subdomain}', '{$not_subdomain}', '{$cat_theme}', '{$filter_pages}', '{$link_title}')";
	}
	
	$wpdb->query($querystr);
}

function wps_action_parse_query($q) {
	global $wps_this_subdomain, $wps_archive_subdomains, $post;

	//--- If user wants root of subdomain to be an index
	if (get_option( WPS_OPT_SUBISINDEX ) != '') {
		// Check if we're on the root of a subdomain.
		// If so then tell WP_Query it's index not archive
		if ($wps_this_subdomain && $wps_this_subdomain->archive && ($_SERVER["REQUEST_URI"] == '/')) {		
			$q->is_archive = false;
			if ($wps_this_subdomain->type == WPS_TYPE_CAT) {
			  $q->set('cat', $wps_this_subdomain->id);
			  $q->is_category= true;
			  $q->is_home = false;
			  $q->set('tax_query', 		array(
          'taxonomy' => 'category',
          'field'    => 'slug',
          'terms'    => array( $wps_this_subdomain->slug ),
        ));
			}
			if ($wps_this_subdomain->type == WPS_TYPE_AUTHOR) $query->set('author', $wps_this_subdomain->id);
		}
		if ($wps_this_subdomain && ($wps_this_subdomain->type == WPS_TYPE_PAGE) && ($_SERVER["REQUEST_URI"] == '/')) {
			set_query_var('page_id',$wps_this_subdomain->id);
			$query->is_home = false;	
			$query->is_page = true;
		}
	}
}

function wps_action_parse_qp($wp) {
	global $wps_this_subdomain, $wps_archive_subdomains, $post;

	//--- If user wants root of subdomain to be an index
	if (get_option( WPS_OPT_SUBISINDEX ) != '') {
		// Check if we're on the root of a subdomain.
		// If so then tell WP_Query it's index not archive
		if ($wps_this_subdomain && $wps_this_subdomain->archive && ($_SERVER["REQUEST_URI"] == '/')) {		
      write_log("on root" );
			$wp->set('is_archive', false);
			if ($wps_this_subdomain->type == WPS_TYPE_CAT) {
        write_log("in category" );
		  write_log($wps_this_subdomain->slug);
			  $wp->set('cat', $wps_this_subdomain->id);
			  $wp->set('is_category', true);
			  $wp->set('is_home', false);
			  $wp->set('tax_query', 		array(
          'taxonomy' => 'category',
          'field'    => 'slug',
          'terms'    => array( $wps_this_subdomain->slug ),
        ));
			}
			if ($wps_this_subdomain->type == WPS_TYPE_AUTHOR) $query->set('author', $wps_this_subdomain->id);
		}
		if ($wps_this_subdomain && ($wps_this_subdomain->type == WPS_TYPE_PAGE) && ($_SERVER["REQUEST_URI"] == '/')) {
			$wp->page_id = $wps_this_subdomain->id;
			$wp->is_home = false;	
			$wp->is_page = true;
		}
	}
}

function wps_action_page_meta () {
	add_meta_box('subdomainsdiv', __('WordPress Subdomains'), 'wps_page_meta_box', 'page', 'normal', 'core');
}


function wps_page_meta_box($post) {
  global $wpdb, $wps_page_metakey_subdomain, $wps_page_metakey_tie, $wps_page_metakey_theme;

	wp_nonce_field( plugin_basename( __FILE__ ), 'wps_noncename' );

	$post_meta[0] = get_post_meta($post->ID, $wps_page_metakey_subdomain);
	$post_meta[1] = get_post_meta($post->ID, $wps_page_metakey_tie);
	$post_meta[2] = get_post_meta($post->ID, $wps_page_metakey_theme);
?>
<h4>
<input type="checkbox" value="true" id="<?php echo $wps_page_metakey_subdomain;?>" name="<?php echo $wps_page_metakey_subdomain;?>" <?php checked( $post_meta[0][0], 'true' ); ?> />
<label for="<?php echo $wps_page_metakey_subdomain;?>"><?php _e('Make the Page as Subdomain', 'wps'); ?></label>
</h4>
<p> <?php _e('Tied to', 'wps'); ?> &nbsp;
<?php wp_dropdown_categories(array('hide_empty' => 0, 'name' => $wps_page_metakey_tie, 'orderby' => 'name', 'selected' => $post_meta[1][0], 'hierarchical' => true, 'show_option_none' => __('Select Category'))); ?>
</p>
<p> <?php _e('Themes', 'wps'); ?> &nbsp;
<?php
    $table_name = $wpdb->prefix . "category_subdomains";
		
		$themes = wp_get_themes();
		$theme_options = '<option value="">' . __('Default') . '</option>';
		foreach ( $themes as $theme ) {
		    $selected = ($post_meta[2][0] == $theme->Template) ? ' selected="selected"' : '';
		    $theme_options .= '<option value="' . $theme->Template . '"'.$selected.'>' . $theme->Name . '</option>';
		}
?><select name="<?php echo $wps_page_metakey_theme;?>"><?php echo $theme_options; ?></select>
</p>
<p>
    <?php _e('Go to', 'wps'); ?> &nbsp; <a href="admin.php?page=wps"><?php _e('Subdomains Settings', 'wps'); ?> &raquo;</a>
</p>
<?php 
}

/* When the post is saved, saves our custom data */
function wps_action_save_postdata( $post_id ) {
  global $wps_page_metakey_subdomain, $wps_page_metakey_tie, $wps_page_metakey_theme;

  // verify if this is an auto save routine. 
  // If it is our form has not been submitted, so we dont want to do anything
  if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
      return;

  // verify this came from the our screen and with proper authorization,
  // because save_post can be triggered at other times

  if ( !wp_verify_nonce( $_POST['wps_noncename'], plugin_basename( __FILE__ ) ) )
      return;

  
  // Check permissions
  if ( 'page' == $_POST['post_type'] ) 
  {
    if ( !current_user_can( 'edit_page', $post_id ) )
        return;
  }
  else
  {
    if ( !current_user_can( 'edit_post', $post_id ) )
        return;
  }

  $metavalue['subdomain'] = ($_POST[$wps_page_metakey_subdomain]) ? 'true' : '';
  $metavalue['tietocat'] = ($_POST[$wps_page_metakey_tie] > 0) ? $_POST[$wps_page_metakey_tie] : '';
  $metavalue['theme'] = ($_POST[$wps_page_metakey_theme]) ? $_POST[$wps_page_metakey_theme] : '';

  // OK, we're authenticated: we need to find and save the data
  update_post_meta($post_id, $wps_page_metakey_subdomain, $metavalue['subdomain']);
  $metavalue['tietocat'] ? update_post_meta($post_id, $wps_page_metakey_tie, $metavalue['tietocat']) : delete_post_meta($post_id, $wps_page_metakey_tie);
  $metavalue['theme'] ? update_post_meta($post_id, $wps_page_metakey_theme, $metavalue['theme']) : delete_post_meta($post_id, $wps_page_metakey_theme);
}
?>
