<?php

namespace Rheinschafe\RsLock\Locking;

/*
 * This file is part of the Rheinschafe/Lock project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use Rheinschafe\RsLock\Locking\Exception\LockAdapterException;
use Rheinschafe\RsLock\Locking\Strategy\LockingStrategyInterface;
use TYPO3\CMS\Core\Locking\Locker as CoreLocker;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Typo3 adapter to fit function 'instanceof' \TYPO3\CMS\Core\Locking\Locker.
 *  X-Class must extend \TYPO3\CMS\Core\Locking\Locker!
 *
 * @author     Daniel Hürtgen <huertgen@rheinschafe.de>
 * @author     Florian in der Beek <inderbeek@rheinschafe.de>
 */
class Locker extends CoreLocker {

	/**
	 * Holds the locker factory instance
	 *
	 * @var LockFactory
	 */
	protected $lockFactory = NULL;

	/**
	 * Holds the lock object instance
	 *
	 * @var LockingStrategyInterface
	 */
	protected $lockObject = NULL;

	/**
	 * Is log enabled?
	 *
	 * @var bool
	 */
	protected $isLoggingEnabled = FALSE;

	/**
	 * @var string
	 */
	protected $id = "";

	/**
	 * @var string
	 */
	protected $method = "";

	/**
	 * @var string Logging facility
	 */
	protected $syslogFacility = 'cms';

	/**
	 * Constructor:
	 * initializes locking, check input parameters and set variables accordingly.
	 *
	 * Parameters $loops and $step only apply to the locking method LOCKING_METHOD_SIMPLE.
	 *
	 * @param string $id     ID to identify this lock in the system
	 * @param string $method Define which locking method to use. Use one of the LOCKING_METHOD_* constants. Defaults to
	 *                       LOCKING_METHOD_SIMPLE. Use '' to use setting from Install Tool.
	 * @param int    $loops  Number of times a locked resource is tried to be acquired.
	 * @param int    $step   Milliseconds after lock acquire is retried. $loops * $step results in the maximum delay of a lock.
	 * @throws \RuntimeException
	 * @throws \InvalidArgumentException
	 */
	public function __construct($id, $method, $loops = 0, $step = 0) {
		if ( isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['enableLockLogging'] ) ){
			$this->setEnableLogging( (boolean)$GLOBALS['TYPO3_CONF_VARS']['SYS']['enableLockLogging'] );
		}else {
			$this->setEnableLogging( FALSE );
		}

		$this->lockFactory = GeneralUtility::makeInstance("Rheinschafe\\RsLock\\Locking\\LockFactory");
		$this->lockFactory->addLockingStrategy("Rheinschafe\\RsLock\\Locking\\Strategy\\MysqlLockStrategy");

		// Force ID to be string
		$this->id = (string)$id;
		if ((int)$loops) {
			$this->loops = (int)$loops;
		}
		if ((int)$step) {
			$this->step = (int)$step;
		}
		$this->method = $method;

	}

	/**
	 * Destructor.
	 * Releases lock automatically when instance is destroyed and release resources
	 *
	 * @return void
	 */
	public function __destruct() {
		$this->release();
	}

	/**
	 * Acquire a exclusive look from the Lock Strategy Object
	 *
	 * @return bool
	 */
	public function acquire() {
		return $this->acquireExclusiveLock();
	}

	/**
	 * Acquire a exclusive look from the Lock Strategy Object
	 *
	 * @return bool
	 */
	public function acquireExclusiveLock() {
		return $this->acquireLock(
			LockingStrategyInterface::LOCK_CAPABILITY_EXCLUSIVE
		);
	}

	/**
	 * Acquire a shared look from the Lock Strategy Object
	 *
	 * @return bool
	 */
	public function acquireSharedLock() {
		return $this->acquireLock(
			LockingStrategyInterface::LOCK_CAPABILITY_SHARED
		);
	}

	/**
	 * Acquire a look with the given capabilitys from the Lock Strategy Object
	 *
	 * @return bool
	 */
	protected function acquireLock($type) {
		$this->sysLog('LockTypeId'.$type);
		if (!$this->lockObject) {
			$this->lockObject = $this->lockFactory->createLocker($this->id, $type);
		}

		return $this->lockObject->acquire($type);
	}

	/**
	 * Return the global status of the lock
	 *
	 * @return bool Returns TRUE if the lock is locked by either this or another process, FALSE otherwise
	 */
	public function getLockStatus() {
		if (!$this->lockObject) {
			return FALSE;
		}

		return $this->lockObject->isAcquired();
	}

	/**
	 * Release the lock
	 *
	 * @return bool Returns TRUE on success or FALSE on failure
	 * @throws LockAdapterException
	 */
	public function release() {
		if (!$this->lockObject) {
			throw new LockAdapterException("Tried to release lock, but adapter was not ready");
		}

		return $this->lockObject->release();
	}

	/**
	 * Return the global status of the lock
	 *
	 * @return bool Returns TRUE if the lock is locked by either this or another process, FALSE otherwise
	 */
	public function isLocked() {
		if (!$this->lockObject) {
			return FALSE;
		}

		return $this->lockObject->isAcquired();
	}

	/**
	 * Get the given method
	 *
	 * @return string
	 */
	public function getMethod() {
		return $this->method;
	}

	/**
	 * Get the given id
	 *
	 * @return string
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * Return the resource which is currently used
	 *
	 * @return NULL
	 */
	public function getResource() {
		return NULL;
	}

	/**
	 * Sets the facility (extension name) for the syslog entry
	 *
	 * @param string $syslogFacility
	 */
	public function setSyslogFacility($syslogFacility) {
		$this->syslogFacility = $syslogFacility;
	}

	/**
	 * Enable/ disable logging
	 *
	 * @param boolean $isLoggingEnabled
	 */
	public function setEnableLogging($isLoggingEnabled) {
		$this->isLoggingEnabled = $isLoggingEnabled;
	}

	/**
	 * Adds a common log entry for this locking API using t3lib_div::sysLog()
	 * Example: 01-01-13 20:00 - cms: Locking [simple::0aeafd2a67a6bb8b9543fb9ea25ecbe2]: Acquired
	 *
	 * @param string  $message  The message to be logged.
	 * @param integer $severity Severity - 0 is info (default), 1 is notice, 2 is warning, 3 is error, 4 is fatal error.
	 * @return void
	 */
	public function sysLog($message, $severity = 0) {
		if (!$this->isLoggingEnabled) {
			return;
		}
		$trace = debug_backtrace();
		if (isset($trace[3])) {
			$caller = $trace[3]['file'];
		}elseif ( isset($trace[2]) ) {
			$caller = $trace[2]['file'];
		}else{
			$caller = '';
		}

		GeneralUtility::sysLog(
			'[' . get_class($this->lockObject) . ' : ' . $this->getId() . '] ' . $message .' - Caller: ' .$caller,
			'rs_lock',
			$severity
		);
	}
}
