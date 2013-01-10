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
 * Typo3 'locker-wrapper' to fit function 'instanceof' t3lib_lock.
 * X-Class must extend t3lib_lock!
 *
 * @package    rs_lock
 * @subpackage Locking
 * @license    http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 * @author     Daniel Hürtgen <huertgen@rheinschafe.de>
 */
class Tx_RsLock_Locking_Typo3Locker extends t3lib_lock implements Tx_RsLock_Locking_LockerInterface {

	/**
	 * @var Tx_RsLock_Locking_Locker
	 */
	private $locker;

	/**
	 * Constructor.
	 *
	 * @param mixed                                           $id     Unique id used for locking.
	 * @param string|Tx_RsLock_Locking_Driver_DrvierInterface $driver Driver class object or string.
	 * @param null                                            $loops  Times a lock is tried to acuqire.
	 * @param null                                            $steps  Milliseconds to sleep between looping.
	 * @return Tx_RsLock_Locking_Typo3Locker
	 */
	public function __construct($id, $driver, $loops = NULL, $steps = NULL) {
		$this->locker = t3lib_div::makeInstance('Tx_RsLock_Locking_Locker', $id, $driver, $loops, $steps);
	}

	/**
	 * Destructor.
	 * Perform shutdown tasks.
	 * Do nothing, already done by locker instance.
	 *
	 * @return void
	 */
	public function __destruct() {
	}

	/**
	 * Get driver.
	 *
	 * @return Tx_RsLock_Locking_Driver_DriverInterface
	 */
	public function getDriver() {
		return $this->locker->getDriver();
	}

	/**
	 * Acquire lock.
	 *
	 * @return boolean
	 */
	public function acquire() {
		$this->getDriver()->acquire();
	}

	/**
	 * Release lock.
	 *
	 * @return boolean
	 */
	public function release() {
		$this->getDriver()->release();
	}

	/**
	 * Is lock aquired?
	 *
	 * @return boolean
	 */
	public function isAcquired() {
		$this->getDriver()->isAcquired();
	}

	/**
	 * Perform shutdown tasks.
	 *
	 * @return void
	 */
	public function shutdown() {
		$this->getDriver()->shutdown();
	}

	/**
	 * Returns string with driver name.
	 *
	 * @return string
	 */
	public function getType() {
		$this->getDriver()->getType();
	}

	public function getMethod() {
		return $this->getType();
	}

	public function getLockStatus() {
		return $this->isAcquired();
	}

	public function getId() {
		if (method_exists($this->getDriver(), 'getId')) {
			return $this->getDriver()->getId();
		}
		return FALSE;
	}

	public function getResource() {
		if (method_exists($this->getDriver(), 'getResource')) {
			return $this->getDriver()->getResource();
		}
		return FALSE;
	}

	public function setSyslogFacility($syslogFacility) {
//		parent::setSyslogFacility($syslogFacility);
	}

	public function setEnableLogging($isLoggingEnabled) {
//		parent::setEnableLogging($isLoggingEnabled);
	}

	public function sysLog($message, $severity = 0) {
//		parent::sysLog($message, $severity);
	}

}
