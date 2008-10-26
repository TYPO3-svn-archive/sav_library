<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

  $savExtKickstarter = array (
    'savext' => array (
      'classname' => 'tx_kickstarter_section_savext',
      'filepath' => 'EXT:sav_library/kickstarter/class.tx_kickstarter_section_savext.php',
      'title' => 'SAV Extension generator',
      'description' => 'Create extension using SAV Library',
      'single' => 1,
      'styles' => array(
                      'field' => 'generateForm',
                      'defaultValue' => 'color:black',
                      'errorValue' => 'color:red;font-weight:bold;',
                      'value' => array (
                            '1' => 'color:red;font-weight:bold;',
                            ),
                      ),                        
      'singleTitles' => array(
                      'field' => 'generateForm',
                      'defaultValue' => '[Click to Edit]',
                      'value' => array (
                            '0' => array ('label' => 'Off',
                                ),
                            '1' => array ('label' => 'On',
                                    'addLabel' => ' - Ext Version ',
                                    'addField' => 'version',
                                ),
                            ),
                      ),
      ),
    'formviews' => array (
      'classname' => 'tx_kickstarter_section_formviews',
      'filepath' => 'EXT:sav_library/kickstarter/class.tx_kickstarter_section_formviews.php',
      'title' => 'Form views',
      'description' => 'Create form views',
      'activationField' => array('section' => 'savext', 'field' => 'generateForm'),
      'styles' => array(
                      'field' => 'type',
                      'defaultValue' => 'color:black',
                      'value' => array (
                            'showAll' => 'color:red;',
                            'showSingle' => 'color:green;',
                            'input' => 'color:blue;',
                            'alt' => 'color:orange;'
                            ),
                      ),                        
    ),
    'formqueries' => array (
      'classname' => 'tx_kickstarter_section_formqueries',
      'filepath' => 'EXT:sav_library/kickstarter/class.tx_kickstarter_section_formqueries.php',
      'title' => 'Form queries',
      'description' => 'Create form queries',
      'activationField' => array('section' => 'savext', 'field' => 'generateForm'),     
    ),
    'forms' => array (
      'classname' => 'tx_kickstarter_section_forms',
      'filepath' => 'EXT:sav_library/kickstarter/class.tx_kickstarter_section_forms.php',
      'title' => 'Forms',
      'description' => 'Create forms',
      'activationField' => array('section' => 'savext', 'field' => 'generateForm'),
      'styles' => array(
                      'field' => 'type',
                      'defaultValue' => 'color:black',
                      'value' => array (
                            'showAll' => 'color:red;',
                            'showSingle' => 'color:green;',
                            'input' => 'color:blue;',
                            'alt' => 'color:orange;'
                            ),
                      ),                        
    ),
  );
  
$TYPO3_CONF_VARS['EXTCONF']['kickstarter']['sections'] = (array) $TYPO3_CONF_VARS['EXTCONF']['kickstarter']['sections'] + $savExtKickstarter;

$TYPO3_CONF_VARS['EXTCONF']['kickstarter']['sections']['tables']['styles'] = $TYPO3_CONF_VARS['EXTCONF']['kickstarter']['sections']['formviews']['styles'];   
$TYPO3_CONF_VARS['EXTCONF']['kickstarter']['sections']['fields']['styles'] = $TYPO3_CONF_VARS['EXTCONF']['kickstarter']['sections']['formviews']['styles'];  
 
// Add user function for help icons in flexforms for extension depending on SAV Library
if (!function_exists('user_helpIcon_savlibrary')) {
  function user_helpIcon_savlibrary($PA, $fobj){	
    if (t3lib_div::int_from_ver(TYPO3_version) < 4002000) { 
      // Check if it's a filter
      if ($PA['fieldConf']['config']['filter']) {
        $ext = str_replace('_pi1','',$PA['row']['list_type']);
        $helpItem = 'help';      
      } else {
	      preg_match('/\[([^\]]+)\]\[[^\]]+\]$/', $PA['itemFormElName'], $matches);
        $ext = 'sav_library';
        $helpItem = ($matches[1] ? $matches[1] : 'help');      
      }
      return '<a href="#" style="padding:3px;" onclick="vHWin=window.open(\''.'view_help.php?tfID='.$ext.'.'.$helpItem.'\',\'viewFieldHelp\',\'height=400,width=600,status=0,menubar=0,scrollbars=1\');vHWin.focus();return false;"><img src="'.'sysext/t3skin/icons/gfx/helpbubble.gif" width="16" height="16" hspace="2" border="0" class="typo3-csh-icon" alt="'.'help'.'" /></a>';      
    } else {
      return '&nbsp;';    
    }
  }
}

// Adding context sensitive help (CSH)
t3lib_extMgm::addLLrefForTCAdescr('sav_library','EXT:sav_library/res/locallang_csh_kickstarter.xml');
if (t3lib_div::int_from_ver(TYPO3_version) < 4002000) { 
  t3lib_extMgm::addLLrefForTCAdescr('sav_library','EXT:sav_library/res/locallang_csh_flexform.xml');
}

t3lib_extMgm::allowTableOnStandardPages('tx_savlibrary_export_configuration');

$TCA["tx_savlibrary_export_configuration"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:sav_library/locallang_db.xml:tx_savlibrary_export_configuration',		
		'label'     => 'uid',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => "ORDER BY crdate",	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'disabled' => 'hidden',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_savlibrary_export_configuration.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "hidden, name, cid, configuration",
	)
);
?>
