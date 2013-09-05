=== Supra CSV ===
Contributors: zmijevik
Author URI: http://www.supraliminalsolutions.com/blog/downloads/supra-csv-premium/
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=CLC8GNV7TRGDU
Tags: csv,import,parse,ingest,custom post,extract,export,attachment,thumbnail
Requires at least: 3.2.1
Tested up to: 3.5.1
Stable tag: 3.1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A plugin to ingest and extract/create/update posts, custom posts, attachments, tags, keywords and custom post meta with csv files. 
 
== Description ==

The purpose of this plugin is to parse uploaded csv files into any type of
post. Themes or plugin store data in posts and post meta. This plugin provides the functionality 
to upload data from the csv file into the postmeta records which themes and plugins create. 
This plugin also provides the ability to import images and associate them to post 
as attachments or thumbnails. Manage existing csv files and promote ease of 
use by creating presets for both postmeta and ingestion mapping.

For general information regarding the plugin visit <http://www.supraliminalsolutions.com/blog/listings/supra-csv/> .

Watch the detailed tutorials <http://www.supraliminalsolutions.com/blog/supra-csv-tutorials/>. 

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

= 3.1.0 =
created help icon tooltips for the configuration page and updated the docs

= 3.0.9 =
added max character count per line of the csv

= 3.0.8 =
error logging including the csv filename and the line number of the row

make the results of the previous ingestion clear when you select a new file to ingest so that it shows you it is uploading it
= 3.0.7 =
added the ability to update posts.

fixed a bug that would ingets blank csv rows

added the sample_basic_edit csv to demonstrate ingesting a record to edit a post
= 3.0.6 =
removed 50 post ingestion limit from the free version
= 3.0.5 =
limited time offer! rate and review for premium
= 3.0.4 = 
updating the readme file
= 3.0.3 = 
added the header image
= 3.0.2 =
added more verbose and detailed debugging ouput
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
