<?php 
require_once(dirname(__FILE__).'/classes/UploadCsv.php');
$uc = new UploadCsv($_FILES);
wp_enqueue_script( 'misc', plugins_url('/js/misc.js', __FILE__) );
?>

<h4>Downloads Sample Csv's <a href="<?=plugins_url('/samplecsvs.zip', __FILE__)?>">Here</a></h4>

<div id="supra_csv_upload_forms" class="wrap_scsv" style="width: 550px;">
    <?php $uc->renderForms();?>
</div>

