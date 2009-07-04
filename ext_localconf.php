<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

// Save the extension Key
$save_EXTKEY = $_EXTKEY;

// Make the extension version number available to the extension scripts
require_once(t3lib_extMgm::extPath($_EXTKEY) . 'ext_emconf.php');
$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['version'] = $EM_CONF[$_EXTKEY]['version'];

if (t3lib_extMgm::isLoaded('kickstarter')) {
  // Get the kickstarter version
  $_EXTKEY = 'kickstarter';
  require_once(t3lib_extMgm::extPath('kickstarter') . 'ext_emconf.php');
  $kickstarterVersion = str_replace('.', '_', $EM_CONF[$_EXTKEY]['version']);

  // Recover the _EXTKEY
  $_EXTKEY = $save_EXTKEY;
  if (file_exists(t3lib_extMgm::extPath($_EXTKEY) . 'kickstarter/' . $kickstarterVersion)) {
    // Extend the kickstarter classes
    $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/kickstarter/class.tx_kickstarter_wizard.php'] =
      t3lib_extMgm::extPath($_EXTKEY) . 'kickstarter/' . $kickstarterVersion . '/class.ux_tx_kickstarter_wizard.php';
    $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/kickstarter/sections/class.tx_kickstarter_section_tables.php'] =
      t3lib_extMgm::extPath($_EXTKEY) . 'kickstarter/' . $kickstarterVersion . '/sections/class.ux_tx_kickstarter_section_tables.php';
    $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/kickstarter/sections/class.tx_kickstarter_section_fields.php'] =
      t3lib_extMgm::extPath($_EXTKEY) . 'kickstarter/' . $kickstarterVersion . '/sections/class.ux_tx_kickstarter_section_fields.php';
    $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/kickstarter/sections/class.tx_kickstarter_section_pi.php'] =
      t3lib_extMgm::extPath($_EXTKEY) . 'kickstarter/' . $kickstarterVersion . '/sections/class.ux_tx_kickstarter_section_pi.php';
  } else {
    $TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['errorKickstarterVersion'] = $EM_CONF[$_EXTKEY]['constraints']['depends']['kickstarter'];
  }

  // Extend the tslib_cObj class if version is lower than 4.3
  if (t3lib_div::int_from_ver(TYPO3_version) < 4003000) {
    $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['tslib/class.tslib_content.php'] =
      t3lib_extMgm::extPath($_EXTKEY) . 'tslib/class.ux_tslib_content.php';
  }

  // Performance debug
//  $TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['performanceDebug'] = 1;

}
                               
?>
