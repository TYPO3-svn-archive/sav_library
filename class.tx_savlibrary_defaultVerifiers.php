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
 * SAV Library: standard verifiers
 *
 * @author	Yolf <yolf.typo3@orange.fr>
 *
 */

class tx_savlibrary_defaultVerifiers {

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
	 * Check if the input is a valid pattern.
	 *
	 * @param  $value       Value to process
	 * @param  $param       Parameter (pattern) 
   *       
	 * @return error message
	 */
	public function isValidPattern($value, $param = '', $uid = 0) {
    if (!preg_match($param, $value)) {
      return ('error.isValidPattern');
    } else {
      return '';
    }
  }
  
	/**
	 * Check if the input is lower or equal to a given length.
	 *
	 * @param  $value       Value to process
	 * @param  $param       Parameter (Length)
	 *
	 * @return error message
	 */
  public function isValidLength($value, $param = '', $uid = 0) {
    if (strlen($value) > $param) {
      return ('error.isValidLength');
    } else {
      return '';
    }
  }

	/**
	 * Check if the input is in a given interval.
	 *
	 * @param  $value       Value to process
	 * @param  $param       Parameter (Interval [a,b])
	 *
	 * @return error message
	 */
  public function isValidInterval($value, $param = '', $uid = 0) {
    if (!preg_match('/\[([\d]+),[ ]*([\d]+)\]/', $param, $matches)) {
      return ('error.verifierInvalidIntervalParameter');    
    }
    
    if ((int)$value < (int)$matches[1] || (int)$value > (int)$matches[2]) {
      return ('error.isValidInterval');
    } else {
      return '';
    }
  }

	/**
	 * Check if the input is in a given interval.
	 *
	 * @param  $value       Value to process
	 * @param  $param       Parameter (Interval [a,b])
	 *
	 * @return error message
	 */
  public function isValidQuery($value, $param = '', $uid = 0) {
  
    // get the field from a query. The value marker is replaced by the selected value
    $query = str_replace('###value###', $value, $param);
    $query = str_replace('###uid###', $uid, $query);

    // Check if the query is a SELECT query and for errors
    if (!$this->savlibrary->isSelectQuery($query)) {
      return 'error.onlySelectQueryAllowed';
    } elseif (!($res = $GLOBALS['TYPO3_DB']->sql_query($query))) {
      return 'error.incorrectQueryInContent';
    } else {
      $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
      if (!current($row)) {
        return ('error.isValidQuery');
      } else {
        return '';
      }
    }
  }
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/sav_library/class.tx_savlibrary_defaultVerifiers.php']) {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/sav_library/class.tx_savlibrary_defaultVerifiers.php']);
}

?>
