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
	$appid = get_option('istribute_appId');
	$appkey = get_option('istribute_appKey');
    return new \Seria\istributeSdk\Istribute($appid,$appkey,'http://api.istribute.com');
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

        <style>
            #wpcontent {background-color: #19b0bf;}
            #wpcontent {margin-left: 160px; padding-left: 20px;}
        </style>
		
		<div class="wrap" style="color: white;">
            <img src="https://istribute.com/assets/img/istribute-logo.png"></img>
			<?="<h2 style='color: white;'>" . __( 'Options', 'istribute_trdom' ) . "</h2>"; ?>

			<form name="istribute_form" method="post" action="<?=str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
				<input type="hidden" name="istribute_hidden" value="Y">
				<p style="font-size: 16px;"><?php _e("App Id: " ); ?><br/><input type="text" style="max-width: 100%;" name="istribute_appId" value="<?=$appid; ?>" size="40"></p>
				<p style="font-size: 16px;"><?php _e("App Key: "); ?><br/><input type="text" style="max-width: 100%;" name="istribute_appKey" value="<?=$appkey; ?>" size="40"></p>
				<hr />
				<p class="submit">
					<input style="background-color: #fd6120;border: 0px;color: white;padding: 10px;font-weight: bold;font-size: 16px;"type="submit" name="Submit" value="<?php _e('Update Options', 'istribute_trdom' ) ?>" />
				</p>
			</form>
		</div>
	<?php
	}
}

add_action('admin_menu','super_plugin_menu');

function super_plugin_menu() {
		add_menu_page("Istribute", "Istribute", 0, "istribute-settings", "super_plugin_options", '', '', 8);
}

add_action('media_buttons_context',  'addEditorButton');
add_action( 'admin_footer',  'add_inline_popup_content' );

function addEditorButton($contexti) {

	$img = 'favicon.png';
	$title = 'An Inline Popup!';
	$plugurl = plugins_url();
	$contexti .= '<a href="#" id="insert-istribute-button" class="button insert-istribute add_is_video" data-editor="content" title="Add Istribute video"><span class="wp-media-buttons-icon"><img src="'.$plugurl.'/Istribute/favicon.png" style="max-width: 100%; padding: 0px; vertical-align: top;" /></span> Add Istribute video</a>';
	
	ob_start(); ?>
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
				'right': '15%',
				'display': 'block',
				'background-color': 'white',
				'width': '70%',
				'height': height+'px',
				'z-index': '1000000',
				'border': '1px solid #dddddd',
				'overflow': 'auto',
				'background-color': '#ffffff'
			});
			var overlay = document.getElementById(id+'_overlay');
			if (typeof(overlay) == 'undefined' || !overlay) {
    			overlay = document.createElement('div');
    			overlay.setAttribute('id', id+'_overlay');
    			document.body.appendChild(overlay);
			}
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
         $video->setTitle($_POST['vidtitle']);
         $video->save();
         echo "<!DOCTYPE html>\n"; ?>
         <title>Uploader</title>
         <script>
            parent.window.istributeFileUploaded(<?php echo json_encode($video->getId()); ?>);
         </script>
         <?php
         die();
    }

    echo "<!DOCTYPE html>\n"; ?>
    <title>Uploader</title>
    <form id='uploadForm' enctype='multipart/form-data' action='#' method='post' style='padding: 10px 30px 0px 0px;'>
        <input type='file' name='file' value='Choose file' style='max-width: 100%; border: 1px solid rgb(167, 166, 166); color: black; '>
        <input style="padding: 2px; max-width: 100%;" type='text' id="vidtitle" name='vidtitle' value='' placeholder='Title'>
        <input style="background-color: #fd6120;border: 0px;color: white;padding: 6px;font-weight: bold;font-size: 12px;" type='submit' value='Upload'>
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
	try {
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
    		echo '<li style="width: 18%; height: 200px; min-width: 150px; margin: 1% 10px; float: left; cursor: pointer;" onclick="'.htmlspecialchars('send_istribute_iframe('.json_encode($video->getPlayerUrl()).','.json_encode($w).','.json_encode($h).');').'"><img style="max-width:100%;" src="http://api.istribute.com' . $video->getPreviewImage() . '"></img><p style=" color: black; font-size: 16px; margin: 0px;">' . $video->getTitle() . '</p></li>';
    	}
    	echo '</ul>';
	} catch (\Seria\istributeSdk\IstributeErrorException $e) {
	    echo '<p style="padding-left: 10px;">Something went wrong!<br>Check your App id / App key under in the Istribute tab, or contact post@seria.no</p>';
	}
	die();
}

function add_inline_popup_content() {
    $plugurl = plugins_url();
    ob_start(); ?>
	<div id="istribute_popup_container" style="display: none; ">
	   <div style="height: 40px; padding: 10px 35px; background-color: #0f4f6d;"><img src="https://istribute.com/assets/img/istribute-logo.png" style="float: left;"></img><img class="close_button" src="<?=$plugurl?>/Istribute/close.png" onclick="closeIsPopup();" style="float: right; margin-top: 5px; cursor: pointer;"></img></div>
    	<h3 style="padding: 0px 28px;" >Upload a video:</h3>
    	<div id="istributeVidUploaderArea" style="padding: 0px 20px;">
    	</div>
    	<h3 style="padding: 0px 28px;">Choose a video:</h3>
    	<div id='istributeVidListContentArea' style="padding: 0px 18px;">
    	</div>
	</div>
	<style>
        @media screen and (max-width: 400px) {
            .close_button {
                display: none;
            }
        }
    </style>
	<script>
	    function closeIsPopup() {
		   document.getElementById('istribute_popup_container').style.display="none";
		   var overlay = document.getElementById('istribute_popup_container_overlay');
		   document.body.removeChild(overlay);
	    }

	    (function () {
		    var uploaderIframe;
		    var uploadingFlagElement;
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
    		    uploadingFlagElement = document.createElement('div');
    		    uploaderIframe.parentNode.appendChild(uploadingFlagElement);
    		    uploadingFlagElement.innerHTML = "<p style='padding-left: 10px;'>Uploading...</p>";
    	    }
    	    window.istributeFileUploaded = function(id) {
        	    uploadingFlagElement.parentNode.removeChild(uploadingFlagElement);
    		    uploaderIframe.parentNode.removeChild(uploaderIframe);
    		    istributeInsertUpload();
    		    setTimeout(function() {
 		    	   update_istribute_content_area();
    		    }, 1000);
    	    }
    
    	    istributeInsertUpload();
	    })();
		
    	function update_istribute_content_area() {
    		document.getElementById('istributeVidListContentArea').innerHTML = "<p style='padding-left: 10px;'>Loading...</p>";
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
			closeIsPopup();
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
} ?>