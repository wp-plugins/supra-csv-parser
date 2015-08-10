<?php 
require_once('classes/Debug.php');
require_once('classes/Presets.php');
require_once('classes/SupraCsvPostMeta.php');

$pmp = new \SupraCsvFree\SupraCsvPostMetaPreset();
$pm = new \SupraCsvFree\SupraCsvPostMeta();

wp_enqueue_script( 'inputCloner', plugins_url('/js/inputCloner.js', __FILE__) );
wp_enqueue_script( 'base_preset', plugins_url('/js/base_preset.js', __FILE__) );
wp_enqueue_script( 'postmeta_preset', plugins_url('/js/postmeta_preset.js', __FILE__) );

$postmetas = null;

$option = get_option('scsv_postmeta');
$csvpost = get_option('scsv_post');

$suggestions = $pm->getSuggestions($csvpost['type']);

if(!empty($option)) $postmetas = get_option('scsv_postmeta');
?>
<div class="wrap_scsv">
<div id="flash"></div>
    <h3><span id="postinfo_tt" class="tooltip"></span>Post Info</h3>

    <h3><span id="pmpresets_tt" class="tooltip"></span>Preset Configuration</h3>

    <div id="postmeta_preset"><div id="scsv_form" style="width: 300px;"><?php echo $pmp->getForm();?></div></div>

    <h3><span id="pmmapping_tt" class="tooltip"></span>Mapping Configuration</h3>
    <form id="supra_csv_postmeta_form"><?php echo $pm->getFormContents($postmetas,$suggestions)?></form>
    <h3><span id="pmsuggest_tt" class="tooltip"></span>Post Meta Suggestions for Post Type '<?php echo $csvpost['type']?>'</h3>

        <table id="postmeta_suggestions">
          <thead>
            <tr>
            <th>meta key</th>
            <th>random value</th>
            </tr> 
          </thead>
          <tbody>
          <?php
          foreach($suggestions as $i=>$suggestion):
              echo '<tr ';
              echo ($i%2 == 0)?'class="even"':'class="odd"';
              echo '><td>'.$suggestion->meta_key.'</td><td>'.$suggestion->meta_value.'</td></tr>';
          endforeach;
          ?>
          </tbody>
        </table>
</div>
