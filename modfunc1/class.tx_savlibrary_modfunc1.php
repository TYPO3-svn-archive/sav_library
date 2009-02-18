<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2008 Yolf (yolf.typo3@orange.fr)
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
 * Module extension (addition to function menu)
 *
 * @author	Yolf <yolf.typo3@orange.fr>
 */



require_once(PATH_t3lib.'class.t3lib_extobjbase.php');
require_once(t3lib_extMgm::extPath('kickstarter').'class.tx_kickstarter_wizard.php');

class tx_savlibrary_modfunc1 extends t3lib_extobjbase {

	/**
	 * Main method of modfunc1
	 */
	function main()	{

    // Check if the version of the kickstarter is the ood one
    if ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['sav_library']['errorKickstarterVersion']) {
		  return $this->pObj->doc->section('',
        $GLOBALS['LANG']->getLL('fatal.incorrectKickstarterVersion') .
          '<span style="color:red;font-weight:bold;">' .
          $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['sav_library']['errorKickstarterVersion'] .
          '</span>',
        0,
        1
      );
    }

    // Check if extensions have to be updated
    $updateArray = t3lib_div::_POST('update');
    $xmlArray = t3lib_div::_POST('xml');

    if (is_array($updateArray)) {
		  $kickstarter = t3lib_div::makeInstance('tx_kickstarter_wizard');
		  $kickstarter->pObj = &$this->pObj;
      // Get the installed extensions
		  $installedExtensions = $this->pObj->getInstalledExtensions();

		  foreach($updateArray as $extKey => $value) {
        if (file_exists(t3lib_extMgm::extPath($extKey) . 'doc/wizard_form.dat')) {

          // Update xml configuration flag
          $wizardFormFile = t3lib_div::getURL(t3lib_extMgm::extPath($extKey) . 'doc/wizard_form.dat');
          $wizardForm = unserialize($wizardFormFile);

          $wizardForm['savext'][1]['xmlConfiguration'] = ($xmlArray[$extKey] ? 1 : 0);
          t3lib_div::writeFile(t3lib_extMgm::extPath($extKey) . 'doc/wizard_form.dat', serialize($wizardForm));

          // reset the files
          unset($kickstarter->ext_localconf);
          unset($kickstarter->ext_tables);
          unset($kickstarter->ext_tca);
          unset($kickstarter->ext_tables_sql);
          unset($kickstarter->ext_locallang);
          unset($kickstarter->ext_locallang_db);

          // Update the extension
          $wizardFormFile = t3lib_div::getURL(t3lib_extMgm::extPath($extKey) . 'doc/wizard_form.dat');
          $kickstarter->modData['wizArray_ser'] = base64_encode($wizardFormFile);
          $kickstarter->modData['WRITE'] = 1;
		      $kickstarter->EMmode = 1;
          $kickstarter->mgm_wizard();

          // Clear the content
          $this->pObj->content = '';

          // Get the DB updates
          $DBupdates .= $this->pObj->checkDBupdates($extKey, $installedExtensions[0][$extKey]);
        }
      }
    }

    // Get the extensions with depends on the SAV Library Extension Generator
    $paramSET = t3lib_div::_GET('SET');

    $content[] = '<form name="updateForm" action="index.php?id=' . t3lib_div::_GET('id') . '&SET[function]=' . $paramSET['function'] . '" method="post" enctype="multipart/form-data">';
    $content[] = '  <input name="updateButton" type="submit" class="submit" value="Update the extensions" />';
    $content[] = '  <ul class="update">';
    $content[] = '    <li class="extNameTitle">Extension name</li>';
    $content[] = '    <li class="xmlTitle">XML Generation</li>';
    $content[] = '    <li class="updateTitle">Update</li>';
    $content[] = '    <li class="version">SAV Library version</li>';

    foreach($GLOBALS['TYPO3_LOADED_EXT'] as $extKey => $extInfo) {
      if (file_exists(t3lib_extMgm::extPath($extKey) . 'doc/wizard_form.dat')) {
        $wizardFormFile = t3lib_div::getURL(t3lib_extMgm::extPath($extKey) . 'doc/wizard_form.dat');
        $wizardForm = unserialize($wizardFormFile);

        if ($wizardForm['savext'][1]['generateForm']) {
          $extArray[] = array(
            'extKey' => $extKey,
            'version' => $wizardForm['savext'][1]['savlibraryVersion'],
          );
        }
      }
    }

    // Sort the array and process it
    sort($extArray);

    foreach($extArray as $ext) {
      $extKey = $ext['extKey'];
      $content[] = '    <li class="extName">' .
				'<a href="' . htmlspecialchars('index.php?CMD[showExt]='.$extKey.'&SET[singleDetails]=tx_kickstarter_modfunc2'). '">' .
        $ext['extKey'] . '</a></li>';
      $content[] = '    <li class="xml">' . '<input type="checkbox" name="xml[' . $extKey .']" ' . ($xmlArray[$extKey] || !is_array($updateArray) ? 'checked="checked"' : '') . ' /></li>';
      $content[] = '    <li class="update">' . '<input type="checkbox" name="update[' . $extKey . ']" ' . ($updateArray[$extKey] == 'on' ? 'checked="checked"' : '') . ' /></li>';
      $content[] = '    <li class="version' .
        ($ext['version'] == $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['sav_library']['version'] ? 'Ok' : 'NotOk') .
        '">' . ($ext['version'] ? $ext['version'] : $GLOBALS['LANG']->getLL('unknown')) . '</li>';
    }

    $content[] = '  </ul>';
    $content[] = '<div class="extManager">';
    $content[] = $DBupdates;
    $content[] = '</div>';
    $content[] = '</form>';
    
    // Add the css file
    $this->pObj->doc->styleSheetFile2 =
              t3lib_extMgm::extRelPath('sav_library'). 'modfunc1/savlibrary_modfunc1.css';
              
		return $this->pObj->doc->section('Extension Manager', implode(chr(10), $content),0,1);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/sav_libray/modfunc1/class.tx_savlibrary_modfunc1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/sav_libray/modfunc1/class.tx_savlibrary_modfunc1.php']);
}

?>
