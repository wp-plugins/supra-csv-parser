=== Supra CSV ===
Contributors: zmijevik
Author URI: http://www.supraliminalsolutions.com/blog/downloads/supra-csv-premium/
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=CLC8GNV7TRGDU
Tags: csv,import,parse,ingest,custom post,extract,export,attachment,thumbnail
Requires at least: 3.2.1
Tested up to: 3.5.1
Stable tag: 3.0.1

A plugin to ingest and extract posts from csv files. 

== Description ==

The purpose of this plugin is to parse uploaded csv files into any type of
post. Themes or plugin store data in posts and this plugin provides the functionality 
to upload data from the csv file to the records that the theme or plugin creates. The plugin also provides the ability to import images and associate them to post as attachment in the premium version.
Manage existing csv files and promote ease of use by creating presets for both postmeta 
and ingestion mapping. For general information regarding the plugin visit <http://www.supraliminalsolutions.com/blog/listings/supra-csv/> . For more infomation on how to obtain the necessary info watch the 
detailed tutorials <http://www.supraliminalsolutions.com/blog/supra-csv-tutorials/>. To ingest csv files into custom posts or extract posts into csv files
you must upgrade to the premium version of the plugin. 

== Frequently Asked Questions ==

= How do I ingest mutiple taxonomy for a post?  =
Provide a pipe symbol | as a delimiter for the custom terms. more info is provide in the docs at III.a.a

= parse error. not well formed =
Make sure there are no special characters in the csv values. CSV Values Must be UTF-8 compliant!

== Screenshots ==

1. Configuration Tab

2. Uploads Tab

3. Post Meta Mapping Tab

4. Ingestion Tab

5. Extraction Tab

6. Easily debug issues

== Changelog ==

= 3.0.0 =
autopopulated suggestions as postmeta keys with enable feature.
added post_status and menu_order to predefined in ingestion.
added the ability to encode special characters in the configuration tab.
= 2.9.9 =
fixed the delimiter bug in export tab and added some example csvs
= 2.9.8 =
added the post parent to predefined on the ingestion page
= 2.9.6 = 
auto populate export meta keys
= 2.9.1 = 
plugin split into premium and free version
= 2.8.9 =
xmlrpc tweak for wp 3.5 enable option depracation
= 2.8.8 = 
no more open_short_tag
