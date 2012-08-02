<?php
session_start();
require_once(dirname(__FILE__).'/UploadCsv.php');
require_once(dirname(__FILE__).'/IngestCsv.php');
require_once(dirname(__FILE__).'/Debug.php');
class SupraCsvAjaxHandler {

    //an instance of IngestCsv for the ingestion commands to share

    function __construct($request) {
        $uc = new UploadCsv();
        $ic = new IngestCsv();

        switch($request['command']) {
            case "delete_file":
                $uc->deleteFileByKey($request['args']);
            break;
            case "download_file":
                $uc->downloadFile($request['args']);
            break;
            case "select_ingest_file":
                $filename = $uc->getFileByKey($request['args']);
                $ic->parseAndMap($filename);
            break;
            case "ingest_file":
                $mapping = array();
                parse_str($request['args']['data'], $mapping);
                $params['mapping']  = $mapping;
                $params['filename'] = $request['args']['filename'];
                $ic->ingest($params);
            break;
        }
    }
}
