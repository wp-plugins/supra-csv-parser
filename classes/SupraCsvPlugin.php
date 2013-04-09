<?php
require_once(dirname(__FILE__).'/SupraCsvDBAL.php');
require_once(dirname(__FILE__).'/../SupraCsvParser_Plugin.php');

class SupraCsvPlugin {

    private $plugin_name = 'supra-csv-parser';
    public $dbal = false;
    private $download_link = 'www.supraliminalsolutions.com/blog/downloads/supra-csv-premium/';

    //set the DBAL instance
    public function __construct() {
           $this->dbal = new SupraCsvDBAL(DB_NAME,DB_HOST,DB_USER,DB_PASSWORD);
           $this->plugin = new SupraCsvParser_Plugin();
    }

    public function getPresetsTable() {
        return $this->plugin->getPresetsTable();
    }

    public function getPluginDirUrl() {
        return WP_PLUGIN_URL . '/' . $this->plugin_name .'/';
    }

    public function getCsvDir() {
        return $this->plugin->getCsvDir();
    }

    public function getImgDir() {
        return $this->plugin->getImgDir();
    }

    public function getCsvDirUrl() {
        return $this->plugin->getCsvDirUrl();
    }

    public function getPremiumLink($target,$text) {
        return '<a href="http://'.$target.'" target="_blank">'.$text.'</a>';
    }

    public function upgradeToPremiumMsg($reason=null) {
        return '<span class="error">Upgrade to '.$this->getPremiumLink($this->download_link,'premium').' to '.$reason.'</span>';
    }
}
