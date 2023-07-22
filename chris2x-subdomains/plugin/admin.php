<?php

function wps_page_rows( $pages ) {
	global $wps_page_metakey_subdomain, $wps_page_metakey_theme;
	
	$count = 0;
	$rows = '';
	
	foreach ( $pages as $page ) {
		$count ++;
		if ( $count % 2 ) {
			$rows .= '<tr class="alternate">';
		} else {
			$rows .= '<tr>';
		}
		
		$rows .= '<td><b><a href="post.php?action=edit&amp;post=' . $page ['ID'] . '">' . $page ['title'] . '</a></b></td>';
		$rows .= '<td>' . ($page [$wps_page_metakey_subdomain] ? __('Yes') : __('No')) . '</td>';
		$rows .= '<td>' . ($page [$wps_page_metakey_theme] ? $page [$wps_page_metakey_theme] : 'None') . '</td>';
		$rows .= '<td>' . ($page ['category'] ? $page ['category'] : __('None')) . '</td>';
		$rows .= '</tr>';
	}
	
	return $rows;
}

function wps_category_rows( $cats, $subdomains = 0 ) {
	
	$count = 0;
	$rows = '';
	
	if ( ! empty ( $cats ) ) {
		foreach ( $cats as $cat ) {
			$count ++;
			if ( $count % 2 ) {
				$rows .= '<tr class="alternate">';
			} else {
				$rows .= '<tr>';
			}
			
			$rows .= '<td><b><a href="edit-tags.php?action=edit&taxonomy=category&post_type=post&tag_ID=' . $cat ['ID'] . '">' . $cat ['name'] . '</a></b></td>';
			$rows .= '<td>' . $cat ['slug'] . '</td>';
			if ( $subdomains ) {
				$rows .= '<td>' . ($cat ['theme'] ? $cat ['theme'] : 'None') . '</td>';
				$rows .= '<td>' . ($cat ['filter_pages'] ? 'On' : 'Off') . '</td>';
			}
			$rows .= '</tr>';
		}
	}
	
	return $rows;
}

function wps_settings_settings() {
	global $wps_page_metakey_theme, $wps_page_metakey_tie;

    wps_admin_tabs('settings');
?>
	
<form method="post" action="options.php">
<?php
	  //verifying and saving
    if ( $_POST && wp_verify_nonce( $_POST['wps_noncename'], 'wps_settings' ) ) {      
        do_settings_sections( 'wps-settings-group' );
    }
    
    wp_nonce_field( 'wps_settings', 'wps_noncename' );
    settings_fields('wps-settings-group');
	?>
<script type="text/javascript" >
jQuery(document).ready(function(){
    jQuery('ul.wps li a').click(function(){
        if (!jQuery(this).hasClass('more')) {
            jQuery(this).parent().toggleClass('active').find('input')
                        .attr('value', jQuery(this).parent().hasClass('active') ? '<?php echo WPS_CHK_ON?>' : '');
        }
    });
});
</script>
<div class="trd-forth"><div class="center">
    <label class="large_text"><?php _e ( 'Main Domain', 'wps' ) ?><br />
    <input type="text" name="wps_domain" value="<?php echo get_option ( WPS_OPT_DOMAIN )?>" placeholder="ex.: <?php echo $_SERVER['HTTP_HOST'] ?>" />
    </label>
    <ul class="wps">
        <li <?php echo get_option ( WPS_OPT_SUBALL ) ? 'class="active "' : ''?>>
            <a href="javascript:;">
                <img src="<?php echo WPS_URL.'views/Folders.png'?>" alt="" />
                <?php _e ( 'Category Subdomains', 'wps' )?>
            </a>
            <input type="hidden" name="wps_subdomainall" value="<?php echo get_option ( WPS_OPT_SUBALL ) ? WPS_CHK_ON : ''?>" />
        </li>
        <li <?php echo get_option ( WPS_OPT_SUBPAGES ) ? 'class="active "' : ''?>>
            <a href="javascript:;">
                <img src="<?php echo WPS_URL.'views/Pages.png'?>" alt="" />
                <?php _e ( 'Page Subdomains', 'wps' )?>
            </a>
            <input type="hidden" name="wps_subpages" value="<?php echo get_option ( WPS_OPT_SUBPAGES ) ? WPS_CHK_ON : ''?>" />
        </li>
        <li <?php echo get_option ( WPS_OPT_SUBAUTHORS ) ? 'class="active "' : ''?>>
            <a href="javascript:;">
                <img src="<?php echo WPS_URL.'views/Authors.png'?>" alt="" />
                <?php _e ( 'Author Subdomains', 'wps' )?>
            </a>
            <input type="hidden" name="wps_subauthors" value="<?php echo get_option ( WPS_OPT_SUBAUTHORS ) ? WPS_CHK_ON : ''?>" />
        </li>
        <!--
        <li>
            <a href="javascript:;" class="more">
                <img src="<?php echo WPS_URL.'views/more.png'?>" alt="" />
                ?<br /><em><?php _e ( 'More...', 'wps' )?></em>
            </a>
        </li>
        -->
    </ul>
</div>
<h3><?php _e( 'General Settings', 'wps')?></h3>
<table class="form-table">
	<tr valign="top">
		<th scope="row"><?php _e ( 'Custom Subdomain Themes', 'wps' )?></th>
		<td><input type="checkbox" name="wps_themes"
			value="<?php
	echo WPS_CHK_ON?>"
			<?php
	checked ( WPS_CHK_ON, get_option ( WPS_OPT_THEMES ) );
	?> /> <span class="description"><?php _e( 'Activate the subdomain theme system. To set different themes for each category', 'wps')?>, <a href="admin.php?page=wps_categories"><?php _e( 'Edit them', 'wps')?></a>.</span></td>
	</tr>

	<tr valign="top">
		<th scope="row"><?php
	_e ( 'Redirect Old URLs', 'wps' )?></th>
		<td><input type="checkbox" name="wps_redirectold"
			value="<?php
	echo WPS_CHK_ON?>"
			<?php
	checked ( WPS_CHK_ON, get_option ( WPS_OPT_REDIRECTOLD ) );
	?> /> <span class="description"><?php _e( 'If someone comes to the site on an old category or page url it redirects them to the new Subdomain one.', 'wps')?></span>
	</tr>

	<tr valign="top">
		<th scope="row"><?php
	_e ( 'Redirect Posts Canonical URL', 'wps' )?></th>
		<td><input type="checkbox" name="wps_redirectcanonical"
			value="<?php
	echo WPS_CHK_ON?>"
			<?php
	checked ( WPS_CHK_ON, get_option ( WPS_OPT_REDIRECT_CANONICAL ) );
	?> /> <span class="description"><?php _e('This will set posts to only showed on single category/pages based on canonical and will redirect if accessed from another/root subdomain.', 'wps') ?><br />
		<b><?php _e('Note', 'wps') ?>:</b> <?php _e('This is best for SEO as will avoid duplicate content on subdomains!', 'wps') ?></span>
	</tr>
	
	<tr valign="top">
		<th scope="row"><?php
	_e ( 'Keep Pages on Subdomain', 'wps' )?></th>
		<td><input type="checkbox" name="wps_keeppagesub"
			value="<?php
	echo WPS_CHK_ON?>"
			<?php
	checked ( WPS_CHK_ON, get_option ( WPS_OPT_KEEPPAGESUB ) );
	?> /> <span class="description"><?php _e('Activate this to have links to your normal pages, not Subdomain or Category Tied, remain on the subdomain being viewed.', 'wps') ?><br />
		<b><?php _e('Note', 'wps') ?>:</b> <?php _e('This could be bad for SEO as some search engines will see this as duplicate pages.', 'wps') ?></span></td>
	</tr>
	
	<tr valign="top">
		<th scope="row"><?php
	_e ( 'Subdomain Roots as Indexes', 'wps' )?></th>
		<td><input type="checkbox" name="wps_subisindex"
			value="<?php
	echo WPS_CHK_ON?>"
			<?php
	checked ( WPS_CHK_ON, get_option ( WPS_OPT_SUBISINDEX ) );
	?> /> <span class="description"><?php _e('The main page of Category and Author Subdomains will be treated by Wordpress as an Index rather than an archive.', 'wps') ?><br />
		<?php _e('The difference between how Index and Archive are displayed is set by your theme.', 'wps') ?></span>
		</td>
	</tr>
</table>

    <p>&nbsp;</p>
    
<h3><?php _e ( 'Content Filters', 'wps' )?></h3>
<p><?php _e('Configure filters to filter out content not belonging to the Subdomain you\'re on.', 'wps') ?></p>
<table class="form-table">

	<tr valign="top">
		<th scope="row"><?php _e ( 'Archives', 'wps' )?></th>
		<td><input type="checkbox" name="wps_arcfilter"
			value="<?php echo WPS_CHK_ON?>"
			<?php
	checked ( WPS_CHK_ON, get_option ( WPS_OPT_ARCFILTER ) );
	?> /> <span class="description"><?php _e('Change Archives to just show archive of the Category or Author Subdomain you\'re on.', 'wps' )?></span></td>
	</tr>
	
	<tr valign="top">
		<th scope="row"><?php _e ( 'Pages', 'wps' )?></th>
		<td><input type="checkbox" name="wps_pagefilter"
			value="<?php echo WPS_CHK_ON?>"
			<?php
	checked ( WPS_CHK_ON, get_option ( WPS_OPT_PAGEFILTER ) );
	?> /> <span class="description"><?php _e('Activate the Page filtering system. Use this to be able tie pages to categories.', 'wps' )?></td>
	</tr>
	<!--
	<tr valign="top">
		<th scope="row"><?php _e ( 'Tags', 'wps' )?></th>
		<td><input type="checkbox" name="wps_tagfilter"
			value="<?php echo WPS_CHK_ON?>"
			<?php
	checked ( WPS_CHK_ON, get_option ( WPS_OPT_TAGFILTER ) );
	?> /> <span class="description">Activate the Tag filtering system. Viewing Tags will show only the posts that belong to the subdomain you are on.</span></td>
	</tr>
	-->
</table>

<input type="hidden" name="action" value="update" /> <input
	type="hidden" name="page_options"
	value="wps_domain,wps_disabled,wps_subdomainall,wps_themes,wps_pagefilter,wps_arcfilter,wps_nocatbase,wps_redirectold,wps_redirectcanonical,wps_subpages,wps_subauthors,wps_keeppagesub,wps_subisindex, wps_tagfilter" />

<p class="submit"><input type="submit" name="Submit" class="button-primary" value="<?php _e ( 'Save Changes', 'wps' )?>" /></p>

</form>
</div>
<?php
    wps_admin_donate();
}

function wps_settings_categories() {
	global $wpdb, $wps_subdomains;
	
	$categories = array ();
	
	// Build Cat Subdomain array (link, name, slug, theme, tied)
	foreach ( $wps_subdomains->cats as $catID => $cat ) {
		$categories ['subdomains'] [$catID] ['ID'] = $catID;
		$categories ['subdomains'] [$catID] ['name'] = $cat->name;
		$categories ['subdomains'] [$catID] ['slug'] = $cat->slug;
		$categories ['subdomains'] [$catID] ['theme'] = $cat->theme;
		$categories ['subdomains'] [$catID] ['filter_pages'] = $cat->filter_pages;
	}
	
	$cats_nosub = wps_getNonSubCats();
	
	if ( ! empty ( $cats_nosub ) ) {
		$tmp_cats = get_categories ( 'hide_empty=0&include=' . implode ( ',', $cats_nosub ) );
		
		// Build Excluded Cat array (link, name, slug, theme, tied)
		foreach ( $tmp_cats as $cat ) {
			$categories ['non_subdomains'] [$cat->term_id] ['ID'] = $cat->term_id;
			$categories ['non_subdomains'] [$cat->term_id] ['name'] = $cat->name;
			$categories ['non_subdomains'] [$cat->term_id] ['slug'] = $cat->slug;
		}
	} else {
		$categories ['non_subdomains'] = array ();
	}
	
	// Determine if MakeAllSubdomain is set.
	$suball = (get_option ( WPS_OPT_SUBALL ) != "");
	
	wps_admin_tabs('categories');
?>

<h3><?php _e('Active Subdomains', 'wps'); ?></h3>
<?php	if ( $suball ) { ?>
	<p class="description"><b><?php _e( 'Make all Subdomains', 'wps' )?></b> <?php _e( 'is turned <b>ON</b> so all main categories are turned into subdomains unless specifically excluded.', 'wps' )?>	
  </p>
	<?php }	?>
	<p class="description"><b><?php _e( 'Note', 'wps' )?>:</b> <?php _e( 'Only works for main categories', 'wps' )?>
  </p>
<table class="widefat">
	<thead>
		<tr>
			<th scope="col"><?php _e('Category', 'wps'); ?></th>
			<th scope="col"><?php _e('Subdomain', 'wps'); ?></th>
			<th scope="col"><?php _e('Themes', 'wps'); ?></th>
			<th scope="col"><?php _e('Pages Filter', 'wps'); ?></th>
		</tr>
	</thead>
	<tbody>
<?php
	print ( wps_category_rows ( $categories ['subdomains'], 1 ) );
	?>
	</tbody>
</table>
<p>&nbsp;</p>
<div class="trd-forth">
<h3><?php _e('Inactive Subdomains', 'wps'); ?></h3>
<?php	if ( $suball ) { ?>
	<p class="description"><b><?php _e( 'Make all Subdomains', 'wps' )?></b> <?php _e( 'is turned <b>ON</b> so all main categories are turned into subdomains unless specifically excluded as below.', 'wps')?>	
  </p>
	<?php }	?>
	<p class="description"><b><?php _e( 'Note', 'wps' )?>:</b> <?php _e( 'Subdomains not working for child categories', 'wps' )?>
<table class="widefat">
	<thead>
		<tr>
			<th scope="col"><?php _e('Category', 'wps'); ?></th>
			<th scope="col"><?php _e('Subdomain', 'wps'); ?></th>
		</tr>
	</thead>
	<tbody>
<?php
	print ( wps_category_rows ( $categories ['non_subdomains'], 0 ) );
	?>
	</tbody>
</table>
</div>
<?php
    wps_admin_donate();
}

function wps_settings_pages() {
	global $wpdb, $wps_page_metakey_theme, $wps_page_metakey_subdomain, $wps_page_metakey_tie;
	
	$meta_keys = array ( $wps_page_metakey_theme, $wps_page_metakey_subdomain, $wps_page_metakey_tie );
	
	$sql = "SELECT Post_ID, meta_key, meta_value FROM {$wpdb->postmeta} WHERE meta_key in ('" . implode ( "','", $meta_keys ) . "') and meta_value != ''";
	$metapages = $wpdb->get_results ( $sql );
	
	$pages_root = array ();
	$pages_child = array ();
	$pages = array ();
	
	if ( ! empty ( $metapages ) ) {
		foreach ( $metapages as $metapage ) {
			$pages [$metapage->Post_ID] [$metapage->meta_key] = $metapage->meta_value;
		}
	}
	
	if ( ! empty ( $pages ) ) {
		foreach ( $pages as $pageid => $page ) {
			$pageobj = get_post ( $pageid );
			
			$page ['ID'] = $pageid;
			$page ['title'] = $pageobj->post_title;
			
			if ( $page [$wps_page_metakey_tie] ) {
				$page_cat = get_category ( $page [$wps_page_metakey_tie] );
				$page ['category'] = $page_cat->cat_name;
			}
			
			if ( $pageobj->post_parent == 0 ) {
				$pages_root [$pageid] = $page;
			} else {
				$pages_child [$pageid] = $page;
			}
		
		}
	}
	
	wps_admin_tabs('pages');
?>	
<div class="trd-forth">
<h3><?php _e('Active Subdomains', 'wps'); ?></h3>
<p class="description"><?php _e('A list of main pages that are configured to use WP Subdomains features.', 'wps') ?></p>
<table class="widefat">
	<thead>
		<tr>
			<th scope="col"><?php _e('Page', 'wps'); ?></th>
			<th scope="col"><?php _e('Subdomain', 'wps'); ?></th>
			<th scope="col"><?php _e('Custom Theme', 'wps'); ?></th>
			<th scope="col"><?php _e('Category', 'wps'); ?></th>
		</tr>
	</thead>
	<tbody>
<?php
	print ( wps_page_rows ( $pages_root ) );
	?>
	</tbody>
</table>

<p>&nbsp;</p>

<h3><?php _e('Inactive Subdomains', 'wps'); ?></h3>
<p class="description"><?php _e('A list of child pages that are configured to WP Subdomains features.', 'wps') ?><br />
<b><?php _e('Note', 'wps') ?>:</b> <?php _e('Subdomain and Theme Settings will not function for child pages.', 'wps') ?></p>
<table class="widefat">
	<thead>
		<tr>
			<th scope="col"><?php _e('Page', 'wps'); ?></th>
			<th scope="col"><?php _e('Subdomain', 'wps'); ?></th>
			<th scope="col"><?php _e('Custom Theme', 'wps'); ?></th>
			<th scope="col"><?php _e('Category', 'wps'); ?></th>
		</tr>
	</thead>
	<tbody>
<?php
	print ( wps_page_rows ( $pages_child ) );
	?>
	</tbody>
</table>
</div>
<?php
    wps_admin_donate();
}

function wps_settings_welcome() {
    wps_admin_tabs();
	
?>
<form method="post" action="options.php">
		<h3 class="large_text"><?php _e ( 'You are using', 'wps' )?> Wordpress Subdomains (Revisited) <?php _e ( 'Version', 'wps' )?> <?php echo WPS_VERSION;?></h3>
<?php
	  //verifying and saving
    if ( $_POST && wp_verify_nonce( $_POST['wps_noncename'], 'wps_settings' ) ) {      
        do_settings_sections( 'wps_disabled' );
    }
    
    wp_nonce_field( 'wps_settings', 'wps_noncename' );
    settings_fields('wps_disabled');
	?>
		<h3 style="float:left; margin-right: 150px;color: #c00"><?php _e ( 'DISABLE PLUGIN', 'wps' )?></h3>
		<input type="hidden" name="wps_disabled" value="<?php echo get_option(WPS_OPT_DISABLED) ? '' : 'on'; ?>" />
	  <h4><input type="submit" name="Submit" class="button-primary" value="<?php get_option(WPS_OPT_DISABLED) ? _e('ENABLE', 'wps') : _e('DISABLE', 'wps');?>" /></h4>
	<span class="clear description"><?php _e ( 'This will disable the plugin\'s functionality whilst allowing you to continue configuring it.', 'wps' )?></span>
</form>

<p>&nbsp;</p>

<p><hr /></p>

<div class="trd-forth">
<h3><?php _e('History', 'wps')?></h3>
<p><?php _e('This version is an updated modification of "WP Subdomains" by <a href="http://casualgenius.com">Alex Stansfield</a> that stop supporting until version 0.6.9 that not worked for Wordpress 3.3 when I need it for a client.', 'wps')?><br />
<?php _e('The original version is at Wordpress <a href="http://wordpress.org/extend/plugins/wordpress-subdomains/">plugin\'s page</a>. I change the revision numbers to easier maintance and will try continue to support this plugin as much as I can.', 'wps')?><br />
<?php _e('Donation will be much appreciated to me or original author.', 'wps')?></p>
<p><?php _e('WP Subdomains originally based on the <a href="http://www.biggnuts.com/wordpress-subdomains-plugin/">Subster Rejunevation</a> wordpress plugin by <a href="http://www.biggnuts.com/">Dax Herrera</a>.', 'wps')?><br />
<?php _e('Original version started as a few bug fixes but as I found more and more things to add I realised only a rewrite would enable me to make the changes I wanted for my site.', 'wps')?><br />
<?php _e('Please <a href="mailto:lontongcorp@gmail.com">contact me</a> if you want contribute or need support.', 'wps')?></p>

<p>&nbsp;</p>

<h3><?php _e('Credits', 'wps')?></h3>
<ul>
<li><a href="http://www.lontongcorp.com">Erick Tampubolon</a> of <a href="http://www.igits.co.id">IGITS</a> (<?php _e('Author', 'wps')?>)</li>
<li><a href="http://profiles.wordpress.org/selnomeria">selnomeria</a> (<?php _e('Commiter', 'wps')?>)</li>
<li><a	href="http://demp.se/y/2008/04/11/category-subdomains-plugin-for-wordpress-25/">Adam Dempsey</a> (<?php _e('Contributor', 'wps')?>)</li>
<li><a href="http://blog.youontop.com/wordpress/wordpress-category-as-subdomain-plugin-41.html">Gilad Gafni</a> (<?php _e('Contributor', 'wps')?>)</li>
<li><a href="mailto:alex@casualgenius.com">Alex Stansfield</a> of <a href="http://casualgenius.com">Casual Genius</a> (<?php _e('Original Author', 'wps')?>)</li>
<li>I forgot some of icon designer, please remind me</li>
</ul>

<p>&nbsp;</p>

<h3><?php _e('Copyright', 'wps')?> &amp; <?php _e('Disclaimer', 'wps')?></h3>
<p><?php _e('Use of this plugin will be at your own risk. No guarantees or warranties are made, direct or implied. The creators cannot and will not be liable or held accountable for damages, direct or consequential.', 'wps')?><br />
<?php _e('Fully comply to the GPLv2+ and MIT License, in works as is as Wordpress License. By using this application it implies agreement to these conditions.', 'wps')?></p>
<p><?php _e('Thank you for using this plugin', 'wps') ?>.</p>
</div>
<?php
    wps_admin_donate();
}

function wps_add_options() {
    
    add_menu_page ( 'WP Subdomains', 'WP Subdomains', 7, 'wps', 'wps_settings_welcome', WPS_URL . 'icon.png' );
    
    foreach( wps_admin_tabs(null, false) as $tab => $name ){
        if ($tab) add_submenu_page ( 'wps', $name, __($name), 7, 'wps_'.$tab, 'wps_settings_'.$tab );
    }
	  
	  add_filter( 'plugin_action_links', 'wps_settings_links',10,2);
	  add_filter('contextual_help', 'wps_settings_help', 10, 3);
}

function wps_settings_links( $links, $file ) {
	if ( $file == WPS_BASE) {
	   $link = '<a href="admin.php?page=wps">' . __('Settings') . '</a>';
	   array_unshift($links, $link);
  }
	return $links;
}

function wps_admin_init(){
	if (function_exists('register_setting')){
	    
		register_setting( 'wps_disabled', 'wps_disabled', 'wps_filter_on_off');
		
		// this whitelists form elements on the options page
		register_setting( 'wps-settings-group', 'wps_domain');
		register_setting( 'wps-settings-group', 'wps_subdomainall', 'wps_filter_on_off');
		register_setting( 'wps-settings-group', 'wps_subpages', 'wps_filter_on_off');
		register_setting( 'wps-settings-group', 'wps_subauthors', 'wps_filter_on_off');
		register_setting( 'wps-settings-group', 'wps_themes', 'wps_filter_on_off');
		register_setting( 'wps-settings-group', 'wps_redirectold', 'wps_filter_on_off');
		register_setting( 'wps-settings-group', 'wps_redirectcanonical', 'wps_filter_on_off');
		register_setting( 'wps-settings-group', 'wps_keeppagesub', 'wps_filter_on_off');
		register_setting( 'wps-settings-group', 'wps_subisindex', 'wps_filter_on_off');
		register_setting( 'wps-settings-group', 'wps_arcfilter', 'wps_filter_on_off');
		register_setting( 'wps-settings-group', 'wps_pagefilter', 'wps_filter_on_off');
		//register_setting( 'wps-settings-group', 'wps_tagfilter', 'wps_filter_on_off');
	}
    
	  load_plugin_textdomain( 'wp-subdomains-revisited', false, WPS_BASE.'/languages/' );
}

function wps_filter_on_off($data){
	if ($data){
		return WPS_CHK_ON;
	}
	return '';
}

function wps_edit_taxonomy( $tag ) {
    global $wpdb, $wps_subdomains;
    $table_name = $wpdb->prefix . "category_subdomains";
    $tagID = $tag->term_id;
    $cat_meta = get_option( "taxonomy_$tagID");
	
		$csd_cat_options = $wpdb->get_row ( "SELECT * FROM {$table_name} WHERE cat_ID = {$tagID};" );
		$cat_theme = stripslashes ( $csd_cat_options->cat_theme );
		$checked_exclude = ('1' == $csd_cat_options->not_subdomain) ? ' checked="checked"' : '';
		$checked_include = ('1' == $csd_cat_options->is_subdomain) ? ' checked="checked"' : '';
		$checked_filterpages = ('1' == $csd_cat_options->filter_pages) ? ' checked="checked"' : '';
		$link_title = stripslashes ( $csd_cat_options->cat_link_title );
		
		$themes = wp_get_themes();
		$theme_options = '<option value="">' . __('Default') . '</option>';
		foreach ( $themes as $theme ) {
		    $selected = ($cat_theme == $theme->Template) ? ' selected="selected"' : '';
		    $theme_options .= '<option value="' . $theme->Template . '"'.$selected.'>' . $theme->Name . '</option>';
		}
?>
<tr class="form-field">
<th scope="row" valign="top"><label for="csd_include"><?php _e('Make as Subdomain', 'wps'); ?></label></th>
<td><div class="form-field"><input type="checkbox" id="csd_include" name="wps[csd_include]" value="true"<?php echo $checked_include; ?> style="max-width:20px;" />
    <span class="description"><?php _e('Must be a main category', 'wps'); ?></span></div>
</td>
</tr>
<tr class="form-field">
<th scope="row" valign="top"><label for="csd_exclude"><?php _e('Exclude as Subdomain', 'wps'); ?></label></th>
<td><div class="form-field"><input type="checkbox" id="csd_exclude" name="wps[csd_exclude]" value="true"<?php echo $checked_include; ?> style="max-width:20px;" />
    <span class="description"><?php _e('Must be a main category to exclude if you opted All Categories as Subdomains by default', 'wps'); ?></span></div>
</td>
</tr>
<tr class="form-field">
<th scope="row" valign="top"><?php _e('Custom Title', 'wps'); ?></th>
<td><div class="form-field"><input type="text" name="wps[csd_link_title]" value="<?php	echo $link_title; ?>" />
    <p class="description"><?php _e('Custom Title', 'wps'); ?> <?php _e('to appear in any links to this Subdomain.', 'wps'); ?></p></div>
</td>
</tr>
<tr class="form-field">
<th scope="row" valign="top"><?php _e('Themes', 'wps'); ?></th>
<td><div class="form-field"><select name="wps[csd_cat_theme]"><?php echo $theme_options; ?></select>
    <span class="description"><?php _e('You have to activate Subdomain Themes in', 'wps'); ?> <a href="<?php admin_url('admin.php?page=wps_settings'); ?>"><?php _e('Settings', 'wps'); ?></a></span></div>
</td>
</tr>
<tr class="form-field">
<th scope="row" valign="top"><label for="csd_filterpages"><?php _e('Show Only Tied Pages', 'wps'); ?></label></th>
<td><div class="form-field"><input type="checkbox" id="csd_filterpages" name="wps[csd_filterpages]" value="true"<?php echo $checked_filterpages; ?> style="max-width:20px;" />
    <span class="description"><?php _e('Select this to only filter out pages not tied to categories, page lists will only show pages tied to this category', 'wps'); ?></span></div>
</td>
</tr>
<?php
}

function wps_save_taxonomy( $tagID ) {
  global $wpdb;
  $table_name = $wpdb->prefix . "category_subdomains";
    
  if ( isset( $_POST['wps'] ) ) {
	    $is_subdomain = ('true' == $_POST['wps']['csd_include']) ? '1' : '0';

	    $not_subdomain = ('true' == $_POST['wps']['csd_exclude']) ? '1' : '0';
	    
	    $cat_theme = addslashes($_POST['wps']['csd_cat_theme']);
	    if ($cat_theme == "(none)") {
	        $cat_theme = "";
	    }
	    
	    $link_title = addslashes(trim($_POST['wps']['csd_link_title']));
	    
	    $filter_pages = ('true' == $_POST['wps']['csd_filterpages']) ? '1' : '0';
	    
	    if ($wpdb->get_var("SELECT cat_ID FROM {$table_name} WHERE cat_ID = '{$tagID}'")) {
	        $querystr = "UPDATE {$table_name} SET is_subdomain={$is_subdomain}, not_subdomain={$not_subdomain}, cat_theme='{$cat_theme}', filter_pages={$filter_pages}, cat_link_title='{$link_title}' WHERE cat_ID = '{$tagID}'";
	    } else {
	        $querystr = "INSERT INTO {$table_name} (cat_ID, is_subdomain, not_subdomain, cat_theme, filter_pages, cat_link_title) VALUES ('{$tagID}', '{$is_subdomain}', '{$not_subdomain}', '{$cat_theme}', '{$filter_pages}', '{$link_title}')";
      }
      
      $wpdb->query($querystr);
  }
}

function wps_settings_help($contextual_help, $screen) {
    
    $menu = wps_admin_tabs(null, false); 
  
		if ( strpos($screen, 'page_wps') ) {
		    $_screen = get_current_screen();
		    
		    include_once(dirname(__FILE__) . '/help.php');
		    
		    foreach ($contextual as $title => $content) {
		      $_screen->add_help_tab(
		        array(
                'id'        => 'wps_' . strtolower(str_replace(' ','_',$title)),
                'title'     => __( $title ),
                'content'   => '<div class="trd-forth"><h2>' . __( $title ) . '</h2>' . __( $content ) . '</div>'
                ) 
          );
        }
		}
	return $contextual_help;
}
?>
