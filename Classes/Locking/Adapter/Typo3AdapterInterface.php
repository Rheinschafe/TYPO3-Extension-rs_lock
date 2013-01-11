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
 * Legacy TYPO3-Adapter interface.
 *
 * @package    rs_lock
 * @subpackage Locking/Adapter
 * @license    http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 * @author     Daniel Hürtgen <huertgen@rheinschafe.de>
 */
interface Tx_RsLock_Locking_Adapter_Typo3AdapterInterface {

	/**
	 * Get real locker object.
	 *
	 * @return Tx_RsLock_Locking_SimpleLockerInterface
	 */
	public function getLocker();

	/**
	 * Get driver object.
	 *
	 * @return Tx_RsLock_Locking_Driver_DriverInterface
	 */
	public function getDriver();

	/**
	 * Acquire lock.
	 *  Tries to acquire locking. It is very important, that the lock will be generated. If something went wrong,
	 *  throw an runtime exception, but do NOT return FALSE on fail!
	 *
	 * @return boolean Return TRUE, if lock was acquired without waiting for other clients/instances, otherwise, if the client was waiting, return FALSE.
	 */
	public function acquire();

	/**
	 * Release lock.
	 *
	 * @return boolean TRUE if locked was release, otherwise throw lock exception.
	 */
	public function release();

	/**
	 * Return the locking method which is currently used.
	 *
	 * @return string
	 */
	public function getMethod();

	/**
	 * Return the unique identifier used for locking.
	 *
	 * @return string
	 */
	public function getId();

	/**
	 * Return the resource which is currently used.
	 * Depending on the locking method this can be a filename or a semaphore resource.
	 *
	 * @return mixed
	 */
	public function getResource();

	/**
	 * Return the lock status.
	 *
	 * @return string Returns TRUE if lock is acquired, otherwise FALSE.
	 */
	public function getLockStatus();

	/**
	 * Sets the facility (extension name) for the syslog entry.
	 *
	 * @param string $syslogFacility
	 * @return void
	 */
	public function setSyslogFacility($syslogFacility);

	/**
	 * Enable / disable logging.
	 *
	 * @param boolean $isLoggingEnabled TRUE to enable, FALSE to disable.
	 * @return void
	 */
	public function setEnableLogging($isLoggingEnabled);

	/**
	 * Adds a common log entry for this locking API using t3lib_div::sysLog().
	 * Example: 01-01-13 20:00 - cms: Locking [simple::0aeafd2a67a6bb8b9543fb9ea25ecbe2]: Acquired
	 *
	 * @param string  $message  The message to be logged.
	 * @param integer $severity Severity - 0 is info (default), 1 is notice, 2 is warning, 3 is error, 4 is fatal error.
	 * @return void
	 */
	public function sysLog($message, $severity = 0);

}
