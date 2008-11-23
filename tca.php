<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$TCA["tx_savlibrary_export_configuration"] = array (
	"ctrl" => $TCA["tx_savlibrary_export_configuration"]["ctrl"],
	"interface" => array (
		"showRecordFieldList" => "hidden,fe_group,name,cid,configuration"
	),
	"feInterface" => $TCA["tx_savlibrary_export_configuration"]["feInterface"],
	"columns" => array (
		'hidden' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		'fe_group' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.fe_group',
			'config'  => array (
				'type'  => 'select',
				'items' => array (
					array('', 0),
				),
				'foreign_table' => 'fe_groups'
			)
		),
		"name" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:sav_library/locallang_db.xml:tx_savlibrary_export_configuration.name",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"cid" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:sav_library/locallang_db.xml:tx_savlibrary_export_configuration.cid",		
			"config" => Array (
				"type"     => "input",
				"size"     => "4",
				"max"      => "4",
				"eval"     => "int",
				"default" => 0
			)
		),
		"configuration" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:sav_library/locallang_db.xml:tx_savlibrary_export_configuration.configuration",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",	
				"rows" => "5",
			)
		),
	),
	"types" => array (
		"0" => array("showitem" => "hidden;;1;;1-1-1, name, cid, configuration")
	),
	"palettes" => array (
		"1" => array("showitem" => "fe_group")
	)
);
?>
