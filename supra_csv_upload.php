<?php 
require_once(dirname(__FILE__).'/classes/UploadCsv.php');
$uc = new \SupraCsvFree\UploadCsv($_FILES);
wp_enqueue_script( 'FileSaver', plugins_url('/js/FileSaver.min.js', __FILE__) );
wp_enqueue_script( 'misc', plugins_url('/js/misc.js', __FILE__) );
wp_enqueue_script( 'tablesorter', plugins_url('/js/jquery.tablesorter.js', __FILE__) );
wp_enqueue_style( 'tablesorter-blue', plugins_url('/css/tablesorter-blue.css', __FILE__) );
?>
<h3>
<span id="filemgmt_tt" class="tooltip"></span>
CSV File Management
</h3>
<h4>Downloads Sample Csv's <a href="<?=plugins_url('/samplecsvs.zip', __FILE__)?>">Here</a></h4>

<div id="supra_csv_upload_forms" class="wrap_scsv" style="width: 550px;">
    <?php $uc->renderForms();?>
</div>

