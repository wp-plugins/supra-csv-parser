<?php
require_once('hooks/SupraCsvRowHooks.php');
require_once('hooks/SupraCsvIngestionHooks.php');
 
class SupraCsvHookManager { 

    function __construct($dependencies) {

        $this->dependencies[get_class($this)] = $dependencies;
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

    public function callHooks($input,$output = null) {

        $results = null;

        switch(get_class($this)) {
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

    public function hasHooks() {
    
        return count($this->getHooks()) > 0;
    }
}
