<?php
require_once("Debug.php");
require_once('CsvLib.php');
require_once('RemotePost.php');

class IngestCsv {

    function ParseAndMap($filename) {
        $cp = new CsvParser($filename);
        $mf = new MapperForm($cp);
    }

    function ingest($params) {
        $cp = new CsvParser($params['filename']);
        $cp->ingestContent($params['mapping']);
    }
}
