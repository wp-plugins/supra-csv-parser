<?php
require_once(dirname(__FILE__).'/classes/SupraCsvPlugin.php');

$scp = new SupraCsvPlugin();

if(!empty($_POST['scsv_submit'])) {
    $csvfile= $_POST['scsv_filename'];
    $csvuser['name'] = $_POST['scsv_wpname'];
    $csvuser['pass'] = $_POST['scsv_wppass'];
    $csvpost['publish'] = $_POST['scsv_autopub'];
    $csvpost['type'] = (empty($_POST['scsv_posttype'])) ? $_POST['scsv_custom_posttype'] : $_POST['scsv_posttype'];
    $csvpost['title'] = $_POST['scsv_defaulttitle'];
    $csvpost['desc'] = $_POST['scsv_defaultdesc'];
    $post_terms = $_POST['scsv_custom_terms'];
    $parse_terms = $_POST['scsv_parse_terms'];
    $ingest_debugger = $_POST['scsv_ingest_debugger'];
    $csv_settings = $_POST['scsv_csv_settings'];
    $report_issue = $_POST['scsv_report_issue'];
    $encode_chars = $_POST['scsv_encode_special_chars'];
    update_option('scsv_filename', $csvfile);
    update_option('scsv_user', $csvuser);
    update_option('scsv_post', $csvpost);
    update_option('scsv_custom_terms', $post_terms);
    update_option('scsv_parse_terms', $parse_terms);
    update_option('scsv_ingest_debugger', $ingest_debugger);
    update_option('scsv_report_issue', $report_issue);
    update_option('scsv_csv_settings', $csv_settings);
    update_option('scsv_encode_special_chars',$encode_chars);
    echo '<div class="updated"><p><strong>Configuration saved</strong></p></div>';
} else {
    $csvfile = get_option('scsv_filename');
    $csvuser = get_option('scsv_user');
    $csvpost = get_option('scsv_post');
    $post_terms = get_option('scsv_custom_terms');
    $parse_terms = get_option('scsv_parse_terms');
    $ingest_debugger = get_option('scsv_ingest_debugger');
    $report_issue = get_option('scsv_report_issue');
    $csv_settings = get_option('scsv_csv_settings');
    $encode_chars = get_option('scsv_encode_special_chars');
}

?>
<div class="wrap_scsv" style="width: 630px">
<h2>Supra CSV Configuration</h2>
        <hr />
<div style="float: left; width: 300px;">
<form name="scsv_form" method="post">
        <h3>User Settings</h3>
        <p>Username<input type="text" name="scsv_wpname" value="<?php echo $csvuser['name']; ?>" size="20"></p>
        <p>Pasword<input type="password" name="scsv_wppass" value="<?php echo $csvuser['pass']; ?>" size="20"></p>

        <hr />
        <h3>Post Settings</h3>
        <p>
            Auto Publish
            <select name="scsv_autopub">
                <option value="0">false</option>
                <option value="1" <?php if($csvpost['publish']) echo 'selected="selected"';?>>true</option>
            </select>
        </p>
        <p>
            Post Type
            <select name="scsv_posttype" value="<?php echo $csvpost['type']; ?>">
                <option value=""></option>
                <option value="post" value="post" selected>post</option>
                <option value="page" value="page">page</option>
                <option value="attachment" value="attachment">attachment</option>
                <option value="nav_menu_item" value"nav_menu_item">nav_menu_item</option>
            </select>
        </p>
        <p style="text-align: center">
            <h3>or</h3>
        </p>
        <p>
            Custom Post Type <span class="premium_only">(Premium Only)</span>
            <input type="text" name="scsv_custom_posttype" value="" size="5" style="background-color: #FDEEF4" disabled>
        </p>
        <p>Default Title<input type="text" name="scsv_defaulttitle" value="<?php echo $csvpost['title']; ?>" size="20"></p>
        <p>Default Description<textarea name="scsv_defaultdesc" cols="50"><?php echo $csvpost['desc']; ?></textarea></p>
</div>
<div style="float: right; width: 300px;">
        <h3>Ingestion Settings</h3>
        <p id="custom_terms" class="input">
          Custom Terms (<span style="color: red">separate terms with commas</span>)
            <input type="text" name="scsv_custom_terms" value="<?php echo $post_terms?>" style="width: 240px;">
        </p>
        <p id="compex_categories">
            Parse complex categories: <input type="checkbox" name="scsv_parse_terms" value="true" <?php echo ($parse_terms)?'checked="checked"':''?>>
        </p>
        <p id="ingestion_debugging">
            Debug Ingestion: <input type="checkbox" name="scsv_ingest_debugger" value="true" <?php echo ($ingest_debugger)?'checked="checked"':''?>>
        </p>
        <p id="issue_reporting">
            Report Issues: <span class="premium_only">(Premium Only)</span><input type="checkbox" name="scsv_report_issue" value="true" <?php echo ($report_issue)?'checked="checked"':''?> disabled>
        </p>
        <p id="encode_char">
            Encode Special Characters: <input type="checkbox" name="scsv_encode_special_chars" value="true" <?php echo($encode_chars)?'checked="checked"':''?>>
        </p>
        <hr />
        <h3>CSV Settings</h3>
        <p id="csv_settings">
            <?php $settings_keys = array('delimiter'=>',','enclosure'=>'"','escape'=>'\\'); ?>
            <?php foreach($settings_keys as $k=>$v): ?>
                <p class="scsv_input"><?php echo $k?>:<input type='text' name='scsv_csv_settings[<?php echo $k?>]' value='<?php echo($csv_settings[$k])?stripslashes($csv_settings[$k]):$v;?>' size='2' maxlength='2' /></p>
            <?php endforeach; ?>
        </p>
        <h3>Go Premium</h3>
        <p>
            Take advantage of premium features such as ingesting more than 50 posts, using custom post types and extracting csv from posts.
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

