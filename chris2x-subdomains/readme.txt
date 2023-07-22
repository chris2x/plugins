=== Plugin Name ===
Contributors: lontongcorp, casualgenius, selnomeria
Donate link: http://www.lontongcorp.com/2012/03/16/wp-subdomains/
Tags: subdomain, subdomains, categories, post, posts, page, pages, themes, cdn
Requires at least: 3.0
Tested up to: 3.5.1
Stable tag: 0.9.3

Setup your main categories, pages, and authors as subdomains with custom themes. Surely will come for more options...

== Description ==

An updated modification of "<a href="http://wordpress.org/extend/plugins/wordpress-subdomains/">WP Subdomains</a>" 0.6.9 to make subdomains for Categories, Pages and Authors without or inside Multisite.
The original description is at <a href="http://wordpress.org/extend/plugins/wordpress-subdomains/">original plugin's page</a>, but you MUST also read <a href="other_notes/"><strong>INSTRUCTIONS</strong></a> sections.

Works perfectly as CDNs, likewise with <a href="http://wordpress.org/extend/plugins/w3-total-cache/">W3 Total Cache</a>, to increase performances without any additional technical problems.

= Features =
* Setup main categories as subdomains
* Setup main pages as subdomains
* Setup author archives as subdomains
* Custom themes for each subdomains
* Tie pages to categories
* Contextual help screen
* Widgets
* Localization


== Installation ==

* Download `wp-subdomains-revisited.zip` manually or automatically `Plugins/Add New`
* Unzip
* Upload `wp-subdomains-revisited` directory to your `/wp-content/plugins` directory
* Activate the plugin
* Configure the options from the `WP Subdomains/Settings` page
* Configure each category from the Category Settings `Posts/Category` page
* Configure each pages from the Pages Editor `Pages/your-page-name/Edit`



== Frequently Asked Questions ==

See the original plugin Faq: http://wordpress.org/extend/plugins/wordpress-subdomains/

= How to add subdomains? =

Please go to your domain manager or contact your provider

= Do I have to add each subdomain manually? =

You can add wildcard (A or CNAME) *.domain.com to the same installations path 

= Is this plugins works with cache? =

Sure things. I recommend it using W3 Total Cache by setting one page as "cdn" or anypage.domain.com, and make as Generic Mirror CDN and add as cdn for image, css or javascript without any technical difficulties anymore


== Screenshots ==

1. WP Subdomains -&gt; Settings
2. Category Interface
3. Pages Editor widget interface


== Changelog ==

= 0.9.3 = 

* Add i18n localization POT file

= 0.9.2 = 

* Fix Theme Option Bug

= 0.9.1 = 

* Fix UI Bug

= 0.9 = 

* Update UI
* Add icons
* Add (Contextual) Help Screen
* Disabling Tags tied to subdomains options until further notice

= 0.8 = 

* Updating WP Query
* Fix database table creations for new installation
* Updating layouts
* Adding menu icon
* Page Structures
* Adding Custom Theme options in page editor widget
* Post Canonical URL to avoid duplicate content
* Support localization
* Changing revision numbers

= 0.7.0 = 

* Updating to work with Wordpress 3.3+

= 0.6.9 = 

see the original changelog at: http://wordpress.org/extend/plugins/wordpress-subdomains/)



== Instructions ==

Read the original instructions at <a href="http://wordpress.org/extend/plugins/wordpress-subdomains/">Original Plugin</a>.
Read the instructions on plugins help screen on plugins backend.

== Credits ==
* <a href="http://www.lontongcorp.com">Erick Tampubolon</a> of <a href="http://www.igits.co.id">IGITS</a> (Author)
* <a href="http://profiles.wordpress.org/selnomeria">selnomeria</a> (Commiter)
* <a href="http://demp.se/y/2008/04/11/category-subdomains-plugin-for-wordpress-25/">Adam Dempsey</a> (Contributor)
* <a href="http://blog.youontop.com/wordpress/wordpress-category-as-subdomain-plugin-41.html">Gilad Gafni</a> (Contributor)
* <a href="http://casualgenius.com">Alex Stansfield</a> of <a href="http://casualgenius.com">Casual Genius</a> (Original Author)
* Based on the <a href="http://www.biggnuts.com/wordpress-subdomains-plugin/">Subster Rejunevation</a> wordpress plugin by <a href="http://www.biggnuts.com/">Dax Herrera</a>.
