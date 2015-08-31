<?php
require_once(dirname(__FILE__).'/classes/SupraCsvLogs.php');
$sl = new \SupraCsvFree\SupraCsvLogs();
wp_enqueue_script( 'misc', plugins_url('/js/misc.js', __FILE__) );
wp_enqueue_script( 'tablesorter', plugins_url('/js/jquery.tablesorter.js', __FILE__) );
wp_enqueue_style( 'tablesorter-blue', plugins_url('/css/tablesorter-blue.css', __FILE__) );
?>
<h3>
<span id="logfilemgmt_tt" class="tooltip"></span>
Log File Management
</h3>

<div id="supra_csv_log_forms" class="wrap_scsv" style="width: 550px;">
    <?php $sl->renderForms();?>
</div>

