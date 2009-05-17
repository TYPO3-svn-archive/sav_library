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



class ux_tslib_cObj extends tslib_cObj {


 	/**
	 * Set to true by doConvertToUserIntObject() if USER object wants to become USER_INT
	 */
	protected $doConvertToUserIntObject = false;

	/**
	 * Indicates current object type. Can hold one of OBJECTTYPE_ constants or false.
	 * The value is set and reset inside USER() function. Any time outside of
	 * USER() it is false.
	 */
	protected $userObjectType = false;

	/**
	 * Indicates that object type is USER.
	 *
	 * @see tslib_cObjh::$userObjectType
	 */
	const OBJECTTYPE_USER_INT = 1;

	/**
	 * Indicates that object type is USER.
	 *
	 * @see tslib_cObjh::$userObjectType
	 */
	const OBJECTTYPE_USER = 2;

	/**
 	 * Class constructor.
 	 * Well, it has to be called manually since it is not a real constructor function.
 	 * So after making an instance of the class, call this function and pass to it a database record and the tablename from where the record is from. That will then become the "current" record loaded into memory and accessed by the .fields property found in eg. stdWrap.
 	 * @param	array		Array of TypoScript properties
 	 * @param	string		If "INT" then the cObject is a "USER_INT" (non-cached), otherwise just "USER" (cached)
 	 * @return	string		Output
	 * @link	http://typo3.org/documentation/document-library/references/doc_core_tsref/4.1.0/view/8/22/
 	 */

	function USER($conf, $ext = '') {
		$content = '';
		switch ($ext) {
 			case 'INT':
				$this->userObjectType = self::OBJECTTYPE_USER_INT;
				$substKey = $ext . '_SCRIPT.' . $GLOBALS['TSFE']->uniqueHash();
				$content.='<!--' . $substKey . '-->';
				$GLOBALS['TSFE']->config[$ext . 'incScript'][$substKey] = array(
 					'file' => $conf['includeLibs'],
 					'conf' => $conf,
 					'cObj' => serialize($this),
 					'type' => 'FUNC'
 				);
				break;
 			default:
				if ($this->userObjectType === false) {
					// Come here only if we are not called from $TSFE->INTincScript_process()!
					$this->userObjectType = self::OBJECTTYPE_USER;
				}
				$tempContent = $this->callUserFunction($conf['userFunc'], $conf, '');
				if ($this->doConvertToUserIntObject) {
					$this->doConvertToUserIntObject = false;
					$content = $this->USER($conf, 'INT');
				} else {
					$content .= $tempContent;
				}
				break;
 		}
		$this->userObjectType = false;
 		return $content;
 	}

 	/**
	 * Retrieves a type of object called as USER or USER_INT. Object can detect their
	 * type by using this call. It returns OBJECTTYPE_USER_INT or OBJECTTYPE_USER depending on the
	 * current object execution. In all other cases it will return false to indicate
	 * a call out of context.
	 *
	 * @return	mixed	One of OBJECTTYPE_ class constants or false
	 */
	public function getUserObjectType() {
		return $this->userObjectType;
	}

	/**
	 * Requests the current USER object to be converted to USER_INT.
	 *
	 * @return	void
	 */
	public function convertToUserIntObject() {
		if ($this->userObjectType !== self::OBJECTTYPE_USER) {
			$GLOBALS['TT']->setTSlogMessage('tslib_cObj::convertToUserIntObject() ' .
				'is called in the wrong context or for the wrong object type', 2);
		}
		else {
			$this->doConvertToUserIntObject = true;
		}
	}

}

?>
