<?php
namespace SupraCsvFree;

require_once('hooks/SupraCsvRowHooks.php');
require_once('hooks/SupraCsvIngestionHooks.php');
 
class SupraCsvHookManager { 

    function __construct($dependencies) {

        $currentClassName = $this->getCurrentClassName();

        $this->dependencies[$currentClassName] = $dependencies;
    }

    public function setLogger($logger)
    {
        $this->logger = $logger;
    }

    protected function getDependencies() {

        return $this->dependencies;
    }

    protected function getHooks() {

        $methods = array_diff(get_class_methods($this),get_class_methods(get_parent_class($this)));

        $matches = array();

        foreach($methods as $v) {
            if(substr($v, 0, 4) == "hook") {
                $matches[] = $v;
            }
        }
       
        return $matches;
    }

    /**
     * callHooks
     *
     * example of values passed to ingestion hooks:
     *
     * [2015-08-01 22:53:12.801593] [info] array (
             'i' => 
             array (
                 'post_id' => NULL,
                 'post_title' => 'ZZB - Zahnmedizinisches Zentrum Berlin',
                 'post_content' => 'Bahnhofstr. 9, 12305 Berlin-Lichtenrade',
                 'post_type' => NULL,
                 'post_status' => NULL,
                 'post_author' => NULL,
                 'post_password' => NULL,
                 'post_excerpt' => NULL,
                 'post_date' => NULL,
                 'post_date_gmt' => NULL,
                 'post_thumbnail' => NULL,
                 'comment_status' => NULL,
                 'ping_status' => NULL,
                 'post_format' => NULL,
                 'enclosure' => NULL,
                 'post_parent' => NULL,
                 'menu_order' => NULL,
                 'terms' => 
                 array (
                 ),
                 'terms_names' => 
                 array (
                 ),
                 'custom_fields' => 
                 array (
                     '_geo_city' => 'Berlin-Lichtenrade',
                 ),
                 'attachments' => NULL,
                 'blog_id' => 1,
             ),
             'o' => '223',
         )
     *
     *
     * example of values passed to row hooks:
     *
     *
         array (
             0 => 'ZZB - Zahnmedizinisches Zentrum Berlin',
             1 => 'Bahnhofstr. 9, 12305 Berlin-Lichtenrade',
             2 => 'Bahnhofstr. 9',
             3 => 'Berlin-Lichtenrade',
             4 => '12305',
             5 => '12|18',
             6 => 'http://www.zzb.de',
             7 => 'info@zzb.de',
             8 => ' ',
         )
         *
     * @param mixed $input 
     * @param mixed $output 
     * @access public
     * @return void
     */
    public function callHooks($input,$output = null) {

        $results = null;

        $currentClassName = $this->getCurrentClassName();

        switch($currentClassName) {
            case 'SupraCsvIngestionHooks':

                foreach($this->getHooks() as $hook) {
                    $results[$hook] = $this->$hook($input,$output);
                }
            break;
            case 'SupraCsvRowHooks':
                foreach($this->getHooks() as $hook) {
                    $input = $this->$hook($input);
                }
                $results = $input;
            break;
        }
 
        return $results;
    }

    /**
     * getCurrentClassName
     * 
     * Namespaces are a pain in the arse
     *
     * @access protected
     * @return void
     */
    protected function getCurrentClassName()
    {
        $currentClassName = get_class($this);

        $currentClassSegments = explode('\\', $currentClassName);

        if(is_array($currentClassSegments))
        {
            $currentClassName = end( $currentClassSegments );
        }

        return $currentClassName; 
    }

    public function hasHooks() {
    
        return count($this->getHooks()) > 0;
    }
}
