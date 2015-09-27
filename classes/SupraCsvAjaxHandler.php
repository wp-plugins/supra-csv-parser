<?php
namespace SupraCsvFree;
require_once(dirname(__FILE__).'/UploadCsv.php');
require_once(dirname(__FILE__).'/ExtractCsv.php');
require_once(dirname(__FILE__).'/IngestCsv.php');
require_once(dirname(__FILE__).'/Debug.php');
require_once(dirname(__FILE__).'/Presets.php');
require_once(dirname(__FILE__).'/SupraCsvPlugin.php');
require_once(dirname(__FILE__).'/SupraCsvPostMeta.php');
require_once(dirname(__FILE__).'/SupraCsvExtractor.php');
require_once(dirname(__FILE__).'/CsvLib.php');
require_once(dirname(__FILE__).'/SupraCsvLogs.php');

class SupraCsvAjaxHandler extends SupraCsvPlugin {

    function __construct($request) {

        session_start();

        parent::__construct();

        $uc = new UploadCsv();
        $xc = new ExtractCsv();
        $ic = new IngestCsv();
        $sl = new SupraCsvLogs();

        $scp = new SupraCsvParser();

        $settingsResolver = (function($setting_key) {
            return get_option($setting_key);
        });

        $scp->setSettingsResolver($settingsResolver);

        $scp->init();

        $ic->setSupraCsvParser($scp); 

        $xc->setSettingsResolver($settingsResolver);

        $uc->setSettingsResolver($settingsResolver);

        switch($request['command']) {
        case "delete_file":
            $uc->deleteFileByKey($request['args']);
            break;
        case "download_file":
            $uc->downloadFile($request['args']);
            break;
        case "delete_log":
            $sl->deleteFileByKey($request['args']);
            break;
        case "download_log":
            $sl->downloadFile($request['args']);
            break;
        case "debug_file":
            $uc->debugFile($request['args']);
            break;
        case "delete_extract_file":
            $xc->deleteFileByKey($request['args']);
            break;
        case "download_extract_file":
            $xc->downloadFile($request['args']);
            break;
        case "select_ingest_file":
            $filename = $uc->getFileByKey($request['args']);
            $result['map'] = $ic->parseAndMap($filename);
            $mp = new SupraCsvMappingPreset($filename);
            $result['preset'] = $mp->getForm();
            $result['error_tips'] = $ic->getSupraCsvParser()->getErrorTips();
            echo json_encode($result);
            break;
        case "ingest_file":
 
            $mapping = array();
            parse_str($request['args']['data'], $mapping);
            $params['mapping']  = $mapping;
            $params['filename'] = $request['args']['filename'];
            $result = $ic->ingest($params);
            $errors = $ic->getSupraCsvParser()->getErrorTips();

            if(is_object($result))
            {
                $chunk_namespace = $result->getChunkNamespace();

                echo json_encode(compact('chunk_namespace'), true);
            }
            else
            {
                echo json_encode(compact('result','errors'), true);
            }

            break;
        case "poll_ingestion_completion":
            
            $chunkNamespace = $request['args']['data'];

            if(!empty($chunkNamespace))
            {
                $output = $ic->pollIngestionCompletion($chunkNamespace);
                $errors = $ic->getSupraCsvParser()->getErrorTips();

                echo json_encode(compact('output','errors'));
            }

            break;
        case "select_mapping_preset":
            $p = new SupraCsvPreset();
            echo json_encode($p->getPreset($request['args']));
            break;
        case "create_mapping_preset":
            $filename = $uc->getFileByKey($request['args']['filename']);
            $mp = new SupraCsvMappingPreset($filename);
            $mapping = array();
            parse_str($request['args']['preset'], $mapping);
            echo json_encode($mp->savePreset(array('preset'=>$mapping,'preset_name'=>$request['args']['preset_name'])));
            break;
        case "update_mapping_preset":
            $filename = $uc->getFileByKey($request['args']['filename']);
            $mp = new SupraCsvMappingPreset($filename);
            $mapping = array();
            parse_str($request['args']['preset'], $mapping);
            echo json_encode($mp->savePreset(array(
                'preset_id'=>$request['args']['preset_id'],
                'preset_name'=>$request['args']['preset_name'],
                'preset'=>$mapping
            )));
            break;
        case "select_postmeta_preset":
            $pmp = new SupraCsvPostMetaPreset();
            $pm = new SupraCsvPostMeta();
            $preset = $pmp->getPreset($request['args']);
            $postMetas = $preset['preset'];
            update_option('scsv_postmeta',$postMetas);
            $csvpost = $scp->getSetting('scsv_post');
            $suggestions = $pm->getSuggestions($csvpost['type']);
            $preset = array_merge($preset,array('form'=>$pm->getFormContents($postMetas,$suggestions)));
            echo json_encode($preset);
            break;
        case "delete_mapping_preset":
        case "delete_postmeta_preset":
            $p = new SupraCsvPreset();
            $p->deletePreset($request['args']);
            break;
        case "create_postmeta_preset":
            $mp = new SupraCsvPostMetaPreset();
            $postmetas = array();
            parse_str($request['args']['preset'], $postmetas);
            echo json_encode($mp->savePreset(array('preset'=>$postmetas,'preset_name'=>$request['args']['preset_name'])));
            break;
        case "update_postmeta_preset":
            $mp = new SupraCsvPostMetaPreset();
            $mapping = array();
            parse_str($request['args']['preset'], $mapping);
            echo json_encode($mp->savePreset(array(
                'preset_id'=>$request['args']['preset_id'],
                'preset_name'=>$request['args']['preset_name'],
                'preset'=>$mapping
            )));
            break;
        case "extract_and_preview":
            parse_str($_POST['args'], $query_args);
            $sce = new SupraCsvExtractor($query_args);
            echo $sce->displayExtractedPosts();
            break;
        case "extract_and_export":
            parse_str($_POST['args'], $query_args);
            $sce = new SupraCsvExtractor($query_args);
            $posts = $sce->getPostsAndDetails(); 
            //var_dump($query_args);
            $scex = new SupraCsvExporter($posts,$query_args);

            $content = $scex->download($query_args);

            $filename = 'ingest-'.date('y-m-d-h-i-s-a') .'.csv';

            $success = $xc->writeToFile($filename,$content);
            echo json_encode(array('extracted'=>$content,'success'=>$success,'premium'=>$this->upgradeToPremiumMsg('export more than 1 row'),'filename'=>$filename));

            break;
        case "get_extracted_form":
            echo json_encode(array('html'=>$xc->getForms()));
            break;
        case "get_tooltips":
            include(dirname(__FILE__) . '/../supra_csv_docs.php');
            break;
        }
    }
}
