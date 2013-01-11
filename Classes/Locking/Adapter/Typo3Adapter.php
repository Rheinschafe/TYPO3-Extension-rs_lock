<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012 Daniel Hürtgen <huertgen@rheinschafe.de>, Rheinschafe GmbH
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
 * Typo3 adapter to fit function 'instanceof' t3lib_lock.
 *  X-Class must extend t3lib_lock!
 *
 * @package    rs_lock
 * @subpackage Locking/Adapter
 * @license    http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 * @author     Daniel Hürtgen <huertgen@rheinschafe.de>
 */
class Tx_RsLock_Locking_Adapter_Typo3Adapter extends t3lib_lock
	implements Tx_RsLock_Locking_Adapter_Typo3AdapterInterface {

	/**
	 * Get real simple locker object.
	 *
	 * @var Tx_RsLock_Locking_SimpleLockerInterface
	 */
	private $locker;

	/**
	 * Constructor.
	 *
	 * @param mixed                                           $id     Unique id used for locking.
	 * @param string|Tx_RsLock_Locking_Driver_DrvierInterface $driver Driver class object or string.
	 * @param null                                            $loops  Times a lock is tried to acuqire.
	 * @param null                                            $steps  Milliseconds to sleep between looping.
	 * @return Tx_RsLock_Locking_Adapter_Typo3Adapter
	 */
	public function __construct($id, $driver, $loops = NULL, $steps = NULL) {
		$context = $this->_determineLockingContext();
		$this->locker = t3lib_div::makeInstance('Tx_RsLock_Locking_SimpleLocker', $id, $driver, $context, $loops,
												$steps);
	}

	/**
	 * Tries to resolve the current locking context.
	 *
	 * @return string
	 * @todo hate me :( but need context for unique id hash generation
	 */
	protected function _determineLockingContext() {
		// set default value
		$default = $this->_getDefaultLockingContext();
		$context = $default;

		// tries by backtrace
		$contextMapping = $this->_getLockingContextMapping();
		if (version_compare(PHP_VERSION, '5.3.6', '<')) {
			$bt = debug_backtrace(FALSE);
		} else {
			$bt = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
		}
		if (is_array($bt)) {
			foreach ($bt as $btItem) {
				if (!isset($btItem['class']) || !isset($btItem['function']) || !isset($btItem['type'])) {
					continue;
				}

				$matchAgainstArray = array(
					'class'    => $btItem['class'],
					'function' => $btItem['function']
				);
				$matchedContext = array_search($matchAgainstArray, $contextMapping, TRUE);
				if ($matchedContext !== FALSE) {
					$context = $contextMapping[$matchedContext]['class'] . '->' . $contextMapping[$matchedContext]['function'];
					break;
				}
			}
		}

		return $context;
	}

	/**
	 * Returns the default locking context/prefix.
	 *
	 * @return string
	 */
	protected function _getDefaultLockingContext() {
		return 'typo3-legacy-api';
	}

	/**
	 * Returns an array of context/prefix mapping against debug_backtrage.
	 *  Use the following format:
	 *   array(
	 *     'class'    => 'className to search for',
	 *     'function' => 'function call to search for',
	 *   )
	 *
	 * @return array
	 */
	protected function _getLockingContextMapping() {
		return array(
			array(
				'class'    => 't3lib_div',
				'function' => 'sysLog'
			),
			array(
				'class'    => 'tslib_fe',
				'function' => 'getFromCache'
			),
			array(
				'class'    => 't3lib_mail_MboxTransport',
				'function' => 'send'
			),
		);
	}

	/**
	 * Get real locker object.
	 *
	 * @return Tx_RsLock_Locking_SimpleLockerInterface
	 */
	public function getLocker() {
		return $this->locker;
	}

	/**
	 * Get driver object.
	 *
	 * @return Tx_RsLock_Locking_Driver_DriverInterface
	 */
	public function getDriver() {
		return $this->locker->getDriver();
	}

	/**
	 * Acquire lock.
	 *  Tries to acquire locking. It is very important, that the lock will be generated. If something went wrong,
	 *  throw an runtime exception, but do NOT return FALSE on fail!
	 *
	 * @return boolean Return TRUE, if lock was acquired without waiting for other clients/instances, otherwise, if the client was waiting, return FALSE.
	 * @see Tx_RsLock_Locking_Adapter_Typo3AdapterInterface::acquire()
	 */
	public function acquire() {
		$this->getDriver()->acquire();
	}

	/**
	 * Release lock.
	 *
	 * @return boolean TRUE if locked was release, otherwise throw lock exception.
	 * @see Tx_RsLock_Locking_Adapter_Typo3AdapterInterface::release()
	 */
	public function release() {
		$this->getDriver()->release();
	}

	/**
	 * Return the unique identifier used for locking.
	 *
	 * @return string
	 * @see Tx_RsLock_Locking_Adapter_Typo3AdapterInterface::getMethod()
	 */
	public function getMethod() {
		return $this->getDriver()->getType();
	}

	/**
	 * Return the unique identifier used for locking.
	 *
	 * @return string
	 * @see Tx_RsLock_Locking_Adapter_Typo3AdapterInterface::getId()
	 */
	public function getId() {
		$this->getDriver()->getId();
	}

	/**
	 * Return the resource which is currently used.
	 * Depending on the locking method this can be a filename or a semaphore resource.
	 *
	 * @return mixed
	 * @see Tx_RsLock_Locking_Adapter_Typo3AdapterInterface::getResource()
	 */
	public function getResource() {
		if ($this->getDriver() instanceof Tx_RsLock_Locking_Driver_Typo3_DriverApiInterface) {
			return $this->getDriver()->getResource();
		}

		return FALSE;
	}

	/**
	 * Return the lock status.
	 *
	 * @return string Returns TRUE if lock is acquired, otherwise FALSE.
	 * @see Tx_RsLock_Locking_Adapter_Typo3AdapterInterface::getLockStatus()
	 */
	public function getLockStatus() {
		return $this->getDriver()->isAcquired();
	}

	/**
	 * Sets the facility (extension name) for the syslog entry.
	 *
	 * @param string $syslogFacility
	 * @return void
	 * @see Tx_RsLock_Locking_Adapter_Typo3AdapterInterface::setSyslogFacility()
	 */
	public function setSyslogFacility($syslogFacility) {
		$this->getLocker()->setSyslogFacility($syslogFacility);
	}

	/**
	 * Enable / disable logging.
	 *
	 * @param boolean $isLoggingEnabled TRUE to enable, FALSE to disable.
	 * @return void
	 * @see Tx_RsLock_Locking_Adapter_Typo3AdapterInterface::setEnableLogging()
	 */
	public function setEnableLogging($isLoggingEnabled) {
		$this->setEnableSysLogging($isLoggingEnabled);
	}

	/**
	 * Adds a common log entry for this locking API using t3lib_div::sysLog().
	 * Example: 01-01-13 20:00 - cms: Locking [simple::0aeafd2a67a6bb8b9543fb9ea25ecbe2]: Acquired
	 *
	 * @param string  $message  The message to be logged.
	 * @param integer $severity Severity - 0 is info (default), 1 is notice, 2 is warning, 3 is error, 4 is fatal error.
	 * @return void
	 * @see Tx_RsLock_Locking_Adapter_Typo3AdapterInterface::sysLog()
	 */
	public function sysLog($message, $severity = 0) {
		$this->getLocker()->_log($message, $severity);
	}

}
