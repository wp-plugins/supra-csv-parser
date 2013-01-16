=== Supra CSV ===
Contributors: zmijevik
Author URI: http://profiles.wordpress.org/zmijevik
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=CLC8GNV7TRGDU
Tags: Csv Importer , Csv Parser , Csv Injector , Custom Post, csv, Csv Extractor, Csc Exporter
Requires at least: 3.2.1
Tested up to: 3.2.1
Stable tag: 2.8.9

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

= 2.8.8 = 
no more open_short_tag
= 2.8.9 =
xmlrpc tweak for wp 3.5 enable option depracation
