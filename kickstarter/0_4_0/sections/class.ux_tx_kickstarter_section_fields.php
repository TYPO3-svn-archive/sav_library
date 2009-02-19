<?php
/***************************************************************
*  Copyright notice
*
*  (c)  2001-2008 Kasper Skaarhoj (kasperYYYY@typo3.com)  All rights reserved
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
 * @author	Yolf <yolf.typo3@orange.fr>
 */

class ux_tx_kickstarter_section_fields extends tx_kickstarter_section_fields {

  var $siteBackPath ;
  
	/**
	 * Renders the form in the kickstarter; this was add_cat_fields()
	 *
	 * @return	string		HTML code
	 */
	function render_wizard() {
		$lines=array();

		$action = explode(':',$this->wizard->modData['wizAction']);
		if ($action[0]=='edit')	{
			$this->regNewEntry($this->sectionID,$action[1]);
			$lines = $this->catHeaderLines($lines,$this->sectionID,$this->wizard->options[$this->sectionID],'&nbsp;',$action[1]);
			$piConf = $this->wizard->wizArray[$this->sectionID][$action[1]];
			$ffPrefix='['.$this->sectionID.']['.$action[1].']';

		}


				// Header field
			$optValues = array(
				'tt_content' => 'tt_content (Content)',
				'fe_users' => 'fe_users (Frontend Users)',
				'fe_groups' => 'fe_groups (Frontend Groups)',
				'be_users' => 'be_users (Backend Users)',
				'be_groups' => 'be_groups (Backend Groups)',
				'pages' => 'pages (Pages)',
			);

			foreach($GLOBALS['TCA'] as $tablename => $tableTCA) {
				if(!$optValues[$tablename]) {
					$optValues[$tablename] = $tablename.' ('.$GLOBALS['LANG']->sL($tableTCA['ctrl']['title']).')';
				}
			}
			asort($optValues);

			$subContent = '<strong>Which table:<br /></strong>'.
					$this->renderSelectBox($ffPrefix.'[which_table]',$piConf['which_table'],$optValues).
					$this->whatIsThis('Select the table which should be extended with these extra fields.');
			$lines[]='<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).
				'<input type="hidden" name="' . $this->piFieldName('wizArray_upd') . $ffPrefix . '[title]" value="' . ($piConf['which_table']?$optValues[$piConf['which_table']]:'') . '" /></td></tr>';





				// PRESETS:
			$selPresetBox=$this->presetBox($piConf['fields']);

				// Fields
			$c=array(0);
			$this->usedNames=array();
			if (is_array($piConf['fields']))	{
				$piConf['fields'] = $this->cleanFieldsAndDoCommands($piConf['fields'],$this->sectionID,$action[1],$piConf['which_table']?$piConf['which_table']:'');
//--------------------------
// begin - Modified 
//--------------------------						
				$this->siteBackPath = $this->wizard->siteBackPath;
				if ($this->wizard->wizArray['savext'][1]['generateForm']) {
				

          // Add the import link
          $lines[] ='<tr><td colspan="5"><a href="#"               
                onClick="document.kickstarter_wizard[\'kickstarter[wizSpecialCmd]\'].value=\''.'importTable'.'\';
                 setFormAnchorPoint(\''.t3lib_div::shortMd5($this->piFieldName("wizArray_upd").$ffPrefix.'[fieldHeader]').'\');
      			     document.kickstarter_wizard.submit();
      			     return false;" >Import fields from table as "Only shown in SAV form"</a> '.'</td></tr>';

				  $lines[]='<tr'.$this->bgCol(1).'><td><strong> Fields Overview </strong></td></tr>';
				  $lines[]='<tr><td></td></tr>';
				

				  $subContent ='<tr '.$this->bgCol(2).'>
					 <td><strong>Name</strong></td>
					 <td><strong>Title</strong></td>
					 <td><strong>Type</strong></td>
					 <td><strong>Exclude?</strong></td>
					 <td><strong>Details</strong></td>
				  </tr>';
				
          if ($this->wizard->modData["wizId"] == t3lib_div::shortMd5($this->piFieldName("wizArray_upd").$ffPrefix.'[fieldHeader]') ) {
            $wizKey = $this->wizard->modData["wizKey"];
          } else {
            $wizKey = ($piConf['conf_viewWizKey'] ? $piConf['conf_viewWizKey'] : 1);
          }
          $this->wizard->wizArray[$this->sectionID][$action[1]]['conf_viewWizKey'] = $wizKey;

          // Change the conf_viewWizKey if activated
          if ($this->wizard->modData['wizSpecialCmd'] == 'changeAllWizKey') {
            foreach ($piConf['fields'] as $k =>$v) {
              $this->wizard->wizArray[$this->sectionID][$action[1]]['fields'][$k]['conf_showFieldWizKey'] = $wizKey;
            }
          $this->wizard->modData['wizSpecialCmd'] = ''; 
          }

          // Import the table fields as "Show Only" if activated
          if ($this->wizard->modData['wizSpecialCmd'] == 'importTable') {
  				  // Recover the wizard_form data         
    				preg_match('/EXT:([^\/]+)\//',$GLOBALS['TCA'][$piConf['which_table']]['ctrl']['title'], $matches);
            if(file_exists(t3lib_extMgm::extPath($matches[1]).'doc/wizard_form.dat')) {
    				  $temp = unserialize(file_get_contents(t3lib_extMgm::extPath($matches[1]).'doc/wizard_form.dat'));

     				  $tables = $temp['tables'];
            			  
    				  preg_match('/'.'tx_'.str_replace('_','', $temp['save']['extension_key']).'[_]?(.*)/',$piConf['which_table'],$matches);
    				  $tableName = $matches[1];			  
  				    $temp = array();
    				  foreach($tables as $keyTable=>$table) {
                if ($table['tablename']==$tableName) {
                  foreach ($tables[$keyTable]['fields'] as $keyField => $field) {

                    $temp['tables'][$keyTable]['fields'][$keyField] = $field;
                    $temp['tables'][$keyTable]['fields'][$keyField]['conf_showFieldWizKey'] = 0;
                    $temp['tables'][$keyTable]['fields'][$keyField]['type'] = 'ShowOnly';

                    if (isset($this->wizard->wizArray['formviews'])) {
                      foreach($this->wizard->wizArray['formviews'] as $keyForm => $view) {
                        $temp['tables'][$keyTable]['fields'][$keyField]['conf_showFieldDisp'][$keyForm] = 0;
                        $temp['tables'][$keyTable]['fields'][$keyField]['conf_showField'][$keyForm] = '';
                        $temp['tables'][$keyTable]['fields'][$keyField]['conf_showFieldExpand'][$keyForm] = 0;
                        $temp['tables'][$keyTable]['fields'][$keyField]['conf_showFolders'][$keyForm] = 0;
                      }
                    }
                  }
                  $this->wizard->wizArray['fields'][$action[1]]['fields'] = $temp['tables'][$keyTable]['fields'];
                  break;
                }
              }
            } elseif (is_array($GLOBALS['TCA'][$piConf['which_table']]['columns'])) {
              // Case for specific tables like fe_users
                  $columns = $GLOBALS['TCA'][$piConf['which_table']]['columns'];
                  $keyTable = $piConf['which_table'];

                  foreach ($columns as $keyField => $field) {
                    $temp['tables'][$keyTable]['fields'][$keyField]['fieldname'] = $keyField;
                    $temp['tables'][$keyTable]['fields'][$keyField]['title'] = $GLOBALS['LANG']->sL($field['label']);
                    $temp['tables'][$keyTable]['fields'][$keyField]['conf_showFieldWizKey'] = 0;
                    $temp['tables'][$keyTable]['fields'][$keyField]['type'] = 'ShowOnly';

                    if (isset($this->wizard->wizArray['formviews'])) {
                      foreach($this->wizard->wizArray['formviews'] as $keyForm => $view) {
                        $temp['tables'][$keyTable]['fields'][$keyField]['conf_showFieldDisp'] = array();
                        $temp['tables'][$keyTable]['fields'][$keyField]['conf_showFieldDisp'][$keyForm] = 0;
                        $temp['tables'][$keyTable]['fields'][$keyField]['conf_showField'][$keyForm] = '';
                        $temp['tables'][$keyTable]['fields'][$keyField]['conf_showFieldExpand'][$keyForm] = 0;
                        $temp['tables'][$keyTable]['fields'][$keyField]['conf_showFolders'][$keyForm] = 0;
                      }
                    }
                  }
                  $this->wizard->wizArray['fields'][$action[1]]['fields'] = $temp['tables'][$keyTable]['fields'];
              
            }
          $this->wizard->modData['wizSpecialCmd'] ='';
          }

          // Reorder the fields if activated
          if ($this->wizard->modData['wizSpecialCmd'] == 'reorderFields') {                  
            foreach ($piConf['fields'] as $k =>$v) {
              $this->wizard->wizArray[$this->sectionID][$action[1]]['fields'][$k]['conf_showOrder'][$wizKey] = 	$this->wizard->wizArray[$this->sectionID][$action[1]]['fields'][$k]['conf_showOrder'][$piConf['conf_opt_formViews']];
            }
             
          $this->wizard->modData['wizSpecialCmd'] = ''; 
          $this->wizard->wizArray[$this->sectionID][$action[1]]['conf_opt_formViews'] = 0;
          $piConf['conf_opt_formViews'] = 0;
          }  

          // Copy the fields if activated
          if ($this->wizard->modData['wizSpecialCmd'] == 'copyFields') {   
            foreach ($piConf['fields'] as $k =>$v) {
              // Copy the order
              $this->wizard->wizArray[$this->sectionID][$action[1]]['fields'][$k]['conf_showOrder'][$wizKey] = 	$this->wizard->wizArray[$this->sectionID][$action[1]]['fields'][$k]['conf_showOrder'][$piConf['conf_opt_formViews']];
              // Copy the display
              $this->wizard->wizArray[$this->sectionID][$action[1]]['fields'][$k]['conf_showFieldDisp'][$wizKey] = 	$this->wizard->wizArray[$this->sectionID][$action[1]]['fields'][$k]['conf_showFieldDisp'][$piConf['conf_opt_formViews']];
              // Copy the field
              $this->wizard->wizArray[$this->sectionID][$action[1]]['fields'][$k]['conf_showField'][$wizKey] = 	$this->wizard->wizArray[$this->sectionID][$action[1]]['fields'][$k]['conf_showField'][$piConf['conf_opt_formViews']];
              // Copy the expand
              $this->wizard->wizArray[$this->sectionID][$action[1]]['fields'][$k]['conf_showFieldExpand'][$wizKey] = 	$this->wizard->wizArray[$this->sectionID][$action[1]]['fields'][$k]['conf_showFieldExpand'][$piConf['conf_opt_formViews']];
              // Copy the folder
              $this->wizard->wizArray[$this->sectionID][$action[1]]['fields'][$k]['conf_showFolders'][$wizKey] = 	$this->wizard->wizArray[$this->sectionID][$action[1]]['fields'][$k]['conf_showFolders'][$piConf['conf_opt_formViews']];
            }             
            
          $this->wizard->modData['wizSpecialCmd'] = ''; 
          $this->wizard->wizArray[$this->sectionID][$action[1]]['conf_opt_formViews'] = 0;
          $piConf['conf_opt_formViews'] = 0;
          }  


          // Selector and buttons for action on the fields 
          if (isset($this->wizard->wizArray['formviews'])) {
            $opt_formViews = array();
            $opt_formViews[0] = '';
            foreach($this->wizard->wizArray['formviews'] as $key => $view) {
              $opt_formViews[$key] = $view['title'];
            }

            $subContent .='<tr><td colspan="5">Use fields as in</a> '.$this->renderSelectBox($ffPrefix.'[conf_opt_formViews]',$piConf['conf_opt_formViews'],$opt_formViews).
                 '&nbsp;&nbsp;<input type="button" onClick="document.kickstarter_wizard[\'kickstarter[wizSpecialCmd]\'].value=\''.'reorderFields'.'\';
                 setFormAnchorPoint(\''.t3lib_div::shortMd5($this->piFieldName("wizArray_upd").$ffPrefix.'[fieldHeader]').'\');
      			     document.kickstarter_wizard.submit();" name="'.$this->piFieldName('reOrder').'" value="Reorder?">'.
                 '&nbsp;&nbsp;<input type="button" onClick="document.kickstarter_wizard[\'kickstarter[wizSpecialCmd]\'].value=\''.'copyFields'.'\';
                 setFormAnchorPoint(\''.t3lib_div::shortMd5($this->piFieldName("wizArray_upd").$ffPrefix.'[fieldHeader]').'\');
      			     document.kickstarter_wizard.submit();" name="'.$this->piFieldName('copy').'" value="Copy?">'.      			     
                 '&nbsp;&nbsp;<input type="submit" name="'.$this->piFieldName('viewResult').'" value="View result">'.
                 '&nbsp;&nbsp;<input type="submit" name="'.$this->piFieldName('WRITE').'" value="Write?" onclick="
                 return confirm(\'If you are not sure, use the View result button\');               
                 " />'.
                 '</td></tr>';
          } 
          				  
				  //  Add the overview
          $subContent .= '<tr><td colspan="5" style="padding-left:0px; padding-right:0px; padding-top:0px; padding-bottom:5px;"><div style="float: left;width: 100%; background: url('.$this->siteBackPath.t3lib_extMgm::siteRelPath('sav_library').'kickstarter/taMenuBorder.gif) repeat-x bottom;"><ul style="margin: 0px;padding: 0px;list-style: none;">';
                   
          $viewKeys = array();
          if (isset($this->wizard->wizArray['formviews'])) {
            foreach($this->wizard->wizArray['formviews'] as $key => $view) {

              // Get the style
              if (isset($this->wizard->sections[$this->sectionID]['styles'])) {
                $style = $this->wizard->sections[$this->sectionID]['styles']['defaultValue'];
              }
              if (isset($this->wizard->sections[$this->sectionID]['styles']['value'])) {
                $style = $this->wizard->sections[$this->sectionID]['styles']['value'][$view[$this->wizard->sections[$this->sectionID]['styles']['field']]];
              } 
              $style .= "font-weight:bold;";
              
            $viewKeys[$key] = 0;
            
            if ($key == $wizKey) {
              $stylePos = 'background-position: 100% -150px; border-width: 0px;';
              $styleSPAN ='float: left;display: block;background: url('.$this->siteBackPath.t3lib_extMgm::siteRelPath('sav_library').'kickstarter/taMenuLeft.gif) no-repeat left top;padding: 5px 9px;white-space: nowrap; background-position: 0% -150px; padding-bottom: 6px;';
              $styleSel = $style;
            } else {
              $stylePos = 'border-bottom: 1px solid #84B0C7;';        
              $styleSPAN ='float: left;display: block;background: url('.$this->siteBackPath.t3lib_extMgm::siteRelPath('sav_library').'kickstarter/taMenuLeft.gif) no-repeat left top;padding: 5px 9px;white-space: nowrap;';
            }
            $subContent .='<li style="display: inline;margin: 0px;padding: 0px;"><a href="#"
              style="float: left;'.$style.'background: url('.$this->siteBackPath.t3lib_extMgm::siteRelPath('sav_library').'kickstarter/taMenuRight.gif) no-repeat right top; '.$stylePos.'font-size: 10px;font-weight: bold; text-decoration: none;" 
                onClick="document.kickstarter_wizard[\'kickstarter[wizSubCmd]\'].value=\''.$this->wizard->modData["wizSubCmd"].'\';
                 document.kickstarter_wizard[\'kickstarter[wizSpecialCmd]\'].value=\''.'changeAllWizKey'.'\';
      			     document.kickstarter_wizard[\'kickstarter[wizAction]\'].value=\''.$this->wizard->modData["wizAction"].'\';
      			     document.kickstarter_wizard[\'kickstarter[wizKey]\'].value=\''.$key.'\';
      			     document.kickstarter_wizard[\'kickstarter[wizId]\'].value=\''.t3lib_div::shortMd5($this->piFieldName("wizArray_upd").$ffPrefix.'[fieldHeader]').'\';
                 setFormAnchorPoint(\''.t3lib_div::shortMd5($this->piFieldName("wizArray_upd").$ffPrefix.'[fieldHeader]').'\');
      			     document.kickstarter_wizard.submit();
      			     return false;" ><span style="'.$styleSPAN.'">'.$view['title'].'</span></a></li>';
            }
          }
          $subContent .= '</td></tr>'; 
        
          // Initialize the showOrder for each view	if necessary
				  $cpt = 1;
          foreach ($piConf['fields'] as $k => $field) {
            foreach ($viewKeys as $key => $viewKey) {
              if (!isset($field['conf_showOrder'][$key])) {
                $this->wizard->wizArray[$this->sectionID][$action[1]]['fields'][$k]['conf_showOrder'][$key] = $k;
              }
            }
            if($this->wizard->wizArray[$this->sectionID][$action[1]]['conf_reorderFields']) {
              $this->wizard->wizArray[$this->sectionID][$action[1]]['fields'][$k]['conf_showOrder'][$wizKey] = $cpt;          
            }
            $cpt++;
          }
          
          unset($this->wizard->wizArray[$this->sectionID][$action[1]]['conf_reorderFields']);
          $piConf = $this->wizard->wizArray[$this->sectionID][$action[1]];
          // Put the fields in the right order for the view
          $tempo = $piConf['fields'];

          if(count($tempo)) {
            foreach ($tempo as $key => $field) {
              if (isset($field['conf_showOrder'][$wizKey])) {
                $orderedField[$field['conf_showOrder'][$wizKey]] = $key;
              } else {
                $orderedField[$key] = $key;
              }
            }
            ksort($orderedField);
            unset($piConf['fields']);
            foreach ($orderedField as $key => $field) {
              $piConf['fields'][$field] = $tempo[$field];
            }
          }
        }          
//--------------------------
// End - Modified 
//--------------------------		

					// Do it for real...
//--------------------------
// begin - Modified 
//--------------------------		
/* Original code
				reset($piConf['fields']);
				while(list($k,$v)=each($piConf['fields']))	{
					$c[]=$k;
					$subContent=$this->renderField($ffPrefix.'[fields]['.$k.']',$v);
					$lines[]='<tr'.$this->bgCol(2).'><td>'.$this->fw('<strong>FIELD:</strong> <em>'.$v['fieldname'].'</em>').'</td></tr>';
					$lines[]='<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).'</td></tr>';
				}
*/
				foreach($piConf['fields'] as $k=>$v)	{
					$c[]=$k;
          $style = ( $v['conf_showFieldDisp'][$wizKey] ? $styleSel : '');
					$subContent .= $this->renderFieldOverview($ffPrefix.'[fields]['.$k.']',$v,0,$style);
				}
				$lines[]='<tr'.$this->bgCol(3).'><td><table>'.$this->fw($subContent).'</table></td></tr>';
	
			
          unset($optValues);
          $optValues[0] = ' ';
          foreach ($piConf['fields'] as $key => $field) {
            $optValues[$key] = $field['fieldname'];
          }

				  foreach($piConf['fields'] as $k=>$v)	{
					 $c[]=$k;
					 $subContent=$this->renderField($ffPrefix.'[fields]['.$k.']',$v);
					 $prefix = $ffPrefix.'[fields]['.$k.']';
            $md5h = t3lib_div::shortMd5($this->piFieldName('wizArray_upd').$prefix.'[fieldHeader]');
          
            $fConf = $piConf['fields'][$k];
            $onCP = $this->getOnChangeParts($prefix.'[conf_moveAfter]');
            $upCP = $this->getOnChangeParts($prefix.'[conf_opt_formViews]');

					 $lines[]='<tr'.$this->bgCol(2).'><td>'.$this->fw('<a name="'.$md5h.'"></a><strong>FIELD:</strong> <em>'.$v['fieldname'].'</em>').
				    '&nbsp;&nbsp;&nbsp;&nbsp;<input type="image" hspace=2 src="'.$this->siteBackPath.TYPO3_mainDir.'gfx/move_record.gif" name="'.$this->varPrefix.'_CMD_'.$fConf['fieldname'].'_MOVE" onClick="'.$onCP[1].'" title="Move this field after">'.
            '&nbsp;&nbsp;'.$this->renderSelectBox($prefix."[conf_moveAfter]",'',$optValues).
				    '&nbsp;&nbsp;'.'<a href="'.$this->linkThisCmd().'#">'.
            '<input type="image" hspace=2 width="10" height="10" src="'.$this->siteBackPath.TYPO3_mainDir.'gfx/redup.gif" name="'.$this->varPrefix.'_CMD_'.$fConf['fieldname'].'_MOVE_OVERVIEW" onClick="'.$upCP[1].'" title="Move to the overview">'.          
            '</a></td></tr>';
					 $lines[]='<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).'</td></tr>';
				  }
  
//--------------------------
// End - Modified 
//--------------------------	

			}


				// New field:
			$k=max($c)+1;
			$v=array();
			$lines[]='<tr'.$this->bgCol(2).'><td>'.$this->fw('<strong>NEW FIELD:</strong>').'</td></tr>';
			$subContent=$this->renderField($ffPrefix.'[fields]['.$k.']',$v,1);
			$lines[]='<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).'</td></tr>';


			$lines[]='<tr'.$this->bgCol(3).'><td>'.$this->fw('<br /><br />Load preset fields: <br />'.$selPresetBox).'</td></tr>';

		/* HOOK: Place a hook here, so additional output can be integrated */
		if(is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kickstarter']['add_cat_fields'])) {
		  foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kickstarter']['add_cat_fields'] as $_funcRef) {
		    $lines = t3lib_div::callUserFunction($_funcRef, $lines, $this);
		  }
		}

		$content = '<table border="0" cellpadding="2" cellspacing="2">'.implode('',$lines).'</table>';
		return $content;
  }

	/**
	 * Cleans fields and do commands
	 *
	 * @param	array		$fConf: current field configuration
	 * @param	string		$catID: ID of current category
	 * @param	string		$action: the action that should be performed
	 * @param	string		$table: referring table
	 * @return	array		new fieldconfiguration
	 */
	function cleanFieldsAndDoCommands($fConf,$catID,$action,$table)	{
		$newFConf=array();
		$downFlag=0;
		foreach($fConf as $k=>$v)	{
			if ($v['type'] && trim($v['fieldname']))	{
				$v['fieldname'] = $this->cleanUpFieldName($v['fieldname'],$table);

				if (!$v['_DELETE'])	{
					$newFConf[$k]=$v;
					if (t3lib_div::_GP($this->varPrefix.'_CMD_'.$v['fieldname'].'_UP_x') || $downFlag)	{
						if (count($newFConf)>=2)	{
							$lastKeys = array_slice(array_keys($newFConf),-2);

							$buffer = Array();
							$buffer[$lastKeys[1]] = $newFConf[$lastKeys[1]];
							$buffer[$lastKeys[0]] = $newFConf[$lastKeys[0]];

							unset($newFConf[$lastKeys[0]]);
							unset($newFConf[$lastKeys[1]]);

							$newFConf[$lastKeys[1]] = $buffer[$lastKeys[1]];
							$newFConf[$lastKeys[0]] = $buffer[$lastKeys[0]];
//--------------------------
// Begin - Modified
//--------------------------
						  $this->wizard->wizArray[$catID][$action]['conf_reorderFields'] = 1;
//--------------------------
// End - Modified
//--------------------------

						}
						$downFlag=0;
					} elseif (t3lib_div::_GP($this->varPrefix.'_CMD_'.$v['fieldname'].'_DOWN_x'))	{
						$downFlag=1;
//--------------------------
// Begin - Modified
//--------------------------
						$this->wizard->wizArray[$catID][$action]['conf_reorderFields'] = 1;
					} elseif (t3lib_div::_GP($this->varPrefix.'_CMD_'.$v["fieldname"].'_MOVE_x')) {
						$x = array_slice(array_keys($newFConf),-1);
						$moveKey = $x[0];
						$moveBuffer = $newFConf[$moveKey];
						$this->wizard->wizArray[$catID][$action]['conf_reorderFields'] = 1;
//--------------------------
// End - Modified
//--------------------------

          }
				}
			}
		}
//--------------------------
// Begin - Modified
//--------------------------
		// Make the move if any
    if ($moveKey && $moveBuffer['conf_moveAfter']) {
		  $buffer = $newFConf;
		  unset($newFConf);
      foreach($buffer as $key => $value) {
        if ($key == $moveBuffer['conf_moveAfter']) {
          $newFConf[$key]= $value;
          $newFConf[$moveKey]= $moveBuffer;
        } elseif ($key != $moveKey) {
          $newFConf[$key]= $value;
        }
      }
    }
//--------------------------
// End - Modified
//--------------------------

		$this->wizard->wizArray[$catID][$action]['fields'] = $newFConf;
		$sesdat = $GLOBALS['BE_USER']->getSessionData('kickstarter');
		$sesdat['presets'][$this->wizard->extKey.'-'.$catID.'-'.$action]=$newFConf;
		$GLOBALS['BE_USER']->setAndSaveSessionData('kickstarter',$sesdat);

		return $newFConf;
  }

	/**
	 * Renders a single field
	 *
	 * @param	string		$prefix: The prefix for the fieldname
	 * @param	array		$fConf: field config
	 * @param	boolean		$dontRemove: if true the field can't be removed (option link is not rendered)
	 * @return	string		HTML code of the field
	 */
	function renderField($prefix,$fConf,$dontRemove=0)	{
		global $LANG;
		
		$onCP = $this->getOnChangeParts($prefix.'[fieldname]');
		$fieldName = $this->renderStringBox($prefix.'[fieldname]',$fConf['fieldname']).
			(!$dontRemove?' (Remove:'.$this->renderCheckBox($prefix.'[_DELETE]',0).')'.
				'<input type="image" hspace="2" src="'.$this->wizard->siteBackPath.TYPO3_mainDir.'gfx/pil2up.gif" name="'.$this->varPrefix.'_CMD_'.$fConf["fieldname"].'_UP" onclick="'.$onCP[1].'" />'.
				'<input type="image" hspace="2" src="'.$this->wizard->siteBackPath.TYPO3_mainDir.'gfx/pil2down.gif" name="'.$this->varPrefix.'_CMD_'.$fConf["fieldname"].'_DOWN" onclick="'.$onCP[1].'" />'.
				'<input type="image" hspace="2" src="'.$this->wizard->siteBackPath.TYPO3_mainDir.'gfx/savesnapshot.gif" name="'.$this->varPrefix.'_CMD_'.$fConf["fieldname"].'_SAVE" onclick="'.$onCP[1].'" title="Save this field setting as a preset." />':'');

		$fieldTitle = ((string)$fConf['type'] != 'passthrough') ? $this->renderStringBox_lang('title',$prefix,$fConf) : '';
		$typeCfg = '';

			// Sorting
		$optValues = array(
			'' => '',
			'input' => 'String input',
			'input+' => 'String input, advanced',
			'textarea' => 'Text area',
			'textarea_rte' => 'Text area with RTE',
			'textarea_nowrap' => 'Text area, No wrapping',
			'check' => 'Checkbox, single',
			'check_4' => 'Checkbox, 4 boxes in a row',
			'check_10' => 'Checkbox, 10 boxes in two rows (max)',
			'link' => 'Link',
			'date' => 'Date',
			'datetime' => 'Date and time',
			'integer' => 'Integer, 10-1000',
			'select' => 'Selectorbox',
			'radio' => 'Radio buttons',
			'rel' => 'Database relation',
		//	'inline'          => 'Inline relation',
			'files'           => 'Files',
			'flex'            => 'Flex',
			'none' => 'Not editable, only displayed',
			'passthrough' => '[Passthrough]',
//--------------------------
// Begin - Modified 
//--------------------------			
      'ShowOnly'  => 'Not created, only shown in SAV form',
//--------------------------
// End - Modified 
//--------------------------				
		);
		$typeCfg .= $this->renderSelectBox($prefix.'[type]',$fConf['type'],$optValues);
		$typeCfg .= $this->renderCheckBox($prefix.'[excludeField]', isset($fConf['excludeField']) ? $fConf['excludeField'] : 0);
		$typeCfg .= $LANG->getLL('fields.excludeField') . $this->whatIsThis($LANG->getLL('fields.excludeField_description')) . '<br />';

		$fDetails='';
		switch((string)$fConf['type'])	{
			case 'inline':
				$fDetails .= $this->renderInlineField($prefix,$fConf);
				break;
			case 'input+':
				$typeCfg.=$this->resImg('t_input.png','','');

				$fDetails.=$this->renderStringBox($prefix.'[conf_size]',$fConf['conf_size'],50).' Field width (5-48 relative, 30 default)<br />';
				$fDetails.=$this->renderStringBox($prefix.'[conf_max]',$fConf['conf_max'],50).' Max characters<br />';
				$fDetails.=$this->renderCheckBox($prefix.'[conf_required]',$fConf['conf_required']).'Required<br />';
				$fDetails.=$this->resImg('t_input_required.png','hspace=20','','<br /><br />');

				$fDetails.=$this->renderCheckBox($prefix.'[conf_varchar]',$fConf['conf_varchar']).'Create VARCHAR, not TINYTEXT field (if not forced INT)<br />';

				$fDetails.=$this->renderCheckBox($prefix.'[conf_check]',$fConf['conf_check']).'Apply checkbox<br />';
				$fDetails.=$this->resImg('t_input_check.png','hspace=20','','<br /><br />');

				$optValues = array(
					'' => '',
					'date' => 'Date (day-month-year)',
					'time' => 'Time (hours, minutes)',
					'timesec' => 'Time + seconds',
					'datetime' => 'Date + Time',
					'year' => 'Year',
					'int' => 'Integer',
					'int+' => 'Integer 0-1000',
					'double2' => 'Floating point, x.xx',
					'alphanum' => 'Alphanumeric only',
					'upper' => 'Upper case',
					'lower' => 'Lower case',
				);
				$fDetails.='<br />Evaluate value to:<br />'.$this->renderSelectBox($prefix.'[conf_eval]',$fConf['conf_eval'],$optValues).'<br />';
				$fDetails.=$this->renderCheckBox($prefix.'[conf_stripspace]',$fConf['conf_stripspace']).'Strip space<br />';
				$fDetails.=$this->renderCheckBox($prefix.'[conf_pass]',$fConf['conf_pass']).'Is password field<br />';
				$fDetails.=$this->renderCheckBox($prefix.'[conf_md5]',$fConf['conf_md5']).'Save as MD5<br />';
				$fDetails.=$this->resImg('t_input_password.png','hspace=20','','<br /><br />');

				$fDetails.='<br />';
				$fDetails.=$this->renderRadioBox($prefix.'[conf_unique]',$fConf['conf_unique'],'G').'Unique in whole database<br />';
				$fDetails.=$this->renderRadioBox($prefix.'[conf_unique]',$fConf['conf_unique'],'L').'Unique inside parent page<br />';
				$fDetails.=$this->renderRadioBox($prefix.'[conf_unique]',$fConf['conf_unique'],'').'Not unique (default)<br />';
				$fDetails.='<br />';
				$fDetails.=$this->renderCheckBox($prefix.'[conf_wiz_color]',$fConf['conf_wiz_color']).'Add colorpicker wizard<br />';
				$fDetails.=$this->resImg('t_input_colorwiz.png','hspace=20','','<br /><br />');
				$fDetails.=$this->renderCheckBox($prefix.'[conf_wiz_link]',$fConf['conf_wiz_link']).'Add link wizard<br />';
				$fDetails.=$this->resImg('t_input_link2.png','hspace=20','','<br /><br />');
			break;
			case 'input':
				$typeCfg.=$this->resImg('t_input.png','','');

				$fDetails.=$this->renderStringBox($prefix.'[conf_size]',$fConf['conf_size'],50).' Field width (5-48 relative, 30 default)<br />';
				$fDetails.=$this->renderStringBox($prefix.'[conf_max]',$fConf['conf_max'],50).' Max characters<br />';
				$fDetails.=$this->renderCheckBox($prefix.'[conf_required]',$fConf['conf_required']).'Required<br />';
				$fDetails.=$this->resImg('t_input_required.png','hspace=20','','<br /><br />');

				$fDetails.=$this->renderCheckBox($prefix.'[conf_varchar]',$fConf['conf_varchar']).'Create VARCHAR, not TINYTEXT field<br />';
			break;
			case 'textarea':
			case 'textarea_nowrap':
				$typeCfg.=$this->resImg('t_textarea.png','','');

				$fDetails.=$this->renderStringBox($prefix.'[conf_cols]',$fConf['conf_cols'],50).' Textarea width (5-48 relative, 30 default)<br />';
				$fDetails.=$this->renderStringBox($prefix.'[conf_rows]',$fConf['conf_rows'],50).' Number of rows (height)<br />';
				$fDetails.='<br />';
				$fDetails.=$this->renderCheckBox($prefix.'[conf_wiz_example]',$fConf['conf_wiz_example']).'Add wizard example<br />';
				$fDetails.=$this->resImg('t_textarea_wiz.png','hspace=20','','<br /><br />');
			break;
			case 'textarea_rte':
				$typeCfg.=$this->resImg($fConf['conf_rte']!='tt_content'?'t_rte.png':'t_rte2.png','','');

				$optValues = array(
					'tt_content' => 'Transform content like the Content Element "Bodytext" field (default/old)',
					'basic' => 'Typical basic setup (new "Bodytext" field based on CSS stylesheets)',
					'moderate' => 'Moderate transform of images and links',
					'none' => 'No transformation at all',
					'custom' => 'Custom'
				);
				$fDetails.='<br />Rich Text Editor Mode:<br />'.$this->renderSelectBox($prefix.'[conf_rte]',$fConf['conf_rte'],$optValues).'<br />';
				if ((string)$fConf['conf_rte']=='custom')	{
					$optValues = array(
						'cut' => array('Cut button'),
						'copy' => array('Copy button'),
						'paste' => array('Paste button'),
						'formatblock' => array('Paragraph formatting','<DIV>, <P>'),
						'class' => array('Character formatting','<SPAN>)'),
						'fontstyle' => array('Font face','<FONT face=>)'),
						'fontsize' => array('Font size','<FONT size=>)'),
						'textcolor' => array('Font color','<FONT color=>'),
						'bold' => array('Bold','<STRONG>, <B>'),
						'italic' => array('italic','<EM>, <I>'),
						'underline' => array('Underline','<U>'),
						'left' => array('Left align','<DIV>, <P>'),
						'center' => array('Center align','<DIV>, <P>'),
						'right' => array('Right align','<DIV>, <P>'),
						'orderedlist' => array('Ordered bulletlist','<OL>, <LI>'),
						'unorderedlist' => array('Unordered bulletlist','<UL>, <LI>'),
						'outdent' => array('Outdent block','<BLOCKQUOTE>'),
						'indent' => array('Indent block','<BLOCKQUOTE>'),
						'link' => array('Link','<A>'),
						'table' => array('Table','<TABLE>, <TR>, <TD>'),
						'image' => array('Image','<IMG>'),
						'line' => array('Ruler','<HR>'),
						'user' => array('User defined',''),
						'chMode' => array('Edit source?','')
					);
					$subLines=array();
					$subLines[]='<tr>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td><strong>'.$this->fw('Button name:').'</strong></td>
						<td><strong>'.$this->fw('Tags allowed:').'</strong></td>
					</tr>';
					foreach($optValues as $kk=>$vv)	{
						$subLines[]='<tr>
							<td>'.$this->renderCheckBox($prefix.'[conf_rte_b_'.$kk.']',$fConf['conf_rte_b_'.$kk]).'</td>
							<td>'.$this->resIcon($kk.'.png').'</td>
							<td>'.$this->fw($vv[0]).'</td>
							<td>'.$this->fw(htmlspecialchars($vv[1])).'</td>
						</tr>';
					}
					$fDetails.='<table border="0" cellpadding="2" cellspacing="2">'.implode('',$subLines).'</table><br />';

					$fDetails.='<br /><strong>Define specific colors:</strong><br />
						<em>Notice: Use only HEX-values for colors ("blue" should be #0000ff etc.)</em><br />';
					for($a=1;$a<4;$a++)	{
						$fDetails.='Color #'.$a.': '.$this->renderStringBox($prefix.'[conf_rte_color'.$a.']',$fConf['conf_rte_color'.$a],70).'<br />';
					}
					$fDetails.=$this->resImg('t_rte_color.png','','','<br /><br />');

					$fDetails.=$this->renderCheckBox($prefix.'[conf_rte_removecolorpicker]',$fConf['conf_rte_removecolorpicker']).'Hide colorpicker<br />';
					$fDetails.=$this->resImg('t_rte_colorpicker.png','hspace=20','','<br /><br />');

					$fDetails.='<br /><strong>Define classes:</strong><br />';
					for($a=1;$a<7;$a++)	{
						$fDetails.='Class Title:'.$this->renderStringBox($prefix.'[conf_rte_class'.$a.']',$fConf['conf_rte_class'.$a],100).
						  '<br />CSS Style: {'.$this->renderStringBox($prefix.'[conf_rte_class'.$a.'_style]',$fConf['conf_rte_class'.$a.'_style'],250).'}'.
						  '<br />';
					}
					$fDetails.=$this->resImg('t_rte_class.png','','','<br /><br />');

					$optValues = array(
						'0' => '',
						'1' => 'Hide Hx and PRE from Paragraph selector.',
						'H2H3' => 'Hide all, but H2,H3,P,PRE',
					);
					$fDetails.='<br />Hide Paragraph Items:<br />'.$this->renderSelectBox($prefix.'[conf_rte_removePdefaults]',$fConf['conf_rte_removePdefaults'],$optValues).'<br />';
					$fDetails.=$this->resImg('t_rte_hideHx.png','hspace=20','','<br /><br />');

					$fDetails.='<br /><strong>Misc:</strong><br />';
					$fDetails.=$this->renderCheckBox($prefix.'[conf_rte_div_to_p]',isset($fConf['conf_rte_div_to_p'])?$fConf['conf_rte_div_to_p']:1).htmlspecialchars('Convert all <DIV> to <P>').'<br />';
				}

				$fDetails.='<br />';
				$fDetails.=$this->renderCheckBox($prefix.'[conf_rte_fullscreen]',isset($fConf['conf_rte_fullscreen'])?$fConf['conf_rte_fullscreen']:1).'Fullscreen link<br />';
				$fDetails.=$this->resImg('t_rte_fullscreen.png','hspace=20','','<br /><br />');

				if (t3lib_div::inList('moderate,basic,custom',$fConf['conf_rte']))	{
					$fDetails.='<br />';
					$fDetails.=$this->renderCheckBox($prefix.'[conf_rte_separateStorageForImages]',isset($fConf['conf_rte_separateStorageForImages'])?$fConf['conf_rte_separateStorageForImages']:1).'Storage of images in separate folder (in uploads/[extfolder]/rte/)<br />';
				}
				if (t3lib_div::inList('moderate,custom',$fConf['conf_rte']))	{
					$fDetails.='<br />';
					$fDetails.=$this->renderCheckBox($prefix.'[conf_mode_cssOrNot]',isset($fConf['conf_mode_cssOrNot'])?$fConf['conf_mode_cssOrNot']:1) . 'Use "ts_css" transformation instead of "ts_images-ts-reglinks"<br />';
				}
			break;
			case 'check':
				$typeCfg.=$this->resImg('t_input_link.png','','');
				$fDetails.=$this->renderCheckBox($prefix.'[conf_check_default]',$fConf['conf_check_default']).'Checked by default<br />';
			break;
			case 'select':
			case 'radio':
				if ($fConf['type']=='radio')	{
					$typeCfg.=$this->resImg('t_radio.png','','');
				} else	{
					$typeCfg.=$this->resImg('t_sel.png','','');
				}
				$fDetails.='<br /><strong>Define values:</strong><br />';
				$subLines=array();
					$subLines[]='<tr>
						<td valign="top">'.$this->fw('Item label:').'</td>
						<td valign="top">'.$this->fw('Item value:').'</td>
					</tr>';
				$nItems = $fConf['conf_select_items'] = isset($fConf['conf_select_items'])?t3lib_div::intInRange(intval($fConf['conf_select_items']),0,20):4;
				for($a=0;$a<$nItems;$a++)	{
					$subLines[]='<tr>
						<td valign="top">'.$this->fw($this->renderStringBox_lang('conf_select_item_'.$a,$prefix,$fConf)).'</td>
						<td valign="top">'.$this->fw($this->renderStringBox($prefix.'[conf_select_itemvalue_'.$a.']',isset($fConf['conf_select_itemvalue_'.$a])?$fConf['conf_select_itemvalue_'.$a]:$a,50)).'</td>
					</tr>';
				}
				$fDetails.='<table border="0" cellpadding="2" cellspacing="2">'.implode('',$subLines).'</table><br />';
				$fDetails.=$this->renderStringBox($prefix.'[conf_select_items]',$fConf['conf_select_items'],50).' Number of values<br />';

				if ($fConf['type']=='select')	{
					$fDetails.=$this->renderCheckBox($prefix.'[conf_select_icons]',$fConf['conf_select_icons']).'Add a dummy set of icons<br />';
					$fDetails.=$this->resImg('t_select_icons.png','hspace="20"','','<br /><br />');

					$fDetails.=$this->renderStringBox($prefix.'[conf_relations]',t3lib_div::intInRange($fConf['conf_relations'],1,1000),50).' Max number of relations<br />';
					$fDetails.=$this->renderStringBox($prefix.'[conf_relations_selsize]',t3lib_div::intInRange($fConf['conf_relations_selsize'],1,50),50).' Size of selector box<br />';

					$fDetails.=$this->renderCheckBox($prefix.'[conf_select_pro]',$fConf['conf_select_pro']).'Add pre-processing with PHP-function<br />';
				}
			break;
			case 'rel':
				if ($fConf['conf_rel_type']=='group' || !$fConf['conf_rel_type'])	{
					$typeCfg.=$this->resImg('t_rel_group.png','','');
				} elseif(intval($fConf['conf_relations'])>1)	{
					$typeCfg.=$this->resImg('t_rel_selmulti.png','','');
				} elseif(intval($fConf['conf_relations_selsize'])>1)	{
					$typeCfg.=$this->resImg('t_rel_selx.png','','');
				} else {
					$typeCfg.=$this->resImg('t_rel_sel1.png','','');
				}


				$optValues = array(
					'pages' => 'Pages table, (pages)',
					'fe_users' => 'Frontend Users, (fe_users)',
					'fe_groups' => 'Frontend Usergroups, (fe_groups)',
					'tt_content' => 'Content elements, (tt_content)',
					'_CUSTOM' => 'Custom table (enter name below)',
					'_ALL' => 'All tables allowed!',
				);
				if ($fConf['conf_rel_type']!='group')	{unset($optValues['_ALL']);}
				$optValues = $this->addOtherExtensionTables($optValues);
				$fDetails.='<br />Create relation to table:<br />'.$this->renderSelectBox($prefix.'[conf_rel_table]',$fConf['conf_rel_table'],$optValues).'<br />';
				if ($fConf['conf_rel_table']=='_CUSTOM')	$fDetails.='Custom table name: '.$this->renderStringBox($prefix.'[conf_custom_table_name]',$fConf['conf_custom_table_name'],200).'<br />';

				$optValues = array(
					'group' => 'Field with Element Browser',		
					'select' => 'Selectorbox, select global',
					'select_cur' => 'Selectorbox, select from current page',
					'select_root' => 'Selectorbox, select from root page',
					'select_storage' => 'Selectorbox, select from storage page',
				);
				$fDetails.='<br />Type:<br />'.$this->renderSelectBox($prefix.'[conf_rel_type]',$fConf['conf_rel_type']?$fConf['conf_rel_type']:'group',$optValues).'<br />';
				if (t3lib_div::intInRange($fConf['conf_relations'],1,1000)==1 && $fConf['conf_rel_type']!='group')	{
					$fDetails.=$this->renderCheckBox($prefix.'[conf_rel_dummyitem]',$fConf['conf_rel_dummyitem']).'Add a blank item to the selector<br />';
				}

				$fDetails.=$this->renderStringBox($prefix.'[conf_relations]',t3lib_div::intInRange($fConf['conf_relations'],1,1000),50).' Max number of relations<br />';
				$fDetails.=$this->renderStringBox($prefix.'[conf_relations_selsize]',t3lib_div::intInRange($fConf['conf_relations_selsize'],1,50),50).' Size of selector box<br />';
				$fDetails.=$this->renderCheckBox($prefix.'[conf_relations_mm]',$fConf['conf_relations_mm']).'True M-M relations (otherwise commalist of values)<br />';

//--------------------------
// Begin - Modified 
//--------------------------			
        if ($this->wizard->wizArray['savext'][1]['generateForm'] && $fConf['conf_rel_type']=='group') {
				  $fDetails.=$this->renderCheckBox($prefix.'[conf_norelation]',$fConf['conf_norelation']).'Just create the Element browser (no relation)<br />';    
        }
//--------------------------
// End - Modified 
//--------------------------			
    
				if ($fConf['conf_rel_type']!='group')	{
					$fDetails.='<br />';
					$fDetails.=$this->renderCheckBox($prefix.'[conf_wiz_addrec]',$fConf['conf_wiz_addrec']).'Add "Add record" link<br />';
					$fDetails.=$this->renderCheckBox($prefix.'[conf_wiz_listrec]',$fConf['conf_wiz_listrec']).'Add "List records" link<br />';
					$fDetails.=$this->renderCheckBox($prefix.'[conf_wiz_editrec]',$fConf['conf_wiz_editrec']).'Add "Edit record" link<br />';
					$fDetails.=$this->resImg("t_rel_wizards.png",'hspace="20"','','<br /><br />');
				}
			break;
			case 'files':
				if ($fConf['conf_files_type']=='images')	{
					$typeCfg.=$this->resImg('t_file_img.png','','');
				} elseif ($fConf['conf_files_type']=='webimages')	{
					$typeCfg.=$this->resImg('t_file_web.png','','');
				} else {
					$typeCfg.=$this->resImg('t_file_all.png','','');
				}

				$optValues = array(
					'images' => 'Imagefiles',
					'webimages' => 'Web-imagefiles (gif,jpg,png)',
					'all' => 'All files, except php/php3 extensions',
				);
				$fDetails.='<br />Extensions:<br />'.$this->renderSelectBox($prefix.'[conf_files_type]',$fConf['conf_files_type'],$optValues).'<br />';

				$fDetails.=$this->renderStringBox($prefix.'[conf_files]',t3lib_div::intInRange($fConf['conf_files'],1,1000),50).' Max number of files<br />';
				$fDetails.=$this->renderStringBox($prefix.'[conf_max_filesize]',t3lib_div::intInRange($fConf['conf_max_filesize'],1,1000,500),50).' Max filesize allowed (kb)<br />';
				$fDetails.=$this->renderStringBox($prefix.'[conf_files_selsize]',t3lib_div::intInRange($fConf['conf_files_selsize'],1,50),50).' Size of selector box<br />';
				$fDetails.=$this->resImg('t_file_size.png','','','<br /><br />');
				$fDetails.=$this->renderCheckBox($prefix.'[conf_files_thumbs]',$fConf['conf_files_thumbs']).'Show thumbnails<br />';
				$fDetails.=$this->resImg('t_file_thumb.png','hspace="20"','','<br /><br />');
			break;
			case 'integer':
				$typeCfg.=$this->resImg('t_integer.png','','');
			break;
			case 'check_4':
			case 'check_10':
				if ((string)$fConf['type']=='check_4')	{
					$typeCfg.=$this->resImg('t_check4.png','','');
				} else {
					$typeCfg.=$this->resImg('t_check10.png','','');
				}
				$nItems= t3lib_div::intInRange($fConf['conf_numberBoxes'],1,10,(string)$fConf['type']=='check_4'?4:10);
				$fDetails.=$this->renderStringBox($prefix.'[conf_numberBoxes]',$nItems,50).' Number of checkboxes<br />';

				for($a=0;$a<$nItems;$a++)	{
					$fDetails.='<br />Label '.($a+1).':<br />'.$this->renderStringBox_lang('conf_boxLabel_'.$a,$prefix,$fConf);
				}
			break;
			case 'date':
				$typeCfg.=$this->resImg('t_date.png','','');
			break;
			case 'datetime':
				$typeCfg.=$this->resImg('t_datetime.png','','');
			break;
			case 'link':
				$typeCfg.=$this->resImg('t_link.png','','');
			break;
			case 'flex':
				if($this->wizard->wizArray['save']['extension_key']) {
					$extKey = $this->wizard->wizArray['save']['extension_key'];
					$fDetails.='<strong>FILE:EXT:'.$extKey.'/flexform_'.$fConf['fieldname'].'.xml</strong> will be created.';
        }
				break;
		}

		if($fConf['type']) {
			$typeCfg .= $this->textSetup('',$fDetails);
		}
//--------------------------
// Begin - Modified 
//--------------------------
    if ($this->wizard->wizArray['savext'][1]['generateForm'] && !$dontRemove) {
			$this->siteBackPath = $this->wizard->siteBackPath;

      if ($this->wizard->modData['wizId'] == t3lib_div::shortMd5($this->piFieldName('wizArray_upd').$prefix.'[fieldHeader]') ) {
        $wizKey = $this->wizard->modData['wizKey'];
      } else {
        $wizKey = ($fConf['conf_showFieldWizKey'] ? $fConf['conf_showFieldWizKey'] : 1);
      }

      if (isset($this->wizard->wizArray['formviews'])) {
        foreach($this->wizard->wizArray['formviews'] as $key=>$view) {
        
          // Get the style
          if (isset($this->wizard->sections[$this->sectionID]['styles'])) {
            $style = $this->wizard->sections[$this->sectionID]['styles']['defaultValue'];
          }
          if (isset($this->wizard->sections[$this->sectionID]['styles']['value'])) {
            $style = $this->wizard->sections[$this->sectionID]['styles']['value'][$view[$this->wizard->sections[$this->sectionID]['styles']['field']]];
          }  

        if ($key == $wizKey) {
          $stylePos = 'background-position: 100% -150px; border-width: 0px;';
          $styleSPAN ='float: left;display: block;background: url('.$this->siteBackPath.t3lib_extMgm::siteRelPath('sav_library').'kickstarter/taMenuLeft.gif) no-repeat left top;padding: 5px 9px;white-space: nowrap; background-position: 0% -150px; padding-bottom: 6px;';
          $styleSel = $style;
        } else {
          $stylePos = 'border-bottom: 1px solid #84B0C7;';        
          $styleSPAN ='float: left;display: block;background: url('.$this->siteBackPath.t3lib_extMgm::siteRelPath('sav_library').'kickstarter/taMenuLeft.gif) no-repeat left top;padding: 5px 9px;white-space: nowrap;';
        }
        $subContent .= '<li style="display: inline;margin: 0px;padding: 0px;"><a href="#"
         style="float: left;'.$style.'background: url('.$this->siteBackPath.t3lib_extMgm::siteRelPath('sav_library').'kickstarter/taMenuRight.gif) no-repeat right top; '.$stylePos.'font-size: 10px;font-weight: bold; text-decoration: none;" 
          onClick="document.kickstarter_wizard[\'kickstarter[wizSubCmd]\'].value=\''.$this->wizard->modData['wizSubCmd'].'\';
            document.kickstarter_wizard[\'kickstarter[wizSpecialCmd]\'].value=\''.'changeAllWizKey'.'\';
      			document.kickstarter_wizard[\'kickstarter[wizAction]\'].value=\''.$this->wizard->modData['wizAction'].'\';
      			document.kickstarter_wizard[\'kickstarter[wizKey]\'].value=\''.$key.'\';
      			document.kickstarter_wizard[\'kickstarter[wizId]\'].value=\''.t3lib_div::shortMd5($this->piFieldName("wizArray_upd").$prefix.'[fieldHeader]').'\';
            setFormAnchorPoint(\''.t3lib_div::shortMd5($this->piFieldName("wizArray_upd").$prefix.'[fieldHeader]').'\');
      			document.kickstarter_wizard.submit();
      			return false;" ><span style="'.$styleSPAN.'">'.$view['title'].'</span></a></li>';
      	}
      }   

      if ($this->wizard->wizArray['formviews'][$wizKey]['showFolders']) {
        $showFolders = explode(';',';'.$this->wizard->wizArray['formviews'][$wizKey]['showFolders']); 
        $nb_showFolders = count($showFolders);

        // Remove the last item, if necessary  
        if ($nb_showFolders >1 && !trim($showFolders[$nb_showFolders-1])) {
          unset($showFolders[$nb_showFolders-1]);
        }

        foreach($showFolders as $v) {
          $x = explode(':', $v);
          $opt_showFolders[] = $x[0];
        }  

        $showFoldersCfg = $this->renderSelectBox($prefix.'[conf_showFolders]['.$wizKey.']',$fConf['conf_showFolders'][$wizKey],$opt_showFolders);
      }

      $nbLines = substr_count($fConf['conf_showField'][$wizKey], chr(13).chr(10)) + 2;      
      $showField =  $this->renderTextareaBox($prefix.'[conf_showField]['.$wizKey.']',$fConf['conf_showField'][$wizKey],400,($fConf['conf_showFieldExpand'][$wizKey]?$nbLines:5)).$this->renderCheckBox($prefix.'[conf_showFieldExpand]['.$wizKey.']',$fConf['conf_showFieldExpand'][$wizKey]).'<br />';      
      $additionalContent .= '
			<input type="hidden" name="'.$this->piFieldName('wizArray_upd').$prefix.'[conf_showFieldWizKey]" value="'.$wizKey.'">
			<tr><td colspan="2"><table style="background-color:#f1fbfd; border: 1px  #000099 solid;">
		  <tr><td colspan="2" style="padding-left:0px; padding-right:0px; padding-top:0px; padding-bottom:5px;">
        <div style="float: left;width: 100%; background: url('.$this->siteBackPath.t3lib_extMgm::siteRelPath('sav_library').'kickstarter/taMenuBorder.gif) repeat-x bottom;">
          <ul style="margin: 0px;padding: 0px;list-style: none;">'.$subContent.'</ul>'.
          $this->helpIcon($fConf['type']).'
        </div>
      </td></tr>
			<tr><td valign=top>'.$this->fw($this->renderCheckBox($prefix.'[conf_showFieldDisp]['.$wizKey.']',$fConf['conf_showFieldDisp'][$wizKey]).'&nbsp;<b style="'.$styleSel.'">Select:</b><br>').$showFoldersCfg.'</td><td valign=top>'.$this->fw($showField).'</td></tr>
			</table></tr></td>
      ';

    }	

		$content='<table border="0" cellpadding="0" cellspacing="0">
			<tr><td valign="top">'.$this->fw('Field name:').'</td><td valign="top">'.$this->fw($fieldName).'</td></tr>
			<tr><td valign="top">'.$this->fw('Field title:').'</td><td valign="top">'.$this->fw($fieldTitle).'</td></tr>
			<tr><td valign="top">'.$this->fw('Field type:').'</td><td valign="top">'.$this->fw($typeCfg).'</td></tr>
			'.$additionalContent.'
		</table>';

/* Original code
		$content = '<table border="0" cellpadding="0" cellspacing="0">
			<tr><td valign="top">'.$this->fw('Field name:').'</td><td valign="top">'.$this->fw($fieldName).'</td></tr>
			<tr><td valign="top">'.$this->fw('Field title:').'</td><td valign="top">'.$this->fw($fieldTitle).'</td></tr>
			<tr><td valign="top">'.$this->fw('Field type:').'</td><td valign="top">'.$this->fw($typeCfg).'</td></tr>
		</table>';
*/
//--------------------------
// End - Modified 
//--------------------------	

		return $content;
	}


//--------------------------
// Begin - Modified 
//--------------------------	
  function helpIcon($field){
    return '<a href="#" style="float:right;" onclick="vHWin=window.open(\''.$this->wizard->siteBackPath.TYPO3_mainDir.'view_help.php?tfID=sav_library.'.$field.'\',\'viewFieldHelp\',\'height=400,width=600,status=0,menubar=0,scrollbars=1\');vHWin.focus();return false;"><img src="'.$this->wizard->siteBackPath.TYPO3_mainDir.'gfx/helpbubble.gif" width="16" height="16" hspace="2" border="0" class="typo3-csh-icon" alt="'.$field.'" /></a>';
  }
//--------------------------
// End - Modified 
//--------------------------	



	/**
	 * Creates the TCA for fields
	 *
	 * @param	array		&$DBfields: array of fields (PASSED BY REFERENCE)
	 * @param	array		$columns: $array of fields (PASSED BY REFERENCE)
	 * @param	array		$fConf: field config
	 * @param	string		$WOP: ???
	 * @param	string		$table: tablename
	 * @param	string		$extKey: extensionkey
	 * @return	void
	 */
	function makeFieldTCA(&$DBfields,&$columns,$fConf,$WOP,$table,$extKey)	{
		if (!(string)$fConf['type'])	return;
		$id = $table.'_'.$fConf['fieldname'];

		$configL=array();
		$t = (string)$fConf['type'];
		switch($t)	{
			case 'input':
			case 'input+':
				$isString = true;
				$configL[]='\'type\' => \'input\',	' . $this->WOPcomment('WOP:'.$WOP.'[type]');
				$configL[]='\'size\' => \'' . t3lib_div::intInRange($fConf['conf_size'],5,48,30) . '\',	' .$this->WOPcomment('WOP:'.$WOP.'[conf_size]');
				if (intval($fConf['conf_max']))	$configL[]='\'max\' => \'' . t3lib_div::intInRange($fConf['conf_max'],1,255).'\',	'.$this->WOPcomment('WOP:'.$WOP.'[conf_max]');

				$evalItems=array();
				if ($fConf['conf_required'])	{$evalItems[0][] = 'required';			$evalItems[1][] = $WOP.'[conf_required]';}

				if ($t=='input+')	{
					$isString = (bool) !$fConf['conf_eval'] || t3lib_div::inList('alphanum,upper,lower',$fConf['conf_eval']);
					$isDouble2 = (bool) !$fConf['conf_eval'] || t3lib_div::inList('double2',$fConf['conf_eval']);
					if ($fConf['conf_varchar'] && $isString)		{$evalItems[0][] = 'trim';			$evalItems[1][] = $WOP.'[conf_varchar]';}
					if ($fConf['conf_eval']=='int+')	{
						$configL[]='\'range\' => array (\'lower\'=>0,\'upper\'=>1000),	'.$this->WOPcomment('WOP:'.$WOP.'[conf_eval] = int+ results in a range setting');
						$fConf['conf_eval']='int';
					}
					if ($fConf['conf_eval'])		{$evalItems[0][] = $fConf['conf_eval'];			$evalItems[1][] = $WOP.'[conf_eval]';}
					if ($fConf['conf_check'])	$configL[]='\'checkbox\' => \''.($isString?'':'0').'\',	'.$this->WOPcomment('WOP:'.$WOP.'[conf_check]');

					if ($fConf['conf_stripspace'])		{$evalItems[0][] = 'nospace';			$evalItems[1][] = $WOP.'[conf_stripspace]';}
					if ($fConf['conf_pass'])		{$evalItems[0][] = 'password';			$evalItems[1][] = $WOP.'[conf_pass]';}
					if ($fConf['conf_md5'])		{$evalItems[0][] = 'md5';			$evalItems[1][] = $WOP.'[conf_md5]';}
 					if ($fConf['conf_unique'])	{
						if ($fConf['conf_unique']=='L')		{$evalItems[0][] = 'uniqueInPid';			$evalItems[1][] = $WOP.'[conf_unique] = Local (unique in this page (PID))';}
						if ($fConf['conf_unique']=='G')		{$evalItems[0][] = 'unique';			$evalItems[1][] = $WOP.'[conf_unique] = Global (unique in whole database)';}
					}

					$wizards =array();
					if ($fConf['conf_wiz_color'])	{
						$wizards[] = trim($this->sPS('
							'.$this->WOPcomment('WOP:'.$WOP.'[conf_wiz_color]').'
							\'color\' => array(
								\'title\' => \'Color:\',
								\'type\' => \'colorbox\',
								\'dim\' => \'12x12\',
								\'tableStyle\' => \'border:solid 1px black;\',
								\'script\' => \'wizard_colorpicker.php\',
								\'JSopenParams\' => \'height=300,width=250,status=0,menubar=0,scrollbars=1\',
							),
						'));
					}
					if ($fConf['conf_wiz_link'])	{
						$wizards[] = trim($this->sPS('
							'.$this->WOPcomment('WOP:'.$WOP.'[conf_wiz_link]').'
							\'link\' => array(
								\'type\' => \'popup\',
								\'title\' => \'Link\',
								\'icon\' => \'link_popup.gif\',
								\'script\' => \'browse_links.php?mode=wizard\',
								\'JSopenParams\' => \'height=300,width=500,status=0,menubar=0,scrollbars=1\'
							),
						'));
					}
					if (count($wizards))	{
						$configL[]=trim($this->wrapBody('
							\'wizards\' => array(
								\'_PADDING\' => 2,
								',implode(chr(10),$wizards),'
							),
						'));
					}
				} else {
					if ($fConf['conf_varchar'])		{$evalItems[0][] = 'trim';			$evalItems[1][] = $WOP.'[conf_varchar]';}
				}

				if (count($evalItems))	$configL[]='\'eval\' => \''.implode(",",$evalItems[0]).'\',	'.$this->WOPcomment('WOP:'.implode(' / ',$evalItems[1]));

				if (!$isString && !$isDouble2)	{
					$DBfields[] = $fConf['fieldname'] . ' int(11) DEFAULT \'0\' NOT NULL,';
				} elseif (!$isString && $isDouble2) {
					$DBfields[] = $fConf["fieldname"]." double(11,2) DEFAULT '0.00' NOT NULL,";
				} elseif (!$fConf['conf_varchar'])		{
					$DBfields[] = $fConf['fieldname'] . ' tinytext,';
				} else {
					$varCharLn = (intval($fConf['conf_max'])?t3lib_div::intInRange($fConf['conf_max'],1,255):255);
					$DBfields[] = $fConf['fieldname'] . ' ' . ($varCharLn>$this->wizard->charMaxLng?'var':'') . 'char(' . $varCharLn .') DEFAULT \'\' NOT NULL,';
				}
			break;
			case 'link':
				$DBfields[] = $fConf['fieldname'].' tinytext,';
				$configL[]  = trim($this->sPS('
					\'type\'     => \'input\',
					\'size\'     => \'15\',
					\'max\'      => \'255\',
					\'checkbox\' => \'\',
					\'eval\'     => \'trim\',
					\'wizards\'  => array(
						\'_PADDING\' => 2,
						\'link\'     => array(
							\'type\'         => \'popup\',
							\'title\'        => \'Link\',
							\'icon\'         => \'link_popup.gif\',
							\'script\'       => \'browse_links.php?mode=wizard\',
							\'JSopenParams\' => \'height=300,width=500,status=0,menubar=0,scrollbars=1\'
						)
					)
				'));
			break;
			case 'datetime':
			case 'date':
				$DBfields[] = $fConf['fieldname'].' int(11) DEFAULT \'0\' NOT NULL,';
				$configL[] = trim($this->sPS('
					\'type\'     => \'input\',
					\'size\'     => \''.($t=="datetime"?12:8).'\',
					\'max\'      => \'20\',
					\'eval\'     => \''.$t.'\',
					\'checkbox\' => \'0\',
					\'default\'  => \'0\'
				'));
			break;
			case 'integer':
				$DBfields[] = $fConf['fieldname'] . ' int(11) DEFAULT \'0\' NOT NULL,';
				$configL[]=trim($this->sPS('
					\'type\'     => \'input\',
					\'size\'     => \'4\',
					\'max\'      => \'4\',
					\'eval\'     => \'int\',
					\'checkbox\' => \'0\',
					\'range\'    => array (
						\'upper\' => \'1000\',
						\'lower\' => \'10\'
					),
					\'default\' => 0
				'));
			break;
			case 'textarea':
			case 'textarea_nowrap':
				$DBfields[] = $fConf['fieldname'].' text,';
				$configL[]='\'type\' => \'text\',';
				if ($t=='textarea_nowrap')	{
					$configL[]='\'wrap\' => \'OFF\',';
				}
				$configL[]='\'cols\' => \''.t3lib_div::intInRange($fConf["conf_cols"],5,48,30).'\',	'.$this->WOPcomment('WOP:'.$WOP.'[conf_cols]');
				$configL[]='\'rows\' => \''.t3lib_div::intInRange($fConf["conf_rows"],1,20,5).'\',	'.$this->WOPcomment('WOP:'.$WOP.'[conf_rows]');
				if ($fConf["conf_wiz_example"])	{
					$wizards =array();
					$wizards[] = trim($this->sPS('
						'.$this->WOPcomment('WOP:'.$WOP.'[conf_wiz_example]').'
						\'example\' => array(
							\'title\'         => \'Example Wizard:\',
							\'type\'          => \'script\',
							\'notNewRecords\' => 1,
							\'icon\'          => t3lib_extMgm::extRelPath(\''.$extKey.'\').\''.$id.'/wizard_icon.gif\',
							\'script\'        => t3lib_extMgm::extRelPath(\''.$extKey.'\').\''.$id.'/index.php\',
						),
					'));

					$cN = $this->returnName($extKey,'class',$id.'wiz');
					$this->writeStandardBE_xMod(
						$extKey,
						array('title'=>'Example Wizard title...'),
						$id.'/',
						$cN,
						0,
						$id.'wiz'
					);
					$this->addFileToFileArray($id.'/wizard_icon.gif',t3lib_div::getUrl(t3lib_extMgm::extPath('kickstarter').'res/notfound.gif'));

					$configL[]=trim($this->wrapBody('
						\'wizards\' => array(
							\'_PADDING\' => 2,
							',implode(chr(10),$wizards),'
						),
					'));
				}
			break;
			case 'textarea_rte':
				$DBfields[] = $fConf['fieldname'].' text,';
				$configL[]  = '\'type\' => \'text\',';
				$configL[]  = '\'cols\' => \'30\',';
				$configL[]  = '\'rows\' => \'5\',';
				if ($fConf['conf_rte_fullscreen'])	{
					$wizards =array();
					$wizards[] = trim($this->sPS('
						'.$this->WOPcomment('WOP:'.$WOP.'[conf_rte_fullscreen]').'
						\'RTE\' => array(
							\'notNewRecords\' => 1,
							\'RTEonly\'       => 1,
							\'type\'          => \'script\',
							\'title\'         => \'Full screen Rich Text Editing|Formatteret redigering i hele vinduet\',
							\'icon\'          => \'wizard_rte2.gif\',
							\'script\'        => \'wizard_rte.php\',
						),
					'));
					$configL[]=trim($this->wrapBody('
						\'wizards\' => array(
							\'_PADDING\' => 2,
							',implode(chr(10),$wizards),'
						),
					'));
				}

				$rteImageDir = '';
				if ($fConf['conf_rte_separateStorageForImages'] && t3lib_div::inList('moderate,basic,custom',$fConf['conf_rte']))	{
					$this->wizard->EM_CONF_presets['createDirs'][]=$this->ulFolder($extKey).'rte/';
					$rteImageDir = '|imgpath='.$this->ulFolder($extKey).'rte/';
				}

				$transformation='ts_images-ts_reglinks';
				if ($fConf['conf_mode_cssOrNot'] && t3lib_div::inList('moderate,custom',$fConf['conf_rte']))	{
					$transformation='ts_css';
				}


				switch($fConf['conf_rte'])	{
					case 'tt_content':
						$typeP = 'richtext[]:rte_transform[mode=ts]';
					break;
					case 'moderate':
						$typeP = 'richtext[]:rte_transform[mode='.$transformation.''.$rteImageDir.']';
					break;
					case 'basic':
						$typeP = 'richtext[]:rte_transform[mode=ts_css'.$rteImageDir.']';
						$this->wizard->ext_localconf[]=trim($this->wrapBody("
								t3lib_extMgm::addPageTSConfig('

									# ***************************************************************************************
									# CONFIGURATION of RTE in table \"".$table."\", field \"".$fConf["fieldname"]."\"
									# ***************************************************************************************

									",trim($this->slashValueForSingleDashes(str_replace(chr(9),"  ",$this->sPS("
										RTE.config.".$table.".".$fConf["fieldname"]." {
											hidePStyleItems = H1, H4, H5, H6
											proc.exitHTMLparser_db=1
											proc.exitHTMLparser_db {
												keepNonMatchedTags=1
												tags.font.allowedAttribs= color
												tags.font.rmTagIfNoAttrib = 1
												tags.font.nesting = global
											}
										}
									")))),"
								');
						",0));
					break;
					case 'none':
						$typeP = 'richtext[]';
					break;
					case 'custom':
						$enabledButtons=array();
						$traverseList = explode(',','cut,copy,paste,formatblock,class,fontstyle,fontsize,textcolor,bold,italic,underline,left,center,right,orderedlist,unorderedlist,outdent,indent,link,table,image,line,user,chMode');
						$HTMLparser=array();
						$fontAllowedAttrib=array();
						$allowedTags_WOP = array();
						$allowedTags=array();
						while(list(,$lI)=each($traverseList))	{
							$nothingDone=0;
							if ($fConf['conf_rte_b_'.$lI])	{
								$enabledButtons[]=$lI;
								switch($lI)	{
									case 'formatblock':
									case 'left':
									case 'center':
									case 'right':
										$allowedTags[]='div';
										$allowedTags[]='p';
									break;
									case 'class':
										$allowedTags[]='span';
									break;
									case 'fontstyle':
										$allowedTags[]='font';
										$fontAllowedAttrib[]='face';
									break;
									case 'fontsize':
										$allowedTags[]='font';
										$fontAllowedAttrib[]='size';
									break;
									case 'textcolor':
										$allowedTags[]='font';
										$fontAllowedAttrib[]='color';
									break;
									case 'bold':
										$allowedTags[]='b';
										$allowedTags[]='strong';
									break;
									case 'italic':
										$allowedTags[]='i';
										$allowedTags[]='em';
									break;
									case 'underline':
										$allowedTags[]='u';
									break;
									case 'orderedlist':
										$allowedTags[]='ol';
										$allowedTags[]='li';
									break;
									case 'unorderedlist':
										$allowedTags[]='ul';
										$allowedTags[]='li';
									break;
									case 'outdent':
									case 'indent':
										$allowedTags[]='blockquote';
									break;
									case 'link':
										$allowedTags[]='a';
									break;
									case 'table':
										$allowedTags[]='table';
										$allowedTags[]='tr';
										$allowedTags[]='td';
									break;
									case 'image':
										$allowedTags[]='img';
									break;
									case 'line':
										$allowedTags[]='hr';
									break;
									default:
										$nothingDone=1;
									break;
								}
								if (!$nothingDone)	$allowedTags_WOP[] = $WOP.'[conf_rte_b_'.$lI.']';
							}
						}
						if (count($fontAllowedAttrib))	{
							$HTMLparser[]='tags.font.allowedAttribs = '.implode(',',$fontAllowedAttrib);
							$HTMLparser[]='tags.font.rmTagIfNoAttrib = 1';
							$HTMLparser[]='tags.font.nesting = global';
						}
						if (count($enabledButtons))	{
							$typeP = 'richtext['.implode('|',$enabledButtons).']:rte_transform[mode='.$transformation.''.$rteImageDir.']';
						}

						$rte_colors=array();
						$setupUpColors=array();
						for ($a=1;$a<=3;$a++)	{
							if ($fConf['conf_rte_color'.$a])	{
								$rte_colors[$id.'_color'.$a]=trim($this->sPS('
									'.$this->WOPcomment('WOP:'.$WOP.'[conf_rte_color'.$a.']').'
									'.$id.'_color'.$a.' {
										name = Color '.$a.'
										value = '.$fConf['conf_rte_color'.$a].'
									}
								'));
								$setupUpColors[]=trim($fConf['conf_rte_color'.$a]);
							}
						}

						$rte_classes=array();
						for ($a=1;$a<=6;$a++)	{
							if ($fConf['conf_rte_class'.$a])	{
								$rte_classes[$id.'_class'.$a]=trim($this->sPS('
									'.$this->WOPcomment('WOP:'.$WOP.'[conf_rte_class'.$a.']').'
									'.$id.'_class'.$a.' {
										name = '.$fConf['conf_rte_class'.$a].'
										value = '.$fConf['conf_rte_class'.$a.'_style'].'
									}
								'));
							}
						}

						$PageTSconfig= Array();
						if ($fConf['conf_rte_removecolorpicker'])	{
							$PageTSconfig[]='	'.$this->WOPcomment('WOP:'.$WOP.'[conf_rte_removecolorpicker]');
							$PageTSconfig[]='disableColorPicker = 1';
						}
						if (count($rte_classes))	{
							$PageTSconfig[]='	'.$this->WOPcomment('WOP:'.$WOP.'[conf_rte_class*]');
							$PageTSconfig[]='classesParagraph = '.implode(', ',array_keys($rte_classes));
							$PageTSconfig[]='classesCharacter = '.implode(', ',array_keys($rte_classes));
							if (in_array('p',$allowedTags) || in_array('div',$allowedTags))	{
								$HTMLparser[]='	'.$this->WOPcomment('WOP:'.$WOP.'[conf_rte_class*]');
								if (in_array('p',$allowedTags))	{$HTMLparser[]='p.fixAttrib.class.list = ,'.implode(',',array_keys($rte_classes));}
								if (in_array('div',$allowedTags))	{$HTMLparser[]='div.fixAttrib.class.list = ,'.implode(',',array_keys($rte_classes));}
							}
						}
						if (count($rte_colors))		{
							$PageTSconfig[]='	'.$this->WOPcomment('WOP:'.$WOP.'[conf_rte_color*]');
							$PageTSconfig[]='colors = '.implode(', ',array_keys($rte_colors));

							if (in_array('color',$fontAllowedAttrib) && $fConf['conf_rte_removecolorpicker'])	{
								$HTMLparser[]='	'.$this->WOPcomment('WOP:'.$WOP.'[conf_rte_removecolorpicker]');
								$HTMLparser[]='tags.font.fixAttrib.color.list = ,'.implode(',',$setupUpColors);
								$HTMLparser[]='tags.font.fixAttrib.color.removeIfFalse = 1';
							}
						}
						if (!strcmp($fConf['conf_rte_removePdefaults'],1))	{
							$PageTSconfig[]='	'.$this->WOPcomment('WOP:'.$WOP.'[conf_rte_removePdefaults]');
							$PageTSconfig[]='hidePStyleItems = H1, H2, H3, H4, H5, H6, PRE';
						} elseif ($fConf['conf_rte_removePdefaults']=='H2H3')	{
							$PageTSconfig[]='	'.$this->WOPcomment('WOP:'.$WOP.'[conf_rte_removePdefaults]');
							$PageTSconfig[]='hidePStyleItems = H1, H4, H5, H6';
						} else {
							$allowedTags[]='h1';
							$allowedTags[]='h2';
							$allowedTags[]='h3';
							$allowedTags[]='h4';
							$allowedTags[]='h5';
							$allowedTags[]='h6';
							$allowedTags[]='pre';
						}


						$allowedTags = array_unique($allowedTags);
						if (count($allowedTags))	{
							$HTMLparser[]='	'.$this->WOPcomment('WOP:'.implode(' / ',$allowedTags_WOP));
							$HTMLparser[]='allowTags = '.implode(', ',$allowedTags);
						}
						if ($fConf['conf_rte_div_to_p'])	{
							$HTMLparser[]='	'.$this->WOPcomment('WOP:'.$WOP.'[conf_rte_div_to_p]');
							$HTMLparser[]='tags.div.remap = P';
						}
						if (count($HTMLparser))	{
							$PageTSconfig[]=trim($this->wrapBody('
								proc.exitHTMLparser_db=1
								proc.exitHTMLparser_db {
									',implode(chr(10),$HTMLparser),'
								}
							'));
						}

						$finalPageTSconfig=array();
						if (count($rte_colors))		{
							$finalPageTSconfig[]=trim($this->wrapBody('
								RTE.colors {
								',implode(chr(10),$rte_colors),'
								}
							'));
						}
						if (count($rte_classes))		{
							$finalPageTSconfig[]=trim($this->wrapBody('
								RTE.classes {
								',implode(chr(10),$rte_classes),'
								}
							'));
						}
						if (count($PageTSconfig))		{
							$finalPageTSconfig[]=trim($this->wrapBody('
								RTE.config.'.$table.'.'.$fConf['fieldname'].' {
								',implode(chr(10),$PageTSconfig),'
								}
							'));
						}
						if (count($finalPageTSconfig))	{
							$this->wizard->ext_localconf[]=trim($this->wrapBody("
								t3lib_extMgm::addPageTSConfig('

									# ***************************************************************************************
									# CONFIGURATION of RTE in table \"".$table."\", field \"".$fConf["fieldname"]."\"
									# ***************************************************************************************

								",trim($this->slashValueForSingleDashes(str_replace(chr(9),"  ",implode(chr(10).chr(10),$finalPageTSconfig)))),"
								');
							",0));
						}
					break;
				}
				$this->wizard->_typeP[$fConf['fieldname']]	= $typeP;
			break;
			case 'check':
			case 'check_4':
			case 'check_10':
				$configL[]='\'type\' => \'check\',';
				if ($t=='check')	{
					$DBfields[] = $fConf['fieldname'].' tinyint(3) DEFAULT \'0\' NOT NULL,';
					if ($fConf['conf_check_default'])	$configL[]='\'default\' => 1,	'.$this->WOPcomment('WOP:'.$WOP.'[conf_check_default]');
				} else {
					$DBfields[] = $fConf['fieldname'].' int(11) DEFAULT \'0\' NOT NULL,';
				}
				if ($t=='check_4' || $t=='check_10')	{
					$configL[]='\'cols\' => 4,';
					$cItems=array();
					$aMax = intval($fConf["conf_numberBoxes"]);
					for($a=0;$a<$aMax;$a++)	{
						$cItems[]='array(\''.addslashes($this->getSplitLabels_reference($fConf,"conf_boxLabel_".$a,$table.".".$fConf["fieldname"].".I.".$a)).'\', \'\'),';
					}
					$configL[]=trim($this->wrapBody('
						\'items\' => array (
							',implode(chr(10),$cItems),'
						),
					'));
				}
			break;
			case 'radio':
			case 'select':
				$configL[]='\'type\' => \''.($t == 'select' ? 'select' : 'radio').'\',';
				$notIntVal=0;
				$len=array();
				for($a=0;$a<t3lib_div::intInRange($fConf["conf_select_items"],1,20);$a++)	{
					$val = $fConf["conf_select_itemvalue_".$a];
					$notIntVal+= t3lib_div::testInt($val)?0:1;
					$len[]=strlen($val);
					if ($fConf["conf_select_icons"] && $t=="select")	{
						$icon = ', t3lib_extMgm::extRelPath(\''.$extKey.'\').\''.'selicon_'.$id.'_'.$a.'.gif'.'\'';
										// Add wizard icon
						$this->addFileToFileArray("selicon_".$id."_".$a.".gif",t3lib_div::getUrl(t3lib_extMgm::extPath("kickstarter")."res/wiz.gif"));
					} else $icon="";
//					$cItems[]='Array("'.str_replace("\\'","'",addslashes($this->getSplitLabels($fConf,"conf_select_item_".$a))).'", "'.addslashes($val).'"'.$icon.'),';
					$cItems[]='array(\''.addslashes($this->getSplitLabels_reference($fConf,"conf_select_item_".$a,$table.".".$fConf["fieldname"].".I.".$a)).'\', \''.addslashes($val).'\''.$icon.'),';
				}
				$configL[]=trim($this->wrapBody('
					'.$this->WOPcomment('WOP:'.$WOP.'[conf_select_items]').'
					\'items\' => array (
						',implode(chr(10),$cItems),'
					),
				'));
				if ($fConf['conf_select_pro'] && $t=='select')	{
					$cN = $this->returnName($extKey,'class',$id);
					$configL[]='\'itemsProcFunc\' => \''.$cN.'->main\',	'.$this->WOPcomment('WOP:'.$WOP.'[conf_select_pro]');

					$classContent = $this->sPS(
						'class '.$cN.' {

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$$params: ...
	 * @param	[type]		$pObj: ...
	 * @return	[type]		...
	 */
							function main(&$params,&$pObj)	{
/*
								debug(\'Hello World!\',1);
								debug(\'$params:\',1);
								debug($params);
								debug(\'$pObj:\',1);
								debug($pObj);
*/
									// Adding an item!
								$params[\'items\'][] = array($pObj->sL(\'Added label by PHP function|Tilfjet Dansk tekst med PHP funktion\'), 999);

								// No return - the $params and $pObj variables are passed by reference, so just change content in then and it is passed back automatically...
							}
						}
					',
					0);

					$this->addFileToFileArray(
						'class.'.$cN.'.php',
						$this->PHPclassFile(
							$extKey,
							'class.'.$cN.'.php',
							$classContent,
							'Class/Function which manipulates the item-array for table/field '.$id.'.'
						)
					);

					$this->wizard->ext_tables[]=$this->sPS('
						'.$this->WOPcomment('WOP:'.$WOP.'[conf_select_pro]:').'
						if (TYPO3_MODE == \'BE\')	{
							include_once(t3lib_extMgm::extPath(\''.$extKey.'\').\''.'class.'.$cN.'.php\');
						}
					');
				}

				$numberOfRelations = t3lib_div::intInRange($fConf["conf_relations"],1,100);
				if ($t == 'select')	{
					$configL[]='\'size\' => '.t3lib_div::intInRange($fConf["conf_relations_selsize"],1,100).',	'.$this->WOPcomment('WOP:'.$WOP.'[conf_relations_selsize]');
					$configL[]='\'maxitems\' => '.$numberOfRelations.',	'.$this->WOPcomment('WOP:'.$WOP.'[conf_relations]');
				}

				if ($numberOfRelations>1 && $t=="select")	{
					if ($numberOfRelations*4 < 256)	{
						$DBfields[] = $fConf["fieldname"]." varchar(".($numberOfRelations*4).") DEFAULT '' NOT NULL,";
					} else {
						$DBfields[] = $fConf["fieldname"]." text,";
					}
				} elseif ($notIntVal)	{
					$varCharLn = t3lib_div::intInRange(max($len),1);
					$DBfields[] = $fConf["fieldname"]." ".($varCharLn>$this->wizard->charMaxLng?'var':'')."char(".$varCharLn.") DEFAULT '' NOT NULL,";
				} else {
					$DBfields[] = $fConf["fieldname"].' int(11) DEFAULT \'0\' NOT NULL,';
				}
			break;
			case 'rel':
				if ($fConf["conf_rel_type"]=="group")	{
					$configL[]='\'type\' => \'group\',	'.$this->WOPcomment('WOP:'.$WOP.'[conf_rel_type]');
					$configL[]='\'internal_type\' => \'db\',	'.$this->WOPcomment('WOP:'.$WOP.'[conf_rel_type]');
				} else {
					$configL[]='\'type\' => \'select\',	'.$this->WOPcomment('WOP:'.$WOP.'[conf_rel_type]');
				}

				if ($fConf["conf_rel_type"]!="group" && $fConf["conf_relations"]==1 && $fConf["conf_rel_dummyitem"])	{
					$configL[]=trim($this->wrapBody('
						'.$this->WOPcomment('WOP:'.$WOP.'[conf_rel_dummyitem]').'
						\'items\' => array (
							','array(\'\',0),','
						),
					'));
				}

				if (t3lib_div::inList("tt_content,fe_users,fe_groups",$fConf["conf_rel_table"]))		$this->wizard->EM_CONF_presets["dependencies"][]="cms";

				if ($fConf["conf_rel_table"]=="_CUSTOM")	{
					$fConf["conf_rel_table"]=$fConf["conf_custom_table_name"]?$fConf["conf_custom_table_name"]:"NO_TABLE_NAME_AVAILABLE";
				}

				if ($fConf["conf_rel_type"]=="group")	{
					$configL[]='\'allowed\' => \''.($fConf["conf_rel_table"]!="_ALL"?$fConf["conf_rel_table"]:"*").'\',	'.$this->WOPcomment('WOP:'.$WOP.'[conf_rel_table]');
					if ($fConf["conf_rel_table"]=="_ALL")	$configL[]='\'prepend_tname\' => 1,	'.$this->WOPcomment('WOP:'.$WOP.'[conf_rel_table]=_ALL');
				} else {
					switch($fConf["conf_rel_type"])	{
						case "select_cur":
							$where="AND ".$fConf["conf_rel_table"].".pid=###CURRENT_PID### ";
						break;
						case "select_root":
							$where="AND ".$fConf["conf_rel_table"].".pid=###SITEROOT### ";
						break;
						case "select_storage":
							$where="AND ".$fConf["conf_rel_table"].".pid=###STORAGE_PID### ";
						break;
						default:
							$where="";
						break;
					}
					$configL[]='\'foreign_table\' => \''.$fConf["conf_rel_table"].'\',	'.$this->WOPcomment('WOP:'.$WOP.'[conf_rel_table]');
					$configL[]='\'foreign_table_where\' => \''.$where.'ORDER BY '.$fConf["conf_rel_table"].'.uid\',	'.$this->WOPcomment('WOP:'.$WOP.'[conf_rel_type]');
				}
				$configL[]='\'size\' => '.t3lib_div::intInRange($fConf["conf_relations_selsize"],1,100).',	'.$this->WOPcomment('WOP:'.$WOP.'[conf_relations_selsize]');
				$configL[]='\'minitems\' => 0,';
				$configL[]='\'maxitems\' => '.t3lib_div::intInRange($fConf["conf_relations"],1,100).',	'.$this->WOPcomment('WOP:'.$WOP.'[conf_relations]');

				if ($fConf["conf_relations_mm"])	{
					$mmTableName=$id."_mm";
					$configL[]='"MM" => "'.$mmTableName.'",	'.$this->WOPcomment('WOP:'.$WOP.'[conf_relations_mm]');
					$DBfields[] = $fConf["fieldname"].' int(11) DEFAULT \'0\' NOT NULL,';

					$createTable = $this->sPS("
						#
						# Table structure for table '".$mmTableName."'
						# ".$this->WOPcomment('WOP:'.$WOP.'[conf_relations_mm]')."
						#
						CREATE TABLE ".$mmTableName." (
						  uid_local int(11) DEFAULT '0' NOT NULL,
						  uid_foreign int(11) DEFAULT '0' NOT NULL,
						  tablenames varchar(30) DEFAULT '' NOT NULL,
						  sorting int(11) DEFAULT '0' NOT NULL,
						  KEY uid_local (uid_local),
						  KEY uid_foreign (uid_foreign)
						);
					");
					$this->wizard->ext_tables_sql[]=chr(10).$createTable.chr(10);
				} elseif (t3lib_div::intInRange($fConf["conf_relations"],1,100)>1 || $fConf["conf_rel_type"]=="group") {
					$DBfields[] = $fConf["fieldname"]." text,";
				} else {
					$DBfields[] = $fConf["fieldname"].' int(11) DEFAULT \'0\' NOT NULL,';
				}

//--------------------------
// Begin - Modified 
//--------------------------		
        if ($this->wizard->wizArray['savext'][1]['generateForm'] && $fConf['conf_rel_type']=='group' && $fConf['conf_norelation']) {
  				unset($DBfields[count($DBfields)-1]); 	
          $configL[] = '"norelation" => 1,'; 
        }
//--------------------------
// End - Modified 
//--------------------------	

				if ($fConf["conf_rel_type"]!="group")	{
					$wTable=$fConf["conf_rel_table"];
					$wizards =array();
					if ($fConf["conf_wiz_addrec"])	{
						$wizards[] = trim($this->sPS('
							'.$this->WOPcomment('WOP:'.$WOP.'[conf_wiz_addrec]').'
							\'add\' => array(
								\'type\'   => \'script\',
								\'title\'  => \'Create new record\',
								\'icon\'   => \'add.gif\',
								\'params\' => array(
									\'table\'    => \''.$wTable.'\',
									\'pid\'      => \'###CURRENT_PID###\',
									\'setValue\' => \'prepend\'
								),
								\'script\' => \'wizard_add.php\',
							),
						'));
					}
					if ($fConf["conf_wiz_listrec"])	{
						$wizards[] = trim($this->sPS('
							'.$this->WOPcomment('WOP:'.$WOP.'[conf_wiz_listrec]').'
							\'list\' => array(
								\'type\'   => \'script\',
								\'title\'  => \'List\',
								\'icon\'   => \'list.gif\',
								\'params\' => array(
									\'table\' => \''.$wTable.'\',
									\'pid\'   => \'###CURRENT_PID###\',
								),
								\'script\' => \'wizard_list.php\',
							),
						'));
					}
					if ($fConf["conf_wiz_editrec"])	{
						$wizards[] = trim($this->sPS('
							'.$this->WOPcomment('WOP:'.$WOP.'[conf_wiz_editrec]').'
							\'edit\' => array(
								\'type\'                     => \'popup\',
								\'title\'                    => \'Edit\',
								\'script\'                   => \'wizard_edit.php\',
								\'popup_onlyOpenIfSelected\' => 1,
								\'icon\'                     => \'edit2.gif\',
								\'JSopenParams\'             => \'height=350,width=580,status=0,menubar=0,scrollbars=1\',
							),
						'));
					}
					if (count($wizards))	{
						$configL[] = trim($this->wrapBody('
							\'wizards\' => array(
								\'_PADDING\'  => 2,
								\'_VERTICAL\' => 1,
								',implode(chr(10),$wizards),'
							),
						'));
					}
				}
			break;
			case "files":
				$configL[] = '\'type\' => \'group\',';
				$configL[] = '\'internal_type\' => \'file\',';
				switch($fConf["conf_files_type"])	{
					case "images":
						$configL[]='\'allowed\' => $GLOBALS[\'TYPO3_CONF_VARS\'][\'GFX\'][\'imagefile_ext\'],	'.$this->WOPcomment('WOP:'.$WOP.'[conf_files_type]');
					break;
					case "webimages":
						$configL[]='\'allowed\' => \'gif,png,jpeg,jpg\',	'.$this->WOPcomment('WOP:'.$WOP.'[conf_files_type]'); // TODO use web images definition from install tool
					break;
					case "all":
						$configL[] = '\'allowed\' => \'\',	'.$this->WOPcomment('WOP:'.$WOP.'[conf_files_type]');
						$configL[] = '\'disallowed\' => \'php,php3\',	'.$this->WOPcomment('WOP:'.$WOP.'[conf_files_type]');
					break;
				}
				$configL[] = '\'max_size\' => $GLOBALS[\'TYPO3_CONF_VARS\'][\'BE\'][\'maxFileSize\'],	'.$this->WOPcomment('WOP:'.$WOP.'[conf_max_filesize]');

				$this->wizard->EM_CONF_presets["uploadfolder"]=1;

				$ulFolder  = 'uploads/tx_'.str_replace("_","",$extKey);
				$configL[] = '\'uploadfolder\' => \''.$ulFolder.'\',';
				if($fConf['conf_files_thumbs']) {
					$configL[] = '\'show_thumbs\' => 1,	'.$this->WOPcomment('WOP:'.$WOP.'[conf_files_thumbs]');
				}

				$configL[] = '\'size\' => '.t3lib_div::intInRange($fConf["conf_files_selsize"],1,100).',	'.$this->WOPcomment('WOP:'.$WOP.'[conf_files_selsize]');
				$configL[] = '\'minitems\' => 0,';
				$configL[] = '\'maxitems\' => '.t3lib_div::intInRange($fConf["conf_files"],1,100).',	'.$this->WOPcomment('WOP:'.$WOP.'[conf_files]');

				$DBfields[] = $fConf["fieldname"]." text,";
			break;
			case 'flex':
				$DBfields[] = $fConf['fieldname'] . ' mediumtext,';
				$configL[]  = trim($this->sPS('
					\'type\' => \'flex\',
		\'ds\' => array (
			\'default\' => \'FILE:EXT:'.$extKey.'/flexform_'.$table.'_'.$fConf['fieldname'].'.xml\',
		),
				'));
				$this->addFileToFileArray(
        			'flexform_'.$table.'_'.$fConf['fieldname'].'.xml',
        			$this->createFlexForm()
        		);
			break;
			case "none":
				$DBfields[] = $fConf["fieldname"]." tinytext,";
				$configL[]=trim($this->sPS('
					\'type\' => \'none\',
				'));
			break;
			case "passthrough":
				$DBfields[] = $fConf["fieldname"]." tinytext,";
				$configL[]=trim($this->sPS('
					\'type\' => \'passthrough\',
				'));
			break;
			case 'inline':
	#$DBfields=$this->getInlineDBfields($fConf);
				if ($DBfields) {
					$DBfields=array_merge($DBfields, $this->getInlineDBfields($table, $fConf));
				}
				$configL = $this->getInlineTCAconfig($table, $fConf);
			break;
//--------------------------
// Begin - Modified 
//--------------------------			
			case "ShowOnly":
			  $this->getSplitLabels_reference($fConf,"title",$table.".".str_replace($this->returnName($extKey,'class','').'_', '', $fConf["fieldname"]));
			  return;
        break;
//--------------------------
// End - Modified 
//--------------------------	
			default:
				debug("Unknown type: ".(string)$fConf["type"]);
			break;
		}

		if ($t=="passthrough")	{
			$columns[$fConf["fieldname"]] = trim($this->wrapBody('
				\''.$fConf["fieldname"].'\' => array (		'.$this->WOPcomment('WOP:'.$WOP.'[fieldname]').'
					\'config\' => array (
						',implode(chr(10),$configL),'
					)
				),
			',2));
		} else {
			$columns[$fConf["fieldname"]] = trim($this->wrapBody('
				\''.$fConf["fieldname"].'\' => array (		'.$this->WOPcomment('WOP:'.$WOP.'[fieldname]').'
					\'exclude\' => '.($fConf["excludeField"]?1:0).',		'.$this->WOPcomment('WOP:'.$WOP.'[excludeField]').'
					\'label\' => \''.addslashes($this->getSplitLabels_reference($fConf,"title",$table.".".$fConf["fieldname"])).'\',		'.$this->WOPcomment('WOP:'.$WOP.'[title]').'
					\'config\' => array (
						',implode(chr(10),$configL),'
					)
				),
			',2));
		}
	}

	
	function renderFieldOverview($prefix,$fConf,$dontRemove=0,$style='')	{
    return ux_tx_kickstarter_section_tables::renderFieldOverview($prefix,$fConf,$dontRemove,$style);
  }	
	
	function renderTextareaBox($prefix,$value,$width=600,$rows=10)	{
		$onCP = $this->getOnChangeParts($prefix);
		return $this->wopText($prefix).$onCP[0].'<textarea name="'.$this->piFieldName('wizArray_upd').$prefix.'" style="width:'.$width.'px;" rows="'.$rows.'" wrap="OFF" onChange="'.$onCP[1].'" title="'.htmlspecialchars("WOP:".$prefix).'"'.$this->wop($prefix).'>'.t3lib_div::formatForTextarea($value).'</textarea>';
	}

	
	/**
	 * Cleaning up fieldname from invalid characters (only alphanum is allowed)
	 * and chechking for reserved words
	 *
	 * @param	string		$str: orginal fieldname
	 * @return	cleaned up fieldname
	 */
	function cleanUpFieldName($str)	{
//--------------------------
// Begin - Modified 
//--------------------------			
//		$fieldName = ereg_replace('[^[:alnum:]_]','',strtolower($str));
		$fieldName = ereg_replace('[^[:alnum:]_]','',$str);
//--------------------------
// End - Modified 
//--------------------------	
		if (!$fieldName || in_array($fieldName, $this->wizard->reservedWords) || in_array($fieldName, $this->usedNames))	{
			$fieldName.=($fieldName?'_':'').t3lib_div::shortmd5(microtime());
		}
		$this->usedNames[]=$fieldName;
		return $fieldName;
	}
	
}

?>
