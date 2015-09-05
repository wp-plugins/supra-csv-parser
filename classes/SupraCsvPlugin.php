<?php
namespace SupraCsvFree;

use Katzgrau\KLogger\Logger;
use Goodby\CSV\Import\Standard\Interpreter;
use Goodby\CSV\Import\Standard\Lexer;
use Goodby\CSV\Import\Standard\LexerConfig;


require_once(dirname(__FILE__) . '/../vendor/autoload.php');
require_once(dirname(__FILE__).'/SupraCsvDBAL.php');
require_once(dirname(__FILE__).'/../SupraCsvParser_Plugin.php');

if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
{
    define("DIR_SEP", "\\");
}
else
{
    define("DIR_SEP", "/");
}

class SupraCsvPlugin {

    public $dbal = false;
    private static $download_link = 'www.supraliminalsolutions.com/blog/listings/supra-csv/';
    private static $csv_dir = "csv";
    private static $logs_dir = "logs";
    private static $extracted_csv_dir = "extracted-csv";
    private static $chunks_dir = "chunks";

    protected $settingsResolver;
    protected $settings;
    protected $logger;
    protected $error_tips = array();

    public function __construct() {
        $this->dbal = new SupraCsvDBAL(DB_NAME,DB_HOST,DB_USER,DB_PASSWORD);
        $this->plugin = new \SupraCsvParser_Plugin();
        $this->setPluginName();
        $this->logger = new Logger($this->getPluginLogsDir());
        ini_set("log_errors", 1);
        ini_set("error_log", $this->logger->getLogFilePath());
        ini_set("display_errors" , "0");
   }

    public function getErrorTips()
    {
        return $this->error_tips;
    }

    public function getLogger()
    {
        return $this->logger;
    }

    private function setPluginName() {
        $arr = array_reverse(explode(DIR_SEP, dirname(__FILE__)));
        $this->plugin_name = $arr[1];
    }

    public function getPresetsTable() {
        return $this->plugin->getPresetsTable();
    }

    protected function setSettings($settings = array())
    {
        $this->settings = $settings;
    }

    public function getSettings()
    {
        if(!empty($this->settings))
        {
            return $this->settings;
        }

        $setting_keys = array(
            'scsv_has_hooks',
            'scsv_post',
            'scsv_parse_terms',
            'scsv_custom_terms',
            'scsv_postmeta',
            'scsv_misc_options',
            'scsv_ingest_debugger',
            'scsv_report_issue',
            'scsv_user',
            'scsv_encode_special_chars',
            'scsv_csv_settings',
            'scsv_additional_csv_settings'
        );

        foreach($setting_keys as $setting_key)
        {
            $settings[$setting_key] = get_option($setting_key);
        }

        return $settings;
    }

    public function setSettingsResolver($settingsResolver)
    {
        $this->settingsResolver = $settingsResolver;
    }

    public function getSetting($setting_key)
    {
        if(empty($this->settingsResolver))
        {
            $this->settingsResolver = (function($setting_key) {
                    return get_option($setting_key);
            });

            return $this->getSetting($setting_key);
        }
        else
        {
            $settingsResolver = $this->settingsResolver;

            $setting = $settingsResolver($setting_key);
        }

        return $setting;
    }

    public function _get_scsv_settings()
    {
        $csv_settings = $this->getSetting('scsv_csv_settings');

        //$this->logger->info(__METHOD__ . var_export($csv_settings, true));

        foreach($csv_settings as $key=>$csv_setting)
        {
            $value = html_entity_decode($csv_setting);

            $value = stripslashes($value);

            $converted[$key] = $value;
        }

        return $converted;
    }

    protected function parseLines($file) {

        $interpreter = new Interpreter();
        
        $interpreter->unstrict(); // Ignore row column count consistency
        
        $config = new LexerConfig();

        $csv_settings = $this->_get_scsv_settings();

        $config
            ->setDelimiter($csv_settings['delimiter']) // Customize delimiter. Default value is comma(,)
            ->setEnclosure($csv_settings['enclosure'])  // Customize enclosure. Default value is double quotation(")
            ->setEscape($csv_settings['escape'])    // Customize escape character. Default value is backslash(\)
        ;

        $lexer = new Lexer($config);

        $output = array();

        $interpreter->addObserver(function($columns) use(&$output) {
        
            $output[] = $columns;
        });

        $nextLines = $lexer->parse($file, $interpreter);
    
        return $output;
    }

    protected function getPostTypeTaxonomies()
    {
        $csvpost = $this->getSetting('scsv_post');
        $post_type = $csvpost['type'];

        //this is for mocking
        if(function_exists('get_object_taxonomies'))
        {
            $post_type_taxonomies = get_object_taxonomies($post_type, 'objects');
        }
        else
        {
            $post_type_taxonomies = "*";
        }

        return $post_type_taxonomies;
    }

    public function getPluginDirUrl() {
        return WP_PLUGIN_URL . DIR_SEP . $this->plugin_name . DIR_SEP;
    }

    protected function getPluginRelUploadsDir() {
        return DIR_SEP . 'uploads' . DIR_SEP . $this->plugin_name . DIR_SEP;
    }

    protected function getPluginUploadsDir() {
        return WP_CONTENT_DIR . $this->getPluginRelUploadsDir();
    }

    protected function getPluginUploadsDirUrl() {
        return WP_CONTENT_URL . $this->getPluginRelUploadsDir();
    }

    public function getPluginBasePath()
    {
        return dirname(__FILE__) . '/../';
    }

    protected function getPluginLogsDir() {
        return $this->getPluginBasePath() . self::$logs_dir . DIR_SEP;
    }

    protected function getPluginLogsDirUrl() {
        return $this->getPluginDirUrl() . self::$logs_dir . DIR_SEP;
    }

    public function getPluginChunkDir() {
        return $this->getPluginBasePath() . self::$chunks_dir . DIR_SEP;
    }

    public function getCsvDir() {
        return $this->getPluginUploadsDir() . self::$csv_dir . DIR_SEP;
    }

    public function getExtractedCsvDir() {
        return $this->getPluginUploadsDir() . self::$extracted_csv_dir . DIR_SEP;
    }

    public function getSampleCsvDir() {
        return $this->plugin->getSampleCsvDir();
    }

    public function getCsvDirUrl() {
        return $this->getPluginUploadsDirUrl() . self::$csv_dir . DIR_SEP;
    }

    public function getExtractedCsvDirUrl() {
        return $this->getPluginUploadsDirUrl() . self::$extracted_csv_dir . DIR_SEP;
    }

    public function getPremiumLink($target,$text) {
        return '<a href="http://'.$target.'" target="_blank">'.$text.'</a>';
    }

    public function upgradeToPremiumMsg($reason=null) {
    
        $premium_link = $this->getPremiumLink(self::$download_link,'premium');

        return '<span class="error">Upgrade to '.$premium_link.' to '.$reason.'</span>';
    }
}
