<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009 Yolf (Laurent Foulloy) <yolf.typo3@orange.fr>
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
*  A copy is found in the textfile GPL.txt and important notices to the license
*  from the author is found in LICENSE.txt distributed with these scripts.
*
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

	/**
	 * Renders form for addition of SAV extension:
	 */
 
require_once(t3lib_extMgm::extPath('kickstarter').'class.tx_kickstarter_sectionbase.php');

class tx_kickstarter_section_savext extends tx_kickstarter_sectionbase {
  var $sectionID = 'savext';

	function render_wizard() {
		$lines=array();

		$action = explode(':',$this->wizard->modData['wizAction']);
		if ($action[0]=='edit')	{
			$this->regNewEntry($this->sectionID,$action[1]);
			$lines = $this->catHeaderLines($lines,$this->sectionID,$this->wizard->options[$this->sectionID],'&nbsp;',$action[1]);
			$piConf = $this->wizard->wizArray[$this->sectionID][$action[1]];
			$ffPrefix='['.$this->sectionID.']['.$action[1].']';

			// Enter generateForm flag
      $subContent='<strong>Generate Form:</strong><BR>'.
        $this->renderCheckBox($ffPrefix.'[generateForm]',$piConf['generateForm']);
    	$lines[]='<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).'</td></tr>';
    	
    	// Set the version
      $this->wizard->wizArray['emconf'][1]['version'] = $this->wizard->wizArray[$this->sectionID][1]['version'];
    	// Select if the icono should be displayed below the plugin selector
      $subContent='<strong>Display the icon below the plugin selector:</strong><BR>'.
        $this->renderCheckBox($ffPrefix.'[displayIconBelowPluginSelector]',$piConf['displayIconBelowPluginSelector']);
    	$lines[]='<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).'</td></tr>';
      
  		// Enter additionalCode
  		$nbLines = substr_count($piConf['additionalCode'], chr(13).chr(10)) + 2;      
    	$subContent="<strong>Additional Code:</strong><BR>".
    		$this->renderTextareaBox($ffPrefix.'[additionalCode]',$piConf['additionalCode'], 500, max(10, $nbLines));
    	$lines[]='<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).'</td></tr>';
    	
  		// Enter additionalFlexFormCode
  		$nbLines = substr_count($piConf['additionalFlexFormCode'], chr(13).chr(10)) + 2;      
    	$subContent="<strong>Additional FlexForm Code:</strong><BR>".
    		$this->renderTextareaBox($ffPrefix.'[additionalFlexFormCode]',$piConf['additionalFlexFormCode'], 500, max(10, $nbLines));
    	$lines[]='<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).'</td></tr>';
		}
		
		/* HOOK: Place a hook here, so additional output can be integrated */
		if(is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kickstarter']['add_cat_savext'])) {
		  foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kickstarter']['add_cat_savext'] as $_funcRef) {
		    $lines = t3lib_div::callUserFunction($_funcRef, $lines, $this);
		  }
		}

		$content = '<table border=0 cellpadding=2 cellspacing=2>'.$this->helpIcon('formGen').implode('',$lines).'</table>';
		$content .= '<input type="hidden" name="'.$this->piFieldName("wizArray_upd").$ffPrefix.'[title]'.'" value="'.$piConf['title'].'">';

		return $content;
	}

  function helpIcon($field){	
    return '<a href="#" style="float:left;" onclick="vHWin=window.open(\''.$this->wizard->siteBackPath.TYPO3_mainDir.'view_help.php?tfID=sav_library.'.$field.'\',\'viewFieldHelp\',\'height=400,width=600,status=0,menubar=0,scrollbars=1\');vHWin.focus();return false;"><img src="'.$this->wizard->siteBackPath.TYPO3_mainDir.'gfx/helpbubble.gif" width="16" height="16" hspace="2" border="0" class="typo3-csh-icon" alt="'.$field.'" /></a>';
  }
  
	function renderTextareaBox($prefix,$value,$width=600,$rows=10)	{
		$onCP = $this->getOnChangeParts($prefix);
		return $this->wopText($prefix).$onCP[0].'<textarea name="'.$this->piFieldName('wizArray_upd').$prefix.'" style="width:'.$width.'px;" rows="'.$rows.'" wrap="OFF" onChange="'.$onCP[1].'" title="'.htmlspecialchars("WOP:".$prefix).'"'.$this->wop($prefix).'>'.t3lib_div::formatForTextarea($value).'</textarea>';
	}			
	
}

?>
