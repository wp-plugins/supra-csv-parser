<?php
require_once(dirname(__FILE__).'/classes/IngestCsv.php');
require_once(dirname(__FILE__).'/classes/UploadCsv.php');
require_once(dirname(__FILE__).'/classes/Presets.php');

$uc = new \SupraCsvFree\UploadCsv();

$settingsResolver = (function($setting_key) {
    return get_option($setting_key);
});    

$uc->setSettingsResolver($settingsResolver);

wp_enqueue_script( 'base_preset', plugins_url('/js/base_preset.js', __FILE__) ); 
wp_enqueue_script( 'postmeta_preset', plugins_url('/js/mapping_preset.js', __FILE__) ); 
wp_enqueue_script( 'misc', plugins_url('/js/misc.js', __FILE__) ); 
?>
<div class="wrap_scsv">
<div id="flash"></div>
<div id="file_selector">
<span id="selectfile_tt" class="tooltip"></span>
<?php $uc->displayFileSelector();?>
</div>
<div id="preset_container">
  <div id="supra_csv_ingestion_mapper"></div>
  <div id="supra_csv_mapping_preset"></div>
  <div class="clear"></div>
</div>
<div id="ingestion_errors_wrapper">
  <span id="errortips_tt" class="tooltip"></span>
  <h2>Error Tips</h2>
  <ul id="supra_csv_ingestion_errors">
  </ul>
</div>
<div id="supra_csv_ingestion_log">
   <img id="patience" src="<?php echo $uc->getPluginDirUrl()?>/img/patience.gif" style="display:none"/>
</div>
</div>
