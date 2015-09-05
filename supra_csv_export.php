<?php 
require_once('classes/Debug.php');
require_once(dirname(__FILE__).'/classes/ExtractCsv.php');
wp_enqueue_script( 'inputCloner', plugins_url('/js/inputCloner.js', __FILE__) );

wp_enqueue_script( 'misc', plugins_url('/js/misc.js', __FILE__) );
wp_register_script('extractor', plugins_url('/js/export.js', __FILE__) );
wp_enqueue_script( 'extractor' );
wp_enqueue_script( 'tablesorter', plugins_url('/js/jquery.tablesorter.js', __FILE__) );
wp_enqueue_style( 'tablesorter-blue', plugins_url('/css/tablesorter-blue.css', __FILE__) );

$xc = new \SupraCsvFree\ExtractCsv($_FILES);

$xc->setSettingsResolver(function($setting_key) {
    return get_option($setting_key);
});

?>
<div id="supra_csv_extractor_form">
 
    <form id="extraction_form"> 
        <div class="wrap_scsv inputGroupWrapper" style="width: 350px">

        <h3>Extract Settings</h3>

        <div id="input">
            <label for="posts_per_page">Post Per Page</label>
            <input type="text" id="posts_per_page" name="posts_per_page" maxlength="3" size="3" value="5" />
        </div>

        <div id="input">
            <label for="offset">Offset</label>
            <input type="text" id="offset" name="offset" maxlength="3" size="3" />
        </div>

        <div id="input" style="height: 100px">
            <label for="post_type">Post Type</label>
            <select name="post_type[]" id="post_type" multiple="multiple">
            <?php foreach(get_post_types() as $post_type): ?>
                <option value="<?php echo $post_type?>"><?php echo $post_type?></option>
            <?php endforeach ?>
            </select>
            <br />
            <span class="help">Hold ctrl to select multiple</span>
        </div>

        <div id="input">
            <label for="order_by">Order By</label>
            <select name="order_by" id="order_by">
                <option value="post_date">Date</option>
                <option value="post_title">Title</option>
                <option value="post_status">Status</option>
                <option value="post_type">Type</option>
            </select>
        </div>

        <div id="input">
            <label for="order">Order</label>
            <select name="order" id="order">
                <option value="DESC">DESC</option>
                <option value="ASC">ASC</option>
            </select>
        </div>

        <div id="input">
            <label for="post_status">Post Status</label>
            <select name="post_status" id="post_status">
                <option value="publish">Published</option>
                <option value="pending">Pending</option>
                <option value="trash">Trash</option>
                <option value="auto-draft">Draft</option>
            </select>
        </div>

        <div id="input">
            <label for="year">Year</label>
            <input type="text" id="year" name="year" maxlength="4" size="4" />
        </div>

        <div id="input">
            <label for="weeks_ago">Weeks Ago</label>
            <input type="text" id="weeks_ago" name="weeks_ago" maxlength="2" size="2" />
        </div>

        <div id="input">
            <span class="help">Clicking this button shows all the posts that match your criteria specified above.</span>
            <button id="extract_and_preview">Extract</button>
        </div>

        <h3>Export Settings</h3>
        <div id="input" style="height: 210px">
            <label for="post_fields">Post Fields</label>
            <span class="help">Hold Ctrl to select multiple</span>
            <select id="post_fields" name="post_fields[]" multiple="multiple" style="height: 200px">
                <?php
                $postfields = array(
                  'ID',
                  'post_author',
                  'post_date',
                  'post_date_gmt',
                  'post_content',
                  'post_title',
                  'post_category',
                  'post_excerpt',
                  'comment_status',
                  'ping_status',
                  'post_password',
                  'post_name',
                  'to_ping',
                  'pinged',
                  'post_modified',
                  'post_modified_gmt',
                  'post_content_filtered',
                  'post_parent',
                  'guid',
                  'menu_order',
                  'post_mime_type',
                  'comment_count',
                );
                ?>
                <?php foreach($postfields as $postfield): ?>
                <option value="<?php echo $postfield?>"><?php echo $postfield?></option>
                <?php endforeach ?>
            </select>
        </div>

        <div id="input">
            <label for="post_taxonomies">Taxonomies</label>
            <span class="help">provide comma-separated-values</span>
            <input type="text" id="post_taxonomies" name="post_taxonomies" value="category,post_tag" size="35" />
        </div>

        <div id="input" style="height: 100px">
            <label for="meta_keys">Meta Keys</label>
            <span class="help">Hold Ctrl to select multiple. These are populated from the preset selected in the post info tab.</span>
            <select name="meta_keys[]" id="meta_keys" multiple="multiple">
                <?php 
                    $postmetas = get_option('scsv_postmeta');
                    foreach($postmetas['meta_key'] as $mk) { 
                      echo '<option value="'.$mk.'">'.$mk.'</option>';
                    }
                ?>
            </select>
        </div>

        <?php
            extract($xc->_get_scsv_settings());
        ?>
        <div id="input">
            <label for="extract_delim">CSV delimiter</label>
            <span class="help">This setting will override the delimiter character provided on the configuration page</span>
            <input type="text" id="extract_delim" name="extract_delim" value="<?php echo htmlentities($delimiter)?>" size="2" maxlength="2" />
        </div>
 
        <div id="input">
            <label for="extract_enclose">CSV enclosure</label>
            <span class="help">This setting will override the enclosure character provided on the configuration page</span>
            <input type="text" id="extract_enclose" name="extract_enclose" value="<?php echo htmlentities($enclosure)?>"  size="2" maxlength="2" />
        </div>
 
        <div id="input">
            <label for="extract_escape">CSV escape</label>
            <span class="help">This setting will override the escape character provided on the configuration page</span>
            <input type="text" id="extract_escape" name="extract_escape" value="<?php echo htmlentities($escape)?>"  size="2" maxlength="2" />
        </div>

        <div id="input">
            <label for="from_charset">Extract from charset</label>
            <span class="help">If you're not sure about this just leave it blank</span>
            <input type="text" id="from_charset" name="from_charset" />
        </div>
 
        <div id="input">
            <label for="to_charset">Extract to charset</label>
            <span class="help">If you're not sure about this just leave it blank</span>
            <input type="text" id="to_charset" name="to_charset" />
        </div>

        <div id="input">
            <button id="extract_and_export">Extract And Export</button>
        </div>
</div>
    </form>
<div id="extracted_results"></div>
</div>
<hr />
<h3>
<span id="extractedfilemgmt_tt" class="tooltip"></span>
Extracted CSV File Management
</h3>
<div id="supra_csv_extract_forms" class="wrap_scsv" style="width: 550px;">
    <?php $xc->renderForms();?>
</div>
