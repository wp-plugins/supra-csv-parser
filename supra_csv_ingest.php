<?
require_once(dirname(__FILE__).'/classes/IngestCsv.php');
require_once(dirname(__FILE__).'/classes/UploadCsv.php');
$uc = new UploadCsv();
$uc->displayFileSelector();
?>
<div id="supra_csv_ingestion_mapper"></div>
<div id="supra_csv_ingestion_log">
   <img id="patience" src="<?=$uc->getPluginDirUrl()?>/img/patience.gif" style="display:none"/>
</div>

