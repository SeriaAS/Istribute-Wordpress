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

add_action('media_buttons_context',  'addEditorButton');
add_action( 'admin_footer',  'add_inline_popup_content' );

function addEditorButton($contexti) {

	$img = 'favicon.png';
	$title = 'An Inline Popup!';
	$plugurl = plugins_url();
	
	//$contexti .= '<a href="#TB_inline?width=700&inlineId=popup_container" id="insert-istribute-button" class="thickbox button insert-istribute add_is_video" data-editor="content" title="Add Istribute video"><span class="wp-media-buttons-icon"><img src="'.$plugurl.'/Istribute/favicon.png" style="max-width: 100%; padding: 0px; vertical-align: top;" /></span> Add Istribute video</a>';
	$contexti .= '<a href="#" id="insert-istribute-button" class="button insert-istribute add_is_video" data-editor="content" title="Add Istribute video"><span class="wp-media-buttons-icon"><img src="'.$plugurl.'/Istribute/favicon.png" style="max-width: 100%; padding: 0px; vertical-align: top;" /></span> Add Istribute video</a>';
	
	ob_start();
	?>
	<script>
		jQuery('#insert-istribute-button').click(function (event) {
			event.preventDefault();
			update_istribute_content_area();

			var id = 'istribute_popup_container';
			var popupFrame = document.getElementById(id);
			var margin = 150;
			var width = jQuery(window).width() - (2 * margin);
			var height = jQuery(window).height() - (1 * margin);
			jQuery(popupFrame).css({
				'position': 'fixed',
				'top': margin/2+'px',
				'right': margin+'px',
				'display': 'block',
				'background-color': 'white',
				'width': width+'px',
				'height': height+'px',
				'z-index': '1000000',
				'border': '1px solid #dddddd',
				'overflow': 'auto'
			});
			var overlay = document.createElement('div');
			overlay.setAttribute('id', id+'_overlay');
			document.body.appendChild(overlay);
			jQuery(overlay).css({
				'position': 'fixed',
				'top': '0px',
				'left': '0px',
				'width': '100%',
				'height': '100%',
				'background-color': 'black',
				'z-index': '999999',
				'opacity': '0.7'
			});
		});
	</script>
	<?php
	$contexti .= ob_get_clean();
	
  return $contexti;
}

add_action( 'wp_ajax_istributeVidUploader', 'istributeUploader' );

function istributeUploader() {
    $istribute = getIstributeConnection();
    
    if(isset($_FILES['file'])) {
         $video = $istribute->uploadVideo($_FILES['file']['tmp_name']);
         echo "<!DOCTYPE html>\n";
         ?>
         <title>Uploader</title>
         <script>
            parent.window.istributeFileUploaded(<?php echo json_encode($video->getId()); ?>);
         </script>
         <?php
         die();
    }

    echo "<!DOCTYPE html>\n";
    ?>
    <title>Uploader</title>
    <form id='uploadForm' enctype='multipart/form-data' action='' method='post' style='padding: 10px 30px 0px 0px;'>
        <input type='file' name='file' value='Choose file' style='border: 1px solid rgb(167, 166, 166); color: rgb(167, 166, 166);'>
        <input type='text' name='title' value='' placeholder='Title'>
        <input type='text' name='description' value='' placeholder='Description'>
        <input type='submit' value='Upload'>
    </form>
    <script>
    (function () {
        var onSubmit = function () {
            parent.window.istributeFileUploadStarts();
        };
        var uploadForm = document.getElementById('uploadForm');
        if (uploadForm.addEventListener)
            uploadForm.addEventListener('submit', onSubmit, false);
        else
            uploadForm.attachEvent('onsubmit', onSubmit);
    })();
    </script>
    <?php
    die();
}

add_action( 'wp_ajax_istributeVidList', 'istributeVidList' );

function istributeVidList() {
	$istribute = getIstributeConnection();
	$videos_pre = $istribute->getVideoList();
	
	$videos = array();
	foreach ($videos_pre as $video)
	    array_unshift($videos, $video);
	
	echo '<ul id="selectedVid">';
	foreach ($videos as $video) {
		if (!is_object($video))
			continue;
		$aspect = $video->getAspect();
		if (!$aspect)
			$aspect = 1.67;
		$h = 300;
		$w = $h * $aspect;
		echo '<li style="width: 18%; height: 200px; margin: 1% 10px; float: left; cursor: pointer;" onclick="'.htmlspecialchars('send_istribute_iframe('.json_encode($video->getPlayerUrl()).','.json_encode($w).','.json_encode($h).');').'"><img style="max-width:100%;" src="https://joneirikdev-apiistributecom.webhosting.seria.net:8480' . $video->getPreviewImage() . '"></img>' . $video->getTitle() . '</li>';
	}
	echo '</ul>';
	die();
}

function add_inline_popup_content() {
	ob_start();
	?>
	<div id="istribute_popup_container" style="display: none;">
	   <div style="height: 40px; padding: 10px 35px; background-color: rgb(61, 175, 175);"><img src="https://istribute.com/assets/img/istribute-logo.png" style="float: left;"></img><span onclick="closeIsPopup();" style="float: right;">X</span></div>
    	<h3 style="padding: 0px 28px;" >Upload a video:</h3>
    	<div id="istributeVidUploaderArea" style="padding: 0px 20px;">
    	</div>
    	<h3 style="padding: 0px 28px;">Choose a video:</h3>
    	<div id='istributeVidListContentArea' style="padding: 0px 18px;">
    	</div>
	</div>
	<script>
	    function closeIsPopup() {
		   document.getElementById('istribute_popup_container').style.display="none";
		   var overlay = document.getElementById('istribute_popup_container_overlay');
		   document.body.removeChild(overlay);
	    }

	    (function () {
		    var uploaderIframe;
    	    var istributeInsertUpload = function() {
    		    var width = '100%';
    		    var height = '110px';
    		    var containerElement = document.getElementById('istributeVidUploaderArea');
    	    	var iframeElement = document.createElement('iframe');
    			iframeElement.setAttribute('src', ajaxurl+'?action=istributeVidUploader');
    			iframeElement.setAttribute('width', width);
    			iframeElement.setAttribute('height', height);
    			containerElement.appendChild(iframeElement);
    			uploaderIframe = iframeElement;
    	    }
    	    window.istributeFileUploadStarts = function () {
    		    jQuery(uploaderIframe).css({'display': 'none'});
    	    }
    	    window.istributeFileUploaded = function(id) {
    		    uploaderIframe.parentNode.removeChild(uploaderIframe);
    		    istributeInsertUpload();
    		    update_istribute_content_area();
    	    }
    
    	    istributeInsertUpload();
	    })();
		
    	function update_istribute_content_area() {
    		document.getElementById('istributeVidListContentArea').innerHTML = "Loading...";
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
	<?php
	echo ob_get_clean();
}

?>