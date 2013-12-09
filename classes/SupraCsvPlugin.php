<?php
require_once(dirname(__FILE__).'/SupraCsvDBAL.php');
require_once(dirname(__FILE__).'/../SupraCsvParser_Plugin.php');

class SupraCsvPlugin {

    public $dbal = false;
    private $download_link = 'www.supraliminalsolutions.com/blog/listings/supra-csv/';

    //set the DBAL instance
    public function __construct() {
           $this->dbal = new SupraCsvDBAL(DB_NAME,DB_HOST,DB_USER,DB_PASSWORD);
           $this->plugin = new SupraCsvParser_Plugin();
           $this->setPluginName();
    }

    private function setPluginName() {
      $arr = array_reverse(split('/', dirname(__FILE__)));
      $this->plugin_name = $arr[1];
    }

    public function getPresetsTable() {
        return $this->plugin->getPresetsTable();
    }

    public function getPluginDirUrl() {
        return WP_PLUGIN_URL . '/' . $this->plugin_name .'/';
    }

    public function getCsvDir() {
        return WP_CONTENT_DIR . '/uploads/' . $this->plugin_name .'/'. 'csv' . '/';
    }

    public function getSampleCsvDir() {

        return $this->plugin->getSampleCsvDir();
    }

    public function getCsvDirUrl() {
        return WP_CONTENT_URL . '/uploads/' . $this->plugin_name .'/'. 'csv' . '/';
    }

    public function getPremiumLink($target,$text) {
        return '<a href="http://'.$target.'" target="_blank">'.$text.'</a>';
    }

    public function upgradeToPremiumMsg($reason=null) {
        return '<span class="error">Upgrade to '.$this->getPremiumLink($this->download_link,'premium').' to '.$reason.'</span>';
    }
}
