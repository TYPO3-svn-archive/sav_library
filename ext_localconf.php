<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

// Extending the kickstarter classes
$TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/kickstarter/class.tx_kickstarter_wizard.php'] = t3lib_extMgm::extPath($_EXTKEY).'kickstarter/class.ux_tx_kickstarter_wizard.php';
$TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/kickstarter/sections/class.tx_kickstarter_section_tables.php'] = t3lib_extMgm::extPath($_EXTKEY).'kickstarter/class.ux_tx_kickstarter_section_tables.php';
$TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/kickstarter/sections/class.tx_kickstarter_section_fields.php'] = t3lib_extMgm::extPath($_EXTKEY).'kickstarter/class.ux_tx_kickstarter_section_fields.php';
$TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/kickstarter/sections/class.tx_kickstarter_section_pi.php'] = t3lib_extMgm::extPath($_EXTKEY).'kickstarter/class.ux_tx_kickstarter_section_pi.php';

// Make the extension version number available to the extension scripts
require_once(t3lib_extMgm::extPath($_EXTKEY) . 'ext_emconf.php');
$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['version'] = $EM_CONF[$_EXTKEY]['version'];
                               
?>
