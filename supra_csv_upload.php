<?php 
require_once(dirname(__FILE__).'/classes/UploadCsv.php');
$uc = new UploadCsv($_FILES);
?>
<div id="supra_csv_upload_forms">
    <?$uc->renderForms();?>
</div>

