<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012-2015 Daniel Hürtgen <huertgen@rheinschafe.de>, Rheinschafe GmbH
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
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
 * MySQL-Locking-Driver class.
 *  Main locking method: mysql table
 *
 * @package    rs_lock
 * @subpackage Locking/Driver
 * @license    http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 * @author     Daniel Hürtgen <huertgen@rheinschafe.de>
 */
class Tx_RsLock_Locking_Driver_MySQLDriver extends Tx_RsLock_Locking_Driver_AbstractDriver {

	/**
	 * Lock table name.
	 *
	 * @var string
	 */
	protected $_lockTableName = 'sys_lock';

	/**
	 * Lock table hash field.
	 *
	 * @var string
	 */
	protected $_hashTableField = 'hash';

	/**
	 * Lock table created_at field.
	 *
	 * @var string
	 */
	protected $_createdAtTableField = 'created_at';

	/**
	 * @var boolean
	 */
	private $_typo3DbDebugVar = FALSE;

	/**
	 * Returns string with driver name.
	 *
	 * @return string
	 * @see Tx_RsLock_Locking_Driver_DriverInterface::getType()
	 */
	public function getType() {
		return 'mysql';
	}

	/**
	 * Acquire lock.
	 *  Tries to acquire locking. It is very important, that the lock will be generated. If something went wrong,
	 *  throw an runtime exception, but do NOT return FALSE on fail!
	 *
	 * @throws Exception
	 * @return boolean TRUE, if lock was acquired without waiting for other clients/instances, otherwise, if the client was waiting, return FALSE.
	 */
	public function acquire() {
		$noWait = TRUE;
		$isAcquired = FALSE;

		// cleanup (GC)
		if (FALSE !== $row = $this->_getLockRecordFromDb(TRUE, '<')) {
			$this->_deleteLockRecordFromDb();
		}

		// try to acquire lock
		for ($i = 0; $i < $this->getRetries(); $i++) {
			if ($this->_insertLockRecordIntoDb()) {
				$noWait = ($i === 0);
				$isAcquired = TRUE;
				break;
			}
			$this->_waitForRetry();
		}

		// @todo write own exception class
		if (!$isAcquired) {
			throw new Exception('DB-Lock could not be acquired.');
		}

		$this->_isAcquired = $isAcquired;

		return $noWait;
	}

	/**
	 * Release lock.
	 *
	 * @return boolean TRUE if locked was release, otherwise throw lock exception.
	 */
	public function release() {
		// FIXME during php shutdown there is no database connection
		if (!$this->_getTypo3Db() instanceof t3lib_DB) {
			return FALSE;
		}

		$isReleased = TRUE;

		// if is acquired // release lock
		if ($this->isAcquired()) {
			if (!$this->_deleteLockRecordFromDb()) {
				$isReleased = FALSE;
			}
		}

		$this->_isAcquired = FALSE;

		return $isReleased;
	}

	/**
	 * Is lock aquired?
	 *
	 * @return boolean TRUE if lock was acquired, otherwise FALSE.
	 * @see Tx_RsLock_Locking_Driver_DriverInterface::isAcquired()
	 */
	public function isAcquired() {
		return $this->_getLockRecordFromDb(TRUE) && parent::isAcquired();
	}

	/**
	 * Revalidate if locking type is usable/available.
	 *
	 * @return boolean TRUE if locking type is usable/available, FALSE if not.
	 */
	public function isAvailable() {
		if (!$this->_getTypo3Db() instanceof t3lib_DB || !parent::isAvailable()) {
			return FALSE;
		}

		$sql = 'SHOW TABLES LIKE \'' . $this->_lockTableName . '\'';

		return $this->_getTypo3Db()->sql_num_rows($this->_getTypo3Db()->sql_query($sql)) ? TRUE : FALSE;
	}

	/**
	 * Getter for typo3 db object.
	 *
	 * @return t3lib_DB
	 */
	protected function _getTypo3Db() {
		return $GLOBALS['TYPO3_DB'];
	}

	/**
	 * Save typo3 db debug var, if will send debug output on errors.
	 *
	 * @return void
	 */
	protected function _saveTypo3DbDebugVar() {
		if ($this->_getTypo3Db()->debugOutput == 1) {
			$this->_typo3DbDebugVar = TRUE;
			$this->_getTypo3Db()->debugOutput = FALSE;
		}
	}

	/**
	 * Restore typo3 db debug var & send debug output on error.
	 *
	 * @return void
	 */
	protected function _restoreTypo3DbDebugVar() {
		if ($this->_typo3DbDebugVar) {
			$this->_typo3DbDebugVar = FALSE;
			$this->_getTypo3Db()->debugOutput = 1;
		}
	}

	/**
	 * Insert new lock record.
	 *
	 * @return boolean TRUE, if records was persisted to db, otherwise false.
	 */
	protected function _insertLockRecordIntoDb() {
		// prepare array
		$fields = array();
		$fields[$this->_hashTableField] = $this->getIdHash();
		$fields[$this->_createdAtTableField] = time();

		// exec insert
		$this->_saveTypo3DbDebugVar();
		$this->_getTypo3Db()->exec_INSERTquery($this->_lockTableName, $fields);
		$this->_restoreTypo3DbDebugVar();

		// return status
		return ($this->_getTypo3Db()->sql_affected_rows() === 1) ? TRUE : FALSE;
	}

	/**
	 * Get lock record for current id-hash from db.
	 *  Set param $useMaxAgeClause to only return record where max age is not passed.
	 *
	 * @param boolean $useMaxAgeClause (default: true)
	 * @param string  $comparisonMaxAgeClause
	 * @return array
	 */
	protected function _getLockRecordFromDb($useMaxAgeClause = TRUE, $comparisonMaxAgeClause = '>=') {
		// prepare sql select array
		$sql = array();
		$sql['table'] = $this->_lockTableName;
		$sql['fields'] = $this->_lockTableName . '.' . $this->_hashTableField . ', ' . $this->_lockTableName . '.' . $this->_createdAtTableField;
		$sql['where'] = $this->_lockTableName . '.' . $this->_hashTableField . ' = ' . $this->_getTypo3Db()
				->fullQuoteStr($this->getIdHash(), $this->_lockTableName);
		if ($useMaxAgeClause) {
			$sql['where'] .= ' AND ' . $this->_lockTableName . '.' . $this->_createdAtTableField . $comparisonMaxAgeClause . $this->_getMaxAge();
		}

		// exec & return result
		return $this->_getTypo3Db()->exec_SELECTgetSingleRow($sql['fields'], $sql['table'], $sql['where']);
	}

	/**
	 * Deletes lock records from db (matches by id-hash).
	 *
	 * @return boolean TRUE, if records affected, otherwise FALSE.
	 */
	protected function _deleteLockRecordFromDb() {
		// delete where clause
		$sqlWhere = $this->_lockTableName . '.' . $this->_hashTableField . '=' . $this->_getTypo3Db()
				->fullQuoteStr($this->getIdHash(), $this->_lockTableName);

		// execute delete
		$this->_getTypo3Db()->exec_DELETEquery($this->_lockTableName, $sqlWhere);

		// return status
		return $this->_getTypo3Db()->sql_affected_rows() ? TRUE : FALSE;
	}

}
