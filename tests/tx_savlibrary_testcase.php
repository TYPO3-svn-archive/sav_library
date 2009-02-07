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
		$this->importExtensions(array('cms'));
		$this->importDataSet(dirname(__FILE__). '/tx_savlibrary_testcase_dataset.xml');

		$this->initFE();

    // Create the sav_library object
    $this->fixture = new tx_savlibrary();
    $this->fixture->extObj = $this->initExt();
    $this->fixture->xmlToSavlibrayConfig($this->fixture->extObj->cObj->fileResource('EXT:sav_library/res/sav_library.xml'));
    $this->fixture->queriers = t3lib_div::makeInstance('tx_savlibrary_defaultQueriers');
    $this->fixture->queriers->initVars($this->fixture);

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
  
    // Get the content of the fe_users table
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
    // The extension sav_library_example1 must be loaded for this test
    $this->loadExt('sav_library_example1');
    $this->assertEquals('Back', $this->fixture->getExtLL('back'));

    // For an unknown label, getExtLL returns a void string
    // and errors is set to the message associated with error.missingLabel.
    $this->assertEquals('', $this->fixture->getExtLL('unknown'));
    $this->assertEquals($this->fixture->getLibraryLL('error.missingLabel', 'unknown'), $this->fixture->getError(0));

    // For an unknown label, getExtLL returns the label if the second argument is 0
    $this->assertEquals('unknown', $this->fixture->getExtLL('unknown', 0));
  }
  
  public function test_processLocalizationTags() {
    // The extension sav_library_example1 must be loaded for this test
    $this->loadExt('sav_library_example1');
    // The string is not modified if there is no tag
    $this->assertEquals('Test without a tag', $this->fixture->processLocalizationTags('Test without a tag'));

    // The tag is replaced by its definition
    $this->assertEquals('Test without a tag : Back', $this->fixture->processLocalizationTags('Test without a tag : $$$back$$$'));

    // The tag is replaced by its definition. Several tags can be used
    $this->assertEquals('Back : Test without a tag : Back', $this->fixture->processLocalizationTags('$$$back$$$ : Test without a tag : $$$back$$$'));
  }
  
  /***************************************************************/
  /* Condition methods                                       	   */
  /***************************************************************/
  
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
