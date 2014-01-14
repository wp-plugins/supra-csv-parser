<?php
require_once(dirname(__FILE__).'/SupraCsvDBAL.php');
require_once(dirname(__FILE__).'/../SupraCsvParser_Plugin.php');

class SupraCsvPlugin {

    public $dbal = false;
    private $download_link = 'www.supraliminalsolutions.com/blog/listings/supra-csv/';
    private $csv_dir = "csv";
    private $extracted_csv_dir = "extracted-csv";

    //set the DBAL instance
    public function __construct() {
           $this->dbal = new SupraCsvDBAL(DB_NAME,DB_HOST,DB_USER,DB_PASSWORD);
           $this->plugin = new SupraCsvParser_Plugin();
           $this->setPluginName();
    }

    private function setPluginName() {
      $arr = array_reverse(explode('/', dirname(__FILE__)));
      $this->plugin_name = $arr[1];
    }

    public function getPresetsTable() {
        return $this->plugin->getPresetsTable();
    }

    public function getPluginDirUrl() {
        return WP_PLUGIN_URL . '/' . $this->plugin_name .'/';
    }

    private function getPluginRelUploadsDir() {
        return '/uploads/' . $this->plugin_name .'/';
    }

    private function getPluginUploadsDir() {
        return WP_CONTENT_DIR . $this->getPluginRelUploadsDir();
    }

    private function getPluginUploadsDirUrl() {
        return WP_CONTENT_URL . $this->getPluginRelUploadsDir();
    }

    public function getCsvDir() {
        return $this->getPluginUploadsDir() . $this->csv_dir . '/';
    }

    public function getExtractedCsvDir() {
        return $this->getPluginUploadsDir() . $this->extracted_csv_dir . '/';
    }

    public function getSampleCsvDir() {
        return $this->plugin->getSampleCsvDir();
    }

    public function getCsvDirUrl() {
        return $this->getPluginUploadsDirUrl() . $this->csv_dir . '/';
    }

    public function getExtractedCsvDirUrl() {
        return $this->getPluginUploadsDirUrl() . $this->extracted_csv_dir . '/';
    }

    public function getPremiumLink($target,$text) {
        return '<a href="http://'.$target.'" target="_blank">'.$text.'</a>';
    }

    public function upgradeToPremiumMsg($reason=null) {
        return '<span class="error">Upgrade to '.$this->getPremiumLink($this->download_link,'premium').' to '.$reason.'</span>';
    }
}
