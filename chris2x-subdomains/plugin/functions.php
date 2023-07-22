<?php

function wps_blogurl() {
		$url = get_option ( 'home' );
		$url = substr ( $url, 7 );
		$url = str_replace ( "www.", "", $url );
	
	return $url;
}

function wps_domain() {
	if (get_option ( WPS_OPT_DOMAIN )) {
		$domain = get_option ( WPS_OPT_DOMAIN );
	} else {
		$domain = get_option ( 'home' );
		$domain = substr ( $domain, 7 );
		$domain = str_replace ( "www.", "", $domain );
	}
	
	return $domain;
}

//--- Find all the Pages marked with the showall meta key
function wps_showall_pages() {
	global $wpdb, $wps_page_metakey_showall;
	
	$pages = $wpdb->get_col ( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '{$wps_page_metakey_showall}' and meta_value = 'true'" );
	
	return $pages;
}

//--- Get the details of the authors for use with Author Subdomains
function wps_get_authors( $exclude_admin = false ) {
	global $wpdb;
	
	$authors = $wpdb->get_results ( "SELECT ID, user_nicename, display_name from $wpdb->users " . ($exclude_admin ? "WHERE user_login <> 'admin' " : '') . "ORDER BY display_name" );
	
	return $authors;
}

function wps_admin_style() {
    print('<style type="text/css"><!--
    body {
        font: 13px Arial;
    }
    hr {
        height: 1px;
        border: 0;
        border-top: 1px dashed #ccc;
        margin: 0 1em;
    }
    pre {
        background: #f7f7f7;
        border: 1px solid #ccc;
        padding: 1em;
        border-radius: 4px;
    }
    input[type="text"]{
        line-height: 1.2em;
        padding: 7px 1em;
        text-align: center;
        border-radius: 9px;
        background: #fcfcfc;
    }
   .large_text{
       color: #ccc;
       margin: 1em 0;
       text-align:center;
       font-size:2em;
       font-weight: bold;
       line-height: 1em;
       padding: 0;
       margin: 0;
    }
    ul{
       list-style: square;
       margin-left: 1em;
    }
    ul.wps{
        clear: both;
       list-style: none;
       padding: 0;
       margin: 5% 0 0;
    }
    ul.wps li{
        float: left;
        width: 17%;
        min-width: 165px;
        padding: 0;
        margin: 0 3% 3%;
    }
    ul.wps.child li{
        width: 12%;
    }
    ul.wps li:nth-child(4n+1), ul.wps.child li:nth-child(5n+1){
        clear: both;
    }
    ul.wps.child li:nth-child(4n+1){
        clear: none;
    }
    ul.wps li a{
        display:block;
        padding: 5px;
        min-height: 50px;
        color: #333;
        text-decoration: none;
        border-radius: 6px;
        border: 1px solid #999;
        background: #f7f7f7;
        font: bold 1.5em Arial;
        opacity: 0.3;
        transition: color .3s, background .3s, opacity .3s;
        -o-transition: color .3s, background .3s, opacity .3s;
        -ms-transition: color .3s, background .3s, opacity .3s;
        -moz-transition: color .3s, background .3s, opacity .3s;
        -webkit-transition: color .3s, background .3s, opacity .3s;
        
    }
    ul.wps li a:hover{
        opacity: 0.6;
    }
    ul.wps li.active a{
        opacity: 1.0;
        color: #000;
    }
    ul.wps li a img{
        display:block;
        padding:0 5%;
        width: 90%;
        max-width: 90%;
        min-width: 40px;
       text-align:center;
    }
    .center {
       text-align:center;
    }
    .one-forth {
        float: left;
        width: 25%;
        padding-bottom: 30px;
    }
    .trd-forth {
        clear: both;
        float: left;
        width: 70%;
        margin-right: 2%;
        padding-right: 2%;
        padding-bottom: 30px;
    }
--></style>');
}

function wps_admin_donate() {
    print('<div class="center one-forth">');
    print('<h3>' . __('Pair cup of coffee really helpful', 'wps') . ' :)</h3>');
    print('<img src="'.WPS_URL.'views/cup.png" width="128" />');
    print('<form class="alignright" action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank"><input type="hidden" name="cmd" value="_s-xclick" /><br/> <input type="hidden" name="hosted_button_id" value="J64YUTQ5E7UL6" /><br/> <input type="image" alt="PayPal - The safer, easier way to pay online!" name="submit" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" /><br/> <img class="lh_lazyimg" alt="" src="http://www.lontongcorp.com/wp-content/plugins/simple-lazyload/blank_1x1.gif" file="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1" border="0" /><noscript><img alt="" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1" border="0" /></noscript></form>');
    print('</div>');
}

function wps_admin_tabs( $current = '', $print = true ) {
    
    $wps_admin_menu = array( 
        '' => __('Welcome'),
        'settings' => __('Settings'),
        'categories' => __('Categories'),
        'pages' => __('Pages')
    );
    
  if($print) {
    wps_admin_style();
    echo '<h1>WP Subdomains</h1>';
    echo '<div id="icon-themes" class="icon32" style="margin-top:-2px;"><br /></div>';
    echo '<h2 class="nav-tab-wrapper">';
    foreach( $wps_admin_menu as $tab => $name ){
        $class = ( $tab == $current ) ? ' nav-tab-active' : '';
        if ($tab) $tab = '_'.$tab;
        echo "<a class='nav-tab$class' href='?page=wps$tab'>$name</a>";
    }
    echo '</h2>';
    echo wps_admin_notices();
    
  } else return $wps_admin_menu;
}

function wps_admin_notices() {
	global $wps_permalink_set;

	$notices = '';
	
	if (get_option(WPS_OPT_DISABLED) != '') {
		$notices .= '<div class="error-message"><h3>Wordpress Subdomains has been <span class="error-message">DISABLED</span>, but you can continue configuring it.</h3></div>';
	}
	
	if (!$wps_permalink_set) {
		$notices .= '<div class="error-message"><h3>Warning: you do not have <span class="error-message">PERMALINKS</span> configured so this plugin cannot operate.</h3></div>';	
	}
	
	return $notices;
}

function getPageChildren($pageID) {
	$childrenARY = array();
	$args = array(
	'post_parent' => $pageID,
	'post_status' => 'publish',
	'post_type' => 'page'
	);
	$children =& get_children($args);

	if ( $children ) {
		foreach (array_keys( $children ) as $child) {
			$childrenARY[] = $child;
			$childrenARY = array_merge($childrenARY, getPageChildren($child));	
		}
	}
	
	return $childrenARY;
}

function wps_getUrlPath($url) {
	$parsed_url = parse_url($url);
	
	if(isset($parsed_url['path'])) {
  	$path = ( (substr($parsed_url['path'], 0, 1) == '/') ? substr($parsed_url['path'], 1) : $parsed_url['path'] );
	} else {
		$path = '';
	}
	
	$path .= ( isset($parsed_url['query']) ? '?'.$parsed_url['query'] : '' );
	$path .= ( isset($parsed_url['fragment']) ? '#'.$parsed_url['fragment'] : '' );

	return $path;	
}

function wps_getNonSubCats() {
	global $wps_subdomains;
	
	$cats_root = get_terms( 'category', 'hide_empty=0&parent=0&fields=ids' );
		
	return array_diff( $cats_root, array_keys($wps_subdomains->cats) );
}

?>
