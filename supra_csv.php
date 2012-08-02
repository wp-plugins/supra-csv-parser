<?php 
/*
Plugin Name: Supra Csv Importer
Plugin URI: http://wordpress.org/extend/plugins/supra-csv-parser/
Description: Plugin for parsing a csv files into posts.
Author: J. Persie
Version: 2.0
*/

function scsv_admin() {
    require_once(dirname(__FILE__).'/supra_csv_admin.php');
}

function scsv_ingest() {
    require_once(dirname(__FILE__).'/supra_csv_ingest.php');
}

function scsv_postmeta() {
    require_once(dirname(__FILE__).'/supra_csv_postmeta.php');
}

function scsv_upload() {
    require_once(dirname(__FILE__).'/supra_csv_upload.php');
}

function scsv_admin_actions() {
    add_menu_page("Supra CSV", "Supra CSV", "manage_options", "supra_csv", "scsv_admin");
    add_submenu_page("supra_csv", "Configuration", "Configuration", "manage_options", "supra_csv", "scsv_admin");
    add_submenu_page("supra_csv", "Post Info", "Post Info", "manage_options", "supra_csv_postmeta", "scsv_postmeta");
    add_submenu_page("supra_csv", "Ingestion", "Ingestion", "manage_options", "supra_csv_ingest", "scsv_ingest");
    add_submenu_page("supra_csv", "Upload", "Upload", "manage_options", "supra_csv_upload", "scsv_upload");
}

function supraCsvAjax() {
    require_once(dirname(__FILE__).'/classes/SupraCsvAjaxHandler.php');
    $ah = new SupraCsvAjaxHandler($_REQUEST);
    die();
}

wp_enqueue_script( 'ajax', plugins_url('/js/ajax.js', __FILE__) );
add_action('admin_menu','scsv_admin_actions');
add_action('wp_ajax_supra_csv','supraCsvAjax');
