<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

  ## Extending TypoScript from static template uid=43 to set up userdefined tag:
t3lib_extMgm::addTypoScript($_EXTKEY,'editorcfg','
	tt_content.CSS_editor.ch.tx_mmdamfilelist_pi1 = < plugin.tx_mmdamfilelist_pi1.CSS_editor
',43);

// Lolevel-init can be done with eID_include
$TYPO3_CONF_VARS['FE']['eID_include']['tx_mm_bccmsbase_zip'] = 'EXT:mm_bccmsbase/phphelper/zip.php';

t3lib_extMgm::addPItoST43($_EXTKEY,'pi1/class.tx_mmdamfilelist_pi1.php','_pi1','list_type',1);

$extConf 			= unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['mm_dam_filelist']);
$realurlintegration	= $extConf['realurlintegration'];

if($realurlintegration) {
	$TYPO3_CONF_VARS['EXTCONF'] ['realurl'] ['_DEFAULT'] ['postVarSets'] ['_DEFAULT'] = array(
				// MM DAM FileList - begin
				'viewmode' => array(
					'0' => array( 
						'GETvar' => 'tx_mmdamfilelist_pi1[viewmode]' ,
						'userFunc' => 'EXT:mm_dam_filelist/pi1/class.tx_mmdamfilelist_realurl.php:&tx_mmdamfilelist_realurl->category',
						),
					), // viewmode
	
				'mode' => array(			
					'0' => array(
						'GETvar' => 'tx_mmdamfilelist_pi1[mode]' ,
						'userFunc' => 'EXT:mm_dam_filelist/pi1/class.tx_mmdamfilelist_realurl.php:&tx_mmdamfilelist_realurl->category',
						),
					), // mode
	
				'damdetails' => array(			
					'0' => array(
						'GETvar' => 'tx_mmdamfilelist_pi1[showuid]' ,
						'userFunc' => 'EXT:mm_dam_filelist/pi1/class.tx_mmdamfilelist_realurl.php:&tx_mmdamfilelist_realurl->details',
						),
					), // showuid 
	
				'damdetails' => array(			
					'0' => array(
						'GETvar' => 'tx_mmdamfilelist_pi1[showUid]' ,
						'userFunc' => 'EXT:mm_dam_filelist/pi1/class.tx_mmdamfilelist_realurl.php:&tx_mmdamfilelist_realurl->details',
						),
					), // showUid 
	
				'pointer' => array(			
					'0' => array(
						'GETvar' => 'tx_mmdamfilelist_pi1[pointer]' ,
						),
					), // pointer 
	
				'oldmode' => array(			
					'0' => array(
						'GETvar' => 'tx_mmdamfilelist_pi1[oldmode]' ,
						),
					), // oldmode 
	
				'sword' => array(			
					'0' => array(
						'GETvar' => 'tx_mmdamfilelist_pi1[sword]' ,
						),
					), // sword

				'filterfield' => array(			
					'0' => array(
						'GETvar' => 'tx_mmdamfilelist_pi1[filterfield]' ,
						),
					), // filterfield
					
				'filterid' => array(			
					'0' => array(
						'GETvar' => 'tx_mmdamfilelist_pi1[filterid]' ,
						),
					), // filterid
					
				// MM DAM FileList - end	
	      ); 
	}
?>