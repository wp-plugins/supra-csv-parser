<?php

require_once('../../../../../wp-load.php');
require_once('../IngestCsv.php');
require_once('../CsvLib.php');

$ic = new \SupraCsvFree\IngestCsv();

$settings = array(
    'scsv_has_hooks' => true,
    'scsv_post' => array (
        'publish' => '1',
        'type' => 'post',
        'title' => 'default title',
        'desc' => 'default description',
    ),
    'scsv_parse_terms' => true,
    'scsv_custom_terms' => '',
    'scsv_postmeta' => array(
        'meta_key' => array(
            0 => '_edit_lock',
            1 => '_gc_price',
            2 => '_gc_savings',
            3 => '_su_desc',
            4 => '_su_keywords',
            5 => '_su_title',
            6 => '_encloseme',
            7 => '_geo_city',
        ),
        'displayname' => array(
            0 => '_edit_lock',
            1 => '_gc_price',
            2 => '_gc_savings',
            3 => '_su_desc',
            4 => '_su_keywords',
            5 => '_su_title',
            6 => '_encloseme',
            7 => '_geo_city',
        ),
        'use_metakey' => array(
            0 => '1',
            1 => '2',
            2 => '3',
            3 => '4',
            4 => '7',
        ),
    ),
    'scsv_misc_options' => array(
        'is_ingestion_chunked' => '1',
        'chunk_by_n_rows' => '50',
    ),
    'scsv_ingest_debugger' => true,
    'scsv_report_issue' => true,
    'scsv_user' => array(
        'name' => 'admin',
        'pass' => 'admin',
    ),
    'scsv_encode_special_chars' => true,
    'scsv_csv_settings' => array(
        'delimiter' => ',',
        'enclosure' => '\\"',
        'escape' => '\\\\',
    ),
    'scsv_additional_csv_settings' => array(
        'line_maxlen' => 0,
    )
);

$scp = new \SupraCsvFree\SupraCsvParser(null, $settings);

$settingsResolver = (function($setting_key) use($scp) {

    $settings = $scp->getSettings();

    if(in_array($setting_key, $settings))
    {
        $setting = $settings[$setting_key];
    }
    else
    {
        $scp->getLogger()->info('tried to retrieve a non-existing setting: ' . $setting_key);
    }

    return $setting;
});

$scp->setSettingsResolver($settingsResolver);

$scp->init($settings);

$ic->setSupraCsvParser($scp);

$ic->ingest([
    'filename'=>'sample.csv',
    'mapping'=> array (
        'post_id' => '',
        'post_title' => 'Business Name',
        'post_content' => 'Full Address',
        'category' => '',
        'post_tag' => '',
        'post_type' => '',
        'post_status' => '',
        'post_author' => '',
        'post_password' => '',
        'post_excerpt' => '',
        'post_date' => '',
        'post_date_gmt' => '',
        'post_thumbnail' => '',
        'comment_status' => '',
        'ping_status' => '',
        'post_format' => '',
        'enclosure' => '',
        'post_parent' => '',
        'menu_order' => '',
        'term_name' => '',
        'term_slug' => '',
        'term_parent' => '',
        'term_description' => '',
        '_gc_price' => '',
        '_gc_savings' => '',
        '_su_desc' => '',
        '_su_keywords' => '',
        '_geo_city' => 'City',
    )
]);
