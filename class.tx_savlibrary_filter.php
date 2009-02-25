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
 * SAV Library Filter: Common code for filters to be used in SAV extensions
 *
 * @author	Yolf <yolf.typo3@orange.fr>
 *
 *
 *
 */   

require_once(PATH_tslib.'class.tslib_pibase.php');

abstract class tx_savlibrary_filter extends tslib_pibase {

  abstract protected function SetSessionField_addWhere();

  // Variables for HTML outputs    	
  protected $EOL = '';                                 // End of line for HTML output
  protected $TAB = '';                                 // Tabulation
  protected $SPACE = '';                               // Space before element
  protected $WRAP = '';                                // String before wrapping
  
  // General variables for all sav_filter extension  
  protected $flexConf = array();                       // Configuration from the flexform
  protected $setterList = array();                     // Additional setter list 
  protected $extKeyWithId;                             // The extension key with content Id 
  protected $errors;                                   // Errors list
  protected $messages;                                 // Messages list
  protected $piVarsReloaded = false;                   // True if piVars are reloaded from the session 
  protected $debugQuery = false;                       // Debug the query if set to true. FOR DEVELLOPMENT ONLY !!!
  protected $forceSetSessionFields = false;            // Force the execution of setSessionFields
  protected $setFilterSelected = true;                 // If false the filter is not selected

  // Session variables
  protected $sessionFilter = array();                  // Filters data
  protected $sessionFilterSelected ='';                // Selected filter key
  protected $sessionAuth = array();                    // Authentications data
  
  /**
   * Constructor
   * 
   * @return void
   */              
  public function __construct() {
    parent::__construct();
    $this->EOL = chr(10); 
    $this->TAB = chr(9); 
    $this->SPACE = '    ';
    $this->WRAP = $this->EOL . $this->TAB . $this->TAB;
  }

	/**
	 * Init the filter
	 *
   * @return  boolean (false if a problem occured)
	 */
  protected function init() {

    // Check if a global maintenance is requested. In this case do not display the filtter.
    $temp = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['sav_library']);
    $maintenanceAllowedUsers = explode(',', $temp['maintenanceAllowedUsers']);
    if ($temp['maintenance']) {
      if (!in_array($GLOBALS['TSFE']->fe_user->user['uid'], $maintenanceAllowedUsers)) {
        return false;         
      }
    }

    // Get the session variables
    $this->sessionFilter = $GLOBALS['TSFE']->fe_user->getKey('ses','filter');
    $this->sessionFilterSelected = $GLOBALS['TSFE']->fe_user->getKey('ses','filterSelected');
    $this->sessionAuth = $GLOBALS['TSFE']->fe_user->getKey('ses','auth');

    // Set debug
    if ($this->debugQuery) {
      $GLOBALS['TYPO3_DB']->debugOutput=true;
    }
      
    // Set the pageID
    $this->extKeyWithId = $this->extKey . '_' . $this->cObj->data['uid'];
    if ($this->sessionFilter[$this->extKeyWithId]['pageID'] != $GLOBALS['TSFE']->id && $this->sessionFilterSelected == $this->extKeyWithId) {
      unset($this->sessionFilterSelected);
    }
    $this->sessionFilter[$this->extKeyWithId]['pageID'] = $GLOBALS['TSFE']->id;
    $this->sessionFilter[$this->extKeyWithId]['contentID'] = $this->cObj->data['uid'];
    $this->sessionFilter[$this->extKeyWithId]['tstamp'] = time();    

    // Recover the piVars in the session
    if (!count($this->piVars) && t3lib_div::_GP('sav_library') && isset($this->sessionFilter[$this->extKeyWithId]['piVars'])) {    
      $this->piVars = $this->sessionFilter[$this->extKeyWithId]['piVars'];   
		  $this->sessionFilterSelected = $this->extKeyWithId;  
      $this->piVarsReloaded = true;  
    } elseif ($this->piVars['logout']) {
      unset($this->sessionFilter[$this->extKeyWithId]['piVars']);
      unset($this->sessionAuth[$this->extKeyWithId]);
    } elseif ($this->piVars['logoutReloadPage']) {
      unset($this->sessionFilter[$this->extKeyWithId]);
      unset($this->sessionAuth[$this->extKeyWithId]);
  		header('Location: ' . t3lib_div::locationHeaderUrl($this->pi_getPageLink($GLOBALS['TSFE']->id)));           
    } elseif ($this->sessionAuth[$this->extKeyWithId]['authentified']) {
      if ($this->sessionFilter[$this->extKeyWithId]['piVars']) {
        $this->piVars = $this->sessionFilter[$this->extKeyWithId]['piVars'];
      }   
		  $this->sessionFilterSelected = $this->extKeyWithId;   
    } 
 
    // Init FlexForm configuration for plugin and get the configuration fields
    $this->pi_initPIflexForm(); 
    if (!isset($this->cObj->data['pi_flexform']['data'])) {
      $this->addError('error.incorrectPluginConfiguration_1', $this->extKey);
      $this->addError('error.incorrectPluginConfiguration_2');
      return $this->pi_wrapInBaseClass($this->showErrors());
    }
    foreach ($this->cObj->data['pi_flexform']['data']['sDEF']['lDEF'] as $key => $value) {
  		$this->flexConf[$key] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], $key);
    }
    
    // Merge the flexform configuration with the plugin configuration
    $this->conf = array_merge($this->conf, $this->flexConf);   
    
    // Include the default style sheet if none was provided
		if (!isset($GLOBALS['TSFE']->additionalHeaderData[$this->extKey])) {
		  if (!$this->conf['fileCSS']) {
		    if (file_exists(t3lib_extMgm::siteRelPath($this->extKey) . 'res/' . $this->extKey . '.css')) {
          $css = '<link rel="stylesheet" type="text/css" href="' . t3lib_extMgm::siteRelPath($this->extKey) . 'res/' . $this->extKey . '.css" />';
        }
      } elseif (file_exists($this->conf['fileCSS'])) {
        $css = '<link rel="stylesheet" type="text/css" href="' . $this->conf['fileCSS'] . '" />';
		  } else {
        $this->addError('error.incorrectCSS');
        return false;
      }

      $GLOBALS['TSFE']->additionalHeaderData[$this->extKey] = $this->TAB . $css;
		}    
    
    return true;		
  }
  
  /**
   * Call the various setters for the session     
   * 
   *@return void
   */
  protected function SetSessionFields() {

    if ((count($this->piVars) && !$this->piVarsReloaded) || $this->forceSetSessionFields) {
      $this->SetSessionField_tablename();
      $this->SetSessionField_fieldName();
      $this->SetSessionField_addWhere();
      $this->SetSessionField_search();
      $this->SetSessionField_searchOrder();                  
      $this->SetSessionField_pidList();
      $this->SetSessionField_enableFields();
      
      // Set the filterSelected with the current extension
      if ($this->setFilterSelected) {
        $this->sessionFilterSelected = $this->extKeyWithId; 
      } 
      
      // Add the piVars
      $this->sessionFilter[$this->extKeyWithId]['piVars'] = $this->piVars; 

      // Add setter      
      foreach($this->setterList as $setter) {
        if (method_exists($this, $setter)) {
          $this->$setter();
        }
      }  
    }

    // Set session data
    $GLOBALS['TSFE']->fe_user->setKey('ses','filter',$this->sessionFilter);
    $GLOBALS['TSFE']->fe_user->setKey('ses','filterSelected',$this->sessionFilterSelected);
    $GLOBALS['TSFE']->fe_user->setKey('ses','auth',$this->sessionAuth);
    $GLOBALS['TSFE']->storeSessionData();       
  }

  /**
   * Default setters
   * 
   */
   
  /**
   * Setter for tableName    
   * 
   * @return void
   */
  protected function SetSessionField_tableName() {  
    $this->sessionFilter[$this->extKeyWithId]['tableName'] = '';    
  } 

  /**
   * Setter for fieldName    
   * 
   * @return void
   */
  protected function SetSessionField_fieldName() {
    $this->sessionFilter[$this->extKeyWithId]['fieldName'] = '';    
  } 
   
   
  /**
   * Setter for search    
   * 
   * @return void
   */
  protected function SetSessionField_search() {
    $this->sessionFilter[$this->extKeyWithId]['search'] = false;          
  }

  /**
   * Setter for order    
   * 
   * @return void
   */
  protected function SetSessionField_searchOrder() {
    $this->sessionFilter[$this->extKeyWithId]['searchOrder'] = '';        
  }

  /**
   * Setter for pidList    
   * 
   * @return void
   */
  protected function SetSessionField_pidList() {
    $this->sessionFilter[$this->extKeyWithId]['pidList'] = ($this->conf['pidList'] ? ' AND pid IN (' . $this->conf['pidList'] . ')' : '');
  } 

  /**
   * Setter for enableFields    
   * 
   * @return void
   */
  protected function SetSessionField_enableFields() {
    $this->sessionFilter[$this->extKeyWithId]['enableFields'] = '';
    $tables = explode(',', $this->conf['tableName']);
    foreach ($tables as $table) {
      if(isset($GLOBALS['TCA'][$table])) {
        $this->sessionFilter[$this->extKeyWithId]['enableFields'] .= $this->cObj->enableFields($table);
      }     
    }
  } 
           

	/**
	 * Wrap views to get a form with name 
	 *
	 * @content string (content of the form)
	 * @hidden string (hidden fields for the form)
	 * @name string (name of the form)
	 *
	 * @return string (the whole content result)
	 */
	protected function wrapForm($content, $hidden='', $name='', $url='#') {
	
    $htmlArray = array();

    if ($this->errors) {
      $htmlArray[] = $this->showErrors();
    }
    
    if ($this->messages) {
      $htmlArray[] = $this->showMessages();
    }
    
    if (!$this->conf['noForm']) {   
      $htmlArray[] = '<form method="post" name="' . $name . '" enctype="multipart/form-data" action="' . $url . '" title="' . $GLOBALS['TSFE']->sL('LLL:EXT:' . $this->extKey . '/locallang.xml:pi1_plus_wiz_description') . '">';
    }
    $htmlArray[] = '  <div class="container-' . str_replace('_', '', $this->extKey) . '">';
    if ($hidden) {
      $htmlArray[] = array_merge($htmlArray, explode($this->EOL, '    ' . implode($this->EOL . '    ', explode($this->EOL, $hidden))));
    }
    $htmlArray = array_merge($htmlArray, explode($this->EOL, '    ' . implode($this->EOL . '    ', explode($this->EOL, $content))));
    $htmlArray[] = '  </div>';
    if (!$this->conf['noForm']) {   
      $htmlArray[] = '</form>';
    }  

    return implode($this->WRAP, $htmlArray);   
	}  

	/**
	 * Add a class to a link 
	 * 
	 * @param string (string containin the <a> tag)
	 * @param string (string containin the class)
	 * 
	 * @return string (string with the class added)
	 */  
  protected function add_class($x, $class) {
    return preg_replace('/^<a/', '<a class="' . $class . '"', $x);
  }
	
	/**
	 * Add an error to the error list
	 *
	 * @errorLabel string (error label)
	 * @addMessage string (additional message)
	 *
	 * @return void 
	 */
	protected function addError($errorLabel, $addMessage='') {
    $this->errors[] = $this->pi_getLL($errorLabel) . $addMessage;
  }
  
	/**
	 * Return the error list
	 *	 
	 * @return string (the error content result) 
	 */
	protected function showErrors() {
		if(!$this->errors) {
			return '';
		} else {
			$errorList = '';
			foreach($this->errors as $error) {
        $errorList .= '<li class="error">' . $error . '</li>';
      }
			return  '<ul>' . $errorList . '</ul>';
		}
	}	

	/**
	 * Add a message to the messagess array
	 *
	 * @param $messageLabel string (message label)
	 * @param $addMessage string (additionalmessage)
	 *
	 * @return  (none)
	 */
  protected function addMessage($messageLabel, $addMessage='', $class='') {
    $message['text'] = $this->pi_getLL($messageLabel) . $addMessage;
    $message['class'] = $class;
    $this->messages[] = $message;
  }
	 
	/**
	 * Return the message list
	 *	 *
	 * @return string (the messgae content result) 
	 */
	protected function showMessages() {
		if(!$this->messages) {
			return '';
		} else {
  		$messageList = '';
  		foreach($this->messages as $message) {
        $messageList .= '<li class="' . ($message['class'] ? $message['class'] : 'message') . '">' . $message['text'] . '</li>';
      }
      return '<ul>' . $messageList . '</ul>';
    }
  }

	/**
	 * Transform an array of HTML code into HTML code
	 *
	 * @return  string
	 */
  protected function arrayToHTML($htmlArray, $space='') {
  
		return  implode($this->EOL . $space, $htmlArray);
  }  	
	
}

?>
