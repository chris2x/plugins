=== WP-Click-Tracker ===
Contributors: mithra62
Tags: tracking, clicks, link, links, click, stats, reports
Requires at least: 2.6
Tested up to: 3.1.1
Stable tag: trunk

Tracks link clicks from hrefs in posts and pages. REQUIRES PHP at least 5.2

== Description ==

The click tracker works in 2 modes:<br />
1. Scans posts and rewrites them to include a tracking element<br />
2. Enables users to create stand alone trackable links that can be embedded in posts or offsite.<br /><br />

There is advanced reporting for viewing the click statistics. Reports include:<br />
1. Top Referrers of clicks. Helps to coorelate where the popular links are at :)<br />
2. Individual clicks. <br />
3. Clicks by day. <br />
4. Clicks by hour.<br />
5. Graphs and charts per link and globally.<br />
6. Admin dashboard widget.<br /><br />

**Usage**<br />
The click tracker works in 2 modes:<br />
1. Scans posts and rewrites them to include a tracking element<br />
2. Enables users to create stand alone trackable links that can be embedded in posts or offsite.<br /><br />

Links that are included in posts should contain a title paramater in the href to have the reference name <br />
be automatically created. Ex:<br />
&lt;a href="http://url.com" title="Link Title"&gt;link copy&lt;/a&gt;<br /><br />

Links without a title attribute will have the name parsed through the wrapped text (if that fails default name is used (No Name Given)).<br /><br />

You can also create standalone links using the Link Manager to embed in external websites. <br /><br />

**Future Releases**<br />
* localization<br />
* export data<br />
* link list sorting<br />
* referrer link list grouping<br />

**Bugs**<br />
* There's a conflict with any plugin that uses the onclick method in URLs.<br />

**Changelog**

<br />
0.7.2 :: <br />
*Bug Fixes *   
  <br />Fixed Timezone issue
<br />
0.7.2 :: <br />
*Bug Fixes *   
  <br />Fixed data reset
  <br />Fixed IIS6 HTTPS issue
<br />
0.7.1 :: <br />
*Bug Fixes *   
  <br />Small bug fixes for JS compatibility and slow and sloppy SQL. 
<br />

0.7 :: <br />
* Modification * 
  <br />Improved tracking cookie management.
  <br />Added setting to disable the tracking of internal (same URL) links 
  <br />Changed Configure page to use tab ui
<br />
*Bug Fixes *   
  <br />Fixed ssl (https) => flash issue
  <br />Fixed sidebar.css 404 error  
  <br />Added "nowrap" to table template headers
  <br />Fixed Day Pie chart for thousandths
<br />

0.6 :: <br />
* Modification * 
  <br />Added Top Clicks Sidebar Widget
  <br />Added Todays Clicks Sidebar Widget
  <br />Added Link Search (requires MyISAM table type)
  <br />Began localization phase
  <br />Improved admin interface and menu
  <br />Improved tracking mechanism
<br />
*Bug Fixes * 
  <br />Fixed 'label' in Exclude IP field on configure page
  <br />Fixed "self" link tracking/FireFox issue.<br />
  <br />Fixed "https" being ignored during sanity check.<br />

<br />
0.5.1 :: <br />
* Modification * 
  <br />Added Miscellaneous Settings (Sarah Mod)
  <br />Changed admin widget date range to match WordPress.com Stats widget
<br />
*Bug Fixes * 
  <br />Fixed 'label' in configure page form<br />

0.5 :: <br />
* Modification *
 <br />Added settings to disable user clicks 
 <br />Added ignore IP address for click tracking
 <br />Added link statistics reset
 <br />Changed graphs to Open Flash Chart
 <br />Added additional line chart vectors to display unique clicks
 <br />Added link parsing of next and prev template tags
 <br />Added link parsing of categories in posts/pages as well as sidebar widget
 <br />Added link parsing of tags links template
 <br />Improved title extraction to reduce No Name Given auto-label
 <br />Added global history and report page
* Bug Fixes *<br />
 <br />Click graph date descrepancy issue
 <br />Added bypass for external links being double tracked when entered in page 
 <br />Fixed backwards tracking flags
 <br />Changed admin widget ordering to list most clicked to least click
<br />

0.4.2 :: <br />
* Modification *
 <br />Admin widget links to click admin 
 <br />Added contextual help
* Bug Fixes *
 <br />Comment parsing in admin removal
 <br />Fixed pathing issue for blogs in a sub directory
<br />

0.4.1 :: <br />
* Improvements *
 <br />Added admin dashboard for x day click count line graph 
* Bug Fixes *
 <br />Fixed Division by zero bug
<br />

**Changelog**<br />
0.4 :: <br />
* Improvements *
 <br />Added configuration page
 <br />Added "Add Link" mod. Admins can create standalone links for placement on external sites.
 <br />Added "Edit Link" mod for editing names or stored links.
 <br />Enable or disable individual parsing sections
 <br />Enable or disable individual parsing sections
 <br />Moved google api js call to only fire on click track pages (was slowing down some areas of the admin).
* Bug Fixes *
 <br />fixed spelling of days of week
 <br />fixed pathing issue for tracking call (Logan and Gary's Bug)
 <br />fixed comment body parsing (wasn't grabbing links)
 <br />fixed memory issues (FINALLY :)
<br />

0.3 :: <br />
* Improvements *
 <br />Added Indexes on tables for better performance
 <br />Updated Install system
 <br />Added pie charts for clicks by day and hour
* Bug Fixes *
 <br />fixed hour click report formatting
 <br />fixed blank link haunting
 <br />fixed option saving
<br />

0.2.1 :: <br />
* Bug Fixes * 
 <br />fixed bug for attributes with single quotes
 <br />added parsing / tracking of Archive links, Bookmark links, Comment Author URL links and fixed option updates<br />

0.2 :: <br />
* Improvements *
 <br />Added reporting
 <br />Added blogroll link tracking
 <br />Added upgrade alert and plugin compatibility notification<br />

0.1.1 :: 
* Bug Fixes *
 <br />Moved js to <head> to fix tracking issues in some browsers<br />

0.1 :: Release

== Screenshots ==

1. Your click tracker report per post / page.

2. Link Report.

3. Daily Report.

4. Admin Widget.

5. Configuration.

== Installation ==

1. Create backup.
2. Upload the zip file to the `/wp-content/plugins/` directory
3. Unzip.
4. Activate the plugin through the 'Plugins' menu in WordPress

Please let me know any bugs, improvements, comments, suggestions.

[Documentation](http://blog.ericlamb.net/wp-click-track/"WP Click Track
Documentation")

== Frequently Asked Questions ==

= Does it track logged in user clicks? =

At the moment the click tracker makes no distinction between logged in users and guests.

= How soon do stats show up =

Stats will show up once a link has been clicked.

= I can't get it to work! Help!! =

Check if you have the Google Analytics plugin installed. If it is, make sure you have "Track Outbound Links" unchecked. This conflicts with wp-click-tracker.