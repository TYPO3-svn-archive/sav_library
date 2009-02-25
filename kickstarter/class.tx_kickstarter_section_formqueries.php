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
	 * Renders form for addition of formqueries:
	 */
 
require_once(t3lib_extMgm::extPath('kickstarter').'class.tx_kickstarter_sectionbase.php');

class tx_kickstarter_section_formqueries extends tx_kickstarter_sectionbase {
  var $sectionID = 'formqueries';

	function render_wizard() {
		$lines=array();

		$action = explode(':',$this->wizard->modData['wizAction']);
		if ($action[0]=='edit')	{
			$this->regNewEntry($this->sectionID,$action[1]);
			$lines = $this->catHeaderLines($lines,$this->sectionID,$this->wizard->options[$this->sectionID],'&nbsp;',$action[1]);

			$piConf = $this->wizard->wizArray[$this->sectionID][$action[1]];
			$ffPrefix='['.$this->sectionID.']['.$action[1].']';

				// Enter title of the view
      $subContent='<strong>Title:</strong><BR>'.
        $this->renderStringBox($ffPrefix.'[title]',$piConf['title']);
    	$lines[]='<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).'</td></tr>';
    					  
        // Enter tableLocal
      if (!$piConf['tableLocal']) {  
        // Set the first table name as the default local table    
        $tableRootName = 'tx_'.$this->wizard->extKey_nusc; 
        if (is_array($this->wizard->wizArray['tables'])) {
          $table = current($this->wizard->wizArray['tables']);
        }
        $piConf['tableLocal'] = $tableRootName.($table['tablename'] ? '_'.$table['tablename'] :'');       
      }  
      $subContent='<strong style="color:black">Local table:</strong><BR>'.
          $this->renderStringBox($ffPrefix.'[tableLocal]',$piConf['tableLocal'], 500);
      $lines[]='<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).'</td></tr>';

				// Enter tableForeign
			$nbLines = substr_count($piConf['tableForeign'], chr(13).chr(10)) + 2;
      $subContent='<strong style="color:black">Foreign table:</strong><BR>'.
          ($piConf['tableForeignExpand']
            ? $this->renderTextareaBox($ffPrefix.'[tableForeign]',$piConf['tableForeign'], 500, max(5, $nbLines))
            : $this->renderStringBox($ffPrefix.'[tableForeign]',$piConf['tableForeign'], 500)).
          $this->renderCheckBox($ffPrefix.'[tableForeignExpand]',$piConf['tableForeignExpand']);
      $lines[]='<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).'</td></tr>';

				// Enter aliases
			$nbLines = substr_count($piConf['aliases'], chr(13).chr(10)) + 2;      
      $subContent='<strong style="color:black">Aliases:</strong><BR>'.
          ($piConf['aliasesExpand'] 
            ? $this->renderTextareaBox($ffPrefix.'[aliases]',$piConf['aliases'], 500, max(5, $nbLines)) 
            : $this->renderStringBox($ffPrefix.'[aliases]',$piConf['aliases'], 500)).
          $this->renderCheckBox($ffPrefix.'[aliasesExpand]',$piConf['aliasesExpand']);
      $lines[]='<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).'</td></tr>';

				// Enter where
			$nbLines = substr_count($piConf['where'], chr(13).chr(10)) + 2;      
      $subContent='<strong style="color:black">Where clause:</strong><BR>'.
          ($piConf['whereExpand'] 
            ? $this->renderTextareaBox($ffPrefix.'[where]',$piConf['where'], 500, max(5, $nbLines)) 
            : $this->renderStringBox($ffPrefix.'[where]',$piConf['where'], 500)).
          $this->renderCheckBox($ffPrefix.'[whereExpand]',$piConf['whereExpand']);
      $lines[]='<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).'</td></tr>';
      
				// Enter group
			$nbLines = substr_count($piConf['group'], chr(13).chr(10)) + 2;      
      $subContent='<strong style="color:black">Group clause:</strong><BR>'.
          ($piConf['groupExpand'] 
            ? $this->renderTextareaBox($ffPrefix.'[group]',$piConf['group'], 500, max(5, $nbLines)) 
            : $this->renderStringBox($ffPrefix.'[group]',$piConf['group'], 500)).
          $this->renderCheckBox($ffPrefix.'[groupExpand]',$piConf['groupExpand']);
      $lines[]='<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).'</td></tr>';
          
				// Enter order
			$nbLines = substr_count($piConf['order'], chr(13).chr(10)) + 2;      
      $subContent='<strong style="color:black">Order clause:</strong><BR>'.
          ($piConf['orderExpand'] 
            ? $this->renderTextareaBox($ffPrefix.'[order]',$piConf['order'], 500, max(5, $nbLines)) 
            : $this->renderStringBox($ffPrefix.'[order]',$piConf['order'], 500)).
          $this->renderCheckBox($ffPrefix.'[orderExpand]',$piConf['orderExpand']);
      $lines[]='<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).'</td></tr>';
          
				// Enter whereTags
			$nbLines = substr_count($piConf['whereTags'], chr(13).chr(10)) + 2;      
      $subContent='<strong style="color:black">Where Tags:</strong><BR>'.
          ($piConf['whereTagsExpand'] 
            ? $this->renderTextareaBox($ffPrefix.'[whereTags]',$piConf['whereTags'], 500, max(5, $nbLines)) 
            : $this->renderStringBox($ffPrefix.'[whereTags]',$piConf['whereTags'], 500)).
          $this->renderCheckBox($ffPrefix.'[whereTagsExpand]',$piConf['whereTagsExpand']);
      $lines[]='<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).'</td></tr>';
		}
		
		/* HOOK: Place a hook here, so additional output can be integrated */
		if(is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kickstarter']['add_cat_formqueries'])) {
		  foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kickstarter']['add_cat_formqueries'] as $_funcRef) {
		    $lines = t3lib_div::callUserFunction($_funcRef, $lines, $this);
		  }
		}

		$content = '<table border=0 cellpadding=2 cellspacing=2>'.$this->helpIcon('formQueries').implode('',$lines).'</table>';

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

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/sav_library/kickstarter/class.tx_kickstarter_section_formqueries.php']) {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/sav_library/kickstarter/class.tx_kickstarter_section_formqueries.php']);
}

?>
