<?php
require_once(dirname(__FILE__) . '/../../../wp-load.php');
require_once(dirname(__FILE__) . '/classes/IngestCsv.php');
require_once(dirname(__FILE__) . '/classes/CsvLib.php');

$csvFilename = $argv[1];

$mappingFilename = $argv[2];

$ic = new \SupraCsvFree\IngestCsv();

$scp = new \SupraCsvFree\SupraCsvParser();

$scp->setSettingsResolver(function($setting_key) {
        return get_option($setting_key);
});

$scp->init();

$ic->setSupraCsvParser($scp);

$mapping_file = $scp->getPluginChunkDir() . '/' . $mappingFilename;

if(!file_exists($mapping_file))
{
    Throw new \Exception($mapping_file . " does not exist");
}
else
{
    $mapping = json_decode(file_get_contents($mapping_file), true);
}

$csvFile = $scp->getPluginChunkDir() . '/' . $csvFilename;

$scp->setFile($csvFile, true);

$scp->setMapping($mapping['mapping']);

$scp->setColumns($mapping['columns']);

$ingest_output = $scp->ingestContent();

$error_tips = $scp->getErrorTips();

$error_output = "";

foreach($error_tips as $error_tip)
{
    $error_output .= '<span class="error">'.$error_tip.'</span><br />';
}

$output = $error_output . $ingest_output;

$scp->getLogger()->info($output);

$fh = fopen($csvFile.'.ingest', 'w+');

fwrite($fh, $output);
