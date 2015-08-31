<?php
include_once('SupraCsvParser_LifeCycle.php');

class SupraCsvParser_Plugin extends SupraCsvParser_LifeCycle {
 
    private $preset_table;
    /**
     * See: http://plugin.michael-simpson.com/?page_id=31
     * @return array of option meta data.
     */
    public function getOptionMetaData() {
        //  http://plugin.michael-simpson.com/?page_id=31
        return array(
            //'_version' => array('Installed Version'), // Leave this one commented-out. Uncomment to test upgrades.
        );
    }

    public function getPluginDisplayName() {
        return 'Supra Csv Importer';
    }

    protected function getMainPluginFileName() {
        return 'supra-csv-parser.php';
    }

    public function getPluginNameDehumanized() {
        return 'supra-csv-parser';
    }

    public function getPresetsTable() {

        if(empty($this->preset_table)) 
            $this->preset_table = $this->prefixTableName('presets');

        return $this->preset_table;
    }

    /**
     * See: http://plugin.michael-simpson.com/?page_id=101
     * Called by install() to create any database tables if needed.
     * Best Practice:
     * (1) Prefix all table names with $wpdb->prefix
     * (2) make table names lower case only
     * @return void
     */
    protected function installDatabaseTables() {
                global $wpdb;
                $preset_table= $this->getPresetsTable(); 

                $presetsSql = "
                CREATE TABLE IF NOT EXISTS `$preset_table` (
                `id` int(8) NOT NULL AUTO_INCREMENT,
                `preset_name` varchar(64) NOT NULL,
                `preset_type` varchar(64) NOT NULL,
                `preset` longtext NOT NULL,
                PRIMARY KEY (`id`)
                );";

                $wpdb->query($presetsSql);
    }

    public function getSampleCsvDir() {

        return dirname(__FILE__) . '/samplecsvs/';
    }

    public function getImgDir() {
        return WP_CONTENT_DIR . '/uploads/' . $this->getPluginNameDehumanized() .'/img/';
    }
 
    public function getCsvDir() {
        return WP_CONTENT_DIR . '/uploads/' . $this->getPluginNameDehumanized() .'/csv/';
    }

 
    protected function createFileSystem() {

        if(!file_exists($this->getImgDir())) {
            mkdir($this->getImgDir(),0777,true);
            chmod($this->getImgDir(),0777);
        }
        if(!file_exists($this->getCsvDir())) {
            mkdir($this->getCsvDir(),0777,true);
            chmod($this->getCsvDir(),0777);
        }

        $this->createSampleFiles();
    }

    public function createSampleFiles() {

        $source = $this->getSampleCsvDir();
        $dest = $this->getCsvDir();

        if(is_dir($source)) {
            $dir_handle=opendir($source);
            $sourcefolder = basename($source);
            while($file=readdir($dir_handle)){
                if($file!="." && $file!=".."){
                    if(is_dir($source."/".$file)){
                        self::copyr($source."/".$file, $dest."/".$sourcefolder);
                    } else {
                        copy($source."/".$file, $dest."/".$file);
                    }
                }
            }
            closedir($dir_handle);
        } else {
            // can also handle simple copy commands
            copy($source, $dest);
        }
    }

    /**
     * See: http://plugin.michael-simpson.com/?page_id=101
     * Drop plugin-created tables on uninstall.
     * @return void
     */
    protected function unInstallDatabaseTables() {
                global $wpdb;
                $tables[] = $this->getPresetsTable();
                foreach($tables as $table) {
                    $wpdb->query("DROP TABLE IF EXISTS `$table`");
                }
    }

    /**
     * Perform actions when upgrading from version X to version Y
     * See: http://plugin.michael-simpson.com/?page_id=35
     * @return void
     */
    public function upgrade() {
    }


    //page-factory
    public function __call($name, $arguments)
    {
        $callable = array('home','admin','ingest','postmeta','upload','docs','export','logs');
 
        foreach($callable as $called) {
            if( substr($name,0,5) == "scsv_" && strstr($name,$called)) {
                require_once(dirname(__FILE__) . '/supra_csv_' . $called . '.php');
                break;
            }
        }
    }
    public function callAdminActions() {
        add_menu_page("Supra CSV", "Supra CSV", "manage_options", "supra_csv", array(&$this,"scsv_home"));
        add_submenu_page("supra_csv", "Docs", "Docs", "manage_options", "supra_csv_docs", array(&$this,"scsv_docs"));
        add_submenu_page("supra_csv", "Configuration", "Configuration", "manage_options", "supra_csv_admin", array(&$this,"scsv_admin"));
        add_submenu_page("supra_csv", "Upload", "Upload", "manage_options", "supra_csv_upload", array(&$this,"scsv_upload"));
        add_submenu_page("supra_csv", "Post Info", "Post Info", "manage_options", "supra_csv_postmeta", array(&$this,"scsv_postmeta"));
        add_submenu_page("supra_csv", "Ingestion", "Ingestion", "manage_options", "supra_csv_ingest", array(&$this,"scsv_ingest"));
        add_submenu_page("supra_csv", "Extraction", "Extraction", "manage_options", "supra_csv_export", array(&$this,"scsv_export"));
        add_submenu_page("supra_csv", "Logs", "Logs", "manage_options", "supra_csv_logs", array(&$this,"scsv_logs"));
    }

    public function supraCsvAjax() {
        require_once(dirname(__FILE__).'/classes/SupraCsvAjaxHandler.php');
        $ah = new \SupraCsvFree\SupraCsvAjaxHandler($_REQUEST);
        die();    
    }

    function supracsv_enqueue_scripts() {
        wp_enqueue_style('my-style', plugins_url('/css/style.css', __FILE__));
        wp_enqueue_script('jquery');
        wp_enqueue_script('supra_csv_globals', plugins_url('/js/global.js', __FILE__));
        wp_enqueue_script('toolip-lib', plugins_url('/js/jquery.qtip-1.0.0-rc3.min.js', __FILE__));
        wp_enqueue_script('toolip', plugins_url('/js/tooltip.js', __FILE__));
    }

    public function addActionsAndFilters() {
        add_action('admin_menu', array(&$this, 'callAdminActions'));
        add_action('wp_ajax_supra_csv',array(&$this,'supraCsvAjax'));
        add_action('activated_plugin',array(&$this,'save_error'));
        add_action( 'admin_enqueue_scripts',array(&$this,'supracsv_enqueue_scripts'));
    }

    function save_error(){
        update_option('supracsvplugin_error',  array(
          'details'=>ob_get_contents(),
          'date'=> date('F d, Y h:i:s a', strtotime("now"))
        ));
    }
}
