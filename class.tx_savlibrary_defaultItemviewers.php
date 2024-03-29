<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009 Laurent Foulloy <yolf.typo3@orange.fr>
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
 * @author	Laurent Foulloy <yolf.typo3@orange.fr>
 *
 */

class tx_savlibrary_defaultItemviewers {

  // Variables in calling classes
  protected $savlibrary;      // Reference to the savlibrary object
  protected $cObj;            // Reference to the cObj in the extension
  protected $extConfig;       // Reference to the extension configuration
  protected $extKey;          // Extension Key

  // Variables for the RTE API
	public $RTEObj;
	public $RTEinit = 0;
	public $docLarge = 1;
	public $RTEcounter = 0;
	public $additionalJS_initial = '';		// Initial JavaScript to be printed before the form (should be in head, but cannot due to IE6 timing bug)
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

  // Variables for the graphs
  private $imageCounter = 0;       // image counter for graph

  /**
   * Init vars
   *
   * @param $ref (reference to the calling object)
   *
   * @return none
   */
  public function initVars(&$ref) {
    $this->savlibrary = $ref;
    $this->extConfig = &$ref->extObj->extConfig;
    $this->cObj = &$ref->extObj->cObj;
    $this->extKey = $ref->extObj->extKey;
  }


	/**
	 * String Input viewer
	 *
	 * @param $config array (Configuration array)
	 *
	 * @return string (item to display)
	 */	      
  public function viewStringInput(&$config) {

    $htmlArray = array();
    
    if (!$config['value'] && !$config['keepzero']) {
      $htmlArray[] = '';
    } elseif ($config['tsobject']) {
      $htmlArray[] = $config['value'];
    } else {
      $htmlArray[] = nl2br(stripslashes($config['value']));
    }

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
    
		if ($config['default']) {
      $ondblclick = 'this.value=\'' .
        (
          !$config['value'] ?
          stripslashes($config['default']) :
          stripslashes($config['value'])
        ) .
        '\';';
    } else {
		  $ondblclick = '';
    }

    // Adds the Input text element
    $htmlArray[] = utils::htmlInputTextElement(
      array(
        utils::htmlAddAttribute('name', $config['elementControlName']),
        utils::htmlAddAttributeIfNotNull('class', $config['classhtmltag']),
        utils::htmlAddAttributeIfNotNull('style', $config['stylehtmltag']),
        utils::htmlAddAttribute('value', stripslashes($config['value'])),
        utils::htmlAddAttribute('size', $config['size']),
        utils::htmlAddAttribute('onchange', 'document.changed=1;'),
        utils::htmlAddAttributeIfNotNull('ondblclick', $ondblclick),
      )
    );
    
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

    // Adds the input password element
    $htmlArray[] = utils::htmlInputPasswordElement(
      array(
        utils::htmlAddAttribute('name', $config['elementControlName']),
        utils::htmlAddAttributeIfNotNull('class', $config['classhtmltag']),
        utils::htmlAddAttributeIfNotNull('style', $config['stylehtmltag']),
        utils::htmlAddAttribute('value', stripslashes($config['value'])),
        utils::htmlAddAttribute('size', $config['size']),
        utils::htmlAddAttribute('onchange', 'document.changed=1;'),
      )
    );
    
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

    $htmlArray = array();
    
    if (is_array($config['items'])) {
      $cols = ($config['cols'] ? $config['cols'] : 1);

      $cpt = 0;
      $cptItem = 0;
      $val = $config['value'];
      foreach ($config['items'] as $key => $value) {
        $checked = ($val&0x01 ? 'checked' : '');
        $val = $val >> 1;
        
        $messageValue = utils::htmlSpanElement(
          array(
            utils::htmlAddAttribute('class', 'checkboxMessage'),
          ),
          stripslashes($this->savlibrary->getLL_db($value[0]))
        );
          
        if ($config['displayasimage']) {

          $messageIfChecked = utils::htmlDivElement(
            array(
              utils::htmlAddAttribute('class', 'checkbox item' . $key),
            ),
            utils::htmlImgElement(
              array(
                utils::htmlAddAttribute('class', 'checkboxSelected'),
                utils::htmlAddAttribute('src', $this->savlibrary->iconsDir . 'checkboxSelected.gif'),
                utils::htmlAddAttribute('title', $this->savlibrary->getLibraryLL('itemviewer.checkboxSelected')),
                utils::htmlAddAttribute('alt', $this->savlibrary->getLibraryLL('itemviewer.checkboxSelected')),
              )
            ) . $messageValue
          );
          $messageIfNotChecked = utils::htmlDivElement(
            array(
              utils::htmlAddAttribute('class', 'checkbox item' . $key),
            ),
            utils::htmlImgElement(
              array(
                utils::htmlAddAttribute('class', 'checkboxNotSelected'),
                utils::htmlAddAttribute('src', $this->savlibrary->iconsDir . 'checkboxNotSelected.gif'),
                utils::htmlAddAttribute('title', $this->savlibrary->getLibraryLL('itemviewer.checkboxNotSelected')),
                utils::htmlAddAttribute('alt', $this->savlibrary->getLibraryLL('itemviewer.checkboxNotSelected')),
              )
            ) . $messageValue
          );

        } else {
          $messageIfChecked = utils::htmlDivElement(
            array(
              utils::htmlAddAttribute('class', 'checkbox item' . $key),
            ),
            utils::htmlSpanElement(
              array(
                utils::htmlAddAttribute('class', 'checkboxSelected'),
              ),
              $this->savlibrary->getLibraryLL('itemviewer.yesMult', ' ')
            ) . $messageValue
          );
          $messageIfNotChecked = utils::htmlDivElement(
            array(
              utils::htmlAddAttribute('class', 'checkbox item' . $key),
            ),
            utils::htmlSpanElement(
              array(
                utils::htmlAddAttribute('class', 'checkboxNotSelected'),
              ),
              $this->savlibrary->getLibraryLL('itemviewer.noMult', ' ')
            ) . $messageValue
          );
        }

        // Checks if donotdisplayifnotchecked is set
        if ($config['donotdisplayifnotchecked']) {
          $messageIfNotChecked = '';
        }
            
        $htmlArray[] = ($checked ? $messageIfChecked : $messageIfNotChecked);
        
        $cpt++;  
        $cptItem++;
        if ($cptItem == $config['nbitems']){
          break;
        }
        if ($cpt == $cols){
          // Adds the br element
          $htmlArray[] = utils::htmlBrElement(
            array(
              utils::htmlAddAttribute('class', 'checkbox'),
            )
          );
          
          // Resets the counter
          $cpt = 0;
        }    
      }
    } else {
    
      if ($config['displayasimage']) {

        $messageIfChecked = utils::htmlDivElement(
          array(
            utils::htmlAddAttribute('class', 'checkbox' . $key),
          ),
          utils::htmlImgElement(
            array(
              utils::htmlAddAttribute('class', 'checkboxSelected'),
              utils::htmlAddAttribute('src', $this->savlibrary->iconsDir . 'checkboxSelected.gif'),
              utils::htmlAddAttribute('title', $this->savlibrary->getLibraryLL('itemviewer.radioSelected')),
              utils::htmlAddAttribute('alt', $this->savlibrary->getLibraryLL('itemviewer.radioSelected')),
            )
          )
        );
        $messageIfNotChecked = utils::htmlDivElement(
          array(
            utils::htmlAddAttribute('class', 'checkbox' . $key),
          ),
          utils::htmlImgElement(
            array(
              utils::htmlAddAttribute('class', 'checkboxNotSelected'),
              utils::htmlAddAttribute('src', $this->savlibrary->iconsDir . 'checkboxNotSelected.gif'),
              utils::htmlAddAttribute('title', $this->savlibrary->getLibraryLL('itemviewer.radioNotSelected')),
              utils::htmlAddAttribute('alt', $this->savlibrary->getLibraryLL('itemviewer.radioNotSelected')),
            )
          )
        );
      } else {
        $messageIfChecked = $this->savlibrary->getLibraryLL('itemviewer.yes');
        $messageIfNotChecked = (
          $config['donotdisplayifnotchecked'] ?
          '' :
          $this->savlibrary->getLibraryLL('itemviewer.no')
        );
      }

      $htmlArray[] = (
        $config['value'] ?
        $messageIfChecked :
        $messageIfNotChecked
      );
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

    $htmlArray = array();
    
    if (is_array($config['items'])) {
      $cols = ($config['cols'] ? $config['cols'] : 1);
      $cpt = 0;
      $cptItem = 0;
      $val = $config['value'];
      foreach ($config['items'] as $key => $value) {
        $checked = (($val&0x01 || $value[1]==1 )? 'checked' : '');
        $val = $val >> 1;
        
        // Adds the hidden input element
        $htmlArray[] = utils::htmlInputHiddenElement(
          array(
            utils::htmlAddAttribute(
              'name',
              $config['elementControlName'] . '[' . $key . ']'
            ),
            utils::htmlAddAttribute('value', '0'),
          )
        );
        
        // Adds the checkbox input element
        $htmlArray[] = utils::htmlInputCheckBoxElement(
          array(
            utils::htmlAddAttribute(
              'name',
              $config['elementControlName'] . '[' . $key . ']'
            ),
            utils::htmlAddAttribute('value', '1'),
            utils::htmlAddAttributeIfNotNull('checked', $checked),
            utils::htmlAddAttribute('onchange', 'document.changed=1;'),
          )
        );

        // Adds the span element
        $htmlArray[] = utils::htmlSpanElement(
          array(
            utils::htmlAddAttribute('class', 'checkbox'),
            $value['addattributes'],
          ),
          stripslashes($this->savlibrary->getLL_db($value[0]))
        );
          
        $cpt++;  
        $cptItem++;
        if ($cptItem == $config['nbitems']){
          break;
        }
        if ($cpt == $cols){
        
          // Adds the br element
          $htmlArray[] = utils::htmlBrElement(
            array(
              utils::htmlAddAttribute('class', 'checkbox'),
            )
          );
          
          // Resets the counter
          $cpt = 0;
        }    
      }
    } else {
      // Only one checkbox	
      if ($config['value'] == 1) {
        $checked = 'checked';
      } else {
        if ($config['uid']) {
          $checked='';
        } else {
          $checked = ($config['default'] ? 'checked' : '');
        }
      }      	
      // Checks if it is associated with a mail
      if ($config['mail']) {

        $htmlArray[] = $this->savlibrary->mailButton(
          $this->savlibrary->formName,
          $config['cryptedFieldName'],
          (
            $config['valueforcheckmail'] ?
            $config['valueforcheckmail'] :
            !$config['value']
          ),
          $this->savlibrary->rowItem
          ) . '<div class="separator">&nbsp;</div>';
        if ($config['value']) {
        
          // Adds the hidden input element
          $htmlArray[] = utils::htmlInputHiddenElement(
            array(
              utils::htmlAddAttribute('name', $config['elementControlName']),
              utils::htmlAddAttribute('value', '0'),
            )
          );

          // Adds the checkbox input element
          $htmlArray[] = utils::htmlInputCheckBoxElement(
            array(
              utils::htmlAddAttribute('name', $config['elementControlName']),
              utils::htmlAddAttribute('value', '1'),
              utils::htmlAddAttributeIfNotNull('checked', $checked),
              utils::htmlAddAttribute('onchange', 'document.changed=1;'),
            )
          );
        } else {
          // Adds the hidden input element
          $htmlArray[] = utils::htmlInputHiddenElement(
            array(
              utils::htmlAddAttribute('name', $config['elementControlName']),
              utils::htmlAddAttribute('value', '0'),
            )
          );
        }
      } else {
        // Adds the hidden input element
        $htmlArray[] = utils::htmlInputHiddenElement(
          array(
            utils::htmlAddAttribute('name', $config['elementControlName']),
            utils::htmlAddAttribute('value', '0'),
          )
        );

        // Adds the checkbox input element
        $htmlArray[] = utils::htmlInputCheckBoxElement(
          array(
            utils::htmlAddAttribute('name', $config['elementControlName']),
            utils::htmlAddAttribute('value', '1'),
            utils::htmlAddAttributeIfNotNull('checked', $checked),
            utils::htmlAddAttribute('onchange', 'document.changed=1;'),
          )
        );
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

    $htmlArray = array();
    
    if (is_array($config['items'])) {
      $val = $config['value'];
      foreach ($config['items'] as $key => $value) {
        if ($config['displayasimage']) {
          if ($val == $value[1]) {
            $htmlArray[] = utils::htmlDivElement(
              array(
                utils::htmlAddAttribute('class', 'radio item' . $key),
              ),
              utils::htmlImgElement(
                array(
                  utils::htmlAddAttribute('class', 'radioSelected'),
                  utils::htmlAddAttribute('src', $this->savlibrary->iconsDir . 'radioSelected.gif'),
                  utils::htmlAddAttribute('title', $this->savlibrary->getLibraryLL('itemviewer.radioSelected')),
                  utils::htmlAddAttribute('alt', $this->savlibrary->getLibraryLL('itemviewer.radioSelected')),
                )
              )
            );
          } else {
            $htmlArray[] = utils::htmlDivElement(
              array(
                utils::htmlAddAttribute('class', 'radio item' . $key),
              ),
              utils::htmlImgElement(
                array(
                  utils::htmlAddAttribute('class', 'radioNotSelected'),
                  utils::htmlAddAttribute('src', $this->savlibrary->iconsDir . 'radioNotSelected.gif'),
                  utils::htmlAddAttribute('title', $this->savlibrary->getLibraryLL('itemviewer.radioNotSelected')),
                  utils::htmlAddAttribute('alt', $this->savlibrary->getLibraryLL('itemviewer.radioNotSelected')),
                )
              )
            );
          }
        } elseif ($val == $value[1]) {
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

    $htmlArray = array();
    
    if (is_array($config['items'])) {
      $cols = ($config['cols'] ? $config['cols'] : 1);
      $cols = ($config['horizontallayout'] ? count($config['items']) : $cols);

      $cpt = 0;
      $val = $config['value'];

      // Checks if one item is selected
      foreach ($config['items'] as $key => $value) {
        if ($val == $value[1]) {
          $selectedItemIkey = $key;
        }
      }
      // Sets the default if no item where selected and a default exists
      if (!isset($selectedItemIkey) && isset($config['default'])) {
        foreach ($config['items'] as $key => $value) {
          if ($config['default'] == $value[1]) {
            $selectedItemIkey = $key;
          }
        }
      }

      // Builds the radio buttons
      foreach ($config['items'] as $key => $value) {
        $checked = (isset($selectedItemIkey) && $selectedItemIkey == $key ? 'checked' : '');

        // Adds the radio input element
        $htmlArray[] = utils::htmlInputRadioElement(
          array(
            utils::htmlAddAttribute('name', $config['elementControlName']),
            utils::htmlAddAttribute('value', $value[1]),
            utils::htmlAddAttributeIfNotNull('checked', $checked),
            utils::htmlAddAttribute('onchange', 'document.changed=1;'),
          )
        );

        // Adds the span element
        $htmlArray[] = utils::htmlSpanElement(
          array(
            utils::htmlAddAttribute('class', 'left'),
            $value['addattributes'],
          ),
          stripslashes($this->savlibrary->getLL_db($value[0]))
        );
        
        $cpt++;  
        if ($cpt == $cols) {
          // Adds the BR element
          $htmlArray[] = utils::htmlBrElement(
            array(
              utils::htmlAddAttribute('class', 'radio'),
            )
          );
          
          // Resets the counter
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
	 * Code could be cleaned when tx_rtehtmlarea_pi2 will separate the id
	 * and the name.
	 *
	 * @param $config array (Configuration array)
	 *
	 * @return string (item to display)
	 */	
  public function viewTextAreaEditMode(&$config) {

    $htmlArray = array();
    
		if (isset($config['wizards']['RTE'])) {

			if(!$this->RTEObj) {
        $this->RTEObj = t3lib_div::makeInstance('tx_rtehtmlarea_pi2');
        $GLOBALS['TSFE']->additionalHeaderData['rtehtmlarea'] .=
          $this->additionalJS_initial;
      }

			if($this->RTEObj->isAvailable()) {
				$this->RTEcounter++;
				
				$pageTSConfig = t3lib_BEfunc::getPagesTSconfig($GLOBALS['TSFE']->id);
				$thisConfig = array_merge(array('rteResize' => 1), $pageTSConfig['RTE.']['default.']['FE.']);
			  $specConf = array(
          'richtext' => 1,
          'rte_transform' => array(
            'parameters' => array('flag=rte_enabled', 'mode=ts_css'),
          ),         
        );
				$PA['itemFormElName'] = $config['elementControlName'];
        $PA['itemFormElValue'] = html_entity_decode($config['value'], ENT_QUOTES);
				$out = $this->RTEObj->drawRTE(
          $this,
          '',
          '',
          $row = array(),
          $PA,
          $specConf,
          $thisConfig,
          $RTEtypeVal,
          '',
          0
        );

				// Removes the hidden field
				$out = preg_replace('/<input type="hidden"[^>]*>/', '', $out);

        // Replaces [ and ] in the id
				$out = preg_replace('/id="([^"]*)"/e',
          '\'id="\' . strtr(\'$1\', \'[]\', \'__\') . \'"\'',
          $out
        );
        
        // Adds onchange
				$out = preg_replace('/<textarea ([^>]*)>/',
          '<textarea $1 onchange="document.changed=1;">'	,
          $out
        );
				
        // Replaces height and width
        if ($config['height']) {
          $out = preg_replace(
            '/height:[^p]*/',
            'height:' . $config['height'],
            $out
          );
        }
        // Adds 45px to the first div
        $out = preg_replace('/height:([^p]*)/', 'height:$1+45', $out, 1);
        
        if ($config['width']) {
          $out = preg_replace(
            '/width:[^p]*/',
            'width:' . $config['width'],
            $out
          );
        }
        
        $htmlArray[] = $out;
        $htmlArray[] = '<script type="text/javascript">';

        // Replaces [ and ] in the id
        $htmlArray[] = preg_replace('/editornumber = "([^"]*)"/e',
          '\'editornumber = "\' . strtr(\'$1\', \'[]\', \'__\') . \'"\'',
          $this->additionalJS_post[$this->RTEcounter-1]
        );

		    $htmlArray[] = '</script>';
		    if (!$this->RTEinit) {
          $GLOBALS['TSFE']->additionalHeaderData['rtehtmlarea'] .=
            $this->additionalJS_initial;
          $this->RTEinit = 1;   
        }

        // Builds the additional header data
        $js = array();
        $js[] = '<script type="text/javascript">';
        $js[] = (
          isset($this->additionalJS_pre[0]) ?
          $this->additionalJS_pre[0] :
          $this->additionalJS_pre['rtehtmlarea-loadJScode']
        );
		    $js[] = '</script>';
        $GLOBALS['TSFE']->additionalHeaderData['rtehtmlarea'] .= implode('', $js);

        // Corrects the bug #0017763
        if ($this->RTEcounter == 1) {
          $this->additionalHeaderData_rtehtmlarea = $GLOBALS['TSFE']->additionalHeaderData['rtehtmlarea'];
        } else {
          $GLOBALS['TSFE']->additionalHeaderData['rtehtmlarea'] = $this->additionalHeaderData_rtehtmlarea;
        }

        // Replaces [ and ] in the id
		    $this->updateRTEList .= preg_replace('/RTEarea\[\'([^\']*)\'\]/e',
          '\'RTEarea[\\\'\' . strtr(\'$1\', \'[]\', \'__\') . \'\\\']\'',
          $this->additionalJS_submit[$this->RTEcounter-1]
        );

		    $this->changedRTEList .= 'changedTextareaRTE(' . $this->RTEcounter . ');';
			}
    } else {
      // Adds the textarea element
      $htmlArray[] = utils::htmlTextareaElement(
        array(
          utils::htmlAddAttribute('name', $config['elementControlName']),
          utils::htmlAddAttributeIfNotNull('class', $config['classhtmltag']),
          utils::htmlAddAttributeIfNotNull('style', $config['stylehtmltag']),
          utils::htmlAddAttribute('cols', $config['cols']),
          utils::htmlAddAttribute('rows', $config['rows']),
          utils::htmlAddAttribute('onchange', 'document.changed=1;'),
        ),
        $config['value']
      );
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

    $htmlArray = array();
    
    if ($config['generatertf'] && $config['value']) {
      if ($config['savefilertf']) {    
        $path_parts = pathinfo($config['savefilertf']);
        $config['folder'] = $path_parts['dirname'];
        $htmlArray[] = $this->savlibrary->makeLink(
          $config['value'],
          0,
          $config
        );
      } else  {
        $this->savlibrary->addError('error.incorrectRTFSaveFileName');
      }            
    } else {
      $htmlArray[] = $this->savlibrary->makeUrlLink(
        $this->viewStringInput($config),
        '',
        $config
      );
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
  
    $htmlArray = array();
    
    // Generates the button
    if ($config['generatertf']) {
      $htmlArray[] = $this->savlibrary->generateRTFButton(
        $this->savlibrary->formName,
        $config['cryptedFieldName'],
        $this->savlibrary->rowItem
      );
      // Updates the field
      if ($config['uid']) {
        $res = $GLOBALS['TYPO3_DB']->exec_UPDATEquery(
          /* TABLE   */	$config['table'],		
          /* WHERE   */	'uid=' . intval($config['uid']),
          /* FIELDS  */	array($config['field'] => $config['value'])
        );
      }
      if ($config['value']) {
        $path_parts = pathinfo($config['savefilertf']);
        $config['folder'] = $path_parts['dirname'];
        $htmlArray[] = '<div class="separator">&nbsp;</div>' .
          utils::htmlInputHiddenElement(
            array(
              utils::htmlAddAttribute('name', $config['elementControlName']),
              utils::htmlAddAttribute('value', $config['value']),
            )
          ) .
          '<div class="separator">&nbsp;</div>' .
          $this->savlibrary->makeLink($config['value'], 0, $config);
      } else {
        $htmlArray[] = '<div class="separator">&nbsp;</div>' .
          utils::htmlInputHiddenElement(
            array(
              utils::htmlAddAttribute('name', $config['elementControlName']),
              utils::htmlAddAttribute('value', $config['value']),
            )
          );
      }
    } else {
      $config['size'] = (
        $config['size']<=20 ?
        $config['size']= 40 :
        $config['size']
      );
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

    $htmlArray = array();
    
		if(!$config['value']) {
      $htmlArray[] = '';
    } else {
      // Checks if a makeItemLink func is used
      if ($config['func'] == 'makeItemLink') {
        $temp = $this->savlibrary->makeDateFormat(
          $config['_value'],
          '',
          $config
        );
        $htmlArray[] = str_replace(
          $config['_value'],
          $temp,
          $config['value']
        );
      } else {
        $htmlArray[] = $this->savlibrary->makeDateFormat(
          $config['value'],
          '',
          $config
        );
      }
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
  
    $htmlArray = array();
    
    // Sets the format
    $format = ($config['format'] ? $config['format'] : '%d/%m/%Y %H:%M');
    
    // Sets the value
    $value = (
      $config['value'] ?
      strftime($format, $config['value']) :
      ($config['nodefault'] ? '' : strftime($format, time()))
    );
    $htmlArray[] = utils::htmlInputTextElement(
      array(
        utils::htmlAddAttribute('name', $config['elementControlName']),
        utils::htmlAddAttribute('id', 'input_' . strtr($config['elementControlName'],'[]','__')),
        utils::htmlAddAttributeIfNotNull('class', $config['classhtmltag']),
        utils::htmlAddAttributeIfNotNull('style', $config['stylehtmltag']),
        utils::htmlAddAttribute('value', $value),
        utils::htmlAddAttribute('onchange', 'document.changed=1;'),
      )
    );
    $htmlArray[] = $this->savlibrary->datePicker->buildDatePickerSetup(
      array(
        'id' => strtr($config['elementControlName'],'[]','__'),
        'format' => ($config['format'] ? $config['format'] : '%d/%m/%Y %H:%M'),
        'showsTime' => true,
      )
    );

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
  
    $htmlArray = array();
    
		if(!$config['value']) {
      $htmlArray[] = '';
    } else {
      // Checks if a makeItemLink func is used
      if ($config['func'] == 'makeItemLink') {
        $temp = $this->savlibrary->makeDateFormat(
          $config['_value'],
          '',
          $config
        );
        $htmlArray[] = str_replace(
          $config['_value'],
          $temp,
          $config['value']
        );
      } else {
        $htmlArray[] = $this->savlibrary->makeDateFormat(
          $config['value'],
          '',
          $config
        );
      }
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

    $htmlArray = array();

    // Sets the format
    $format = ($config['format'] ? $config['format'] : '%d/%m/%Y');

    // Sets the value
    $value = (
      $config['value'] ?
      strftime($format, $config['value']) :
      ($config['nodefault'] ? '' : strftime($format, strtotime(date('m/d/Y'))))
    );

    $htmlArray[] = utils::htmlInputTextElement(
      array(
        utils::htmlAddAttribute('name', $config['elementControlName']),
        utils::htmlAddAttribute('id', 'input_' . strtr($config['elementControlName'], '[]', '__')),
        utils::htmlAddAttributeIfNotNull('class', $config['classhtmltag']),
        utils::htmlAddAttributeIfNotNull('style', $config['stylehtmltag']),
        utils::htmlAddAttribute('value', $value),
        utils::htmlAddAttribute('onchange', 'document.changed=1;'),
      )
    );
    $htmlArray[] = $this->savlibrary->datePicker->buildDatePickerSetup(
      array(
        'id' => strtr($config['elementControlName'], '[]', '__'),
        'format' => ($config['format'] ? $config['format'] : '%d/%m/%Y'),
        'showsTime' => true,
      )
    );

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

    $htmlArray = array();

    $folder = $config['uploadfolder'] .
      ($config['addtouploadfolder'] ? '/' . $config['addtouploadfolder'] : '');

		if ($config['iframe']) {
		    // It's an image to be opened in an iframe
		    $width = $config['width'] ? $config['width'] : '100%';
		    $height = $config['height'] ? $config['height'] : '800';
		    $message = $config['message'] ? $config['message'] : '';

        // Adds the iframe element
        $htmlArray[] = utils::htmlIframeElement(
          array(
            utils::htmlAddAttribute('src', $folder . '/' . $config['value']),
            utils::htmlAddAttribute('width', $width),
            utils::htmlAddAttribute('height', $height),
          ),
          $message
        );

        
    }	elseif ($config['allowed'] == $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext'] || $config['allowed'] == 'gif,png,jpeg,jpg') {
      
        //
        if ($config['func'] == 'makeItemLink') {
          $fileArray = explode(',', $config['_value']);
        } else {
          $fileArray = explode(',', $config['value']);
        }

        // One or several images to display. Sets parameters
        foreach($fileArray as $file) {
        
  		    if ($file && file_exists($folder . '/' . $file)) {
            $params['tsproperties'] = $config['tsproperties'];
            $params['width'] = $config['width'];
            $params['height'] = $config['height'];
            $params['folder'] = $folder;
            $params['alt'] = $config['alt'];
            
            if ($config['onlyfilename']) {
              $out = $folder . '/' . $file;
            } else {
              $out = $this->savlibrary->makeImage($file, '', $params);
            }

            if ($config['func']=='makeNewWindowLink') {
              $out = $this->savlibrary->makeNewWindowLink (
                $out,
                $uid='',
                array('windowurl' => $folder . '/' . $file)
              );
            } elseif ($config['func'] == 'makeItemLink') {
              $out = preg_replace(
                '/(<a[^>]*>)[^<]*(<\/a>)/',
                '$1' . $out . '$2',
                $config['value']
              );
            }
            $htmlArray[] = $out;
          } else {
            $params['tsproperties'] = $config['tsproperties'];
            $params['width'] = $config['width'];
            $params['height'] = $config['height'];
            $image = (
              $config['default'] ?
              $config['default'] :
              t3lib_extMgm::siteRelPath('sav_library') . 'res/images/unknown.gif'
            );

            $out = $this->savlibrary->makeImage(
              $image,
              '',
              $params
            );
          
            if ($config['func'] == 'makeItemLink') {
              $out = preg_replace(
                '/(<a[^>]*>)[^<]*(<\/a>)/',
                '$1' . $out . '$2',
                $config['value']
              );
            }

            $htmlArray[] = $out;
          }
        }
      } else {
        // It's a file. Makes an hyperlink
        $out = '';
        $params['folder'] = $folder;
        $params['message'] = $config['message'];
        $params['target'] = $config['target'];
        $fileNames = explode(';', $config['value']);
        foreach ($fileNames as $fileName) {
          if($fileName) {
            $icon = '';
            if ($config['addicon']) {
              $pathInfo = pathinfo($fileName);
              $iconFileName = $pathInfo['extension'] . '.gif';
              if (file_exists(t3lib_extMgm::extPath('sav_library') . 'res/fileicons/' . $iconFileName)) {
                $icon = utils::htmlImgElement(
                  array(
                    utils::htmlAddAttribute('src',
                      t3lib_extMgm::siteRelPath('sav_library') . 'res/fileicons/' . $iconFileName),
                   utils::htmlAddAttribute('alt', 'Icon ' . $pathInfo['extension']),
                   utils::htmlAddAttribute('class', 'fileIcon '),
                  )
                );
              } elseif (file_exists('typo3/gfx/fileicons/' . $iconFileName)) {
                $icon = utils::htmlImgElement(
                  array(
                    utils::htmlAddAttribute('src', 'typo3/gfx/fileicons/' . $iconFileName),
                    utils::htmlAddAttribute('alt', 'Icon ' . $pathInfo['extension']),
                    utils::htmlAddAttribute('class', 'fileIcon '),
                  )
                );
              }
            }
            $htmlArray[] = utils::htmlDivElement(
              array(
                utils::htmlAddAttribute('class', 'file'),
              ),
              $icon . $this->savlibrary->makeLink($fileName, '', $params)
            );
          }
        }
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

    $htmlArray = array();
    
    $addtouploadfolder = (
      $config['addtouploadfolder'] ?
      $config['addtouploadfolder'] :
      ''
    );
		
    if ($config['size'] < 10) {
      $config['size'] = '';
    }

    $fileNames = explode(';', $config['value']);
    for ($i = 0; $i < $config['maxitems']; $i++) {
      $htmlArray[] = utils::htmlInputTextElement(
        array(
          utils::htmlAddAttribute('name', $config['elementControlName'] . '[' . $i . ']'),
          utils::htmlAddAttribute('class', 'file'),
          utils::htmlAddAttribute('value', ($fileNames[$i] ? $fileNames[$i] : '')),
          utils::htmlAddAttribute('size', $config['size']),
        )
      );

      $htmlArray[] = utils::htmlInputFileElement(
        array(
          utils::htmlAddAttribute('name', $config['elementControlName'] . '[' . $i . ']'),
          utils::htmlAddAttribute('value', ''),
          utils::htmlAddAttribute('size', $config['size']),
          utils::htmlAddAttribute('onchange', 'document.changed=1;'),
        )
      );

      $htmlArray[] = utils::htmlInputHiddenElement(
        array(
          utils::htmlAddAttribute('name',
            $this->savlibrary->formName . '[addtouploadfolder]' . (
              isset($this->savlibrary->rowItem) ?
              '[' . $this->savlibrary->rowItem . ']' :
              '[' . $config['uid'] . ']')
            ),
          utils::htmlAddAttribute('value', $addtouploadfolder),
        )
      );
    }
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
  
    $htmlArray = array();
      
		$viewItem = (
      $config['edit'] ?
      'viewStringInputEditMode' :
      'viewStringInput'
    );
  
    if(!$config['value'] && !$config['edit']) {
      $out = '';
    } else {
      // Explodes the value
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
		  $ta['TYPE'] = 'schedule';
		  foreach (($config['edit'] ? range(0, 4) : $schedule )  as $key => $value) {
        unset ($node);
        $node['TYPE'] = 'sub_item';

        $node['MARKERS']['separator'] = (
          $config['edit'] ?
          '<span class="left">-</span>' :
          '-'
        );
			 
        $node['MARKERS']['day'] = $this->savlibrary->getLibraryLL(
          'itemviewer.days.' . $key
        );
        $localConfig['uid'] = $config['uid'];

        $localConfig['elementControlName'] = preg_replace(
          '/\[([^\[]+)\](.*)$/',
          '[' . $config['fullFieldName'] . '][' . $config['uid'] . '][' .
            $key . '][beginAm][' . $config['uid'] . ']',
          $config['elementControlName']
        );
        $localConfig['value'] = $schedule[$key]['beginAm'];
        $node['MARKERS']['beginAm'] = $this->$viewItem($localConfig);
			 
        $localConfig['elementControlName'] = preg_replace(
          '/\[([^\[]+)\](.*)$/',
          '[' . $config['fullFieldName'] . '][' . $config['uid'] . '][' .
            $key . '][endAm][' . $config['uid'] . ']',
          $config['elementControlName']
        );
        $localConfig['value'] = $schedule[$key]['endAm'];
        $node['MARKERS']['endAm'] = $this->$viewItem($localConfig);

        $localConfig['elementControlName'] = preg_replace(
          '/\[([^\[]+)\](.*)$/',
          '[' . $config['fullFieldName'] . '][' . $config['uid'] . '][' .
            $key . '][beginPm][' . $config['uid'] . ']',
          $config['elementControlName']
        );
        $localConfig['value'] = $schedule[$key]['beginPm'];
        $node['MARKERS']['beginPm'] = $this->$viewItem($localConfig);

        $localConfig['elementControlName'] = preg_replace(
          '/\[([^\[]+)\](.*)$/',
          '[' . $config['fullFieldName'] . '][' . $config['uid'] . '][' .
            $key . '][endPm][' . $config['uid'] . ']',
          $config['elementControlName']
        );
        $localConfig['value'] = $schedule[$key]['endPm'];
        $node['MARKERS']['endPm'] = $this->$viewItem($localConfig);

        $items[] = $node;
		  }
		  $ta['REGIONS']['sub_items'] = $items;

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

    $htmlArray = array();

    // Finds the selected item
    foreach ($config['items'] as $key => $item) {
      if ($item[1] == $config['_value']) {
        break;
      }
    }
       
		if (isset($config['func'])) {
			$htmlArray[] =  $this->savlibrary->$config['func'](
        $this->savlibrary->getLL_db($config['items'][$key][0]),
        $config['uid'],
        $config
      ) . '<br />';
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
  
    $htmlArray = array();
    
    // Initializes the option element array
    $htmlOptionArray = array();
		$htmlOptionArray[] = '';
    
    // Adds the empty item option if any
		if ($config['emptyitem']) {
			// Add the Option element
			$htmlOptionArray[] = utils::htmlOptionElement(
        array(
          utils::htmlAddAttribute('value', '0'),
        ),
        ''
      );
    }
    
    foreach ($config['items'] as $item) {
			$selected = (
        ((string)$item[1] == (string)$config['value']) ?
        'selected' :
        ''
      );

			// Adds the Option element
			$htmlOptionArray[] = utils::htmlOptionElement(
        array(
          utils::htmlAddAttributeIfNotNull('selected', $selected),
          utils::htmlAddAttribute('value', $item[1]),
        ),
        stripslashes($this->savlibrary->getLL_db($item[0]))
      );
		}
		$htmlOptionArray[] = '';

    // Adds the select element
		$htmlArray[] = utils::htmlSelectElement(
      array(
        utils::htmlAddAttribute('name', $config['elementControlName']),
        utils::htmlAddAttributeIfNotNull('class', $config['classhtmltag']),
        utils::htmlAddAttributeIfNotNull('style', $config['stylehtmltag']),
        utils::htmlAddAttribute('size', $config['size']),
        utils::htmlAddAttribute('onchange', 'document.changed=1;'),
      ),
      $this->savlibrary->arrayToHTML($htmlOptionArray)
    );
		 
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

    $htmlArray = array();
    
    // Searchs for the key
    $keyFound = 0;
    foreach ($config['items'] as $key => $item) {
      if ($item['uid'] == $config['_value']) {
        $keyFound = $key;
        break;
      }
    }
    // Keeps only the item found
    if (!isset($config['codeArray'])) {
      $temp = $config['items'][$keyFound];
      unset($config['items']);
      $config['items'][$keyFound] = $temp;
    }
        
    // Checks if a special value exists
    if (is_array($config['items'][$keyFound]['special'])) {
      $config['special'] = $config['items'][$keyFound]['special'];
    }

    // Checks if a function is called
  	if ($config['func']) {
   		$htmlArray[] = $this->savlibrary->$config['func'](
        stripslashes($config['items'][$keyFound]['label']),
        ($config['setuid']=='this'? $config['_value'] : $config['uid']),
        $config
      );
    } else {
      // Gets the field from the label field of the allowed table.
      if (isset($config['codeArray'])) {
  			$code = ((int) ($config['items'][$keyFound]['code']/100))*100;
  			
  			if(!($config['items'][$keyFound]['code']%100)) {
  				$htmlArray[] = ($config['nobold'] ? '' : '<b>') .
            $config['items'][$keyFound]['label'] .
            ($config['nobold'] ? '' : '</b>');
        } else {
  				$htmlArray[] = ($config['nobold'] ? '' : '<b>') .
            $config['items'][$config['codeArray'][$code]]['label'] .
            ($config['nobold'] ? '' : '</b>');
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

    $htmlArray = array();
    
    // Initializes the option element array
    $htmlOptionArray = array();
		$htmlOptionArray[] = '';

    // Adds the empty item option if any
		if ($config['emptyitem']) {
			// Adds the Option element
			$htmlOptionArray[] = utils::htmlOptionElement(
        array(
          utils::htmlAddAttribute('value', '0'),
        ),
        ''
      );
    }
    
    // Adds the option elements
    foreach ($config['items'] as $key => $item) {
			$selected = ($item['selected']) ? 'selected' : '';
			
			// Adds the Option element
			$htmlOptionArray[] = utils::htmlOptionElement(
        array(
          utils::htmlAddAttributeIfNotNull('style', $item['style']),
          utils::htmlAddAttributeIfNotNull('selected', $selected),
          utils::htmlAddAttribute('value', $item['uid']),
        ),
        stripslashes($item['label'])
      );
      
		}
		$htmlOptionArray[] = '';
		
    // Adds the select element
		$htmlArray[] = utils::htmlSelectElement(
      array(
        utils::htmlAddAttribute('name', $config['elementControlName']),
        utils::htmlAddAttributeIfNotNull('class', $config['classhtmltag']),
        utils::htmlAddAttributeIfNotNull('style', $config['stylehtmltag']),
        utils::htmlAddAttribute('size', $config['size']),
        utils::htmlAddAttribute('onchange', 'document.changed=1;'),
      ),
      $this->savlibrary->arrayToHTML($htmlOptionArray)
    );

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
  
    $htmlArray = array();

		if ($config['MM'] || $config['maxitems']>1) {	  
      // Gets all the fields
  		foreach($config['items'] as $item) {
  			if($item['selected']) {
  			
  			    // Checks if a special value exists
          if (is_array($item['special'])) {
            $config['special'] = $item['special'];
          }

  				// If the code is not null, get the hierarchy item
  				if (isset($config['codeArray'])) {
  				  $code = ((int) ($item['code']/100))*100;
  				  if(!($item['code'] % 100)) {
  					  $htmlArray[] = ($config['nobold'] ? '' : '<b>') .
                $item['label'] .
                ($config['nobold'] ? '' : '</b><br />');
            } else {
  				    $htmlArray[] = ($config['nobold'] ? '' : '<b>') .
                 $config['items'][$config['codeArray'][$code]]['label'] .
                 ($config['nobold'] ? ' ' : '</b> ');
  					  $htmlArray[] = $item['label'] . '<br />';
  				  }
          } else {
            // Checks if a function is called
            if ($config['func']) {
              $temp = $this->savlibrary->$config['func'](
                stripslashes($item['label']),
                (
                  $config['setuid']=='this'?
                  $config['_value'] :
                  $config['uid']
                ),
                $config
              );
            } else { 
              $temp = $item['label'];
            }
  					$htmlArray[] = (
              $htmlArray ?
              ($config['separator'] ? $config['separator'] . ' ' :'<br />') :
              ''
            ) . $temp;
  				}
  			}
  		}
  		return $this->savlibrary->arrayToHTML($htmlArray, $config['nohtmlprefix']);
 		}    
		else {
  		if ($config['content']) {
        // Gets the field from a query. the uid marker is replace by the selected value
        $query = $config['content']; 
        $mA["###uid###"] = $config['uid'];
        $mA["###uidSelected###"] = key($selected);
        $query = $this->savlibrary->extObj->cObj->substituteMarkerArrayCached(
          $query,
          $mA,
          array(),
          array()
        );
        
        // Checks if the query is a SELECT query and for errors
        if (!$this->savlibrary->isSelectQuery($query)) {
          $this->savlibrary->addError(
            'error.onlySelectQueryAllowed',
            $config['field']
          );
          return $this->savlibrary->arrayToHTML($htmlArray);
        } elseif (!($res = $GLOBALS['TYPO3_DB']->sql_query($query))) {
          $this->savlibrary->addError(
            'error.incorrectQueryInContent',
            $config['field']
          );
          return $this->savlibrary->arrayToHTML($htmlArray);
        }
        
        // Processes the query
  		  $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
  		  // Checks if the makeExtLink is required
  		  if ($config['func'] == 'makeExtLink') {
  		    $params['ext'] = $config['ext'];
  		    $params['id'] = $config['id'];
   		    return $this->savlibrary->makeExtLink(
            stripslashes($row['label']),
            $row['uid'],
            $params
          );
        } else {
  		    return $row['label'];
        }
    	} else {
        // Searchs for the key
        $keyFound = 0;
        foreach ($config['items'] as $key => $item) {
          if ($item['uid'] == $config['_value']) {
            $keyFound = $key;
            break;
          }
        }

        // Gets the field from the label field of the allowed table.
        if (isset($config['codeArray'])) {
          $code = ((int) ($config['items'][$keyFound]['code']/100))*100;
          if(!($config['codeArray'][$code])) {
  				  $htmlArray[] = '<b>' .
              $config['items'][$keyFound]['label'] . '</b>';
          } else {
  				  $htmlArray[] = '<b>' .
              $config['items'][$config['codeArray'][$code]]['label'] . '</b> ';
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
  
    $htmlArray = array();

    // Initializes the option element array
    $htmlOptionArray = array();
		$htmlOptionArray[] = '';

		if ($config['emptyitem']) {
			// Adds the Option element
			$htmlOptionArray[] = utils::htmlOptionElement(
        array(
          utils::htmlAddAttribute('value', '0'),
        ),
        ''
      );
    }
		foreach($config['items'] as $key => $item) {
			$selected = ($item['selected']) ? 'selected' : '';

			// Adds the Option element
			$htmlOptionArray[] = utils::htmlOptionElement(
        array(
          utils::htmlAddAttributeIfNotNull('style', $item['style']),
          utils::htmlAddAttributeIfNotNull('selected', $selected),
          utils::htmlAddAttribute('value', $item['uid']),
        ),
        stripslashes($item['label'])
      );

		}
		$htmlOptionArray[] = '';

    // Adds the select element
		$htmlArray[] = utils::htmlSelectElement(
      array(
        utils::htmlAddAttribute('multiple', 'multiple'),
        utils::htmlAddAttribute('name', $config['elementControlName'] .'[]'),
        utils::htmlAddAttribute('size', $config['size']),
        utils::htmlAddAttribute('onchange', 'document.changed=1;'),
        utils::htmlAddAttributeIfNotNull('class', $config['classhtmltag']),
        utils::htmlAddAttributeIfNotNull('style', $config['stylehtmltag']),
      ),
      $this->savlibrary->arrayToHTML($htmlOptionArray)
    );

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

    $htmlArray = array();
    
    // Initializes the option element array
    $htmlOptionArray = array();
		$htmlOptionArray[] = '';

		$elementControlName = $this->savlibrary->formName . '[' .
      $config['cryptedFieldName'] . ']' .
      (
        isset($this->savlibrary->rowItem) ?
        '[' . $this->savlibrary->rowItem . ']' :
        '[' . (
          $config['mm_uid_local'] ?
          $config['mm_uid_local'] :
          $config['uid']
          ) . ']'
      );

		$class = (
      $config['classhtmltag'] ?
      $config['classhtmltag'] :
      'multiple'
    );


    $fieldName = $config['field'] . (
      isset($this->savlibrary->rowItem) ?
      '[' . $this->savlibrary->rowItem . ']' :
      ''
    );
    $sort = ($config['orderselect'] ? 1 : 0);
    
		foreach($config['items'] as $key => $item) {
			if($item['selected']) {
  			// Adds the Option element
  			$htmlOptionArray[] = utils::htmlOptionElement(
          array(
            utils::htmlAddAttribute('value', $item['uid']),
          ),
          stripslashes($item['label'])
        );
      }
		}
		$htmlOptionArray[] = '';

    // Adds the select element
		$htmlArray[] = utils::htmlSelectElement(
      array(
        utils::htmlAddAttribute('multiple', 'multiple'),
        utils::htmlAddAttribute('class', $class),
        utils::htmlAddAttributeIfNotNull('style', $config['stylehtmltag']),
        utils::htmlAddAttribute('name', $elementControlName .'[]'),
        utils::htmlAddAttribute('size', $config['maxitems']),
        utils::htmlAddAttribute('ondblclick',
          'move(\'' . $this->savlibrary->formName . '\', \'' .
          $elementControlName . '[]\', \'' . $fieldName . '\',' . $sort . ');'
        ),
        utils::htmlAddAttribute('onchange', 'document.changed=1;'),
      ),
      $this->savlibrary->arrayToHTML($htmlOptionArray)
    );

    // Initializes the option element array
    $htmlOptionArray = array();
		$htmlOptionArray[] = '';
		
		foreach($config['items'] as $key => $item) {
			if(!$item['selected']) {
  			// Adds the Option element
  			$htmlOptionArray[] = utils::htmlOptionElement(
          array(
            utils::htmlAddAttribute('value', $item['uid']),
          ),
          stripslashes($item['label'])
        );
      }
		}
		$htmlOptionArray[] = '';

    // Adds the select element
		$htmlArray[] = utils::htmlSelectElement(
      array(
        utils::htmlAddAttribute('multiple', 'multiple'),
        utils::htmlAddAttribute('class', $class),
        utils::htmlAddAttributeIfNotNull('style', $config['stylehtmltag']),
        utils::htmlAddAttribute('name', $fieldName),
        utils::htmlAddAttribute('size', $config['maxitems']),
        utils::htmlAddAttribute('ondblclick',
          'move(\'' . $this->savlibrary->formName . '\', \'' .
          $fieldName . '\', \'' . $elementControlName . '[]\',' . $sort . ');'
        ),
        utils::htmlAddAttribute('onchange', 'document.changed=1;'),
      ),
      $this->savlibrary->arrayToHTML($htmlOptionArray)
    );

		// Adds it to the select list for Javascript
    $this->savlibrary->selectList .= 'selectAll(\'' .
      $this->savlibrary->formName . '\', \'' . $elementControlName . '[]\');';

		return $this->savlibrary->arrayToHTML($htmlArray);
	}


 	/**
	 * db relation checkboxes viewer
	 *
	 * @param $config array (Configuration array)
	 *
	 * @return string (item to display)
	 */
 	public function viewDbRelationCheckboxes(&$config) {

    $htmlArray = array();

    $htmlArray[] = $this->viewDbRelationSingleSelectorMultiple($config);

		return $this->savlibrary->arrayToHTML($htmlArray);
  }



 	/**
	 * DB relation checkboxes viewer in edit mode
	 *
	 * @param $config array (Configuration array)
	 *
	 * @return string (item to display)
	 */
 	public function viewDbRelationCheckboxesEditMode(&$config) {

    $htmlArray = array();

    // Initializes the option element array
    $htmlOptionArray = array();
		$htmlOptionArray[] = '';

		$elementControlName = $this->savlibrary->formName . '[' .
      $config['cryptedFieldName'] . ']' .
      (
        isset($this->savlibrary->rowItem) ?
        '[' . $this->savlibrary->rowItem . ']' :
        '[' . (
          $config['mm_uid_local'] ?
          $config['mm_uid_local'] :
          $config['uid']
          ) . ']'
      );

		$class = (
      $config['classhtmltag'] ?
      $config['classhtmltag'] :
      'multiple'
    );


    $fieldName = $config['field'] . (
      isset($this->savlibrary->rowItem) ?
      '[' . $this->savlibrary->rowItem . ']' :
      ''
    );
    $sort = ($config['orderselect'] ? 1 : 0);

		foreach($config['items'] as $key => $item) {
			if($item['selected']) {
        $checked = 'checked';
			} else {
        $checked = '';
      }
			
      // Adds the hidden input element
      $htmlArray[] = utils::htmlInputHiddenElement(
        array(
          utils::htmlAddAttribute(
            'name',
            $config['elementControlName'] . '[' . $key . ']'
          ),
          utils::htmlAddAttribute('value', '0'),
        )
      );

      // Adds the checkbox input element
      $htmlArray[] = utils::htmlInputCheckBoxElement(
        array(
          utils::htmlAddAttribute(
            'name',
            $config['elementControlName'] . '[' . $key . ']'
          ),
          utils::htmlAddAttribute('value', $item['uid']),
          utils::htmlAddAttributeIfNotNull('checked', $checked),
          utils::htmlAddAttribute('onchange', 'document.changed=1;'),
        )
      );

      // Adds the span element
      $htmlArray[] = utils::htmlSpanElement(
        array(
          utils::htmlAddAttribute('class', 'checkbox'),
          $value['addattributes'],
        ),
        stripslashes($item['label'])
      );

      // Adds the br element
      $htmlArray[] = utils::htmlBrElement(
        array(
          utils::htmlAddAttribute('class', 'checkbox'),
        )
      );
		}

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

    $htmlArray = array();
    
    $query = $this->savlibrary->extObj->extConfig['queries'][$this->savlibrary->formConfig['query']];

		$foreign_table = $config['foreign_table'];
		$MM_table = $config['MM'];
		$table = $config['table'];
    $MM_field = $config['mm_field'] ? $config['mm_field'] : 'uid';
    
    $uid = $config['uid'];
		$selected = array();

    // Redisplays the item if an error in new form is detected
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

        // Builds a part of the WHERE clause depending on the relation
        if ($config['MM']) {
          // Case of a true MM relation
          $wherePart = ' AND ' . $MM_table . '.uid_local=' . $table . '.' . $MM_field .
  					' AND ' . $MM_table . '.uid_foreign=' . $foreign_table . '.uid';
        } elseif (!$config['MM'] && $config['maxitems']>1) {
          // Case of a non true MM relation
          $wherePart = ($config['_value'] ?
              ' AND ' . $foreign_table . '.uid IN (' . $config['_value'] . ')' :
              '');
        } else {
          // Usual case
          $wherePart = ' AND ' . $table . '.' . $config['field'] . '=' . $foreign_table . '.uid';
        }

  		  $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
  				  /* SELECT   */	'*' .
                (
                  $config['mm_field'] ?
                  ',' . $table . '.' . $MM_field . ' as mm_uid_local' :
                  ''
                ) .
  						  '',
  				  /* FROM     */	$table . ',' . $foreign_table .
                (
                  $MM_table ?
                  ',' . $MM_table :
                  ''
                ) .
  						  '',
  	 			  /* WHERE    */	'1' .
                $wherePart .
  						  ' AND ' . $table . '.uid=' . intval($uid) .
  			        (
                  $config['overrideenablefields'] ?
                  '' :
                  $this->savlibrary->extObj->cObj->enableFields($table)
                ) .
  			        (
                  ($this->savlibrary->extObj->cObj->data['pages'] && !$config['overridestartingpoint']) ?
                  ' AND ' . $table . '.pid IN (' . $this->savlibrary->extObj->pi_getPidList($this->savlibrary->extObj->cObj->data['pages'], $this->savlibrary->extObj->cObj->data['recursive']) . ')' :
                  ''
                ) .
  						  (
                  $config['where'] ?
                  ' AND ' . $config['where'] :
                  ''
                ) .
  						  '',
  				  /* GROUP BY */	
  						  '',
  				  /* ORDER BY */
                (
                  $MM_table ?
                  $MM_table . '.sorting' :
                  ''
                ) .
  						  '',
  				  /* LIMIT    */	''
  		  );

  		  // Gets all selected fields
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

		// Gets the label of the allowed_table
		$label = (
      $config['labelselect'] ?
      $config['labelselect'] :
      $GLOBALS['TCA'][$foreign_table]['ctrl']['label']
    );
    $defaultOrder = (
      $GLOBALS['TCA'][$foreign_table]['ctrl']['default_sortby'] ?
      str_replace(
        'ORDER BY',
        '',
        $foreign_table . '.' .$GLOBALS['TCA'][$foreign_table]['ctrl']['default_sortby']
      ) :
      ''
    );
		$order = (
      $GLOBALS['TCA'][$foreign_table]['ctrl']['sortby'] ?
      $foreign_table . '.' .$GLOBALS['TCA'][$foreign_table]['ctrl']['sortby'] :
      $defaultOrder
    );
    
    // Processes the foreign_table_where
    if ($config['foreign_table_where']) {
      preg_match('/^AND (.*)? ORDER BY (.*)$/', $config['foreign_table_where'], $match);  
    }
    if (!$config['whereselect'] && $match[1]) {
      $config['whereselect'] = $match[1];
    }
    if (!$config['orderselect'] && $match[2]) {
      $config['orderselect'] = $match[2];
    }

    // Processes tags in whereselect clause
    $config['whereselect'] = $this->savlibrary->queriers->processWhereClause($config['whereselect']); 

		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
		  /* SELECT   */	'*' . ($config['aliasselect'] ? ',' . $config['aliasselect'] : '') .
				'',
			/* FROM     */	$foreign_table .
        (
          $config['additionaltableselect'] ?
          ',' . $config['additionaltableselect'] :
          ''
        ) .
        (
          $config['additionaljointableselect'] ?
          ' ' . $config['additionaljointableselect'] :
          ''
        ) .
				'',
			/* WHERE    */	'1' .
 			  (
          $config['overrideenablefields'] ?
          '' :
          $this->savlibrary->extObj->cObj->enableFields($foreign_table)
        ) .
 			  (
          ($this->savlibrary->extObj->cObj->data['pages'] && !$config['overridestartingpoint']) ?
          ' AND ' . $foreign_table . '.pid IN (' . $this->savlibrary->extObj->pi_getPidList($this->savlibrary->extObj->cObj->data['pages'], $this->savlibrary->extObj->cObj->data['recursive']) . ')' :
          ''
        ).
				(
          $config['whereselect'] ?
          ' AND ' . $config['whereselect'] :
          ''
        ) .
				'',
			/* GROUP BY */
        ($config['groupbyselect'] ? $config['groupbyselect'] : '') .
				'',
			/* ORDER BY */
				($config['orderselect'] ? $config['orderselect'] : $order) .
        '',
				/* LIMIT    */	''
		);

		if (!isset($config['items'])) {
		  if($config['addedit'] && $config['addedit'] && $config['singlewindow'] && !$config['MM'] && $config['maxitems']>1) {
        $config['items'][0] = array(
          'uid'=>0,
          'label'=>'',
          'selected' => $selected[0]
        );
      } else {
        $config['items'] = array();
      }     
    } else {
      $config['items'][0] = array(
        'uid'=>0,
        'label'=>''
      );
    }
    $items = $config['items'];
    
    $cpt= count($config['items']);

		while ($rows = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {

		  if ($rows[$label]) {
		    // Checks if special fields were added
		    if ($config['specialfields']) {
          $specialfields = explode(',', $config['specialfields']);
          foreach ($specialfields as $specialfield) {
            $special[$specialfield] = $rows[$specialfield];
          }
        }
    
        // Processes the tags in the label
        if (preg_match_all('/###([^#]+)###/', $rows[$label], $matches)) {
          foreach($matches[1] as $keyMatch=>$valueMatch) {
            $replaceValue = (
              $GLOBALS['TCA'][$foreign_table]['columns'][$valueMatch]['config']['type']=='select' ?
              $this->savlibrary->getLL_db(
                $GLOBALS['TCA'][$foreign_table]['columns'][$valueMatch]['config']['items'][$rows[$valueMatch]][0]
              ) :
              $rows[$valueMatch]
            );
            $rows[$label] = str_replace($matches[0][$keyMatch], $replaceValue, $rows[$label]);
          }
        }    
        
			  $config['items'][$cpt] = array(
			    'uid' => $rows['uid'],
          'label'=> $this->savlibrary->htmlentitiesWithCharset(
            $GLOBALS['TCA'][$foreign_table]['columns'][$label]['config']['type']=='select' ?
            $this->savlibrary->getLL_db(
              $GLOBALS['TCA'][$foreign_table]['columns'][$label]['config']['items'][$rows[$label]][0]
            ) :
            $rows[$label]
          ),
          'selected' => $selected[$rows['uid']],
          'code' => $rows[$config['code']],
          'style' => (
            ($config['optionCond'] && $rows[$config['optionCond']]) ?
            $config['optionStyle'] :
            ''
          ),
          'special' => (is_array($special) ? $special : ''),
        );
        if($config['code']) {
          $config['codeArray'][$rows[$config['code']]] = $cpt;
        }

        // Removes if not allowed
        if ($label == $this->savlibrary->conf['inputAdminField'] && $config['edit'] && !$this->savlibrary->userIsAdmin($rows)) {
					unset($config['items'][$cpt]);
				}
				$cpt++;
      }
		}

		if ($MM_table) {
		  // Changes the number of dispayed items if only one selected. In general, it occurs
		  // when a where is added in the field parameter to select only one field
		  if (count($config['items']) == 1){
		    $config['size'] = 1;
		    $viewItem = (
          $config['edit'] ?
          'viewDbRelationSingleSelectorMultipleEditMode' :
          'viewDbRelationSingleSelectorMultiple'
        );
        return $this->$viewItem($config);
		  } else {
		    $viewItem = (
          $config['edit'] ?
          'viewDbRelationDoubleWindowSelectorEditMode' :
          'viewDbRelationDoubleWindowSelector'
        );
        return $this->$viewItem($config);
      }				
		} else {
			if ($config['content']) {

        // Gets the field from a query. The uid marker is replaced by the selected value
        $query = $config['content']; 
        $mA['###uid###'] = intval($config['uid']);
        $mA['###uidSelected###'] = key($selected);
        $mA['###cruser###'] = $GLOBALS['TSFE']->fe_user->user['uid'];
        $mA['###user###'] = $GLOBALS['TSFE']->fe_user->user['uid'];
        $query = $this->savlibrary->extObj->cObj->substituteMarkerArrayCached(
          $query,
          $mA,
          array(),
          array()
        );

        $config['items'] = $items;

        // Checks if the query is a SELECT query and for errors
        if (!$this->savlibrary->isSelectQuery($query)) {
          $this->savlibrary->addError(
            'error.onlySelectQueryAllowed',
            $config['field']
          );
          return $this->savlibrary->arrayToHTML($htmlArray);
        } elseif (!($res = $GLOBALS['TYPO3_DB']->sql_query($query))) {
          $this->savlibrary->addError(
            'error.incorrectQueryInContent',
            $config['field']
          );
          return $this->savlibrary->arrayToHTML($htmlArray);
        }

        // Processes the query
  			if (!is_array($config['items'])) {
          $config['items'] = array();
        } 
		    while ($rows = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {     
			    $config['items'][] =  array(
			      'uid' => $rows['uid'],
            'label'=> $this->savlibrary->htmlentitiesWithCharset(stripslashes($rows['label'])),
            'selected' => ($config['selected'] ? 1 : $selected[$rows['uid']])
          );
		    }
      } 

      if ($config['foreign_table'] && $config['maxitems']>1) {
        if ($config['displayascheckboxes']) {
		      $viewItem = (
            $config['edit'] ?
            'viewDbRelationCheckboxesEditMode' :
            'viewDbRelationCheckboxes'
          );
        } elseif ($config['singlewindow']) {
		      $viewItem = (
            $config['edit'] ?
            'viewDbRelationSingleSelectorMultipleEditMode' :
            'viewDbRelationSingleSelectorMultiple'
          );
        } else {
  		    $viewItem = (
            $config['edit'] ?
            'viewDbRelationDoubleWindowSelectorEditMode' :
            'viewDbRelationDoubleWindowSelector'
          );
        }
      } else {    
		    $viewItem = (
          $config['edit'] ?
          'viewDbRelationSingleSelectorEditMode' :
          'viewDbRelationSingleSelector'
        );
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

    $htmlArray = array();
    
		$allowed_table = $config['allowed'];
		$config['subform'] = '_subForm';
		
    // Checks if there exists a MM relation. It generates a subform
    if ($config['MM'] || $config['norelation']) {
      $uid = $this->savlibrary->uid;

      // Gets the number of items satisfying the query with no limit field
      $rows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			 	/* SELECT   */   'count(*) as nbitem',		
				/* FROM     */   $allowed_table .
          (
            $config['norelation'] ?
            '' :
            ',' . $config['MM']
          ),
	 			/* WHERE    */   '1'.
 			    $this->savlibrary->extObj->cObj->enableFields($allowed_table).
	 			  (
            $config['norelation'] ?
            '' :
            ' AND ' . $allowed_table . '.uid=' . $config['MM'] . '.uid_foreign'.
	 			    ' AND ' . $config['MM'] . '.uid_local=' . intval($uid)
          ).
	 			  (
            $config['errors']['_subFormId'] ?
            ' AND ' . $allowed_table . '.uid=' . $config['errors']['_subFormId'] :
            ''
          ) .
          (
            $config['where'] ?
            ' AND ' . $this->savlibrary->queriers->processWhereClause($config['where']) :
            ''
          ) ,
				/* GROUP BY */	 '',
				/* ORDER BY */	 '',
				/* LIMIT    */	 ''
		  );
  	  $nbitem = $rows[0]['nbitem'];

		  $order = (
        $GLOBALS['TCA'][$allowed_table]['ctrl']['sortby'] ?
        $GLOBALS['TCA'][$allowed_table]['ctrl']['sortby'] :
        str_replace(
          'ORDER BY',
          '',
          $GLOBALS['TCA'][$allowed_table]['ctrl']['default_sortby']
        )
      );
      if ($order == 'sorting') {
        $order = ($config['MM'] ? $config['MM'] . '.' . $order : $order);
      }
      $maxSubItems = (
        isset($config['maxsubitems']) ?
        $config['maxsubitems'] :
        $config['maxitems']
      );
   
      $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			 	/* SELECT   */   $allowed_table . '.*',
				/* FROM     */   $allowed_table .
          ($config['norelation'] ? '' : ','.$config['MM']),
	 			/* WHERE    */   '1'.
 			    $this->savlibrary->extObj->cObj->enableFields($allowed_table) .
	 			  (
            $config['norelation'] ?
            '' :
            ' AND ' . $allowed_table . '.uid=' . $config['MM'] . '.uid_foreign' .
            ' AND ' . $config['MM'] . '.uid_local=' . intval($uid)
          ).
	 			  (
            $config['errors']['_subFormId'] ?
            ' AND ' . $allowed_table . '.uid=' . $config['errors']['_subFormId'] :
            ''
          ).
          (
            $config['where'] ?
            ' AND ' . $this->savlibrary->queriers->processWhereClause($config['where']) :
            ''
          ) ,
				/* GROUP BY */	 '',
				/* ORDER BY */
          (
            $config['addupdown'] ?
            $config['MM'] . '.sorting' :
            ($config['order'] ? $config['order'] : $order)
          ),
				/* LIMIT    */
          (
            $maxSubItems ?
            ($maxSubItems*($this->savlibrary->limitSub[$config['cryptedFieldName']])) . ',' . ($maxSubItems) :
            ''
          )
		  );

		  $fields = $config['fullFieldName'];
      $value = '';
      
      // Builds the subForm
      $subForm = array();            
      $subForm['TYPE']= (
        $config['subformtemplate'] ?
        $config['subformtemplate'] :
        'subForm'
      );
      
      // Adds the new button
      $subForm['CUTTERS']['CUT_title'] = (
        $this->savlibrary->inputIsAllowedInForm() ||
          (!$config['edit'] &&
            ($config['labelontitle'] || $config['subformtitle'])
          ) ? 0 : 1
      );
      $subForm['MARKERS']['titleIconLeft'] = (
        !$config['edit'] || ($config['cutnewbuttonifnotsaved'] && !$this->savlibrary->uid) ?
        '' :
        $this->savlibrary->newButtonSubForm(
          $this->savlibrary->formName,
          $uid,
          $config['cryptedFieldName']
        )
      );
      $subForm['MARKERS']['CLASS_titleIconLeft'] = (
        $this->savlibrary->inputIsAllowedInForm() ?
        'titleIconLeft' :
        'titleIconLeftVoid'
      );
      if ($config['labelontitle']) {     
  		  $subForm['MARKERS']['formTitle'] = $this->savlibrary->getLL_db(
          'LLL:EXT:' . $this->extKey .
          '/locallang_db.xml:' . $config['fullFieldName']);
      } else {
        $subForm['MARKERS']['formTitle'] = $this->savlibrary->processLocalizationTags($config['subformtitle']);
      }
  
 		  // Adds a new row if the new button has been activated
      if ($this->savlibrary->newSubForm && $this->savlibrary->subFormName == $config['cryptedFieldName']) {
    		  $this->savlibrary->rowItem = 0;
    		  $row = array();
              		  
    		  // Addq the field that were kept in the subform
          if (isset($config['keepfieldsinsubformvalues'])) {
            $row += $config['keepfieldsinsubformvalues'];          
          }
          
          // Removeq the values that may come from the parent form
          $temp = $this->savlibrary->queriers->sql_fetch_assoc_with_tablename($res);

          if (is_array($temp)) {
            foreach ($temp as $keyTemp => $valueTemp) {
              unset($row[$keyTemp]);
            }
          }

          // Checks if fields are set in the subform
          if (!isset($config[$this->savlibrary->cryptTag('0')])) {
            $out = '<span class="error">' .
              $this->savlibrary->getLibraryLL('error.noFieldSelectedInSubForm') .
              '</span>';
            return $out;
          }

          $this->savlibrary->processSubForm = true;
		      $x = $this->savlibrary->generateFormTa(
            $config['name'],
            $row,
            array(
              $this->savlibrary->cryptTag('0') => $config[$this->savlibrary->cryptTag('0')]
            ),
            $config['errors'],
            $config['edit']
          );
          $this->savlibrary->processSubForm = false;

          $x['TYPE']= 'subFormItem';
          foreach ($x['REGIONS']['items'] as $key => $val) {
            $x['REGIONS']['items'][$key]['MARKERS']['icon'] = '';
            $x['REGIONS']['items'][$key]['MARKERS']['CLASS_iconLeft'] = 'iconLeftVoid';
          }
          $value .= $this->savlibrary->replaceTemplate($x);
          $cutLeft = 1;
          $cutRight = 1;
      } else {  
  
        // Returns empty if no rows and not newSubform and no arrows
        if (!$config['edit'] && !$this->savlibrary->newSubForm && !$nbitem && !isset($this->savlibrary->limitSub[$config['cryptedFieldName']])) {
          return '';
        }
         // Parses the fields
        $cpt = 0;
  		  while ($row = $this->savlibrary->queriers->sql_fetch_assoc_with_tablename($res)) {
          // Adds the field kept from the parent form
          if (isset($config['keepfieldsinsubformvalues'])) {
              $row += $config['keepfieldsinsubformvalues'];      
          }
 		    
  		    // Processes the field
    		  $this->savlibrary->rowItem = $row[$allowed_table . '.uid'];
    		  
          // Checks fields are set in the subform
          if (!isset($config[$this->savlibrary->cryptTag('0')])) {
            $out = '<span class="error">' .
              $this->savlibrary->getLibraryLL('error.noFieldSelectedInSubForm') .
              '</span>';
            return $out;
          }

          $this->savlibrary->processSubForm = true;
		      $x = $this->savlibrary->generateFormTa(
            $config['name'],
            $row,
            array(
              $this->savlibrary->cryptTag('0') => $config[$this->savlibrary->cryptTag('0')]
            ),
            $config['errors'],
            $config['edit']
          );
          $this->savlibrary->processSubForm = false;
          $x['TYPE'] = 'subFormItem';          

          // Sets the class
          if ($config['addupdown'] || $config['adddelete'] || $config['addsave']) {
            $iconClass = 'itemIconLeft';
          } else {
            $iconClass = 'itemIconLeftVoid';
          }
          foreach ($x['REGIONS']['items'] as $key => $val) {
            $x['REGIONS']['items'][$key]['MARKERS']['icon'] = '';
            $x['REGIONS']['items'][$key]['MARKERS']['CLASS_iconLeft'] = $iconClass;
          }

          $controlIcons = '';
          // Adds the up and down icons if required
          if ($config['addupdown']) {
            $controlIcons .=
              $this->savlibrary->downButton(
                $this->savlibrary->formName,
                $uid,
                $this->savlibrary->rowItem,
                $config['cryptedFieldName']
              ) .
              $this->savlibrary->upButton(
                $this->savlibrary->formName,
                $uid,
                $this->savlibrary->rowItem,
                $config['cryptedFieldName']
              );
          }
          
          // Adds the delete button if required
          if ($config['adddelete']){
            $controlIcons .=
              $this->savlibrary->deleteItemButton(
                $this->savlibrary->formName,
                $uid,
                $this->savlibrary->rowItem,
                $config['cryptedFieldName']
              );
          }
          
          // Adds the save button with an anchor if required
          if ($config['addsave']){
            // Builds an id for the anchor
            $id = 'a_' . $this->cObj->data['uid'] . '_' . $cpt++;

            $controlIcons =
              '<a id="' . $id . '"></a>' .
              $controlIcons .
              $this->savlibrary->saveButton(
                $this->savlibrary->formName,
                $uid,
                'document.' . $this->savlibrary->formName . '.action=\'#' . $id .'\';'
              );
          }

          // Sets the icons
          $x['REGIONS']['items'][0]['MARKERS']['icon'] = $controlIcons;
          
          $value .= $this->savlibrary->replaceTemplate($x);
        }  		  
		  
  		  // Arrow selectors
  		  $cutLeft = 0;
  		  $cutRight = 0;
  		  if($errors['_subFormId']){
   		    $cutLeft = 1;
  		    $cutRight = 1;
        }
  		  if ($this->savlibrary->limitSub[$config['cryptedFieldName']] > 0) {
          $left = $this->savlibrary->leftArrowButtonSubForm(
            $this->savlibrary->formName,
            $this->savlibrary->limitSub[$config['cryptedFieldName']] - 1,
            $uid,
            $config['cryptedFieldName']
          );
  	 	  } else {
          $left = '';
          $cutLeft = 1;
        }

    		if($maxSubItems && ($this->savlibrary->limitSub[$config['cryptedFieldName']] + 1)*$maxSubItems < $nbitem ) {
          $right = $this->savlibrary->rightArrowButtonSubForm(
            $this->savlibrary->formName,
            $this->savlibrary->limitSub[$config['cryptedFieldName']] + 1,
            $uid,
            $config['cryptedFieldName']
          );
    		} else {
    			$right = '';
      		$cutRight = 1;
    		}
    	}

    	$subForm['MARKERS']['arrows'] = $left . $right;
    	$subForm['CUTTERS']['CUT_arrows'] = ($cutRight && $cutLeft ) ? 1 : 0;
    	

      // Items selectors using pi_browseresults
      $conf = array (
        'pointerName' => 'limitSub',
        'res_count' => $nbitem,
        'results_at_a_time' => ($maxSubItems ? $maxSubItems : 1),
        'pagefloat' => 'center',
        'showFirstLast' => ($config['nofirstlast'] ? false : true),
        'cache' => (
          $this->savlibrary->conf['caching'] & tx_savlibrary::PAGE_BROWSER_IN_SUBFORM ?
          1 :
          0
         ),
      );

      // Sets the form parameters and call the browser
      $formParams = array(
        'formAction' => 'browseSubForm',
        'uid' => $config['uid'],
        'field' => $config['cryptedFieldName'],
        'limitSub' => $this->savlibrary->limitSub[$config['cryptedFieldName']],
      );
  		$subForm['MARKERS']['browse'] = $this->savlibrary->browseresults($conf, $formParams);

  		$subForm['MARKERS']['Value'] = $value;
      $htmlArray[] = $this->savlibrary->replaceTemplate($subForm);  
      
      // Unsets rowItem
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
   
      // Adds the field kept from the parent form
      if (isset($config['keepfieldsinsubformvalues']) && is_array($row)) {
          $row += $config['keepfieldsinsubformvalues'];      
      }
       
		  $fields = $config['fullFieldName'];
      $value = '';
      
      // Builds the subForm
      if (isset($config[$this->savlibrary->cryptTag('0')])) {
        if ($config['func'] == 'makeLink') { 

          $temp = explode(',', $row['link_groups']);          
          if ($config['cutvalue'] || !count(array_intersect($temp, $GLOBALS['TSFE']->fe_user->groupData['uid'])) || ($row['link_enddate'] && $row['link_enddate'] < time() )) {
            $htmlArray[] = '';
          } else {       
            $htmlArray[] = $this->savlibrary->extObj->pi_linkToPage(
              (
                $config['message'] ?
                $config['message'] :
                $this->savlibrary->getLibraryLL('general.clickHere')
              ),
              $row['link_page']
            );
          }
        } else {
          $subForm = array();            
          $subForm[TYPE]= 'subForm';
     
          // Sets the CUTTERS
          $subForm['CUTTERS']['CUT_title'] = 1;
      	  $subForm['CUTTERS']['CUT_arrows'] = 1;
    	
          // Parses the fields
      	  $this->savlibrary->rowItem = $config['_value'];
          $this->savlibrary->processSubForm = true;
		      $x = $this->savlibrary->generateFormTa(
            $config['name'],
            $row,
            array(
              $this->savlibrary->cryptTag('0') => $config[$this->savlibrary->cryptTag('0')]
            ),
            $config['errors'],
            $config['edit']
          );
          $this->savlibrary->processSubForm = false;

          $x['TYPE']= 'subFormItem';
          $x['MARKERS']['icon'] = '';
          $x['MARKERS']['CLASS_iconLeft'] = 'iconLeftVoid';
          $value .= $this->savlibrary->replaceTemplate($x);
		  
  		    $subForm['MARKERS']['Value'] = $value;
          $htmlArray[] = $this->savlibrary->replaceTemplate($subForm); 
        }
      } else {
      
        // Adds the span element
        $htmlArray[] = utils::htmlSpanElement(
          array(
            utils::htmlAddAttribute('class', 'error'),
          ),
          $this->savlibrary->getLibraryLL('error.noFieldSelectedInSubForm')
        );
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
  
 	/**
	 * Graph element
	 *
	 * @param $config array (Configuration array)
	 *
	 * @return string (item to display)
	 */
  public function viewGraph(&$config){

    $htmlArray = array();
    
    // Checks that sav_jpgraph is loaded
    if (t3lib_extMgm::isLoaded('sav_jpgraph')) {
    
      // Defines the constant LOCALE for the use in the template
      define(LOCALE, $GLOBALS['TSFE']->config['config']['locale_all']);

      // Defines the constant CURRENT_PID for the use in the template
      define(CURRENT_PID, $GLOBALS['TSFE']->page['uid']);

      // Defines the constant STORAGE_PID for the use in the template
      $temp = $GLOBALS['TSFE']->getStorageSiterootPids();
      define(STORAGE_PID, $temp['_STORAGE_PID']);

      // Redefines the constant for TTF directory if necessary
      $temp = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['sav_jpgraph']);
      if ($temp['plugin.']['sav_jpgraph.']['ttfDir']) {
        define('TTF_DIR', $temp['plugin.']['sav_jpgraph.']['ttfDir']);
      }
    
      // Defines the main directory
      define('JP_maindir', t3lib_extMgm::extPath('sav_jpgraph') . 'src/');

      // Defines the cache dir
      define('CACHE_DIR', 'typo3temp/sav_jpgraph/');
    
      // Requires the xml class
      require_once(t3lib_extMgm::extPath('sav_jpgraph'). 'class.typo3.php');
      require_once(t3lib_extMgm::extPath('sav_jpgraph'). 'class.xmlgraph.php');
      
      // Creates the xlmgraph
      $xmlGraph = new xmlGraph();
      
      // Sets the markers if any
      if ($config['markers']) {
        $markers = explode(',', $config['markers']);
        $temp = array();
        foreach($markers as $marker) {
          if (preg_match('/^([0-9A-Za-z_]+)#([0-9A-Za-z_]+)=(.*)$/', trim($marker), $match)) {
            $xmlGraph->setReferenceArray($match[1], $match[2], $match[3]);
          }
        }
      }
      
      // Defines the file name for the resulting image
      if (!is_dir('typo3temp/sav_jpgraph')) {
        mkdir('typo3temp/sav_jpgraph');
      }
      $imageFileName = 'typo3temp/sav_jpgraph/img_' .
        $this->savlibrary->formName . '_' . $this->imageCounter . '.png';
      $this->imageCounter++;
      
      // Sets the file reference
      $xmlGraph->setReferenceArray(
        'file',
        1,
        $imageFileName
        );

      // Deletes the file if it exists
      if (file_exists(PATH_site . $imageFileName)) {
        unlink(PATH_site . $imageFileName);
      }

      // Processes the template
      $xmlGraph->loadXmlFile($config['graphtemplate']);
      $xmlGraph->processXmlGraph();
    }
    
    $htmlArray[] = '<img class="jpgraph" src="' . $imageFileName . '" alt="" />';
    
    return $this->savlibrary->arrayToHTML($htmlArray);
  }
  
 	/**
 	 *
	 * Graph element
	 *
	 * @param $config array (Configuration array)
	 *
	 * @return string (item to display)
	 */
  public function viewGraphEditMode(&$config){

    $htmlArray = array();

		$htmlArray[] = $this->viewGraph($config);

    return $this->savlibrary->arrayToHTML($htmlArray);
  }

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/sav_library/class.tx_savlibrary_defaultItemviewers.php']) {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/sav_library/class.tx_savlibrary_defaultItemviewers.php']);
}

?>
