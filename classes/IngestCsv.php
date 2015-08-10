<?php
namespace SupraCsvFree;
require_once("Debug.php");
require_once('CsvLib.php');
require_once('RemotePost.php');

class IngestCsv {

    public function setSupraCsvParser(SupraCsvParser $supraCsvParser)
    {
        $this->scp = $supraCsvParser;
    }

    public function ParseAndMap($filename) {

        $this->scp->setFile($filename);

        $mf = new SupraCsvMapperForm($this->scp);

        return $mf->getForm();
    }

    public function ingest($params) {

        $this->misc_options = $this->scp->getSetting('scsv_misc_options');

        $this->scp->setFile($params['filename']);

        $mapper = $this->scp->setMapping($params['mapping']);

        return $this->scp->ingestContent();
    }

    public function getSupraCsvParser()
    {
        return $this->scp;
    }
}
