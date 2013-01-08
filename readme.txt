=== Supra CSV ===
Contributors: zmijevik
Author URI: http://profiles.wordpress.org/zmijevik
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=CLC8GNV7TRGDU
Tags: Csv Importer , Csv Parser , Csv Injector , Custom Post, csv, Csv Extractor, Csc Exporter
Requires at least: 3.2.1
Tested up to: 3.2.1
Stable tag: 2.8.8

== Description ==

The purpose of this plugin is to parse uploaded csv files into any type of
post. The plugin also provides the functioanlity to extract and export post
into csv files. Some themes or plugin store data in post with custom post_type, thus this
plugin provides the functionality to upload data from the csv file to the records
that the theme or plugin creates. Manage existing csv files and promote ease of use
by creating presets for both postmeta and ingestion mapping. For more infomation
on how to obtain the necessary info watch the detailed tutorial. 

[youtube http://www.youtube.com/watch?v=0xKpNw1cT-Q]

== Frequently Asked Questions ==

= How do I ingest mutiple taxonomy for a post?  =
Provide a pipe symbol | as a delimiter for the custom terms. more info is provide in the docs at III.a.a

= transport error - could not open socketProblem Ingesting? =
First make sure you have xmlrpc enabled. Secondly make sure your hosting has port 111 open.

= parse error. not well formed =
Make sure there are no special characters in the csv values. The will show as question marks in your debug output 

== Screenshots ==

1. Ajax Real-Time ingestion interface

2. PostMeta Identifier Via Post Type

3. Easily debug issues

== Changelog ==

= 2.2 =
Added the post meta suggestions and post type picker functioanlity
= 2.3 = 
updated the youtube video
= 2.3.1 = 
updated mime type validation per user request
= 2.3.2 =
fixed a javscript bug of missing key
= 2.3.3 =
added the categories and tags import functionality per user request
= 2.3.4 =
added the ability to provide multiple tags and categories for each post
= 2.3.5 =  
revised uploadcsv fopen to use file access instead to prevent chaning php.ini
= 2.3.6 =
added the ability to provide category ids instead of names per user request
= 2.3.9 =
storing uploads outside of plugin direcotry to prevent file deletion during updates
= 2.4 =
created the term taxonomy import functionality per user request
= 2.4.1 =
overhauled xmlrpc script to allow ingestion of hidden and protected postmeta
= 2.4.3 =
trigger the change event after mappring preset modification and creation
= 2.4.5 = 
subcategory import functionality and absent postmeta mapping bug fix
= 2.4.7 = 
created the ingestion debugger and fixed the complex category ingestion bug
= 2.4.8 =
validating taxonomy based on selected posttype
= 2.5.1 = 
added paypal donate button and more info about the plugin
= 2.5.7 =
added delimiter,enclosure, escape settings for parsing the csv
= 2.5.8 =
created the issue reporting functionality
= 2.6.2 =
fixed the unsupportted taxonomy bug
= 2.6.8 = 
splitted the home and admin page 
= 2.7 =
checking for empty post meta values and imporving issue reporting
= 2.7.1 =
fixed uploads folder permission bug
= 2.7.2 = 
fixed php compatability with fgetcsv
= 2.7.5 =
added documentation to the plugin
= 2.7.6 =
fixed blank description/title bug
= 2.7.7 = 
added the post extractor/csv exporter base functionality
= 2.7.8 =
fixed the ingestion form when deviating from csv settings
= 2.7.9 =
fixed the predefined categories and tags bug
= 2.8 =
added the donate link and the FAQ
= 2.8.1 =
provided the ability for omitting the post_content field
= 2.8.2 = 
added more predfined fields and added styling to the ingestion page
= 2.8.3 =
fixed the fcsv bug again..
= 2.8.5 =
adding the screenshots
= 2.8.6 = 
fixed the category ingestion bug
= 2.8.7 =
fixed an issue with 3.5 deprecating enablexmlrpc
= 2.8.8 = 
no more open_short_tag
