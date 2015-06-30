<?php

namespace Rheinschafe\RsLock\Locking\Adapter;

/*
 * This file is part of the Rheinschafe/Lock project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

/**
 * Legacy TYPO3-Adapter interface.
 *
 * @package    rs_lock
 * @subpackage Locking/Adapter
 * @author     Daniel Hürtgen <huertgen@rheinschafe.de>
 */
interface Typo3AdapterInterface {

	/**
	 * Get real locker object.
	 *
	 * @return Typo3AdapterInterface
	 */
	public function getLocker();

	/**
	 * Get driver object.
	 *
	 * @return Typo3AdapterInterface
	 */
	public function getDriver();

	/**
	 * Acquire lock.
	 *  Tries to acquire locking. It is very important, that the lock will be generated. If something went wrong,
	 *  throw an runtime exception, but do NOT return FALSE on fail!
	 *
	 * @return boolean Return TRUE, if lock was acquired without waiting for other clients/instances, otherwise, if the client
	 *                 was waiting, return FALSE.
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
