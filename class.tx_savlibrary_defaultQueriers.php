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
 * SAV Library: standard queriers
 *
 * @author	Yolf <yolf.typo3@orange.fr>
 *
 */
 
class tx_savlibrary_defaultQueriers {

  protected $sqlFields;              // Used in sql_fetch_assoc_with_tablename(). Array of field information.
  protected $aliasTable = array();   // Aliases for tables
  protected $refTable = array();     // Reference for tables

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
	 * Default SELECT querier for 'showAll'.
 	 *
   * @param $query array (query array)
	 * @param $uid integer (uid)
 	 *
	 * @return array (query result or false in case of error)
	 */
	public function showAll_SELECT_defaultQuerier(&$query, $uid=0) {

	  // Add or replace the query with the page TSconfig if any
    $pageTSConfig = $GLOBALS['TSFE']->getPagesTSconfig();
    $fieldTSConfig = $pageTSConfig['tx_' .
      str_replace(
        '_',
        '',
        $this->extKey
      ) .
      '.'][$this->savlibrary->formConfig['title'] . '.']['showAll.']['query.'];
    if(is_array($fieldTSConfig)) {
      foreach($fieldTSConfig as $key=>$value) {
        $query[$key] = $value;
      }  
    }

		// Get the extKey from the caller if any
		$extKeyCaller = $this->savlibrary->sessionFilterSelected;

		if ($extKeyCaller) {
		  $extFilter = $this->savlibrary->sessionFilter[$extKeyCaller];

      $search = $extFilter['search'];
      $searchOrder = $extFilter['searchOrder'];
			$addWhere = ' AND '.($extFilter['addWhere'] ? $extFilter['addWhere'] : 0);
      $addTables = $extFilter['tableName'];

		} else {
        // Add a where clause depending on the configuration
			 $addWhere = ' AND '.(
        isset($this->savlibrary->conf['noFilterShowAll']) ?
        $this->savlibrary->conf['noFilterShowAll'] :
        1
      );
		}

		// Add the permanent filter if any
		$addWhere .= (
      isset($this->savlibrary->conf['permanentFilter']) && $this->savlibrary->conf['permanentFilter'] ?
      ' AND ' . $this->savlibrary->conf['permanentFilter'] :
      ''
    );
    $addWhere = $this->processWhereClause($addWhere);

    // Get the where identifier if sent via the get method
    if ($this->savlibrary->sessionExt[$this->savlibrary->formName]['where']) {
      $whereId = $this->savlibrary->sessionExt[$this->savlibrary->formName]['where'];
    }

    $tableReference = $this->buidTableReference($query, $addTables);

    // Process the where clause
    if ($query['where']) {    
      
      $query['where'] = $this->processWhereClause($query['where']);   

      // Replace the table names by their aliases in the where clause
      $query['where'] = $this->replaceTableNames($query['where']); 
    }

    // Get the number of items satisfying the query with no limit field
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			/* SELECT   */	'count(' .
        ($query['group'] ? 'distinct ' . $query['group']:'*') . ') as nbitem' .
				'',
			/* FROM     */	$tableReference .
				'',
 			/* WHERE    */	' 1' .
 			  $this->cObj->enableFields($query['tableLocal']) .
 			  $this->getAllowedPages($query['tableLocal']) .
				($whereId ?
          ($query['whereTags'][$whereId]['where'] ? ' AND ' . $query['whereTags'][$whereId]['where'] : '') :
          (($query['where'] && !$search) ? ' AND ' . $query['where'] : '')
        ).
				$this->replaceTableNames($addWhere) .
				'',
			/* GROUP BY */	
        '',
			/* ORDER BY */	
        '',
			/* LIMIT    */	
				''
			);

    // Check for errors
	  if ($GLOBALS['TYPO3_DB']->sql_error($res)) {
      return false;
    }

    $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);

	  $nbitem = $row['nbitem'];
    
		// Exec the query
		$order = (($GLOBALS['TCA'][$query['tableLocal']]['ctrl']['sortby'] && !($search && $searchOrder)) ? $query['tableLocal'] . '.' . $GLOBALS['TCA'][$query['tableLocal']]['ctrl']['sortby'] : $searchOrder);
      	
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			/* SELECT   */	'*' .
				$this->replaceTableNames($query['aliases'] ? ', ' . $query['aliases'] : '') .
				($extFilter['fieldName'] ? ', ' . $extFilter['fieldName'] . ' as fieldname' : '') .
				'',
			/* FROM     */	$tableReference .
				'',
 			/* WHERE    */	' 1' .
 			  $this->cObj->enableFields($query['tableLocal']) .
 			  $this->getAllowedPages($query['tableLocal']) .
				($whereId
          ? ($query['whereTags'][$whereId]['where'] ? ' AND ' . $query['whereTags'][$whereId]['where'] : '')
          : (($query['where'] && !$search )? ' AND ' . $query['where'] : '')
        ) .
				$this->replaceTableNames($addWhere) .
				'',
			/* GROUP BY */	$query['group'] .
        '',
			/* ORDER BY */	
				$this->replaceTableNames(
          $whereId
          ? ($query['whereTags'][$whereId]['order'] ? $query['whereTags'][$whereId]['order'] : '')
          : (
            ($query['order'] && !($search && $searchOrder))
            ? $query['order']
            : $order)
        ),
			/* LIMIT    */	
				($this->savlibrary->conf['maxItems'] ? ($this->savlibrary->conf['maxItems']*($this->savlibrary->limit)) . ',' . ($this->savlibrary->conf['maxItems']) : '')
		);

    // Check for errors
	  if ($GLOBALS['TYPO3_DB']->sql_error($res)) {
      return false;
    }

		$array = array();
    $cpt = 0;
		while ($row = $this->sql_fetch_assoc_with_tablename($res, $cpt++)) {
		  $row['__nbitem__'] = $nbitem;
		  $row['uid'] = $row[$query['tableLocal'] . '.uid'];
		  $row['cruser_id'] = $row[$query['tableLocal'] . '.cruser_id'];
			$array[] = $row;
		}
		$GLOBALS['TYPO3_DB']->sql_free_result($res);

	 	return $array;
	}

	/**
	 * Default SELECT querier for 'showSingle'.
	 *
   * @param $query array (query array)
	 * @param $uid integer (uid)
 	 *
	 * @return array (query result)
	 */
	public function showSingle_SELECT_defaultQuerier(&$query, $uid=0) {

	  // Add or replace the query with the page TSconfig if any
    $pageTSConfig = $GLOBALS['TSFE']->getPagesTSconfig();
    $fieldTSConfig = $pageTSConfig['tx_' . str_replace('_', '', $this->extKey) . '.'][$this->savlibrary->formConfig['title'] . '.']['showSingle.']['query.'];
    if(is_array($fieldTSConfig)) {
      foreach($fieldTSConfig as $key=>$value) {
        $query[$key] = $value;
      }  
    }

		// Exec the query
		$tableReference = $this->buidTableReference($query, $addTables);

		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			/* SELECT   */	'*' .
				$this->replaceTableNames($query['aliases'] ? ', ' . $query['aliases'] : '') .
				'',
			/* FROM     */	$tableReference .
				'',
 			/* WHERE    */	' 1' .
				' AND '.$query['tableLocal'] . '.uid=' . intval($uid) .
				($query['addWhere'] ? ' AND ' . $this->replaceTableNames($query['addWhere']) : ''),
			/* GROUP BY */	$query['group'] .
				'',
			/* ORDER BY */	
				'',
			/* LIMIT    */
        ''
		);

    // Check for errors
	  if ($GLOBALS['TYPO3_DB']->sql_error($res)) {
      return false;
    }

		$array = array();

		while ($row = $this->sql_fetch_assoc_with_tablename($res)) {
		  $row['uid'] = $row[$query['tableLocal'] . '.uid'];
		  $row['cruser_id'] = $row[$query['tableLocal'] . '.cruser_id'];
			$array[] = $row;
		}
    $GLOBALS['TYPO3_DB']->sql_free_result($res);
    
 	 	return $array;
	}

	/**
	 * Default SELECT querier for 'inputForm'.
	 *
   * @param $query array (query array)
	 * @param $uid integer (uid)
 	 *
	 * @return array (query result)
	 */
	public function inputForm_SELECT_defaultQuerier(&$query, $uid=0) {
	
	  // Add or replace the query with the page TSconfig if any
    $pageTSConfig = $GLOBALS['TSFE']->getPagesTSconfig();
    $fieldTSConfig = $pageTSConfig['tx_' .
      str_replace(
        '_',
        '',
        $this->extKey
      ) . '.'][$this->savlibrary->formConfig['title'] . '.']['inputForm.']['query.'];
    if(is_array($fieldTSConfig)) {
      foreach($fieldTSConfig as $key=>$value) {
        $query[$key] = $value;
      }  
    }

		if (!$uid) {
		  $array = array();
      return $array;
		}

		// Exec the query
		$tableReference = $this->buidTableReference($query, $addTables);

		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			/* SELECT   */	'*'.
				$this->replaceTableNames($query['aliases'] ? ', ' . $query['aliases'] : '') .
				'',
			/* FROM     */	$tableReference .
				'',
 			/* WHERE    */	' 1' .
				' AND '.$query['tableLocal'] . '.uid=' . intval($uid) .
				'',
			/* GROUP BY */	$query['group'] .
				'',
			/* ORDER BY */	
				'',
			/* LIMIT    */	''	
		);

    // Check for errors
	  if ($GLOBALS['TYPO3_DB']->sql_error($res)) {
      return false;
    }

		$array = array();
		while ($row = $this->sql_fetch_assoc_with_tablename($res)) {
		  $row['uid'] = $row[$query['tableLocal'] . '.uid'];
			$array[] = $row;
		}
    $GLOBALS['TYPO3_DB']->sql_free_result($res);

 	 	return $array;
	}

	/**
	 * Default SELECT querier for 'updateForm'.
	 *
   * @param $query array (query array)
	 * @param $uid integer (uid)
 	 *
	 * @return array (query result)
	 */
	public function updateForm_SELECT_defaultQuerier(&$query, $uid=0) {
	
		  // Add or replace the query with the page TSconfig if any
    $pageTSConfig = $GLOBALS['TSFE']->getPagesTSconfig();
    $fieldTSConfig = $pageTSConfig['tx_' .
      str_replace(
        '_',
        '',
        $this->extKey
      ) . '.'][$this->savlibrary->formConfig['title'] . '.']['updateForm.']['query.'];
    if(is_array($fieldTSConfig)) {
      foreach($fieldTSConfig as $key=>$value) {
        $query[$key] = $value;
      }  
    }

		if (!$uid) {
		  $array = array();
      return $array;
		}
    $this->savlibrary->addMessage('message.clikToUpdate');

    // Gest the variable
    $extPOSTVars = t3lib_div::_POST($this->savlibrary->formName); 

    // Buid the configuration table
    $viewConfiguration = $this->extConfig['views'][$this->savlibrary->formConfig['updateForm']][$this->savlibrary->folderTab]['fields'];
    $configTable = $this->buildConfigurationTable($viewConfiguration);
    
    // Verify the required fields
    if (is_array($extPOSTVars)) {
      $error = false;
      foreach ($extPOSTVars as $keyVar => $valueVar) {
        if (is_array($valueVar) && isset($configTable[$keyVar])) {
          foreach ($valueVar as $key => $value) {
            if ($configTable[$keyVar]['required'] && !trim($value)) {
              $error = true;
      				$errorForm = 'error.fieldRequired'; 
      				break;
            }
          } 
        } 
      }           
    }     

    // Check if it is the Admin mode
    if ($extPOSTVars['formAction']=='updateFormAdmin') {
      // Prepare data for update
      foreach($_POST[$this->savlibrary->formName] as $field => $value) {
        if(!$_POST['Check_'.$this->savlibrary->formName][$field]) {
          unset($_POST[$this->savlibrary->formName][$field]);
        } 
      }
      // Update     
      $errors = $this->savlibrary->queries_update(
        $this->extConfig['queries'][$this->savlibrary->formConfig['query']]
      );
      if ($this->savlibrary->errorInForm) {
        foreach($errors as $key => $error) {
          $this->savlibrary->addError(current($error), ' [' . $key . ']');
        }      
      } else {
        // Get the _submitted data
        $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
          /* SELECT  */ '_submitted_data_',
        	/* TABLE   */	$this->savlibrary->tableLocal,		
        	/* WHERE   */	$this->savlibrary->tableLocal . '.uid=' . intval($uid)
       	);
        $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
        $submitted_data = unserialize($row['_submitted_data_']);
      
        // Clear the data updated by the Admin
        unset($submitted_data[$this->savlibrary->formShortName]);
        unset($submitted_data['New_' . $this->savlibrary->formShortName]);
        unset($_POST['New_'.$this->savlibrary->formName]);

        // update
        $res = $GLOBALS['TYPO3_DB']->exec_UPDATEquery(
        	/* TABLE   */	$this->savlibrary->tableLocal,		
        	/* WHERE   */	$this->savlibrary->tableLocal . '.uid=' . intval($uid),
        	/* FIELDS  */	array('_submitted_data_' => serialize($submitted_data))
       	); 
       	
       	$this->savlibrary->addMessage('message.dataSaved', '', 'datasaved');
      } 
    } elseif ($extPOSTVars) {
      // Get the previous data
      $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
        /* SELECT  */ '_submitted_data_',
      	/* TABLE   */	$this->savlibrary->tableLocal,		
      	/* WHERE   */	$this->savlibrary->tableLocal . '.uid=' . intval($uid)
     	);
      $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);

      // Merge with new one
      $arrayToMerge = (
        $error ?
        array('Temp_' . $this->savlibrary->formShortName => $extPOSTVars) :
        array($this->savlibrary->formShortName => $extPOSTVars)
      );
      $submitted_data = (
        $row['_submitted_data_'] ?
        array_merge(unserialize($row['_submitted_data_']), $arrayToMerge) :
        $arrayToMerge
      );
      if ($error) {
        unset($submitted_data[$this->savlibrary->formShortName]);
      } else {
        unset($submitted_data['Temp_' . $this->savlibrary->formShortName]);
      }

      // Check if data with the New_ tag were posted
      $extNewPOSTVars = t3lib_div::_POST('New_' . $this->savlibrary->formName);

      if ($extNewPOSTVars) {
        $submitted_data = array_merge(
          $submitted_data,
          array('New_' . $this->savlibrary->formShortName => $extNewPOSTVars)
        );
      }  
      
      // update
      $res = $GLOBALS['TYPO3_DB']->exec_UPDATEquery(
      	/* TABLE   */	$this->savlibrary->tableLocal,		
      	/* WHERE   */	$this->savlibrary->tableLocal . '.uid=' . intval($uid),
      	/* FIELDS  */	array('_submitted_data_' => serialize($submitted_data))
     	); 

      if ($error) {
       	$this->savlibrary->addError($errorForm);      
      } else {    	
       	$this->savlibrary->addMessage('message.dataSaved', '', 'datasaved');
      }
    }

		// Exec the query
		$tableReference = $this->buidTableReference($query, $addTables);

		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			/* SELECT   */	'*' .
				$this->replaceTableNames($query['aliases'] ? ', ' . $query['aliases'] : '') .
				'',
			/* FROM     */	$tableReference .
				'',
 			/* WHERE    */	' 1' .
				' AND ' . $query['tableLocal'] . '.uid=' . intval($uid) .
				'',
			/* GROUP BY */	
				'',
			/* ORDER BY */	
				($query['order'] ? $query['order'] : ''),
			/* LIMIT    */
        '1'
		);

    // Check for errors
	  if ($GLOBALS['TYPO3_DB']->sql_error($res)) {
      return false;
    }

		$array = array();
		while ($row = $this->sql_fetch_assoc_with_tablename($res)) {
		  $row['uid'] = $row[$query['tableLocal'] . '.uid'];
			$array[] = $row;
		}
    $GLOBALS['TYPO3_DB']->sql_free_result($res);

 	 	return $array;
	}


	/**
	 * Default SELECT querier for 'export'.
	 *
   * @param $query array (query array)
	 * @param $uid integer (uid)
 	 *
	 * @return array (query result)
	 */
	public function export_SELECT_defaultQuerier(&$query, $uid=0) {
	
    $addTables = $query['addTables'];
    
    $tableReference = $this->buidTableReference($query, $addTables);
    
    $GLOBALS['TYPO3_DB']->store_lastBuiltQuery = true;

    $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			/* SELECT   */	($query['fields'] ? $query['fields'] : '*') .
				$this->replaceTableNames($query['aliases'] ? ', ' . $query['aliases'] : '') .
				'',
			/* FROM     */	$tableReference .
				'',
 			/* WHERE    */	' 1' .
 			  $this->cObj->enableFields($query['tableLocal']) .
 			  $this->getAllowedPages($query['tableLocal']) .
 			  ($query['where'] ? ' AND ' . $this->processWhereClause($query['where']) : '') .
				'',
			/* GROUP BY */	$query['group'] .
        '',
			/* ORDER BY */	$query['order'] .
        '',
			/* LIMIT    */	$query['limit'] .
			  ''
			);

		$error = $GLOBALS['TYPO3_DB']->sql_error($res);
		if ($error)	{
			return array(
				'caller' => 't3lib_DB::' . $func,
				'ERROR' => $error,
				'lastBuiltQuery' => $GLOBALS['TYPO3_DB']->debug_lastBuiltQuery,
			);
		} else {
      return $res;
    }

	}

	/**
	 * Default DELETE querier.
	 *
   * @param $query array (query array)
	 * @param $uid integer (uid)
 	 *
	 * @return array (query result)
	 */
	public function DELETE_defaultQuerier(&$query, $uid=0) {

		$res = $GLOBALS['TYPO3_DB']->exec_UPDATEquery(
			/* TABLE   */	$query['tableLocal'],		
			/* WHERE   */	$query['tableLocal'] . '.uid=' . intval($uid),
			/* FIELDS  */	array('deleted' => 1)
			);
	}

	/**
	 * Default UPDATE querier.
	 *
   * @param $query array (query array)
	 * @param $uid integer (uid)
 	 *
	 * @return array (query result)
	 */
	public function UPDATE_defaultQuerier(&$query, $uid=0) {

 		if (! $GLOBALS['TSFE']->fe_user->user['uid']) {
			return array('fatal' => 'notAuthentified');
		}

    // Get the view configuration
    if ($this->savlibrary->formConfig['updateForm']) {
			$viewConfiguration = $this->extConfig['views'][$this->savlibrary->formConfig['updateForm']][$this->savlibrary->folderTab]['fields'];
			
		  // Set viewName for the page TSconfig
      $this->savlibrary->viewName = 'updateForm';
		} else {
		  $viewConfiguration = $this->extConfig['views'][$this->savlibrary->formConfig['inputForm']][$this->savlibrary->folderTab]['fields'];

		  // Set viewName for the page TSconfig
      $this->savlibrary->viewName = 'inputForm';
		}

    // Buid the configuration table
    $configTable = $this->buildConfigurationTable($viewConfiguration);

    // Check if the mailAways is set. If found, the first configuration field is taken for configuration
    foreach ($configTable as $field => $config) {
      if ($config['mail'] && $config['mailalways']) {
        $configMailAlways = $config;
        break;
      }    
    }   

    // Check if the update concerns subform items processing
    $getVars = t3lib_div::_GET($this->savlibrary->formName);
       
    switch ($this->savlibrary->formAction) {
      case 'upBtn':
      case 'downBtn': 
         
        if ($this->savlibrary->formAction == 'upBtn') {
          $comp = '<';
          $direction = 'desc';    
        } else {
          $comp = '>';
          $direction = 'asc';
        }

        $MM_table = $configTable[$getVars['field']]['MM'];
        $uidItem = $getVars['uidItem'];

        // Get the sorting value of the item
      	$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
      		/* SELECT  */  'sorting',
      		/* TABLE   */	$MM_table,		
      		/* WHERE   */	$MM_table . '.uid_local=' . intval($uid) .
            ' AND ' . $MM_table . '.uid_foreign=' . intval($uidItem)
     		);
        $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
        $sorting = $row['sorting'];
        
        // Get the minimum
      	$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
      		/* SELECT  */  '*',
      		/* TABLE   */	$MM_table,
      		/* WHERE   */	$MM_table . '.uid_local=' . intval($uid),
          /* GROUP   */ '',
          /* ORDER BY */	'sorting asc'
     		);
        $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
        $uidForeignMin = $row['uid_foreign'];
        $sortingMin = $row['sorting'];

        // Get the maximum
      	$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
      		/* SELECT  */  '*',
      		/* TABLE   */	$MM_table,
      		/* WHERE   */	$MM_table . '.uid_local=' . intval($uid),
          /* GROUP   */ '',
          /* ORDER BY */	'sorting desc'
     		);
        $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
        $uidForeignMax = $row['uid_foreign'];
        $sortingMax = $row['sorting'];

        // Find the record to change
        if ($sorting == $sortingMin && $this->savlibrary->formAction == 'upBtn') {
          $newSorting = $sortingMax + 1;

          // update the first record
        	$res = $GLOBALS['TYPO3_DB']->exec_UPDATEquery(
        		/* TABLE   */	$MM_table,
        		/* WHERE   */	$MM_table . '.uid_local=' . intval($uid) .
              ' AND ' . $MM_table . '.uid_foreign=' . intval($uidItem),
        		/* FIELDS  */	array('sorting' => $newSorting)
       		);
       		
       		// reorder all the records
       		$queryUpdate = 'UPDATE ' . $MM_table . ' SET sorting = sorting - 1 ' .
            ' WHERE ' . $MM_table . '.uid_local=' . intval($uid);
          $res = $GLOBALS['TYPO3_DB']->sql_query($queryUpdate);
          
        } elseif ($sorting == $sortingMax && $this->savlibrary->formAction == 'downBtn') {
          $newSorting = $sortingMin - 1;

          // update the first record
        	$res = $GLOBALS['TYPO3_DB']->exec_UPDATEquery(
        		/* TABLE   */	$MM_table,
        		/* WHERE   */	$MM_table . '.uid_local=' . intval($uid) .
              ' AND ' . $MM_table . '.uid_foreign=' . intval($uidItem),
        		/* FIELDS  */	array('sorting' => $newSorting)
       		);

       		// reorder all the records
       		$queryUpdate = 'UPDATE ' . $MM_table . ' SET sorting = sorting + 1 ' .
            ' WHERE ' . $MM_table . '.uid_local=' . intval($uid);
          $res = $GLOBALS['TYPO3_DB']->sql_query($queryUpdate);

        } else {
        	$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
        		/* SELECT  */  '*',
        		/* TABLE   */	$MM_table,
        		/* WHERE   */	$MM_table . '.uid_local=' . intval($uid) .
              ' AND ' . $MM_table . '.sorting' . $comp . $sorting,
            /* GROUP   */ '',
            /* ORDER BY */	'sorting ' . $direction
       		);
          $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
          $newSorting = $row['sorting'];
          $uidForeign = $row['uid_foreign'];
   
          // update the first record
        	$res = $GLOBALS['TYPO3_DB']->exec_UPDATEquery(
        		/* TABLE   */	$MM_table,
        		/* WHERE   */	$MM_table . '.uid_local=' . intval($uid) .
              ' AND ' . $MM_table . '.uid_foreign=' . intval($uidItem),
        		/* FIELDS  */	array('sorting' => $newSorting)
       		);
               
          // update the second record
        	$res = $GLOBALS['TYPO3_DB']->exec_UPDATEquery(
        		/* TABLE   */	$MM_table,
        		/* WHERE   */	$MM_table . '.uid_local=' . intval($uid) .
              ' AND ' . $MM_table . '.uid_foreign=' . intval($uidForeign),
        		/* FIELDS  */	array('sorting' => $sorting)
       		);
        }

     		return 0;
        break;
      case 'deleteItemBtn':    
        // Set the deleted flag of the foreign table
        $MM_table = $configTable[$getVars['field']]['MM'];
        $allowed_table = $configTable[$getVars['field']]['allowed'];
        $uidItem = $getVars['uidItem'];

       	$res = $GLOBALS['TYPO3_DB']->exec_UPDATEquery(
      		/* TABLE   */	$allowed_table,		
      		/* WHERE   */	$allowed_table . '.uid=' . intval($uidItem),
      		/* FIELDS  */	array('deleted' => 1)
     		);
                 
        // Delete the relation
        if (!$configTable[$getVars['field']]['norelation']) {
  				$res = $GLOBALS['TYPO3_DB']->exec_DELETEquery(
            /* TABLE   */	$MM_table,		
            /* WHERE   */	$MM_table . '.uid_local=' . intval($uid) .
              ' AND ' . $MM_table . '.uid_foreign=' . intval($uidItem)
          );   
        }
        return 0;             
        break;    
    }

    // Process the data
		$row = t3lib_div::_POST($this->savlibrary->formName);	

		if (!is_array($row)) {
		  if (is_array(t3lib_div::_POST('New_' . $this->savlibrary->formName))) {
        $row = array();
      } else {
  			return 0;
  		}
		}
		$error = false;

		// check for uploaded files
		if (is_array($GLOBALS['_FILES'][$this->savlibrary->formName])) {	
			foreach($GLOBALS['_FILES'][$this->savlibrary->formName]['name'] as $key => $value) {
			
				if (array_key_exists($key, $configTable)) {
				  foreach ($value as $k => $v) {

            // Insert the file name in the row
            $v = str_replace(' ', '_', $v);
            if ($v) {
         						  
              $config = $configTable[$key];
               
              // Verification 
              $func = $config['verifier'];
              if ($func) {
                if (method_exists($this->savlibrary->verifiers, $func)) {
                  $temp = $this->savlibrary->verifiers->$func($v, $config['verifierparam']);
                  if (!$errorForm[$key][$k] && $temp) {
                    $errorForm[$key][$k] = $temp;
                    $error_field = true;
                  }
                } else {
                  $error_field = true;
				          $errorForm[$key][$k] = 'error.verifierUnknown';
                }      
              }            
	
              if (!$error_field) {
  						  $row[$key][$k] = $v;
  						  $value = $v;
					       // check if the file extention is allowed
                $path_parts = pathinfo($value);

                if (($config['allowed'] && strpos($config['allowed'], $path_parts['extension']) === false)
                  || (!$config['allowed'] && (strpos($config['disallowed'], $path_parts['extension']) === true))
                  ) {
						      $error = true;
						      $errorForm[$key][$k] = 'error.extensionNotAllowed';
                } else {

  						    // create the directories if necessary
  						    $uploadfolder = $config['uploadfolder'] . ($row['addtouploadfolder'][$k] ? '/' . $row['addtouploadfolder'][$k] : '');

  						    $dirs = explode('/',$uploadfolder);
  						    $path = '.';
  						    foreach($dirs as $dir){ 
                    $path .= '/' . $dir;
  						      if (!is_dir($path)) {
                      if(!mkdir($path)) {
                        $error = true;
                        $errorForm[$key][$k] = 'error.mkdirIncorrect';
                      }
  							    }
  						    }

  						    // Move the file in the uploadfolder
  						    if (!move_uploaded_file($GLOBALS['_FILES'][$this->savlibrary->formName]['tmp_name'][$key][$k], $uploadfolder . '/' . $value)) {
                    $error = true;
  							    $errorForm[$key][$k] = 'error.uploadedIncorrect';
  						    }
  					    }
					    }
					  }
					}
				}
			}
			// Unset the hidden parameter with the additional path to the uploadfolder
			unset($row['addtouploadfolder']);
		}
		
		// Remove the fields or make special processing when special processing is needed
		$regularRow = $row;
		$special = array();

		foreach ($row as $field => $values) {

      // Get the configuration
      $config = $configTable[$field];

		  foreach ($values as $key => $value) {
		  
  			// Verification
  			// Check if the field is required 	
  			if ($config['required'] || $config['eval'] == 'required') {

          switch ($config['type']) {
            case 'select':
            case 'group': 
             if ($config['errorval'] == $value) {
  				      $error_field = true;
  				      $errorForm[$field][$key] = 'error.fieldIncorrectValue';
              }
              break;
            default:
              if ($value == '' || (isset($config['errorval']) && $config['errorval'] == $value)) {
  				      $error_field = true;
  				      $errorForm[$field][$key] = 'error.fieldVoid';
              }
          }
        } 
        // check if the max attribute is set
        if ($config['max']) {
          switch ($config['type']) {
            case 'input':
              if ($config['eval'] != 'datetime' && $config['eval'] != 'date') {
                // use the isValidLength verifier
                $config['verifier'] = 'isValidLength';
                $config['verifierparam'] = $config['max'];
              }
              break;
          }
        } 
   
        // Check if verifier is used
        $func = $config['verifier'];
        if ($func) {
          if (method_exists($this->savlibrary->verifiers, $func)) {
            $temp = $this->savlibrary->verifiers->$func($value, $config['verifierparam']);
            if (!$errorForm[$field] && $temp) {
              $errorForm[$field][$key] = $temp;
              $error_field = true;
            }
          } else {
            $error_field = true;
  				  $errorForm[$field][$key] = 'error.verifierUnknown';
          }      
        }        

        if ($error_field) {
          $error = true;
  				$error_field = false;
  				//remove the field
  				unset($regularRow[$field]);
  			}	else {
  				switch($config['type']) {
  				  case 'check' :
  				    if (is_array ($config['items'])) {
  				      // Several checkboxes => build one integer
  				      $pow = 1;
  				      $val = 0;
  				      foreach($value as $checked) {
                  if ($checked) {
                    $val += $pow;
                  }
                  $pow = $pow <<1;
                }
  				    unset($regularRow[$field]);
              $regularRow[$field][$key] = $val;		
              }	  
  				    break;
  					case 'schedule':
  						$line = '';

  						foreach($value as $day) {
  							$line .= current($day['beginAm']) . '-' .
                  current($day['endAm']) . '|' . current($day['beginPm']) . '-' .
                  current($day['endPm']) . ';';
  							$k = key($day['beginAm']);
  						}
  						$line = substr($line,0,-1);

  						//replace the field
  						$regularRow[$field][$k] = $line;
  						break;
  					case 'group':
  						switch ($config['internal_type']) {
  							case 'file':
  								break;
  						}
  						break;
  					case 'select':
              if ($config['foreign_table'] && $config['maxitems']>1) {
                if ($config['MM']) {
                  $special[] = array($field => $regularRow[$field]);
                  //remove the field
                  unset($regularRow[$field]);             
                } else {
                // Comma list field
                $regularRow[$field][$key] = implode(',', $value);
                }
              }            			
              break;
  					case 'input':
  						switch($config['eval']) {
  							case 'datetime':
  							case 'date':

  								$regularRow[$field][$key] = $this->savlibrary->date2timestamp($value , $config, $errorDate);	

                  if ($errorDate)	{
                    $errorForm[$field][$key] = $errorDate;
                    $error_field = true;
                    $error = true;
                  }					
  								break;
  						}
  						break;
  				}				
  			}
  		  // check if a mail should be sent. Get the previous data for the post-processing.	  
        if ($config['mail'] || isset($configMailAlways)) {
    		  // check if a mail should be sent
          if ($value || isset($configMailAlways)) {
            $func = trim($this->savlibrary->savlibraryConfig['queriers']['select']['showSingle']);
            $query = $this->extConfig['queries'][$this->savlibrary->formConfig['query']];
            if ($this->savlibrary->rowItemFromButton) {
              $query['addWhere'] = $config['table'] . '.uid=' . $key;
            }
        		$temp = $this->$func(
              $this->extConfig['queries'][$this->savlibrary->formConfig['query']],
              $uid
            );
            $dataset = current($temp);  		     
            if (is_array($dataset)) { 
              $previousData[$key] = $dataset; 
            } else {
              $previousData[$key] = array();
            }
          } 
        }       
	    }
		}

		// return errors if any
		if($error) {
			$this->savlibrary->errorInForm = true;
			return $errorForm;
		}

		// Process the regular rows. Explode the key to get the table and field names
		$vars = array();
		foreach($regularRow as $key => $value) {
		  foreach ($value as $k => $v) {
		    $updateVars[$configTable[$key]['table']][$k][$configTable[$key]['field']] = $v;
      }
		}		

    if ($updateVars) {
  		foreach ($updateVars as $table => $values) {
  		  foreach ($values as $uidWhere => $fields) {
  
         if ($uidWhere) {
          
            // update the fields
    			  if ($GLOBALS['TCA'][$table]['ctrl']['tstamp']) {
              $fields[$GLOBALS['TCA'][$table]['ctrl']['tstamp']] = time();
    				}

        		$res = $GLOBALS['TYPO3_DB']->exec_UPDATEquery(
        			/* TABLE   */	$table,		
        			/* WHERE   */	$table.'.uid=' . intval($uidWhere),
        			/* FIELDS  */	$fields
       			  );
		
    				$this->savlibrary->uid = $uid;
          
          } else {

            // Insert the fields in the storage page if any or in the current
            // page by default
            $fields['pid'] = (
              $this->savlibrary->conf['storagePage'] ?
              $this->savlibrary->conf['storagePage'] :
              $GLOBALS['TSFE']->id
            );
  				  // Controls
  				  if ($GLOBALS['TCA'][$table]['ctrl']['cruser_id']) {
  					  $fields[$GLOBALS['TCA'][$table]['ctrl']['cruser_id']] =
                $GLOBALS['TSFE']->fe_user->user['uid'];
  				  }
  				  if ($GLOBALS['TCA'][$table]['ctrl']['crdate']) {
  					  $fields[$GLOBALS['TCA'][$table]['ctrl']['crdate']] = time();
  				  }
  				  if ($GLOBALS['TCA'][$table]['ctrl']['tstamp']) {
  					  $fields[$GLOBALS['TCA'][$table]['ctrl']['tstamp']] = time();
  				  }

  					$res = $GLOBALS['TYPO3_DB']->exec_INSERTquery(
  						/* TABLE   */	$table,		
  						/* FIELDS  */	$fields
  					);
  			    $new_uid = $GLOBALS['TYPO3_DB']->sql_insert_id($res);
  			    
            if ($GLOBALS['TYPO3_DB']->sql_error($res)) {
              $this->savlibrary->addError('error.incorrectQueryInsertNew'); 
              return;   
            }
                               
            // Check if it is a relation table
            $parentField = $configTable[$table]['parentField'];
            if ($parentField) {
              $config = $configTable[$parentField];

              if ($config['type']=='group' && $config['internal_type']=='db' && !$config['norelation']) {
                if ($config['MM']) {
                  $MM_table = $config['MM'];
              
                  // Get the number of items in the MM table
    						  $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
  						      /* SELECT */  'count(*) AS nbitem, max(sorting) as maxsorting', 
  							    /* FROM   */	$MM_table,		
  							    /* WHERE  */	'uid_local=' . intval($uid)
    						  );
                  $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res); 
                  $maxSorting = $row['maxsorting'];           
                                 
                  // Insert the relation in the MM table
     						  $vars = array('uid_local' => $uid, 'uid_foreign' => $new_uid,'sorting' => (int) $maxSorting +1);
    						  $res = $GLOBALS['TYPO3_DB']->exec_INSERTquery(
    							  /* TABLE   */	$MM_table,		
    							  /* FIELDS  */	$vars
    						  );            
						  
                  // Update the local table field with the number of records
                  $vars = array($config['field'] => (int) $nbitem +1);
                  $res = $GLOBALS['TYPO3_DB']->exec_UPDATEquery(
    						    /* TABLE   */	$config['table'],		
    						    /* WHERE   */	$config['table'] . '.uid=' . intval($uid),
    						    /* FIELDS  */	$vars
                  );
               
                  $this->savlibrary->uid = $uid; 
                                        
                } elseif ($config['allowed']) {
                  // Update the relation
                  $vars = array($config['field'] => (int) $new_uid);
                  $res = $GLOBALS['TYPO3_DB']->exec_UPDATEquery(
    						    /* TABLE   */	$config['table'],		
    						    /* WHERE   */	$config['table'] . '.uid=' . intval($uid),
    						    /* FIELDS  */	$vars
                  );
          
                } else {
                  $this->savlibrary->uid = $new_uid;
                }
              }                      
            } else {
              $this->savlibrary->uid = $new_uid;          
            }         
          }
        }  
      }
    }
 
    // Post-Processing if any

  	// check if a mail (MailAlways) should be sent
    if (isset($configMailAlways)) {
      // Get the dataset
      $func = trim($this->savlibrary->savlibraryConfig['queriers']['select']['showSingle']);
      $query = $this->extConfig['queries'][$this->savlibrary->formConfig['query']];
      $temp = $this->$func($query, $this->savlibrary->uid);
      $dataset = current($temp);  

      $ta = $this->savlibrary->generateFormTa(
        'items',
        $dataset,
        $this->extConfig['views'][$this->savlibrary->formConfig['updateForm']],
        $errors,
        0
      );
      $mA = array();
      foreach ($ta['REGIONS']['items'] as $item) {
        $mA['###' . $item['MARKERS']['field'] . '###'] =
          $item['MARKERS'][$item['MARKERS']['field']];
      }

      $item = array();
      $mA['###ITEMS_AUTO###'] = '';
		  foreach($regularRow as $keyField => $valueField) {
		    $fieldName = $configTable[$keyField]['field'];
        $item['###mailalways_field###'] = $configTable[$keyField]['fullFieldName'];
        $item['###mailalways_value###'] = $mA['###' . $fieldName . '###'];
        $mA['###ITEMS_AUTO###'] = $mA['###ITEMS_AUTO###'] .
          $this->cObj->substituteMarkerArrayCached(
            $configMailAlways['mailalwaysitemtmpl'],
            $item,
            array(),
            array()
          );
      }
      
      // Get the new items (posted as New_) 
      $newPOSTVars = t3lib_div::_POST('New_' . $this->savlibrary->formName);

      unset($dataset);

		  foreach($newPOSTVars as $keyField => $valueField) { 
		    $value = current($valueField);
        // process special type
        if ($configTable[$keyField]['type'] == 'input' && ($configTable[$keyField]['eval'] == 'date' || $configTable[$keyField]['eval'] == 'datetime')) {
          $value = $this->savlibrary->date2timestamp(
            $value ,
            $configTable[$keyField],
            $errorDate
          );
        }         
        $dataset[$configTable[$keyField]['fullFieldName']] = $value;
      }   

      $ta = $this->savlibrary->generateFormTa('items', $dataset, $this->extConfig['views'][$this->savlibrary->formConfig['updateForm']], $errors, 0);
      foreach ($ta['REGIONS']['items'] as $item) {
        if (array_key_exists($item['MARKERS'][$item['MARKERS']['field'] . '_cryptedFieldName'], $newPOSTVars)) {
          $keyCryptedField = $item['MARKERS'][$item['MARKERS']['field'] . '_cryptedFieldName'];
          $keyFullField = $item['MARKERS'][$item['MARKERS']['field'] . '_fullFieldName'];
          if($dataset[$keyFullField] && $dataset[$keyFullField] != $configTable[$keyCryptedField]['default']) {
            $x['###mailalways_field###'] = $keyFullField;
            $x['###mailalways_value###'] = $item['MARKERS'][$item['MARKERS']['field']];
            $temp[$keyCryptedField] =
              $this->cObj->substituteMarkerArrayCached(
                $configMailAlways['mailalwaysitemtmpl'],
                $x,
                array(),
                array()
              );
          }  
        }
      }

      // Put it in the order of the post
      $mA['###ITEMS_MANUAL###'] = '';
      foreach($newPOSTVars as $keyField => $valueField) {  
        $mA['###ITEMS_MANUAL###'] = $mA['###ITEMS_MANUAL###'] . $temp[$keyField];
      }        

      $this->sendEmail($configMailAlways, $mA);
    }

		foreach($regularRow as $keyField => $valueField) {
  		// check if a mail should be sent		
      if (($configTable[$keyField]['mail'] && (
            ($this->savlibrary->formAction=='updateBtn' && $keyField == $this->savlibrary->fieldFromButton) ||
            $configTable[$keyField]['mailauto'] ||
            $configTable[$keyField]['mailiffieldsetto']
            ))) {

        $mailSent = 0;
  			foreach($valueField as $key => $value) {
          // Get the dataset
          $func = trim($this->savlibrary->savlibraryConfig['queriers']['select']['showSingle']);
          $query = $this->extConfig['queries'][$this->savlibrary->formConfig['query']];
          if ($this->savlibrary->rowItemFromButton) {
            $query['addWhere'] = $configTable[$keyField]['table'] . '.uid=' .
              intval($this->savlibrary->rowItemFromButton);
          }
          $temp = $this->$func($query, $this->savlibrary->uid);
          $dataset = current($temp);  
      
          // Remove timestamp
          unset($previousData[$key][$configTable[$keyField]['table'] . '.tstamp']);
          unset($dataset[$configTable[$keyField]['table'] . '.tstamp']);
          
          // Check if mail must be sent 			
          $mailToSend = 0; 
          if ($configTable[$keyField]['mailauto']) {
            if ($value && $previousData[$key] != $dataset) {  			
              $mailToSend = 1;
            }
          } elseif ($configTable[$keyField]['mailiffieldsetto']) {
            // Mail is sent if the field was previsously null and is set to the config value
            if (!$previousData[$key][$keyField] && $value == $configTable[$keyField]['mailiffieldsetto']) {
              $mailToSend = 1;
            }           
          } else { 
            if (!$value && ($this->savlibrary->rowItemFromButton ? ($this->savlibrary->rowItemFromButton == $key) : 1) ) {
              $mailToSend = 1;
            }
          }

          if ($mailToSend) {
          
            // Check if a language configuration is set for the message
            if ($configTable[$keyField]['mailmessagelanguagefromfield']) {            
              $configTable[$keyField]['mailmessagelanguage'] =
                $regularRow[$configTable[$keyField]['table'] . '.' .
                $configTable[$keyField]['mailmessagelanguagefromfield']][$key];
            } 
            if ($configTable[$keyField]['mailmessagelanguage']) {
              $lang = $GLOBALS['TSFE']->lang;
              $GLOBALS['TSFE']->lang = $configTable[$keyField]['mailmessagelanguage'];     
            }     

            $ta = $this->savlibrary->generateFormTa(
              'items',
              $dataset,
              $this->extConfig['views'][$this->savlibrary->formConfig[$this->savlibrary->viewName]],
              $errors,
              0
            );

            // Reset language if a language configuration is set for the message         
            if ($configTable[$keyField]['mailmessagelanguage']) {
              $GLOBALS['TSFE']->lang = $lang;
              // unset the cached language file which is obtained through the title field
              $title = $GLOBALS['TCA'][$configTable[$keyField]['table']]['ctrl']['title'];
              if (preg_match('/EXT:[^:]+/', $title, $match)) {
                unset($GLOBALS['TSFE']->LL_files_cache[$match[0]]);            
              }
            }          

            $this->savlibrary->titleProcessed = false;

            foreach ($ta['REGIONS']['items'] as $item) {
              $mA['###'.$item['MARKERS']['field'].'###'] = (
                $this->savlibrary->viewName == 'inputForm' ?
                $item['MARKERS']['Value'] :
                $item['MARKERS'][$item['MARKERS']['field']]
              );
            }

            // Set the mail reveiver from field if any
            if (isset($configTable[$keyField]['mailreceiverfromfield'])) {
              $configTable[$keyField]['mailreceiver'] =
                $this->savlibrary->getValue(
                  $configTable[$keyField]['table'],
                  $configTable[$keyField]['mailreceiverfromfield'],
                  $dataset
                );
            }

            $mailSent = $this->sendEmail($configTable[$keyField], $mA);

            // update the mail Flag
            if (!$configTable[$keyField]['mailauto'] && !$configTable[$keyField]['mailiffieldsetto']) {
              $res = $GLOBALS['TYPO3_DB']->exec_UPDATEquery(
            		/* TABLE   */	$configTable[$keyField]['table'],		
            		/* WHERE   */	$configTable[$keyField]['table'] . '.uid=' . intval($this->savlibrary->rowItemFromButton ? $this->savlibrary->rowItemFromButton : $this->savlibrary->uid),
            		/* FIELDS  */	array($configTable[$keyField]['fullFieldName'] => $mailSent)
           		);
           	}
          }
        } 
      } 

      // Check if a query should be executed
      if ($configTable[$keyField]['query']) {
        $fieldQuery = $configTable[$keyField]['query']; 

  			foreach($valueField as $key => $value) {

          // Check if the use of the query property is allowed
          if (!$this->savlibrary->conf['allowQueryProperty']) {
            $error = true;
            $errorForm[$keyField][$key] = 'error.queryPropertyNotAllowed';
            continue;
          }
        
          if (!$configTable[$keyField]['queryonvalue'] || ($configTable[$keyField]['queryonvalue'] == $value)) {
            $mA["###uid###"] = $this->savlibrary->uid;
            $mA["###CURRENT_PID###"] = $GLOBALS['TSFE']->page['uid'];
            $mA["###value###"] = $val;
            $mA["###user###"] = $GLOBALS['TSFE']->fe_user->user['uid'];
            if ($configTable[$keyField]['queryforeach']) {
              // Get the table name and the field
              if(strpos($configTable[$keyField]['queryforeach'], '.') === false) {
                $foreachField = $this->savlibrary->cryptTag(
                  $this->savlibrary->tableLocal . '.' . $configTable[$keyField]['queryforeach']
                );
              }

              $foreachValues = explode(',', current($regularRow[$foreachField]));
              foreach($foreachValues as $foreachValue) {
                $mA['###' . $configTable[$keyField]['queryforeach'] . '###'] = $foreachValue;
                $fieldQueryTemp = $this->cObj->substituteMarkerArrayCached(
                  $fieldQuery,
                  $mA,
                  array(),
                  array()
                );
                $queryStrings = explode(';', $fieldQueryTemp);
                foreach($queryStrings as $queryString) {

                  $res = $GLOBALS['TYPO3_DB']->sql_query($queryString);
                  if ($GLOBALS['TYPO3_DB']->sql_error($res)) {
                    $error = true;
                    $errorForm[$keyField][$key] = 'error.incorrectQueryInQueryProperty';
                    break;
                  }
                }
              }
            } else {
              $fieldQueryTemp = $this->cObj->substituteMarkerArrayCached(
                $fieldQuery,
                $mA,
                array(),
                array()
              );
              $queryStrings = explode(';', $fieldQueryTemp);
              foreach($queryStrings as $queryString) {
                $res = $GLOBALS['TYPO3_DB']->sql_query($queryString);
                if ($GLOBALS['TYPO3_DB']->sql_error($res)) {
                  $error = true;
                  $errorForm[$keyField][$key] = 'error.incorrectQueryInQueryProperty';
                  break;
                }
              }
            }
          }
        }
  		}
    }
    
		// Special processing
		foreach ($special as $no => $field) {
		  $fieldName = key($field);
		  $fieldValues = current($field);
		  $config = $configTable[$fieldName];

			$table = $config['table'];
      
      foreach ($fieldValues as $key => $values) {

  			switch($config['type']) {
  				case 'select':
  				  if($config['MM']) {
  				    // the table has multiple links
              $foreign_table = $config['foreign_table'];
              $MM_table = $config['MM'];
 
               // Get the uid
              $temp = current($field);
              $uid = $key;           

              // Delete the previous records in the MM table if $uid exists
              if ($key) {
  						  $res = $GLOBALS['TYPO3_DB']->exec_DELETEquery(
                  /* TABLE   */	$MM_table,		
                  /* WHERE   */	$MM_table . '.uid_local=' . intval($uid)
                );
  						  $new_uid_special = (
                  $config['mm_field'] == 'cruser_id' ?
                  $GLOBALS['TSFE']->fe_user->user['uid'] :
                  $uid
                );
              } elseif (!$this->savlibrary->uid) {
                // Insert the fields in the storage page if any or in the current
                // page by default
                $fields['pid'] = (
                  $this->savlibrary->conf['storagePage'] ?
                  $this->savlibrary->conf['storagePage'] :
                  $GLOBALS['TSFE']->id
                );
     				  // Controls
      				  if ($GLOBALS['TCA'][$table]['ctrl']['cruser_id']) {
      					  $fields[$GLOBALS['TCA'][$table]['ctrl']['cruser_id']] =
                    $GLOBALS['TSFE']->fe_user->user['uid'];
      				  }
      				  if ($GLOBALS['TCA'][$table]['ctrl']['crdate']) {
      					  $fields[$GLOBALS['TCA'][$table]['ctrl']['crdate']] = time();
      				  }
      				  if ($GLOBALS['TCA'][$table]['ctrl']['tstamp']) {
      					  $fields[$GLOBALS['TCA'][$table]['ctrl']['tstamp']] = time();
      				  }

      					$res = $GLOBALS['TYPO3_DB']->exec_INSERTquery(
      						/* TABLE   */	$table,		
      						/* FIELDS  */	$fields
      					);
                $new_uid = $GLOBALS['TYPO3_DB']->sql_insert_id($res);
                $this->savlibrary->uid = $new_uid;          
      			    $new_uid_special = (
                  $config['mm_field'] == 'cruser_id' ?
                  $GLOBALS['TSFE']->fe_user->user['uid'] :
                  $new_uid
                );
      			  } elseif ($new_uid && $new_uid!=$this->savlibrary->uid) {
      			    $new_uid_special = (
                  $config['mm_field'] == 'cruser_id' ?
                  $GLOBALS['TSFE']->fe_user->user['uid'] :
                  $new_uid
                );
              } else {
                $new_uid_special = $new_uid; // Case where a MM table is associated with a record created a regular
              }

              // Insert the new records

              // Check if items are in the table. This may happen in special cases when a new record is created and items are already in the table
              // For example, fe_user associated with the current user. In that case, the new records have to be added and, therefore, the position 
              // should take into account existing items.
    					$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
  						  /* SELECT */  'max(sorting) as max_sorting', 
  							/* FROM   */	$MM_table,		
  							/* WHERE  */	'uid_local=' . intval($new_uid_special)
    					);   
              $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res); 
              $initPos = ($row['max_sorting'] ? $row['max_sorting'] : 0);            

              foreach ($values as $pos => $value) {            
                if ($value) {
  						    unset($vars);
  						    $vars = array(
                    'uid_local' => $new_uid_special,
                    'uid_foreign' => $value,
                    'sorting' => (int) $initPos + $pos + 1
                  );
  						    
  						    // Because no primary key is used, check if the record exists
    						  $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
  						      /* SELECT */  '*', 
  							    /* FROM   */	$MM_table,		
  							    /* WHERE  */	'uid_local=' . intval($new_uid_special) .
                      ' AND uid_foreign=' . intval($value)
    						  );
                  $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res); 

                  if (!$row) {					    			    
   						     $res = $GLOBALS['TYPO3_DB']->exec_INSERTquery(
    							   /* TABLE   */	$MM_table,		
    							   /* FIELDS  */	$vars
    						    );
    						  }
                }
              }
              // The field is modified with the number of records
              if (!$value) {
                $pos = -1;
              }
              $vars = array($config['field'] => (int) $pos + 1);

              $res = $GLOBALS['TYPO3_DB']->exec_UPDATEquery(
  						  /* TABLE   */	$table,		
  						  /* WHERE   */	$table . '.uid=' . intval($uid ? $uid : $new_uid),
  						  /* FIELDS  */	$vars
              );
  					}
  				break;
  			}	
      }									
		}

		// return errors if any
		if($error) {
			$this->savlibrary->errorInForm = true;
			return $errorForm;
		} else {
			return false;
		}	
	}


   /***************************************************************
    *
    *   Utils
    *
   ***************************************************************/

	/**
	 * Get allowed Pages from the starting point and the storage page
	 *
   * @param $table string (table name)
 	 *
	 * @return string
	 */
  public function getAllowedPages($table) {
    if (!$table) {
      return '';
    } else {
      // Add the starting point pages
      if ($this->cObj->data['pages']) {
        $pageListArray = explode(',', $this->cObj->data['pages']);
      } else {
        $pageListArray = array();
      }
      // Add the storage page
      if ($this->savlibrary->conf['storagePage']) {
        $pageListArray[] = $this->savlibrary->conf['storagePage'];
      }

      $pageList = implode(',', $pageListArray);
    
   		return ($pageList ? ' AND ' . $table . '.pid IN (' . $pageList . ')' : '');
    }
  }

	/**
	 * Send an email.
	 *
   * @param $config array (configuration array containing email parameters)
	 * @param $mA array (Marker array)
 	 *
	 * @return bool(result)
	 */  
  public function sendEmail($config, &$mA) {

    $mailSender = $this->cObj->substituteMarkerArrayCached(
      $config['mailsender'],
      array('###user_email###' => $GLOBALS['TSFE']->fe_user->user['email']),
      array(),
      array()
    );

    if ($mailReceiverFromQuery = $config['mailreceiverfromquery']) {
      $this->savlibrary->addSpecialMarkersToArray($mA);
      $mailReceiverFromQuery =
        $this->cObj->substituteMarkerArrayCached(
          $mailReceiverFromQuery,
          $mA,
          array(),
          array()
        );

      // Check if the query is a SELECT query and for errors
      if (!$this->savlibrary->isSelectQuery($mailReceiverFromQuery)) {
        $this->savlibrary->addError('error.onlySelectQueryAllowed', $config['field']);
        return false;
      } elseif (!($res = $GLOBALS['TYPO3_DB']->sql_query($mailReceiverFromQuery))) {
        $this->savlibrary->addError('error.incorrectQueryInContent', $config['field']);
        return false;
      }

        // Process the query
      $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);

      foreach ($row as $k => $v) {
        $mA['###'.$k.'###'] = $v;
      }
      $mailReceiver = $row['email'];
    } 
            
    if ($config['mailreceiver']) {
      $mailReceiver = $config['mailreceiver'];
    } 

    
    // Check if a language configuration is set for the message
    if ($config['mailmessagelanguage']) {

      // Save the current language key
      $LLkey = $this->savlibrary->extObj->LLkey;
        
      // Load the new LOCAL_LANG
      $this->savlibrary->extObj->LLkey = $config['mailmessagelanguage'];
      $this->savlibrary->extObj->LOCAL_LANG_loaded = false;
      $this->savlibrary->extObj->pi_loadLL();
    }          

    // Check if the message contains localization variable,
    //that is tags in the locallang.xml file. Tags are defined as §§§tag§§§.
    $mailMessage = $config['mailmessage'];
    if (preg_match_all('/\$\$\$([^§]+)\$\$\$/', $mailMessage, $matches)) {
        
      foreach ($matches[1] as $key => $match) {
        $message = $this->savlibrary->getExtLL($match);
        if ($message) {
          $mailMessage = str_replace($matches[0][$key], $message, $mailMessage);
        }
      }      
    }

    // Check if the subject contains localization variable, that is tags in
    // the locallang.xml file. Tags are defined as §§§tag§§§.
    $mailSubject = $config['mailsubject'];
    if (preg_match_all('/\$\$\$([^§]+)\$\$\$/', $mailSubject, $matches)) {
        
      foreach ($matches[1] as $key => $match) {
        $message = $this->savlibrary->getExtLL($match);
        if ($message) {
          $mailSubject = str_replace($matches[0][$key], $message, $mailSubject);
        }
      }      
    }
    
    // Reset language          
    if ($config['mailmessagelanguage']) {
      $this->savlibrary->extObj->LLkey = $LLkey;
      $this->savlibrary->extObj->LOCAL_LANG_loaded = false;
      $this->savlibrary->extObj->pi_loadLL();
    }     
    
    // Add markers       
    $mA['###linkToPage###'] = str_replace(
      '<a href="',
      '<a href="' . t3lib_div::getIndpEnv('TYPO3_SITE_URL'),
      $this->savlibrary->extObj->pi_linkToPage('', $GLOBALS['TSFE']->id)
    );

    $mailMessage = $this->cObj->substituteMarkerArrayCached(
      nl2br($mailMessage),
      $mA,
      array(),
      array()
    );
    $mailSubject = mb_encode_mimeheader(
      $this->cObj->substituteMarkerArrayCached(
        $mailSubject,
        $mA,
        array(),
        array()
      ),
      'iso-8859-1',
      'Q'
    );
    $headers  = 'MIME-Version: 1.0' . "\r\n";
    $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
    $headers .= 'From: ' . $mailSender . "\r\n";
    $headers .= 'Reply-To: ' . $mailSender . "\r\n";
    $headers .= 'Return-Path: ' . $mailSender . "\r\n";
    if ($config['mailcc']) {
      $headers .= 'Cc: ' . $config['mailcc'] . "\r\n";
    } 

    if (!ini_get('safe_mode')) {
			// If safe mode is on, the fifth parameter to mail is not allowed,
      // so the fix wont work on unix with safe_mode=On
      return @mail(
        $mailReceiver,
        $mailSubject,
        $mailMessage,
        ($config['mailsender'] ? $headers : ''),
        ($config['mailsender'] ? '-f'.$mailSender : '')
      );
    } else {
      return @mail(
        $mailReceiver,
        $mailSubject,
        $mailMessage,
        ($config['mailsender'] ? $headers : '')
      );
    }
  }

  
	/**
	 * Process ###field### tags.
	 *
   * @param $x string (string to process)
	 * @param $row array (data used to replace the tags)
	 * @param $config array (configuration array)
 	 *
	 * @return string (result)
	 */  
  public function processFieldTags($x, &$row, &$config = array()) {

    $x = preg_replace('/(###[^\r\n#]*)[\r\n]*([^#]*###)/m', '$1$2' ,$x);
    preg_match_all('/###(([^\.#]+)\.?([^#]*))###/', $x, $matches);

    $mA = array();
    foreach($matches[1] as $key => $match) {

      $tag = $matches[1][$key];

      // Clean the tag 
      $tag = preg_replace('/\\\\[^ ]+ /','' ,$tag);

      // If the crypted tag is in the configuration, get the replacement string.
      // Otherwise, replace NL by NL+\\par
      if ($config[$this->savlibrary->cryptTag($tag)]) {
        $temp = explode('->', $config[$this->savlibrary->cryptTag($tag)]);
        switch(trim($temp[0])) {
          case 'NL':
            $search = chr(10);
            break;
          default:
            $search = $temp[0];
        }
        $replace = $temp[1];
      } else {
        $search = chr(10);
        $replace = chr(10) . '\\par ';
      }

      if ($matches[3][$key]) {
        $value = html_entity_decode(
          stripslashes($row[$this->replaceTableNames(trim($tag))]),
          ENT_QUOTES
        );

        if ($config['generatertf']) {
          $value = str_replace($search, $replace, $value);
        }
        $mA[$matches[0][$key]] = $value;
      } else {
        // It's an alias
        $value = html_entity_decode(stripslashes($row[trim($tag)]), ENT_QUOTES);
        if ($config['generatertf']) {
          $value = str_replace($search, $replace, $value);
        }
        $mA[$matches[0][$key]] = $value;
      }
    } 

    return $this->cObj->substituteMarkerArrayCached(
      $x,
      $mA,
      array(),
      array()
    );
  }

	/**
	 * Replace table names by their alias
	 *
   * @param $x string (string to process)
 	 *
	 * @return string (result)
	 */  
  public function replaceTableNames($x) {  

    preg_match_all('/([^(\. =0-9]+)([0-9]*)\./', $x, $matches);

    if ($matches[1]) {
      foreach($matches[1] as $key=>$match) {
        if ($matches[2][$key]) {
          if ($this->aliasTable[$match.$matches[2][$key]]) {
            $x = str_replace(
              $matches[0][$key],
              $this->aliasTable[$match . $matches[2][$key]] . '.',
              $x
            );
          }        
        }
      }
    }

    return $x;
  }

  
	/**
	 * Read rows and return an array with the tablenames
	 *
   * @param $res integer (mySQL ressource)
	 * @param $cpt integer (item counter)
 	 *
	 * @return string (result)
	 */    
  public function sql_fetch_assoc_with_tablename($res, $cpt=0) { 

    $result = array();
		$row = $GLOBALS['TYPO3_DB']->sql_fetch_row($res);
    if ($row) {
  		foreach($row as $key => $value) {
  	    if (!$cpt) {
    		  $this->sqlFields[$key] = mysql_fetch_field($res, $key);
        }
        
        $field = $this->sqlFields[$key];
        if ($field->table) {	  
    		  $result[$field->table . '.' . $field->name] = $value;
    		} else {
    		  $result[$field->name] = $value;
        }
      } 
    return $result;
    } else {
      return false;
    }
  }  

       
	/**
	 * Process tags in whereselect clause
	 *
   * @param $where string (string to process)
 	 *
	 * @return string (result)
	 */ 
  public function processWhereClause($where) {  

    // Replace tags in the where clause if any
    $where = str_replace('###user###', $GLOBALS['TSFE']->fe_user->user['uid'], $where);
    $where = str_replace('###uid###', $this->savlibrary->uid, $where);
    $temp = $GLOBALS['TSFE']->getStorageSiterootPids();
    $where = str_replace('###STORAGE_PID###', $temp['_STORAGE_PID'], $where);
    $where = str_replace('###CURRENT_PID###', $GLOBALS['TSFE']->page['uid'], $where);

    if (preg_match_all('/###group_list[ ]*([!]?)=([^#]*)###/', $where, $matches)) {
      foreach ($matches[2] as $key => $match) {
        $groups = explode (',', str_replace(' ', '', $match)); 
        $clause = '';       
        // Get the group list of uid
        $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
				    /* SELECT   */	'uid,title',	
				    /* FROM     */	'fe_groups',
	 			    /* WHERE    */	'1' .
              $this->cObj->enableFields('fe_groups')
		    );
        while ($rows = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
          if (in_array($rows['title'], $groups)) {
            if ($matches[1][$key]=='!') {
              $clause .= ' AND find_in_set(' . $rows['uid'] . ', usergroup)=0';
            } else {
              $clause .= ' OR find_in_set(' . $rows['uid'] . ', usergroup)>0';
            }        
          }     
        }
    
        // Replace the tag
        if ($matches[1][$key]=='!') {
          $where = preg_replace(
            '/###group_list[ ]*!=([^#]*)###/',
            '(1' . $clause . ')',
            $where
          );
        } else {
          $where = preg_replace(
            '/###group_list[ ]*=([^#]*)###/',
            '(0' . $clause . ')',
            $where
          );
        }
      }
    }

    // Process conditionnal part
    if (preg_match_all('/###([^:]+):([^#]+)###/', $where, $matches)) {

      foreach ($matches[1] as $key => $match) {
        $replace = '1';
        preg_match('/([^\(]+)(?:\(([^\)]*)\)){0,1}/', $match, $matchFunc);
        
        $func = $matchFunc[1];
        if ($func && method_exists($this->savlibrary, $func)) {
          // Check if there is one parameter
          if ($matchFunc[2]) {
            if ($this->savlibrary->$func($matchFunc[2])) {
              $replace .= ' AND ' . $matches[2][$key];
            }          
          } else {
            if ($this->savlibrary->$func()) {
              $replace .= ' AND ' . $matches[2][$key];
            }
          }
        } else {
          $this->savlibrary->addError(
            'error.unknownFunctionInWhere',
            $matchFunc[1]
          );
        }       

        $where = preg_replace('/###[^:]+:[^#]+###/', $replace, $where);
      }

    }

    return $where;
  }

	/**
	 * Build the configuration table from the view configuration
	 *
   * @param $viewConfiguration array (view configuration)
 	 *
	 * @return array (Configuration table)
	 */  
  protected function buildConfigurationTable($viewConfiguration) {
    // Buid the configuration table
    $configTable = array();

    foreach ($viewConfiguration as $field => $config) {
      $fieldName = $this->savlibrary->cryptTag($config['config']['table'] . '.' . $config['config']['field']);
      $configTable[$fieldName] = $this->savlibrary->getConfig($config);
      $configTable[$config['config']['table']]['parentField'] = '';
      
      if (is_array($config['config'][$this->savlibrary->cryptTag('0')])) {
        // A subform is produced by a MM table
        unset($configTable[$fieldName][$this->savlibrary->cryptTag('0')]);

        foreach ($config['config'][$this->savlibrary->cryptTag('0')]['fields'] as $subitemField => $subitemConfig) {
          $subitemFieldName = $this->savlibrary->cryptTag($subitemConfig['config']['table'] . '.' . $subitemConfig['config']['field']);
          $configTable[$subitemFieldName] = $this->savlibrary->getConfig($subitemConfig);
          $configTable[$subitemConfig['config']['table']]['parentField'] = $fieldName;
        }
      }   
    }

    return $configTable;
  }

	/**
	 * Number to alias
	 *
   * @param $number int (Number to convert)
 	 *
	 * @return string (Alias A to Z then AA to AZ then BA to BZ and so on)
	 */
  public function numberToAlias($number) {
    $quotient = (int) (($number-1)/26);
    $firstLetter = ($quotient ? chr($quotient + 64) : '');
    $secondLetter = chr(($number-1)%26 + 65);

    return $firstLetter . $secondLetter;
  }

	/**
	 * Alias to number
	 *
   * @param $alias string (alias to convert A ..Z, AA..ZZ)
 	 *
	 * @return int (1 to 702)
	 */
  public function aliasToNumber($alias) {
    switch(strlen($alias)) {
      case 0:
        $value = 0;
        break;
      case 1:
        $value = ord($alias) - 64;
        break;
      case 2:
        $value = (ord($alias[0]) - 64) * 26 + ord($alias[1]) - 64;
        break;
      default:
        $value = '';
        $this->savlibrary->addError('error.incorrectAlias', $alias);
    }

    return $value;
  }
  
	/**
	 * Build the aliases for tables
	 *
   * @param $tableName string (table name)
 	 *
	 * @return array (result)
	 */  
  public function buidAliasTable($tableName) {
    $nbTable = $this->refTable[$tableName];
    $this->refTable[$tableName] = $this->refTable[$tableName] + 1;

    if ($nbTable) {
      $alias = end($this->aliasTable);
      $this->aliasTable[$tableName . $nbTable] =
        $this->numberToAlias($this->aliasToNumber($alias) + 1);
      return array(
        'def' => $tableName . ' AS ' . $this->aliasTable[$tableName . $nbTable],
        'table' => $this->aliasTable[$tableName . $nbTable]
      );
    }
    return array('def' => $tableName, 'table' => $tableName);
  }


	/**
	 * Build the table reference
	 *
   * @param $query array (query array)
	 * @param $addTables string (additional tables)
 	 *
	 * @return string (the tables with their left join fields )
	 */  
  public function buidTableReference(&$query, $addTables = '') {

    $this->aliasTable = array();
    $tableName = $query['tableLocal'];
    $this->buidAliasTable($tableName);
    
		t3lib_div::loadTCA($tableName);    
    $TCA = $GLOBALS['TCA'][$tableName]['columns'];

    if (isset($TCA)) {
      foreach ($TCA as $field => $descr) {
        $TCA[$field]['tableLocal'] = $tableName;
        if ($descr['config']['type'] == 'group' && $descr['config']['internal_type'] == 'db') {
          t3lib_div::loadTCA($descr['config']['allowed']);
          $temp = $GLOBALS['TCA'][$descr['config']['allowed']]['columns'];
          if (is_array($temp)) {
            foreach ($temp as $fieldTemp => $descrTemp) {
              $temp[$fieldTemp]['tableLocal'] = $descr['config']['allowed'];        
            }
            $TCA += $temp;
          }
        }
      }
    } else {
      die($this->savlibrary->getLibraryLL('fatal.incorrectTCA'));
    }
		t3lib_div::loadTCA('fe_users');

    // Add the columns for existing tables
    if (isset($this->extConfig['TCA'][$tableName])) {
      $temp = $this->extConfig['TCA'][$tableName];
      foreach ($temp as $fieldTemp => $descrTemp) {
        $temp[$fieldTemp]['tableLocal'] = $tableName;        
      }     
      $TCA += $temp;
    }
    $tableReference = $tableName;

    $tableArray = array();
    // Build the reference       
    foreach ($TCA as $field => $descr) {
      $config = $descr['config'];

      if ($config['type'] == 'group' && $config['internal_type'] == 'db' && !$config['norelation']) {
        if ($config['MM']) {
          // MM table
          $alias1 = $this->buidAliasTable($config['MM']);          
          $alias2 = $this->buidAliasTable($config['allowed']);          

          $tableReference .= ' LEFT JOIN ' . $alias1['def'] .
            ' ON (' . $alias1['table'] . '.uid_local=' . $descr['tableLocal'] . '.uid) LEFT JOIN ' . $alias2['def'] . ' ON (' . $alias1['table'] . '.uid_foreign=' . $alias2['table'] . '.uid)';
        } else {
          $alias1 = $this->buidAliasTable($config['allowed']);          

          $tableReference .= ' LEFT JOIN ' . $alias1['def'] .
          ' ON (' . $alias1['table'] . '.uid=' . $descr['tableLocal'] . '.' . $field . ')';
        }
      }
      if ($config['type'] == 'select') {
        if ($config['MM']) {
          // MM table
          $alias1 = $this->buidAliasTable($config['MM']);          
          $alias2 = $this->buidAliasTable($config['foreign_table']);                    

          $tableReference .= ' LEFT JOIN ' . $alias1['def'] .
            ' ON (' . $alias1['table'] . '.uid_local=' . $descr['tableLocal'] . '.uid) LEFT JOIN ' . $alias2['def'] . ' ON (' . $alias1['table'] . '.uid_foreign=' . $alias2['table'] . '.uid)';
        } elseif ($config['foreign_table']) {
          $alias1 = $this->buidAliasTable($config['foreign_table']);          

          $tableReference .= ' LEFT JOIN ' . $alias1['def'] .
            ' ON (' . $alias1['table'] . '.uid=' . $descr['tableLocal'] . '.' . $field . ')';
          
          // Check if a link is defined
          $extendLink = $this->extConfig['views'][$this->savlibrary->formConfig[$this->savlibrary->viewName]][$this->savlibrary->folderTab]['fields'][$this->savlibrary->cryptTag($tableName . '.' . $field)]['config']['setextendlink'];
          if ($extendLink) {
            $alias2 = $this->buidAliasTable($extendLink);
            $tableReference .= ' LEFT JOIN ' . $alias2['def'] .
              ' ON (' . $alias1['table'] . '.' . $extendLink . '=' . $alias2['table'] . '.uid)';
          }
                  
        }
      }
    }

    // Add the foreign table
      // Check that the 'tableForeign' start either by LEFT JOIN, INNER JOIN or RIGHT JOIN or a comma
    if ($query['tableForeign']) {
      if (!preg_match('/^[\s]*(?i)(,|inner join|left join|right join)[\s]*/', $query['tableForeign'])) {
        $this->savlibrary->addError('error.incorrectQueryForeignTable');
      } else {
        $tableReference .= ' ' . $query['tableForeign'];
      }
    }

    // Check for duplicate table names with addTables
    $temp = explode(',', $addTables);
    $addTablesArray = array();
    foreach ($temp as $key => $table) {
      if($table && !in_array($table, $addTablesArray) && !array_key_exists($table, $this->refTable)) {
        $addTablesArray[] = $table;
      }
    }
    $addTables = implode(',', $addTablesArray);

    return $tableReference . ($addTables ? ', ' . $addTables : '');
  }

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/sav_library/class.tx_savlibrary_defaultQueriers.php']) {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/sav_library/class.tx_savlibrary_defaultQueriers.php']);
}

?>
