<?php
/*
Plugin Name: Liketly Bar
Plugin URI: http://www.liketly.com/likebutton/
Description: This plugin will show <a href="http://Liket.ly/likebutton/">LiketLy Bar</a> on your website. + <strong>Auto Post</strong> a Liketly Update when you publish Wordpress posts.
Version: 1.2
Author:  Ketnooi
*/
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
if(!isset($_SESSION))
{
session_start();
}
if( !is_admin() ) {
	add_action('wp_print_scripts', 'liketlybar_filter_footer');
}
add_action('admin_menu', 'liketlybar_config_page');

function liketlybar_filter_footer() {
	$liketlybar_username = get_option('liketlybar_username');
	$liketlybar_color = get_option('liketlybar_color');
	$liketlybar_position = get_option('liketlybar_position');
	$liketlybar_enabled = get_option('liketlybar_enabled');

	$script = 'http://apps.liketly.com/widget/liketlybar.php?delay=1000&position='.urlencode($liketlybar_position).'&color='.urlencode($liketlybar_color).'&username='.urlencode($liketlybar_username);
	
	if ($liketlybar_enabled) {
		wp_enqueue_script('liketlybarToolbar', $script, false, false, true );
	}
}

function liketlybar_config_page() {
	add_submenu_page('themes.php', __('LiketlyBar Configuration'), __('LiketlyBar Configuration'), 'manage_options', 'liketlybar-key-config', 'liketlybar_config');
}

function liketlybar_config() {
	$liketlybar_username = get_option('liketlybar_username');
	$liketlybar_color = get_option('liketlybar_color');
	$liketlybar_position = get_option('liketlybar_position');
	$liketlybar_enabled = get_option('liketlybar_enabled');
	$liketlybar_token = get_option('liketlybar_token');

	if ( isset($_POST['submit']) ) {
		if (isset($_POST['liketlybar_username']))
		{
			$liketlybar_username = $_POST['liketlybar_username'];
			$liketlybar_color = $_POST['liketlybar_color'];
			$liketlybar_position = $_POST['liketlybar_position'];
			$liketlybar_token = $_POST['liketlybar_token'];
			if ($_POST['liketlybar_enabled'] == 'on')
			{
				$liketlybar_enabled = 1;
			}
			else
			{
				$liketlybar_enabled = 0;
			}
		}
		else
		{
	$liketlybar_username = '';
	$liketlybar_color = '';
	$liketlybar_position = 'top';
	$liketlybar_enabled = 1;
	$liketlybar_token = '';
		}
		update_option('liketlybar_username', $liketlybar_username);
		update_option('liketlybar_color', $liketlybar_color);
		update_option('liketlybar_position', $liketlybar_position);
		update_option('liketlybar_enabled', $liketlybar_enabled);
		update_option('liketlybar_token', $liketlybar_token);
		echo "<div id=\"updatemessage\" class=\"updated fade\"><p>Liketly settings updated.</p></div>\n";
		echo "<script type=\"text/javascript\">setTimeout(function(){jQuery('#updatemessage').hide('slow');}, 3000);</script>";	
	}
	?>
	<div class="wrap">
		<h2>Liketly Bar for WordPress Configuration</h2>
		<div class="postbox-container">
			<div class="metabox-holder">	
				<div class="meta-box-sortables">
					<form action="" method="post" id="liketlybar-conf">
					<div id="liketlybar_settings" class="postbox">
						<div class="handlediv" title="Click to toggle"><br /></div>
						<h3 class="hndle"><span>Liketly Bar Settings</span></h3>
						<div class="inside">
							<table class="form-table">
								<tr><th valign="top" scrope="row">Liketly Bar On/Off:</th>
								<td valign="top"><input type="checkbox" id="liketlybar_enabled" name="liketlybar_enabled" <?php echo ($liketlybar_enabled ? 'checked="checked"' : ''); ?> /> <label for="liketlybar_enabled">Enable or disable the Liketly Bar</label><br/></td></tr>

								<tr><th valign="top" scrope="row">Position:</th>
								<td valign="top">
								<select name="liketlybar_position">
									<option value="top" <?php echo ($liketlybar_position=='top' ? 'selected' : ''); ?>>Top</option>
									<option value="bottom" <?php echo ($liketlybar_position=='bottom' ? 'selected' : ''); ?>>Bottom</option>
								</select>
								<label for="liketlybar_enabled">Position of Liketly Bar</label><br/></td></tr>

								<tr><th valign="top" scrope="row"><label for="liketlybar_username">Liketly Username:</label></th>
								<td valign="top"><input id="liketlybar_username" name="liketlybar_username" type="text" size="20" value="<?php echo $liketlybar_username; ?>"/></td></tr>

								<tr><th valign="top" scrope="row">Bar Color :</th>
								<td valign="top"><input id="liketlybar_color" name="liketlybar_color" type="text" size="20" value="<?php echo $liketlybar_color; ?>"/>
								<label for="liketlybar_color">Red, blue, green, #FF33CC,...etc</label></td></tr>

								<tr><th valign="top" scrope="row">AutoPost to Liketly</th>
								<td valign="top">
								Auto post a Liketly update when you update/publish your WordPress blog. <br />Leave "API Token" blank if you don't want to use this feature.
								</td></tr>
								<tr><th valign="top" scrope="row">Liketly API Token:</th>
								<td valign="top"><input id="liketlybar_token" name="liketlybar_token" type="text" size="20" value="<?php echo $liketlybar_token; ?>"/>
								<label for="liketlybar_token">Get your API Token from <a href="http://www.liketly.com/apps/71/auto-post-to-liketly/" target="_blank">this Liketly App</a></label>
								</td></tr>

							</table>
						</div>
					</div>
					<div class="submit"><input type="submit" class="button-primary" name="submit" value="Update Toolbar &raquo;" /></div>
					</form>
				</div>
			</div>
		</div>
	</div>
	<?php
} 

########## auto post to Liket.Ly ############
########## Thanks to Liketly Api class ######
$post_type_settings = get_option('wpt_post_types');
if ( is_array( $post_type_settings ) ) {
	$post_types = array_keys($post_type_settings);
	foreach ($post_types as $value ) {
		add_action( 'publish_'.$value, 'post_to_Liketly', 10 );	
	}
}

add_action( 'save_post', 'post_to_Liketly', 10 ); // Now things will happen twice. Hmmm...guess that's OK. 

function Liketly_link( $post_ID ) {
		$ex_link = false;
		$wtb_extlink_custom_field = get_option('jd_twit_custom_url'); 
		$permalink = get_permalink( $post_ID );
			if ( $wtb_extlink_custom_field != '' ) {
				$ex_link = get_post_meta($post_ID, $wtb_extlink_custom_field, true);
			}
       return ( $ex_link ) ? $ex_link : $permalink;
}


function post_to_Liketly( $id ) {
	if ( empty($_POST) || ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) || wp_is_post_revision($id) || isset($_POST['_inline_edit']) || isset($_POST['save']) ) { return $id; }
	$liketlybar_token = get_option('liketlybar_token');
	if(!$liketlybar_token) return $id;

	$status = '';
	if(isset($_POST["post_title"])){
	// submit
	$post_ID = $id;
	$values['postLink'] = Liketly_link( $post_ID );
	$link = $values['postLink'];
	$status = '** '.$_POST["post_title"] . ''."\n". mb_substr(trim(strip_tags($_POST["post_content"])),0,100).'... '."\n Link:  ".$link ;
	}else
	{
	$post_ID = $id;
	$post = get_post( $post_ID );
	$values['postLink'] = Liketly_link( $post_ID );
	$link = $values['postLink'];
	$status = '** '.$post->post_title . ''."\n". mb_substr(trim(strip_tags($post->post_content)),0,100).'... '."\n Link:  ".$link ;
	}
if($status){
if (!class_exists('liketlyapi')){
require_once('liketlyapi.class.php');
}
$lkapi = new LiketLyApi;

######## config ###########
$lkapi->_domain = 'ABC.com';
$lkapi->_appurl = 'http://www.liketly.com/apps/71/auto-post-to-liketly/';
$lkapi->_appid = '20MOKQCR0YY5X';
######### end config #######

$_SESSION['l_auth_token'] = trim($liketlybar_token);
$aPost = array();
$aPost['user_status'] = $status;
$res = $lkapi->api('user.updateStatus', $aPost, false);
return true;
}
else 
return false;
}

?>