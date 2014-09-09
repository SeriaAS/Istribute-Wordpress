<?php

/*
Plugin Name: Istribute
Description: A plugin for integrating Istribute.
Version: 1.0
Author: Andreas Nergaard
Author URI: http://seria.no
License: GNU General Public License v2 or later*/

require_once('sdk/istribute.php');

function getIstributeConnection() {
	//$appid = get_option('istribute_appId');
	//$appkey = get_option('istribute_appKey');
	//$serverUrl = 'https://joneirikdev-apiistributecom.webhosting.seria.net';
	//return new \Seria\istributeSdk\Istribute($appId, $appKey, $serverUrl );
	return new \Seria\istributeSdk\Istribute('tZgTUJT','K2xv3FCYp2tzpmAWVY4ur4rPrxmh0FcA','https://joneirikdev-apiistributecom.webhosting.seria.net');
}

/*Admin page*/
function super_plugin_home() {

	/*$conn = getIstributeConnection();
	$result = $conn->getVideoList();*/
	
	$istribute = new \Seria\istributeSdk\Istribute(
      'tZgTUJT',
      'K2xv3FCYp2tzpmAWVY4ur4rPrxmh0FcA',
      'https://joneirikdev-apiistributecom.webhosting.seria.net'
    );
	$videos = $istribute->getVideoList();
	
	echo '<div class="wrap">';
	echo "<h2>" . __( 'My istribute Videos', 'istribute_trdom' ) . "</h2>";
?>
	<table>
		<tr>
			<th>Preview:</th>
			<th>Video name:</th>
			<th>Id:</th>
			<th>Embed code:</th>
		</tr>
		
		<?php
			if (!empty($videos)) {
				foreach ($videos as $video) {
					echo '<tr><td></td><td>' . $video->getTitle() . '</td><td>'.$video->getId().'</td><td>'.$video->getPlayerUrl().'</td></tr>';
				}
			} else {
				echo '<tr><td>No videos were found! Check your settings.</td><td></td><td></td></tr>';
			}
		?>
		<!-- <img width="100" height="56" style="max-width: 100px; height: auto;" src="https://joneirikdev-apiistributecom.webhosting.seria.net:8480'.$video->getPreviewImage().'" /> -->
	</table>
	<style>
	td, th {border: 1px solid black; padding: 5px; text-align: left;}
	</style>
	</div>

<?php
}

function super_plugin_options() {

	if($_POST['istribute_hidden'] == 'Y') {
        $appid = $_POST['istribute_appId'];
		$appkey = $_POST['istribute_appKey'];
        update_option('istribute_appId', $appid);
		update_option('istribute_appKey', $appkey); ?>

        <div class="updated"><p><strong><?php _e('Options saved.' ); ?></strong></p></div>
        <?php
    } else {
		$appid = get_option('istribute_appId');
		$appkey = get_option('istribute_appKey'); ?>

		<div class="wrap">
			<?php echo "<h2>" . __( 'istribute Options', 'istribute_trdom' ) . "</h2>"; ?>			

			<?php/*
			getIstributeConnection();
			$list = $istributeConnection->getVideoList();
			while($Video = $list->next()) {
				print_r($Video);
			}*/
			?>

			<form name="istribute_form" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
				<input type="hidden" name="istribute_hidden" value="Y">
				<p><?php _e("App Id: " ); ?><input type="text" name="istribute_appId" value="<?php echo $appid; ?>" size="20"></p>
				<p><?php _e("App Key: "); ?><input type="text" name="istribute_appKey" value="<?php echo $appkey; ?>" size="20"></p>
				<hr />
				<p class="submit">
					<input type="submit" name="Submit" value="<?php _e('Update Options', 'istribute_trdom' ) ?>" />
				</p>
			</form>
			<p>You can now get the "appId" and "appKey" within the template by get_option('istribute_appId / istribute_appKey')</p>
		</div>
	<?php
	}
}

add_action('admin_menu','super_plugin_menu');

function super_plugin_menu() {
    	//add_menu_page('Istribute', 'Istribute', 'manage_options', 'istribute-options', 'super_plugin_options', '', '', 6);
		add_menu_page("Istribute", "Istribute", 0, "istribute-settings", "super_plugin_options", '', '', 8);
		add_submenu_page("istribute-settings", "Videos", "Videos", 0, "istribute-videos", "super_plugin_home");
}

function addContent($content) {

	$position = get_post_meta( get_the_ID(), 'ist-position', true );
	$old_content = $content;

	if ($position == 'select-one') {
		$content = '<div class="videoWrapper" style="/*position: relative; padding-bottom: 56.25%; padding-top: 25px; height: 0;*/">' . get_post_meta( get_the_ID(), 'ist-link', true ) . '</div>';
		$content .= $old_content;
	} else if ($position == 'select-two') {
		$content .= '<div class="videoWrapper" style="/*position: relative; padding-bottom: 56.25%; padding-top: 25px; height: 0;*/">' . get_post_meta( get_the_ID(), 'ist-link', true ) . '</div>';
	}
    /*if( !empty( $content ) ) { echo $content; }*/
return $content;

}

add_action('the_content', 'addContent');

add_action('media_buttons_context',  'addEditorButton');
add_action( 'admin_footer',  'add_inline_popup_content' );

function addEditorButton($contexti) {

	$img = 'favicon.png';
	$title = 'An Inline Popup!';
	$plugurl = plugins_url();
	
	$contexti .= '<a href="#TB_inline?width=400&inlineId=popup_container" id="insert-istribute-button" class="thickbox button insert-istribute add_is_video" data-editor="content" title="Add Istribute video"><span class="wp-media-buttons-icon"><img src="'.$plugurl.'/Istribute/favicon.png" style="max-width: 100%; padding: 0px; vertical-align: top;" /></span> Add Istribute video</a>';
	ob_start();
	?>
	<script>
		jQuery('#insert-istribute-button').click(function () {
			update_istribute_content_area();
		});
	</script>
	<?php
	$contexti .= ob_get_clean();
	
  return $contexti;
}

add_action( 'wp_ajax_istributeVidList', 'istributeVidList' );

function istributeVidList() {
	$istribute = getIstributeConnection();
	
	$videos = $istribute->getVideoList();
	echo '<ul id="selectedVid">';
	foreach ($videos as $video) {
		if (!is_object($video))
			continue;
		$aspect = $video->getAspect();
		if (!$aspect)
			$aspect = 1.67;
		$h = 300;
		$w = $h * $aspect;
		echo '<li style="width: 18%; margin: 1% 10px; float: left;" onclick="'.htmlspecialchars('send_istribute_iframe('.json_encode($video->getPlayerUrl()).','.json_encode($w).','.json_encode($h).');').'"><img src="https://joneirikdev-apiistributecom.webhosting.seria.net:8480' . $video->getPreviewImage() . '"></img>' . $video->getTitle() . '</li>';
	}
	echo '</ul>';
}

function add_inline_popup_content() {
	
	echo '<div id="popup_container" style="display: none;"><p>Video embed code:</p>';
	//echo '<textarea id="istributeUrl" rows="4" cols="50" style="max-width: 100%;"><iframe src="" width="550" height="300">Not working</iframe></textarea>';
	
	ob_start();
	?>
	<div id='istributeVidListContentArea'>
	</div>
	<script>
		function update_istribute_content_area() {
			jQuery.post(
				ajaxurl, 
				{
					'action': 'istributeVidList'
				}, 
				function(response){
					document.getElementById('istributeVidListContentArea').innerHTML = response;
				}
			);
		}
		function send_istribute_content(content) {
			window.top.send_to_editor(content);
		}
		function send_istribute_iframe(src, width, height) {
			var containerElement = document.createElement('div');
			var iframeElement = document.createElement('iframe');
			iframeElement.setAttribute('src', src);
			iframeElement.setAttribute('width', width);
			iframeElement.setAttribute('height', height);
			containerElement.appendChild(iframeElement);
			var textHtml = containerElement.innerHTML;
			send_istribute_content(textHtml);
		}
		
	</script>
	<input type='button' onclick='send_istribute_content();' value='Test'>
	<?php
	echo ob_get_clean();
	echo '</div>';
}

?>