<?php

	// Some functions to make accessing the Query Manager easier
	/**
	 * @return query
	 * @param theQuery string
	 * @desc Run a SQL query on the database
	 */
	function query($theQuery,$subSelectQueries = array(),$connection = 'sql') {
		return $GLOBALS[$connection]->query($theQuery,$subSelectQueries);
	}	
	function startTransaction($connection = 'sql') {
		return $GLOBALS[$connection]->startTransaction();
	}
	function commit($connection = 'sql') {
		return $GLOBALS[$connection]->commit();
	}
	function rollback($connection = 'sql') {
		return $GLOBALS[$connection]->rollback();
	}
	function safe($string,$connection = 'sql') {
		return $GLOBALS[$connection]->safe($string);
	}	
	function escape($string,$connection = 'sql') {
		return addSlashes($string);
	}
	function escapeLike($string,$connection = 'sql') {
		return $GLOBALS[$connection]->escapeLike($string);
	}
	function getRow($query,$subSelectQueries = array(),$connection = 'sql') {
        $Q_Result = query($query,$subSelectQueries,$connection);
		$res = $Q_Result->fetchRow();
		$Q_Result->free();
		return $res;
	}
	function getField($query,$subSelectQueries = array(),$connection = 'sql') {
        $Q_Result = query($query,$subSelectQueries,$connection);
		$res = $Q_Result->fetchRow();
		$Q_Result->free();
		if (is_array($res) and count($res)) {
			return array_pop($res);
		} else {
			return null;
		}
	}
	function newPrimaryKeyWithMin($table,$field,$min,$connection = 'sql',$where = '1=1') {
		
		$row = getRow("SELECT MAX(".$field.") AS Max_ID FROM ".$table." WHERE $where",array(),$connection);
		if (!strlen($row['Max_ID'])) {
		 	return $min;
		} else {
			if ($row['Max_ID'] < $min) {
				return $min;
			} else {						
				return $row['Max_ID']+1;
			}
		}
	}
			
	function newPrimaryKey($table,$field,$start = 1,$connection = 'sql',$where = '1=1') {
		
		$row = getRow("SELECT MAX(".$field.") AS Max_ID FROM ".$table." WHERE $where",array(),$connection);
		if (!strlen($row['Max_ID'])) {
		 	return $start;
		} else {
			return $row['Max_ID']+1;
		}
	}
	
	function newSortOrder($table,$sortfield,$groupField,$groupFieldValue,$start = 1,$connection = 'sql') {
		
		$row = getRow("SELECT MAX($sortfield) AS Max_SortOrder 
				FROM $table WHERE $groupField = '$groupFieldValue'
		",array(),$connection);
		if (!strlen($row['Max_SortOrder'])) {
		 	return $start;
		} else {
			return $row['Max_SortOrder']+1;
		}
	}
	
	
	class QueryManager {
		var $dieOnError = true;
		var $rollbackOnError = true;
		var $dbConnection = NULL;
		var $dbType,$user,$pass,$server,$dbName;
		var $queries = array();
		var $inTransactionLevel = 0;
	
		function QueryManager($dbType,$user,$pass,$server,$dbName) {
			$this->dbType = $dbType;
			$this->user = $user;
			$this->pass = $pass;
			$this->server = $server;
			$this->dbName = $dbName;
		}
	
		function connection() {

			global $cfg;
			global $dbCfg;
			
			
			if ($this->dbConnection == NULL) {
				
				// Database abstraction layer
				//timerStart('Pear');
				//require_once('/usr/share/pear/DB.php');		
				//require_once('DB.php');		
				//timerFinish('Pear');
				
				// connect to the database
				timerStart('Get DB Connection');
				
				
				$db = $this->dbConnection = mysql_connect($this->server,$this->user,$this->pass);
				
				//$db = $this->dbConnection = DB::connect("$this->dbType://$this->user:$this->pass@$this->server/$this->dbName");									
				//$db = $this->dbConnection = DB::connect("$this->dbType://$this->user@$this->server/$this->dbName");									

				timerFinish('Get DB Connection');
				
				// see if we connected ok
				if ($db === false) {
					$error = mysql_error();
					ss_DumpVar($error,'',true);
					trigger_error('Could not connect to database.');
					die('');
					//die('Could not connect to database');
				}				
				
				mysql_select_db($this->dbName,$db);		
				
				// set the default fetch mode to return an associative 
				// array with the column names as the array keys
				//$this->dbConnection->setFetchMode(DB_FETCHMODE_ASSOC);
			}
			return $this->dbConnection;
		}	
	
		/* blah = query("
			SELECT * FROM assets
			WHERE
				__AssetID__
				AND __AssetType__
				",
			array(
			'as_id' => array(
				'query'	=>	"SELECT as_id FROM assets	WHERE as_type LIKE 'Page'",
				'type	=>	"IN",
			),
			'as_type' => "NOT IN (
				SELECT at_name FROM asset_types
				WHERE at_limit IS NULL)
			"))
		*/
		
		
		function query($theQuery,$subSelectQueries = array()) {
			$theQuery = trim($theQuery);
			// SubSelect kludge ;-)
//			echo $theQuery."\n";
			foreach ($subSelectQueries as $key => $query) {
				$type = "IN";
				if (is_array($query)) {
					ss_paramKey($query,'type','IN');
					$type = $query['type'];	
					$query = $query['query'];
				}
				$Q_SubSelectQuery = query($query);
				$subQueryResults = $Q_SubSelectQuery->columnValuesList();
				if (strlen($subQueryResults)) {
					$theQuery = str_replace("__{$key}__","$key $type ($subQueryResults)",$theQuery);
				} else {
					$theQuery = str_replace("__{$key}__","0",$theQuery);
				}
			}
			
			// get a connection (either new or reused)
			$temp = $this->connection();

			// do the query
			$startTime = getmicrotime();
			timerStart('Query');
            $result = mysql_query($theQuery.';',$temp);
			timerFinish('Query');

			// Add the query into the log
			array_push($this->queries,$theQuery.'<BR>Execution time: '.number_format((getmicrotime()-$startTime), 3, '.', '').' seconds<HR>');

			if (!$result) {
				$mysqlError = "MySQL reported: ".mysql_errno($temp).": ".mysql_error($temp)."\n";
				
				// Rollback 
				if ($this->rollbackOnError)
					$rollbackResult = mysql_query('ROLLBACK;',$temp);
				
				// Dump the query and halt
				if (ss_isItUs()) {
					print "<table bgcolor=\"#dddddd\"><tr><td>The following is only displayed to the deployer:<br />";
					print "<pre>";
					print $mysqlError;
					ss_log_message_r($theQuery,'Query',true);
					//print "UserInfo:";
					//print ss_HTMLEditFormat($result->userinfo);
					print "</pre>";
					if (!$this->dieOnError) {
						ss_DumpVar($theQuery,'Query Error',true);					
					} else {
						ss_DumpVar($_REQUEST,'$_REQUEST',true);
					}
					print "</td></tr></table>";
				}
				if ($this->dieOnError) {
					trigger_error('Query Error: '.ss_HTMLEditFormatWithBreaks($theQuery),E_USER_ERROR);
					exit;
				}
			} else {
				$result = new FakeQuery($result);	
			}
			
			return $result;
		}

		function startTransaction() {
			if ($this->inTransactionLevel == 0) {
				//$result = $this->query("START TRANSACTION");
				$result = $this->query("BEGIN");
			}
			$this->inTransactionLevel++;
		}

		function commit() {
			$this->inTransactionLevel--;
			if ($this->inTransactionLevel == 0) {
				$result = $this->query("COMMIT");
			} else if ($this->inTransactionLevel < 0) {
				die('Not in a transaction');
			}
			
	}

		function rollback() {
			$this->inTransactionLevel--;
			if ($this->inTransactionLevel == 0) {
				$result = $this->query("ROLLBACK");
			} else if ($this->inTransactionLevel < 0) {
				die('Not in a transaction');
			}
		}
	
		function escapeLike($string) {
			return str_replace('_','\_',str_replace('%','\%',addSlashes($string)));
		}
		
		function escape($string) {
			return addSlashes($string);
//			return str_replace("'","''",$string);
//			return str_replace('\\',
//			'\\\\',str_replace("'","''",$string));
		}
	
		function disconnect() {
			if ($this->dbConnection != NULL) {
				// disconnect from the database
				mysql_close($this->dbConnection);
				//$this->dbConnection->disconnect();
			}
		}
		
		function safe($testString) {
			if (eregi("[^NUL0-9., -]+", $testString)) {
			    die('Unauthorised values supplied. IP address recorded and website admin notified.');
			}
			return $testString;
		}
		
		function dumpQueries() {
			foreach ($this->queries as $query) {
				// Remove tab characters
				$query = str_replace(chr(9),'',$query);			
				ss_Pre("$query");
			}
		}
	}

	// Create a fake query
	class FakeQuery {
		
		var $rows = array();
		var $query = NULL;
		var $currentRow = 0;
		var $queryCurrentRow = 0;
		var $fields = NULL;
		
		function FakeQuery($input = NULL) {
			if (is_array($input)) {
				// Convert array into a 'query' structure
				$this->fields = $input; // array('EsID','EsName','EsParentLink');
			} else if ($input != NULL) {
				$this->query = $input;
			}
		}
		
		//$query->addRow(array('1','Matt','3'));
		
		function addRow($row) {
			if ($this->fields == NULL) {
				die('No fields are defined, so cannot add rows.');				
			}
			$this->query = NULL;
			$newRow = array();
			/*for($i=0;$i<count($row);$i++) {
				$newRow[$this->fields[$i]] = $row[$i];	
			}*/
			$counter = 0;
			foreach ($row as $field) {
				$newRow[$this->fields[$counter++]] = $field;
			}
			array_push($this->rows,$newRow);			
			unset($newRow);
			unset($row);
		}
		
		function addColumn($name) {
			$this->preFetch();				
			foreach	($this->rows as $key => $row) {
				$this->rows[$key][$name] = null;
			}
		}
		
		function free() {
			// does this free properly?
			if ($this->query === NULL) {
				unset($rows);
				$rows = null;
			} else {
				mysql_free_result($this->query);
				//$this->query->free();	
			}
		}
		
		function numRows() {
			if ($this->query != NULL) {
				return mysql_num_rows($this->query);
				//return $this->query->numRows();	
			} else {
				return count($this->rows);
			}	
				
		}
		
		function reset() {
			$this->currentRow = 0;
		}
		
		function setCell($field,$value,$row = null) {
			$this->preFetch();
			if ($row === null) $row = count($this->rows)-1;
			$this->rows[$row][$field] = $value;
		}
		
		function getRowWithValue($columnName,$value) {
			$this->preFetch();
			foreach	($this->rows as $key => $row) {
				if ($row[$columnName] == $value) {
					return $key;
				}
			}
			return null;
		}

		function getRow($key) {
			$this->preFetch();
			return $this->rows[$key];	
		}
		
		function columnValuesList($columnName=NULL,$delimiter = ',',$quotes = "'",$sqlEscape = true) {
			$this->preFetch();				
			$values = '';
			// If no column name supplied, grab the name of the first thing we find
			if ($columnName == NULL) {
				if (count($this->rows)) {
					$columnName = array_pop(array_keys($this->rows[0]));
				} else return '';
			}
			foreach	($this->rows as $key => $row) {
				$values .= ss_comma($values,$delimiter);
				if ($sqlEscape) {
					$values .= $quotes.escape($this->rows[$key][$columnName]).$quotes;
				} else {
					$values .= $quotes.$this->rows[$key][$columnName].$quotes;
				}
			}
			return $values;
		}
				
		function columnValuesArray($columnName,$asKey = false) {
			$this->preFetch();				
			$values = array();
			foreach	($this->rows as $key => $row) {
				if ($asKey) {
					$values[$this->rows[$key][$columnName]] = 1;
				} else {
					array_push($values,$this->rows[$key][$columnName]);
				}
			}
			return $values;
		}
		
		function preFetch() {
			if (!$this->isCached()) {
				// Preserve the current row 
				$oldCurrentRow = $this->currentRow;
				while ($row = $this->fetchRow()) {
					// Do nothing
				}	
				$this->currentRow = $oldCurrentRow;
			}
		}
		
		function isCached() {
//			return !(($this->query != NULL) and ($this->currentRow == $this->queryCurrentRow));
			return $this->query === null;
		}
		
		function fetchRow() {
			
			if (!$this->isCached()) {
				// get a real row
				$row = mysql_fetch_assoc($this->query);
				if ($row !== false) {
					// add it into our buffer
					array_push($this->rows,$row);
					// update curent row counters
					$this->queryCurrentRow++;
					$this->currentRow++;
				} else {
					// end of query so reset
					$this->reset();
					// and we dun want the old query any more
					$this->query = null;
					$row = null;
				}				
			} else {
				if ($this->currentRow < $this->numRows()) {
					$row = $this->rows[$this->currentRow];
					$this->currentRow++;
				} else {
					$this->reset();
					$row = null;	
				}
			}
			return $row;
		}
		
	}

?>
