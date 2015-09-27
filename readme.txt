=== Supra CSV ===
Contributors: zmijevik
Author URI: http://www.supraliminalsolutions.com/blog/listings/supra-csv/
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=CLC8GNV7TRGDU
Tags: csv,import,parse,ingest,custom post,extract,export,attachment,thumbnail
Requires at least: 3.2.1
Tested up to: 4.0
Stable tag: 4.0.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A plugin to ingest and extract/create/update posts, custom posts, attachments, tags, keywords and custom post meta with csv files. 
 
== Description ==

The purpose of this plugin is to parse uploaded csv files into any type of
post including custom post. The ingestion engine of the plugin parses csv records
into a posts by a mapping of columns that you configure in the plugin interface. 

This plugin provides the functionality to upload data from the csv file into the postmeta records which themes and plugins create. 
This plugin also provides the ability to import images and associate them to post 
as attachments or thumbnails. Manage existing csv files and promote ease of 
use by creating presets for both postmeta and ingestion mapping.

The postmeta mapping feature automatically retrieves all of the possible postmeta fileds of the selected post type.
If there is a postmeta field that doesnt show in the interface you have to ability to create a new post meta field.
The ingestion interface is dynamically arranged and populated based on the post type and the values that you have
provided in the configuration interface. 

There is also an extraction interface that provides the ability to extract post records into a csv format. You have the 
option to filter the results returned by various post fields in addition to being able to specify what post fields and 
associated postmeta keys populate in the csv rows.

In the latest version of the plugin there is also the ability to implement the plugin hooking api the customize the flow
of ingestion. For instance if you wanted to customize how a post is linked to several other posts you can do so by writing
very minimal php inside of a hook function that the ingestion engine will detect.

For general information regarding the plugin visit 

<http://www.supraliminalsolutions.com/blog/listings/supra-csv/>

Try the demo here

<http://www.supraliminalsolutions.com/supra-csv/demoblog/wp-admin/>

* username: admin

* password: admin

Watch the detailed tutorials 

<http://www.supraliminalsolutions.com/blog/supra-csv-tutorials/>

== Frequently Asked Questions ==

= Where is some more information about this plugin? =
In addition to the tooltips provided in the various interfaces of the plugin you can also
read the organized documentation provided in the docs tab of the plugin. The support forum
is also a great tool and the author of the plugin is very responsive in addressing a multitude of issues
and questions.

= Links to the info? =
For general information regarding the plugin visit 

<http://www.supraliminalsolutions.com/blog/listings/supra-csv/> 

Watch the detailed tutorials 

<http://www.supraliminalsolutions.com/blog/supra-csv-tutorials/>


= How do I ingest mutiple taxonomy for a post?  =
Provide a pipe symbol | as a delimiter for the custom terms. more info is provide in the docs at III.a.a

= What does the following error message mean? "parse error. not well formed" =
Make sure there are no special characters in the csv values. CSV Values Must be UTF-8 compliant!

= Can I associate attachments or thumbnails to a post =
In the free version of the plugin you do have the ability to associate a thumbnail to a post by 
referencing the attachment id of the media to post thumbnail. In the premium version you can easily
provide urls to the images and associate them that way

= Can I customize to ingestion process of the plugin =
You may customize the ingestion process of the plugin by implementing functionality in the plugin 
hooking api. There is already an example function there that retrieves the last ingested post id
and make it as an available field to associate to a post.

= What are the steps to ingest a csv? =
1. configure in 'Configuration'
1. upload file in 'Upload'
1. define postmeta in 'Post Info'
1. map the data and import in 'Ingestion'
1. save postmeta and mapping presets wherever necessary

== Screenshots ==

1. Configuration Tab

2. Uploads Tab

3. Post Meta Mapping Tab

4. Ingestion Tab

5. Extraction Tab

6. Easily debug issues

== Changelog ==
= 4.0.3 =
* Fixing issues that prevented post editing. 
* Added errors and tips for php max_exution time.
= 4.0.2 =
* fixing a path resolver bug that prevented debug button from working
* adding the post ID to the extraction page
* fixed a bug that was throwing fatal error when attemtping to ingest a date value
= 4.0.1 =
* removed error throwing in remotePost class
* adding the log management page
= 4.0.0 =
* fixed major issues with parsing csv lines
* adding csv file chunking support
* added asynchrnous multithreading capabilities
* added the ability to skip post revision insertions
* added error logging
* added error tips
* removing error reporting functionality
* fixed bugs with post type taxonomy validation
* fixing remaining mysqli support issues
= 3.4.6 =
* adding support for mysqli extension
* removed filetype validation from uploads
= 3.4.5 =
* fixed broken error reporting functionality
= 3.4.4 =
* fixed major performance issues by overhauling xmlrpc methodology to process confined. No overhead of network latency 
* fixed broken CSS that removed styling in 1.3.2. 
= 3.4.1 = 
* fixed an issue with previewing extracts after deletion
= 3.4.0 =
* fixed bugs in the export feature
* fixed javascript bugs with extracted file previews
* added overridable CSV settings to the extraction funtionality
= 3.3.9 =
* adding the jquery table sorter to the csv preview/download buttons
= 3.3.8 =
* made some improvements to the ajax functionality on various pages
* improved usability and user friendliness
* removing more php notice errors
* adding plugin installation error notifier
= 3.3.7 =
* Suppressing notice errors
* fixed bug with updating post meta
= 3.3.6 =
* implementing windows compatible directory delimiter
= 3.3.5 =
* fixed post status bug
= 3.3.4 =
* removing php notice errors
= 3.3.3 =
* fixing up the hideous styling after firing the designer
= 3.3.2 =
* supporting multisite support
* fixing activation hooks to install samples csv files
* removing the unexpected charcters generated plugin activation error
= 3.3.0 =
* separated file extraction from upoad interface
* dynamically populating extracted files in extraction interface
* major refactoring of codebase for extract and upload functionality
= 3.2.9 =
* centralizing documentation 
= 3.2.8 =
* critical bug in extract and export process fixed
= 3.2.7 =
* fixed a major issue resolving directory names
= 3.2.5 =
* fixed a bunch of bugs in extraction process
= 3.2.4 =
* fixed a bug that was preventing postmeta suggestions from populating
= 3.2.3 =
* adding all post fields to select drop down in extraction interface
* replaced post meta input with select drop down in extraction interface
= 3.2.1 =
* fixing script clash of enqueue scripts bug
* added file export functionality into uploads from extraction
* added the ability to select multiple post type in extraction
* imploding field array in extracted post csv file
= 3.1.5 =
* updating the documentation, faq and screenshots of the plugin
= 3.1.2 =
* implemented a hooking API
* updating the docs and tooltips about hooking
* integrated last_post_id as a hook
= 3.1.1 =
* adding tooltips to the rest of the pages
= 3.1.0 =
* created help icon tooltips for the configuration page and updated the docs
= 3.0.9 =
* added max character count per line of the csv
= 3.0.8 =
* error logging including the csv filename and the line number of the row
* make the results of the previous ingestion clear when you select a new file to ingest so that it shows you it is uploading it
= 3.0.7 =
* added the ability to update posts.
* fixed a bug that would ingets blank csv rows
* added the sample_basic_edit csv to demonstrate ingesting a record to edit a post
= 3.0.6 =
* removed 50 post ingestion limit from the free version
= 3.0.5 =
* limited time offer! rate and review for premium
= 3.0.4 = 
* updating the readme file
= 3.0.3 = 
* added the header image
= 3.0.2 =
* added more verbose and detailed debugging ouput
= 3.0.0 =
* autopopulated suggestions as postmeta keys with enable feature.
* added post_status and menu_order to predefined in ingestion.
* added the ability to encode special characters in the configuration tab.
= 2.9.9 =
* fixed the delimiter bug in export tab and added some example csvs
= 2.9.8 =
* added the post parent to predefined on the ingestion page
= 2.9.6 = 
* auto populate export meta keys
= 2.9.1 = 
* plugin split into premium and free version
= 2.8.9 =
* xmlrpc tweak for wp 3.5 enable option depracation
= 2.8.8 = 
* no more open_short_tag
