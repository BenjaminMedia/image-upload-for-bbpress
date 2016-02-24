<?php
/**
 * Plugin Name: Image Upload for BBPress
 * Description: Upload inline images to BBPress forum topics and replies.
 * Version: 1.0.8
 * Author: Potent Plugins
 * Author URI: http://potentplugins.com/?utm_source=image-upload-for-bbpress&utm_medium=link&utm_campaign=wp-plugin-author-uri
 * License: GNU General Public License version 2 or later
 * License URI: http://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html
 */


add_filter('plugin_action_links_'.plugin_basename(__FILE__), 'hm_bbpui_action_links');
function hm_bbpui_action_links($links) {
	array_unshift($links, '<a href="'.esc_url(get_admin_url(null, 'options-general.php?page=hm_bbpui')).'">About</a>');
	return $links;
}

add_action('admin_menu', 'hm_bbpui_admin_menu');
function hm_bbpui_admin_menu() {
	add_options_page('Image Upload for BBPress', 'Forum Images', 'activate_plugins', 'hm_bbpui', 'hm_bbpui_admin_page');
}

function hm_bbpui_admin_page() {
	echo('
		<div class="wrap">
			<h2>Image Upload for BBPress</h2>
			
	');
	echo('<div style="background-color: #fff; border: 1px solid #ccc; padding: 20px; max-width: 800px;">
		<h3 style="margin: 0;">Upgrade to <a href="http://potentplugins.com/downloads/image-upload-for-bbpress-pro-wordpress-plugin/?utm_source=image-upload-for-bbpress&amp;utm_medium=link&amp;utm_campaign=wp-plugin-upgrade-link" target="_blank">Image Upload for BBPress Pro</a> for advanced configuration options!</h3>
		<ul>
<li>Change the directory where uploaded images are stored.</li>
<li>Limit which user roles are permitted to upload images.</li>
<li>Limit the number of uploaded images allowed per post.</li>
<li>Automatically downsize images to fit specified maximum dimensions.</li>
<li>Convert all uploaded images to the same image format, if desired.</li>
<li>Set PNG and JPEG compression levels so images take up less disk space.</li>
<li>View total image count and file size statistics.</li>
		</ul>
		<strong>Receive a 25% discount with the coupon code <span style="color: #f00;">BBPIMAGES25</span>!</strong>
		<a href="http://potentplugins.com/downloads/image-upload-for-bbpress-pro-wordpress-plugin/?utm_source=image-upload-for-bbpress&amp;utm_medium=link&amp;utm_campaign=wp-plugin-upgrade-link" target="_blank">Buy Now &gt;</a>
	</div>');
	echo('
			<h3 style="margin-top: 40px;">Usage Instructions</h3>
			<p>To upload an image to a forum topic or reply, click the <em>Insert/edit image</em> button in the editor toolbar:</p>
			<img src="'.plugins_url('images/bbpui-screenshot-toolbar.png', __FILE__).'" alt="Toolbar screenshot" />
			<p>Then click the Browse button in the image dialog to select and upload an image:</p>
			<img src="'.plugins_url('images/bbpui-screenshot-dialog.png', __FILE__).'" alt="Dialog screenshot" />
			<h3 style="margin-top: 40px;">Supported File Formats and File Size</h3>
			<p>This plugin supports images in JPEG, PNG, and GIF format. The maximum file size is determined by the following settings in your server\'s PHP configuration:</p>
			<ul>
				<li><strong>upload_max_filesize:</strong> '.ini_get('upload_max_filesize').'</li>
				<li><strong>post_max_size:</strong> '.ini_get('post_max_size').'</li>
			</ul>
			<div style="margin-top: 40px;"></div>
	');
	$potent_slug = 'image-upload-for-bbpress';
	include(__DIR__.'/plugin-credit.php');
	echo('
		</div>
	');
}

add_filter('bbp_after_get_the_content_parse_args', 'hm_bbpiu_modify_editor');
function hm_bbpiu_modify_editor($args = array()) {
	$args['tinymce'] = array(
		'file_browser_callback' => 'function(field_id){hm_bbpui_file_upload(field_id);}'
	);
	return $args;
}

add_action('init', 'hm_bbpui_handle_upload');
function hm_bbpui_handle_upload() {
	if (empty($_GET['hm_bbpui_do_upload']))
		return;
	
	// Check capabilities
	if (!(
			current_user_can('publish_topics') || current_user_can('publish_replies')
			|| (!is_user_logged_in() && get_option('_bbp_allow_anonymous', false))
		))
		hm_bbpui_upload_error();
	
	// Check file upload
	if (!isset($_FILES['hm_bbpui_file']) || !empty($_FILES['hm_bbpui_file']['error']) || !is_uploaded_file($_FILES['hm_bbpui_file']['tmp_name']))
		hm_bbpui_upload_error();
	
	// Get/create temp directory
	$uploadDir = wp_upload_dir();
	$tempUploadDir = $uploadDir['basedir'].'/hm_bbpui_temp';
	if (!is_dir($tempUploadDir))
		@mkdir($tempUploadDir) or hm_bbpui_upload_error();
	
	// Get temp filename
	$chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
	$maxChar = strlen($chars) - 1;
	do {
		$tempName = '';
		for ($i = 0; $i < 32; ++$i)
			$tempName .= $chars[rand(0, $maxChar)];
	} while (file_exists($tempUploadDir.'/'.$tempName));
	$dotPos = strrpos($_FILES['hm_bbpui_file']['name'], '.');
	if ($dotPos)
		$tempName .= substr($_FILES['hm_bbpui_file']['name'], $dotPos);
	
	// Save as an image file (for security reasons)
	switch (strtolower($_FILES['hm_bbpui_file']['type'])) {
		case 'image/jpeg':
			$img = imagecreatefromjpeg($_FILES['hm_bbpui_file']['tmp_name']) or hm_bbpui_upload_error();
			imagejpeg($img, $tempUploadDir.'/'.$tempName) or hm_bbpui_upload_error();
			break;
		case 'image/png':
			$img = imagecreatefrompng($_FILES['hm_bbpui_file']['tmp_name']) or hm_bbpui_upload_error();
			imagesavealpha($img, true) or hm_bbpui_upload_error();
			imagealphablending($img, false) or hm_bbpui_upload_error();
			imagepng($img, $tempUploadDir.'/'.$tempName) or hm_bbpui_upload_error();
			break;
		case 'image/gif':
			$img = imagecreatefromgif($_FILES['hm_bbpui_file']['tmp_name']) or hm_bbpui_upload_error();
			imagegif($img, $tempUploadDir.'/'.$tempName) or hm_bbpui_upload_error();
			break;
		default:
			($img = imagecreatefromjpeg($_FILES['hm_bbpui_file']['tmp_name']) && imagejpeg($img, $tempUploadDir.'/'.$tempName)) or
			($img = imagecreatefrompng($_FILES['hm_bbpui_file']['tmp_name']) && imagesavealpha($img, true) && imagepng($img, $tempUploadDir.'/'.$tempName)) or
			($img = imagecreatefromgif($_FILES['hm_bbpui_file']['tmp_name'])  && imagegif($img, $tempUploadDir.'/'.$tempName)) or
			hm_bbpui_upload_error();
	}
	
	@unlink($_FILES['hm_bbpui_file']['tmp_name']);
	echo($uploadDir['baseurl'].'/hm_bbpui_temp/'.$tempName);
	exit;
	
}

function hm_bbpui_upload_error() {
	echo('Error');
	exit;
}

add_action('wp_enqueue_scripts', 'hm_bbpui_enqueue_scripts');
function hm_bbpui_enqueue_scripts() {
	wp_enqueue_script('hm_bbpui', plugins_url('js/bbp-image-upload.js', __FILE__));
	wp_enqueue_style('hm_bbpui', plugins_url('css/bbp-image-upload.css', __FILE__));
}

add_action('wp_insert_post', 'hm_bbpui_insert_post');
function hm_bbpui_insert_post($postId) {
	$post = get_post($postId);
	if ($post->post_type != 'topic' && $post->post_type != 'reply')
		return;
	
	preg_match_all('/\/hm_bbpui_temp\/(.+)["\']/iU', $post->post_content, $matches);
	
	if (!empty($matches[1])) {
		
		$uploadDir = wp_upload_dir();
		
		if (!is_dir($uploadDir['basedir'].'/hm_bbpui'))
			mkdir($uploadDir['basedir'].'/hm_bbpui');
		if (!is_dir($uploadDir['basedir'].'/hm_bbpui/'.$post->ID))
			mkdir($uploadDir['basedir'].'/hm_bbpui/'.$post->ID);
	
		foreach($matches[1] as $match) {
			if (strpos($match, '/') || strpos($match, '\\'))
				continue;
			rename($uploadDir['basedir'].'/hm_bbpui_temp/'.$match, $uploadDir['basedir'].'/hm_bbpui/'.$post->ID.'/'.$match);
			$post->post_content = str_replace('/hm_bbpui_temp/'.$match, '/hm_bbpui/'.$post->ID.'/'.$match, $post->post_content);
		}
		
		remove_action('wp_insert_post', 'hm_bbpui_insert_post');
		wp_update_post($post);
	}
}

add_action('delete_post', 'hm_bbpui_delete_post');
function hm_bbpui_delete_post($postId) {
	$uploadDir = wp_upload_dir();
	$postUploadDir = $uploadDir['basedir'].'/hm_bbpui/'.$postId;
	if (is_dir($postUploadDir)) {
		foreach (scandir($postUploadDir) as $dirItem) {
			if (is_file($postUploadDir.'/'.$dirItem))
				unlink($postUploadDir.'/'.$dirItem);
		}
		rmdir($postUploadDir);
	}
}

function hm_bbpui_clean_temp_dir() {
	$uploadDir = wp_upload_dir();
	$timeThreshold = time() - 86400;
	foreach (scandir($uploadDir['basedir'].'/hm_bbpui_temp') as $dirItem) {
		$dirItem = $uploadDir['basedir'].'/hm_bbpui_temp/'.$dirItem;
		if (is_file($dirItem) && filemtime($dirItem) < $timeThreshold)
			unlink($dirItem);
	}
}

register_activation_hook(__FILE__, 'hm_bbpui_activate');
function hm_bbpui_activate() {
	// Schedule temp dir cleaning
	wp_schedule_event(time(), 'daily', 'hm_bbpui_clean_temp_dir');
}

register_deactivation_hook(__FILE__, 'hm_bbpui_deactivate');
function hm_bbpui_deactivate() {
	// Unschedule temp dir cleaning
	wp_clear_scheduled_hook('hm_bbpui_clean_temp_dir');
}


/* Review/donate notice */

register_activation_hook(__FILE__, 'hm_bbpui_first_activate');
function hm_bbpui_first_activate() {
	$pre = 'hm_bbpui';
	$firstActivate = get_option($pre.'_first_activate');
	if (empty($firstActivate)) {
		update_option($pre.'_first_activate', time());
	}
}
if (is_admin() && get_option('hm_bbpui_rd_notice_hidden') != 1 && time() - get_option('hm_bbpui_first_activate') >= (14*86400)) {
	add_action('admin_notices', 'hm_bbpui_rd_notice');
	add_action('wp_ajax_hm_bbpui_rd_notice_hide', 'hm_bbpui_rd_notice_hide');
}
function hm_bbpui_rd_notice() {
	$pre = 'hm_bbpui';
	$slug = 'image-upload-for-bbpress';
	echo('
		<div id="'.$pre.'_rd_notice" class="updated notice is-dismissible"><p>Do you use the <strong>Image Upload for BBPress</strong> plugin?
		Please support our free plugin by <a href="https://wordpress.org/support/view/plugin-reviews/'.$slug.'" target="_blank">writing a review</a> and/or <a href="https://potentplugins.com/donate/?utm_source='.$slug.'&amp;utm_medium=link&amp;utm_campaign=wp-plugin-notice-donate-link" target="_blank">making a donation</a>!
		Thanks!</p></div>
		<script>jQuery(document).ready(function($){$(\'#'.$pre.'_rd_notice\').on(\'click\', \'.notice-dismiss\', function(){jQuery.post(ajaxurl, {action:\'hm_bbpui_rd_notice_hide\'})});});</script>
	');
}
function hm_bbpui_rd_notice_hide() {
	$pre = 'hm_bbpui';
	update_option($pre.'_rd_notice_hidden', 1);
}


?>