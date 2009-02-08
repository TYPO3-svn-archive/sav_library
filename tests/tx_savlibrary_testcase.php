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

require_once(dirname(__FILE__) . '/phpunit/class.tx_phpunit_frontend.php');

require_once(dirname(__FILE__) . '/../class.tx_savlibrary.php');
require_once(PATH_tslib . 'class.tslib_fe.php');
require_once(PATH_tslib . 'class.tslib_content.php');



class tx_savlibrary_testcase extends tx_phpunit_frontend {
  public $fixture;

  public function setUp() {
		// Init database
		$this->initDatabase();

    // Import data in the database
    // The extension sav_library_example1 must be installed for this test case
		$this->importExtensions(array('cms','sav_library_example1'));
		$this->importDataSet(dirname(__FILE__). '/tx_savlibrary_testcase_dataset.xml');

		$this->initFE();

    // Create the sav_library object
    $this->fixture = new tx_savlibrary();
    $this->fixture->extObj = $this->initExt();
    $this->fixture->xmlToSavlibrayConfig($this->fixture->extObj->cObj->fileResource('EXT:sav_library/res/sav_library.xml'));
    $this->fixture->queriers = t3lib_div::makeInstance('tx_savlibrary_defaultQueriers');
    $this->fixture->queriers->initVars($this->fixture);
    
    // Use the extension sav_library_example1 for this test case
    $this->loadExt('sav_library_example1');

  }

	public function tearDown() {

		// insures that test database always is dropped
		// even when testcases fails
		$this->dropDatabase();
	}

   /***************************************************************/
   /* Form methods                                                */
   /***************************************************************/
  public function test_getFunc() {

    // Check String Input
    $config = array(
      'type' => 'input',
    );
    $this->assertEquals('viewStringInput', $this->fixture->getFunc($config));

    // Check String Input in edit mode
    $config = array(
      'type' => 'input',
      'edit' => 1,
    );
    $this->assertEquals('viewStringInputEditMode', $this->fixture->getFunc($config));

    // Check password
    $config = array(
      'type' => 'input',
      'eval' => 'password',
    );
    $this->assertEquals('viewStringPassword', $this->fixture->getFunc($config));

    // check file
    $config = array(
      'type' => 'group',
      'internal_type' => 'file',
    );
    $this->assertEquals('viewFile', $this->fixture->getFunc($config));

    // check global selector
    $config = array(
      'type' => 'select',
      'foreign_table' => 'test',
    );
    $this->assertEquals('viewDbRelationSelectorGlobal', $this->fixture->getFunc($config));
  }

  public function test_getValue(){
  
    // Get the content of the fe_users table as data test
    $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
      '*, substring_index(name, \' \', 1) as firstName',
      'fe_users',
      ''
    );
		while ($row = $this->fixture->queriers->sql_fetch_assoc_with_tablename($res)) {
			$data[] = $row;
		}
    $GLOBALS['TYPO3_DB']->sql_free_result($res);

    // Get the name of the first user (Valid User) with only the field name
    $this->assertEquals('Valid User', $this->fixture->getValue('fe_users', 'name', $data[0]));

    // Get the name of the first user (Valid User) with the full field name
    $this->assertEquals('Valid User', $this->fixture->getValue('fe_users', 'fe_users.name', $data[0]));

    // Get the first name (case of an alias)
    $this->assertEquals('Valid', $this->fixture->getValue('', 'firstName', $data[0]));
    
    // Return void string if data is not an array
    $data = '';
    $this->assertEquals('', $this->fixture->getValue('', 'firstName', $data));
  }
  
	/***************************************************************/
	/* Admin methods                                               */
	/***************************************************************/
  public function test_userIsAdmin() {
     // set the tableLocal
     $this->fixture->tableLocal = 'tx_savlibraryexample1_members';
    // Get the content of the tx_savlibraryexample1_members table as data test
    $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
      '*',
      $this->fixture->tableLocal,
      ''
    );
		while ($row = $this->fixture->queriers->sql_fetch_assoc_with_tablename($res)) {
			$data[] = $row;
		}
    $GLOBALS['TYPO3_DB']->sql_free_result($res);
  
    // True if there is not an inputAdminField configuration
    $this->fixture->conf['inputAdminField'] = '';
    $this->assertTrue($this->fixture->userIsAdmin($data[0]));

    // True if there is an inputAdminField configuration and the user is
    // a super admin (* in the TS config)
     $this->fixture->conf['inputAdminField'] = 'lastname';
    // set a valid user
    $this->userAuth('validSuperUser', 'test');
    $this->assertTrue($this->fixture->userIsAdmin($data[0]));

    // True if there is an inputAdminField configuration and the user has a
    // correct TS config
     $this->fixture->conf['inputAdminField'] = 'lastname';
    // set a valid user
    $this->userAuth('validUser', 'test');
    $this->assertTrue($this->fixture->userIsAdmin($data[0]));
    // Same with an admin plus user
    $this->userAuth('validAdminPlusUser', 'test');
    $this->assertTrue($this->fixture->userIsAdmin($data[0]));
    // False is the user is not admin plus and admin plus is checked
    $this->userAuth('validUser', 'test');
    $this->assertFalse($this->fixture->userIsAdmin($data[0], 1));
    // True is user is admin plus and admin plus is checked
    $this->userAuth('validAdminPlusUser', 'test');
    $this->assertTrue($this->fixture->userIsAdmin($data[0], 1));

    // True if there is an inputAdminField configuration and the user has a
    // correct TS config and dates are correct
    $this->fixture->conf['inputAdminField'] = 'lastname';
    $this->fixture->conf['inputApplyLimit'] = 1;
    $this->fixture->conf['inputStartDate'] = time() - 1000;
    $this->fixture->conf['inputEndDate'] = time() + 1000;
    // set a valid user
    $this->userAuth('validUser', 'test');
    $this->assertTrue($this->fixture->userIsAdmin($data[0]));

    // False if there is an inputAdminField configuration and the user has a
    // correct TS config and the start date is incorrect
    $this->fixture->conf['inputAdminField'] = 'lastname';
    $this->fixture->conf['inputApplyLimit'] = 1;
    $this->fixture->conf['inputStartDate'] = time() + 500;
    $this->fixture->conf['inputEndDate'] = time() + 1000;
    // set a valid user
    $this->userAuth('validUser', 'test');
    $this->assertFalse($this->fixture->userIsAdmin($data[0]));

    // False if there is an inputAdminField configuration and the user has a
    // correct TS config and the stop date is incorrect
    $this->fixture->conf['inputAdminField'] = 'lastname';
    $this->fixture->conf['inputApplyLimit'] = 1;
    $this->fixture->conf['inputStartDate'] = time() - 1000;
    $this->fixture->conf['inputEndDate'] = time() - 500;
    // set a valid user
    $this->userAuth('validUser', 'test');
    $this->assertFalse($this->fixture->userIsAdmin($data[0]));

    // True if there is an inputAdminField configuration set to cruser_id
    // and the user has a correct TS config and the user created the record.
    $this->fixture->conf['inputAdminField'] = 'cruser_id';
    $this->fixture->conf['inputApplyLimit'] = 0;
    // set a valid user
    $this->userAuth('validUser', 'test');
    $this->assertTrue($this->fixture->userIsAdmin($data[0]));

    // False if there is an inputAdminField configuration set to cruser_id
    // and the user has a correct TS config and the user has not created the record.
    $this->fixture->conf['inputAdminField'] = 'cruser_id';
    $this->fixture->conf['inputApplyLimit'] = 0;
    // set a valid user
    $this->userAuth('validUser', 'test');
    $this->assertFalse($this->fixture->userIsAdmin($data[1]));
  }

  public function test_userIsAllowedToExportData() {
    // Assert true if the user is allowed to export data for an extension
    // Set a valid extension
    $this->fixture->setExtKey('validExt');
    // set a valid user
    $this->userAuth('validUser', 'test');

    $this->assertTrue($this->fixture->userIsAllowedToExportData());

    // Assert false if the user is not allowed to export data for an extension
    // Set another extension
    $this->fixture->setExtKey('unvalidExt');

    $this->assertFalse($this->fixture->userIsAllowedToExportData());

    // Assert false if the user has no TSconfig
    // set an unvalid user
    $this->userAuth('unvalidUser', 'test');

    $this->fixture->setExtKey('validExt');

    $this->assertFalse($this->fixture->userIsAllowedToExportData());
  }
  
  public function test_userIsAllowedToInputData() {
     // set the tableLocal
     $this->fixture->tableLocal = 'tx_savlibraryexample1_members';
    // Get the content of the tx_savlibraryexample1_members table as data test
    $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
      '*',
      $this->fixture->tableLocal,
      ''
    );
		while ($row = $this->fixture->queriers->sql_fetch_assoc_with_tablename($res)) {
			$data[] = $row;
		}
    $GLOBALS['TYPO3_DB']->sql_free_result($res);

    // set a valid user
    $this->userAuth('validUser', 'test');

    // True if there is no group, no date set
    $this->fixture->conf['inputOnForm'] = true;
    $this->assertTrue($this->fixture->userIsAllowedToInputData());

    // False if there is no group, no date set butinputOnForm false
    $this->fixture->conf['inputOnForm'] = false;
    $this->assertFalse($this->fixture->userIsAllowedToInputData());
    
    // True if there is no group and the dates are correct
    $this->fixture->conf['inputOnForm'] = true;
    $this->fixture->conf['inputApplyLimit'] = 1;
    $this->fixture->conf['inputStartDate'] = time() - 1000;
    $this->fixture->conf['inputEndDate'] = time() + 1000;
    $this->assertTrue($this->fixture->userIsAllowedToInputData());

    // False if there is no group and the start date is incorrect
    $this->fixture->conf['inputOnForm'] = true;
    $this->fixture->conf['inputApplyLimit'] = 1;
    $this->fixture->conf['inputStartDate'] = time() + 500;
    $this->fixture->conf['inputEndDate'] = time() + 1000;
    $this->assertFalse($this->fixture->userIsAllowedToInputData());

    // False if there is no group and the end date is incorrect
    $this->fixture->conf['inputOnForm'] = true;
    $this->fixture->conf['inputApplyLimit'] = 1;
    $this->fixture->conf['inputStartDate'] = time() -1000;
    $this->fixture->conf['inputEndDate'] = time() - 500;
    $this->assertFalse($this->fixture->userIsAllowedToInputData());
    
    // True if there is a  valid group
    $this->fixture->conf['inputOnForm'] = true;
    $this->fixture->conf['inputApplyLimit'] = 0;
    $this->fixture->conf['allowedGroups'] = 1;
    $this->assertTrue($this->fixture->userIsAllowedToInputData());

    // False if there is no valid group
    $this->fixture->conf['inputOnForm'] = true;
    $this->fixture->conf['inputApplyLimit'] = 0;
    $this->fixture->conf['allowedGroups'] = 2;
    $this->assertFalse($this->fixture->userIsAllowedToInputData());
  }

  public function test_inputIsAllowedInForm() {
    // Assert true if there is allowed groups and the user belongs to it and inputMode is true
    $this->fixture->inputMode = true;
    $this->fixture->conf['allowedGroups'] = 1;
    $this->userAuth('validUser', 'test');
    $this->assertTrue($this->fixture->inputIsAllowedInForm());

    // Assert false if there is allowed groups and the user belongs to it and inputMode is false
    $this->fixture->inputMode = false;
    $this->fixture->conf['allowedGroups'] = 1;
    $this->userAuth('validUser', 'test');
    $this->assertFalse($this->fixture->inputIsAllowedInForm());

    // Assert false if there is allowed groups and the user does not belongs to it
    $this->fixture->inputMode = true;
    $this->fixture->conf['allowedGroups'] = 2;
    $this->userAuth('validUser', 'test');
    $this->assertFalse($this->fixture->inputIsAllowedInForm());

    // Assert true if there is no allowed groups and inputOnForm is true and inputMode is true
    $this->fixture->conf['allowedGroups'] = 0;
    $this->fixture->conf['inputOnForm'] = true;
    $this->fixture->inputMode = true;
    $this->assertTrue($this->fixture->inputIsAllowedInForm());

    // Assert false if there is no allowed groups and inputOnForm is false and inputMode is true
    $this->fixture->conf['allowedGroups'] = 0;
    $this->fixture->conf['inputOnForm'] = false;
    $this->fixture->inputMode = true;
    $this->assertFalse($this->fixture->inputIsAllowedInForm());

    // Assert false if there is no allowed groups and inputOnForm is true and inputMode is false
    $this->fixture->conf['allowedGroups'] = 0;
    $this->fixture->conf['inputOnForm'] = true;
    $this->fixture->inputMode = false;
    $this->assertFalse($this->fixture->inputIsAllowedInForm());
  }
  
  public function test_userBelongsToAllowedGroup() {
    // Assert true if the user belongs to a valid group
    $this->fixture->conf['allowedGroups'] = 1;
    $this->userAuth('validUser', 'test');
    $this->assertTrue($this->fixture->userBelongsToAllowedGroup());

    // Assert false if the user does not belong to a valid group
    $this->fixture->conf['allowedGroups'] = 2;
    $this->userAuth('validUser', 'test');
    $this->assertFalse($this->fixture->userBelongsToAllowedGroup());

    // Assert false if there is no valid group
    $this->fixture->conf['allowedGroups'] = 0;
    $this->assertFalse($this->fixture->userBelongsToAllowedGroup());
  }

	/***************************************************************/
	/* Language methods                                            */
	/***************************************************************/
  public function test_getLibraryLL() {
    $this->assertEquals('Message for tests.', $this->fixture->getLibraryLL('message.forTests'));
    $this->assertEquals('Message for tests. Added message.', $this->fixture->getLibraryLL('message.forTests', ' Added message.'));
    $this->assertEquals('', $this->fixture->getLibraryLL('unknown'));
  }
  
  public function test_getExtLL() {
    // Labelb Back is defined in locallang.xml of sav_library_example1
    $this->assertEquals('Back', $this->fixture->getExtLL('back'));

    // For an unknown label, getExtLL returns a void string
    // and errors is set to the message associated with error.missingLabel.
    $this->assertEquals('', $this->fixture->getExtLL('unknown'));
    $this->assertEquals($this->fixture->getLibraryLL('error.missingLabel', 'unknown'), $this->fixture->getError(0));

    // For an unknown label, getExtLL returns the label if the second argument is 0
    $this->assertEquals('unknown', $this->fixture->getExtLL('unknown', 0));
  }
  
  public function test_processLocalizationTags() {

    $this->loadExt('sav_library_example1');
    // The string is not modified if there is no tag
    $this->assertEquals('Test without a tag', $this->fixture->processLocalizationTags('Test without a tag'));

    // The tag is replaced by its definition
    $this->assertEquals('Test without a tag : Back', $this->fixture->processLocalizationTags('Test without a tag : $$$back$$$'));

    // The tag is replaced by its definition. Several tags can be used
    $this->assertEquals('Back : Test without a tag : Back', $this->fixture->processLocalizationTags('$$$back$$$ : Test without a tag : $$$back$$$'));
  }

	/***************************************************************/
	/* Other methods                                               */
	/***************************************************************/
	public function test_date2timestamp() {

    // Use default format '%d/%m/%Y' (date) '%d/%m/%Y %H:%M' (datetime)
    $config = array(
      'eval' => 'date',
    );
    $this->assertEquals(mktime(0,0,0,2,1,2009), $this->fixture->date2timestamp('01/02/2009', $config, $errors));
    $config = array(
      'eval' => 'datetime',
    );
    $this->assertEquals(mktime(19,30,0,2,1,2009), $this->fixture->date2timestamp('01/02/2009 19:30', $config, $errors));

    // use another format
    $config = array(
      'eval' => 'datetime',
      'format' => '%d/%m/%y %H:%M',
    );
    $this->assertEquals(mktime(7,30,0,2,1,2009), $this->fixture->date2timestamp('01/02/09 07:30', $config, $errors));
  }
	
	
  /***************************************************************/
  /* Condition methods                                       	   */
  /***************************************************************/
  public function test_isInString() {
    $this->assertTrue($this->fixture->isInString('test', 'This is a test.'));
    $this->assertFalse($this->fixture->isInString('test', 'This is a not a bird.'));
  }
  
  public function test_isNotInString() {
    $this->assertFalse($this->fixture->isNotInString('test', 'This is a test.'));
    $this->assertTrue($this->fixture->isNotInString('test', 'This is a not a bird.'));
  }
  
  public function test_isGroupMember() {
    // Assert true with an user with a valid group
    $this->userAuth('validUser', 'test');
    $this->assertTrue($this->fixture->isGroupMember('validGroup'));

    // Assert false with an user with an unvalid group
    $this->userAuth('unvalidUser', 'test');
		$this->assertFalse($this->fixture->isGroupMember('validGroup'));
		
    // Assert false if no group is provided
    $this->userAuth('validUser', 'test');
		$this->assertFalse($this->fixture->isGroupMember(''));

    // Assert false if an unknown group is provided
    $this->userAuth('validUser', 'test');
		$this->assertFalse($this->fixture->isGroupMember('unkownGroup'));
		
    // Assert false if no user is provided
    $this->userAuth('', '');
		$this->assertFalse($this->fixture->isGroupMember('validGroup'));

    // Assert false if no user and no group are provided
    $this->userAuth('', '');
		$this->assertFalse($this->fixture->isGroupMember(''));
  }

  public function test_isNotGroupMember() {
    // Assert false with an user with a valid group
    $this->userAuth('validUser', 'test');
		$this->assertFalse($this->fixture->isNotGroupMember('validGroup'));

    // Assert true with an user with an unvalid group
    $this->userAuth('validUser', 'test');
		$this->assertTrue($this->fixture->isNotGroupMember('unvalidGroup'));

    // Assert true if no group is provided
    $this->userAuth('validUser', 'test');
		$this->assertTrue($this->fixture->isNotGroupMember(''));

    // Assert true if an unknown group is provided
    $this->userAuth('validUser', 'test');
		$this->assertTrue($this->fixture->isNotGroupMember('unkownGroup'));

    // Assert true if no user is provided
    $this->userAuth('', '');
		$this->assertTrue($this->fixture->isNotGroupMember('validGroup'));

    // Assert true if no user and no group are provided
    $this->userAuth('', '');
		$this->assertTrue($this->fixture->isNotGroupMember(''));
  }
  
}

?>