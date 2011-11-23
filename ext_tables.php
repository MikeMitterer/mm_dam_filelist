<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

t3lib_div::loadTCA("tt_content");

// Hide the formfields for layout and select_key
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1']='layout,select_key';

// add FlexForm field to tt_content
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY."_pi1"]='pi_flexform';

// Makes the field tx_mmdamfilelist_mm_dam_category visible
//$TCA["tt_content"]["types"]["list"]["subtypes_addlist"][$_EXTKEY."_pi1"]="tx_mmdamfilelist_mm_dam_category;;;;1-1-1";

// Add an entry to the selectorbox in the BE-Form
t3lib_extMgm::addPlugin(Array('LLL:EXT:mm_dam_filelist/locallang_db.php:tt_content.list_type_pi1', $_EXTKEY.'_pi1'),'list_type');

// Add an entry in the static template list found in sys_templates "static template files"
t3lib_extMgm::addStaticFile($_EXTKEY,"pi1/static/","MM DAM-FileList");

// Add "default" static CSS File
t3lib_extMgm::addStaticFile($_EXTKEY,'pi1/static/layout/default/','MM DAM Layout I (CSS-styles)');

// Add "rohloff" static CSS File
t3lib_extMgm::addStaticFile($_EXTKEY,'pi1/static/layout/rohloff/','MM DAM Layout II (CSS-styles)');

// Add "gallery" static CSS File
t3lib_extMgm::addStaticFile($_EXTKEY,'pi1/static/layout/gallery/','MM DAM Layout III Gallery (CSS-styles)');

// Add "address" static CSS File
t3lib_extMgm::addStaticFile($_EXTKEY,'pi1/static/layout/address/','MM DAM Layout IV Address (CSS-styles)');

// Add "address" static CSS File
t3lib_extMgm::addStaticFile($_EXTKEY,'pi1/static/layout/video/','MM DAM Layout V Video (CSS-styles)');

$tempColumns = Array (
	"tx_mmdamfilelist_mm_dam_category" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:mm_dam_filelist/locallang_db.php:tt_content.tx_mmdamfilelist_mm_dam_category",		
		"config" => Array (
			"type" => "select",	
			"foreign_table" => "tx_dam_cat",	
			"foreign_table_where" => "ORDER BY tx_dam_cat.uid",	
			"size" => 5,	
			"minitems" => 0,
			"maxitems" => 99,	
			"wizards" => Array(
				"_PADDING" => 2,
				"_VERTICAL" => 1,
				"add" => Array(
					"type" => "script",
					"title" => "Create new record",
					"icon" => "add.gif",
					"params" => Array(
						"table"=>"tx_dam_cat",
						"pid" => "###CURRENT_PID###",
						"setValue" => "prepend"
					),
					"script" => "wizard_add.php",
				),
				"list" => Array(
					"type" => "script",
					"title" => "List",
					"icon" => "list.gif",
					"params" => Array(
						"table"=>"tx_dam_cat",
						"pid" => "###CURRENT_PID###",
					),
					"script" => "wizard_list.php",
				),
			),
		)
	),
);

t3lib_extMgm::addPiFlexFormValue($_EXTKEY."_pi1", 'FILE:EXT:mm_dam_filelist/flexform_ds_pi1.xml');

//t3lib_div::loadTCA("tt_content");

// Adds the field from tx_mmdamfilelist_mm_dam_category to the BE-Form
t3lib_extMgm::addTCAcolumns("tt_content",$tempColumns,1);

#-----------------------------------------------------------------------
# Adds a new field to the dam table
#
$extConf 	= unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['mm_dam_filelist']);
$ttaddress_uid 				= $extConf['ttaddress_uid'];
$use_ttaddress_connection 	= $extConf['use_ttaddress_connection'];

//t3lib_div::debug($extConf,'$extConf=');

t3lib_div::loadTCA("tx_dam");
$tabEntry = '--div--;LLL:EXT:mm_dam_filelist/locallang_db.xml:tx_dam.tab.entry,';

if(t3lib_extMgm::isLoaded('tt_address') && $use_ttaddress_connection) {
	$WHERE		= '';
	if($ttaddress_uid != -1) {
		$WHERE = "AND tt_address.pid='" . addslashes($ttaddress_uid) . "'";
	}
	

	//t3lib_div::debug($WHERE,'$WHERE=');
	
	// Connect tx_dam to tt_address
	$tempColumns = Array( 
		"tx_mmdamfilelis_address_uid" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:mm_dam_filelist/locallang_db.xml:tx_mmdamfilelist_address_name",		
		"config" => Array (
			"type" => "select",	
			"foreign_table" => "tt_address",	
			"foreign_table_where" => "$WHERE ORDER BY tt_address.name",	
			"size" => 10,	
			"minitems" => 0,
			"maxitems" => 1,
			"default" => '',	
			),
		),
	);
	
	t3lib_extMgm::addTCAcolumns("tx_dam",$tempColumns,1);
	t3lib_extMgm::addToAllTCAtypes("tx_dam",$tabEntry . "tx_mmdamfilelis_address_uid;;;;1-1-1");
	$tabEntry = ''; // Add it just once
}

$tempColumns2 = Array( 
	'tx_mmdamfilelist_altpreview' => array (		
		'exclude' => 0,		
		'label' => 'LLL:EXT:mm_dam_filelist/locallang_db.xml:tx_dam.tx_mmdamfilelist_altpreview',		
		'config' => array (
			'type' => 'group',
			'internal_type' => 'file',
			'allowed' => 'gif,png,jpeg,jpg',	
			'max_size' => $GLOBALS['TYPO3_CONF_VARS']['BE']['maxFileSize'],	
			'uploadfolder' => 'uploads/tx_mmdamfilelist',
			'show_thumbs' => 1,	
			'size' => 1,	
			'minitems' => 0,
			'maxitems' => 1,
		)
	),
	'tx_mmdamfilelist_oldfilehash' => array (		
		'exclude' => 0,		
		'label' => 'LLL:EXT:mm_dam_filelist/locallang_db.xml:tx_dam.tx_mmdamfilelist_oldfilehash',		
		'config' => array (
			'type' => 'input',	
			'size' => '10',
		)
	),
);

t3lib_extMgm::addTCAcolumns("tx_dam",$tempColumns2,1);
t3lib_extMgm::addToAllTCAtypes("tx_dam",$tabEntry . "tx_mmdamfilelist_altpreview,tx_mmdamfilelist_oldfilehash");


// Made by the Kickstarter - not realy helpful in this case
//t3lib_extMgm::addToAllTCAtypes("tt_content","tx_mmdamfilelist_mm_dam_category;;;;1-1-1");

?>