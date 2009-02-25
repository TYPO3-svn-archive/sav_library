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
	 * Renders form for addition of formviews:
	 */
 
require_once(t3lib_extMgm::extPath('kickstarter').'class.tx_kickstarter_sectionbase.php');

class tx_kickstarter_section_formviews extends tx_kickstarter_sectionbase {
  var $sectionID = 'formviews';

	function render_wizard() {
		$lines=array();

		$action = explode(':',$this->wizard->modData['wizAction']);
		if ($action[0]=='edit')	{
			$this->regNewEntry($this->sectionID,$action[1]);
			$lines = $this->catHeaderLines($lines,$this->sectionID,$this->wizard->options[$this->sectionID],'&nbsp;',$action[1]);
			$piConf = $this->wizard->wizArray[$this->sectionID][$action[1]];
			$ffPrefix='['.$this->sectionID.']['.$action[1].']';

        // Get the style
      if (isset($this->wizard->sections[$this->sectionID]['styles'])) {
        $style = $this->wizard->sections[$this->sectionID]['styles']['defaultValue'];
      }
      if (isset($this->wizard->sections[$this->sectionID]['styles']['value'])) {
        $style = $this->wizard->sections[$this->sectionID]['styles']['value'][$piConf[$this->wizard->sections[$this->sectionID]['styles']['field']]];
      } 

      // For version comptability reason
      if ($this->wizard->wizArray[$this->sectionID][$action[1]]['showAllTitle'] && !$this->wizard->wizArray[$this->sectionID][$action[1]]['showTitleField']) {
        $piConf['showTitleField'] = $this->wizard->wizArray[$this->sectionID][$action[1]]['showAllTitle'];
        unset($this->wizard->wizArray[$this->sectionID][$action[1]]['showAllTitle']);
      }
      if ($this->wizard->wizArray[$this->sectionID][$action[1]]['showAllTitleExpand'] && !$this->wizard->wizArray[$this->sectionID][$action[1]]['showTitleFieldExpand']) {
        $piConf['showTitleFieldExpand'] = $this->wizard->wizArray[$this->sectionID][$action[1]]['showAllTitleExpand'];
        unset($this->wizard->wizArray[$this->sectionID][$action[1]]['showAllTitleExpand']);
      }
      if ($this->wizard->wizArray[$this->sectionID][$action[1]]['update'] && !$this->wizard->wizArray[$this->sectionID][$action[1]]['alt']) {
        $piConf['alt'] = $this->wizard->wizArray[$this->sectionID][$action[1]]['update'];
      }

				// Enter title of the view
      $subContent='<strong style="'.$style.'">Title:</strong><br />'.
        $this->renderStringBox($ffPrefix.'[title]',$piConf['title']);
    	$lines[]='<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).'</td></tr>';

				// Type of the view
			$optValues = array(
				'showAll' => 'Show all',
				'showSingle' => 'Show single',
				'input' => 'Input form',
				'alt' => 'Alt form',
			);
			$subContent='<strong style="'.$style.'">Type of the view:</strong><br />'.
			 $this->renderSelectBox($ffPrefix.'[type]',$piConf['type'],$optValues);
			$lines[]='<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).'</td></tr>';

  		// Enter Title
			$nbLines = substr_count($piConf['showTitleField'], chr(13).chr(10)) + 2;      
    	$subContent='<strong style="'.$style.'">'.$piConf['type'].'Title:</strong><br />'.
        ($piConf['showTitleFieldExpand'] 
          ? $this->renderTextareaBox($ffPrefix.'[showTitleField]',$piConf['showTitleField'], 500,$nbLines) 
          : $this->renderStringBox($ffPrefix."[showTitleField]",$piConf['showTitleField'],500)).
        $this->renderCheckBox($ffPrefix.'[showTitleFieldExpand]',$piConf['showTitleFieldExpand']);
     	$lines[]='<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).'</td></tr>';
     	
      switch ($piConf['type']) {
        case 'alt':   
  				// Enter showAllItemTemplate
			    $nbLines = substr_count($piConf['showAllItemTemplate'], chr(13).chr(10)) + 2;      
    			$subContent='<strong style="'.$style.'">showAllItemTemplate:</strong><br />'.
    			  $this->renderTextareaBox($ffPrefix.'[showAllItemTemplate]',$piConf['showAllItemTemplate'],500,$nbLines);
    			$lines[]='<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).'</td></tr>'; 

  				// Sub-type of the view
    			$optValues = array(
    				'update' => 'Update View',
    				'print' => 'Print View',
    			);
    			$subContent='<strong style="'.$style.'">Sub-type of the view:</strong><br />'.
    			 $this->renderSelectBox($ffPrefix.'[subtype]',$piConf['subtype'],$optValues);
    			$lines[]='<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).'</td></tr>'; 
          
          // Add the pagebreak if print is selected
          if($piConf['subtype']=='print') {
    			 $subContent='<strong style="'.$style.'">Number of items before page break:</strong><br />'.
    			   $this->renderStringBox($ffPrefix.'[pagebreak]',$piConf['pagebreak'],20);
    			 $lines[]='<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).'</td></tr>';         
    			 $subContent='<strong style="'.$style.'">Number of items before page break for the first page:</strong><br />'.
    			   $this->renderStringBox($ffPrefix.'[pagebreakfirstpage]',$piConf['pagebreakfirstpage'],20);
    			 $lines[]='<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).'</td></tr>';         
          }
              
          break;     
        case 'showAll':        
  				// Enter showAllItemTemplate
			    $nbLines = substr_count($piConf['showAllItemTemplate'], chr(13).chr(10)) + 2;      
    			$subContent='<strong style="'.$style.'">showAllItemTemplate:</strong><br />'.
    			  $this->renderTextareaBox($ffPrefix.'[showAllItemTemplate]',$piConf['showAllItemTemplate'],500,$nbLines);
    			$lines[]='<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).'</td></tr>';

  				// Enter showAllItemParentTemplate
    			$subContent='<strong style="'.$style.'">showAllItemParentTemplate:</strong><br />'.
    			  $this->renderStringBox($ffPrefix.'[showAllItemParentTemplate]',$piConf['showAllItemParentTemplate']);
    			$lines[]='<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).'</td></tr>';

          if ($piConf['showAddPrintIcon']) {
          
            // Get the print views
            $optValues = array();
            $optValues[0] = '';
            
            foreach ($this->wizard->wizArray[$this->sectionID] as $k => $view) {
              if ($view['type'] == 'alt' && $view['subtype'] == 'print') {
                $optValues[$k] = $view['title']; 
              }            
            }

    			 $addContent=' <strong style="'.$style.'">Related view:</strong> '.
    			   $this->renderSelectBox($ffPrefix.'[relview]',$piConf['relview'],$optValues);                        
          }
  		    // Enter Add Print Icon
    	    $subContent='<strong style="'.$style.'">Add print icon:</strong><br />'.
    		    $this->renderCheckBox($ffPrefix.'[showAddPrintIcon]',$piConf['showAddPrintIcon']);
    	    $lines[]='<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).$addContent.'</td></tr>';

   	    break;
    	  case 'showSingle':
          if ($piConf['showAddPrintIcon']) {
          
            // Get the print views
            $optValues = array();
            $optValues[0] = '';
            
            foreach ($this->wizard->wizArray[$this->sectionID] as $k => $view) {
              if ($view['type'] == 'alt' && $view['subtype'] == 'print') {
                $optValues[$k] = $view['title']; 
              }            
            }

    			 $addContent=' <strong style="'.$style.'">Related view:</strong> '.
    			   $this->renderSelectBox($ffPrefix.'[relview]',$piConf['relview'],$optValues);                        
          }
    	  case 'input':
    		
  		    // Enter show Folders
			    $nbLines = substr_count($piConf['showFolders'], chr(13).chr(10)) + 2;      
    	    $subContent='<strong style="'.$style.'">Folders:</strong><br />'.
    		    $this->renderTextareaBox($ffPrefix.'[showFolders]',$piConf['showFolders'],500, max(10,$nbLines));
    	    $lines[]='<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).'</td></tr>';
    	  
  		    // Enter Add Print Icon
    	    $subContent='<strong style="'.$style.'">Add print icon:</strong><br />'.
    		    $this->renderCheckBox($ffPrefix.'[showAddPrintIcon]',$piConf['showAddPrintIcon']);
    	    $lines[]='<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).$addContent.'</td></tr>';
    	    break;
     	}
		}
		
		/* HOOK: Place a hook here, so additional output can be integrated */
		if(is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kickstarter']['add_cat_formviews'])) {
		  foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kickstarter']['add_cat_formviews'] as $_funcRef) {
		    $lines = t3lib_div::callUserFunction($_funcRef, $lines, $this);
		  }
		}

		$content = '<table border=0 cellpadding=2 cellspacing=2>'.$this->helpIcon('formViews').implode('',$lines).'</table>';
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

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/sav_library/kickstarter/class.tx_kickstarter_section_formviews.php']) {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/sav_library/kickstarter/class.tx_kickstarter_section_formviews.php']);
}

?>
