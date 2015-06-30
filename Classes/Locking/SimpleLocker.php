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
 * Locker wrapper class for single (simple) locking.
 *
 * @package    rs_lock
 * @subpackage Locking
 * @license    http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 * @author     Daniel Hürtgen <huertgen@rheinschafe.de>
 */
class Tx_RsLock_Locking_SimpleLocker extends Tx_RsLock_Locking_AbstractLocker implements Tx_RsLock_Locking_SimpleLockerInterface {

	/**
	 * @var Tx_RsLock_Locking_Driver_DriverInterface
	 */
	private $driver;

	/**
	 * Constructor.
	 *
	 * @param mixed                                           $id      Unique id used for locking.
	 * @param string|Tx_RsLock_Locking_Driver_DrvierInterface $driver  Driver class object or string.
	 * @param string                                          $context Locking context/prefix.
	 * @param null                                            $loops   Times a lock is tried to acuqire.
	 * @param null                                            $steps   Milliseconds to sleep between looping.
	 * @throws InvalidArgumentException
	 * @return Tx_RsLock_Locking_SimpleLocker
	 * @see Tx_RsLock_Locking_LockerInterface::__construct()
	 */
	public function __construct($id, $driver, $context, $loops = NULL, $steps = NULL) {
		if ($driver instanceof Tx_RsLock_Locking_Driver_DriverInterface) {
			$this->driver = $driver;
		} else if (!$driver instanceof Tx_RsLock_Locking_Driver_DriverInterface && is_string($driver)) {
			$this->driver = $this->_getDriverInstance(
				$driver,
				array(
					$this,
					$id,
					$context,
					$loops,
					$steps
				)
			);
		} else {
			throw new InvalidArgumentException(
				sprintf(
					'Invalid driver "%s" given. Driver must be implement "Tx_RsLock_Locking_Driver_DriverInterface".',
					(is_object($driver) ? get_class($driver) : $driver)
				)
			);
		}
	}

	/**
	 * Get driver.
	 *
	 * @return Tx_RsLock_Locking_Driver_DriverInterface
	 * @see Tx_RsLock_Locking_LockerInterface::getDriver()
	 */
	public function getDriver() {
		return $this->driver;
	}

	/**
	 * Adds a common log entry for this locking API using t3lib_div::sysLog().
	 * Example: 01-01-13 20:00 - cms: Locking [simple::0aeafd2a67a6bb8b9543fb9ea25ecbe2]: Acquired
	 *
	 * @param string  $message  The message to be logged.
	 * @param integer $severity Severity - 0 is info (default), 1 is notice, 2 is warning, 3 is error, 4 is fatal error.
	 * @return void
	 */
	public function _log($message, $severity = 0) {
		if (!$this->isSysLoggingEnabled()) {
			return;
		}
		t3lib_div::sysLog(
			'[' . $this->getDriver()->getContext() . ' : ' . $this->getDriver()->getIdHash() . '] ' . $message,
			$this->getSyslogFacility(),
			$severity
		);
	}

}
