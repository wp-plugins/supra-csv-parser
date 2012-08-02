<?php 
if(!empty($_POST['scsv_submit'])) {
    $csvfile= $_POST['scsv_filename'];
    $csvuser['name'] = $_POST['scsv_wpname'];
    $csvuser['pass'] = $_POST['scsv_wppass'];
    $csvpost['publish'] = $_POST['scsv_autopub'];
    $csvpost['type'] = (empty($_POST['scsv_posttype'])) ? $_POST['scsv_custom_posttype'] : $_POST['scsv_posttype'];
    $csvpost['title'] = $_POST['scsv_defaulttitle'];
    $csvpost['desc'] = $_POST['scsv_defaultdesc'];
    update_option('scsv_filename', $csvfile);
    update_option('scsv_user', $csvuser);
    update_option('scsv_post', $csvpost);
    echo '<div class="updated"><p><strong>Configuration saved</strong></p></div>';
} else {
    $csvfile = get_option('scsv_filename');
    $csvuser = get_option('scsv_user');
    $csvpost = get_option('scsv_post');
}
?>
<div class="wrap">
<h2>Supra CSV Configuration</h2>
<form name="scsv_form" method="post"">
        <h4>User Settings</h4>
	<p>Username<input type="text" name="scsv_wpname" value="<?php echo $csvuser['name']; ?>" size="20"></p>
	<p>Pasword<input type="password" name="scsv_wppass" value="<?php echo $csvuser['pass']; ?>" size="20"></p>

        <hr />
        <h4>Post Settings</h4>
	<p>
            Auto Publish
            <select name="scsv_autopub">
                <option value="0">false</option>
                <option value="1" <?php if($csvpost['publish']) echo 'selected="selected"';?>>true</option>
            </select>
        </p>
	<p>
            <i>Warning: custom post_type may no longer be supported.</i><br />
            Post Type
            <select name="scsv_posttype" value="<?php echo $csvpost['type']; ?>">
                <option value=""></option>
                <option value="post" <?if($csvpost['type']=="post") echo 'selected="selected"';?>>Post</option>
                <option value="page" <?if($csvpost['type']=="page") echo 'selected="selected"';?>>Page</option>
            </select>
            <b>or</b>
            Custom Post Type
            <input type="text" name="scsv_custom_posttype" value="<?php if($csvpost['type']!="page"&&$csvpost['type']!="post")echo $csvpost['type']; ?>" size="20">
        </p>
	<p>Default Title<input type="text" name="scsv_defaulttitle" value="<?php echo $csvpost['title']; ?>" size="20"></p>
	<p>Default Description<textarea name="scsv_defaultdesc"><?php echo $csvpost['desc']; ?></textarea></p>
	<p class="submit">
	<input type="submit" name="scsv_submit" value="Update Options" />
	</p>
</form>
</div>
