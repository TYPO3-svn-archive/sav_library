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

require_once(PATH_tslib . 'class.tslib_fe.php');
require_once(PATH_tslib . 'class.tslib_content.php');
require_once(PATH_t3lib. 'class.t3lib_timetrack.php');

class tx_phpunit_frontend extends tx_phpunit_database_testcase {

    // Init the test database
  protected function initDatabase() {
    $this->createDatabase();
 		$this->useTestDatabase();
  }
  
  // Basic authentication
  protected function userAuth($name, $password) {

    t3lib_div::_GETset(
      array(
        'logintype' => 'login',
        'user' => $name,
        'pass' => $password,
      )
    );

    // Reproduce initFEuser() which cannot be directly use because it
    // generates an error "Cannot modify header information" due to setcookie or
    // session_start. I have not been able to fix this problem.
    $GLOBALS['TSFE']->getMethodEnabled = true;
    $GLOBALS['TSFE']->fe_user = t3lib_div::makeInstance('tslib_feUserAuth');
		$GLOBALS['TSFE']->fe_user->lockIP = $this->TYPO3_CONF_VARS['FE']['lockIP'];
		$GLOBALS['TSFE']->fe_user->lockHashKeyWords = $this->TYPO3_CONF_VARS['FE']['lockHashKeyWords'];
		$GLOBALS['TSFE']->fe_user->checkPid = $this->TYPO3_CONF_VARS['FE']['checkFeUserPid'];
		$GLOBALS['TSFE']->fe_user->lifetime = intval($this->TYPO3_CONF_VARS['FE']['lifetime']);
		$GLOBALS['TSFE']->fe_user->checkPid_value = $GLOBALS['TYPO3_DB']->cleanIntList(t3lib_div::_GP('pid'));	// List of pid's acceptable
    $result = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*', 'fe_users', 'username=\'' . $name . '\' AND password=\''. $password . '\'');
    $GLOBALS['TSFE']->fe_user->user = $result[0];
    $GLOBALS['TSFE']->fe_user->loginType = 'FE';
    $GLOBALS['TSFE']->initUserGroups();

  }

  // Basic FE environment
  protected function initFE() {

    chdir(PATH_site);
    $temp_TSFEclassName = t3lib_div::makeInstanceClassName('tslib_fe');
    $GLOBALS['TSFE'] = new $temp_TSFEclassName(
   		$GLOBALS['TYPO3_CONF_VARS'],
  		t3lib_div::_GP('id'),
  		t3lib_div::_GP('type'),
  		t3lib_div::_GP('no_cache'),
  		t3lib_div::_GP('cHash'),
  		t3lib_div::_GP('jumpurl'),
  		t3lib_div::_GP('MP'),
  		t3lib_div::_GP('RDCT')
  	);
  	$GLOBALS['TSFE']->newCObj();
  	$GLOBALS['TSFE']->initTemplate();
	  $GLOBALS['TSFE']->tmpl->init();
	  }
}

?>
