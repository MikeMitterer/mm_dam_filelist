<?php
$output = '<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE html  PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de" lang="de">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

	<link rel="stylesheet" type="text/css" href="/typo3conf/ext/mm_dam_filelist/pi1/res/css/dam_video.css" />
	<script src="/typo3conf/ext/mm_dam_filelist/pi1/res/mediaplayer/swfobject.js" type="text/javascript"></script>

	<title>Videoscreen</title>

	<script type="text/javascript">
		var basepath 	= "/###PLAYERPATH###/";
		var skinfile 	= "skins/nacht/nacht.swf";
		var videopath 	= "/###FILE_PATH###/"; 	// Pfad entweder absolut oder relativ zum Player
				
		var flashvars = {file: videopath + "###FILE_NAME###",skin: basepath + skinfile};
		var params = {allowfullscreen: "true", allowscriptaccess: "always", allownetworking: "all"};
		var attributes = {};
		
		swfobject.embedSWF(basepath + "player.swf", 
				"myAlternativeContent", 
				"###WIDTH###", "###HEIGHT###", 
				"9.0.0",
				basepath + "expressInstall.swf", flashvars, params, attributes);
	</script>
</head>
<body id="mmdamvideopage">
	<div class="videoblock">
		<div id="myAlternativeContent">
			<a href="http://www.adobe.com/go/getflashplayer">
				<img src="http://www.adobe.com/images/shared/download_buttons/get_flash_player.gif" alt="Get Adobe Flash player" />
			</a>
		</div>
	</div>
</body>
</html>';

function getDocRoot() {
	return $_SERVER['DOCUMENT_ROOT'];
}

$filepath 	= $_GET['filepath'];
$filename 	= $_GET['filename'];
$width 		= $_GET['width'];
$height 	= $_GET['height'];

$playerpath = 'typo3conf/ext/mm_dam_filelist/pi1/res/mediaplayer';
$docroot 	= getDocRoot();

if(!is_numeric($width) || !is_numeric($height)) die("Width and height must be numbers!");
if($width == 0 || $height == 0) 				die("Width and height must not be 0!");
if(!is_dir($docroot . '/' . $filepath)) 				die("Wrong filepath, path ($filepath) does not exist!");
if(!is_file($docroot . '/' . $filepath . $filename)) 	die("Wrong filename, path ($filepath$filename) does not exist!");

$output = str_replace('###FILE_PATH###',$filepath,$output);
$output = str_replace('###FILE_NAME###',$filename,$output);
$output = str_replace('###PLAYERPATH###',$playerpath,$output);
$output = str_replace('###WIDTH###',$width,$output);
$output = str_replace('###HEIGHT###',$height,$output);

echo $output;
?>