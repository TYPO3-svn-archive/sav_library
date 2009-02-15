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
 
// Needed to have the XCLASS taken into account
require_once(t3lib_extMgm::extPath('kickstarter') . 'sections/class.tx_kickstarter_section_tables.php');
require_once(t3lib_extMgm::extPath('kickstarter') . 'sections/class.tx_kickstarter_section_pi.php');

class ux_tx_kickstarter_wizard extends tx_kickstarter_wizard {


	/**
	 * Side menu
	 *
	 * @return	HTML code of the side menu
	 */
	function sidemenu()	{
//--------------------------
// Begin - Modified
//--------------------------
  // Get the extension version
  if ($this->saveKey && file_exists(t3lib_extMgm::extPath($this->saveKey) . 'ext_emconf.php')) {
    $_EXTKEY = $this->saveKey;
    require(t3lib_extMgm::extPath($this->saveKey) . 'ext_emconf.php');
    $this->wizArray['savext'][1]['version'] = $EM_CONF[$_EXTKEY]['version'];
  }
//--------------------------
// End - Modified
//--------------------------
		$actionType = $this->modData['wizSubCmd'].':'.$this->modData['wizAction'];
		$singles    = $this->getSingles();
		$lines      = array();
		foreach($this->options as $k => $v)	{
			// Add items:
			$items = $this->wizArray[$k];
			$c = 0;
			$iLines = array();
			if (is_array($items))	{
				foreach($items as $k2=>$conf)	{
//--------------------------
// Begin - Modified 
//--------------------------			
          $style = $this->sections[$k]['styles']['defaultValue'];
          if (isset($this->sections[$k]['styles'])) {
            $style = $this->sections[$k]['styles']['value'][$conf[$this->sections[$k]['styles']['field']]];
          }      

          // Get the title
          $title = '[Click to Edit]';
          if (isset($this->sections[$k]['singleTitles'])) {
            $title = $this->sections[$k]['singleTitles']['defaultValue'];
          }
          if (isset($this->sections[$k]['singleTitles']['value']) && isset($conf[$this->sections[$k]['singleTitles']['field']])) {          
            $title = $this->sections[$k]['singleTitles']['value'][$conf[$this->sections[$k]['singleTitles']['field']]]['label'];
            if (isset($this->sections[$k]['singleTitles']['value'][$conf[$this->sections[$k]['singleTitles']['field']]]['addField'])) {
              $title .= $this->sections[$k]['singleTitles']['value'][$conf[$this->sections[$k]['singleTitles']['field']]]['addLabel'].$conf[$this->sections[$k]['singleTitles']['value'][$conf[$this->sections[$k]['singleTitles']['field']]]['addField']];
            }
          }  
//					$dummyTitle = t3lib_div::inList($singles,$k)  ? '[Click to Edit]' : '<em>Item '.$k2.'</em>';
					$dummyTitle = (t3lib_div::inList($singles,$k) || $this->sections[$k]['single'])  ? $title : '<em>Item '.$k2.'</em>';
//--------------------------
// End - Modified 
//--------------------------							
					$isActive   = !strcmp($k.':edit:'.$k2, $actionType);
					$delIcon    = $this->linkStr('<img src="'.$this->siteBackPath.TYPO3_mainDir.'gfx/garbage.gif" width="11" height="12" border="0" title="Remove item" />','','deleteEl:'.$k.':'.$k2);
//--------------------------
// Begin - Modified 
//--------------------------			
//					$iLines[]   = '<tr'.($isActive?$this->bgCol(2,-30):$this->bgCol(2)).'><td>'.$this->fw($this->linkStr($this->bwWithFlag($conf['title']?$conf['title']:$dummyTitle,$isActive),$k,'edit:'.$k2)).'</td><td>'.$delIcon.'</td></tr>';
  				$iLines[]   = '<tr'.($isActive?$this->bgCol(2,-30):$this->bgCol(2)).'><td>&nbsp;'.$this->fw($this->linkStr($this->bwWithFlag($conf['title']?$conf['title']:$dummyTitle,$isActive,$style),$k,'edit:'.$k2)).'</td><td>'.$delIcon.'</td></tr>';
//--------------------------
// End - Modified 
//--------------------------							
					$c=$k2;
				}
			}
//--------------------------
// Begin - Modified 
//--------------------------			
//			if (!t3lib_div::inList($singles,$k) || !count($iLines))	{
			if (!(t3lib_div::inList($singles,$k) || $this->sections[$k]['single']) || !count($iLines))	{
//--------------------------
// End - Modified 
//--------------------------							
				$c++;
				$addIcon = $this->linkStr('<img src="'.$this->siteBackPath.TYPO3_mainDir.'gfx/add.gif" width="12" height="12" border="0" title="Add item" />',$k,'edit:'.$c);
			} else {$addIcon = '';}

			$lines[]='<tr'.$this->bgCol(1).'><td nowrap="nowrap"><strong>'.$this->fw($v[0]).'</strong></td><td>'.$addIcon.'</td></tr>';
			$lines = array_merge($lines,$iLines);
		}

		$lines[]='<tr><td>&nbsp;</td><td></td></tr>';

		$lines[]='<tr><td width="150">
		'.$this->fw('Enter extension key:').'<br />
		<input type="text" name="'.$this->piFieldName('wizArray_upd').'[save][extension_key]" value="'.$this->wizArray['save']['extension_key'].'" maxlength="30" />
		'.($this->wizArray['save']['extension_key']?'':'<br /><a href="http://typo3.org/1382.0.html" target="_blank"><font color="red">Make sure to enter the right extension key from the beginning here!</font> You can register one here.</a>').'
		</td><td></td></tr>';

		$lines[]='<tr><td><input type="submit" value="Update..." /></td><td></td></tr>';
		$lines[]='<tr><td><input type="submit" name="'.$this->piFieldName('totalForm').'" value="Total form" /></td><td></td></tr>';

		if ($this->saveKey)	{
			$lines[]='<tr><td><input type="submit" name="'.$this->piFieldName('viewResult').'" value="View result" /></td><td></td></tr>';
			$lines[]='<tr><td><input type="submit" name="'.$this->piFieldName('downloadAsFile').'" value="D/L as file" /></td><td></td></tr>';
			$lines[]='<tr><td>
			<input type="hidden" name="'.$this->piFieldName('wizArray_upd').'[save][print_wop_comments]" value="0" /><input type="checkbox" name="'.$this->piFieldName('wizArray_upd').'[save][print_wop_comments]" value="1" '.($this->wizArray['save']['print_wop_comments']?' checked="checked"':'').' />'.$this->fw('Print WOP comments').'
			</td><td></td></tr>';
		}

		/* HOOK: Place a hook here, so additional output can be integrated */
		if(is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kickstarter']['sidemenu'])) {
			foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kickstarter']['sidemenu'] as $_funcRef) {
				$lines = t3lib_div::callUserFunction($_funcRef, $lines, $this);
			}
		}

		$content = '<table border="0" cellpadding="2" cellspacing="2">'.implode('',$lines).'</table>';
		return $content;
	}


	/**
	 * Creates all files that are necessary for an extension
	 * (defined in class.tx_kickstarter_compilefiles.php
	 *  This method provides specific processing before and after
	 *  calling the original method)
	 *
	 * 	- ext_localconf.php
	 * 	- ext_tables.php
	 * 	- tca.php
	 * 	- ext_tables.sql
	 * 	- locallang.xml
	 * 	- locallang_db.xml
	 * 	- doc/wizard_form.html
	 * 	- doc/wizard_form.dat
	 * 	- ChangeLog
	 * 	- README.txt
	 * 	- ext_icon.gif
	 *
	 * @param	string		$extKey: the extension key
	 * @return	void
	 */
	function makeFilesArray($extKey) {

//--------------------------
// Begin - Modified 
//--------------------------
					
		// Update the version
		if(is_array($this->wizArray['savext'])) {
  		$key = key($this->wizArray['savext']);
  		$val = current($this->wizArray['savext']);
  		$temp = explode('.', $val['version']);
  		if ($this->modData['version']['x']) {
  		  $temp[0]++;
  		  $temp[1] = 0;
  		  $temp[2] = 0;
      }	
  		if ($this->modData['version']['y']) {
  		  $temp[1]++;
  		  $temp[2] = 0;
      }	
  		if ($this->modData['version']['z']) {
  		  $temp[2]++;
      }	
      $this->wizArray['savext'][$key]['version'] = implode('.', $temp);
      
      // Updates debug
      $this->wizArray['savext'][$key]['debug'] = 0;
      if (is_array($this->modData['debug'])) {
        foreach ($this->modData['debug'] as $keyDebug => $valueDebug) {
          $this->wizArray['savext'][$key]['debug'] += $keyDebug;
        }
      }

      // Updates XML configuration
      if (isset($this->modData['xmlConfiguration'])) {
        $this->wizArray['savext'][$key]['xmlConfiguration'] = $this->modData['xmlConfiguration'];
      }
      // Updates maintenance
      if (isset($this->modData['maintenance'])) {
        $this->wizArray['savext'][$key]['maintenance'] = $this->modData['maintenance'];      
      }

      // Create a new extension from this one
      if ($this->modData['WRITE']=='WRITE') {
        $this->wizArray['savext'][$key]['newExtensionWrite'] = 1;      
      } else {
        unset($this->wizArray['savext'][$key]['newExtensionWrite']);
      }
      if ($this->modData['newExtension']) {
        $this->wizArray['savext'][$key]['newExtension'] = $this->modData['newExtension'];        
        // Check if the new extension folder exists
        if (!file_exists(t3lib_extMgm::extPath('sav_library').'../'.$this->modData['newExtension'])) {
          if (!$this->wizArray['savext'][$key]['oldExtension']) {      
            $this->wizArray['savext'][$key]['oldExtension'] = $this->wizArray['save']['extension_key']; 
          }              
          $this->wizArray['save']['overwrite_files']['pi1/locallang.xml'] = '1';
        } else {
          $this->wizArray['savext'][$key]['newExtensionExists'] = 1;  
          if ($this->modData['newExtensionOverwrite'][1]) {
            if(!$this->wizArray['savext'][$key]['oldExtension']) {     
              $this->wizArray['savext'][$key]['oldExtension'] = $this->wizArray['save']['extension_key'];              
            }
          }     
        }
      } elseif(!file_exists(t3lib_extMgm::extPath('sav_library').'../'.$this->wizArray['save']['extension_key'].'/pi1/locallang.xml')) {
        $this->wizArray['save']['overwrite_files']['pi1/locallang.xml'] = '1';    
      } else {
        $this->wizArray['save']['overwrite_files']['pi1/locallang.xml'] = '0';
      }  
      
      // Make sure that the pi section is the last one because of the processings perform in this section
      $x = $this->wizArray['pi'];
      unset($this->wizArray['pi']);
      $this->wizArray['pi'] = $x;         
    }

    // update the wizArray save information
    if (isset($this->wizArray['save']['overwrite_files'])) {
  		foreach($this->wizArray['save']['overwrite_files'] as $key => $val) {
        if (is_numeric($key)) {
          unset($this->wizArray['save']['overwrite_files'][$key]);
        }
      }
		}
//--------------------------
// End - Modified
//--------------------------

    // Call the method in class.tx_kickstarter_compilefiles.php
		tx_kickstarter_compilefiles::makeFilesArray($extKey);

//--------------------------
// begin - Modified
//--------------------------
    // Change the icon
    if (isset($this->sections['savext'])) {
		  $iconPath = t3lib_div::getUrl(t3lib_extMgm::extPath('sav_library').'kickstarter/ext_icon.gif');
  		$this->addFileToFileArray("ext_icon.gif",$iconPath);   
    }   
//--------------------------
// End - Modified
//--------------------------
  }


	/**
	 * View result
	 *
	 * @return	HTML with filelist and fileview
	 */
	function view_result()	{
		$this->makeFilesArray($this->saveKey);

		$keyA = array_keys($this->fileArray);
		asort($keyA);

		$filesOverview1 = array();
		$filesOverview2 = array();
		$filesContent   = array();
		
		$filesOverview1[]= '<tr'.$this->bgCol(1).'>
			<td><strong>' . $this->fw('Filename:') . '</strong></td>
			<td><strong>' . $this->fw('Size:') . '</strong></td>
			<td><strong>' . $this->fw('&nbsp;') . '</strong></td>
			<td><strong>' . $this->fw('Overwrite:') . '</strong></td>
		</tr>';

		foreach($keyA as $fileName)	{
			$data = $this->fileArray[$fileName];

			$fI = pathinfo($fileName);
			if (t3lib_div::inList('php,sql,txt,xml',strtolower($fI['extension'])))	{
				$linkToFile='<strong><a href="#'.md5($fileName).'">'.$this->fw("&nbsp;View&nbsp;").'</a></strong>';
				
				if($fI['extension'] == 'xml') {
					$data['content'] = $GLOBALS['LANG']->csConvObj->utf8_decode(
						$data['content'],
						$GLOBALS['LANG']->charSet
					);
				}

//--------------------------
// Begin - Modified
//--------------------------
				$fileContent='<tr' .$this->bgCol(1) .'>
				<td><a name="' . md5($fileName) . '"></a><strong>' . $this->fw($fileName) . '</strong></td>
				</tr>
				<tr>';
				if (t3lib_div::inList('xml', strtolower($fI['extension'])))	{
          $data['content'] = utf8_decode($data['content']);
				} 
				$fileContent.='</tr><tr><td>'.$this->preWrap($data['content']).'<td></tr>';
				$filesContent[]=$fileContent;
//--------------------------
// End - Modified
//--------------------------
				
			} else $linkToFile=$this->fw('&nbsp;');

			$line = '<tr' . $this->bgCol(2) . '>
				<td>' . $this->fw($fileName) . '</td>
				<td>' . $this->fw(t3lib_div::formatSize($data['size'])) . '</td>
				<td>' . $linkToFile . '</td>
				<td>';

			if($fileName == 'doc/wizard_form.dat' 
			|| $fileName == 'doc/wizard_form.html') {
				$line .= '<input type="hidden" name="' . $this->piFieldName('wizArray_upd') . '[save][overwrite_files]['.$fileName.']" value="1" />';
			} else {
				$checked = '';				

				if(!is_array($this->wizArray['save']['overwrite_files']) // check for first time call of "View Result"
				|| (isset($this->wizArray['save']['overwrite_files'][$fileName]) && $this->wizArray['save']['overwrite_files'][$fileName] == '1') // if selected
				|| !isset($this->wizArray['save']['overwrite_files'][$fileName]) // if new
				) {
					$checked = ' checked="checked"';
				}

				$line .= '<input type="hidden" name="' . $this->piFieldName('wizArray_upd') . '[save][overwrite_files]['.$fileName.']" value="0" />';	
				$line .= '<input type="checkbox" name="' . $this->piFieldName('wizArray_upd') . '[save][overwrite_files]['.$fileName.']" value="1"'.$checked.' />';
			}

			$line .= '</td>
			</tr>';
			if (strstr($fileName,'/'))	{
				$filesOverview2[]=$line;
			} else {
				$filesOverview1[]=$line;
			}
		}

		$content  = '<table border="0" cellpadding="1" cellspacing="2">'.implode('',$filesOverview1).implode('',$filesOverview2).'</table>';
		$content .= '<br /><input type="submit" name="'.$this->piFieldName('updateResult').'" value="Update result" /><br />';
		$content .= $this->fw('<br /><strong>Author name:</strong> '.$this->wizArray['emconf'][1]['author'].'
							<br /><strong>Author email:</strong> '.$this->wizArray['emconf'][1]['author_email']);


		$content.= '<br /><br />';
		if (!$this->EMmode)	{
			$content.='<input type="submit" name="'.$this->piFieldName('WRITE').'" value="WRITE to \''.$this->saveKey.'\'" />';
		} else {
			$content.='
				<strong>'.$this->fw('Write to location:').'</strong><br />
				<select name="'.$this->piFieldName('loc').'">'.
					($this->pObj->importAsType('G')?'<option value="G">Global: '.$this->pObj->typePaths['G'].$this->saveKey.'/'.(@is_dir(PATH_site.$this->pObj->typePaths['G'].$this->saveKey)?' (OVERWRITE)':' (empty)').'</option>':'').
					($this->pObj->importAsType('L')?'<option value="L" selected="selected">Local: '.$this->pObj->typePaths['L'].$this->saveKey.'/'.(@is_dir(PATH_site.$this->pObj->typePaths['L'].$this->saveKey)?' (OVERWRITE)':' (empty)').'</option>':'').
				'</select>
				<input type="submit" name="'.$this->piFieldName('WRITE').'" value="WRITE" onclick="return confirm(\'If the setting in the selectorbox says OVERWRITE\nthen the marked files of the current extension in that location will be OVERRIDDEN! \nPlease decide if you want to continue.\n\n(Remember, this is a *kickstarter* - NOT AN editor!)\');" />
			';
//--------------------------
// Begin - Modified 
//--------------------------					
		  if(is_array($this->wizArray['savext']) && $this->wizArray['savext'][1]['generateForm'] ) {

        // Adds version checkboxes
		    $content.= '<br /><br />'.$this->helpIcon('formWizard');
        $content.= '<br /><br /><strong>New version :</strong> 
        x <input type="checkbox" name="' . $this->piFieldName('version') . '[x]" value="1" />
        .y <input type="checkbox" name="' . $this->piFieldName('version') . '[y]" value="1" />
        .z <input type="checkbox" name="' . $this->piFieldName('version') . '[z]" value="1" />
        ';

        // Adds xml generation checkbox
        $content.= '<br /><br /><strong>Generate XML configuration :</strong>
        <input type="hidden" name="' . $this->piFieldName('xmlConfiguration') . '" value="0" />
        <input type="checkbox" '.($this->wizArray['savext'][1]['xmlConfiguration'] ? 'checked ' : '').'name="' . $this->piFieldName('xmlConfiguration') . '" value="1" />
        ';

        // Adds debug checkbox
        $content.= '<br /><br /><strong>Debug Query:</strong>
        <input type="checkbox" name="' . $this->piFieldName('debug') . '[1]" value="1" /> 
        ';

        // Adds maintenance checkbox
        $content.= '<br /><br /><strong>Add maintenance :</strong> 
        <input type="hidden" name="' . $this->piFieldName('maintenance') . '" value="0" />
        <input type="checkbox" '.($this->wizArray['savext'][1]['maintenance'] ? 'checked ' : '').'name="' . $this->piFieldName('maintenance') . '" value="1" />
        ';

        // Adds new extension generation
        if ($this->wizArray['savext'][1]['newExtensionExists']) {
          $style = $this->sections['savext']['styles']['errorValue'];
          $checked = ($this->modData['newExtensionOverwrite'][1] ? ' checked="checked"' : '');
          $content.= '<br /><br /><span style="'.$style.'">Extension folder already exists :</span> 
          <br /><input type="hidden" name="' . $this->piFieldName('newExtensionOverwrite') . '[1]" value="0" />
          <input type="checkbox" name="' . $this->piFieldName('newExtensionOverwrite') . '[1]" value="1"'.$checked.' /> Overwrite ?
          ';
        }
        
        $content.= '<br /><br /><strong>Generate a new extension :</strong> 
        <br /><input name="' . $this->piFieldName('newExtension') . '" value="'.$this->wizArray['savext'][1]['newExtension'].'" size="30" /> 
        from <strong>'.($this->wizArray['savext'][1]['oldExtension'] ? $this->wizArray['savext'][1]['oldExtension'] : $this->wizArray['save']['extension_key']).'</strong>';
        
      }
//--------------------------
// End - Modified 
//--------------------------							
		}

		$this->afterContent= '<br /><table border="0" cellpadding="1" cellspacing="2">'.implode('',$filesContent).'</table>';
		return $content;
	}


  function helpIcon($field){	
    return '<a href="#" style="float:left;" onclick="vHWin=window.open(\''.$this->wizard->siteBackPath.TYPO3_mainDir.'view_help.php?tfID=sav_library.'.$field.'\',\'viewFieldHelp\',\'height=400,width=600,status=0,menubar=0,scrollbars=1\');vHWin.focus();return false;"><img src="'.$this->wizard->siteBackPath.TYPO3_mainDir.'gfx/helpbubble.gif" width="16" height="16" hspace="2" border="0" class="typo3-csh-icon" alt="'.$field.'" /></a>';
  }


	/**
	 * makes a text bold if $flag is set
	 * (defined in class.tx_kickstarter_sectionbase.php)
	 *
	 * @param	string		text
	 * @param	boolean		flag to make the text bold
	 * @return	string		text, optionaly made bold
	 */
	function bwWithFlag($str,$flag, $style='')	{
	  $str = '<span style="'.$style.'">'.$str.'</span>';
		if ($flag)	$str = '<strong>'.$str.'</strong>';
		return $str;
	}

	/**
	 * returns hidden field containing the current command
	 * (defined in class.tx_kickstarter_sectionbase.php)
	 *
	 * @return	sting		hidden field containing the current command
	 */
	function cmdHiddenField()	{

//--------------------------
// Begin - Modified
//--------------------------
    $addHidden = '
		<input type="hidden" name="'.$this->piFieldName("wizKey").'" value="'.$this->wizard->modData["wizKey"].'">
		<input type="hidden" name="'.$this->piFieldName("wizId").'" value="'.$this->wizard->modData["wizId"].'">
		<input type="hidden" name="'.$this->piFieldName("wizSpecialCmd").'" value="'.$this->wizard->modData["wizSpecialCmd"].'">
    ';

//		return '<input type="hidden" name="'.$this->piFieldName("cmd").'" value="'.htmlspecialchars($this->currentCMD).'">';
		return '<input type="hidden" name="'.$this->piFieldName("cmd").'" value="'.htmlspecialchars($this->currentCMD).'">'.$addHidden;
//--------------------------
// End - Modified
//--------------------------
	}
	
}


?>
