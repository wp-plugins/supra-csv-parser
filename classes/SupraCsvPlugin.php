<?php
require_once(dirname(__FILE__).'/SupraCsvDBAL.php');
require_once(dirname(__FILE__).'/../SupraCsvParser_Plugin.php');

if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
  define("DIR_SEP", "\\");
else
  define("DIR_SEP", "/");

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
      $arr = array_reverse(explode(DIR_SEP, dirname(__FILE__)));
      $this->plugin_name = $arr[1];
    }

    public function getPresetsTable() {
        return $this->plugin->getPresetsTable();
    }

    public function _get_scsv_settings()
    {
        $csv_settings = get_option('scsv_csv_settings');

        foreach($csv_settings as $key=>$csv_setting)
        {
            $value = html_entity_decode($csv_setting);

            $value = stripslashes($value);
            
            $converted[$key] = $value;
        }

        return $converted;
    }

    protected function parseNextLine($handle) {

        $csv_settings = $this->_get_scsv_settings();

        $additional_csv_settings = get_option('scsv_addtional_csv_settings');

        if (strnatcmp(phpversion(),'5.3') >= 0) {
            return fgetcsv($handle, $additional_csv_settings['line_maxlen'], $csv_settings['delimiter'], $csv_settings['enclosure'], $csv_settings['escape']);

        }
        else {
            return fgetcsv($handle,$additional_csv_settings['line_maxlen'], $csv_settings['delimiter'], $csv_settings['enclosure']);
        }
    }

    public function getPluginDirUrl() {
        return WP_PLUGIN_URL . '/' . $this->plugin_name .'/';
    }

    private function getPluginRelUploadsDir() {
        return DIR_SEP . 'uploads' . DIR_SEP . $this->plugin_name . DIR_SEP;
    }

    private function getPluginUploadsDir() {
        return WP_CONTENT_DIR . $this->getPluginRelUploadsDir();
    }

    private function getPluginUploadsDirUrl() {
        return WP_CONTENT_URL . $this->getPluginRelUploadsDir();
    }

    public function getCsvDir() {
        return $this->getPluginUploadsDir() . $this->csv_dir . DIR_SEP;
    }

    public function getExtractedCsvDir() {
        return $this->getPluginUploadsDir() . $this->extracted_csv_dir . DIR_SEP;
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
