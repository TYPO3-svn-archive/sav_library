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

/**
 * SAV Library: standard viewers
 *
 * @author	Laurent Foulloy <yolf.typo3@orange.fr>
 *
 */


class tx_savlibrary_defaultViewers {


  protected $fileHandle;    // File handle for export operation

  // Variables in calling classes
  protected $savlibrary;      // Reference to the savlibrary object
  protected $cObj;            // Reference to the cObj in the extension
  protected $extConfig;       // Reference to the extension configuration
  protected $extKey;          // Extension Key

  protected $xmlReferenceArray = array();
  protected $replaceDistinctArray = array();
  protected $previousMarkerArray = array();

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

    // If print icon is associated with a related view, calls it
    if (t3lib_div::_GET('print') && $this->extConfig['views'][$this->savlibrary->viewId][$this->savlibrary->folderTab]['relViewPrintIcon']) {
      return $this->printForm_defaultViewer(
        $dataset,
        $this->extConfig['views'][$this->extConfig['views'][$this->savlibrary->viewId][$this->savlibrary->folderTab]['relViewPrintIcon']],
        $errors
      );
    }

    $showAllTemplate = $this->extConfig['showAllTemplates'][$this->savlibrary->formConfig['showAll']];

		// Prepares the template
		$tmpl = '<!-- ###item### begin -->'.
				$showAllTemplate['itemTmpl'].'
			<!-- ###item### end -->';
			     
		// Processes the dataset
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

  			// Makes some processing to retrieve a simple item type
  			$item = array();

  			if ($x['REGIONS']['items']) {
    			foreach($x['REGIONS']['items'] as $k => $v) {

    			  // Clears the field value if the cutter is set
    			  if ($v['CUTTERS']['CUT_value']) {
              $v['MARKERS'][$v['MARKERS']['field']] = '';
            } elseif ($v['WRAPPERS']['wrapitem']) {
              // Checks if there is a wrapper
              $v['MARKERS'][$v['MARKERS']['field']] = $this->cObj->dataWrap(
                $v['MARKERS'][$v['MARKERS']['field']],
                $v['WRAPPERS']['wrapitem']
              );
            }

            // Checks if the classItem is set
            if ($v['MARKERS']['classItem']) {
              $item['classItem'] = $v['MARKERS']['classItem'];
            }
            
            // Checks if the field already exists in the MARKERS.
            if(!array_key_exists($v['MARKERS']['field'], $item)) {
    				  // Adds the field
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
  			
        // Processes labels associated with forms
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
		
  			// Adds the type and Value
  			$value = $this->savlibrary->_doTemplateItem($items, $tmpl);

        // Processes localization tags
        $value = $this->savlibrary->processLocalizationTags($value);
 			
        // Processes additionnal markers
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
  			$ta['REGIONS']['items'][$key]['MARKERS']['classItem'] = $items['MARKERS']['classItem'];

        // Check if the workspace is set
        $ta['REGIONS']['items'][$key]['MARKERS']['workspace'] = (
          $row['__workspace__'] ?
          $row['__workspace__'] :
          ''
        );

  			// Sets the icons
  			if ($this->savlibrary->userIsAdmin($row)) {
          if ($this->savlibrary->inputIsAllowedInForm()) {
            $content = '';
  				  // Adds the edit button if allowed
            if (!$this->savlibrary->conf['noEditButton']) {
             	$content .= $this->savlibrary->editButton(
                $this->savlibrary->formName,
                $row['uid']
              );
            }
  				  // Adds the delete button if allowed
            if ($this->savlibrary->userIsSuperAdmin() || (!$this->savlibrary->conf['noDeleteButton'] && !($this->savlibrary->conf['deleteButtonOnlyForCruser'] && $row['cruser_id']!=$GLOBALS['TSFE']->fe_user->user['uid']))) {
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
		    case 0: // Shows a message
          $ta['REGIONS']['items'][0]['TYPE'] = 'item';
  			  $ta['REGIONS']['items'][0]['MARKERS']['Value'] =
            $this->savlibrary->getLibraryLL('general.noAvailableInformation');
  			  $ta['REGIONS']['items'][0]['MARKERS']['itemIconLeft'] = '';
  			  $ta['REGIONS']['items'][0]['CUTTERS']['CUT_itemIconLeft'] = 1;
          break;
        case 1: // Does not show a message
  		    $ta['REGIONS']['items']='';     
  		    break;
  		  case 2: // Does not show the extension
		      $ta['TYPE'] = 'showAllHidden';
          return $ta;
  		    break;            
      }
    }

		// Shows the new button if newButton is allowed
		if ($this->savlibrary->inputIsAllowedInForm()) {
      if (!$this->savlibrary->userIsSuperAdmin() && $this->savlibrary->conf['noNewButton']) {
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
    
    // Adds the export icon
    if ($this->savlibrary->userIsAllowedToExportData()) {
      $ta['MARKERS']['titleIconRight'] .= $this->savlibrary->exportButton(
        $this->savlibrary->formName,
        0
      );
    }

    // Adds the print icon
    if ($this->savlibrary->inputIsAllowedInForm() || $this->savlibrary->userBelongsToAllowedGroup()) {
      if ($this->savlibrary->extObj->extConfig['views'][$this->savlibrary->viewId][$this->savlibrary->folderTab]['addPrintIcon']) {
        $ta['MARKERS']['titleIconRight'] .= $this->savlibrary->printButton(
          $this->savlibrary->formName,
          0
        );
      }  
    }        
    
    // Adds the help icon
    if ($this->savlibrary->conf['helpPage']) {
      $ta['MARKERS']['titleIconRight'] .= $this->savlibrary->helpButton(
        $this->savlibrary->formName,
        0
      );
    } 

    // Adds the toggleModeButton
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

		// Arrow selector
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

    // Sets the form parameters and call the browser
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
    if (t3lib_div::_GET('print') && $this->extConfig['views'][$this->savlibrary->viewId][$this->savlibrary->folderTab]['relViewPrintIcon']) {
      return $this->printForm_defaultViewer(
        $dataset,
        $this->extConfig['views'][$this->extConfig['views'][$this->savlibrary->viewId][$this->savlibrary->folderTab]['relViewPrintIcon']],
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

    // Class for the title bar in preview mode
    if ( $GLOBALS['TSFE']->sys_page->versioningPreview && $dataset[0][$this->savlibrary->tableLocal . '.pid'] == -1) {
      $ta['MARKERS']['workspace'] = 'draftWorkspace';
    } else {
      $ta['MARKERS']['workspace'] = '';
    }

    // Add the print icon
    $ta['MARKERS']['titleIconRight'] = '';
    if ($this->savlibrary->extObj->extConfig['views'][$this->savlibrary->viewId][$this->savlibrary->folderTab]['addPrintIcon']) {
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
         
    // Adds the close button
    $ta['MARKERS']['titleIconRight'] .= $this->savlibrary->closeButton(
      $this->savlibrary->formName,
      $dataset[0]['uid']
    );
    
    // Adds the edit button
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

    // Gets the data
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

    // Class for the title bar in preview mode
    if ( $GLOBALS['TSFE']->sys_page->versioningPreview && $dataset[0][$this->savlibrary->tableLocal . '.pid'] == -1) {
      $ta['MARKERS']['workspace'] = 'draftWorkspace';
    } else {
      $ta['MARKERS']['workspace'] = '';
    }
    
    // Add the help icon
    if ($this->savlibrary->conf['helpPage']) {
      $ta['MARKERS']['titleIconLeft'] .= $this->savlibrary->helpButton(
        $this->savlibrary->formName,
        0
      );
    }      
		
    // Add the print icon
    if ($this->extConfig['views'][$this->savlibrary->viewId][$this->savlibrary->folderTab]['addPrintIcon']) {
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
		$tmpl = '<!-- ###item### begin -->' .
				$showAllTemplate['itemTmpl'] . '
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
            $label = (
              $items['MARKERS'][$matches[1][$keyMatch] . '_Required'] ?
              '<span class="required">*</span>' :
              ''
            );
            $tmpl = str_replace(
              $matches[0][$keyMatch],
              '<div class="updateCol1">' . $matches[3][$keyMatch] . $label . '</div>' .
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
		$tmpl = '<!-- ###item### begin -->' .
				$showAllTemplate['itemTmpl'] . '
			<!-- ###item### end -->';
			     
		// Process the dataset
    if (is_array($dataset)) {
  		$ta['REGIONS']['items'] = '';
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
  			$ta['REGIONS']['items'][$key]['MARKERS']['classItem'] = '';
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
      'pid','crdate','tstamp','hidden','deleted','cruser_id','disable',
      'starttime','endtime','password','lockToDomain','is_online','lastlogin',
      'TSconfig',
    );
    if (!$extPOSTVars['xmlFile'][0]) {
      // Keep the uid field when there is a XML file
      $excludeFields = array_merge(array('uid'), $excludeFields);
    }

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
  				  /* WHERE   */	$table . '.uid=' . intval($extPOSTVars['configuration'][0])
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
  				/* WHERE   */	$table . '.uid=' . intval($extPOSTVars['configuration'][0]),
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
          foreach($extPOSTVars as $k => $v) {
            if (is_array($v[0])) {
              foreach($v[0] as $k1 => $v1) {
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
        $extPOSTVars['includeAllFields'][0] || $extPOSTVars['xmlFile'][0] ?
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

    // Display the field for the XML template
    $config = array(
      'size' => 80,
  		'elementControlName' => $this->savlibrary->formName . '[xmlFile][0]',
      'value' => $extPOSTVars['xmlFile'][0],
    );
    $cutters = array();
    $markers = array(
      'Label' => $this->savlibrary->getLibraryLL('itemviewer.xmlFile'),
      'Value' => $this->savlibrary->itemviewers->viewStringInputEditMode($config),
    );
    $ta['REGIONS']['items'][] = $this->buildItemArray($markers, $cutters);
    
    // Display the field for the XLST file
    $config = array(
      'size' => 80,
  		'elementControlName' => $this->savlibrary->formName . '[xsltFile][0]',
      'value' => $extPOSTVars['xsltFile'][0],
    );
    $cutters = array();
    $markers = array(
      'Label' => $this->savlibrary->getLibraryLL('itemviewer.xsltFile'),
      'Value' => $this->savlibrary->itemviewers->viewStringInputEditMode($config),
    );
    $ta['REGIONS']['items'][] = $this->buildItemArray($markers, $cutters);
    
    // Display the field for the exec command
    if ($this->savlibrary->conf['allowExec']) {
      $config = array(
        'size' => 80,
    		'elementControlName' => $this->savlibrary->formName . '[exec][0]',
        'value' => $extPOSTVars['exec'][0],
      );
      $cutters = array();
      $markers = array(
        'Label' => $this->savlibrary->getLibraryLL('itemviewer.exec'),
        'Value' => $this->savlibrary->itemviewers->viewStringInputEditMode($config),
      );
      $ta['REGIONS']['items'][] = $this->buildItemArray($markers, $cutters);
    }

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
      if (is_array($this->extConfig['views'][$this->savlibrary->viewId][$this->savlibrary->cryptTag('0')]['fields'])) {
        foreach ($this->extConfig['views'][$this->savlibrary->viewId][$this->savlibrary->cryptTag('0')]['fields'] as $keyField => $valueField) {
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
      foreach ($this->savlibrary->queriers->sqlFieldsExport as $key => $sqlField) {
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
    if ($exportOK && ((is_array($extPOSTVars['fields']) && in_array(1, $extPOSTVars['fields'][0])) || $extPOSTVars['xmlFile'][0]) && $nbRows > 0 && !isset($res['ERROR'])) {

      // Create the directory in typo3temp if it does not exist
      if (!is_dir('typo3temp/' . $this->extKey)) {
        mkdir('typo3temp/' . $this->extKey);
      }
      
      // Set the path for the files
		  $filePath = PATH_site . 'typo3temp/' . $this->extKey . '/';

      // Check if a XML file is set
      if ($extPOSTVars['xmlFile'][0]) {
        if (file_exists($extPOSTVars['xmlFile'][0])) {

          // Load and process the xml file
          $xml = @simplexml_load_file($extPOSTVars['xmlFile'][0]);
          if ($xml === false) {
            $this->savlibrary->addError(
              'error.incorrectXmlFile',
              $extPOSTVars['xmlFile'][0]
            );
            return $ta;
          }
          if (!$this->processXML($xml)) {
            return $ta;
          }

          // Set the parent field
          foreach ($this->xmlReferenceArray as $key => $value) {
            if(preg_match_all('/###(REF_[^#]+)###/', $value['template'], $matches)) {
              foreach ($matches[0] as $keyMatch => $valueMatch) {
                $this->xmlReferenceArray[$matches[1][$keyMatch]]['parent'] = $key;
              }
            }
          }

          // Clear all reference files
          foreach ($this->xmlReferenceArray as $keyReference => $valueReference) {
		        $fileName = $keyReference . '.xml';
		        if (file_exists($filePath . $fileName)) {
              $arrError['unlink'] = unlink($filePath . $fileName);
            }
          }
        } else {
          $this->savlibrary->addError(
            'error.fileDoesNotExist',
            $extPOSTVars['xmlFile'][0]
          );
          return $ta;
        }
      }

      // Set the ouput file
      $fileName = $this->savlibrary->formName . date('_Y_m_d_H_i') . '.csv';
      t3lib_div::unlink_tempfile($filePath . $fileName);
		  
		  if ($fileHandle = fopen($filePath . $fileName, 'ab')) {

		    // Export the field names if XML file is not set
        if ($extPOSTVars['exportFieldNames'][0] && !$extPOSTVars['xmlFile'][0]) {
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
            $arrError['fwrite'] = fwrite($fileHandle, $this->csvValues($arrValues, ';') . chr(10));
          }
        }

		    // Process the fields
        $cpt = 0;
    		while ($row = $this->savlibrary->queriers->sql_fetch_assoc_with_tablename($res, $cpt++)) {

          // Process the row
          $markerArray = $this->processRow($row, $extPOSTVars, $query, $aliasFields);
          
          // Check if a XML file is set
          if ($extPOSTVars['xmlFile'][0]) {
            if (!$this->processXmlReferenceArray($row, $markerArray)) {
              return $ta;
            }
          } else {
            // Write the content to the output file
            $arrError['fwrite'] = fwrite($fileHandle, $this->csvValues($markerArray, ';') . chr(10));
          }
          $previousRow = $row;
        }

        // Post process the XML file if any
        if ($extPOSTVars['xmlFile'][0]) {
            // process last markers
            if(!$this->postprocessXmlReferenceArray($previousRow, $markerArray)) {
              return $ta;
            }
        }

        // Check if a XLST file is set
        if ($extPOSTVars['xsltFile'][0]) {
          if (file_exists($extPOSTVars['xsltFile'][0])) {
          
            // Get the xml file name from the last item in the reference array
            end($this->xmlReferenceArray);
            $xmlfileName = key($this->xmlReferenceArray) . '.xml';

            // Load the XML source
            $xml = new DOMDocument;
            libxml_use_internal_errors(true);

            if (@$xml->load($filePath . $xmlfileName) === false) {
            
              $conf['parameter'] = 'typo3temp/' . $this->extKey . '/' . $xmlfileName;
              $conf['target'] = '_blank';
              $this->savlibrary->addError(
                'error.incorrectXmlProducedFile',
                  $this->cObj->typoLink($this->savlibrary->getLibraryLL('error.xmlErrorFile'), $conf)
              );

              // Get the errors
              $errors = libxml_get_errors();
              foreach ($errors as $error) {
                $addMessage = sprintf($GLOBALS['TSFE']->sL('LLL:EXT:sav_library/locallang.xml:error.xmlErrorMessage'), $error->message, $error->line);
                $this->savlibrary->addError(
                  'error.xmlError',
                  $addMessage
                );
              }
              

              libxml_clear_errors();
              return $ta;
            }

            $xsl = new DOMDocument;
            if (@$xsl->load($extPOSTVars['xsltFile'][0]) === false) {
              $this->savlibrary->addError(
                'error.incorrectXsltFile',
                $extPOSTVars['xsltFile'][0]
              );
              return $ta;
            }

            // Configure the transformer
            $proc = new XSLTProcessor;
            $proc->importStyleSheet($xsl); // attach the xsl rules

            // Write the result directly
            $arrError['close'] = fclose($fileHandle);
            $bytes = @$proc->transformToURI($xml, 'file://' . $filePath . $fileName);

            if ($bytes === false) {
              $this->savlibrary->addError(
                'error.incorrectXsltResult'
              );
              return $ta;
            }
            
            $arrError['unlink'] = unlink($filePath . $xmlfileName);

          } else {
            $this->savlibrary->addError(
              'error.fileDoesNotExist',
              $extPOSTVars['xsltFile'][0]
            );
            return $ta;
          }
        } elseif ($extPOSTVars['xmlFile'][0]) {
          // Get the xml file name from the last item in the reference array
          end($this->xmlReferenceArray);
          if (key($this->xmlReferenceArray)) {
            $arrError['close'] = fclose($fileHandle);
            $xmlfileName = key($this->xmlReferenceArray) . '.xml';
            $xmlfilePath = $filePath;
             // Copy and delete the temp file
            $arrError['copy'] = copy($xmlfilePath . $xmlfileName, $filePath . $fileName);
            $arrError['unlink'] = unlink($xmlfilePath . $xmlfileName);
          } else {
            $arrError['close'] = fclose($fileHandle);

            $xmlfileName = $extPOSTVars['xmlFile'][0];
            $xmlfilePath = PATH_site;
            // Copy the file
            $arrError['copy'] = copy($xmlfilePath . $xmlfileName, $filePath . $fileName);
          }
        }
        
        clearstatcache();
			  t3lib_div::fixPermissions($filePath . $fileName);

			  // Check if an Exec command exists, if allowec
			  if ($this->savlibrary->conf['allowExec']) {
          if ($extPOSTVars['exec'][0]) {
            // Replace some tags
            $cmd = str_replace('###FILE###',$filePath . $fileName, $extPOSTVars['exec'][0]);
            $cmd = str_replace('###SITEPATH###', dirname(PATH_thisScript), $cmd);


            // Process the command if not in safe mode
            if (!ini_get('safe_mode')) {
              $cmd = escapeshellcmd($cmd);
            }
            
            // Special processing for white spaces in windows directories
            $cmd = preg_replace('/\/(\w+\s\w+)\//', '/"$1"/', $cmd);

            // Exec the command
            exec ($cmd);


            return $ta;
          }
        }



		    if (!in_array(FALSE, $arrError)) {
          header('Content-Disposition: attachment; filename=' . $fileName);
				  header('Content-type: x-application/octet-stream');
				  header('Content-Transfer-Encoding: binary');
				  header('Content-length:' . filesize($filePath . $fileName));
				  readfile($filePath . $fileName);
        }
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
	 * Process a row
	 *
	 * @param	$row array		row of data
	 * @param $extPOSTVars  array POST array
	 * @param $query array  Query
	 * @param $aliasFields array Aliases
	 *
	 * @return	array field array
	 */
  function processRow(&$row, &$extPOSTVars, $query, $aliasFields) {

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
              continue;
            } elseif (!($resLocal = $GLOBALS['TYPO3_DB']->sql_query($queryReqValue))) {
              $this->savlibrary->addError(
                'error.incorrectQueryInReqValue',
                $config['field']
              );
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
              case 'group':
                if ($config['internal_type'] == 'file') {
                  $config['onlyfilename'] = 1;
                }
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
          
          // Convert
          $arrValues['###' . $field . '###'] = $value;
        } elseif (preg_match('/ (as|AS) ' . $field . '/', $extPOSTVars['additionalFields'][0])) {
          $arrValues['###' . $field . '###'] = stripslashes($row[$field]);
        }
      }
    }
    return $arrValues;
  }

   
 	/**
	 * Process the XML file
	 *
	 * @param	$row array		row of data
	 * @param $markerArray  array of markers
	 *
	 * @return	boolean		true if OK
	 */
	public function processXmlReferenceArray($row, $markerArray) {

    // Special processing
    foreach ($markerArray as $key => $value) {
      // Replace & by &amp;
      $markerArray[$key] = str_replace('& ', '&amp; ', $markerArray[$key]);

      // Suppress empty tags
      $markerArray[$key] = preg_replace('/<[^\/>][^>]*><\/[^>]+>/', '', $markerArray[$key]);
    }

    // Set the file Path
    $filePath = PATH_site . 'typo3temp/' . $this->extKey . '/';
    
    // Check if a replaceDistinct id has changed
    foreach ($this->xmlReferenceArray as $key => $value) {
      switch ($value['type']) {
        case 'replacedistinct':
          if ($row[$value['id']] != $value['fieldValue']) {
            if (!is_null($value['fieldValue'])) {
              // Set all the previous replaceDistinct ids to "changed"
              $this->recursiveChangeField($key, 'changed', true);
            }
            $this->xmlReferenceArray[$key]['fieldValue'] = $row[$value['id']];
          } 
          break;
      }
    }

    // Process the replaceDistinct and cutter parts
    foreach ($this->xmlReferenceArray as $key => $value) {
      switch ($value['type']) {
        case 'emptyifsameasprevious':
          // Parse the template with the known markers
          $buffer = utf8_decode($value['template']);
          $buffer = $this->cObj->substituteMarkerArrayCached(
            $buffer,
            $markerArray,
            array(),
            array()
          );
          // Keep the value in the XML reference array
          $this->xmlReferenceArray[$key]['fieldValue'] = $buffer;
          break;
        case 'replacedistinct':
          if ($value['changed']) {
            // Parse the template with the previous known markers
            $buffer = utf8_decode($value['template']);
            $buffer = $this->cObj->substituteMarkerArrayCached(
              $buffer,
              $this->previousMarkerArray,
              array(),
              array()
            );

            $fileName = $key . '.xml';

            if(!$this->replaceReferenceMarkers($filePath, $fileName, $buffer)) {
              return false;
            }
            
            $this->recursiveChangeField($key, 'changed', false);
            $this->unlinkReplaceAlways($filePath, $key);
          } 
          break;
        case 'cutifnull':
        case 'cutifempty':
        case 'cutifnotnull':
        case 'cutifnotempty':
        case 'cutifequal':
        case 'cutifnotequal':
        case 'cutifgreater':
        case 'cutifless':
        case 'cutifgreaterequal':
        case 'cutiflessequal':

          // Set the file name
          $fileName = $key . '.xml';

          // Set the field value
          $value['fieldValue'] = $row[$value['id']];
          
          // The processing of the cutters depends on their place with respect to the replaceAlways attribute
          $isChildOfReplaceAlways = $this->isChildOfReplaceAlways($key);
          if ($isChildOfReplaceAlways) {
            $value['changed'] = true;
            $fieldValue = $value['fieldValue'];
            $marker = $markerArray;
          } else {
            $fieldValue = $value['previousFieldValue'];
            $marker = $this->previousMarkerArray;
          }
          
          // Set the condition
          switch ($value['type']) {
            case 'cutifnull':
            case 'cutifempty':
              $condition = empty($fieldValue);
              break;
            case 'cutifnotnull':
            case 'cutifnotempty':
              $condition = !empty($fieldValue);
              break;
            case 'cutifequal':
              $condition = ($fieldValue == $value['value']);
              break;
            case 'cutifnotequal':
              $condition = ($fieldValue != $value['value']);
              break;
            case 'cutifgreater':
              $condition = ($fieldValue > $value['value']);
              break;
            case 'cutifless':
              $condition = ($fieldValue > $value['value']);
              break;
            case 'cutifgreaterequal':
              $condition = ($fieldValue >= $value['value']);
              break;
            case 'cutiflessequal':
              $condition = ($fieldValue <= $value['value']);
              break;
          }

          // Check if the field must be replaced
          if ($value['changed'] && !$condition) {

            // replace markers in the template
            $buffer = utf8_decode($value['template']);
            $buffer = $this->cObj->substituteMarkerArrayCached(
                $buffer,
                $marker,
                array(),
                array()
            );

            if(!$this->replaceReferenceMarkers($filePath, $fileName, $buffer)) {
              return false;
            }

            if (!$isChildOfReplaceAlways) {
              $this->recursiveChangeField($key, 'changed', false);
            }

          } else {
            // The field is cut
            $buffer = '';

            if(!$this->replaceReferenceMarkers($filePath, $fileName, $buffer)) {
              return false;
            }
          }

          // Update the previous fied value
          $this->xmlReferenceArray[$key]['previousFieldValue'] = $value['fieldValue'];

          break;
      }
    }
    
    // Process the replaceAlways part
    foreach ($this->xmlReferenceArray as $key => $value) {
      switch ($value['type']) {
        case 'replacealways':

		      $fileName = $key . '.xml';

          // replace markers in the template
          $buffer = utf8_decode($value['template']);
          $buffer = $this->cObj->substituteMarkerArrayCached(
            $buffer,
            $markerArray,
            array(),
            array()
          );

          if(!$this->replaceReferenceMarkers($filePath, $fileName, $buffer)) {
            return false;
          }
          break;
      }
    }

    // Keep the marker array
    $this->previousMarkerArray = $markerArray;

    return true;
  }

 	/**
	 * Process the last markers in the XML file
	 *
	 * @param	$row array		row of data
	 * @param $markerArray  array of markers
	 *
	 * @return	boolean		true if OK
	 */
	public function postprocessXmlReferenceArray($row, $markerArray) {

    // Mark all references as changed
    $replacedistinct = FALSE;
    foreach($this->xmlReferenceArray as $key => $value) {
      $this->xmlReferenceArray[$key]['changed'] = true;
      switch ($value['type']) {
        case 'replacedistinct':
          $replacedistinct = TRUE;
          break;
      }
    }
    
    // Process all the references one more time
    if ($replacedistinct) {
      if (!$this->processXmlReferenceArray($row, $markerArray)) {
        return false;
      }
    }

    // Set the file Path
    $filePath = PATH_site . 'typo3temp/' . $this->extKey . '/';

    // Convert to utf8 only for replaceLast
    $utf8Encode = false;
    $altPattern =  '';

    //Post-processing
    foreach($this->xmlReferenceArray as $key => $value) {
      switch ($value['type']) {
        case 'replacelast':
          $utf8Encode = true;
          $altPattern = '/(?s)(.*)(###)(REF_[^#]+)(###)(.*)/';
        case 'replacelastbutone':

          // Parse the template with the previous known markers
          $buffer = utf8_decode($value['template']);
          $buffer = $this->cObj->substituteMarkerArrayCached(
            $buffer,
            $this->previousMarkerArray,
            array(),
            array()
          );

          $fileName = $key . '.xml';

          if(!$this->replaceReferenceMarkers($filePath, $fileName, $buffer, $utf8Encode, $altPattern)) {
            return false;
          }
          break;
      }
    }

    return true;
  }
  
  
 	/**
	 * Change a giben field value for all the child of a node
	 *
	 * @param	$keySearch string key
	 * @param	$setField string field to change
	 * @param	$setvalue mixed value for the field
	 *
	 * @return	none
	 */
  public function recursiveChangeField($keySearch, $setField, $setValue) {
    $this->xmlReferenceArray[$keySearch][$setField] = $setValue;
    foreach ($this->xmlReferenceArray as $key => $value) {
      if($this->xmlReferenceArray[$key]['parent'] == $keySearch) {
        $this->recursiveChangeField($key, $setField, $setValue);
      }
    }
  }
  
 	/**
	 * Unlink the file associated with a replaceAlways item
	 *
	 * @param	$filePath string	file path
	 * @param	$keySearch string key
	 *
	 * @return	none
	 */
   public function unlinkReplaceAlways($filePath, $keySearch) {
    foreach ($this->xmlReferenceArray as $key => $value) {
      if ($this->xmlReferenceArray[$key]['parent'] == $keySearch) {
        if ($this->xmlReferenceArray[$key]['type'] != 'replacealways') {
          $this->unlinkReplaceAlways($filePath, $key);
        } elseif (file_exists($filePath . $key . '.xml')) {
          unlink($filePath . $key . '.xml');
        }
      }
    }
  }

 	/**
	 * Check if the key is a child of a replaceAlways item
	 *
	 * @param	$keySearch string key
	 *
	 * @return	boolean		true if OK
	 */
  public function isChildOfReplaceAlways($keySearch) {
    $parent = $this->xmlReferenceArray[$keySearch]['parent'];
    while ($parent != NULL) {
      if($this->xmlReferenceArray[$parent]['type'] == 'replacealways') {
        return true;
      } else {
        $parent = $this->xmlReferenceArray[$parent]['parent'];
      }
    }
    return false;
  }

 	/**
	 * Replace the reference markers
	 *
	 * @param	$filePath string	file path
	 * @param $fileName string file name
	 * @param $template string template containing the markers
	 * @param $mode string mode for the file writing
	 *
	 * @return	boolean		true if OK
	 */
  public function replaceReferenceMarkers($filePath, $fileName, $template, $utf8Encode = false, $altPattern = '') {

    $pattern = '/(?s)(.*?)(<[^>]+>)###(REF_[^#]+)###(<\/[^>]+>)((?:.(?!<[^>]+>###REF_))*)/';
    $pattern = ($altPattern ? $altPattern : $pattern);
    if (preg_match_all($pattern, $template, $matches)) {

      foreach($matches[0] as $keyMatch => $valueMatch) {

  		  if ($fileHandle = fopen($filePath . $fileName, 'a')) {

          // replace markers in the template
          $buffer = $matches[1][$keyMatch];
          $buffer = ($utf8Encode ? utf8_encode($buffer): $buffer);
          $buffer = $this->savlibrary->processConstantTags($buffer);
          $buffer = $this->savlibrary->processLocalizationTags($buffer);
          

          fwrite($fileHandle, $buffer);
          $fileNameRef = $matches[3][$keyMatch] . '.xml';
          if (file_exists($filePath . $fileNameRef)) {
            if ($fileHandleRef = fopen($filePath . $fileNameRef,'r')) {
              while($buffer = fread($fileHandleRef, 2048)) {
                $buffer = ($utf8Encode ? utf8_encode($buffer): $buffer);
                fwrite($fileHandle, $buffer);
              }
              fclose($fileHandleRef);
              unlink($filePath . $fileNameRef);
            } else {
              $this->savlibrary->addError(
                'error.fileOpenError',
                $fileName
              );
              return false;
            }
          } else {
            // Error, the file does not exist
            $this->savlibrary->addError(
              'error.fileDoesNotExist',
              $fileNameRef
            );
            return false;
          }
          $buffer = $matches[5][$keyMatch];
          $buffer = ($utf8Encode ? utf8_encode($buffer): $buffer);
          $buffer = $this->savlibrary->processConstantTags($buffer);
          $buffer = $this->savlibrary->processLocalizationTags($buffer);

          fwrite($fileHandle, $buffer);
          fclose($fileHandle);
        } else {
          // Error, the file cannot be opened
          $this->savlibrary->addError(
            'error.fileOpenError',
            $fileName
          );
          return false;
        }
      }
    } else {
      // No REF_ marker, just create the reference file with the template
  		if ($fileHandle = fopen($filePath . $fileName, 'a')) {

        // Replace the localization markers
        $buffer = $template;

        // Check if there exists SPECIAL_REF markers
        if (preg_match_all('/(<[^>]+>)###SPECIAL_(REF_[^#]+)###(<\/[^>]+>)/', $buffer, $matches)) {
          foreach($matches[0] as $keyMatch => $match) {
            if ($this->xmlReferenceArray[$matches[2][$keyMatch]]['fieldValue'] != $this->xmlReferenceArray[$matches[2][$keyMatch]]['previousFieldValue']) {
              if (is_null($this->xmlReferenceArray[$matches[2][$keyMatch]]['previousFieldValue'])) {
                $buffer = str_replace($match, $this->xmlReferenceArray[$matches[2][$keyMatch]]['fieldValue'], $buffer);
                $this->xmlReferenceArray[$matches[2][$keyMatch]]['previousFieldValue']  = $this->xmlReferenceArray[$matches[2][$keyMatch]]['fieldValue'];
              } else {
                $buffer = preg_replace('/(<[^>]+>)###SPECIAL_(REF_[^#]+)###(<\/[^>]+>)/', '$1$3', $buffer);
                $this->xmlReferenceArray[$matches[2][$keyMatch]]['previousFieldValue']  = NULL;
              }
            } else {
              $buffer = preg_replace('/(<[^>]+>)###SPECIAL_(REF_[^#]+)###(<\/[^>]+>)/', '$1$3', $buffer);
            }
          }
        }

        $buffer = ($utf8Encode ? utf8_encode($buffer): $buffer);
        $buffer = $this->savlibrary->processConstantTags($buffer);
        $buffer = $this->savlibrary->processLocalizationTags($buffer);
        

        fwrite($fileHandle, $buffer);
        fclose($fileHandle);
      } else {
        // Error, the file cannot be opened
        $this->savlibrary->addError(
          'error.fileOpenError',
          $fileName
        );
        return false;
      }
    }
    return true;
  }

   
	/**
	 * Process the XML tree
	 *
	 * @param	$element object		XML element object
	 *
	 * @return	array		Merged arrays
	 */
  function processXml($element) {

    // Process recursively all nodes
    foreach ($element->children() as $child) {
      if(!$this->processXml($child)) {
        return false;
      }
    }

    $attributes = $element->attributes();
    if ((string) $attributes['sav_type']) {
      $reference = 'REF_' . (int)$this->referenceCounter++;

      $this->xmlReferenceArray[$reference]['type'] = strtolower((string) $attributes['sav_type']);
      $this->xmlReferenceArray[$reference]['id'] = (string) $attributes['sav_id'];
      $this->xmlReferenceArray[$reference]['value'] = (string) $attributes['sav_value'];
      $this->xmlReferenceArray[$reference]['changed'] = false;
      $this->xmlReferenceArray[$reference]['fieldValue'] = NULL;
      $this->xmlReferenceArray[$reference]['previousFieldValue'] = NULL;
      $this->xmlReferenceArray[$reference]['parent'] = NULL;

      // Check if a reference id has to be set
      switch ($this->xmlReferenceArray[$reference]['type']) {
        case 'replacedistinct':
        case 'cutifnull':
        case 'cutifempty':
        case 'cutifnotnull':
        case 'cutifnotempty':
        case 'cutifequal':
        case 'cutifnotequal':
        case 'cutifgreater':
        case 'cutifless':
        case 'cutifgreaterequal':
        case 'cutiflessequal':
          if (!$this->xmlReferenceArray[$reference]['id']) {
            $this->savlibrary->addError(
              'error.xmlIdMissing',
              $this->xmlReferenceArray[$reference]['type']
            );
            return false;
          }
          break;
      }
      
      // Remove the repeat attributes
      unset($element[0]['sav_type']);
      unset($element[0]['sav_id']);
      unset($element[0]['sav_value']);

      // Set the template
      $template = $element->asXML();

      // Check if there is an xml header in the template
      if(preg_match('/^<\?xml[^>]+>/', $template, $match)) {

        // Remove the header
        $template = str_replace($match[0], '', $template);
        $this->xmlReferenceArray[$reference]['template'] = $template;
        if (!$this->xmlReferenceArray[$reference]['type']) {
          $this->xmlReferenceArray[$reference]['type'] = 'replacelastbutone';
        }

        // Set the template with relaceLast type
        $lastReference = 'REF_' . (int)$this->referenceCounter++;
        $this->xmlReferenceArray[$lastReference]['template'] = $match[0] . '###' . $reference . '###';
        $this->xmlReferenceArray[$lastReference]['type'] = 'replacelast';
      } else {
        $this->xmlReferenceArray[$reference]['template'] = $template;
      }

      // Delete all the children
      foreach ($element->children() as $child) {
        unset($element->$child);
      }

      // Replace the node by the reference or a special reference
      switch ($this->xmlReferenceArray[$reference]['type']) {
        case 'emptyifsameasprevious':
          $element[0] = '###SPECIAL_' . $reference . '###';
          break;
        default:
          $element[0] = '###' . $reference . '###';
          break;
      }

    } else {

      $template = $element->asXML();
      // Check if there is an xml header in the template
      if(preg_match('/^<\?xml[^>]+>/', $template, $match)) {
        $reference = 'REF_' . (int)$this->referenceCounter++;

        // Remove the header
        $template = str_replace($match[0], '', $template);
        $this->xmlReferenceArray[$reference]['template'] = $template;
        if (!$this->xmlReferenceArray[$reference]['type']) {
          $this->xmlReferenceArray[$reference]['type'] = 'replacelastbutone';
        }

        // Set the template with replaceLast type
        $lastReference = 'REF_' . (int)$this->referenceCounter++;
        $this->xmlReferenceArray[$lastReference]['template'] = $match[0] . '###' . $reference . '###';
        $this->xmlReferenceArray[$lastReference]['type'] = 'replacelast';
        // Delete all the children
        foreach ($element->children() as $child) {
          unset($element->$child);
        }
        // Replace the node by the reference
        $element[0] = '###' . $reference . '###';
      }
    }
    return true;
  }


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
