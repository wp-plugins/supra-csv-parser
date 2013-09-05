<?php
/* 

Runs before row data is sent to be ingested

Here only one parameter of an array of row data is
used an each subsequent method is chained down to return a result

*/

class SupraCsvRowHooks extends SupraCsvHookManager {

/* 
    //this method executes first
    function hookc($row) {

        return $row + 1;
    }

    //this method executes next with an argument of the return value of function c 
    function hookd($row) {

        return $row + 2;
    }
*/

    function hookStoreLastPostId($row) {

        $dep = $this->getDependencies();

        //Debug::show($dep); 

        $row[] = $dep[get_class($this)]['SupraCsvIngestionHooks']->getLastPostId();

        return $row;
    }
}

