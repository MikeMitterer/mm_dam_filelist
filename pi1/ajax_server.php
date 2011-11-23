<?php
$xapath = '../../';

require_once($xapath . "mm_bccmsbase/xajax/xajax_core/xajax.inc.php");
require_once($xapath . 'mm_bccmsbase/lib/class.mmlib_xajaxwrapper.php');

function ajaxGetPreview($tagID,$filename) {
	$response = new xajaxResponse();
	//t3lib_div::debug($GLOBALS['TSFE']);
	
	$response->call("changePreviewImage", $tagID,'hallo test2');
	return $response;
}

	
$xajaxwrapper = new mmlib_xajaxwrapper();
$xajaxwrapper->init(true);
//-//$this->xajax->registerFunction(array("getPreView",&$this,ajaxGetPreview));
$xajaxwrapper->registerFunction('ajaxGetPreview');
$xajaxwrapper->xajax->processRequest();
?>