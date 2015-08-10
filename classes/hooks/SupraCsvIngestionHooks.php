<?php
namespace SupraCsvFree;


/* Runs in between the execution of every ingested post with access to xmlrpc output */

class SupraCsvIngestionHooks extends SupraCSVHookManager {

    private $last_post_id;


/*
    function hooka($i,$o) {

        return $i . ' ' . $o;
    }

    function hookb($i,$o) {

        return $i . ' ' . $o;
    } 
*/

    /**
     * hookSetLastPostId
     * 
     * @param mixed $i 
     * @param mixed $o 
     * @access public
     * @return void
     */
    function hookSetLastPostId($i, $o) {

        //$this->logger->info(__FUNCTION__);
        //$this->logger->info(var_export(compact('i','o'), true));

        $this->last_post_id = $o;
    }

    //this isn't a hook notice the lack of prefix hook
    function getLastPostId() {

        return $this->last_post_id;
    }
}

