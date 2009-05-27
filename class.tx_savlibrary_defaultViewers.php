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
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

/**
 * SAV Library: standard viewers
 *
 * @author	Yolf <yolf.typo3@orange.fr>
 *
 */


class tx_savlibrary_defaultViewers {


  protected $fileHandle;    // File handle for export operation

  // Variables in calling classes
  protected $savlibrary;      // Reference to the savlibrary object
  protected $cObj;            // Reference to the cObj in the extension
  protected $extConfig;       // Reference to the extension configuration
  protected $extKey;          // Extension Key

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
	 * Default viewer for 'showAll'. 
	 *
	 * This function is called by makeForm 
	 *
	 * @param $dataset array (result of the query after some processing)
	 * @param $fields array (fields configuration)
	 * @param $errors string (errors if any)
	 *
	 * @return array (the template array)
	 */


	public function showAll_defaultViewer(&$dataset, &$fields, $errors='') {

    // If print icon is associated with a related view, call it
    if (t3lib_div::_GET('print') && $this->extConfig['views'][$this->savlibrary->formConfig['showAll']][$this->savlibrary->folderTab]['relViewPrintIcon']) {
      return $this->printForm_defaultViewer(
        $dataset,
        $this->extConfig['views'][$this->extConfig['views'][$this->savlibrary->formConfig['showAll']][$this->savlibrary->folderTab]['relViewPrintIcon']],
        $errors
      );
    }

    $showAllTemplate = $this->extConfig['showAllTemplates'][$this->savlibrary->formConfig['showAll']];

		// Prepare the template
		$tmpl = '<!-- ###item### begin -->'.
				$showAllTemplate['itemTmpl'].'
			<!-- ###item### end -->';
			     
		// Process the dataset
    if (is_array($dataset)) {
  		$ta['REGIONS']['items']='';
  		foreach ($dataset as $key => $row) {
        $nbitem = $row['__nbitem__'];
  			$x = $this->savlibrary->generateFormTa(
          'items',
          $row,
          $fields,
          $errors,
          0
        );

  			// Make some processing to retrieve a simple item type
  			$item = array();

  			if ($x['REGIONS']['items']){
    			foreach($x['REGIONS']['items'] as $k => $v) {

    			  // Clear the field value if the cutter is set
    			  if ($v['CUTTERS']['CUT_value']) {
              $v['MARKERS'][$v['MARKERS']['field']] = '';
            } elseif ($v['WRAPPERS']['wrapitem']) {
              // Check if there is a wrapper
              $v['MARKERS'][$v['MARKERS']['field']] = $this->cObj->dataWrap(
                $v['MARKERS'][$v['MARKERS']['field']],
                $v['WRAPPERS']['wrapitem']
              );
            }

           // Check if the field already exists in the MARKERS.
            if(!array_key_exists($v['MARKERS']['field'], $item)) {
    				  // Add the fied
    				  $item[$v['MARKERS']['field']] = $v['MARKERS'][$v['MARKERS']['field']];
    				  $item[$v['MARKERS']['field'] . '_fullFieldName'] =
                $v['MARKERS'][$v['MARKERS']['field'] . '_fullFieldName'];
            } else {
              $this->savlibrary->addErrorOnce('message.sameFieldName',
                '[' . $this->savlibrary->viewName . ' -> ' . $v['MARKERS']['field'] . ']');
            }
    			}
  			}
        $items['MARKERS'] = $item;
  			$items['TYPE'] = 'item';
  			
        // Process labels associated with forms
        if (preg_match_all('/\$\$\$label\[([^\]]+)\]\$\$\$/', $tmpl, $matches)) {
          foreach ($matches[1] as $keyMatch => $valueMatch) {
            $label = $this->savlibrary->getLL_db(
              'LLL:EXT:' . $this->extKey.'/locallang_db.xml:' .
              $items['MARKERS'][$matches[1][$keyMatch] . '_fullFieldName']
            );
            if ($label) {
              $tmpl = str_replace($matches[0][$keyMatch], $label, $tmpl);
            }
          }
        }      
		
  			// add the type and Value
  			$value = $this->savlibrary->_doTemplateItem($items, $tmpl);

        // process localization tags
        $value = $this->savlibrary->processLocalizationTags($value);
 			
        // Process additionnal markers
        preg_match_all('/###([^#]*)###/', $value, $matches);

        foreach ($matches[1] as $match) {
          $mA['###'.$match.'###'] = $row[$match];
        }   
        $value = $this->cObj->substituteMarkerArrayCached(
          $value,
          $mA,
          array(),
          array()
        );
  			
  			$ta['REGIONS']['items'][$key]['TYPE'] = 'item';
  			$ta['REGIONS']['items'][$key]['MARKERS']['Value'] = $value;

  			// Set the icons
  			if ($this->savlibrary->userIsAdmin($row)) {
          if ($this->savlibrary->inputIsAllowedInForm()) {
            $content = '';
  				  // Add the edit button if allowed
            if (!$this->savlibrary->conf['noEditButton']) {
             	$content .= $this->savlibrary->editButton(
                $this->savlibrary->formName,
                $row['uid']
              );
            }
  				  // Add the delete button if allowed
            if (!$this->savlibrary->conf['noDeleteButton'] && !($this->savlibrary->conf['deleteButtonOnlyForCruser'] && $row['cruser_id']!=$GLOBALS['TSFE']->fe_user->user['uid'])) {
  				    $content .= ($content ? '<br />' : '') .
              $this->savlibrary->deleteButton(
                $this->savlibrary->formName,
                $row['uid']
              );
            }
          }					
  			} else {
  				$content = '&nbsp;';
  			}
  			$ta['REGIONS']['items'][$key]['MARKERS']['itemIconLeft'] = $content;
  			$ta['REGIONS']['items'][$key]['CUTTERS']['CUT_itemIconLeft'] =
          ($this->savlibrary->inputIsAllowedInForm() ? 0 : 1);
  		}      
		} else {
		  switch($this->savlibrary->conf['showNoAvailableInformation']) {
		    case 0: // Show a message
          $ta['REGIONS']['items'][0]['TYPE'] = 'item';
  			  $ta['REGIONS']['items'][0]['MARKERS']['Value'] =
            $this->savlibrary->getLibraryLL('general.noAvailableInformation');
  			  $ta['REGIONS']['items'][0]['MARKERS']['itemIconLeft'] = '';
  			  $ta['REGIONS']['items'][0]['CUTTERS']['CUT_itemIconLeft'] = 1;
          break;
        case 1: // Do not show a message
  		    $ta['REGIONS']['items']='';     
  		    break;
  		  case 2: // Do not show the extension
		      $ta['TYPE'] = 'showAllHidden';
          return $ta;
  		    break;            
      }
    }

		// Show the new button if newButton is allowed
		if ($this->savlibrary->inputIsAllowedInForm()) {
      if ($this->savlibrary->conf['noNewButton']) {
			 $content = '&nbsp;';
			 $ta['MARKERS']['CLASS_titleIconLeft'] = 'titleIconLeftVoid';
		  } else {
			 $content = $this->savlibrary->newButton($this->savlibrary->formName);
			 $ta['MARKERS']['CLASS_titleIconLeft'] = 'titleIconLeft';
		  }
		}

		$ta['TYPE'] = (
      $showAllTemplate['parentTmpl'] ?
      trim($showAllTemplate['parentTmpl']) :
      'showAll'
    );
		$ta['MARKERS']['titleIconLeft'] = $content;

    // Processing for the title
    $ta['MARKERS']['formTitle'] =
      $this->savlibrary->processTitle(
        $fields[$this->savlibrary->cryptTag('0')]['title'],
        $dataset[0]
      );

    // Processing the icon for the input
    $CUT_titleIconRight = !$this->savlibrary->userIsAllowedToInputData();    
    $ta['MARKERS']['titleIconRight'] = '';
    
    // Add the export icon
    if ($this->savlibrary->userIsAllowedToExportData()) {
      $ta['MARKERS']['titleIconRight'] .= $this->savlibrary->exportButton(
        $this->savlibrary->formName,
        0
      );
    }

    // Add the print icon
    if ($this->savlibrary->inputIsAllowedInForm() || $this->savlibrary->userBelongsToAllowedGroup()) {
      if ($this->savlibrary->extObj->extConfig['views'][$this->savlibrary->formConfig['showAll']][$this->savlibrary->folderTab]['addPrintIcon']) {
        $ta['MARKERS']['titleIconRight'] .= $this->savlibrary->printButton(
          $this->savlibrary->formName,
          0
        );
      }  
    }        
    
    // Add the help icon
    if ($this->savlibrary->conf['helpPage']) {
      $ta['MARKERS']['titleIconRight'] .= $this->savlibrary->helpButton(
        $this->savlibrary->formName,
        0
      );
    } 
        
    if ($this->savlibrary->userIsAllowedToInputData()) {
      $ta['MARKERS']['titleIconRight'] .= $this->savlibrary->toggleModeButton(
        $this->savlibrary->formName,
        0
      );
    }
		$ta['CUTTERS']['CUT_titleIconRight'] = 0;    
		$ta['CUTTERS']['CUT_titleIconLeft'] = (
      $this->savlibrary->inputIsAllowedInForm() ?
      0 :
      1
    );

		// arrow selector
		$cutLeft = 0;
		$cutRight = 0;
		
		if ($this->savlibrary->limit > 0) {
			$left = $this->savlibrary->leftArrowButton(
        $this->savlibrary->formName,
        $this->savlibrary->limit -1
      );
		} else {
		  $left = '';
			$cutLeft = 1;
		}

		if($this->savlibrary->conf['maxItems'] && ($this->savlibrary->limit+1)*$this->savlibrary->conf['maxItems'] < $nbitem ) {
			$right = $this->savlibrary->rightArrowButton(
        $this->savlibrary->formName,
        $this->savlibrary->limit + 1
      );
		} else {
		  $right = '';
			$cutRight = 1;
		}

		$ta['MARKERS']['arrows'] = $left . $right;
		$ta['CUTTERS']['CUT_arrows'] = (($cutRight && $cutLeft ) ? 1 : 0);

    // Items selectors using pi_browseresults
    $conf = array (
      'pointerName' => 'limit',
      'res_count' => $nbitem,
      'results_at_a_time' => $this->savlibrary->conf['maxItems'],
      'pagefloat' => 'center',
      'showFirstLast' => ($config['nofirstlast'] ? false : true),
      'cache' => (
        $this->savlibrary->conf['caching'] & tx_savlibrary::PAGE_BROWSER_IN_FORM ?
        1 :
        0
      ),
    );

    // Set the form parameters and call the browser
    $formParams = array(
      'formAction' => 'browse',
      'limit' => $this->savlibrary->limit,
    );
		$ta['MARKERS']['browse'] = $this->savlibrary->browseresults($conf, $formParams);
		
    return $ta;
	}

	/**
	 * Default viewer for 'showSingle'. 
	 *
	 * This function is called by makeForm 
	 *
	 * @param $dataset array (result of the query after some processing)
	 * @param $fields array (fields configuration)
	 * @param $errors string (errors if any)
   *
	 * @return array (the template array)
	 */

	public function showSingle_defaultViewer ($dataset, $fields, $errors='') {

    // If print icon is associated with a related view, call it
    if (t3lib_div::_GET('print') && $this->extConfig['views'][$this->savlibrary->formConfig['showSingle']][$this->savlibrary->folderTab]['relViewPrintIcon']) {
      return $this->printForm_defaultViewer(
        $dataset,
        $this->extConfig['views'][$this->extConfig['views'][$this->savlibrary->formConfig['showSingle']][$this->savlibrary->folderTab]['relViewPrintIcon']],
        $errors
      );
    }
    
		$ta = $this->savlibrary->generateFormTa(
      'items',
      $dataset[0],
      $fields,
      $errors,
      0
    );
		$ta['TYPE'] = 'showSingle';

    // Add the print icon
    $ta['MARKERS']['titleIconRight'] = '';
    if ($this->savlibrary->extObj->extConfig['views'][$this->savlibrary->formConfig['showSingle']][$this->savlibrary->folderTab]['addPrintIcon']) {
      $ta['MARKERS']['titleIconRight'] .= $this->savlibrary->printButton(
        $this->savlibrary->formName,
        $dataset[0]['uid']
      );
    }
   
    // Add the help icon
    if ($this->savlibrary->conf['helpPage']) {
      $ta['MARKERS']['titleIconRight'] .= $this->savlibrary->helpButton(
        $this->savlibrary->formName,
        0
      );
    }      

    $ta['MARKERS']['titleIconRight'] .= $this->savlibrary->closeButton(
      $this->savlibrary->formName,
      $dataset[0]['uid']
    );

    if ($this->savlibrary->userIsAllowedToInputData() && $this->savlibrary->userIsAdmin($dataset[0])) {
      $ta['MARKERS']['titleIconRight'] .= $this->savlibrary->inputModeButton(
        $this->savlibrary->formName,
        $dataset[0]['uid']
      );
    }
		
		return $ta;
	}

	/**
	 * Default viewer for 'inputForm'. 
	 *
	 * This function is called by makeForm 
	 *
	 * @param $dataset array (result of the query after some processing)
	 * @param $fields array (fields configuration)
	 * @param $errors string (errors if any)
 	 *
	 * @return array (the template array)
	 */

	public function inputForm_defaultViewer($dataset, $fields, $errors='') {
	
    // local variables
    $viewId = $this->savlibrary->formConfig['inputForm'];

		$row = $dataset[0];
		if (!$row['uid']) {
			$row['uid'] = 0;
		}
		// ---------------------------------------------------
		$ta = $this->savlibrary->generateFormTa('items', $row, $fields, $errors, 1);
		$ta['TYPE'] = 'inputForm';
		$ta['MARKERS']['titleIconLeft'] = $this->savlibrary->saveButtons(
      $this->savlibrary->formName,
      $row['uid']
    );

    // Add the help icon
    if ($this->savlibrary->conf['helpPage']) {
      $ta['MARKERS']['titleIconLeft'] .= $this->savlibrary->helpButton(
        $this->savlibrary->formName,
        0
      );
    }      
		
    // Add the print icon
    if ($this->extConfig['views'][$this->savlibrary->formConfig['inputForm']][$this->savlibrary->folderTab]['addPrintIcon']) {
      $ta['MARKERS']['titleIconLeft'] .= $this->savlibrary->printButton(
        $this->savlibrary->formName,
        $dataset[0]['uid']
      );
    }

		return $ta;
	}

	/**
	 * Default viewer for 'updateForm'. 
	 *
	 * This function is called by makeForm 
	 *
	 * @param $dataset array (result of the query after some processing)
	 * @param $fields array (fields configuration)
	 * @param $errors string (errors if any)
	 *
	 * @return array (the template array)
	 */


	public function updateForm_defaultViewer(&$dataset, &$fields, $errors='') {

    $showAllTemplate = $this->extConfig['showAllTemplates'][$this->savlibrary->formConfig['updateForm']];

		// Prepare the template
		$tmpl = '<!-- ###item### begin -->'.
				$showAllTemplate['itemTmpl'].'
			<!-- ###item### end -->';
			     
		// Process the dataset
    if (is_array($dataset)) {
  		$ta['REGIONS']['items']='';
  		foreach ($dataset as $key => $row) {
        $nbitem = $row['__nbitem__'];
  			$x = $this->savlibrary->generateFormTa(
          'items',
          $row,
          $fields,
          $errors,
          0
        );

  			// Make some processing to retrieve a simple item type			
  			$items['MARKERS'] = array();
  			if ($x['REGIONS']['items']) {
    			foreach($x['REGIONS']['items'] as $k => $v) {

    			  // Clear the field value if the cutter is set
    			  if ($v['CUTTERS']['CUT_value']) {
              $v['MARKERS'][$v['MARKERS']['field']] = '';
            }

    				// get the name
            $items['MARKERS'] = array_merge($items['MARKERS'], $v['MARKERS']);
          }
  			}
  			$items['TYPE'] = 'item';			       

        // Remove comments
        $tmpl = preg_replace('/\/\/.+/', '', $tmpl);

        // Process labels associated with forms
        if (preg_match_all('/\$\$\$label\[([^\]]+)\]\$\$\$/', $tmpl, $matches)) {
          foreach ($matches[1] as $keyMatch => $valueMatch) {
            $label = $this->savlibrary->getLL_db(
              'LLL:EXT:' . $this->extKey . '/locallang_db.xml:' .
              $items['MARKERS'][$matches[1][$keyMatch] . '_fullFieldName']
            );
            $label .= (
              $items['MARKERS'][$matches[1][$keyMatch] . '_Required'] ?
              '<span class="required">*</span>' :
              ''
            );
            if ($label) {
              $tmpl = str_replace($matches[0][$keyMatch], $label, $tmpl);
            }
          }
        }

        // Process buttons associated with forms
        if (preg_match_all('/###button\[([^\]]+)\]###/', $tmpl, $matches)) {
          foreach ($matches[1] as $keyMatch => $valueMatch) {
            $func = 'user_' . $valueMatch . 'Button';
            if (method_exists($this->savlibrary, $func)) {
              $tmpl = str_replace(
                $matches[0][$keyMatch],
                $this->savlibrary->$func($this->savlibrary->formName, 0),
                $tmpl
              );
            }
          }
        }

        // Process field markers associated with forms
        preg_match_all('/###field\[([^\],]+)(,?)([^\]]*)\]###/', $tmpl, $matches);
        foreach ($matches[1] as $keyMatch => $valueMatch) {
          if ($this->savlibrary->inputIsAllowedInForm()) {      
            $checked = (
              $items['MARKERS'][$matches[1][$keyMatch] . '_Checked'] ?
              'checked ' :
              ''
            );

            if ($items['MARKERS'][$matches[1][$keyMatch] . '_Check']) {
              $checkbox = (
                $matches[2][$keyMatch] ?
                '<div class="updateCol4">' :
                ''
              );

              $temp = utils::htmlInputCheckboxElement(
                array(
                  utils::htmlAddAttribute('class', 'check'),
                  utils::htmlAddAttributeIfNotNull('checked', $checked),
                  utils::htmlAddAttribute('name', 'Check_' . $items['MARKERS'][$matches[1][$keyMatch] . '_Field']),
                  utils::htmlAddAttribute('value', 1),
                )
              );

              if (!$matches[2][$keyMatch] && $items['MARKERS'][$matches[1][$keyMatch] . '_WrapChecked']) {
                $temp = $this->savlibrary->extObj->cObj->dataWrap(
                  $temp,
                  $items['MARKERS'][$matches[1][$keyMatch] . '_WrapChecked']
                );
              }
              $checkbox .= $temp;
              $checkbox .= ($matches[2][$keyMatch] ? '</div>' : '');
            } else {
              $checkbox = (
                $matches[2][$keyMatch] ?
                '<div class="updateCol4Manual">' :
                ''
              );

              $temp = utils::htmlInputCheckboxElement(
                array(
                  utils::htmlAddAttribute('class', 'checkManual'),
                  utils::htmlAddAttributeIfNotNull('checked', $checked),
                  utils::htmlAddAttribute('name', 'Check_' . $items['MARKERS'][$matches[1][$keyMatch] . '_Field']),
                  utils::htmlAddAttribute('value', 1),
                )
              );

              if (!$matches[2][$keyMatch] && $items['MARKERS'][$matches[1][$keyMatch] . '_WrapNotChecked']) {
                $temp = $this->savlibrary->extObj->cObj->dataWrap(
                  $temp,
                  $items['MARKERS'][$matches[1][$keyMatch] . '_WrapNotChecked']
                );
              }
              $checkbox .= $temp;
              $checkbox .= ($matches[2][$keyMatch] ? '</div>' : '');
            }
          } else {
            $checkbox = '';
          }
          
          // Check if label is associated with the field
          if ($matches[2][$keyMatch]) {
            $tmpl = str_replace(
              $matches[0][$keyMatch],
              '<div class="updateCol1">' . $matches[3][$keyMatch] . '</div>' .
                '<div class="updateCol2">###' . $matches[1][$keyMatch] . '###</div>' .
                '<div class="updateCol3">###' . $matches[1][$keyMatch] . '_Edit###</div>' .
                $checkbox,
              $tmpl
            );
          } else {
            $tmpl = str_replace(
              $matches[0][$keyMatch],
              (
                $items['MARKERS'][$matches[1][$keyMatch] . '_Edit'] ?
                '###' . $matches[1][$keyMatch] . '_Edit###' :
                '###' . $matches[1][$keyMatch] . '###'
              ) . $checkbox,
              $tmpl
            );
          }
        }  
         
        preg_match_all('/###newfield\[([^\],]+),?([^\]]*)\]###/', $tmpl, $matches);

        foreach ($matches[1] as $keyMatch => $valueMatch) {
          $tmpl = str_replace(
            $matches[0][$keyMatch],
            '<div class="updateCol1">' . $matches[2][$keyMatch] . '</div>' .
              '<div class="updateCol2">&nbsp;</div>' .
              '<div class="updateCol3">###' . $matches[1][$keyMatch] . '_New###</div>',
            $tmpl
          );
        } 

  			// add the type and Value
  			$value = $this->savlibrary->_doTemplateItem($items, $tmpl); 		
        
        // process localization tags
        $value = $this->savlibrary->processLocalizationTags($value);
                
        // Process additionnal markers
        preg_match_all('/###([^#]*)###/', $value, $matches);
        foreach ($matches[1] as $match) {
          $mA['###'.$match.'###'] = $row[$match];
        }   
        $value = $this->cObj->substituteMarkerArrayCached(
          $value,
          $mA,
          array(),
          array()
        );
 			
  			$ta['REGIONS']['items'][$key]['TYPE'] = 'item';
  			$ta['REGIONS']['items'][$key]['MARKERS']['Value'] = $value;

  		}      
		} else {
		  if ($this->savlibrary->conf['showNoAvailableInformation']) {
  			$ta['REGIONS']['items'][0]['TYPE'] = 'item';
  			$ta['REGIONS']['items'][0]['MARKERS']['Value'] =
          $this->savlibrary->getLibraryLL('general.noAvailableInformation');
  			$ta['REGIONS']['items'][0]['MARKERS']['itemIconLeft'] = '';
  			$ta['REGIONS']['items'][0]['CUTTERS']['CUT_itemIconLeft'] = 1;
			} else {
  		  $ta['REGIONS']['items']='';     
      }
    }

		$ta['TYPE'] = (
      $showAllTemplate['parentTmpl'] ?
      trim($showAllTemplate['parentTmpl']) :
      'updateForm'
    );

    // Processing for the title
    $ta['MARKERS']['formTitle'] = $this->savlibrary->processTitle(
      $fields[$this->savlibrary->cryptTag('0')]['title'],
      $dataset[0]
    );

    // Processing the icon for the submit button
    $CUT_titleIconRight = 0;
    $ta['MARKERS']['titleIconRight'] = '';
    
    // Add the help icon
    if ($this->savlibrary->conf['helpPage']) {
      $ta['MARKERS']['titleIconRight'] .= $this->savlibrary->helpButton(
        $this->savlibrary->formName,
        0
      );
    }     
    
    $ta['MARKERS']['titleIconRight'] .= (
      $this->savlibrary->inputIsAllowedInForm() ?
      $this->savlibrary->submitAdminButton($this->savlibrary->formName, 0) :
      $this->savlibrary->submitButton($this->savlibrary->formName, 0)
    );
         
		$ta['CUTTERS']['CUT_titleIconRight'] = $CUT_titleIconRight;    
     
		return $ta;
	}

	/**
	 * Default viewer for 'printForm'. 
	 *
	 * This function is called by makeForm 
	 *
	 * @param $dataset array (result of the query after some processing)
	 * @param $fields array (fields configuration)
	 * @param $errors string (errors if any)
	 *
	 * @return array (the template array)
	 */


	public function printForm_defaultViewer(&$dataset, &$fields, $errors='') {

    $showAllTemplate =
      $this->extConfig['showAllTemplates'][$this->savlibrary->formConfig['altForm']];
    
		// Prepare the template
		$tmpl = '<!-- ###item### begin -->'.
				$showAllTemplate['itemTmpl'].'
			<!-- ###item### end -->';
			     
		// Process the dataset
    if (is_array($dataset)) {
  		$ta['REGIONS']['items']='';
  		$cpt = 0;
  		$firstPageProcessed = 0;
  		
  		foreach ($dataset as $key => $row) {
        $nbitem = $row['__nbitem__'];
  			$x = $this->savlibrary->generateFormTa(
          'items',
          $row,
          $fields,
          $errors,
          0
        );

  			// Make some processing to retrieve a simple item type			
  			$items['MARKERS'] = array();
        $cut = array();
  			foreach($x['REGIONS']['items'] as $k => $v) {

  			  // Clear the field value if the cutter is set
  			  if ($v['CUTTERS']['CUT_value']) {
            $v['MARKERS'][$v['MARKERS']['field']] = '';
            $cut[$v['MARKERS']['field']] = 1;
          } elseif ($v['WRAPPERS']['wrapitem']) {
            // Check if there is a wrapper
            $v['MARKERS'][$v['MARKERS']['field']] = $this->cObj->dataWrap(
              $v['MARKERS'][$v['MARKERS']['field']],
              $v['WRAPPERS']['wrapitem']
            );
          }
          
  				// get the name
          $items['MARKERS'] = array_merge($items['MARKERS'], $v['MARKERS']);
  			}
  			$items['TYPE'] = 'item';			

        // Process labels associated with forms
        if (preg_match_all('/\$\$\$label\[([^\]]+)\]\$\$\$/', $tmpl, $matches)) {
          foreach ($matches[1] as $keyMatch => $valueMatch) {
            $label = $this->savlibrary->getLL_db(
              'LLL:EXT:' . $this->extKey.'/locallang_db.xml:' .
              $items['MARKERS'][$matches[1][$keyMatch] . '_FieldName']
            );
            $label .= (
              $items['MARKERS'][$matches[1][$keyMatch] . '_Required'] ?
              '<span class="required">*</span>' :
              ''
            );
            if ($label) {
              $tmpl = str_replace($matches[0][$keyMatch], $label, $tmpl);
            }
          }
        }

        // Process markers associated with forms
        preg_match_all('/###field\[([^\],]+),?([^\]]*)\]###/', $tmpl, $matches);

        foreach ($matches[1] as $keyMatch => $valueMatch) {
          if (!$cut[$valueMatch]) {
            $tmpl = str_replace(
              $matches[0][$keyMatch],
              '<div class="printCol1">' . $matches[2][$keyMatch] . '</div>' .
                '<div class="printCol2">###' . $matches[1][$keyMatch] . '###</div>',
              $tmpl
            );
          }
        }  

  			// add the type and Value
  			$value = $this->savlibrary->_doTemplateItem($items, $tmpl);

        // process localization tags
        $value = $this->savlibrary->processLocalizationTags($value);

        // Process additionnal markers
        preg_match_all('/###([^#]*)###/', $value, $matches);

        foreach ($matches[1] as $match) {
          $mA['###'.$match.'###'] = $row[$match];
        }   
        $value = $this->cObj->substituteMarkerArrayCached(
          $value,
          $mA,
          array(),
          array()
        );
        
        // add the page break cutter        
  			$ta['REGIONS']['items'][$key]['CUTTERS']['CUT_break'] = 1;
        $cpt++;

        if (isset($showAllTemplate['pagebreakfirstpage']) && $showAllTemplate['pagebreakfirstpage'] && !$firstPageProcessed && ($cpt == $showAllTemplate['pagebreakfirstpage'])) {
          $firstPageProcessed = 1;
          $cpt = 0;
          $ta['REGIONS']['items'][$key]['CUTTERS']['CUT_break'] = 0;         
        }

        if (isset($showAllTemplate['pagebreak']) && ($cpt == $showAllTemplate['pagebreak'])) {
          $cpt = 0;
          $ta['REGIONS']['items'][$key]['CUTTERS']['CUT_break'] = 0;
        }

  			$ta['REGIONS']['items'][$key]['TYPE'] = 'item';
  			$ta['REGIONS']['items'][$key]['MARKERS']['Value'] = $value;
  		}      
		} else {
		  if ($this->savlibrary->conf['showNoAvailableInformation']) {
  			$ta['REGIONS']['items'][0]['TYPE'] = 'item';
  			$ta['REGIONS']['items'][0]['MARKERS']['Value'] =
          $this->savlibrary->getLibraryLL('general.noAvailableInformation');
  			$ta['REGIONS']['items'][0]['MARKERS']['itemIconLeft'] = '';
  			$ta['REGIONS']['items'][0]['CUTTERS']['CUT_itemIconLeft'] = 1;
			} else {
  		  $ta['REGIONS']['items']='';     
      }
    }

		$ta['TYPE'] = (
      $showAllTemplate['parentTmpl'] ?
      trim($showAllTemplate['parentTmpl']) :
      'printForm'
    );

    // Processing for the title
    $ta['MARKERS']['formTitle'] = $this->savlibrary->processTitle(
      $fields[$this->savlibrary->cryptTag('0')]['title'],
      $dataset[0]
    );
     
		return $ta;
	}


	/**
	 * Default viewer for 'export'. 
	 *
	 * This function is called by makeForm 
	 *
	 * @param $dataset array (result of the query after some processing)
	 * @param $fields array (fields configuration)
	 * @param $errors string (errors if any)
 	 *
	 * @return array (the template array)
	 */

	public function export_defaultViewer ($dataset, $fields, $errors='') {

    // Check if data were posted
    $extPOSTVars = t3lib_div::_POST($this->savlibrary->formName);

    // Define the exclude list of fields
    $excludeFields = array (
      'uid','pid','crdate','tstamp','hidden','deleted','cruser_id','disable',
      'starttime','endtime','password','lockToDomain','is_online','lastlogin',
      'TSconfig',
    );

    // Get the ressource id of the query    
    $func = trim($this->savlibrary->savlibraryConfig['queriers']['select']['export']);
    $query = $this->extConfig['queries'][$this->savlibrary->formConfig['query']];

    $table = 'tx_savlibrary_export_configuration';
    $exportOK = 1;
    $showSelectedFieldsOnly = (
      $extPOSTVars['showSelectedFieldsOnly'] ?
      $extPOSTVars['showSelectedFieldsOnly'] :
      0
    );

    if (!$extPOSTVars) { 
      $query['limit'] = 1;
      $exportOK = 0;
    } else {      
      if (isset($extPOSTVars['deleteExportConfiguration'])) {

		    $res = $GLOBALS['TYPO3_DB']->exec_UPDATEquery(
          /* TABLE   */	$table,
			    /* WHERE   */	$table . '.uid=' . intval($extPOSTVars['configuration'][0]),
			    /* FIELDS  */	array('deleted' => 1)
			  );
        foreach($extPOSTVars as $k=>$v) {
          if (is_array($v[0])) {
            foreach($v[0] as $k1=>$v1) {
               $extPOSTVars[$k][0][$k1] = '';
            }
          } else {
            $extPOSTVars[$k][0] = '';
          }  
        }
        $exportOK = 0;
      } elseif (isset($extPOSTVars['saveExportConfiguration'])) {
        unset($extPOSTVars['saveExportConfiguration']);
  			$fields['tstamp'] = time();
        
        if (!$extPOSTVars['configuration'][0]) {  
  				$fields['cruser_id'] = $GLOBALS['TSFE']->fe_user->user['uid'];
  				$fields['crdate'] = time();
          $fields['pid'] = $GLOBALS['TSFE']->id;
          $fields['cid'] = $this->cObj->data['uid'];
                     
   				$res = $GLOBALS['TYPO3_DB']->exec_INSERTquery(
    				/* TABLE   */	$table,
    				/* FIELDS  */	$fields
    			);
    			$extPOSTVars['configuration'][0] = $GLOBALS['TYPO3_DB']->sql_insert_id($res);
    		} else {
          $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
  				  /* SELECT   */	'*',
  				  /* FROM   */	$table,
  				  /* WHERE   */	$table.'.uid='.intval($extPOSTVars['configuration'][0])
          );                
          $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);       
        }
  			$fields['name'] = (
          $extPOSTVars['configurationName'][0] ?
          $extPOSTVars['configurationName'][0] :
          (
            $extPOSTVars['configuration'][0] ?
            $row['name'] :
            $this->savlibrary->getLibraryLL('general.new')
          )
        );
        
  			$fields['configuration'] = serialize($extPOSTVars);
  			$fields['fe_group'] = $extPOSTVars['configurationGroup'][0];
  			
        $res = $GLOBALS['TYPO3_DB']->exec_UPDATEquery(
  				/* TABLE   */	$table,		
  				/* WHERE   */	$table.'.uid='.intval($extPOSTVars['configuration'][0]),  		
  				/* FIELDS  */	$fields
        );                
        $exportOK = 0;
      } elseif (isset($extPOSTVars['toggleExportDisplay'])) {
        $showSelectedFieldsOnly = ($showSelectedFieldsOnly ? 0 : 1);      
        $exportOK = 0;
      }
   
      // Load the configuration if any
      if (isset($extPOSTVars['loadExportConfiguration'])) {
        if ($extPOSTVars['configuration'][0]) {
          $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
  				    /* SELECT   */	'*',		
  				    /* FROM     */	$table,
  	 			    /* WHERE    */	'uid=' . intval($extPOSTVars['configuration'][0])
  		    );
  		    $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
          $extPOSTVars = unserialize($row['configuration']); 
          $showSelectedFieldsOnly = 1;
        } else {
          // Clear data
          foreach($extPOSTVars as $k=>$v) {
            if (is_array($v[0])) {
              foreach($v[0] as $k1=>$v1) {
                $extPOSTVars[$k][0][$k1] = '';
              }
            } else {
              $extPOSTVars[$k][0] = '';
            }  
          }
        }           
      unset($extPOSTVars['configurationName']);
      $exportOK = 0;      
      }

      // Build the list of the fields to export            
      if ($exportOK && is_array($extPOSTVars['fields'][0])) {
        $fields = array_keys($extPOSTVars['fields'][0]);
        foreach($fields as $key => $field) {
          if (strpos($field, '.') === false || !$extPOSTVars['fields'][0][$field]) {
            unset($fields[$key]);
          }
        }
        $query['fields'] = $query['tableLocal'] . '.uid,' .
          implode(',', $fields);
      }
      $query['fields'] = (
        $extPOSTVars['includeAllFields'][0] ?
        '*' :
        $query['fields']
      );
      $query['addTables']  = $extPOSTVars['additionalTables'][0];
      $query['aliases'] = trim(
        $extPOSTVars['additionalFields'][0] ?
        (
          $query['aliases'] ?
          $query['aliases'] . ',' . $extPOSTVars['additionalFields'][0] :
          $extPOSTVars['additionalFields'][0]
          ) :
        $query['aliases']
      );
      $query['where'] = $this->savlibrary->queriers->processWhereClause(
        $extPOSTVars['where'][0]
      );
      $query['order'] = (
        $extPOSTVars['order'][0] ?
        $extPOSTVars['order'][0] :
        $query['order']
      );
      $query['group'] = (
        $extPOSTVars['exportMM'][0] ?
        ($extPOSTVars['groupBy'][0] ? $extPOSTVars['groupBy'][0] : '' ) :
        $query['group']
      );
      $exportOK =
        $exportOK &&
        !$extPOSTVars['includeAllFields'][0] &&
        (
          (
            $extPOSTVars['additionalTablesValidated'][0] &&
            $extPOSTVars['additionalTables'][0]
        ) ||
        !$extPOSTVars['additionalTables'][0]
        );
      $extPOSTVars['additionalTablesValidated'][0] = (
        $extPOSTVars['additionalTablesValidated'][0] &&
        $extPOSTVars['additionalTables'][0]
      );
    }
    $res = $this->savlibrary->queriers->$func($query, $this->savlibrary->uid);

    // Display the form      
		$ta['TYPE'] = 'showSingle';
		$ta['CUTTERS']['CUT_folderTabsTop'] = 1;

    // Display the selector with the saved configuration
    $config = array(
      'emptyitem' => 1,
      'items' => array(),
  		'elementControlName' => $this->savlibrary->formName . '[configuration][0]',
      'value' => $extPOSTVars['configuration'][0],
    );

    $res1 = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
  		/* SELECT   */	'*',
  		/* FROM     */	$table,
  	 	/* WHERE    */	'cid=' . intval($this->cObj->data['uid']) .
        ' AND ' . $table . '.deleted=0 AND ' . $table . '.hidden=0' .
        ' AND (' .
        '(cruser_id=' . intval($GLOBALS['TSFE']->fe_user->user['uid']) .
        ' AND (' . $table . '.fe_group=\'\' OR ' . $table . '.fe_group IS NULL OR ' . $table . '.fe_group=0))' .
        ' OR ' . $table . '.fe_group IN (' . $GLOBALS['TSFE']->fe_user->user['usergroup'] . '))' .
        '',
  		/* GROUP BY */	'',
  		/* ORDER BY */	'name',
  		/* LIMIT    */	''
  	);

  	while (($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res1))) {
  		$item = array();
  		$item['label'] = $row['name'];
  		$item['uid'] = $row['uid'];
  		if($extPOSTVars['configuration'][0] == $row['uid']) {
        $item['selected'] = 1;
      }
  		$config['items'][] = $item;
    };
    $cutters = array('CUT_fusionEnd' => 1);
    $markers = array(
      'Label' => $this->savlibrary->getLibraryLL('itemviewer.configuration'),
      'Value' => $this->savlibrary->itemviewers->viewDbRelationSingleSelectorEditMode($config),
    );
    $ta['REGIONS']['items'][] = $this->buildItemArray($markers, $cutters);

    $config = array(
  		'elementControlName' => $this->savlibrary->formName.'[configurationName][0]',
      'value' => $extPOSTVars['configurationName'][0],
    );
    $cutters = array(
      'CUT_label' => 1,
      'CUT_fusionBegin' => 1,
    );
    $markers = array(
      'Value' =>
        $this->savlibrary->loadExportConfiguration($this->savlibrary->formName) .
        $this->savlibrary->saveExportConfiguration($this->savlibrary->formName) .
        $this->savlibrary->deleteExportConfiguration($this->savlibrary->formName) .
        $this->savlibrary->itemviewers->viewStringInputEditMode($config) .
        $this->savlibrary->toggleExportDisplay($this->savlibrary->formName, $showSelectedFieldsOnly) .
        utils::htmlInputHiddenElement(
          array(
            utils::htmlAddAttribute('name', $this->savlibrary->formName . '[showSelectedFieldsOnly]'),
            utils::htmlAddAttribute('value', $showSelectedFieldsOnly),
          )
        ),
    );
    $ta['REGIONS']['items'][] = $this->buildItemArray($markers, $cutters);
    // Add the group of the fe_user
    $config = array(
      'emptyitem' => 1,
      'items' => array(),
  		'elementControlName' => $this->savlibrary->formName.'[configurationGroup][0]',
      'value' => $extPOSTVars['configurationGroup'][0],
    );
    foreach($GLOBALS['TSFE']->fe_user->groupData['title'] as $keyGroup => $valueGroup) {
  		$item = array();
  		$item['label'] = $valueGroup;
  		$item['uid'] = $keyGroup;
  		if($extPOSTVars['configurationGroup'][0] == $keyGroup) {
        $item['selected'] = 1;
      }
  		$config['items'][] = $item;
    }
    $cutters = array();
    $markers = array(
      'Label' => $this->savlibrary->getLibraryLL('itemviewer.configurationGroup'),
      'Value' => $this->savlibrary->itemviewers->viewDbRelationSingleSelectorEditMode($config),
    );
    $ta['REGIONS']['items'][] = $this->buildItemArray($markers, $cutters);
      
    // Display the error if any
    if (isset($res['ERROR'])) {
      $config = array(
        'value' => '<b>ERROR : </b><br />' .
          $res['ERROR'] . '<br /><br /><b>QUERY : </b><br />' .
          $res['lastBuiltQuery'],
      );
      $cutters = array();
      $markers = array(
        'Label' => $this->savlibrary->getLibraryLL('itemviewer.error'),
        'Value' => $this->savlibrary->itemviewers->viewTextArea($config),
        'classValue' => 'class="error"',
      );
      $ta['REGIONS']['items'][] = $this->buildItemArray($markers, $cutters);
 
    } else {    
      $nbRows = $GLOBALS['TYPO3_DB']->sql_num_rows($res);
  
      // Display the checkboxes
      $config = array(
        'cols' => 2,
  		  'elementControlName' => $this->savlibrary->formName.'[fields][0]',
      );
 
      // Get the fields
      $cpt = 0;
      while ($field = mysql_fetch_field($res)) {
        if (!in_array($field->name, $excludeFields) || $extPOSTVars['includeAllFields'][0]) {
          $this->savlibrary->queriers->sqlFieldsExport[$cpt++] = $field;
        }
      }

      $extPOSTVars['includeAllFields'][0] = 0;

      // Build Alias fields. An alias field is defined by a reqValue attribute in the showAll form
      $aliasFields = array();
      if (is_array($this->extConfig['views'][$this->savlibrary->formConfig['showAll']][$this->savlibrary->cryptTag('0')]['fields'])) {
        foreach ($this->extConfig['views'][$this->savlibrary->formConfig['showAll']][$this->savlibrary->cryptTag('0')]['fields'] as $keyField => $valueField) {
          if (array_key_exists('reqvalue', $valueField['config'])) {
            $name = 'showAll_' . $valueField['config']['field'];
            $this->savlibrary->queriers->sqlFieldsExport[$cpt++]->name = $name;
            $aliasFields[$name] = $valueField;
          }
        }
      } 
    }

    // Process the fields
    if (is_array($this->savlibrary->queriers->sqlFieldsExport)) {
      foreach ($this->savlibrary->queriers->sqlFieldsExport as $key=>$sqlField) {
        $field = (
          $sqlField->table ?
          $sqlField->table . '.' . $sqlField->name :
          $sqlField->name
        );

        // skip the field if not selected and showSelectedFieldsOnly is set
        if (!$extPOSTVars['fields'][0][$field] && $showSelectedFieldsOnly) {
          continue;
        }
        $config['items'][$field][0] = $field;
        $config['items'][$field]['addattributes'] = 'ondblclick="' .
          $this->savlibrary->formName . '[\'' . $this->savlibrary->formName .
          '[where][0]\'].value+=\'' .
          stripslashes($config['items'][$field][0]) . '\'"';
        if ($extPOSTVars) {
          $config['items'][$field][1] = $extPOSTVars['fields'][0][$field];
        }
      }
    }
    $cutters = array('CUT_label' => 1);
    $markers = array(
      'Value' => (
        ($nbRows || !$exportOK) ?
        $this->savlibrary->itemviewers->viewCheckboxEditMode($config) :
        $this->savlibrary->getLibraryLL('warning.noRecord')
      ),
      'classValue' => (
        ($nbRows || !$exportOK) ?
        'class="export"' :
        'class="error"'
      ),
    );
    $ta['REGIONS']['items'][] = $this->buildItemArray($markers, $cutters);
    
    // Display the textarea for the where clause
    $config = array(
      'rows' => 5,
      'cols' => 70,
  		'elementControlName' => $this->savlibrary->formName . '[where][0]',
      'value' => $extPOSTVars['where'][0],
    );
    $cutters = array();
    $markers = array(
      'Label' => $this->savlibrary->getLibraryLL('itemviewer.whereClause'),
      'Value' => $this->savlibrary->itemviewers->viewTextAreaEditMode($config),
    );
    $ta['REGIONS']['items'][] = $this->buildItemArray($markers, $cutters);
    
    // Display the textarea for the order clause
    $config = array(
      'size' => 70,
  		'elementControlName' => $this->savlibrary->formName . '[order][0]',
      'value' => $extPOSTVars['order'][0],
    );
    $cutters = array();
    $markers = array(
      'Label' => $this->savlibrary->getLibraryLL('itemviewer.orderClause'),
      'Value' => $this->savlibrary->itemviewers->viewStringInputEditMode($config),
    );
    $ta['REGIONS']['items'][] = $this->buildItemArray($markers, $cutters);

    // Display the textinput for additional tables
    $config = array(
      'size' => 70,
  		'elementControlName' => $this->savlibrary->formName . '[additionalTables][0]',
      'value' => $extPOSTVars['additionalTables'][0],
    );
    $config1 = array(
  		'elementControlName' => $this->savlibrary->formName . '[additionalTablesValidated][0]',
      'value' => $extPOSTVars['additionalTablesValidated'][0],
    );
    $cutters = array();
    $markers = array(
      'Label' => $this->savlibrary->getLibraryLL('itemviewer.additionalTables'),
      'Value' => $this->savlibrary->itemviewers->viewStringInputEditMode($config) .
        $this->savlibrary->itemviewers->viewCheckboxEditMode($config1),
    );
    $ta['REGIONS']['items'][] = $this->buildItemArray($markers, $cutters);
    
    // Display the textinput for additional fields
    $config = array(
      'size' => 70,
  		'elementControlName' => $this->savlibrary->formName . '[additionalFields][0]',
      'value' => $extPOSTVars['additionalFields'][0],
    );
    $cutters = array();
    $markers = array(
      'Label' => $this->savlibrary->getLibraryLL('itemviewer.additionalFields'),
      'Value' => $this->savlibrary->itemviewers->viewStringInputEditMode($config),
    );
    $ta['REGIONS']['items'][] = $this->buildItemArray($markers, $cutters);

    // Display the checkbox to export all MM records
    $config = array(
  		'elementControlName' => $this->savlibrary->formName . '[exportMM][0]',
      'value' => $extPOSTVars['exportMM'][0],
    );
    $cutters = array('CUT_fusionEnd' => 1);
    $markers = array(
      'Label' => $this->savlibrary->getLibraryLL('itemviewer.exportMM'),
      'Value' => $this->savlibrary->itemviewers->viewCheckboxEditMode($config),
    );
    $ta['REGIONS']['items'][] = $this->buildItemArray($markers, $cutters);

    // Display the textinput for a group clause with MM records
    $config = array(
      'size' => 50,
  		'elementControlName' => $this->savlibrary->formName . '[groupBy][0]',
      'value' => $extPOSTVars['groupBy'][0],
    );
    $cutters = array('CUT_fusionBegin' => 1);
    $markers = array(
      'Label' => $this->savlibrary->getLibraryLL('itemviewer.groupBy'),
      'Value' => $this->savlibrary->itemviewers->viewStringInputEditMode($config),
    );
    $ta['REGIONS']['items'][] = $this->buildItemArray($markers, $cutters);

    // Display the checkbox to include all fields
    $config = array(
  		'elementControlName' => $this->savlibrary->formName . '[includeAllFields][0]',
      'value' => $extPOSTVars['includeAllFields'][0],
    );
    $cutters = array('CUT_fusionEnd' => 1);
    $markers = array(
      'Label' => $this->savlibrary->getLibraryLL('itemviewer.includeAllFields'),
      'Value' => $this->savlibrary->itemviewers->viewCheckboxEditMode($config),
    );
    $ta['REGIONS']['items'][] = $this->buildItemArray($markers, $cutters);
    
    // Display the checkbox to include the field names
    $config = array(
  		'elementControlName' => $this->savlibrary->formName . '[exportFieldNames][0]',
      'value' => $extPOSTVars['exportFieldNames'][0],
    );
    $cutters = array('CUT_fusionBegin' => 1);
    $markers = array(
      'Label' => $this->savlibrary->getLibraryLL('itemviewer.exportFieldNames'),
      'Value' => $this->savlibrary->itemviewers->viewCheckboxEditMode($config),
    );
    $ta['REGIONS']['items'][] = $this->buildItemArray($markers, $cutters);
    
    // Display the input to order the fields
    $config = array(
      'rows' => 5,
      'cols' => 70,
  		'elementControlName' => $this->savlibrary->formName . '[orderedFieldList][0]',
      'value' => $extPOSTVars['orderedFieldList'][0],
    );
    $cutters = array();
    $markers = array(
      'Label' => $this->savlibrary->getLibraryLL('itemviewer.orderedFieldList'),
      'Value' => $this->savlibrary->itemviewers->viewTextAreaEditMode($config),
    );
    $ta['REGIONS']['items'][] = $this->buildItemArray($markers, $cutters);

    // Processing for the title
		if (!isset($ta['MARKERS']['formTitle'])) {
      $ta['MARKERS']['formTitle'] = $this->extConfig['showAllTemplates'][$this->savlibrary->formConfig['showAll']]['config']['title'];
    }

    // Add the export icon
    if ($this->savlibrary->userIsAllowedToExportData()) {
      $ta['MARKERS']['titleIconRight'] .=
      $this->savlibrary->exportokButton($this->savlibrary->formName);
    }      
    
    // Add the help icon
    if ($this->savlibrary->conf['helpPage']) {
      $ta['MARKERS']['titleIconLeft'] .=
        $this->savlibrary->helpButton($this->savlibrary->formName);
    }      

    $ta['MARKERS']['titleIconRight'] .= $this->savlibrary->closeButton(
      $this->savlibrary->formName,
      $dataset[0]['uid']
    );

    // Export the fields in csv
    if ($exportOK && is_array($extPOSTVars['fields']) && in_array(1, $extPOSTVars['fields'][0]) && $nbRows > 0 && !isset($res['ERROR'])) { 
      $fileName = $this->savlibrary->formName.date('_Y_m_d_H_i').'.csv';
		  $strFilepath = PATH_site . 'typo3temp/'.$fileName;
		  t3lib_div::unlink_tempfile($strFilepath);
		  
		  if ($fileHandle = fopen($strFilepath,'ab')) {

		    // Export the field names
        if ($extPOSTVars['exportFieldNames'][0]) {	    
  		    $arrValues = array();
  	 	    if ($extPOSTVars['fields'][0]) {

            $orderedFieldList = explode(
              ';',
              preg_replace('/[\n\r]/', '', $extPOSTVars['orderedFieldList'][0])
            );
            
            $fields = array_keys($extPOSTVars['fields'][0]);
            $fieldList = array_merge(
              $orderedFieldList,
              array_diff($fields, $orderedFieldList)
            );

    		    foreach($fieldList as $key => $field) {
              if ($extPOSTVars['fields'][0][$field]) {
                $arrValues[] = $field;
              }
            }
            fwrite($fileHandle, $this->csvValues( $arrValues,';') . chr(10));
          }
        }

		    // Export the fields        
        $cpt = 0;
    		while ($row = $this->savlibrary->queriers->sql_fetch_assoc_with_tablename($res, $cpt++)) {		  

		      $arrValues = array();
		      if ($extPOSTVars['fields'][0]) {

            $orderedFieldList = explode(
              ';',
              preg_replace('/[\n\r]/', '', $extPOSTVars['orderedFieldList'][0])
            );
            $fields = array_keys($extPOSTVars['fields'][0]);
            $fieldList = array_merge(
              $orderedFieldList,
              array_diff($fields, $orderedFieldList)
            );

            foreach ($fieldList as $key => $field) {

              if ($extPOSTVars['fields'][0][$field]) {

                if (array_key_exists($field, $aliasFields)) {

                  $config = $this->savlibrary->getConfig($aliasFields[$field]);                 
           			  $config['type'] = (
                    $config['type'] ?
                    $config['type'] :
                    'input'
                  );
       				
           				// Process the query
                  $queryReqValue = $config['reqvalue'];
                  $table = $config['table'];
                  if (preg_match_all('/###row\[([^\]]+)\]###/', $queryReqValue, $matches)) {
                    foreach ($matches[0] as $k => $match) {
                      $mA[$matches[0][$k]] = $this->savlibrary->getValue(
                        $table,
                        $matches[1][$k],
                        $row
                      );
                    }
                  }

                  $mA['###uid###'] = $row[$config['table'] . '.uid'];
                  $mA['###uidParent###'] = $row[$query['tableLocal'] . '.uid'];
                  $mA['###user###'] = $GLOBALS['TSFE']->fe_user->user['uid'];
                  $queryReqValue = $this->cObj->substituteMarkerArrayCached(
                    $queryReqValue,
                    $mA,
                    array(),
                    array()
                  );

                  // Check if the query is a SELECT query and for errors
                  if (!$this->savlibrary->isSelectQuery($queryReqValue)) {
                    $this->savlibrary->addError(
                      'error.onlySelectQueryAllowed',
                      $config['field']
                    );
                    $arrError['reqValue'] = false;
                    continue;
                  } elseif (!($resLocal = $GLOBALS['TYPO3_DB']->sql_query($queryReqValue))) {
                    $this->savlibrary->addError(
                      'error.incorrectQueryInReqValue',
                      $config['field']
                    );
                    $arrError['reqValue'] = false;
                    continue;
                  }

                  // Process the query
		              $value='';
		              while ($rows = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($resLocal)) {
		                if (array_key_exists('value',$rows)) {
                      $config['value'] = stripslashes($rows['value']);
                    }
		              }   
         				
                } else {
                  $config = $this->savlibrary->getConfig($field, 1);
                 
                  // Special processing for exclude fields
                  switch ($config['field']) {
                    case 'tstamp':
                    case 'crdate':
                      $config['eval'] = 'datetime';
                      break;
                  }            
           			  $config['type'] = ($config['type'] ? $config['type'] : 'input');
                  $config['_value'] = stripslashes($row[$field]);
                  $config['value'] = stripslashes($row[$field]);
                }

                if (($func = $this->savlibrary->getFunc($config))) {
                
                  // Special preprocessing according to the type
                  switch ($config['type']) {
                    case 'select':
                      $config['separator'] = ',';
                      $config['nohtmlprefix'] = true;
                      break;
                  }

                  $value = html_entity_decode($this->savlibrary->itemviewers->$func($config));
                
                  // Special postprocessing according to the type
                  switch ($config['type']) {
                    case 'text':
                      $value = preg_replace('/<br \/>/','', $value);
                      $value = str_replace(chr(13), '', $value); 
                      break;
                  }                      
                } else {
                  $value = $config['value'];                
                }              
                $arrValues[] = $value;
              } elseif (preg_match('/ (as|AS) ' . $field . '/', $extPOSTVars['additionalFields'][0])) {
                $arrValues[] = stripslashes($row[$field]);
              }
            }

            fwrite($fileHandle, $this->csvValues( $arrValues,';') . chr(10));
          }
        }       
      }

			$arrError['close'] = fclose($fileHandle);
			t3lib_div::fixPermissions($strFilepath);

		  if (!in_array(FALSE,$arrError)) {
			 	header('Content-Disposition: attachment; filename=' . $fileName.'');
				header('Content-type: x-application/octet-stream');
				header('Content-Transfer-Encoding: binary');
				header('Content-length:' . filesize($strFilepath).'');
				readfile($strFilepath);
      }

    }   
	
		return $ta;
  }

   /***************************************************************
    *
    *   Utils
    *
   ***************************************************************/

	/**
	 * build an item array
	 *
	 * @param	$markers array		item array containing the marker configurations
	 * @param	$cutters array		item array containing the cuuter configurations
	 * @return	array		Merged arrays
	 */
  public function buildItemArray($markers, $cutters) {
    $cuttersDefault = array(
      'CUT_label' => 0,
      'CUT_value' => 0,
      'CUT_fusionBegin' => 0,
      'CUT_fusionEnd' => 0,
    );
    $markersDefault = array(
      'Label' => '',
      'Value' => '',
      'styleLabel' => '',
      'classLabel' => 'class="label"',
      'styleValue' => '',
      'classValue' => 'class="export"',
      'subform' => '',
    );

    // Merged the arrays
    $item['TYPE'] = 'item';
    $item['MARKERS'] = array_merge($markersDefault, $markers);
    $item['CUTTERS'] = array_merge($cuttersDefault, $cutters);

    return $item;
  }


	/**
	 * Takes a row and returns a CSV string of the values with $delim (default is ,) and $quote (default is ") as separator chars.
	 * Usage: 5
	 *
	 * @param	array		Input array of values
	 * @param	string		Delimited, default is comman
	 * @param	string		Quote-character to wrap around the values.
	 * @return	string		A single line of CSV
	 */
	protected function csvValues($row, $delim=',', $quote='"')	{

		reset($row);
		$out = array();
		while(list(,$value)=each($row))	{
// Modification to keep multiline information		
//			list($valPart) = explode(chr(10),$value);
//			$valPart = trim($valPart);
      $valPart = $value;
			$out[]=str_replace($quote, $quote . $quote, $valPart);
		}
		$str = $quote . implode($quote . $delim . $quote, $out) . $quote;
		
		return $str;
	}
  
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/sav_library/class.tx_savlibrary_defaultViewers.php']) {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/sav_library/class.tx_savlibrary_defaultViewers.php']);
}

?>
