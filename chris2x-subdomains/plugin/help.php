<?php

$contextual['Overview'] = '<p>WP Subdomains is a wordpress plugin to setup your main categories, pages, and authors as subdomains and give them custom themes.</p>
<p>This is an updated modifications of "<a href="http://wordpress.org/extend/plugins/wordpress-subdomains/">WP Subdomains</a>" 0.6.9 by Alex of CasualGenius. The original description is at <a href="http://wordpress.org/extend/plugins/wordpress-subdomains/">original plugin\'s page</a>, but you MUST also read <strong>Instructions</strong> sections too.</p>';

$contextual['Instructions'] = '<p>Read the original instructions at <a href="http://wordpress.org/extend/plugins/wordpress-subdomains/">Original Plugin</a> first, but read these instructions as well.</p>
<p>While the plugin is activated for pages, and you check "Make the Page as Subdomain" while you publish e a post, then the published pages link will be "pagename.site.com" in the whole website link structure (and not `site.com/pageName`). but the page can also be accessed at `site.com/pagename`.
but it is essential, that you also create a subdomain in your hosting control panel (as it is described at <a href="http://wordpress.org/extend/plugins/wordpress-subdomains/">http://wordpress.org/extend/plugins/wordpress-subdomains/</a>, but if you cant setup DNS records as described there, then see the manual instruction of subdomain creation in the end of this instruction).</p>
<p>The page link(slug) is created from title. So, if you want to give the page other link rather than its title, then after publishing you should enter "All pages" and "quick edit" the slug (this is the only way for now). </p>
<p>Advice: When you delete the page, delete it from "Trash" too.</p>
<p>Don\'t "Add new page" two times parallely. At first, publish one page, then add another page. otherwise the simultaneously opened two page links will blend with each other.</p>
<p>If you publish many pages and want to show only several ones in the navigation menu, then use "Exclude Pages from nagivation" plugin, and uncheck the special "include box" while you publish a page.</p>';

$contextual['Create Subdomain'] = '<h3>Manual SUBDOMAIN creation in your Hosting</h3>
<p>Create the subdomain with the exact characters, as the link(example: subdomain name should be "zeze" if you are making "zeze.site.com")
then in the FTP (in public_html, there will be created the "homer-gre" folder, where you should upload an index.php file with the content:</p>

<h4>A) If you create a page :</h4>

<pre>&lt;?php
$_GET["pagename"] = basename(getcwd());
/**or $_GET["page_id"] = x; */
define("WP_USE_THEMES", true);
require("../wp-blog-header.php");
?&gt;</pre>

<h4>B) If you want for a category:</h4>

<pre>&lt;?php
$foldername = basename(getcwd());
$SubdomainName = basename(getcwd()) . ".";
$HomepageLink= "http://" . $_SERVER&#x5b;"HTTP_HOST"] . "/";
$HomepageLinkWithoutSubdomainName = str_replace($SubdomainName, "", $HomepageLink);
$HomepageLinkFULL = $HomepageLinkWithoutSubdomainName . "category/" . $foldername;
readfile($HomepageLinkFULL);
?&gt;</pre>

<h4>C) if you want for an author:</h4>

<pre>&lt;?php
$foldername=basename(getcwd());
$SubdomainName=basename(getcwd()) . ".";
$HomepageLink= "http://" . $_SERVER&#x5b;"HTTP_HOST"] . "/";
$HomepageLinkWithoutSubdomainName=str_replace($SubdomainName, "", $HomepageLink);
$HomepageLinkFULL=$HomepageLinkWithoutSubdomainName . "author/" . $foldername;
readfile($HomepageLinkFULL);
?&gt;</pre>';

?>
