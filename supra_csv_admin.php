<?php
require_once(dirname(__FILE__).'/classes/SupraCsvPlugin.php');

$scp = new \SupraCsvFree\SupraCsvPlugin();
    
$scp->setSettingsResolver(function($setting_key) {
    return get_option($setting_key);
});

if(!empty($_POST['scsv_submit'])) {
    $csvuser['name'] = $_POST['scsv_wpname'];
    $csvuser['pass'] = $_POST['scsv_wppass'];
    $csvpost['publish'] = $_POST['scsv_autopub'];
    $csvpost['type'] = (empty($_POST['scsv_posttype'])) ? $_POST['scsv_custom_posttype'] : $_POST['scsv_posttype'];
    $csvpost['title'] = $_POST['scsv_defaulttitle'];
    $csvpost['desc'] = $_POST['scsv_defaultdesc'];
    $additional_csv_settings['line_maxlen'] = $_POST['scsv_line_maxlen'];
    $post_terms = $_POST['scsv_custom_terms'];
    $parse_terms = $_POST['scsv_parse_terms'];
    $ingest_debugger = $_POST['scsv_ingest_debugger'];
    $csv_settings = $_POST['scsv_csv_settings'];
    $encode_chars = $_POST['scsv_encode_special_chars'];
    $has_hooks = $_POST['scsv_has_hooks'];

    $misc_options_keys = array(
        'scsv_is_ingestion_chunked',
        'scsv_chunk_by_n_rows',
        'scsv_are_revisions_skipped',
        'scsv_is_using_multithreads'
    );

    foreach($misc_options_keys as $misc_options_key)
    {
        $option_key_name = str_replace('scsv_', '', $misc_options_key);

        $misc_options[$option_key_name] = $_POST[$misc_options_key];
    }

    update_option('scsv_misc_options', $misc_options);
    update_option('scsv_user', $csvuser);
    update_option('scsv_post', $csvpost);
    update_option('scsv_custom_terms', $post_terms);
    update_option('scsv_parse_terms', $parse_terms);
    update_option('scsv_ingest_debugger', $ingest_debugger);
    update_option('scsv_csv_settings', $csv_settings);
    update_option('scsv_additional_csv_settings', $additional_csv_settings);
    update_option('scsv_encode_special_chars',$encode_chars);
    update_option('scsv_has_hooks',$has_hooks);
    echo '<div class="updated"><p><strong>Configuration saved</strong></p></div>';
    $csv_settings = $scp->_get_scsv_settings();
} else {
    $csvfile = get_option('scsv_filename');
    $csvuser = get_option('scsv_user');
    $csvpost = get_option('scsv_post');
    $post_terms = get_option('scsv_custom_terms');
    $parse_terms = get_option('scsv_parse_terms');
    $ingest_debugger = get_option('scsv_ingest_debugger');
    $csv_settings = $scp->_get_scsv_settings();
    $additional_csv_settings = get_option('scsv_additional_csv_settings');
    $encode_chars = get_option('scsv_encode_special_chars');
    $has_hooks = get_option('scsv_has_hooks');

    $misc_options = get_option('scsv_misc_options');
}

?>
<div class="wrap_scsv" style="width: 630px">
<h2>Supra CSV Configuration</h2>
        <hr />
<div style="float: left; width: 300px;">
<form name="scsv_form" method="post">
        <h3><span id="usersettings_tt" class="tooltip"></span>User Settings</h3>
        <p>Username<input type="text" name="scsv_wpname" value="<?php echo $csvuser['name']; ?>" size="20"></p>
        <p>Pasword<input type="password" name="scsv_wppass" value="<?php echo $csvuser['pass']; ?>" size="20"></p>

        <hr />
        <h3><span id="postsettings_tt" class="tooltip"></span>Post Settings</h3>
        <p>
            <span id="autopublish_tt" class="tooltip"></span>Auto Publish
            <select name="scsv_autopub">
                <option value="0">false</option>
                <option value="1" <?php if($csvpost['publish']) echo 'selected="selected"';?>>true</option>
            </select>
        </p>
        <p>
            <span id="posttype_tt" class="tooltip"></span>Post Type
            <select name="scsv_posttype" value="<?php echo $csvpost['type']; ?>">
                <option value=""></option>
                <option value="post" value="post" selected>post</option>
                <option value="page" value="page">page</option>
                <option value="attachment" value="attachment">attachment</option>
                <option value="nav_menu_item" value"nav_menu_item">nav_menu_item</option>
            </select>
        </p>
        <p style="text-align: center">
            <b>or</b>
        </p>
        <p>
            <span id="customposttype_tt" class="tooltip"></span>Custom Post Type <span class="premium_only">(Premium Only)</span>
            <input type="text" name="scsv_custom_posttype" value="" size="5" style="background-color: #FDEEF4" disabled>
        </p>
        <h3><span id="postdefaults_tt" class="tooltip"></span>Post Defaults</h3>
        <p>Default Title<input type="text" name="scsv_defaulttitle" value="<?php echo $csvpost['title']; ?>" size="20"></p>
        <p>Default Description<textarea name="scsv_defaultdesc" id="scsv_defaultdesc"><?php echo $csvpost['desc']; ?></textarea></p>

        <h3>Performance</h3>

          <p>
            <span id="arerevisionsskipped_tt" class="tooltip"></span>
            Skip Revisions<input type="checkbox" name="scsv_are_revisions_skipped" value="1" <? echo ($misc_options['are_revisions_skipped'])?'checked="checked"':"";?>/>
          </p>

          <p>
            <span id="isingestionchunked_tt" class="tooltip"></span>
            Chunk Ingestion<span class="premium_only">(Premium Only)</span><input type="checkbox" name="scsv_is_ingestion_chunked" disabled />
          </p>

        <p id="chunk_ingestion_options">

                <span id="chunkbynrows_tt" class="tooltip"></span>
                Chunk ingestion for every 
                <input name="scsv_chunk_by_n_rows" value="50" size="4" style="display: inline; float: none" maxlength="4" disabled> rows
                <span class="premium_only">(Premium Only)</span>
                <br />
                <span id="isusingmultithreads_tt" class="tooltip"></span>
                Use Multi-Threading<input type="checkbox" name="scsv_is_using_multithreads" value="1" disabled/>
                <span class="premium_only">(Premium Only)</span>

        </p>

</div>
<div style="float: right; width: 300px;">
        <h3>Ingestion Settings</h3>
        <p id="custom_terms" class="input">
          <span id="customterms_tt" class="tooltip"></span>Custom Terms (<span style="color: red">separate terms with commas</span>)
            <input type="text" name="scsv_custom_terms" id="scsv_custom_terms" value="<?php echo $post_terms?>">
        </p>
        <p id="compex_categories">
            <span id="parsecomplex_tt" class="tooltip"></span>Parse complex categories: <input type="checkbox" name="scsv_parse_terms" value="true" <?php echo ($parse_terms)?'checked="checked"':''?>>
        </p>
        <p id="ingestion_debugging">
            <span id="debugingestion_tt" class="tooltip"></span>Debug Ingestion: <input type="checkbox" name="scsv_ingest_debugger" value="true" <?php echo ($ingest_debugger)?'checked="checked"':''?>>
        </p>
        <p id="encode_char">
            <span id="specialchar_tt" class="tooltip"></span>Encode Special Characters: <input type="checkbox" name="scsv_encode_special_chars" value="true" <?php echo($encode_chars)?'checked="checked"':''?>>
        </p>
        <hr />
        <h3><span id="csvsettings_tt" class="tooltip"></span>CSV Settings</h3>
        <p id="csv_settings">
            <?php $settings_keys = array('delimiter'=>',','enclosure'=>'"','escape'=>'\\'); ?>

            <?php foreach($settings_keys as $k=>$v): ?>
                <p class="scsv_input"><?php echo $k?>:<input type='text' name='scsv_csv_settings[<?php echo $k?>]' value="<?php echo isset($csv_settings[$k])?htmlentities($csv_settings[$k]):htmlentities($v);?>" size='2' maxlength='2' /></p>
            <?php endforeach; ?>
            <p id="line_maxlen"><span id="maxchar_tt" class="tooltip"></span>Max Character Limit Per Line<input type="text" name="scsv_line_maxlen" value="<?php echo (is_null($additional_csv_settings['line_maxlen']))?'1000':$additional_csv_settings['line_maxlen'];?>" size="20"></p>
        </p>
        <h3><span id="hooking_tt" class="tooltip"></span>Hooking</h3>
        <p id="ingestion_hooking">
            <span id="activatehooking_tt" class="tooltip"></span>Activate Hooking: <input type="checkbox" name="scsv_has_hooks" value="true" <?php echo ($has_hooks)?'checked="checked"':''?> />
        </p>
        <h3>Go Premium</h3>
        <p>
            Take advantage of premium features such as using custom post types and extracting unlimited posts into csv format.
            <a href="http://www.supraliminalsolutions.com/blog/listings/supra-csv/" target="_blank">upgrade here!</a>
        </p>
</div>
<div style="clear: both"></div>
<hr />
        <p class="submit">
            <input type="submit" name="scsv_submit" value="Update Options" />
        </p>
</form>
</div>


