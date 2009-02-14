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



class tx_savlibrary_queriers_testcase extends tx_phpunit_frontend {
  public $fixture;

  public function setUp() {

		// Init database
		$this->initDatabase();

    // Import data in the database
    // The extension sav_library_example1 must be installed for this test case
		$this->importExtensions(array('sav_library_example1'));
		$this->importDataSet(dirname(__FILE__). '/tx_savlibrary_testcase_dataset.xml');

		$this->initFE();

    // Create the sav_library object
    $this->fixture = new tx_savlibrary();
    $this->fixture->extObj = $this->loadExt('sav_library_example1');
    $this->fixture->xmlToSavlibrayConfig($this->fixture->extObj->cObj->fileResource('EXT:sav_library/res/sav_library.xml'));
    $this->fixture->initVars($this->fixture->extObj);
    $this->fixture->initClasses();
  }

	public function tearDown() {

		// insures that test database always is dropped
		// even when testcases fails
		$this->dropDatabase();
	}

   /***************************************************************
    *
    *   Utils
    *
   ***************************************************************/

  public function test_numberToAlias() {
    // 1 gives A
    $this->assertEquals('A',
      $this->fixture->queriers->numberToAlias(1));
    // 2 gives B
    $this->assertEquals('B',
      $this->fixture->queriers->numberToAlias(2));
    // 26 gives Z
    $this->assertEquals('Z',
      $this->fixture->queriers->numberToAlias(26));
    // 27 gives AA
    $this->assertEquals('AA',
      $this->fixture->queriers->numberToAlias(27));
    // 52 gives AZ
    $this->assertEquals('AZ',
      $this->fixture->queriers->numberToAlias(52));
    // 53 gives BA
    $this->assertEquals('BA',
      $this->fixture->queriers->numberToAlias(53));
    // 702 gives ZZ (27*26 possibilities)
    $this->assertEquals('ZZ',
      $this->fixture->queriers->numberToAlias(702));
  }

  public function test_aliasToNumber() {
    // '' gives 0
    $this->assertEquals(0,
      $this->fixture->queriers->aliasToNumber(''));
    // A gives 1
    $this->assertEquals(1,
      $this->fixture->queriers->aliasToNumber('A'));
    // B gives 2
    $this->assertEquals(2,
      $this->fixture->queriers->aliasToNumber('B'));
    // Z gives 26
    $this->assertEquals(26,
      $this->fixture->queriers->aliasToNumber('Z'));
    // AA gives 27
    $this->assertEquals(27,
      $this->fixture->queriers->aliasToNumber('AA'));
    // 52 gives AZ
    $this->assertEquals(52,
      $this->fixture->queriers->aliasToNumber('AZ'));
    // 53 gives BA
    $this->assertEquals(53,
      $this->fixture->queriers->aliasToNumber('BA'));
    // 702 gives ZZ (27*26 possibilities)
    $this->assertEquals(702,
      $this->fixture->queriers->aliasToNumber('ZZ'));
    // Error is set with an length greater than 2
    $this->fixture->queriers->aliasToNumber('AAA');
    $this->assertEquals(
      $this->fixture->getLibraryLL('error.incorrectAlias', 'AAA'),
      $this->fixture->getError(0));
  }

  public function test_buidAliasTable() {
    $tableArray = array();
    // If it is the first table in the array, returns the table in def and table fields
    $this->assertEquals(array('def' => 'fe_users', 'table' => 'fe_users'),
      $this->fixture->queriers->buidAliasTable('fe_users', $tableArray));

    // If the same table is input, an alias is generated in the def field and the
    // table field is this alias. Aliases are letter (A to Z)
    $this->assertEquals(array('def' => 'fe_users AS A', 'table' => 'A'),
      $this->fixture->queriers->buidAliasTable('fe_users', $tableArray));

    // Samme as above, the alias becomes B
    $this->assertEquals(array('def' => 'fe_users AS B', 'table' => 'B'),
      $this->fixture->queriers->buidAliasTable('fe_users', $tableArray));

  }
  
}

?>
