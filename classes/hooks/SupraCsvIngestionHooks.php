<?php

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

    function hookSetLastPostId($i, $o) {
   
        $this->last_post_id = $o;
    }

    //this isn't a hook notice the lack of prefix hook
    function getLastPostId() {

        return $this->last_post_id;
    }
}

