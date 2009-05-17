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
	 * Renders form for addition of forms:
	 */
 
require_once(t3lib_extMgm::extPath('kickstarter').'class.tx_kickstarter_sectionbase.php');

class tx_kickstarter_section_forms extends tx_kickstarter_sectionbase {
  var $sectionID = 'forms';

	function render_wizard() {
		$lines=array();

		$action = explode(':',$this->wizard->modData['wizAction']);
		if ($action[0]=='edit')	{
			$this->regNewEntry($this->sectionID,$action[1]);
			$lines = $this->catHeaderLines($lines,$this->sectionID,$this->wizard->options[$this->sectionID],'&nbsp;',$action[1]);
			$piConf = $this->wizard->wizArray[$this->sectionID][$action[1]];
			$ffPrefix='['.$this->sectionID.']['.$action[1].']';

				// Enter title of the form
      $subContent='<strong>Title:</strong><br />'.
        $this->renderStringBox($ffPrefix.'[title]',$piConf['title']);
    	$lines[]='<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).'</td></tr>';

				// Select the views
			$optShowAll[0] = '';
			$optShowSingle[0] = '';
			$optInput[0] = '';
			$optAlt[0] = '';
			
			if (isset($this->wizard->wizArray['formviews'])) {
        foreach ($this->wizard->wizArray['formviews'] as $key => $view) {
          switch ($view['type']) {
            case 'showAll':
              $optShowAll[$key] = $view['title'];
              break;
            case 'showSingle':
              $optShowSingle[$key] = $view['title'];
              break;
            case 'input':
              $optInput[$key] = $view['title'];
              break;
            case 'alt':
              $optAlt[$key] = $view['title'];
              break;
          }
        }
      }

        // Get the style
      if (isset($this->wizard->sections[$this->sectionID]['styles'])) {
        $style = $this->wizard->sections[$this->sectionID]['styles']['defaultValue'];
      }

      if (isset($this->wizard->sections[$this->sectionID]['styles']['value'])) {
        $style = $this->wizard->sections[$this->sectionID]['styles']['value']['showAll'];
      } 
			$subContent='<strong style="'.$style.'">Show all view</strong><br />'.
			 $this->renderSelectBox($ffPrefix.'[showAllView]',$piConf['showAllView'],$optShowAll);
			$lines[]='<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).'</td></tr>';
			
      if (isset($this->wizard->sections[$this->sectionID]['styles']['value'])) {
        $style = $this->wizard->sections[$this->sectionID]['styles']['value']['showSingle'];
      } 
			$subContent='<strong style="'.$style.'">Show single view</strong><br />'.
			 $this->renderSelectBox($ffPrefix.'[showSingleView]',$piConf['showSingleView'],$optShowSingle);
			$lines[]='<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).'</td></tr>';
			
      if (isset($this->wizard->sections[$this->sectionID]['styles']['value'])) {
        $style = $this->wizard->sections[$this->sectionID]['styles']['value']['input'];
      } 
			$subContent='<strong style="'.$style.'">Input view</strong><br />'.
			 $this->renderSelectBox($ffPrefix.'[inputView]',$piConf['inputView'],$optInput);
			$lines[]='<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).'</td></tr>';

      if (isset($this->wizard->sections[$this->sectionID]['styles']['value'])) {
        $style = $this->wizard->sections[$this->sectionID]['styles']['value']['alt'];
      } 
			$subContent='<strong style="'.$style.'">Alt view</strong><br />'.
			 $this->renderSelectBox($ffPrefix.'[altView]',$piConf['altView'],$optAlt);
			$lines[]='<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).'</td></tr>';

				// Select the query
			$optQuery[0] = '';
			if (isset($this->wizard->wizArray['formqueries'])) {
        foreach ($this->wizard->wizArray['formqueries'] as $key => $query) {
          $optQuery[$key] = $query['title'];
        }
      }
			$subContent='<strong style="color:back">Query</strong><br />'.
			 $this->renderSelectBox($ffPrefix.'[query]',$piConf['query'],$optQuery);
			$lines[]='<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).'</td></tr>';
			
        // Adds USER checkbox
      $subContent= '<strong style="color:back">Allow caching</strong><br />'.
        $this->renderCheckBox($ffPrefix.'[userPlugin]',$piConf['userPlugin']);
    	$lines[]='<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).'</td></tr>';


		}
		
		/* HOOK: Place a hook here, so additional output can be integrated */
		if(is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kickstarter']['add_cat_forms'])) {
		  foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kickstarter']['add_cat_forms'] as $_funcRef) {
		    $lines = t3lib_div::callUserFunction($_funcRef, $lines, $this);
		  }
		}

		$content = '<table border=0 cellpadding=2 cellspacing=2>'.$this->helpIcon('forms').implode('',$lines).'</table>';
		return $content;
	}

  function helpIcon($field){	
    return '<a href="#" style="float:left;" onclick="vHWin=window.open(\''.$this->wizard->siteBackPath.TYPO3_mainDir.'view_help.php?tfID=sav_library.'.$field.'\',\'viewFieldHelp\',\'height=400,width=600,status=0,menubar=0,scrollbars=1\');vHWin.focus();return false;"><img src="'.$this->wizard->siteBackPath.TYPO3_mainDir.'gfx/helpbubble.gif" width="16" height="16" hspace="2" border="0" class="typo3-csh-icon" alt="'.$field.'" /></a>';
  }

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/sav_library/kickstarter/class.tx_kickstarter_section_forms.php']) {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/sav_library/kickstarter/class.tx_kickstarter_section_forms.php']);
}

?>
