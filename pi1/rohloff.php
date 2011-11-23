<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2005 Mike Mitterer (mike.mitterer@bitcon.at)
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

/**
 * Plugin 'MM DAM FE FileList' for the 'mm_dam_filelist' extension.
 *
 * @author	Mike Mitterer <mike.mitterer@bitcon.at>
 */

	/**
	 * xajax - Sample script
	*/
	function callScript()
	{
		$response = new xajaxResponse();
		$value2 = "this is a string";
		//t3lib_div::debug($GLOBALS['TSFE']);
		
		$response->call("myJSFunction", "arg1", 9432.12, array("myKey" => "some value", "key2" => $value2));
		return $response;
	}
/*
	function ajaxGetPreview($tagID,$filename)
	{
		$response = new xajaxResponse();
		//t3lib_div::debug($GLOBALS['TSFE']);
		
		$response->call("changePreviewImage", $tagID,'hallo test');
		return $response;
	}
	
*/
		
	
require_once(t3lib_extMgm::extPath('mm_bccmsbase').'lib/class.mmlib_extfrontend.php');
require_once(t3lib_extMgm::extPath('mm_bccmsbase').'lib/class.mmlib_crypt.php');
require_once(t3lib_extMgm::extPath('mm_bccmsbase').'lib/class.mmlib_folderui.php');
require_once(t3lib_extMgm::extPath('mm_bccmsbase').'lib/class.mmlib_tree.php');
require_once(t3lib_extMgm::extPath('mm_bccmsbase').'lib/class.mmlib_minibenchmark.php');

//require(t3lib_extMgm::extPath('mm_bccmsbase'). "xajax/xajax_core/xajax.inc.php");
//require_once(t3lib_extMgm::extPath('mm_bccmsbase').'lib/class.mmlib_xajaxwrapper.php');

/**
 * Displays files from the DAM Table.
 * Kategories have to be set.
 * 
 * @author	Mike Mitterer <mike.mitterer@bitcon.at>
 */
	
//$xajax = t3lib_div::makeInstance('mmlib_xajaxwrapper');
//$xajax->init(true);
//-//$this->xajax->registerFunction(array("getPreView",&$this,ajaxGetPreview));
//$xajax->registerFunction('ajaxGetPreview');

//		$xajax->processRequest();

class tx_mmdamfilelist_pi1 extends mmlib_extfrontend {
	var $prefixId 			= 'tx_mmdamfilelist_pi1';								// Same as class name
	var $scriptRelPath 		= 'pi1/class.tx_mmdamfilelist_pi1.php';	// Path to this script relative to the extension dir.
	var $pi_checkCHash 		= TRUE;																	// If set, then caching is disabled if piVars are incoming while no cHash was set (Set this for all USER plugins!)
	var $folderui			= null;
	var $template			= null;
	var $cattree			= null;
	var $minibench			= null;
	var $extConf 			= null;
	
	/**
	 * Main-function
	 *
	 * @param	[string]		$content: normaly not set
	 * @param	[array]			$conf: 		TS Configuration for this plugin
	 *
	 * @return	[string]	Data in a Table
	 */
	function main($content,$conf)	
		{
		global $xajax;
		
		foreach($this->piVars as $key => $value) {
			//t3lib_div::debug($key . '-' . $value);
		    }
		
		$conf 			= $this->initPlugin($conf);
		$this->extConf 	= unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['mm_dam_filelist']);
		
		//t3lib_div::debug($this->extConf,'$this->extConf');
		//t3lib_div::debug($this->piVars,'$this->piVars');
		
		// Overwrites the Configsettings
		if(isset($this->piVars['viewmode'])) 		$this->conf['view_mode'] = $this->piVars['viewmode'];
		if(strlen(trim($this->piVars['sword']))) 	$this->conf['view_mode'] = 'list';
		if($this->conf['view_mode'] == 'tree') 		unset($this->piVars['mode']);
		
		if(isset($this->piVars['showuid'])) $this->setViewType('singleView');
		
		if($this->conf['use_ajax']) $this->initAJAX();
		
		//$xajax->processRequest();
		
		//$content .= $this->getCategoryTree();
		$this->minibench = t3lib_div::makeInstance('mmlib_minibenchmark');
		//$this->minibench->init(false);
		$content .= $this->getContentForView($view);
		//$this->minibench->showstat();

		return $this->pi_wrapInBaseClass($content);
		}	 

/*		
$xajax = new xajax();
//$xajax->setFlag("debug", true);
$xajax->registerFunction("callScript");
$xajax->registerFunction(array("callScript2",&$this,ajaxGetPreview));
$xajax->processRequest();
ob_start();
$xajax->printJavascript("/typo3conf/ext/mm_bccmsbase/xajax/");
$GLOBALS['TSFE']->additionalHeaderData[] = ob_get_contents();
ob_end_clean();
*/
		
	/**
	 * Do some initialisation work
	 *
	 * @param	[array]			$conf:TS Configuration for this plugin
	 *
	 * @return	[array]			returns the configuration-Array
	 */
	function initPlugin($conf) {
		$aInitData['tablename'] 	= 'tx_dam';
		$aInitData['uploadfolder'] 	= 'tx_mmdamfilelist';
		$aInitData['extensionkey'] 	= 'mm_dam_filelist';
		
		// Optional
		$aInitData['flex2conf'] 		= $this->getFLEXConversionInfo();

		$conf = $this->initFromArray($conf,$aInitData);
		
		$this->cattree = t3lib_div::makeInstance('mmlib_tree');
		$this->cattree->init($this);
		
		
		return $conf;	
		}
		
	function initAJAX() {
		$this->xajax = t3lib_div::makeInstance('mmlib_xajaxwrapper');
		if(!$this->xajax) return;
		
		$this->xajax->init(false,'/typo3conf/ext/mm_dam_filelist/pi1/ajax_server.php');
		//$this->xajax->registerFunction(array("getPreView",&$this,ajaxGetPreview));
		$this->xajax->registerFunction('ajaxGetPreview');
		$this->xajax->processRequest();
		
	}
	/*
	 * Only for testing!
	 */
	function folderView($content) {
		$folderui = t3lib_div::makeInstance('mmlib_folderui');
		$folderui->init($this,'fileadmin/redaktion/');
		
		$content .= '(' . $folderui->getCurrentFolder() . ')<br>';
		$content .= $folderui->getContent();
		$this->internal['this_dam_path_only']  = $folderui->getCurrentFolder();
			
		$res = $this->execQuery();
		while($res && ($record = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))) {
			$content .= $record['f$confile_name'] .  '(' .  $record['file_path'] . ')<br>';
			//debug($record,1);
		}
		
	 	return $content;
	}
	
	/**
	 * Converts the tx_dam_cat Table into a unordered list
	 * You can customize the list elements by changing the cat_tree_view.tmpl
	 * You can insert the list in your page-template with something like this:
	 * 		page.10.subparts.navi_dam_cat < plugin.tx_mmdamfilelist_pi1.catTree
	 *
	 * @param	[string]		$content: Content given by the framework, normaly empty
	 * @param	[array]			$conf: Pluginconfiguration array
	 * 	 *
	 * @return	[string]		tx_dam_cat in a list
	 */
	function getCategoryTree($content,$localconf) {
		$conf = $localconf['localconf.'];
		$this->initPlugin($conf);

		$uid = (isset($localconf['startuid']) ? $localconf['startuid'] : 0);
		//t3lib_div::debug($localconf,1);
		
		return $this->cattree->getCategoriesTreeView($uid);
	}
	
	function getCSSStatement($content,$localconf) {
		t3lib_div::debug($content,1);
		t3lib_div::debug($localconf,1);
		
		return '/* Hallo MIKE */';
	}
	
	function getViewSelectorTS($content,$localconf) {
		$conf = $localconf['localconf.'];
		$this->initPlugin($conf);

		//t3lib_div::debug($localconf,1);
		//$this->initInternalVars($this->getViewType());
		
		//$this->conf['add_modeswitch_toplistview']
		$this->initInternalViewSelector();
		
		//t3lib_div::debug($this->internal['orderselector'],'orderselector');
		
		//$this->internal['viewselector']['toplist'] = "Switch to TopListView";
		
		$content .= $this->getViewSelector();	
		//t3lib_div::debug($content,'$content');
			
		return $content;
	}
	
	
	/**
	 * Makes the "pre-initialisation" for the SQL-Query
	 * For more-information look at the base-class Doku
	 *
	 * @param	[string]		$strView: listView or singleView - these are the names from the TS Code
	 * @return	[void]		
	 */	
	function setInternalQueryParameters($strView) {
		$lConf = $this->conf[$strView . '.'];	// get LocalSettings
		
		parent::setInternalQueryParameters($strView);
		
		if($this->conf['view_mode'] == 'tree') {
			$this->template = $this->getTemplateContent('treeView');
			
			if($this->folderui == null) {
				$this->folderui = t3lib_div::makeInstance('mmlib_folderui');
				$this->folderui->init($this,$this->conf['treeView.']['basepath_for_folders']);
				
				// The method _renderPath will be called (no inheritance - use just this class)
				// This function is only usefull for the tree-mode
				$this->folderui->setRenderingCallback($this,"_renderPath");
			}

			$this->internal['this_dam_path_only']  = $this->folderui->getCurrentFolder();
		} else {
			$this->template = $this->getTemplateContent($strView);
		}
	}

	/**
	 * Callback for displaying the folders in the TreeView
	 *
	 * @param	[string]	$linkText: The foldername without the path
	 * @param	[string]	$folder: The full path-name
	 * @param	[string]	$countFolders: 0 is the current folder - all other numbers are sub-folders
	 * 	 * 
	 * @return	[string]	The HTML-Code for the listView
	 */
	
 	function _renderPath($linkText,$folder,$countFolders) {
		$iconsetExtension = $this->conf['iconset_extension'] ? $this->conf['iconset_extension'] : '.gif';
 		$content					= '';
		$templateFolder 	= $this->cObj->getSubpart($this->template,$countFolders != 0 ? '###FOLDER###' : '##UP_FOLDER###');
		$iconsetPath			= $this->internal['iconset_path_filesystem'];
		$iconName					= 'folder' . $iconsetExtension;	// Default foldername in this iconset
		
		if($countFolders == 0 && $this->folderui->isTopLevelFolder()) {
			$templateFolder 	= $this->cObj->getSubpart($this->template,'###UP_FOLDER_DISABLED###');
			$iconName			= 'up_disabled' . $iconsetExtension;	// name for uplink
		} else if($countFolders == 0) {
			$iconName			= 'up' . $iconsetExtension;				// there is no mor uplink
		}

		//debug($this->piVars);
		//debug("------------------",1);
		
 		$link		= $this->pi_linkTP_keepPIvars($linkText,array('getSubFolders' => $folder),$cache=1);	
 		// Explode with the < + > sign makes things faster (the alternative would be preg_split...
		$href		= explode('>' . $linkText . '<',$link);
		
		// overwrite the default ICON-Path
		$fileResource = 'EXT:' . $this->extKey . $iconsetPath . $iconName;
		if($this->conf['iconset_filesystem'] && file_exists(PATH_site . $this->conf['iconset_filesystem'])) {
			$fileResource =  $this->conf['iconset_filesystem'] . $iconName;
		}

		$imageTag 	= $this->cObj->fileResource($fileResource);	

		$markerArray['###LINK_TEXT###']			= $linkText;
		$markerArray['###FOLDER_NAME###']		= $folder;
		$markerArray['###HREF_BEGIN###']		= $href[0] . '>';
		$markerArray['###HREF_END###']			= '<' . $href[1];
		$markerArray['###CLASS_NAME###']		= $this->pi_getClassName('');
		$markerArray['###EXTENSION_IMAGE###']	= $imageTag;
		$markerArray['###EVEN_ODD###']			= ($countFolders % 2 ? "odd" :"even");
		
		$content = $this->cObj->substituteMarkerArray($templateFolder,$markerArray);		

		return $content;
 	}
 	
	/**
	 * Make some special things for the "TreeView". If the TreeView-View is
	 * not turned on - it makes a normal table.
	 *
	 * @param	[string]		$res: Resource-ID from the DB-Query
	 * @param	[string]		$tableParams: Additional params for the table
	 * 
	 * @return	[string]	The HTML-Code for the listView
	 */
	function pi_list_makelist($res,$tableParams='') {
		$lConf 				= $this->conf[$this->getViewType() . '.'];	// get LocalSettings
		$content 			= '';
		$content_folders	= '';
		$content_files		= '';
		$templateFile		= $this->cObj->getSubpart($this->template,'###FILE###');
		$templateTree		= $this->cObj->getSubpart($this->template,'###TREE###');
		
		$isTreeStyle = ($this->conf['view_mode'] == 'tree');
		
		if($isTreeStyle == true) {
			$this->_resetDummyFieldList();
			
			// Show the folders
			$markerArray['###CLASS_NAME###']		= $this->pi_getClassName('');;
			$markerArray['###CURRENT_FOLDER###']	= str_replace($this->conf['treeView.']['basepath_for_folders'],'',$this->folderui->getCurrentFolder());
			$templateTree = $this->cObj->substituteMarkerArray($templateTree,$markerArray);		
			
			// Show the folders
			$content_folders = $this->folderui->getContent();
			
			// overwrite the default ICON-Path
			$iconsetPath			= 'EXT:' . $this->extKey . $this->internal['iconset_path_mimetypes'];
			if($this->conf['iconset_mimetypes'] && file_exists(PATH_site . $this->conf['iconset_mimetypes'])) {
				$iconsetPath =  $this->conf['iconset_mimetypes'];
			}
			$iconsetExtension = $this->conf['iconset_extension'] ? $this->conf['iconset_extension'] : '.gif';
			
			// And now... - we generate the Filelist
			$counterLine = 0;
			while($res && ($record = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))) {
				if(!$this->isThisRecordValid($record)) continue;
				
				// clear the array
				unset($markerArray);
				
				//$href		= explode('>' . $linkText . '<',$link);
				
				
				$aFileName	= t3lib_div::split_fileref($record['file_name']);
				$extension	= $aFileName['fileext'];
				
				$imageFileExtension 	= $imageFileDefault = $iconsetPath;
				
				// makes a mimetype fallback!!!
				$imageIcon[0] 			= $imageFileExtension . $record['file_mime_type'] . $iconsetExtension;
				$imageIcon[1] 			= $imageFileExtension . $record['file_mime_subtype'] . $iconsetExtension;
				$imageIcon[2] 			= $imageFileExtension . 'unknown' . $iconsetExtension;
				
				$imageTag = '';
				foreach($imageIcon as $filename) {
					$imageTag = $this->cObj->fileResource($filename);	
					if($imageTag != '') break;
				}
				
				// init the internal data-structure (ist needed for getFieldContent
				$this->setCurrentRow($record);

				// Make all the table-fields available for the template
				foreach($record as $key => $value) {
					$markerArray['###' . strtoupper($key) . '###']			= $this->getFieldContent($key);
					$markerArray['###' . strtoupper($key) . '_VALUE###']	= $value;
				}
				$markerArray['###NORMALLINK###']		= $this->getFieldContent('normallink');
				$markerArray['###ZIPLINK###']			= $this->getFieldContent('ziplink');
				
				$href_normal = array('','','','');
				if(preg_match("#(<[^>]*>)([^<]*)(<[^>]*>)#",$markerArray['###NORMALLINK###'],$href)) $href_normal = $href;
				
				$href_zip = array('','','','');
				if(preg_match("#(<[^>]*>)([^<]*)(<[^>]*>)#",$markerArray['###ZIPLINK###'],$href)) $href_zip = $href;
				
				$markerArray['###LINK_TEXT###']			= $record['file_name'];
				$markerArray['###FOLDER_NAME###']		= $record['file_path'];
				$markerArray['###HREF_BEGIN###']		= $href_normal[1];
				$markerArray['###HREF_END###']			= $href_normal[3];
				$markerArray['###HREFZIP_BEGIN###']		= $href_zip[1];
				$markerArray['###HREFZIP_END###']		= $href_zip[3];
				$markerArray['###CLASS_NAME###']		= $this->pi_getClassName('');
				$markerArray['###EVEN_ODD###']			= ($counterLine % 2 ? "odd" :"even");
				$markerArray['###EXTENSION_IMAGE###']	= $imageTag;
				
				$markerArray['###FILE_SIZE_KB###']		= round($record['file_size'] / 1024,2);
				$markerArray['###FILE_SIZE_MB###']		= round($record['file_size'] / 1024 / 1024,2);
				$markerArray['###FILE_MTIME_DMY###']	= strftime('%d.%m.%Y',$record['file_mtime']);
				$markerArray['###FILE_MTIME_MDY###']	= strftime('%m.%d.%Y',$record['file_mtime']);
				

				
				//debug($markerArray);
				
				$content_files .= $this->cObj->substituteMarkerArray($templateFile,$markerArray);		
				$counterLine++;
			}
			
			$content = $this->cObj->substituteSubpart($templateTree,'###DIR_STRUCT###',$content_folders . $content_files);
		}
		// Do the things which are normal for the "listView"
		else {
//			$templatePreView						= $this->cObj->getSubpart($this->template,'###BIG_PREVIEW###');
			
			$content 			= parent::pi_list_makelist($res,$tableParams);
/*			
//--------------------------			
			$this->_resetDummyFieldList();
			$res = $this->execQuery(0,"tx_dam.uid=113");
			
			while($res && ($record = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))) {
				// init the internal data-structure (ist needed for getFieldContent
				unset($this->internal['currentRow']);
				foreach($record as $key => $value) $this->internal['currentRow'][$key] = $value;
				foreach($this->_dummyFieldList as $key => $value) $this->internal['currentRow'][$key] = $value;	
			}			
//------------------------------------------------			

			$markerArray['###BIG_PREVIEW_IMAGE###']	= $this->getFieldContent('big_preview_image');			
			$content_preview 	= $this->cObj->substituteMarkerArray($templatePreView,$markerArray);	

			return $content_preview . $content;
*/
		}
		
		return $content;
	}
			
	/**
	 * The array ties together the static-TS configuration and the Flex Form
	 * If data is set in both areas then FlexForm settings have the priority.
	 *
	 * @return	[array]	- Information about the TS2Flex connection
	 */	
	function getFLEXConversionInfo()	
		{
		return array(
			'view_mode'						=> 'sMAIN:view_mode',
			'add_modeswitch_listview'		=> 'sMAIN:add_modeswitch_listview',
			'add_modeswitch_toplistview'	=> 'sMAIN:add_modeswitch_toplistview',
			'add_modeswitch_treeview'		=> 'sMAIN:add_modeswitch_treeview',
			'add_modeswitch_category'		=> 'sMAIN:add_modeswitch_category',
			'showOrderSelector'				=> 'sMAIN:show_order_selector',
			'show_empty_page_if_no_pivars'	=> 'sMAIN:show_empty_page_if_no_pivars',
			'allowCaching'					=> 'sMAIN:allow_caching',
			'singlePid'						=> 'sSINGLEVIEW:single_pid',
			'show_filter'					=> 'sMAIN:show_filter',
		
			'show_download_link'	=> 'sLISTVIEW:show_download_link',
			'show_zip_link'			=> 'sLISTVIEW:show_zip_link',
			'show_details_link'		=> 'sLISTVIEW:show_details_link',
		
			'listView.' => array (
				'templateFile' 			=> 'sLISTVIEW:templatefile',
				'results_at_a_time' 	=> 'sLISTVIEW:results_at_a_time',
				'maxPages' 				=> 'sLISTVIEW:max_pages',
				'results_at_a_time' 	=> 'sLISTVIEW:results_at_a_time',
				'colsOnPage' 			=> 'sLISTVIEW:cols_on_page',
				'col_width_style' 		=> 'sLISTVIEW:col_width_style',
				'showSearchBox' 		=> 'sLISTVIEW:show_search_box',
				'showModeSelector' 		=> 'sLISTVIEW:show_mode_selector',
				'showBrowserResults' 	=> 'sLISTVIEW:show_browser_results',
				'showHeader' 			=> 'sLISTVIEW:show_header',
				'dontLinkActivePage' 	=> 'sLISTVIEW:dont_link_active_page',
				'showFirstLast' 		=> 'sLISTVIEW:show_first_last',
				),
				
			'singleView.' => array (
				'templateFile' 			=> 'sSINGLEVIEW:templatefile',
				),
				
			'treeView.' => array (
				'templateFile' 			=> 'sTREEVIEW:templatefile',
				'basepath_for_folders'	=> 'sTREEVIEW:basepath_for_folders',
				),
				
			'address.' => array (
				'address_pid' 			=> 'sADDRESS:address_pid',
				),
				
			'typodbfield.' => array (
				'preview.' => array (
					'useMIMEImage'	=> 'sLISTVIEW:use_always_mime_type_images',
				
					'file.'			=> array (
						'width'	=> 'sIMAGES:preview_width',
						'height'=> 'sIMAGES:preview_height',
						'maxW'	=> 'sIMAGES:preview_max_w',
						'maxH'	=> 'sIMAGES:preview_max_h',
						),
					'secure'		=> 'sIMAGES:secure_filename',
					'imageLinkWrap'	=> 'sIMAGES:make_popup',
					'imageLinkWrap.'=> array (
						'width'			=> 'sIMAGES:popup_image_w',
						'height'		=> 'sIMAGES:popup_image_h',
						'JSwindow.' 	=> array (
							'expand'	=> 'sIMAGES:js_window_expand',
							),
						),
				),
			),	
			);		
		}
		
	/**
	 * Sets the Modeselector. 
	 * The Modeselector is an array with the mode as index and the 
	 * Description (Text) as value
	 * The function is called by the framework
	 * 
	 * @param	[string]		$strView: strView
	 *
	 * @return	[void]	
	 */	
	function initInternalVars($strView)	{
		mmlib_extfrontend::initInternalVars($strView);

		// Old Version - got the data from the tt_content-Table
		//$this->internal['modeselector'] = $this->getDataFromForeignTable('tx_mmdamfilelist_mm_dam_category','tx_dam_cat','title');

		unset($this->internal['modeselector'][0]);
		
		// Now the cateories from FlexForm - not available in the TreeView
		if($this->conf['view_mode'] != 'tree' &&
			$this->conf['view_mode'] != 'cattree' &&
			$this->conf['add_modeswitch_category'] ) {
			$categories = $this->getDataFromForeignTable('sCATEGORIES:category','tx_dam_cat','title');
			
			// can be empty
			if(count($categories)) {
				foreach($categories as $key => $value) {
					$this->internal['modeselector']['category:' . $key] = $value;
				}
			}
		}

		if($this->conf['view_mode'] == 'tree') $this->internal['showBrowserResults'] = 0;
		//debug($this->internal['modeselector']);
		
		// Basesettings for these flags must be true
		if($this->conf['add_modeswitch_toplistview'] == true && $this->piVars['viewmode'] == 'toplist') {
			$this->internal["topList"] = true;
			$this->internal["topListField"] = $this->conf['toplist_field'];
			$this->internal["topListFieldDESCFlag"] = $this->conf['toplist_desc_flag'];
			$this->internal["topListFieldResultsAtATime"] = $this->conf['toplist_number_of_results'];
			$this->internal['showBrowserResults'] = 0;
			
			if($this->conf['toplist_turnoff_orderselector']) {
				$this->conf['showOrderSelector'] = 0;			
			}
		}
		
	}
	
	/**
	 * Called by the framework. Set's the internal vars for switching between different views
	 */
	function initInternalViewSelector() {
		$conf =  $this->conf[$this->getViewType() . '.'];

		// Looks for 'qlist_mode_list' in locallang.php
		if($this->conf['add_modeswitch_toplistview']) {
			$this->internal['viewselector']['toplist'] = "Switch to TopListView";
		}
		
		if($this->conf['add_modeswitch_listview']) {
			$this->internal['viewselector']['list'] = "Switch to ListView";
		}
		
		if($this->conf['add_modeswitch_treeview']) {
			$this->internal['viewselector']['tree'] = "Switch to TreeView";
		}
		
		//debug($this->internal['viewselector']);
	}
	
	function initInternalFilterSelector() {
		if($this->useTTAddress() && isset($this->conf['addressfilter.'])) {
			$this->internal['filterselector']['address'] = $this->getSubTableLinkWidget('',$this->conf['addressfilter.']['10.'],false);
		}
	}
	
		
	/**
	 * Executes the main-query from the ListView.
	 * Select all the Files from a specific Kategory ($this->piVars['mode'])
	 * 
	 * @param	[boolean]		$fCountRecords: Just count the records
	 * @param	[string]		$strWhereStatement: Optional additional WHERE clauses put in the end of the query
	 *
	 * @return	[integer]	pointer MySQL result pointer / DBAL object
	 */	
	function execQuery($fCountRecords = 0,$strWhereStatement = '')
		{
		$results_at_a_time 			= t3lib_div::intInRange($this->internal['results_at_a_time'],1,1000);
		$aCateory					= $this->piVars['mode'] && strstr($this->piVars['mode'],'category:') != false ? explode(':',$this->piVars['mode']) : null; // $aCateory[0] = "category", $aCateory[1] = 1 
		$aCateory					= strlen(trim($this->piVars['sword'])) > 0 ? null : $aCateory;
		$categoriesPreSelect 		= $this->getDataFromForeignTable('sCATEGORIES:category','tx_dam_cat','title');
		$categorySpecified			= (($aCateory != null && strlen(trim($aCateory[1])) > 0) || count($categoriesPreSelect) > 0);
		$pointer 					= intval($this->piVars['pointer']);
		
    	$SQL['limit']				= ($pointer * $results_at_a_time) . ',' . $results_at_a_time;
		$SQL['select'] 				= 'tx_dam.*,tx_dam_mm_cat.uid_foreign,tx_dam_cat.uid as catid,tx_dam_cat.title as cattitle';
		$SQL['local_table']			= 'tx_dam';
		$SQL['mm_table']			= 'tx_dam_mm_cat';
		$SQL['foreign_table']		= 'tx_dam_cat';
		$SQL['order_by']			= ''; // Defaultsettings are made in setup.txt (order)
		$SQL['group_by']			= '';
		
		/*
		t3lib_div::debug($SQL,1);
		t3lib_div::debug($categorySpecified,1);
		t3lib_div::debug($categoriesPreSelect,1);
		t3lib_div::debug($aCateory,1);
		t3lib_div::debug($this->piVars,1);
		t3lib_div::debug('+++++++++++++++++++++',1);
		*/
		
		
		if(isset($this->piVars["sword"]) &&
			strlen(trim($this->piVars['sword'])) > 0 &&
			isset($this->conf['group_search_results_by']))	{
			$SQL['group_by'] = $this->conf['group_search_results_by']; 
		} else if(isset($this->conf['group_results_by'])) {
			$SQL['group_by'] = $this->conf['group_results_by'];
		}
		
		
		// order_by can be overwritten by the piVar[order]
		if(isset($this->internal["orderBy"]) && t3lib_div::inList($this->internal["orderByList"],$this->internal["orderBy"])) {
			$SQL['order_by'] = $this->internal["orderBy"] . ($this->internal["descFlag"] ? ' DESC' : ''	);
		}

		// order_by and results_at a time can be overwritten by the piVar[toplist]
		if(isset($this->internal["topList"]) && t3lib_div::inList($this->internal["orderByList"],$this->internal["topListField"])) {
			$SQL['order_by'] 	= $this->internal["topListField"] . ($this->internal["topListFieldDESCFlag"] ? ' DESC' : ''	);
			$SQL['limit']		= '0,' . $this->internal["topListFieldResultsAtATime"];
		}
		
		$WHERE['enable_fields']		= $this->cObj->enableFields($this->getTableName());
		$WHERE['enable_fields_cat']	= $this->cObj->enableFields('tx_dam_cat');
		$WHERE['folder']			= strlen($this->internal['this_dam_path_only']) > 0 ? "tx_dam.file_path = '" . $this->internal['this_dam_path_only'] . "'": '' ;
		$WHERE['showuid']			= $this->piVars['showuid'] ? "AND tx_dam.uid='" . $this->piVars['showuid'] . "''" : '';
		$WHERE['statement']			= $strWhereStatement;

		if(isset($this->piVars['filterfield']) && isset($this->piVars['filterid'])) {
			//$this->piVars['filterfield'] = 'tx_mmdamfilelis_address_uid';
			// bei Rohloff notwendig
			$WHERE['filterfield']	= $this->piVars['filterfield'] . ' = ' . t3lib_div::intval_positive($this->piVars['filterid']);
		}
		
		if($categorySpecified) {
			$allPossibleCATIDs	= array();
			if(count($categoriesPreSelect)) {
				$preselectedIDs			= array_keys($categoriesPreSelect);
				
				$tempAllPossibleCATIDs	= array();
				foreach($preselectedIDs as $uid) {
					$tempAllPossibleCATIDs[]	= $uid; // Add the base UID to the Array
					$tempAllPossibleCATIDs 		= array_merge($tempAllPossibleCATIDs,$this->cattree->getAllChildIDs($uid));
				}
				foreach($tempAllPossibleCATIDs as $uid) {
					if(!in_array($uid,$allPossibleCATIDs)) {
						$allPossibleCATIDs[] = $uid;	
					}
				}
			}
			
			// If categories are preselected then $aCateory must be included in the pre selected categories
			if(count($allPossibleCATIDs) > 0 && $aCateory != null) {
				$baseCategoryID			= $aCateory[1];
				
				if(!in_array($baseCategoryID,$allPossibleCATIDs)) {
					return false;	
				}
			}
			
			// If there is a category - add all subcategories to the $allPossibleCATIDs array
			if($aCateory != null) {
				$baseCategoryID			= $aCateory[1];
				$allPossibleCATIDs		= $this->cattree->getAllChildIDs($baseCategoryID);
				$allPossibleCATIDs[]	= $baseCategoryID; // Add the base UID to the Array

				/*
				foreach($tempAllPossibleCATIDs as $uid) {
					if(!in_array($uid,$allPossibleCATIDs)) {
						$allPossibleCATIDs[] = $uid;	
					}
				}
				*/
			}
			
			$temp_where = null;
			foreach($allPossibleCATIDs as $value) {
				$temp_where[] = 'tx_dam_cat.uid IN (' . $value . ') ';
			}
			$WHERE['cat'] = 'AND (' . implode(' OR ',$temp_where) . ') ';
			
			//t3lib_div::debug($allPossibleCATIDs,1);
			//t3lib_div::debug($aCateory,1);
			//t3lib_div::debug($WHERE,1);
			//t3lib_div::debug('####################',1);
			/*
			if(strlen(trim($aCateory[1])) > 0 && key_exists(trim($aCateory[1]),$categoriesPreSelect)) {
				$WHERE['cat'] = 'AND tx_dam_cat.uid IN (' . $aCateory[1] . ') ';
			} else {
				$temp_where = null;
				foreach($categoriesPreSelect as $key => $value) {
					$temp_where[] = 'tx_dam_cat.uid IN (' . $key . ') ';
				}
				if($temp_where != null) {
					$WHERE['cat'] = 'AND (' . implode(' OR ',$temp_where) . ') ';
				}
			}
			*/
				
		}
		
		//t3lib_div::debug($aCateory,1);
		//t3lib_div::debug($categoriesPreSelect,1);
			
		// If there is noch category an no folder specified - show nothing
		//if($WHERE['cat'] == '' && $WHERE['folder'] == '')	return false;

		if($fCountRecords == true) {
			$SQL['select'] 		= 'count(*)';
			$SQL['limit']		= '';
		}
		
		if($WHERE['folder'] != '') $SQL['limit']		= '';
		
		/*
		t3lib_div::debug($SQL);
		t3lib_div::debug($WHERE);
		t3lib_div::debug('-----------------------------',1);
		*/
		
		$showLastQuery = false;
		if($showLastQuery) {
			$GLOBALS['TYPO3_DB']->debugOutput = true;
			$GLOBALS['TYPO3_DB']->store_lastBuiltQuery = true;
		}
		
		// if we have no category we need no MM-Query
		if($categorySpecified == false) {
			$SQL['select']		= 'tx_dam.*';
			
			unset($SQL['mm_table']);
			unset($SQL['foreign_table']);
			unset($WHERE['enable_fields_cat']);
						
			$SQL['where']		= $this->implodeWithoutBlankPiece('AND ',$WHERE);
			//$SQL['order_by']	= 'tx_dam.file_name';
			
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
				$SQL['select'],
				$SQL['local_table'],
				$SQL['where'],             
				$SQL['group_by'],
				$SQL['order_by'],
				$SQL['limit']
				);	
		}
		else {
			$SQL['where']		= 'AND ' . $this->implodeWithoutBlankPiece('AND ',$WHERE);
			
		 	$res = $GLOBALS['TYPO3_DB']->exec_SELECT_mm_query(
				$SQL['select'],
				$SQL['local_table'],
				$SQL['mm_table'],
				$SQL['foreign_table'], 
				$SQL['where'],
				$SQL['group_by'],		//	groupBy, 
				$SQL['order_by'],		// 	orderBy,
				$SQL['limit'] 			//	limit
				);
		}
		if($showLastQuery) t3lib_div::debug($GLOBALS['TYPO3_DB']->debug_lastBuiltQuery,"lastBuiltQuery=");
		
		//t3lib_div::debug($SQL);

		if(false) { // show SQL Statement
			if($SQL['foreign_table'] == $SQL['local_table']) {
		            $foreign_table_as = $SQL['foreign_table'].uniqid('_join');
		            }
		 
			$mmWhere = $SQL['local_table'] ? $SQL['local_table'].'.uid='.$mm_table.'.uid_local' : '';
			$mmWhere.= ($SQL['local_table'] AND $SQL['foreign_table']) ? ' AND ' : '';
			$mmWhere.= $SQL['foreign_table'] ? ($foreign_table_as ? $foreign_table_as : $SQL['foreign_table']).'.uid='.$SQL['mm_table'].'.uid_foreign' : '';
			 
			$from_table = ($SQL['local_table'] ? $SQL['local_table'].',' : '').$SQL['mm_table'].($SQL['foreign_table'] ? ','. $SQL['foreign_table'].($foreign_table_as ? ' AS '.$foreign_table_as : '') : '');
			$mmWhere = $mmWhere.' '.$SQL['where'];
			
			$SQL['where']		= $mmWhere;
			$SQL['local_table']	= $from_table;
			
			debug("11mm11mm11mm11mm11mm11mm",1);
			debug($SQL);
			debug("22mm22mm22mm22mm22mm22mm",1);
		}
 				 
 				 

		// Only for debugging...
		if(!$res)
			{
			t3lib_div::debug('----------- SQL Statement ---------------',1);
			t3lib_div::debug(mysql_error(),1);
			t3lib_div::debug($SQL);
			t3lib_div::debug('++++++++++++++++++++++++++++++++++++++++++',1);
			}
			
		return $res;
		}
		
	/**
	 * This function is automatically called by the mm_bccmsbase framework.
	 * Add here just SPECIAL fields wich can not handled by the TS Commands
	 * 
	 * @param	[string]		$fieldname: Name of the field in the table from wich you want to see the content
	 *
	 * @return	[string]	The content of the named fiels - maybe wraped with HTML-Code
	 */	
	function getFieldContent($fieldname)	{
		$result = '';
		
		if(!$this->isFieldValidToShow($fieldname)) return '';
		
		if(($bufferdResult = $this->mmlib_cache->getFromBuffer($fieldname)) != null) {
			return $bufferdResult;	}
		
		$this->minibench->start('getFieldContent: ' . $fieldname);
		
		switch($fieldname) {
			case 'ziplink':
			case 'normallink':
			case 'prevnormallink':
			
			// If the display of these buttons is turned off - return just a blank field
			if($this->conf['show_download_link'] == 0 && $fieldname == 'normallink') return '';
			if($this->conf['show_zip_link'] == 0 && $fieldname == 'ziplink') return '';
			
			$crypt			= new mmlib_crypt(); 
			$filename		= $this->internal['currentRow']['file_name'];
			$damid 			= $this->internal['currentRow']['uid'];
			$src 			= trim($this->_getSecureFilename($filename));
			$target 		= trim($filename);
			$aTarget		= t3lib_div::split_fileref($target);	
			$additionalData	= array('feuser' => (isset($GLOBALS["TSFE"]->fe_user->user['username']) ? $GLOBALS["TSFE"]->fe_user->user['username'] : 'no_fe_user'));
			$confDBField 	= $this->conf['typodbfield.'][$fieldname . '.'];
			$content 		= $this->internal['currentRow'][$fieldname];
			
			if($content == '') $content = $this->getLLabel($fieldname,$confDBField['value']);
			else if(strpos($content,'<img') === false) $content = $this->getLLabel($content,$content);
			
			if($fieldname == 'ziplink') $target = $aTarget['filebody'] . '.zip';
			if($fieldname == 'prevnormallink') {
				$imgcontent = mmlib_extfrontend::getFieldContent($fieldname);
				if($imgcontent != '' ) $content = $imgcontent;
				//t3lib_div::debug($imgcontent,'$imgcontent');
			}
			// this md5 value will be compared with the File which is represented by the DAM-ID later on in php.zip (must be the same)
			$additionalData['filemd5']			= md5_file($this->internal['currentRow']['file_path'] . $filename);
			$additionalData['src']				= $src;
			$additionalData['target']			= $target;
			$additionalData['damid']			= $damid;
			$additionalData['uniqueid'] 		= uniqid(rand());
			#$additionalData['valid_extensions'] = $confDBField['valid_extensions'];
			
			// make a bit of encryption for the username with rot13
			$tconf=array(
				"no_cache" => 0,
				"extTarget" => "_blank",
				//"parameter" => "/typo3conf/ext/mm_bccmsbase/phphelper/zip.php" . 
				
				// tx_mm_bccmsbase_zip ist registered in ext_localconf.php
				"parameter" => "index.php?eID=tx_mm_bccmsbase_zip" . 
					//"?src=" .	$src . "&target=" . $target . "&damid=" . $damid . '&data=' . $crypt->encryptData($additionalData),
					//'&data=' . $crypt->encryptData($additionalData),
					'&id=' . $additionalData['uniqueid'],
					"useCacheHash" => 1);
				
				$GLOBALS["TSFE"]->fe_user->setKey("ses","zipdata_" . $additionalData['uniqueid'],$additionalData);
				
				// t3lib_div::debug($content,1);
				// t3lib_div::debug($tconf,1);
			
				$this->minibench->stop('getFieldContent: ' . $fieldname);
				$result = $this->cObj->typolink($content, $tconf);
				
				break;

			case 'big_preview_image':
				$this->minibench->stop('getFieldContent: ' . $fieldname);
				$result = mmlib_extfrontend::getFieldContent($fieldname,$this->internal['currentRow'][$fieldname]);
				break;
				
			case 'file_size':
				$filesize = $this->internal['currentRow'][$fieldname];
				$this->minibench->stop('getFieldContent: ' . $fieldname);
				if($filesize < 1024) $result = $filesize . ' byte';
				else if($filesize < 1048576) $result = round($filesize / 1024,2) . ' KB';
				else $result = round($filesize / (1024 * 1024),2) . ' MB';
				break;	

			case 'cattitle':
				$result = mmlib_extfrontend::getFieldContent($fieldname,$this->internal['currentRow'][$fieldname]);
				if($result == '') {
					$result = $this->pi_getLL('no_category_specified','');	
				}
				$this->minibench->stop('getFieldContent: ' . $fieldname);
				break;
				
			case 'category':
				$result = $this->getCategories($this->internal['currentRow']['uid']);
				if(is_array($result)) $result = implode(', ', $result);
				$this->minibench->stop('getFieldContent: ' . $fieldname);
				//$result = $this->getMMData('uid','tx_dam_mm_cat','tx_dam_cat');
				break;
				
			case 'description':
				$result = mmlib_extfrontend::getFieldContent($fieldname,$this->internal['currentRow'][$fieldname]);
				//$result = str_replace('</br>','<br/>',$result);
				$result = $this->pi_RTEcssText($result);
				
				$this->minibench->stop('getFieldContent: ' . $fieldname);				
				break;
				
			case 'title':
				$result = trim(mmlib_extfrontend::getFieldContent($fieldname,$this->internal['currentRow'][$fieldname]));
				
				if(isset($this->conf['add_number_sign_to_title']) && 
					 $this->conf['add_number_sign_to_title'] == 1 && 
					 preg_match('#^[0-9]{3,}.*#',$result)) {
					 	$result = '#' . $result;
					 }
					 
				$this->minibench->stop('getFieldContent: ' . $fieldname);					 
				break;

			case 'col_width':
				$result = $this->conf['listView.']['col_width_style'];
				break;
				
			case 'address_name':
			case 'address_zip':
			case 'address_city':
			case 'address_address':
			case 'address_company':
			case 'address_www':
				/*
				$pid = -1;
				if(isset($this->conf['address.']['address_pid'])) $pid = $this->conf['address.']['address_pid'];

				$result = $this->getDataFromForeignTable('tx_mmdamfilelis_address_uid','tt_address','name',true,$pid);
				if(is_array($result)) $result = implode(',', $result);
				break;
				*/
				$result = '';
				if($this->useTTAddress()) {
					$result = $this->getAddressPart($fieldname);
					$result = $this->getAutoFieldContent($fieldname,$result);
				}
				break;

			//case 'addressfilter':
			
			//	$result = $this->getSubTableLinkWidget('',$this->conf['addressfilter.']['10.'],false);
			//	break;
				
			default:
				$result = mmlib_extfrontend::getFieldContent($fieldname,$this->internal['currentRow'][$fieldname]);
				$this->minibench->stop('getFieldContent: ' . $fieldname);
			break;
		}
		
	$this->mmlib_cache->setBuffer($fieldname,$result);
	return $result;
	}

	/**
	 * Returns the a field from the tt_address table
	 * 
	 * @param	[string]	$fieldname - Name of field in the tt_address table
	 * 
	 * return [string]	Content of field in the tt_address table
	 *  
	*/
	function getAddressPart($fieldname) {
		$pid = -1;
		$realfieldname = str_replace('address_','',$fieldname);
		
		if(!$this->useTTAddress()) return '';
		
		if(isset($this->conf['address.']['address_pid'])) $pid = $this->conf['address.']['address_pid'];
		else $pid = $this->extConf['ttaddress_uid'];
		
		if($pid == -1) {
			die("You must either turn off the tt_address-connection or you have to spcify the SYSFolder where the addresses are stored. Don't forget the singlepage confinguration!!");
		}
		/*
		t3lib_div::debug($fieldname,'$fieldname');
		t3lib_div::debug($realfieldname,'$realfieldname');
		t3lib_div::debug($pid,'$pid');
		*/
		
		$result = $this->getDataFromForeignTable('tx_mmdamfilelis_address_uid','tt_address',$realfieldname,true,$pid);
		if(is_array($result)) $result = implode(',', $result);
	
		/*
		t3lib_div::debug($fieldname,'$fieldname');
		t3lib_div::debug($realfieldname,'$realfieldname');
		t3lib_div::debug($result,'$result');
		*/
		
		return $result;
	}
	
	/**counter
	 * Makes a MM-Query an looks for the categories.
	 * 
	 * @param	[int]	$uid - for which uid do we need the categories
	 * 
	 * @return	[arry]	Array with the categories - or an empt arra
	 */
	function getCategories($uid) {
		//select * from tx_dam_mm_cat,tx_dam_cat where tx_dam_mm_cat.uid_local=110 and tx_dam_mm_cat.uid_foreign=tx_dam_cat.uid;
		$result					= Array();
		
		$SQL['select']			= 'tx_dam_cat.title';
		$SQL['local_table']		= 'tx_dam';
		$SQL['mm_table']		= 'tx_dam_mm_cat';
		$SQL['foreign_table']	= 'tx_dam_cat';
		$SQL['where']			= 'AND tx_dam_mm_cat.uid_local=' . $uid;
		$SQL['group_by']		= ''; 
		$SQL['order_by']		= '';
		$SQL['limit']			= '';
	
		$showLastQuery = false;
		if($showLastQuery) {
			$GLOBALS['TYPO3_DB']->debugOutput = true;
			$GLOBALS['TYPO3_DB']->store_lastBuiltQuery = true;
		}
		
	 	$res = $GLOBALS['TYPO3_DB']->exec_SELECT_mm_query(
			$SQL['select'],
			$SQL['local_table'],
			$SQL['mm_table'],
			$SQL['foreign_table'], 
			$SQL['where'],
			$SQL['group_by'],		//	groupBy, 
			$SQL['order_by'],		// 	orderBy,
			$SQL['limit'] 			//	limit
			);
	
		if($showLastQuery) t3lib_div::debug($GLOBALS['TYPO3_DB']->debug_lastBuiltQuery,"lastBuiltQuery=");			
		
		while(($record = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))) {
			$result[] = $record['title'];
		}

	sort($result);
	return $result;
	}
	
	/**
	 * "virtual" function - can be overwritten
	 * 
	 * @param	[array]	$record - the record which should be checked
	 * 
	 * @return	[bool]		true if the record is OK, otherwise false
	 * 	 */
	function isThisRecordValid($record) {
		return (file_exists(PATH_site . $record['file_path'] . $record['file_name']) || 
			$this->conf['hideNotExistingFile'] == false);
	}
	
	/**
	 * Overwrites the BaseClass-function and defines all the possible
	 * plugins from where we can get the BE Translations
	 * 
	 * For the Parameter-Description have a look in the baseclass documentation
	 *
	 */
	function getBL($index,$subindex = -1,$foreignTableName = null,$fromPlugin = null)
		{
		$arrAllPossiblePlugins 	= array( $fromPlugin,'mm_dam_filelist', 'dam');
		$arrForeignTableNames	= array('','tx_dam_item');
		
		foreach($arrAllPossiblePlugins as $plugin) {
			foreach($arrForeignTableNames as $foreignTableName) {
				$result	= mmlib_extfrontend::getBL($index,$subindex,$foreignTableName,$plugin);
				if($result != '') return $result;
			}
		}

		return '(no translation: ' . $index . ')';
	}
	
	/**
	 * You can define if the field should be displayed
	 * 
	 * @param	[String] $fieldname: The name of the DB-Field
	 * @return	[bool]	True if the field should be displayed, otherwise false
	 * 
	*/
	function isFieldValidToShow($fieldname) {
		// If the display of these buttons is turned off - return just a blank field
		switch($fieldname) {
		case 'normallink':
			return ($this->conf['show_download_link'] == 1);
			break;
			
		case 'ziplink':
			return ($this->conf['show_zip_link'] == 1);
			break;
			
		case 'details':
		case 'more':
		return ($this->conf['show_details_link'] == 1);
			break;
			
		default:
			return true;
		}
	
		
	return true;
	}
	
	/**
	 * Returns the image with ajax-technologie.
	 * 
	 * @param	[string] $tagID: The id which should be replaced
	 * @param	[string] $filename: The filename which is the base for the previe
	 *  
	 * @return	[object] xajaxResponse Object
	 * 
	*/
	function ajaxGetPreview1($tagID,$filename) {
		$response = new xajaxResponse();
		
		$aFileName = t3lib_div::split_fileref($filename);
		
		$fieldname = 'preview';
		
		$confDBField = $this->conf['typodbfield.'][$fieldname . '.'];		
		$confDBField['path'] = $aFileName['path'];
		$basefilename = $aFileName['filebody'] . '.' .  $aFileName['fileext'];
		
		$content = $this->_getImageContent('ajaxpreview',$basefilename,$confDBField);

		$response->call("changePreviewImage", $tagID,$content);
		return $response;
	
	}
	
	
	/**
	 * Generates a Combo or a Listbox out of T3-Settings
	 * 
	 * @param	[string] $content: From the t3-Framework
	 * @param	[string] $localconf: Settings from the T3-Framework
	 * @param	[string] $reinit: Is initialisation nessecary - true if called by the T3-framework
	 *   
	 * @return	[string] HTML-Code
	 * 
	*/
	
	function getSubTableLinkWidget($content,$localconf,$reinit = true) {
		$localconf = $localconf['localconf.'];

		//$uid = (isset($localconf['startuid']) ? $localconf['startuid'] : 0);
		//t3lib_div::debug($localconf,1);
		//t3lib_div::debug($conf,1);
		
		if($reinit) $this->initPlugin($localconf);
	
		/*
		$tablename			= $localconf['tablename'];
		$fieldname			= $localconf['fieldname'];
		$filterfield		= $localconf['filterfield'];
		$elementtype		= $localconf['elementtype'];
		$firstentry			= $localconf['firstcomboentry'];
		$label				= $localconf['label'];
		$linkfield			= $localconf['linkfield'];
		$linktarget			= $localconf['linktarget'];
		$targetpageid		= $localconf['targetpageid'];
		*/
		
		$entry2remove		= array();
		$tempEntry2remove 	= explode(',',$localconf['entry2remove']);
		foreach($tempEntry2remove as $value) {
			$entry2remove[] = trim($value);
		}
		$localconf['entry2remove'] = $entry2remove;

		
		if($this->useTTAddress() && isset($localconf['tablepid']) && $localconf['tablepid'] == -1) {
			if(isset($this->conf['address.']['address_pid'])) {
				$localconf['tablepid'] = $this->conf['address.']['address_pid'];
			} else $localconf['tablepid'] = $this->extConf['ttaddress_uid'];
		}
		
		//t3lib_div::debug($localconf,'$localconf');
		
		$content .= $this->createSubTableLinkWidgetFromArray($localconf);

		//t3lib_div::debug($content,'$content');
		
		return $content;
		
		//return $this->createSubTableLinkWidget('tx_mmhutinfo_hutguide','name','hutguide_uid','combo');
	}
	
	function useTTAddress() {
		//$extConf 	= unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['mm_dam_filelist']);
		$use_ttaddress_connection 	= $this->extConf['use_ttaddress_connection'];

		//t3lib_div::debug($extConf,'$extConf=');
		return (t3lib_extMgm::isLoaded('tt_address') && $use_ttaddress_connection);
	}
		
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mm_dam_filelist/pi1/class.tx_mmdamfilelist_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mm_dam_filelist/pi1/class.tx_mmdamfilelist_pi1.php']);
}

?>