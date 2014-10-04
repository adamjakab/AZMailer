<?php
namespace AZMailer\Helpers;
/**
 * @package    AZMailer
 * @subpackage Helpers
 * @author     Adam Jakab {@link http://www.alfazeta.com}
 * @author     Created on 09-Feb-2013
 * @license    GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');

class AZMailerDBUpdaterHelper {
	private $tableClassesFolder = null;
	private $db;
	private $verbose = true;
	private $AZMailerTables = array();
	private $currentTable = null;
	private $errorNum = 0;
	private $errorMsg = "";

	public function __construct() {
		$this->db = \JFactory::getDBO();
		$this->tableClassesFolder = realpath(dirname(__DIR__).DS.'Tables');
		//$this->log("tableClassesFolder: " . $this->tableClassesFolder);
	}

	public function update($verbose = false) {
		$this->verbose = $verbose;
		$this->log("AZMailerDBUpdaterHelper: ready to update!");
		$this->getAZMailerTableClassNameList();
		if (count($this->AZMailerTables) > 0) {
			foreach ($this->AZMailerTables as $className) {
				if (class_exists($className)) {
					$this->currentTable = new $className();
					$this->log('<hr /><h2 style="margin:0px;">Checking table: ' . $this->currentTable->name . "</h2>");
					$this->updateTable();
					$this->populateTable();
				}
			}
		}
		return (true);
	}

	public function removeAllTables($verbose = false) {
		$this->verbose = $verbose;
		$this->log("AZMailerDBUpdaterHelper: ready to remove all tables!");
		$this->getAZMailerTableClassNameList();
		if (count($this->AZMailerTables) > 0) {
			foreach ($this->AZMailerTables as $className) {
				if (class_exists($className)) {
					$this->currentTable = new $className();
					$this->deleteTable();
				}
			}
		}
		return (true);
	}

	private function deleteTable() {
		$tableName = '#__' . $this->currentTable->name;
		$msg = 'DELETING TABLE: ' . $this->currentTable->name;
		if ($this->_checkIfTableExists($tableName)) {
			$sql = 'DROP TABLE ' . '`' . $tableName . '`';
			$res = $this->___executeSql($sql);
			$msg .= ($res?' - deleted.':' - error!');
		} else {
			$msg .= ' - already deleted.';
		}
		$this->log($msg);
	}


	private function updateTable() {
		$tableName = '#__' . $this->currentTable->name;
		$this->log("Updating table: " . $tableName);
		//create table
		if (!$this->_checkIfTableExists($tableName)) {
			if (isset($this->currentTable->columns) && is_array($this->currentTable->columns) && count($this->currentTable->columns) > 0) {
				$sql = 'CREATE TABLE IF NOT EXISTS `' . $tableName . '` (';
				foreach ($this->currentTable->columns as $column) {
					$sql .= '`' . $column["Field"] . '`'
						. ' ' . $column["Type"]
						. ' ' . ($column["Null"] == 'NO' ? 'NOT NULL' : 'NULL')
						. '' . (!empty($column["Default"]) || $column["Default"] == "0" ? ' DEFAULT \'' . $column["Default"] . '\'' : '')
						. ' ' . $column["Extra"]
						. ','
						. '';
				}
				if (isset($this->currentTable->pk) && !empty($this->currentTable->pk)) {
					$sql .= ' PRIMARY KEY (`' . $this->currentTable->pk . '`)';
				}
				$sql = trim($sql, ","); //remove trailing comma
				$sql .= ') AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;';
				$res = $this->___executeSql($sql);
				$this->currentTable->isNewTable = true;
			}
		}

		//update columns
		if (isset($this->currentTable->columns) && is_array($this->currentTable->columns) && count($this->currentTable->columns) > 0) {
			//get column from db
			$sql = 'SHOW COLUMNS FROM `' . $tableName . '`;';
			$dbColumns = $this->___loadSqlMultipleResults($sql, "Field");
			foreach ($this->currentTable->columns as $column) {
				if (!isset($dbColumns[$column["Field"]])) {
					//$this->log("COLUMN DOES NOT EXIST: " . $column["Field"]);
					$sql = 'ALTER TABLE `' . $tableName . '` ADD `' . $column["Field"] . '`'
						. ' ' . $column["Type"]
						. ' ' . ($column["Null"] == 'NO' ? 'NOT NULL' : 'NULL')
						. '' . (!empty($column["Default"]) || $column["Default"] == "0" ? ' DEFAULT \'' . $column["Default"] . '\'' : '')
						. ' ' . $column["Extra"]
						. ';';
					$res = $this->___executeSql($sql);
				} else {
					//$this->log("COL EXISTS(CHECKING): " . $column["Field"] . " -> " . print_r($column, true));
					$needsChange = false;
					if (strtolower($column["Type"]) != strtolower($dbColumns[$column["Field"]]["Type"])) {
						//$this->log("COL[Type] ARE DIFFERENT");
						$needsChange = true;
					} else if (strtolower($column["Null"]) != strtolower($dbColumns[$column["Field"]]["Null"])) {
						//$this->log("COL[Null] ARE DIFFERENT");
						$needsChange = true;
					} else if (strtolower($column["Default"]) != strtolower($dbColumns[$column["Field"]]["Default"])) {
						//$this->log("COL[Default] ARE DIFFERENT");
						$needsChange = true;
					} else if (strtolower($column["Extra"]) != strtolower($dbColumns[$column["Field"]]["Extra"])) {
						//$this->log("COL[Extra] ARE DIFFERENT");
						$needsChange = true;
					}
					if ($needsChange) {
						$sql = 'ALTER TABLE `' . $tableName . '` CHANGE `' . $column["Field"] . '` `' . $column["Field"] . '`'
							. ' ' . $column["Type"]
							. ' ' . ($column["Null"] == 'NO' ? 'NOT NULL' : 'NULL')
							. '' . (!empty($column["Default"]) || $column["Default"] == "0" ? ' DEFAULT \'' . $column["Default"] . '\'' : '')
							. ' ' . $column["Extra"]
							. ';';
						$res = $this->___executeSql($sql);
					}

				}
			}

			//create indexes
			if (isset($this->currentTable->keys) && is_array($this->currentTable->keys) && count($this->currentTable->keys) > 0) {
				foreach ($this->currentTable->keys as $keyColumn => $keyUnique) {
					$keyName = $this->currentTable->name . "_" . $keyColumn;
					if (!$this->_checkIfTableKeyExists($tableName, $keyName)) {
						$sql = 'ALTER TABLE `' . $tableName . '` ADD ' . ($keyUnique ? "UNIQUE" : "INDEX") . ' `' . $keyName . '` (' . $keyColumn . ');';
						$res = $this->___executeSql($sql);
					}
				}
			}

			//delete old indexes
			$sql = 'SHOW INDEX FROM `' . $tableName . '` WHERE Key_name <> ' . $this->db->quote("PRIMARY");
			$dbIndexes = $this->___loadSqlMultipleResults($sql, "Key_name");
			foreach ($dbIndexes as $dbKeyName => $dbKey) {
				$needsDrop = true;
				if (isset($this->currentTable->keys) && is_array($this->currentTable->keys) && count($this->currentTable->keys) > 0) {
					foreach ($this->currentTable->keys as $keyColumn => $keyUnique) {
						$keyName = $this->currentTable->name . "_" . $keyColumn;
						if ($dbKeyName == $keyName) {
							$needsDrop = false;
							break;
						}
					}
				}
				if ($needsDrop) {
					$this->log("DROPPING INDEX[$dbKeyName]");
					$sql = 'ALTER TABLE `' . $tableName . '` DROP INDEX `' . $dbKeyName . '`';
					$res = $this->___executeSql($sql);
				}
			}


			//check if column is to be deleted - we need to reload columns
			$sql = 'SHOW COLUMNS FROM `' . $tableName . '`;';
			$dbColumns = $this->___loadSqlMultipleResults($sql, "Field");
			foreach ($dbColumns as $dbColName => $dbColumn) {
				$needsDrop = true;
				foreach ($this->currentTable->columns as $column) {
					if ($dbColName == $column["Field"]) {
						$needsDrop = false;
						break;
					}
				}
				if ($needsDrop) {
					//$this->log("COL[$dbColName] IS TO BE DROPPED");
					$sql = 'ALTER TABLE `' . $tableName . '` DROP `' . $dbColName . '`';
					$res = $this->___executeSql($sql);
				}
			}


		}


	}

	private function populateTable() {
		$tableName = '#__' . $this->currentTable->name;
		$this->log("Populating table: " . $tableName);
		if ($this->currentTable->isNewTable || $this->currentTable->forceNewDataInsert) {
			if (count($this->currentTable->data) > 0) {
				foreach ($this->currentTable->data as $dataArray) {
					//checking if forcing insert
					if ($this->currentTable->forceNewDataInsert) {
						if (empty($this->currentTable->forceNewDataInsert_checkColumn)) {
							continue;
						}
						$FD_checkColumn = $this->currentTable->forceNewDataInsert_checkColumn;
						$FD_checkDataIndex = null;
						$i = -1;
						foreach ($this->currentTable->columns as $column) {
							if ($column["Field"] == $FD_checkColumn) {
								$FD_checkDataIndex = $i;
								break;
							}
							$i++;
						}
						if (is_null($FD_checkDataIndex)) {
							continue;
						}
						$FD_checkValue = $dataArray[$FD_checkDataIndex];
						$sql = 'SELECT `' . $FD_checkColumn . '` FROM `' . $tableName . '` WHERE `' . $FD_checkColumn . '` = ' . $this->db->quote($FD_checkValue);
						$res = $this->___loadSqlSingleResult($sql);
						if (!empty($res)) {
							continue;
						}

					}
					//dataArray always follows "column" order and it has NOT got ID column which must always be the first one
					$columnArray = array();
					$valueArray = array();
					foreach ($this->currentTable->columns as $column) {
						if (strtolower($column["Field"]) != "id") {
							array_push($columnArray, $column["Field"]);
						}
					}
					foreach ($dataArray as $sv) {
						array_push($valueArray, $this->db->quote($sv));
					}
					if (count($columnArray) > 0 && count($valueArray) > 0 && count($columnArray) == count($valueArray)) {
						$cols = implode(", ", $columnArray);
						$vals = implode(", ", $valueArray);
						$sql = 'INSERT INTO `' . $tableName . '` (' . $cols . ') VALUES (' . $vals . ');';
						$res = $this->___executeSql($sql);
					}
				}
			}
		}
	}


	private function getAZMailerTableClassNameList() {
		$CFLIST = $this->getFolderFileList($this->tableClassesFolder, '/^[0-9]{1,2}_azmailer_[a-z0-9\-_]*\.php$/i');
		//$this->log("CFLIST: " . print_r($CFLIST, true));
		if (count($CFLIST)) {
			foreach ($CFLIST as $CF) {
				require_once($this->tableClassesFolder . DS . $CF);
				$className = preg_replace(array('/^[0-9]{1,2}_/', '/\.php/'), array('tbl_', ''), $CF);
				$this->log("classFile: " . $CF . " -->className: " . $className);
				if (class_exists($className)) {
					array_push($this->AZMailerTables, $className);
				}
			}
		}
	}


	//------------------------------------------------------------------------DB-SQL
	private function _checkIfTableExists($tablename) {
		$tablename = str_replace('#__', $this->db->getPrefix(), $tablename);
		$tblList = $this->db->getTableList();
		return(in_array($tablename, $tblList));
	}

	private function _checkIfTableKeyExists($tablename, $keyName) {
		$sql = 'SHOW INDEX FROM `' . $tablename . '` WHERE Key_name = "' . $keyName . '";';
		$res = $this->___loadSqlSingleResult($sql);
		return (!empty($res));
	}

	private function ___loadSqlSingleResult($sql) {
		$this->db->setQuery($sql);
		$res = $this->db->loadResult();
		$err = $this->db->getErrorNum();
		$this->log(strip_tags($sql) . " <b>RES($err):</b> " . ($err == 0 ? "OK" : $this->db->getErrorMsg()));
		return (($err == 0 ? $res : ""));
	}

	private function ___loadSqlMultipleResults($sql, $key = null) {
		$this->db->setQuery($sql);
		$res = $this->db->loadAssocList($key);
		$err = $this->db->getErrorNum();
		$this->log(strip_tags($sql) . " <b>RES($err):</b> " . ($err == 0 ? "OK" : $this->db->getErrorMsg()));
		return (($err == 0 ? $res : ""));
	}

	private function ___executeSql($sql) {
		$this->db->setQuery($sql);
		$this->db->query();
		$err = $this->db->getErrorNum();
		$this->log(strip_tags($sql) . " <b>RES($err):</b> " . ($err == 0 ? "OK" : $this->db->getErrorMsg()));
		return ($err == 0);
	}

	//-------------------------------------------------------------------------UTILS
	private function getFolderFileList($dir, $file_pattern = '/.*/') {
		$answer = array();
		if ($handle = opendir($dir)) {
			while (false !== ($file = readdir($handle))) {
				if (preg_match($file_pattern, $file) == 1) {
					$answer[] = $file;
				}
			}
			closedir($handle);
		}
		if (count($answer) > 0) {
			sort($answer);
		}
		return ($answer);
	}

	private function log($msg, $type = "info") {
		if ($this->verbose) {
			echo '<br /><span class="' . $type . '">' . $msg . '</span>';
		}
	}
}

?>