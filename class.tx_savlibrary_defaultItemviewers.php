<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2008 Yolf <yolf.typo3@orange.fr>
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

require_once(t3lib_extMgm::extPath('rtehtmlarea').'pi2/class.tx_rtehtmlarea_pi2.php');

/**
 * SAV Library: Item viewers
 *
 * @author	Yolf <yolf.typo3@orange.fr>
 *
 */

class tx_savlibrary_defaultItemviewers {

  public $savlibrary;     // Reference to the savlibrary object
  
/**
 * Start variables for the RTE API
 */
	public $RTEObj;
	public $RTEinit = 0;
	public $docLarge = 1;
	public $RTEcounter = 0;
	public $additionalJS_initial = '';		  // Initial JavaScript to be printed before the form (should be in head, but cannot due to IE6 timing bug)
	public $additionalJS_pre = array();	  // Additional JavaScript to be printed before the form
	public $additionalJS_post = array();	  // Additional JavaScript to be printed after the form
	public $additionalJS_submit = array();	// Additional JavaScript to be executed on submit
	public $PA = array(
		'itemFormElName' =>  '',
		'itemFormElValue' => '',
		);
	public $specConf = array();
	public $thisConfig = array();
	public $RTEtypeVal = 'text';
  public $changedRTEList = '';     // List of changedRTE for textarea with RTE	
  public $updateRTEList = '';      // List of updateRTE for textarea with RTE
  /**
 * End variables for the RTE API
 */
  
	/**
	 * String Input viewer
	 *
	 * @param $config array (Configuration array)
	 *
	 * @return string (item to display)
	 */	      
  public function viewStringInput(&$config) {

    $htmlArray = array();
    
    $htmlArray[] = $config['value'] ? nl2br(stripslashes($config['value'])) : '';
    
    return $this->savlibrary->arrayToHTML($htmlArray);
  } 

	/**
	 * String Input viewer in edit mode
	 *
	 * @param $config array (Configuration array)
	 *
	 * @return string (item to display)
	 */	  
  public function viewStringInputEditMode(&$config) {
		
    $htmlArray = array();
    
		$class = ($config['classhtmltag'] ? 'class="'.$config['classhtmltag'].'" ' : '');
		$style = ($config['stylehtmltag'] ? 'style="'.$config['stylehtmltag'].'" ' : '');

    $htmlArray[] = '<input type="text" '.$class.$style.'name="'.$config['elementControlName'].'" value="'.stripslashes($config['value']).'" size="'.$config['size'].'" onchange="document.changed=1" />';

    return $this->savlibrary->arrayToHTML($htmlArray);
  } 



	/**
	 * String Input viewer
	 *
	 * @param $config array (Configuration array)
	 *
	 * @return string (item to display)
	 */	      
  public function viewStringPassword(&$config) {

    $htmlArray = array();
    
    $htmlArray[] = $config['value'] ? str_repeat('*', 7) : '';
    
    return $this->savlibrary->arrayToHTML($htmlArray);
  } 

	/**
	 * String Input viewer in edit mode
	 *
	 * @param $config array (Configuration array)
	 *
	 * @return string (item to display)
	 */	  
  public function viewStringPasswordEditMode(&$config) {
		
    $htmlArray = array();
    
		$class = ($config['classhtmltag'] ? 'class="'.$config['classhtmltag'].'" ' : '');
		$style = ($config['stylehtmltag'] ? 'style="'.$config['stylehtmltag'].'" ' : '');

    $htmlArray[] = '<input type="password" '.$class.$style.'name="'.$config['elementControlName'].'" value="'.stripslashes($config['value']).'" size="'.$config['size'].'" onchange="document.changed=1" />';

    return $this->savlibrary->arrayToHTML($htmlArray);
  } 



	/**
	 * Label viewer
	 *
	 * @param $config array (Configuration array)
	 *
	 * @return string (item to display)
	 */	 
  public function viewLabel(&$config) { 
  
    $htmlArray = array();
    
    $htmlArray[] = $config['cutvalue'] ? '' : stripslashes($config['value']);

    return $this->savlibrary->arrayToHTML($htmlArray);
  } 

	/**
	 * Label viewer in edit mode
	 *
	 * @param $config array (Configuration array)
	 *
	 * @return string (item to display)
	 */	 
  public function viewLabelEditMode(&$config) {
  
    $htmlArray = array();
    
    $htmlArray[] = stripslashes($config['value']);

    return $this->savlibrary->arrayToHTML($htmlArray);
  } 




	/**
	 * Checkbox viewer
	 *
	 * @param $config array (Configuration array)
	 *
	 * @return string (item to display)
	 */	 
  public function viewCheckbox(&$config) {
//debug($config,'viewCheckbox');
  
    $htmlArray = array();
    
    if (is_array($config['items'])) {
      $cols = ($config['nbcols'] ? $config['nbcols'] : 1);
      $cpt = 0;
      $cptItem = 0;
      $val = $config['value'];
      foreach ($config['items'] as $key => $value) {
        $checked = ($val&0x01 ? 'checked' : '');
        $val = $val >> 1;
		    $htmlArray[] = '<span class="checkbox">';
        $htmlArray[] = ($checked ? 
                        $this->savlibrary->getLibraryLL('itemviewer.yesMult').stripslashes($this->savlibrary->getLL_db($value[0])) : 
                        ($config['donotdisplayifnotchecked'] ? '' : $this->savlibrary->getLibraryLL('itemviewer.noMult').stripslashes($this->savlibrary->getLL_db($value[0]))));
        $htmlArray[] = '</span>'; 
        $cpt++;  
        $cptItem++;
        if ($cptItem == $config['nbitems']){
          break;
        }
        if ($cpt == $cols){
          $htmlArray[] = '<br class="checkbox" />';
          $cpt = 0;
        }    
      }
    } else {
      $htmlArray[] = ($config['value'] ? $this->savlibrary->getLibraryLL('itemviewer.yes') : ($config['donotdisplayifnotchecked'] ? '' : $this->savlibrary->getLibraryLL('itemviewer.no')) );
    }
    
    return $this->savlibrary->arrayToHTML($htmlArray);
  } 
  
	/**
	 * Checkbox viewer in edit mode
	 *
	 * @param $config array (Configuration array)
	 *
	 * @return string (item to display)
	 */	
  public function viewCheckboxEditMode(&$config) {
//debug($config,'viewCheckboxEditMode');
  
    $htmlArray = array();
    
    if (is_array($config['items'])) {
      $cols = ($config['nbcols'] ? $config['nbcols'] : 1);
      $cpt = 0;
      $cptItem = 0;
      $val = $config['value'];
      foreach ($config['items'] as $key => $value) {
        $checked = (($val&0x01 || $value[1]==1 )? ' checked="checked"' : '');
        $val = $val >> 1;
		    $htmlArray[] = '<input type="hidden" name="'.$config['elementControlName'].'['.$key.']" value="0" />';
        $htmlArray[] = '<input type="checkbox" name="'.$config['elementControlName'].'['.$key.']"  value="1"'.$checked.' onchange="document.changed=1" />';
        $htmlArray[] = '<span class="checkbox" '.$value['addattributes'].'>'.stripslashes($this->savlibrary->getLL_db($value[0])).'</span>'; 
        $cpt++;  
        $cptItem++;
        if ($cptItem == $config['nbitems']){
          break;
        }
        if ($cpt == $cols){
          $htmlArray[] = '<br class="checkbox" />';
          $cpt = 0;
        }    
      }
    } else {
      // Only one checkbox	
      if ($config['value'] == 1) {
        $checked = ' checked="checked"';
      } else {
        if ($config['uid']) {
          $checked='';
        } else {
          $checked = ($config['default'] ? ' checked="checked"' : '');
        }
      }      	
      // Check if it is associated with a mail		
      if ($config['mail']) {     
        $htmlArray[] = $this->savlibrary->mailButton($this->savlibrary->formName, $config['_field'], ($config['value'] ? '' : $config['valueforcheckmail']), $this->savlibrary->rowItem).'<div class="separator">&nbsp;</div>';
        if ($config['value']) {
  		    $htmlArray[] = '<input type="hidden" name="'.$config['elementControlName'].'" value="0" />';
          $htmlArray[] = '<input type="checkbox" name="'.$config['elementControlName'].'"  value="1"'.$checked.' onchange="document.changed=1" />';        
        } else {
          $htmlArray[] = '<input type="hidden" name="'.$config['elementControlName'].'" value="0" />';      
        }
      } else {

  		  $htmlArray[] = '<input type="hidden" name="'.$config['elementControlName'].'" value="0" />';
        $htmlArray[] = '<input type="checkbox" name="'.$config['elementControlName'].'"  value="1"'.$checked.' onchange="document.changed=1" />';
      }
    }
    
    return $this->savlibrary->arrayToHTML($htmlArray);
  } 





	/**
	 * Radio viewer
	 *
	 * @param $config array (Configuration array)
	 *
	 * @return string (item to display)
	 */	
  public function viewRadio(&$config) {
//debug($config,'viewRadio');
  
    $htmlArray = array();
    
    if (is_array($config['items'])) {
      $val = $config['value'];
      foreach ($config['items'] as $key => $value) {
        if ($val==$value[1]) {
          $htmlArray[] = stripslashes($this->savlibrary->getLL_db($value[0]));
        }
      }
    }
     
    return $this->savlibrary->arrayToHTML($htmlArray);
  } 

	/**
	 * Radio viewer in edit mode
	 *
	 * @param $config array (Configuration array)
	 *
	 * @return string (item to display)
	 */	  
  public function viewRadioEditMode(&$config) {
//debug($config,'viewRadioEditMode');
  
    $htmlArray = array();
    
    if (is_array($config['items'])) {
      $cols = ($config['cols'] ? $config['cols'] : 1);
      $cpt = 0;
      $val = $config['value'];
      foreach ($config['items'] as $key => $value) {
        $checked = ($val==$value[1] ? 'checked="checked"' : '');
		    $htmlArray[] = '<input type="radio" name="'.$config['elementControlName'].'" '.$checked.' value="'.$value[1].'" onchange="document.changed=1" />';
        $htmlArray[] = '<span class="left">'.stripslashes($this->savlibrary->getLL_db($value[0])).'</span>'; 
        $cpt++;  
        if ($cpt == $cols) {
          $htmlArray[] = '<br class="radio" />';
          $cpt = 0;
        }    
      }
    }
    
    return $this->savlibrary->arrayToHTML($htmlArray);
  } 




	/**
	 * Text area viewer
	 *
	 * @param $config array (Configuration array)
	 *
	 * @return string (item to display)
	 */	
  public function viewTextArea(&$config) {
  
    $htmlArray = array();
    
		if (isset($config['wizards']['RTE'])) {
			$htmlArray[] = html_entity_decode(stripslashes($config['value']));		  
		} else {
			$htmlArray[] = nl2br(html_entity_decode(stripslashes($config['value'])));
		}
		
    return $this->savlibrary->arrayToHTML($htmlArray);
  } 

	/**
	 * Text area viewer in edit mode
	 *
	 * @param $config array (Configuration array)
	 *
	 * @return string (item to display)
	 */	
  public function viewTextAreaEditMode(&$config) {
//debug($config,'viewTextAreaEditMode');
  
    $htmlArray = array();
    
		if (isset($config['wizards']['RTE'])) {
		
			if(!$this->RTEObj) {
        $this->RTEObj = t3lib_div::makeInstance('tx_rtehtmlarea_pi2');
        $GLOBALS['TSFE']->additionalHeaderData['tx_savlibrary'] .= $this->additionalJS_initial;       
      }
			if($this->RTEObj->isAvailable()) {
				$this->RTEcounter++;
				
//				$pageTSConfig = $GLOBALS['TSFE']->getPagesTSconfig();
				$pageTSConfig = t3lib_BEfunc::getPagesTSconfig($GLOBALS['TSFE']->id);
				$thisConfig = $pageTSConfig['RTE.']['default.']['FE.'];
			  $specConf = array(
          'richtext' => 1,
          'rte_transform' => array(
            'parameters' => array('flag=rte_enabled', 'mode=ts_css'),
          ),         
        );
				$PA['itemFormElName'] = $config['elementControlName'];
        $PA['itemFormElValue'] = html_entity_decode($config['value'], ENT_QUOTES);
				$out = $this->RTEObj->drawRTE($this,'','',$row=array(), $PA, $specConf, $thisConfig, $RTEtypeVal, '', 0);
				// Remove the hidden field
				$out = preg_replace('/<input type="hidden"[^>]*>/', '', $out);
        // Add onchange				
				$out = preg_replace('/<textarea ([^>]*)>/', '<textarea \\1'.' cols="'.$config['cols'].'" rows="'.$config['rows'].'" onchange="document.changed=1;">'	, $out);
				
        // Replace height and width
        if ($config['height']) {
          $out = preg_replace('/height:[^p]*/', 'height:'.$config['height'], $out);
        }
        // Add 45px to the first div
        $out = preg_replace('/height:([^p]*)/', 'height:$1+45', $out, 1);
        
        if ($config['width']) {
          $out = preg_replace('/width:[^p]*/', 'width:'.$config['width'], $out);
        }
        
        $htmlArray[] = $out;
        $htmlArray[] = '<script type="text/javascript">';
        $htmlArray[] = $this->additionalJS_post[$this->RTEcounter-1];       
		    $htmlArray[] = '</script>';
		    if (!$this->RTEinit) {
          $GLOBALS['TSFE']->additionalHeaderData['tx_savlibrary'] .= $this->additionalJS_initial;  
          $this->RTEinit = 1;   
        }

        $js = array();                
        $js[] = '<script type="text/javascript">';
        $js[] = $this->additionalJS_pre[0];
		    $js[] = '</script>';
        $GLOBALS['TSFE']->additionalHeaderData['tx_savlibrary'] .= implode($this->savlibrary->EOL.$this->savlibrary->TAB, $js);
		      
		    $this->updateRTEList .= $this->additionalJS_submit[$this->RTEcounter-1];
		    
		    $this->changedRTEList .= 'changedTextareaRTE('.$this->RTEcounter.');';

			}

    } else {
      $htmlArray[] = '<textarea name="'.$config['elementControlName'].'" cols="'.$config['cols'].'" rows="'.$config['rows'].'" onchange="document.changed=1">'.$config['value'].'</textarea>';

      return $this->savlibrary->arrayToHTML($htmlArray); 
		}

    return $this->savlibrary->arrayToHTML($htmlArray);
  } 




	/**
	 * Link viewer
	 *
	 * @param $config array (Configuration array)
	 *
	 * @return string (item to display)
	 */	   
  public function viewLink(&$config){
//debug($config,'viewLink');
  
    $htmlArray = array();
    
    if ($config['generatertf'] && $config['value']) {
      if ($config['savefilertf']) {    
        $path_parts = pathinfo($config['savefilertf']);
        $config['folder'] = $path_parts['dirname'];
        $htmlArray[] = $this->savlibrary->makeLink($config['value'], 0, $config);
      } else  {
        $this->savlibrary->addError('error.incorrectRTFSaveFileName');
      }            
    } else {
      $htmlArray[] = $this->savlibrary->makeUrlLink($this->viewStringInput($config), '', $config);
    }
          
    return $this->savlibrary->arrayToHTML($htmlArray);
  } 

	/**
	 * Link viewer in edit mode
	 *
	 * @cparam $onfig array (Configuration array)
	 *
	 * @return string (item to display)
	 */	 
  public function viewLinkEditMode(&$config){
//debug($config,'viewLinkEdit');
  
    $htmlArray = array();
    
    // generate the button
    if ($config['generatertf']) {
      $htmlArray[] = $this->savlibrary->generateRTFButton($this->savlibrary->formName, $config['_field'], $this->savlibrary->rowItem);
      // update the field
      if ($config['uid']) {
        $res = $GLOBALS['TYPO3_DB']->exec_UPDATEquery(
          /* TABLE   */	$config['table'],		
          /* WHERE   */	'uid='.intval($config['uid']),  		
          /* FIELDS  */	array($config['field'] => $config['value'])
        );
      }
      if ($config['value']) {
        $path_parts = pathinfo($config['savefilertf']);
        $config['folder'] = $path_parts['dirname'];
        $htmlArray[] = '<div class="separator">&nbsp;</div>'.$this->savlibrary->hiddenField($config['elementControlName'], $config['value']).'<div class="separator">&nbsp;</div>'.$this->savlibrary->makeLink($config['value'], 0, $config);
      } else {
        $htmlArray[] = '<div class="separator">&nbsp;</div>'.$this->savlibrary->hiddenField($config['elementControlName'], $config['value']);
      }
    } else {
      $config['size'] = ($config['size']<=20 ? $config['size']= 40 : $config['size']);
      $htmlArray[] = $this->viewStringInputEditMode($config);
    } 
      
    return $this->savlibrary->arrayToHTML($htmlArray);
  }
 
 
 
 
 	/**
	 * Date and time viewer
	 *
	 * @param $config array (Configuration array)
	 *
	 * @return string (item to display)
	 */	  
  public function viewDateTime(&$config){
//debug($config,'viewDateTime');
  
    $htmlArray = array();
    
		if(!$config['value']) {
      $htmlArray[] = '';
    } else {
	    $htmlArray[] = $this->savlibrary->makeDateFormat($config['value'], '', $config); 
	  } 
	  
    return $this->savlibrary->arrayToHTML($htmlArray);
  } 

 	/**
	 * Date and time viewer in edit mode
	 *
	 * @param $config array (Configuration array)
	 *
	 * @return string (item to display)
	 */	 
  public function viewDateTimeEditMode(&$config){
//debug($config,'viewDateTimeEdit');
		
		$out = tx_savdateselectlib::getInputButton ($config['elementControlName'], ($config['value'] ? $config['value'] : ($config['nodefault'] ? '' : time())), $config);

		$htmlArray = explode(chr(10), $out);
		
    return $this->savlibrary->arrayToHTML($htmlArray);
  } 




 	/**
	 * Date viewer
	 *
	 * @param $config array (Configuration array)
	 *
	 * @return string (item to display)
	 */	  
  public function viewDate(&$config){
//debug($config,'viewDate');
  
    $htmlArray = array();
    
		if(!$config['value']) {
      $htmlArray[] = '';
    } else {
	    $htmlArray[] = $this->savlibrary->makeDateFormat($config['value'], '', $config); 
	  } 
	  
    return $this->savlibrary->arrayToHTML($htmlArray);
  } 

 	/**
	 * Date viewer in edit mode
	 *
	 * @param $config array (Configuration array)
	 *
	 * @return string (item to display)
	 */	  
  public function viewDateEditMode(&$config){
//debug($config,'viewDateEdit');
  
    $out = tx_savdateselectlib::getInputButton ($config['elementControlName'], ($config['value'] ? $config['value'] : ($config['nodefault'] ? '' : strtotime(date('m/d/Y')))), $config);
    
		$htmlArray = explode(chr(10), $out);

    return $this->savlibrary->arrayToHTML($htmlArray);
  } 




 	/**
	 * File viewer
	 *
	 * @param $config array (Configuration array)
	 *
	 * @return string (item to display)
	 */	  
  public function viewFile(&$config){
//debug($config,'viewFile');
  
    $htmlArray = array();
    
    $folder = $config['uploadfolder'].($config['addtouploadfolder'] ? '/'.$config['addtouploadfolder'] : '');

		  if ($config['iframe']) {
		    // it's an image to be opened in an iframe
		    $width = $config['width'] ? $config['width'] : '100%';
		    $height = $config['height'] ? $config['height'] : '800';
		    $message = $config['message'] ? $config['message'] : '';
        $htmlArray[] = '<iframe src="'.$folder.'/'.$config['value'].'" width="'.$width.'" height="'.$height.'">';
        $htmlArray[] = $message;
        $htmlArray[] = '</iframe>';
      }	elseif ($config['allowed']) {
      
        //
        if ($config['func'] == 'makeItemLink') {
          $file = $config['_value'];
        } else {
          $file = $config['value'];
        }
        
        // It's an image. Set parameters
		    if ($file && file_exists($folder.'/'.$file)) {
          $params['width'] = $config['width'];
          $params['height'] = $config['height'];
          $params['folder'] = $folder;
          $params['alt'] = $config['alt'];
          $out = $this->savlibrary->makeImage($file,'',$params);
                 
          if ($config['func']=='makeNewWindowLink') {
            $out = $this->savlibrary->makeNewWindowLink ($out, $uid='', array('windowurl' => $folder.'/'.$file));
          } elseif ($config['func']=='makeItemLink') {
//            $out = str_replace($file, $out, $config['value']);
            $out = preg_replace('/(<a[^>]*>)[^<]*(<\/a>)/', '$1' . $out . '$2', $config['value']);
          }
          $htmlArray[] = $out;          
        } else {
          $params['width'] = $config['width'];
          $params['height'] = $config['height'];
          $out = $this->savlibrary->makeImage(t3lib_extMgm::siteRelPath('sav_library').'res/images/unknown.gif','',$params);
          $out = preg_replace('/(<a[^>]*>)[^<]*(<\/a>)/', '$1' . $out . '$2', $config['value']);
          $htmlArray[] = $out;
        }
      } else {
        // It's a file. Make an hyperlink
        $out = '';
        $params['folder'] = $folder;
        $params['message'] = $config['message'];
        $params['target'] = $config['target'];
        if ($config['addicon']) {
          $pathInfo = pathinfo($config['_value']);
          if (file_exists(t3lib_extMgm::extPath('sav_library').'res/fileicons/'.$pathInfo['extension'].'.gif')) {
            $htmlArray[] = '<img src="'.t3lib_extMgm::siteRelPath('sav_library').'res/fileicons/'.$pathInfo['extension'].'.gif" alt="Icon '.$pathInfo['extension'].'" />&nbsp;&nbsp;';          
          } elseif (file_exists('typo3/gfx/fileicons/'.$pathInfo['extension'].'.gif')) {
            $htmlArray[] = '<img src="typo3/gfx/fileicons/'.$pathInfo['extension'].'.gif" alt="Icon '.$pathInfo['extension'].'" />&nbsp;&nbsp;';
          }
        }
      $htmlArray[] = $this->savlibrary->makeLink($config['value'],'',$params);        
    }
    
    return $this->savlibrary->arrayToHTML($htmlArray);
  } 

 	/**
	 * File viewer in edit mode
	 *
	 * @param $config array (Configuration array)
	 *
	 * @return string (item to display)
	 */	 
  public function viewFileEditMode(&$config){
//debug($config,'viewFileEditMode');
  
    $htmlArray = array();
    
    $addtouploadfolder = ($config['addtouploadfolder'] ? $config['addtouploadfolder'] : '');
		
    if ($config['size'] < 10) {
      $config['size'] = '';
    }
		$htmlArray[] = '<input type="text" name="'.$config['field'].'" value="'.$config['value'].'"  size="'.$config['size'].'" />';
    $htmlArray[] = '<input type="file" name="'.$config['elementControlName'].'"  value="" size="'.$config['size'].'" onchange="document.changed=1" />';
    $htmlArray[] = '<input type="hidden" name="'.$this->savlibrary->formName.'[addtouploadfolder]'.(isset($this->savlibrary->rowItem) ? '['.$this->savlibrary->rowItem.']' : '['.$config['uid'].']').'" value="'.$addtouploadfolder.'" />';

    return $this->savlibrary->arrayToHTML($htmlArray);
  } 




 	/**
	 * Schedule viewer
	 *
	 * @param $config array (Configuration array)
	 *
	 * @return string (item to display)
	 */	 
  public function viewSchedule(&$config){
//debug($config,'viewSchedule');
  
    $htmlArray = array();
      
		$viewItem = ($config['edit'] ? 'viewStringInputEditMode' : 'viewStringInput');
  
    if(!$config['value'] && !$config['edit']) {
      $out = '';
    } else {
      // explode the value
		  $schedule = array();
		  $days = explode(';', $config['value']);
		  foreach($days as $day) {
			 $hours = explode('|',$day);
			 $am = explode('-',$hours[0]);
			 $pm = explode('-',$hours[1]);
			 $schedule[] = array(
					'day'=> $day,
					'beginAm' => $am[0], 
					'endAm' => $am[1], 
					'beginPm' => $pm[0], 
					'endPm' => $pm[1], 
			   );
		  }
		  $ta[TYPE] = 'schedule';
		  foreach (($config['edit'] ? range(0, 4) : $schedule )  as $key => $value) {
			 unset ($node);	
			 $node[TYPE] = 'sub_item';

			 $node[MARKERS]['separator'] = ($config['edit'] ? '<span class="left">-</span>' : '-');
			 
			 $node[MARKERS]['day'] = $this->savlibrary->getLibraryLL('itemviewer.days.'.$key);;
			 $localConfig['uid'] = $config['uid'];

       $localConfig['elementControlName'] = preg_replace('/\[([^\[]+)\](.*)$/', '['.$config['_field'].']['.$config['uid'].']['.$key.'][beginAm]['.$config['uid'].']', $config['elementControlName']);  		 
			 $localConfig['value'] = $schedule[$key]['beginAm'];
			 $node[MARKERS]['beginAm'] = $this->$viewItem($localConfig);
			 
       $localConfig['elementControlName'] = preg_replace('/\[([^\[]+)\](.*)$/', '['.$config['_field'].']['.$config['uid'].']['.$key.'][endAm]['.$config['uid'].']', $config['elementControlName']);  		 
			 $localConfig['value'] = $schedule[$key]['endAm'];
			 $node[MARKERS]['endAm'] = $this->$viewItem($localConfig);

       $localConfig['elementControlName'] = preg_replace('/\[([^\[]+)\](.*)$/', '['.$config['_field'].']['.$config['uid'].']['.$key.'][beginPm]['.$config['uid'].']', $config['elementControlName']);  		 
			 $localConfig['value'] = $schedule[$key]['beginPm'];
			 $node[MARKERS]['beginPm'] = $this->$viewItem($localConfig);

       $localConfig['elementControlName'] = preg_replace('/\[([^\[]+)\](.*)$/', '['.$config['_field'].']['.$config['uid'].']['.$key.'][endPm]['.$config['uid'].']', $config['elementControlName']);  		 
			 $localConfig['value'] = $schedule[$key]['endPm'];
			 $node[MARKERS]['endPm'] = $this->$viewItem($localConfig);

			 $items[] = $node;
		  }
		  $ta[REGIONS]['sub_items'] = $items;

		  $htmlArray[] = $this->savlibrary->replaceTemplate($ta);		  
    }
      
    return $this->savlibrary->arrayToHTML($htmlArray);
  } 

 	/**
	 * Schedule viewer in edit mode
	 *
	 * @param $config array (Configuration array)
	 *
	 * @return string (item to display)
	 */	 
  public function viewScheduleEditMode(&$config){
  
    $htmlArray = array();
    
		$htmlArray[] = $this->viewSchedule($config);
      
    return $this->savlibrary->arrayToHTML($htmlArray);
  } 




 	/**
	 * Selector box viewer
	 *
	 * @param $config array (Configuration array)
	 *
	 * @return string (item to display)
	 */	 
  public function viewSelectorbox(&$config){
//debug($config,'viewSelectorbox');
  
    $htmlArray = array();

    // find the selected item
    foreach ($config['items'] as $key => $item) {
      if ($item[1] == $config['_value']) {
        break;
      }
    }
       
		if (isset($config['func'])) {
			$htmlArray[] =  $this->savlibrary->$config['func']($this->savlibrary->getLL_db($config['items'][$key][0]), $config['uid'], $config).'<br>';
    } else {
      $htmlArray[] = $this->savlibrary->getLL_db($config['items'][$key][0]);
    }
    
    return $this->savlibrary->arrayToHTML($htmlArray);
  } 

 	/**
	 * Selector box viewer in edit mode
	 *
	 * @param $config array (Configuration array)
	 *
	 * @return string (item to display)
	 */	 
  public function viewSelectorboxEditMode(&$config){
//debug($config,'viewSelectorboxEditMode');
  
    $htmlArray = array();
    
		$htmlArray[] = '<select name="'.$config['elementControlName'].'" size="'.$config['size'].'" onchange="document.changed=1">';
		if ($config['emptyitem']) {
      $htmlArray[] = '<option value="0"></option>';
    }
    foreach ($config['items'] as $item) {
			$sel = ((string)$item[1] == (string)$config['value'])? ' selected="selected"' : '';
			$htmlArray[] = '<option '.$sel.' value="'.$item[1].'">'.stripslashes($this->savlibrary->getLL_db($item[0])).'</option>';
		}
		 $htmlArray[] = '</select>';
		 
    return $this->savlibrary->arrayToHTML($htmlArray);  
  } 
  



 	/**
	 * Single db relation selector box viewer
	 *
	 * @param $config array (Configuration array)
	 *
	 * @return string (item to display)
	 */	 
  public function viewDbRelationSingleSelector(&$config){
//debug($config,'viewDbRelationSingleSelector');
  
    $htmlArray = array();
    
    // Search for the key
    $keyFound = 0;
    foreach ($config['items'] as $key => $item) {
      if ($item['uid'] == $config['_value']) {
        $keyFound = $key;
        break;
      }
    }
    // Keep only the item found
    if (!isset($config['codeArray'])) {
      $temp = $config['items'][$keyFound];
      unset($config['items']);
      $config['items'][$keyFound] = $temp;
    }
        
    // Check if a special value exists
    if (is_array($config['items'][$keyFound]['special'])) {
      $config['special'] = $config['items'][$keyFound]['special'];
    }

    // check if a function is called
  	if ($config['func']) {
   		$htmlArray[] = $this->savlibrary->$config['func'](stripslashes($config['items'][$keyFound]['label']), ($config['setuid']=='this'? $config['_value'] : $config['uid']), $config);
    } else {
      // get the field from the label field of the allowed table.
      if (isset($config['codeArray'])) {
  			$code = ((int) ($config['items'][$keyFound]['code']/100))*100;
  			
  			if(!($config['items'][$keyFound]['code']%100)) {
  				$htmlArray[] = ($config['nobold'] ? '' : '<b>').$config['items'][$keyFound]['label'].($config['nobold'] ? '' : '</b>');
        } else {
  				$htmlArray[] = ($config['nobold'] ? '' : '<b>').$config['items'][$config['codeArray'][$code]]['label'].($config['nobold'] ? '' : '</b>');
  				$htmlArray[] = $config['items'][$keyFound]['label'];
  			}
      } else {
  			$htmlArray[] = $config['items'][$keyFound]['label'];
  		} 
    }
    return $this->savlibrary->arrayToHTML($htmlArray);
  }

 	/**
	 * Single db relation selector box viewer in edit mode
	 *
	 * @param $config array (Configuration array)
	 *
	 * @return string (item to display)
	 */	    
  public function viewDbRelationSingleSelectorEditMode(&$config) {
//debug($config,'viewDbRelationSingleSelectorEditMode');
  
    $htmlArray = array();
    
		$class = ($config['classhtmltag'] ? 'class="'.$config['classhtmltag'].'" ' : '');
		$style = ($config['stylehtmltag'] ? 'style="'.$config['stylehtmltag'].'" ' : '');

		$htmlArray[] = '<select '.$class.$style.'name="'.$config['elementControlName'].'" size="'.$config['size'].'" onchange="document.changed=1">';
		if ($config['emptyitem']) {
      $htmlArray[] = '<option value="0"></option>';
    }
    foreach ($config['items'] as $key => $item) {
			$sel = ($item['selected']) ? ' selected="selected"' : '';
			$style = ($item['style'] ? 'style="'.$item['style'].'"' : '');
			$htmlArray[] = '<option '.$sel.' '.$style.' value="'.$item['uid'].'">'.stripslashes($item['label']).'</option>';
		}
		$htmlArray[] = '</select>';
		
    return $this->savlibrary->arrayToHTML($htmlArray);  
  } 




 	/**
	 * Mutiple db relation selector box viewer
	 *
	 * @param $config array (Configuration array)
	 *
	 * @return string (item to display)
	 */	 
  public function viewDbRelationSingleSelectorMultiple(&$config) {
//debug($config,'viewDbRelationSingleSelectorMultiple');
  
    $htmlArray = array();
    
		if ($config['MM'] || $config['maxitems']>1) {	  
      // get all the fields
  		foreach($config['items'] as $item) {
  			if($item['selected']) {
  			
  			    // Check if a special value exists
          if (is_array($item['special'])) {
            $config['special'] = $item['special'];
          }

  				// if the code is not null, get the hierarchy item
  				if (isset($config['codeArray'])) {
  				  $code = ((int) ($item['code']/100))*100;
  				  if(!($item['code'] % 100)) {
  					  $htmlArray[] = ($config['nobold'] ? '' : '<b>').$item['label'].($config['nobold'] ? '' : '</b><br />');
            } else {
  				    $htmlArray[] = ($config['nobold'] ? '' : '<b>').$config['items'][$config['codeArray'][$code]]['label'].($config['nobold'] ? ' ' : '</b> ');
  					  $htmlArray[] = $item['label'].'<br />';
  				  }
          } else {
            // check if a function is called
            if ($config['func']) {
              $temp = $this->savlibrary->$config['func'](stripslashes($item['label']), ($config['setuid']=='this'? $config['_value'] : $config['uid']), $config);
            } else { 
              $temp = $item['label'];
            }        
  					$htmlArray[] = ($htmlArray ? ($config['separator'] ? $config['separator'].' ' :'<br />') : '').$temp;
  				}
  			}
  		}
  		return $this->savlibrary->arrayToHTML($htmlArray, $config['nohtmlprefix']);
 		}    
		else {
  		if ($config['content']) {
        // get the field from a query. the uid marker is replace by the selected value
        $query = $config['content']; 
        $mA["###uid###"] = $config['uid'];
        $mA["###uidSelected###"] = key($selected);
        $query = $this->savlibrary->extObj->cObj->substituteMarkerArrayCached($query, $mA, array(), array() );      
  			$res = $GLOBALS['TYPO3_DB']->sql_query($query);
  		  $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
  		  // check if the makeExtLink is required
  		  if ($config['func'] == 'makeExtLink') {
  		    $params['ext'] = $config['ext'];
  		    $params['id'] = $config['id'];
   		    return $this->savlibrary->makeExtLink(stripslashes($row['label']), $row['uid'], $params);
        } else {
  		    return $row['label'];
        }
    	} else {
        // Search for the key
        $keyFound = 0;
        foreach ($config['items'] as $key => $item) {
          if ($item['uid'] == $config['_value']) {
            $keyFound = $key;
            break;
          }
        }

        // get the field from the label field of the allowed table.
        if (isset($config['codeArray'])) {
          $code = ((int) ($config['items'][$keyFound]['code']/100))*100;
          if(!($config['codeArray'][$code])) {
  				  $htmlArray[] = '<b>'.$config['items'][$keyFound]['label'].'</b>';
          } else {
  				  $htmlArray[] = '<b>'.$config['items'][$config['codeArray'][$code]]['label'].'</b> ';
  				  $htmlArray[] = $config['items'][$keyFound]['label'];
          }
        } else {
  			 $htmlArray[] = $config['items'][$keyFound]['label'];
  		  } 
  		  
        return $this->savlibrary->arrayToHTML($htmlArray);
      }
    }
  }
  
 	/**
	 * Mutiple db relation selector box viewer in edit mode
	 *
	 * @param $config array (Configuration array)
	 *
	 * @return string (item to display)
	 */	  
  public function viewDbRelationSingleSelectorMultipleEditMode(&$config) {
//debug($config,'viewDbRelationSingleSelectorMultipleEditMode');
  
    $htmlArray = array();
    
		$htmlArray[] = '<select multiple name="'.$config['elementControlName'].'[]" size="'.$config['size'].'" onchange="document.changed=1">';
		if ($config['emptyitem']) {
      $htmlArray[] = '<option value="0"></option>';
    }
		foreach($config['items'] as $key => $item) {
			$sel = ($item['selected']) ? ' selected="selected"' : '';
			$htmlArray[] = '<option '.$sel.' value="'.$item['uid'].'">'.stripslashes($item['label']).'</option>';
		}
		$htmlArray[] = '</select>';

		return $this->savlibrary->arrayToHTML($htmlArray);
	}




 	/**
	 * Double-window db relation selector box viewer
	 *
	 * @param $config array (Configuration array)
	 *
	 * @return string (item to display)
	 */	 
 	public function viewDbRelationDoubleWindowSelector(&$config) {
  
    $htmlArray = array();
       	
    $htmlArray[] = $this->viewDbRelationSingleSelectorMultiple($config);
    
		return $this->savlibrary->arrayToHTML($htmlArray);
  }

 	/**
	 * Double-window db relation selector box viewer in edit mode
	 *
	 * @param $config array (Configuration array)
	 *
	 * @return string (item to display)
	 */	  	
 	public function viewDbRelationDoubleWindowSelectorEditMode(&$config) {
//debug($config,'viewDbRelationDoubleWindowSelectorEditMode');
  
    $htmlArray = array();
    

		$elementControlName = $this->savlibrary->formName.'['.$config['_field'].']'.(isset($this->savlibrary->rowItem) ? '['.$this->savlibrary->rowItem.']' : '['.($config['mm_uid_local'] ?  $config['mm_uid_local'] : $config['uid']).']');

		$class = ($config['classhtmltag'] ? 'class="'.$config['classhtmltag'].'" ' : 'class="multiple" ');
		$style = ($config['stylehtmltag'] ? 'style="'.$config['stylehtmltag'].'" ' : '');

    $fieldName = $config['field'].(isset($this->savlibrary->rowItem) ? '['.$this->savlibrary->rowItem.']' : '');
    $sort = ($config['orderselect'] ? 1 : 0);
		$htmlArray[] = '<select '.$class.$style.'multiple name="'.$elementControlName.'[]" ondblclick="move(\''.$this->savlibrary->formName.'\', \''.$elementControlName.'[]\', \''.$fieldName.'\','.$sort.');" size="'.$config['maxitems'].'" onchange="document.changed=1">';
		foreach($config['items'] as $key => $item) {
			if($item['selected']) {
			 $htmlArray[] = '<option value="'.$item['uid'].'">'.stripslashes($item['label']).'</option>';
      }
		}
		$htmlArray[] = '</select>';

		$htmlArray[] = '&nbsp;&nbsp;<select '.$class.$style.'multiple name="'.$fieldName.'" ondblclick="move(\''.$this->savlibrary->formName.'\', \''.$fieldName.'\', \''.$elementControlName.'[]\','.$sort.');" size="'.$config['maxitems'].'" onchange="document.changed=1">';
		foreach($config['items'] as $key => $item) {
			if(!$item['selected']) {
			 $htmlArray[] = '<option value="'.$item['uid'].'">'.stripslashes($item['label']).'</option>';
      }
		}
		$htmlArray[] = '</select>';
		
		// Add it to the select list for Javascript
    $this->savlibrary->selectList .= 'selectAll(\''.$this->savlibrary->formName.'\', \''.$elementControlName.'[]\');';

		return $this->savlibrary->arrayToHTML($htmlArray);
	}




 	/**
	 * General db relation selector box viewer
	 *
	 * @param $config array (Configuration array)
	 *
	 * @return string (item to display)
	 */	 
  public function viewDbRelationSelectorGlobal(&$config) {
//debug($config,'viewDbRelationSelectorGlobal');
  
    $htmlArray = array();
    
    $query = $this->savlibrary->extObj->extConfig['queries'][$this->savlibrary->formConfig['query']];

		$foreign_table = $config['foreign_table'];
		$MM_table = $config['MM'];
		$table = $config['table'];
    $MM_field = $config['mm_field'] ? $config['mm_field'] : 'uid';
    
    $uid = $config['uid'];
		$selected = array();

    // Redisplay the item if an error in new form is detected
    if ($this->savlibrary->errorInForm) {
      $selected = array($config['_value'] => 1);
    } elseif ($uid) {
    
      // Special processing when a submit icon is added       
      if ($config['addedit']) {
        if (!$config['MM'] && $config['maxitems']>1) {
            $temp = explode(',', $config['_value']);

            foreach ($temp as $v) {
              $selected = $selected + array($v => 1);          
            }
        } else {
            $selected = $selected + array($config['_value'] => 1);
        }    
      } else {

        // The record exits, just read it.

  		  $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
  				  /* SELECT   */	'*'.($config['mm_field'] ? ','.$table.'.'.$MM_field.' as mm_uid_local' : '').
  						  '',		
  				  /* FROM     */	$table.','.$foreign_table.
               ($MM_table ? ','.$MM_table : '').
  						  '',
  	 			  /* WHERE    */	'1'.
                ($MM_table ? ' AND '.$MM_table.'.uid_local='.$table.'.'.$MM_field.
  						              ' AND '.$MM_table.'.uid_foreign='.$foreign_table.'.uid'
	 					            : ' AND '.$table.'.'.$config['field'].'='.$foreign_table.'.uid'
               ).
  						  ' AND '.$table.'.uid='.intval($uid).
  			        ($config['overrideenablefields'] ? '' : $this->savlibrary->extObj->cObj->enableFields($table)).
  			        (($this->savlibrary->extObj->cObj->data['pages'] && !$config['overridestartingpoint']) ? ' AND '.$table.'.pid IN ('.$this->savlibrary->extObj->cObj->data['pages'].')' : '').
  						  ($config['where'] ? ' AND '.$config['where'] : '').
  						  '',
  				  /* GROUP BY */	
  						  '',
  				  /* ORDER BY */	($MM_table ? $MM_table.'.sorting' : '').
  						  '',
  				  /* LIMIT    */	''
  		  );

  		  // get all selected fields
  		  while ($rows = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
          $config['mm_uid_local'] = $rows['mm_uid_local'];
          if (!$config['MM'] && $config['maxitems']>1) {
            $temp = explode(',', $rows[$config['field']]);
            foreach ($temp as $v) {
              $selected = $selected + array($v => 1);          
            }
          } else {
            $selected = $selected + array($rows['uid'] => 1);
          }
  		  }
  		}
    }	else {
      if (isset($config['selected'])) {
        $selected = $selected + array($config['selected'] => 1);
      } elseif (!$config['MM'] && $config['maxitems']>1) {
        $temp = explode(',', $config['_value']);

        foreach ($temp as $v) {
          $selected = $selected + array($v => 1);
        }
      }
    }  

		// get the label of the allowed_table
		$label = ($config['labelselect'] ? $config['labelselect'] : $GLOBALS['TCA'][$foreign_table]['ctrl']['label']);
		$order = ($GLOBALS['TCA'][$foreign_table]['ctrl']['sortby'] ? $GLOBALS['TCA'][$foreign_table]['ctrl']['sortby'] : str_replace('ORDER BY','', $GLOBALS['TCA'][$foreign_table]['ctrl']['default_sortby']));

    
    // Process the foreign_table_where
    if ($config['foreign_table_where']) {
      preg_match('/^AND (.*)? ORDER BY (.*)$/', $config['foreign_table_where'], $match);  
    }
    if (!$config['whereselect'] && $match[1]) {
      $config['whereselect'] = $match[1];
    }
    if (!$config['orderselect'] && $match[2]) {
      $config['orderselect'] = $match[2];
    }

    // Process tags in whereselect clause				
    $config['whereselect'] = $this->savlibrary->queriers->processWhereClause($config['whereselect']); 

		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
				/* SELECT   */	'*'.($config['aliasselect'] ? ','.$config['aliasselect'] : '').
						'',		
				/* FROM     */	$foreign_table.($config['additionaltableselect'] ? ','.$config['additionaltableselect'] : '').
						'',
				/* WHERE    */	'1'.
 			      ($config['overrideenablefields'] ? '' : $this->savlibrary->extObj->cObj->enableFields($foreign_table)).
 			      (($this->savlibrary->extObj->cObj->data['pages'] && !$config['overridestartingpoint']) ? ' AND '.$foreign_table.'.pid IN ('.$this->savlibrary->extObj->cObj->data['pages'].')' : '').
						($config['whereselect'] ? ' AND '.$config['whereselect'] : '').
						'',
				/* GROUP BY */	
						'',
				/* ORDER BY */	
				    ($config['orderselect']? $config['orderselect']: $order).
						'',
				/* LIMIT    */	''
		);

		if (!isset($config['items'])) {
		  if($config['addedit'] && $config['addedit'] && $config['singlewindow'] && !$config['MM'] && $config['maxitems']>1) {
        $config['items'][0] = array('uid'=>0, 'label'=>'', 'selected' => $selected[0]);
      } else {
        $config['items'] = array();
      }     
    } else {
      $config['items'][0] = array('uid'=>0,'label'=>'');
    }
    $items = $config['items'];
    
    $cpt= count($config['items']);

		while ($rows = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {

		  if ($rows[$label]) {
		    // Check if special fields were added
		    if ($config['specialfields']) {
          $specialfields = explode(',', $config['specialfields']);
          foreach ($specialfields as $specialfield) {
            $special[$specialfield] = $rows[$specialfield];
          }
        }
    
        // process the tags in the label
        if (preg_match_all('/###([^#]+)###/', $rows[$label], $matches)) {
          foreach($matches[1] as $keyMatch=>$valueMatch) {
            $replaceValue = ($GLOBALS['TCA'][$foreign_table]['columns'][$valueMatch]['config']['type']=='select' ? $this->savlibrary->getLL_db($GLOBALS['TCA'][$foreign_table]['columns'][$valueMatch]['config']['items'][$rows[$valueMatch]][0]) : $rows[$valueMatch]);         
            $rows[$label] = str_replace($matches[0][$keyMatch], $replaceValue, $rows[$label]);
          }
        }    
        
			  $config['items'][$cpt] = array(
			              'uid' => $rows['uid'],
                    'label'=> htmlentities($GLOBALS['TCA'][$foreign_table]['columns'][$label]['config']['type']=='select' ? $this->savlibrary->getLL_db($GLOBALS['TCA'][$foreign_table]['columns'][$label]['config']['items'][$rows[$label]][0]) : $rows[$label]),
                    'selected' => $selected[$rows['uid']],
                    'code' => $rows[$config['code']],
                    'style' => (($config['optionCond'] && $rows[$config['optionCond']]) ? $config['optionStyle'] : ''),
                    'special' => (is_array($special) ? $special : ''),
                    );
        if($config['code']) {
          $config['codeArray'][$rows[$config['code']]] = $cpt;
        }

        // Remove if not allowed
        if ($label == $this->savlibrary->conf['inputAdminField'] && $config['edit'] && !$this->savlibrary->userIsAdmin($rows)) {
					unset($config['items'][$cpt]);
				}
				$cpt++;
      }
		}

		if ($MM_table) {
		  // change the number of dispayed items if only one selected. In general, it occurs 
		  // when a where is added in the field parameter to select only one field
		  if (count($config['items']) == 1){
		    $config['size'] = 1;
		    $viewItem = ($config['edit'] ? 'viewDbRelationSingleSelectorMultipleEditMode' : 'viewDbRelationSingleSelectorMultiple');
        return $this->$viewItem($config);
		  } else {
		    $viewItem = ($config['edit'] ? 'viewDbRelationDoubleWindowSelectorEditMode' : 'viewDbRelationDoubleWindowSelector');
        return $this->$viewItem($config);
      }				
		} else {
			if ($config['content']) {

        // get the field from a query. The uid marker is replaced by the selected value
        $query = $config['content']; 
        $mA["###uid###"] = intval($config['uid']);
        $mA["###uidSelected###"] = key($selected);
        $mA['###cruser###'] = $GLOBALS['TSFE']->fe_user->user['uid'];
        $mA['###user###'] = $GLOBALS['TSFE']->fe_user->user['uid'];
        $query = $this->savlibrary->extObj->cObj->substituteMarkerArrayCached($query, $mA, array(), array() );

        $config['items'] = $items;
  			$res = $GLOBALS['TYPO3_DB']->sql_query($query);
  			if (!is_array($config['items'])) {
          $config['items'] = array();
        } 
		    while ($rows = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {     
			    $config['items'][] =  array(
			                'uid' => $rows['uid'],
                      'label'=> htmlentities(stripslashes($rows['label'])),
                      'selected' => ($config['selected'] ? 1 : $selected[$rows['uid']])
                      );
		    }
      } 

      if ($config['foreign_table'] && $config['maxitems']>1) {
        if ($config['singlewindow']) {
		      $viewItem = ($config['edit'] ? 'viewDbRelationSingleSelectorMultipleEditMode' : 'viewDbRelationSingleSelectorMultiple');        
        } else {
  		    $viewItem = ($config['edit'] ? 'viewDbRelationDoubleWindowSelectorEditMode' : 'viewDbRelationDoubleWindowSelector');
        }
      } else {    
		    $viewItem = ($config['edit'] ? 'viewDbRelationSingleSelectorEditMode' : 'viewDbRelationSingleSelector');
		  }

		  $htmlArray[] = $this->$viewItem($config);
		  
      return $this->savlibrary->arrayToHTML($htmlArray);
		} 
  }

 	/**
	 * General db relation selector box viewer in edit mode
	 *
	 * @param $config array (Configuration array)
	 *
	 * @return string (item to display)
	 */	 
  public function viewDbRelationSelectorGlobalEditMode(&$config) {
  
    $htmlArray = array();
    
		$htmlArray[] = $this->viewDbRelationSelectorGlobal($config);
		
    return $this->savlibrary->arrayToHTML($htmlArray);
  } 




 	/**
	 * General db relation element browser viewer
	 *
	 * @param $config array (Configuration array)
	 *
	 * @return string (item to display)
	 */	
  public function viewDbRelationElementBrowser(&$config) {
//debug($config,'viewDbRelationElementBrowser','','',10);
  
    $htmlArray = array();
    
		$allowed_table = $config['allowed'];
		$config['subform'] = '_subForm';
		
    // Check if there exists a MM relation. It generates a subform
    if ($config['MM'] || $config['norelation']) {
      $uid = $this->savlibrary->uid;

      // Get the number of items satisfying the query with no limit field
      $rows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			 	/* SELECT   */   'count(*) as nbitem',		
				/* FROM     */   $allowed_table.($config['norelation'] ? '' : ','.$config['MM']),
	 			/* WHERE    */   '1'.
 			                   $this->savlibrary->extObj->cObj->enableFields($allowed_table).
	 			                 ($config['norelation'] ? '' : ' AND '.$allowed_table.'.uid='.$config['MM'].'.uid_foreign'.
	 			                 ' AND '.$config['MM'].'.uid_local='.intval($uid)).
	 			                 ($config['errors']['_subFormId'] ? ' AND '.$allowed_table.'.uid='.$config['errors']['_subFormId'] : '').
                         ($config['where'] ? ' AND '.$this->savlibrary->queriers->processWhereClause($config['where']) : '') ,
				/* GROUP BY */	 '',
				/* ORDER BY */	 '',
				/* LIMIT    */	 ''
		  );
  	  $nbitem = $rows[0]['nbitem'];

		  $order = ($GLOBALS['TCA'][$allowed_table]['ctrl']['sortby'] ? $GLOBALS['TCA'][$allowed_table]['ctrl']['sortby'] : str_replace('ORDER BY','', $GLOBALS['TCA'][$allowed_table]['ctrl']['default_sortby']));
      $maxSubItems = (isset($config['maxsubitems']) ? $config['maxsubitems'] : $config['maxitems']);
   
      $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			 	/* SELECT   */   $allowed_table.'.*',		
				/* FROM     */   $allowed_table.($config['norelation'] ? '' : ','.$config['MM']),
	 			/* WHERE    */   '1'.
 			                   $this->savlibrary->extObj->cObj->enableFields($allowed_table).
	 			                 ($config['norelation'] ? '' : ' AND '.$allowed_table.'.uid='.$config['MM'].'.uid_foreign'.
	 			                 ' AND '.$config['MM'].'.uid_local='.intval($uid)).
	 			                 ($config['errors']['_subFormId'] ? ' AND '.$allowed_table.'.uid='.$config['errors']['_subFormId'] : '').
                         ($config['where'] ? ' AND '.$this->savlibrary->queriers->processWhereClause($config['where']) : '') ,
				/* GROUP BY */	 '',
				/* ORDER BY */	 ($config['addupdown'] ? 'sorting' : ($config['order'] ? $config['order'] : $order)),
				/* LIMIT    */	 ($maxSubItems ? ($maxSubItems*($this->savlibrary->limitSub[$config['_field']])).','.($maxSubItems) : '')
		  );

		  $fields = $config['_field'];
      $value = '';
      
      // Build the subForm
      $subForm = array();            
      $subForm['TYPE']= 'subForm';
     
      // add the new button
      $subForm['CUTTERS']['CUT_title'] = ($this->savlibrary->inputIsAllowedInForm() || (!$config['edit'] && $config['labelontitle']) ? 0 : 1);
      $subForm['MARKERS']['titleIconLeft'] = (!$config['edit'] || ($config['cutnewbuttonifnotsaved'] && !$this->savlibrary->uid) ? '' : $this->savlibrary->newButtonSubForm($this->savlibrary->formName, $uid, $config['_field']));
      $subForm['MARKERS']['CLASS_titleIconLeft'] = ($this->savlibrary->inputIsAllowedInForm() ? 'subitemTitleIconLeft' : 'subItemtitleIconLeftVoid');
      if ($config['labelontitle']) {     
  		  $subForm['MARKERS']['formTitle'] = $this->savlibrary->getLL_db('LLL:EXT:'.$this->savlibrary->extObj->extKey.'/locallang_db.xml:'.$config['_field']);      
      } else {
        $subForm['MARKERS']['formTitle'] = $this->savlibrary->processLocalizationTags($config['subformtitle']);
      }
  
 		  // add a new row if the new button has been activated
      if ($this->savlibrary->newSubForm && $this->savlibrary->subFormName == $config['_field']) {
    		  $this->savlibrary->rowItem = 0;
    		  $row = array();
              		  
    		  // Add the field that were kept in the subform
          if (isset($config['keepfieldsinsubformvalues'])) {
            $row += $config['keepfieldsinsubformvalues'];          
          }
          
          // Remove the values that may come from the parent form 
          $temp = $this->savlibrary->queriers->sql_fetch_assoc_with_tablename($res);

          if (is_array($temp)) {
            foreach ($temp as $keyTemp => $valueTemp) {
              unset($row[$keyTemp]);
            }
          }
          
          // Check fields are set in the subform
          if (!isset($config[0])) {
            $out = '<span class="error">'.$this->savlibrary->getLibraryLL('error.noFieldSelectedInSubForm').'</span>';
            return $out;
          }

		      $x = $this->savlibrary->generateFormTa($config['name'], $row, array(0 => $config[0]), $config['errors'], $config['edit']);
          $x['TYPE']= 'subFormItem';
          foreach ($x['REGIONS']['items'] as $key => $val) {
            $x['REGIONS']['items'][$key]['MARKERS']['icon'] = '';
            $x['REGIONS']['items'][$key]['MARKERS']['CLASS_iconLeft'] = 'iconLeftVoid';
          }
          $value .= $this->savlibrary->replaceTemplate($x);
          $cutLeft = 1;
          $cutRight = 1;
      } else {  
  
        // return empty if no rows and not newSubform and no arrows
        if (!$config['edit'] && !$this->savlibrary->newSubForm && !$nbitem && !isset($this->savlibrary->limitSub[$config['_field']])) {
          return '';
        }
         // Parse the fields
        $cpt = 0;
  		  while ($row = $this->savlibrary->queriers->sql_fetch_assoc_with_tablename($res)) {
          // Add the field kept from the parent form
          if (isset($config['keepfieldsinsubformvalues'])) {
              $row += $config['keepfieldsinsubformvalues'];      
          }
 		    
  		    // Process the field
    		  $this->savlibrary->rowItem = $row[$allowed_table.'.uid'];
    		  
          // Check fields are set in the subform
          if (!isset($config[0])) {
            $out = '<span class="error">'.$this->savlibrary->getLibraryLL('error.noFieldSelectedInSubForm').'</span>';
            return $out;
          }
  		  
		      $x = $this->savlibrary->generateFormTa($config['name'], $row, array(0 => $config[0]), $config['errors'], $config['edit']);
          $x['TYPE'] = 'subFormItem';          

          // add the up and down icon
          if ($config['addupdown'] || $config['adddelete']) {
            $iconClass = 'itemIconLeft';
          } else {
            $iconClass = 'itemIconLeftVoid';
          }
          foreach ($x['REGIONS']['items'] as $key => $val) {
            $x['REGIONS']['items'][$key]['MARKERS']['icon'] = '';
            $x['REGIONS']['items'][$key]['MARKERS']['CLASS_iconLeft'] = $iconClass;
          }

          if ($config['addupdown']) {
            if ($cpt ==0) {
              $x['REGIONS']['items'][0]['MARKERS']['icon'] .= $this->savlibrary->downButton($this->savlibrary->formName, $uid, $this->savlibrary->rowItem, $config['_field']);
            } elseif ($cpt == $nbitem -1) {
              $x['REGIONS']['items'][0]['MARKERS']['icon'] .= $this->savlibrary->upButton($this->savlibrary->formName, $uid, $this->savlibrary->rowItem, $config['_field']);            
            } else {
              $x['REGIONS']['items'][0]['MARKERS']['icon'] .= $this->savlibrary->downButton($this->savlibrary->formName, $uid, $this->savlibrary->rowItem, $config['_field']).$this->savlibrary->upButton($this->savlibrary->formName, $uid, $this->savlibrary->rowItem, $config['_field']);                        
            }
            $cpt++;
          }
          if ($config['adddelete']){
            $x['REGIONS']['items'][0]['MARKERS']['icon'] .= ($x['REGIONS']['items'][0]['MARKERS']['icon'] ? '<br />' : '').$this->savlibrary->deleteItemButton($this->savlibrary->formName, $uid, $this->savlibrary->rowItem, $config['_field']);          
          }
          $value .= $this->savlibrary->replaceTemplate($x);
        }  		  
		  
  		  // arrow selectors
  		  $cutLeft = 0;
  		  $cutRight = 0;
  		  if($errors['_subFormId']){
   		    $cutLeft = 1;
  		    $cutRight = 1;
        }
  		  if ($this->savlibrary->limitSub[$config['_field']] > 0) {
          $left = $this->savlibrary->leftArrowButtonSubForm($this->savlibrary->formName, $this->savlibrary->limitSub[$config['_field']] - 1, $uid, $config['_field']);
  	 	  } else {
          $left = '';
          $cutLeft = 1;
        }

    		if($maxSubItems && ($this->savlibrary->limitSub[$config['_field']] + 1)*$maxSubItems < $nbitem ) {
          $right = $this->savlibrary->rightArrowButtonSubForm($this->savlibrary->formName, $this->savlibrary->limitSub[$config['_field']] + 1, $uid, $config['_field']);
    		} else {
    			$right = '';
      		$cutRight = 1;
    		}
    	}

    	$subForm['MARKERS']['arrows'] = $left.$right;
    	$subForm['CUTTERS']['CUT_arrows'] = ($cutRight && $cutLeft ) ? 1 : 0;
  		$subForm['MARKERS']['Value'] = $value;
      $htmlArray[] = $this->savlibrary->replaceTemplate($subForm);  
      
      // unset rowItem
      unset($this->savlibrary->rowItem);
         
    } else {
   
      $row = array();
      if ($config['_value']) {
        $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
  			 	/* SELECT   */	'*',		
  				/* FROM     */	$allowed_table,
  	 			/* WHERE    */	'1'.
 			                    $this->savlibrary->extObj->cObj->enableFields($allowed_table).
                          ' AND uid='.$config['_value']
  		  );
      	$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
      }
   
      // Add the field kept from the parent form
      if (isset($config['keepfieldsinsubformvalues']) && is_array($row)) {
          $row += $config['keepfieldsinsubformvalues'];      
      }
       
		  $fields = $config['_field'];
      $value = '';
      
      // Build the subForm
      if (isset($config[0])) {
        if ($config['func'] == 'makeLink') { 

          $temp = explode(',', $row['link_groups']);          
          if ($config['cutvalue'] || !count(array_intersect($temp, $GLOBALS['TSFE']->fe_user->groupData['uid'])) || ($row['link_enddate'] && $row['link_enddate'] < time() )) {
            $htmlArray[] = '';
          } else {       
            $htmlArray[] = $this->savlibrary->extObj->pi_linkToPage(($config['message'] ? $config['message'] : $this->savlibrary->getLibraryLL('general.clickHere')),$row['link_page']);
          }
        } else {
          $subForm = array();            
          $subForm[TYPE]= 'subForm';
     
          // Set the CUTTERS
          $subForm['CUTTERS']['CUT_title'] = 1;
      	  $subForm['CUTTERS']['CUT_arrows'] = 1;
    	
          // Parse the fields
      	  $this->savlibrary->rowItem = $config['_value'];
		      $x = $this->savlibrary->generateFormTa($config['name'], $row, array(0 => $config[0]), $config['errors'], $config['edit']);

          $x['TYPE']= 'subFormItem';
          $x['MARKERS']['icon'] = '';
          $x['MARKERS']['CLASS_iconLeft'] = 'iconLeftVoid';
          $value .= $this->savlibrary->replaceTemplate($x);
		  
  		    $subForm['MARKERS']['Value'] = $value;
          $htmlArray[] = $this->savlibrary->replaceTemplate($subForm); 
        }
      } else {
        $htmlArray[] = '<span class="error">'.$this->savlibrary->getLibraryLL('error.noFieldSelectedInSubForm').'</span>';
      } 
		}
    return $this->savlibrary->arrayToHTML($htmlArray);
  } 

 	/**
	 * General db relation element browser viewer in edit mode
	 *
	 * @param $config array (Configuration array)
	 *
	 * @return string (item to display)
	 */	
  public function viewDbRelationElementBrowserEditMode(&$config){
  
    $htmlArray = array();
    
		$htmlArray[] = $this->viewDbRelationElementBrowser($config);		  

    return $this->savlibrary->arrayToHTML($htmlArray);
  } 

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/sav_library/class.tx_savlibrary_defaultItemviewers.php']) {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/sav_library/class.tx_savlibrary_defaultItemviewers.php']);
}

?>
