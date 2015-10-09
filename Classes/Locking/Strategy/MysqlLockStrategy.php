<?php

namespace Rheinschafe\RsLock\Locking\Strategy;

/*
 * This file is part of the Rheinschafe/Lock project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use Rheinschafe\RsLock\Locking\Exception\LockAcquireException;
use Rheinschafe\RsLock\Locking\Exception\LockAcquireWouldBlockException;
use Rheinschafe\RsLock\Locking\Exception\LockCreateException;
use TYPO3\CMS\Core\Database\DatabaseConnection;

class MysqlLockStrategy extends AbstractLockStrategy implements LockingStrategyInterface {

	/**
	 * Lock table name.
	 *
	 * @var string
	 */
	protected $lockTableName = 'sys_lock';

	/**
	 * Lock table hash field.
	 *
	 * @var string
	 */
	protected $hashTableField = 'hash';

	/**
	 * Lock table created_at field.
	 *
	 * @var string
	 */
	protected $createdAtTableField = 'created_at';

	/**
	 * Lock table is_shared_lock field.
	 *
	 * @var string
	 */
	protected $isSharedLockField = 'is_shared_lock';

	/**
	 * Lock table shared_lock_counter field.
	 *
	 * @var string
	 */
	protected $sharedLockCounterField = 'shared_lock_counter';

	/**
	 * @var boolean
	 */
	private $typo3DbDebugVar = FALSE;

	/**
	 * Number of retries, if locking fails.
	 *
	 * @var int
	 */
	protected $retries = 150;

	/**
	 * Number of milliseconds wait between retries.
	 *
	 * @var int
	 */
	protected $retryInterval = 100;

	/**
	 * @var string id
	 */
	protected $id = "";

	/**
	 * @var int lastUsedMode
	 */
	protected $lastUsedMode = self::LOCK_CAPABILITY_EXCLUSIVE;

	/**
	 * @param string $subject ID to identify this lock in the system
	 * @throws LockCreateException if the lock could not be created
	 */
	public function __construct($subject) {
		parent::__construct($subject);
		$this->id = 'mysql_' . md5((string)$this->subject);
	}

	/**
	 * Try to acquire a lock
	 *
	 * @param int $mode LOCK_CAPABILITY_EXCLUSIVE or LOCK_CAPABILITY_SHARED
	 * @return bool Returns TRUE if the lock was acquired successfully
	 * @throws \Exception
	 */
	public function acquire($mode = self::LOCK_CAPABILITY_EXCLUSIVE) {
		$this->lastUsedMode = $mode;
		$noWait = TRUE;
		$isAcquired = FALSE;

		$row = $this->getLockRecordFromDb(TRUE, '<', $mode);
		// cleanup (GC)
		if (FALSE !== $row) {
			$this->deleteLockRecordFromDb();
		}

		// try to acquire lock
		for ($i = 0; $i < $this->getRetries(); $i++) {
			if ($this->insertLockRecordIntoDb()) {
				$noWait = ($i === 0);
				$isAcquired = TRUE;
				break;
			}
			$this->waitForRetry();
		}

		// @todo write own exception class
		if (!$isAcquired) {
			throw new \Exception('DB-Lock could not be acquired.');
		}

		$this->isAcquired = $isAcquired;

		return $noWait;
	}

	/**
	 * Release the lock
	 *
	 * @return bool Returns TRUE on success or FALSE on failure
	 */
	public function release() {
		// FIXME during php shutdown there is no database connection
		if (!$this->getTypo3Db() instanceof DatabaseConnection) {
			return FALSE;
		}

		$isReleased = TRUE;

		// if is acquired // release lock
		if ($this->isAcquired()) {
			if (!$this->deleteLockRecordFromDb()) {
				$isReleased = FALSE;
			}
		}

		$this->isAcquired = FALSE;

		return $isReleased;
	}

	/**
	 * Destructor:
	 * Releases lock automatically when instance is destroyed and release resources
	 */
	public function __destruct() {
		$this->release();
	}

	/**
	 * Destroys the resource associated with the lock
	 *
	 * @return void
	 */
	public function destroy() {

	}

	/**
	 * Getter for typo3 db object.
	 *
	 * @return DatabaseConnection
	 */
	protected function getTypo3Db() {
		return $GLOBALS['TYPO3_DB'];
	}

	/**
	 * Deletes lock records from db (matches by id-hash).
	 *
	 * @return boolean TRUE, if records affected, otherwise FALSE.
	 */
	protected function deleteLockRecordFromDb() {
		$mode = $this->lastUsedMode;
		$lockdata = $row = $this->getLockRecordFromDb(FALSE, '<', $mode);
		$counter = 0;
		if (array_key_exists($this->sharedLockCounterField, $lockdata)) {
			$counter = (int)$lockdata[$this->sharedLockCounterField];
		}

		if ($mode == self::LOCK_CAPABILITY_SHARED && $counter > 1) {
			//If we have a shared lock we only must descrement the counter value
			$sqlWhere = $this->lockTableName . '.' . $this->hashTableField . '=' . $this->getTypo3Db()->fullQuoteStr(
					$this->getIdHash(),
					$this->lockTableName
				) .
				' AND ' . $this->lockTableName . '.' . $this->sharedLockCounterField;

			$this->getTypo3Db()->exec_UPDATEquery(
				$this->lockTableName,
				$sqlWhere,
				array($this->sharedLockCounterField => $counter - 1)
			);
		} else {
			// delete where clause
			$sqlWhere = $this->lockTableName . '.' . $this->hashTableField . '=' . $this->getTypo3Db()->fullQuoteStr(
					$this->getIdHash(),
					$this->lockTableName
				);

			// execute delete
			$this->getTypo3Db()->exec_DELETEquery($this->lockTableName, $sqlWhere);

			// return status
		}

		return $this->getTypo3Db()->sql_affected_rows() ? TRUE : FALSE;

	}

	/**
	 * Get lock record for current id-hash from db.
	 *  Set param $useMaxAgeClause to only return record where max age is not passed.
	 *
	 * @param boolean $useMaxAgeClause (default: true)
	 * @param string  $comparisonMaxAgeClause
	 * @param integer $mode
	 * @return array
	 */
	protected function getLockRecordFromDb($useMaxAgeClause = TRUE, $comparisonMaxAgeClause = '>=', $mode) {
		// prepare sql select array
		$isSharedLock = $mode == self::LOCK_CAPABILITY_SHARED ? 1 : 0;
		$sql = array();
		$sql['table'] = $this->lockTableName;
		$sql['fields'] = $this->lockTableName . '.' . $this->hashTableField . ', ' .
			$this->lockTableName . '.' . $this->createdAtTableField . ', ' .
			$this->lockTableName . '.' . $this->isSharedLockField . ', ' .
			$this->lockTableName . '.' . $this->sharedLockCounterField;
		$sql['where'] = $this->lockTableName . '.' . $this->hashTableField . ' = ' . $this->getTypo3Db()->fullQuoteStr(
				$this->getIdHash(),
				$this->lockTableName
			);

		if ($useMaxAgeClause) {
			$sql['where'] .= ' AND ' . $this->lockTableName . '.' . $this->createdAtTableField . $comparisonMaxAgeClause . $this->getMaxAge(
				);
		}

		$sql['where'] .= ' AND ' . $this->lockTableName . '.' . $this->isSharedLockField . ' = ' . $isSharedLock;

		// exec & return result
		return $this->getTypo3Db()->exec_SELECTgetSingleRow($sql['fields'], $sql['table'], $sql['where']);
	}

	/**
	 * Insert new lock record.
	 *
	 * @return boolean TRUE, if records was persisted to db, otherwise false.
	 */
	protected function insertLockRecordIntoDb() {
		$row = $this->getLockRecordFromDb(FALSE, '<', $this->lastUsedMode);
		// prepare array
		$fields = array();
		$fields[$this->hashTableField] = $this->getIdHash();
		$fields[$this->createdAtTableField] = time();
		$fields[$this->isSharedLockField] = $this->lastUsedMode == self::LOCK_CAPABILITY_SHARED ? 1 : 0;

		if ($this->lastUsedMode == self::LOCK_CAPABILITY_SHARED) {
			if ($row) {
				//Only Update an existing lock
				$fields[$this->sharedLockCounterField] = (int)$row[$this->sharedLockCounterField] + 1;
				$sqlWhere = $this->lockTableName . '.' . $this->hashTableField . '=' . $this->getTypo3Db()->fullQuoteStr(
						$this->getIdHash(),
						$this->lockTableName
					);
				$this->saveTypo3DbDebugVar();
				$this->getTypo3Db()->exec_UPDATEquery(
					$this->lockTableName,
					$sqlWhere,
					array($this->sharedLockCounterField => $fields[$this->sharedLockCounterField])
				);
				$this->restoreTypo3DbDebugVar();
			} else {
				//New shared lock
				// exec insert
				$fields[$this->sharedLockCounterField] = 1;
				$this->saveTypo3DbDebugVar();
				$this->getTypo3Db()->exec_INSERTquery($this->lockTableName, $fields);
				$this->restoreTypo3DbDebugVar();
			}
			//Shared
		} else {
			//Exclusive Lock
			$fields[$this->sharedLockCounterField] = 0;
			// exec insert
			$this->saveTypo3DbDebugVar();
			$this->getTypo3Db()->exec_INSERTquery($this->lockTableName, $fields);
			$this->restoreTypo3DbDebugVar();
		}

		// return status
		return ($this->getTypo3Db()->sql_affected_rows() === 1) ? TRUE : FALSE;
	}

	/**
	 * Save typo3 db debug var, if will send debug output on errors.
	 *
	 * @return void
	 */
	protected function saveTypo3DbDebugVar() {
		if ($this->getTypo3Db()->debugOutput == 1) {
			$this->typo3DbDebugVar = TRUE;
			$this->getTypo3Db()->debugOutput = FALSE;
		}
	}

	/**
	 * Restore typo3 db debug var & send debug output on error.
	 *
	 * @return void
	 */
	protected function restoreTypo3DbDebugVar() {
		if ($this->typo3DbDebugVar) {
			$this->typo3DbDebugVar = FALSE;
			$this->getTypo3Db()->debugOutput = 1;
		}
	}

	/**
	 * Waits milliseconds (interval) for next retry.
	 *
	 * @return void
	 */
	protected function waitForRetry() {
		usleep($this->getRetryInterval() * 1000);
	}

	/**
	 * Get acquire retries setting.
	 *
	 * @return int
	 */
	public function getRetries() {
		return $this->retries;
	}

	/**
	 * Get acquire retry interval setting.
	 *
	 * @return int
	 */
	public function getRetryInterval() {
		return $this->retryInterval;
	}

	/**
	 * Get max age of an existing lock.
	 *
	 * @return integer
	 */
	protected function getMaxAge() {
		$maxExecutionTime = ini_get('max_execution_time');

		return time() - ($maxExecutionTime ? $maxExecutionTime : 120);
	}

	/**
	 * Return unique id hash.
	 * 40 chars long string sha1().
	 *
	 * @return string
	 */
	protected function getIdHash() {
		return $this->id;
	}

	/**
	 * @return int LOCK_CAPABILITY_* elements combined with bit-wise OR
	 */
	static public function getCapabilities() {
		return self::LOCK_CAPABILITY_EXCLUSIVE | self::LOCK_CAPABILITY_SHARED;
	}

	/**
	 * @return int Returns a priority for the method. 0 to 100, 100 is highest
	 */
	static public function getPriority() {
		return 80;
	}
}
