<?php 
require_once('classes/Debug.php');

wp_enqueue_script( 'inputCloner', plugins_url('/js/inputCloner.js', __FILE__) );
wp_enqueue_script( 'extractor', plugins_url('/js/export.js', __FILE__) );

?>
<div id="supra_csv_extractor_form">
 
    <form id="extraction_form"> 
<div class="wrap_scsv" style="width: 230px;">
        <h3>Extract Settings</h3>

        <div id="input">
            <label for="posts_per_page">Post Per Page</label>
            <input type="text" id="posts_per_page" name="posts_per_page" maxlength="3" size="3" value="5" />
        </div>

        <div id="input">
            <label for="offset">Offset</label>
            <input type="text" id="offset" name="offset" maxlength="3" size="3" />
        </div>

        <div id="input">
            <label for="post_type">Post Type</label>
            <select name="post_type" id="post_type">
            <?php foreach(get_post_types() as $post_type): ?>
                <option value="<?php echo $post_type?>"><?php echo $post_type?></option>
            <?php endforeach ?>
            </select>
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
            <button id="extract_and_preview">Extract</button>
        </div>
</div>
<div class="wrap_scsv" style="width: 400px;">
        <h3>Export Settings</h3>
        <span class="help">provide comma-separated-values</span>
        <div id="input">
            <label for="post_fields">Post Fields</label>
            <input type="text" id="post_fields" name="post_fields" size="50" value="post_title,post_content,post_date,post_author,post_status" />
        </div>

        <div id="input">
            <label for="post_taxonomies">Taxonomies</label>
            <input type="text" id="post_taxonomies" name="post_taxonomies" value="category,post_tag" size="50" />
        </div>

        <div id="input">
            <label for="meta_keys">Meta Keys</label>
            <input type="text" id="meta_keys" name="meta_keys" value="<?php
            $postmetas = get_option('scsv_postmeta');
            $values = implode(',',$postmetas['meta_key']);
            echo $values;
            ?>" size="50" />
        </div>

        <div id="input">
            <button id="extract_and_export">Extract And Export</button>
        </div>
</div>
    </form>
<div id="extracted_results"></div>
</div>
