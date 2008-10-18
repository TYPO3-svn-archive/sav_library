<?php
/***************************************************************
*  Copyright notice
*
*  (c) 1999-2004 Kasper Skaarhoj (kasper@typo3.com)
*  (c) 2004-2005 Stanislas Rolland (stanislas.rolland@fructifor.com)
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


class ux_tx_kickstarter_section_tables extends tx_kickstarter_section_tables {

  var $siteBackPath ;
  
	/**
	 * Renders the form in the kickstarter; this was add_cat_tables()
	 *
	 * @return	string		wizard
	 */
	function render_wizard() {
		$lines = array();

		$action = explode(':',$this->wizard->modData['wizAction']);
		if ($action[0] == 'edit')	{
			$this->regNewEntry($this->sectionID,$action[1]);
			$lines    = $this->catHeaderLines($lines,$this->sectionID,$this->wizard->options[$this->sectionID],'&nbsp;',$action[1]);
			$piConf   = $this->wizard->wizArray[$this->sectionID][$action[1]];
			$ffPrefix = '['.$this->sectionID.']['.$action[1].']';

				// Unique table name:
			$table_suffixes = array();
			if (is_array($this->wizard->wizArray[$this->sectionID]))	{
				foreach($this->wizard->wizArray[$this->sectionID] as $kk => $vv)	{
					if (!strcmp($action[1],$kk))	{
						if (count($table_suffixes) && t3lib_div::inList(implode(',',$table_suffixes),$vv['tablename'].'Z'))	{
							$piConf['tablename'] .= $kk;
						}
						break;
					}
					$table_suffixes[] = $vv['tablename'].'Z';
				}
			}


				// Enter title of the table
			$subContent = '<strong>Tablename:</strong><BR>'.
				$this->returnName($this->wizard->extKey,'tables').'_'.$this->renderStringBox($ffPrefix.'[tablename]',$piConf['tablename']).
				'<BR><strong>Notice:</strong> Use characters a-z0-9 only. Only lowercase, no spaces.<BR>
				This becomes the table name in the database. ';
			$lines[] = '<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).'</td></tr>';


				// Enter title of the table
			$subContent = '<strong>Title of the table:</strong><BR>'.
				$this->renderStringBox_lang('title',$ffPrefix,$piConf);
			$lines[] = '<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).'</td></tr>';



				// Fields - overview
			$c = array(0);
			$this->usedNames = array();
			if (is_array($piConf['fields']))	{
				$piConf['fields'] = $this->cleanFieldsAndDoCommands($piConf['fields'],$this->sectionID,$action[1]);

				// Do it for real...
				$lines[] = '<tr'.$this->bgCol(1).'><td><strong> Fields Overview </strong></td></tr>';
//				$lines[] = '<tr'.$this->bgCol(2).'><td>'.$this->fw($v[1]).'</td></tr>';
				$lines[] = '<tr><td></td></tr>';

				$subContent = '<tr '.$this->bgCol(2).'>
					<td><strong>Name</strong></td>
					<td><strong>Title</strong></td>
					<td><strong>Type</strong></td>
					<td><strong>Exclude?</strong></td>
					<td><strong>Details</strong></td>
				</tr>';
				
//--------------------------
// begin - Modified 
//--------------------------						
				$this->siteBackPath = $this->wizard->siteBackPath;
				if ($this->wizard->wizArray['savext'][1]['generateForm']) {

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

          // Reorder the fields if activated
          if ($this->wizard->modData['wizSpecialCmd'] == 'reorderFields') {

            foreach ($piConf['fields'] as $k =>$v) {      
              $this->wizard->wizArray[$this->sectionID][$action[1]]['fields'][$k]['conf_showOrder'][$wizKey] = 	$this->wizard->wizArray[$this->sectionID][$action[1]]['fields'][$k]['conf_showOrder'][$piConf['conf_opt_formViews']];
            }

          $this->wizard->modData['wizSpecialCmd'] = ''; 
          $this->wizard->wizArray[$this->sectionID][$action[1]]['conf_opt_formViews'] = 0;
          $piConf['conf_opt_formViews'] = 0;
          }  

          // Selector for reordering the field 
          if (isset($this->wizard->wizArray['formviews'])) {
            $opt_formViews = array();
            $opt_formViews[0] = '';
            foreach($this->wizard->wizArray['formviews'] as $key => $view) {
              $opt_formViews[$key] = $view['title'];
            }

            $subContent .='<tr><td colspan="5">Reorder the fields as in</a> '.$this->renderSelectBox($ffPrefix.'[conf_opt_formViews]',$piConf['conf_opt_formViews'],$opt_formViews).
                 '&nbsp;&nbsp;<input type="button" onClick="document.kickstarter_wizard[\'kickstarter[wizSpecialCmd]\'].value=\''.'reorderFields'.'\';
                 setFormAnchorPoint(\''.t3lib_div::shortMd5($this->piFieldName("wizArray_upd").$ffPrefix.'[fieldHeader]').'\');
      			     document.kickstarter_wizard.submit();" name="'.$this->piFieldName('reOrder').'" value="Reorder?">'.
                 '&nbsp;&nbsp;<input type="submit" name="'.$this->piFieldName('viewResult').'" value="View result">'.
                 '&nbsp;&nbsp;<input type="submit" name="'.$this->piFieldName('WRITE').'" value="Write?" onclick="
                 return confirm(\'If you are not sure, use the View result button\');               
                 " />'.
                 '</td></tr>';
          }
      			     
				  //  Add the overview
          $subContent .= '<tr><td colspan="5" style="padding-left:0px; padding-right:0px; padding-top:0px; padding-bottom:5px;"><div style="float: left;width: 100%; background: url('.$this->siteBackPath.t3lib_extMgm::siteRelPath('sav_library').'kickstarter/taMenuBorder.gif) repeat-x bottom;"><ul style="margin: 0px;padding: 0px;list-style: none;">';
                  
          if (isset($this->wizard->wizArray['formviews'])) {
            $viewKeys = array();
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
 				  if (isset($viewKeys)) {
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
				
				
				foreach($piConf['fields'] as $k=>$v)	{
					$c[]=$k;
//--------------------------
// begin - Modified 
//--------------------------		
          $style = ( $v['conf_showFieldDisp'][$wizKey] ? $styleSel : '');
					$subContent .= $this->renderFieldOverview($ffPrefix.'[fields]['.$k.']',$v,0,$style);
//					$subContent .= $this->renderFieldOverview($ffPrefix.'[fields]['.$k.']',$v);
//--------------------------
// End - Modified 
//--------------------------					}
				}
				$lines[] = '<tr'.$this->bgCol(3).'><td><table>'.$this->fw($subContent).'</table></td></tr>';
			}

			$lines[] = '<tr'.$this->bgCol(1).'><td><strong> Edit Fields </strong></td></tr>';
//			$lines[] = '<tr'.$this->bgCol(2).'><td>'.$this->fw($v[1]).'</td></tr>';
			$lines[] = '<tr><td></td></tr>';




				// Admin only
			$subContent  = '';
			$subContent .= $this->renderCheckBox($ffPrefix.'[add_deleted]',$piConf['add_deleted'],1).'Add "Deleted" field '.$this->whatIsThis('Whole system: If a table has a deleted column, records are never really deleted, just "marked deleted" . Thus deleted records can actually be restored by clearing a deleted-flag later.\nNotice that all attached files are also not deleted from the server, so if you expect the table to hold some heavy size uploads, maybe you should not set this...') . '<BR>';
			$subContent .= $this->renderCheckBox($ffPrefix . '[add_hidden]', $piConf['add_hidden'],1) . 'Add "Hidden" flag ' . $this->whatIsThis('Frontend: The "Hidden" flag will prevent the record from being displayed on the frontend.') . '<BR>' . $this->resImg('t_flag_hidden.png','hspace=20','','<BR><BR>');
			$subContent .= $this->renderCheckBox($ffPrefix . '[add_starttime]', $piConf['add_starttime']) . 'Add "Starttime" ' . $this->whatIsThis('Frontend: If a "Starttime" is set, the record will not be visible on the website, before that date arrives.') . '<BR>' . $this->resImg('t_flag_starttime.png','hspace=20','','<BR><BR>');
			$subContent .= $this->renderCheckBox($ffPrefix . '[add_endtime]', $piConf['add_endtime']) . 'Add "Endtime" ' . $this->whatIsThis('Frontend: If a "Endtime" is set, the record will be hidden from that date and into the future.') . '<BR>' . $this->resImg('t_flag_endtime.png', 'hspace=20','','<BR><BR>');
			$subContent .= $this->renderCheckBox($ffPrefix . '[add_access]', $piConf['add_access']) . 'Add "Access group" ' . $this->whatIsThis('Frontend: If a frontend user group is set for a record, only frontend users that are members of that group will be able to see the record.') . '<BR>' . $this->resImg('t_flag_access.png', 'hspace=20','','<BR><BR>');
			$lines[]     = '<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).'</td></tr>';

				// Sorting
			$optValues = array(
				'crdate'    => '[crdate]',
				'cruser_id' => '[cruser_id]',
				'tstamp'    => '[tstamp]',
			);
			$subContent  = '';
			$subContent .= $this->renderCheckBox($ffPrefix.'[localization]',$piConf['localization']).'Enabled localization features'.$this->whatIsThis('If set, the records will have a selector box for language and a reference field which can point back to the original default translation for the record. These features are part of the internal framework for localization.').'<BR>';
			$subContent .= $this->renderCheckBox($ffPrefix.'[versioning]',$piConf['versioning']).'Enable versioning '.$this->whatIsThis('If set, you will be able to versionize records from this table. Highly recommended if the records are passed around in a workflow.').'<BR>';
			$subContent .= $this->renderCheckBox($ffPrefix.'[sorting]',$piConf['sorting']).'Manual ordering of records '.$this->whatIsThis('If set, the records can be moved up and down relative to each other in the backend. Just like Content Elements. Otherwise they are sorted automatically by any field you specify').'<BR>';
			$subContent .= $this->textSetup('','If "Manual ordering" is not set, order the table by this field:<BR>'.
				$this->renderSelectBox($ffPrefix.'[sorting_field]',$piConf['sorting_field'],$this->currentFields($optValues,$piConf['fields'])).'<BR>'.
				$this->renderCheckBox($ffPrefix.'[sorting_desc]',$piConf['sorting_desc']).' Descending');
			$lines[] = '<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).'</td></tr>';

				// Type field
			$optValues = array(
				'0' => '[none]',
			);
			$subContent = '<strong>"Type-field", if any:<BR></strong>'.
					$this->renderSelectBox($ffPrefix.'[type_field]',$piConf['type_field'],$this->currentFields($optValues,$piConf['fields'])).
					$this->whatIsThis('A "type-field" is the field in the table which determines how the form is rendered in the backend, eg. which fields are shown under which circumstances.\nFor instance the Content Element table "tt_content" has a type-field, CType. The value of this field determines if the editing form shows the bodytext field as is the case when the type is "Text" or if also the image-field should be shown as when the type is "Text w/Image"');
			$lines[] = '<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).'</td></tr>';

				// Header field
			$optValues = array(
				'0' => '[none]',
			);
			$subContent = '<strong>Label-field:<BR></strong>'.
					$this->renderSelectBox($ffPrefix.'[header_field]',$piConf['header_field'],$this->currentFields($optValues,$piConf['fields'])).
					$this->whatIsThis('A "label-field" is the field used as record title in the backend.');
			$lines[] = '<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).'</td></tr>';

				// Icon
			$optValues = array(
				'default.gif'        => 'Default (white)',
				'default_black.gif'  => 'Black',
				'default_gray4.gif'  => 'Gray',
				'default_blue.gif'   => 'Blue',
				'default_green.gif'  => 'Green',
				'default_red.gif'    => 'Red',
				'default_yellow.gif' => 'Yellow',
				'default_purple.gif' => 'Purple',
			);

			$subContent = $this->renderSelectBox($ffPrefix.'[defIcon]',$piConf['defIcon'],$optValues).' Default icon '.$this->whatIsThis('All tables have at least one associated icon. Select which default icon you wish. You can always substitute the file with another.');
			$lines[] = '<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).'</td></tr>';

				// Allowed on pages
			$subContent = '<strong>Allowed on pages:<BR></strong>'.
					$this->renderCheckBox($ffPrefix.'[allow_on_pages]',$piConf['allow_on_pages']).' Allow records from this table to be created on regular pages.';
			$lines[] = '<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).'</td></tr>';

				// Allowed in "Insert Records"
			$subContent = '<strong>Allowed in "Insert Records" field in content elements:<BR></strong>'.
					$this->renderCheckBox($ffPrefix.'[allow_ce_insert_records]',$piConf['allow_ce_insert_records']).' Allow records from this table to be linked to by content elements.';
			$lines[] = '<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).'</td></tr>';

				// Add new button
			$subContent = '<strong>Add "Save and new" button in forms:<BR></strong>'.
					$this->renderCheckBox($ffPrefix.'[save_and_new]',$piConf['save_and_new']).' Will add an additional save-button to forms by which you can save the item and instantly create the next.';
			$lines[] = '<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).'</td></tr>';


			$subContent = '<strong>Notice on fieldnames:<BR></strong>'.
				'Don\'t use fieldnames from this list of reserved names/words: <BR>
				<blockquote><em>' . implode(', ', $this->wizard->reservedWords).'</em></blockquote>';
			$lines[] = '<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).'</td></tr>';



				// PRESETS:
			$selPresetBox = $this->presetBox($piConf["fields"]);

				// Fields
			$c = array(0);
			$this->usedNames = array();
			if (is_array($piConf['fields']))	{

				// Do it for real...				
//--------------------------
// Begin - Modified 
//--------------------------			
				
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

					$lines[] = '<tr'.$this->bgCol(2).'><td>'.$this->fw('<a name="'.$md5h.'"></a><strong>FIELD:</strong> <em>'.$v['fieldname'].'</em>').
				  '&nbsp;&nbsp;&nbsp;&nbsp;<input type="image" hspace=2 src="'.$this->siteBackPath.TYPO3_mainDir.'gfx/move_record.gif" name="'.$this->varPrefix.'_CMD_'.$fConf['fieldname'].'_MOVE" onClick="'.$onCP[1].'" title="Move this field after">'.
          '&nbsp;&nbsp;'.$this->renderSelectBox($prefix."[conf_moveAfter]",'',$optValues).
				  '&nbsp;&nbsp;'.'<a href="'.$this->linkThisCmd().'#">'.
          '<input type="image" hspace=2 width="10" height="10" src="'.$this->siteBackPath.TYPO3_mainDir.'gfx/redup.gif" name="'.$this->varPrefix.'_CMD_'.$fConf['fieldname'].'_MOVE_OVERVIEW" onClick="'.$upCP[1].'" title="Move to the overview">'.          
          '</a></td></tr>';
					$lines[] = '<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).'</td></tr>';
				}
/* Original code
				foreach($piConf['fields'] as $k => $v)	{
					$c[] = $k;
					$subContent = $this->renderField($ffPrefix.'[fields]['.$k.']',$v);
					$lines[] = '<tr'.$this->bgCol(2).'><td>'.$this->fw('<strong>FIELD:</strong> <em>'.$v['fieldname'].'</em>').'</td></tr>';
					$lines[] = '<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).'</td></tr>';
				}
*/
//--------------------------
// End - Modified 
//--------------------------			
      }
      
				// New field:
			$k = max($c)+1;
			$v = array();
			$lines[] = '<tr'.$this->bgCol(2).'><td>'.$this->fw('<strong>NEW FIELD:</strong>').'</td></tr>';
			$subContent = $this->renderField($ffPrefix.'[fields]['.$k.']',$v,1);
			$lines[] = '<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).'</td></tr>';


			$lines[] = '<tr'.$this->bgCol(3).'><td>'.$this->fw('<BR><BR>Load preset fields: <BR>'.$selPresetBox).'</td></tr>';
		}

		/* HOOK: Place a hook here, so additional output can be integrated */
		if(is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kickstarter']['add_cat_tables'])) {
		  foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kickstarter']['add_cat_tables'] as $_funcRef) {
		    $lines = t3lib_div::callUserFunction($_funcRef, $lines, $this);
		  }
		}

		$content = '<table border=0 cellpadding=2 cellspacing=2>'.implode('',$lines).'</table>';

		return $content;
	}
  

	/**
	 * renders field overview
	 * 
	 * @author	Luite van Zelst <luite@aegee.org>
	 * @param	string		prefix (unused?)
	 * @param	array		field configuration
	 * @param	boolean		dontRemove (unused?)
	 * @return	string		table row with field data 
	 */
//	function renderFieldOverview($prefix, $fConf, $dontRemove=0)	{
	function renderFieldOverview($prefix, $fConf, $dontRemove=0, $style='')	{
			// Sorting
		$optTypes = array(
			''                => '',
			'input'           => 'String input',
			'input+'          => 'String input, advanced',
			'textarea'        => 'Text area',
			'textarea_rte'    => 'Text area with RTE',
			'textarea_nowrap' => 'Text area, No wrapping',
			'check'           => 'Checkbox, single',
			'check_4'         => 'Checkbox, 4 boxes in a row',
			'check_10'        => 'Checkbox, 10 boxes in two rows (max)',
			'link'            => 'Link',
			'date'            => 'Date',
			'datetime'        => 'Date and time',
			'integer'         => 'Integer, 10-1000',
			'select'          => 'Selectorbox',
			'radio'           => 'Radio buttons',
			'rel'             => 'Database relation',
			'files'           => 'Files',
//--------------------------
// Begin - Modified 
//--------------------------			
      'ShowOnly'        => '<b>Only shown in SAV form</b>',
//--------------------------
// End - Modified 
//--------------------------				
		);
		$optEval = array(
			''         => '',
			'date'     => 'Date (day-month-year)',
			'time'     => 'Time (hours, minutes)',
			'timesec'  => 'Time + seconds',
			'datetime' => 'Date + Time',
			'year'     => 'Year',
			'int'      => 'Integer',
			'int+'     => 'Integer 0-1000',
			'double2'  => 'Floating point, x.xx',
			'alphanum' => 'Alphanumeric only',
			'upper'    => 'Upper case',
			'lower'    => 'Lower case',
		);
		$optRte = array(
			'tt_content' => 'Transform like "Bodytext"',
			'basic'      => 'Typical (based on CSS)',
			'moderate'   => 'Transform images / links',
			'none'       => 'No transform',
			'custom'     => 'Custom transform'
		);

		switch($fConf['type']) {
			case 'rel':
				if ($fConf['conf_rel_table'] == '_CUSTOM') {
					$details .= $fConf['conf_custom_table_name'];
				} else {
					$details .= $fConf['conf_rel_table'];
				}
			break;
			case 'input+':
				if($fConf['conf_varchar']) $details[] = 'varchar';
				if($fConf['conf_unique']) $details[] = ($fConf['conf_unique'] == 'L') ?  'unique (page)': 'unique (site)';
				if($fConf['conf_eval']) $details[] = $optEval[$fConf['conf_eval']];
				$details = implode(', ', (array) $details);
			break;
			case 'check_10':
			case 'check_4':
				$details = ($fConf['conf_numberBoxes'] ? $fConf['conf_numberBoxes'] : '4') . ' checkboxes';
			break;
			case 'radio':
				if($fConf['conf_select_items']) $details = $fConf['conf_select_items'] . ' options';
			break;
			case 'select':
				if($fConf['conf_select_items']) $details[] = $fConf['conf_select_items'] . ' options';
				if($fConf['conf_select_pro']) $details[] = 'preprocessing';
				$details = implode(', ', (array) $details);
			break;
			case 'textarea_rte':
				if($fConf['conf_rte']) $details = $optRte[$fConf['conf_rte']];
			break;
			case 'files':
				$details[] = $fConf['conf_files_type'];
				$details[] = $fConf['conf_files'] . ' files';
				$details[] = $fConf['conf_max_filesize'] . ' kB';
				$details = implode(', ', (array) $details);
			break;
		}
//--------------------------
// Begin - Modified 
//--------------------------	
	
		return sprintf('<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td>',
			'<a style="'.$style.'" href="'.$this->linkThisCmd().'#'.t3lib_div::shortMd5($this->piFieldName("wizArray_upd").$prefix.'[fieldHeader]').'">'.$fConf['fieldname'].'</a>',
			$fConf['title'],
			$optTypes[$fConf['type']],
			$fConf['exludeField'] ? 'Yes' : '',
			$details
			);
/* Orginal code
		return sprintf('<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td>',
			$fConf['fieldname'],
			$fConf['title'],
			$optTypes[$fConf['type']],
			$fConf['exludeField'] ? 'Yes' : '',
			$details
			);
*/
//--------------------------
// end - Modified 
//--------------------------			
	}

  function helpIcon($field){
    return ux_tx_kickstarter_section_fields::helpIcon($field);
  }
		
	
	function makeFieldTCA(&$DBfields,&$columns,$fConf,$WOP,$table,$extKey)	{
    return ux_tx_kickstarter_section_fields::makeFieldTCA($DBfields,$columns,$fConf,$WOP,$table,$extKey);
  }	

	function cleanFieldsAndDoCommands($fConf,$catID,$action)	{
    return ux_tx_kickstarter_section_fields::cleanFieldsAndDoCommands($fConf,$catID,$action);
  }	

	function renderField($prefix,$fConf,$dontRemove=0)	{
    return ux_tx_kickstarter_section_fields::renderField($prefix,$fConf,$dontRemove);
  }	
  
	function renderTextareaBox($prefix,$value,$width=600,$rows=10)	{
		$onCP = $this->getOnChangeParts($prefix);
		return $this->wopText($prefix).$onCP[0].'<textarea name="'.$this->piFieldName('wizArray_upd').$prefix.'" style="width:'.$width.'px;" rows="'.$rows.'" wrap="OFF" onChange="'.$onCP[1].'" title="'.htmlspecialchars("WOP:".$prefix).'"'.$this->wop($prefix).'>'.t3lib_div::formatForTextarea($value).'</textarea>';
	}
  
}

?>
