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
 * Abstract locker wrapper class.
 *
 * @package    rs_lock
 * @subpackage Locking
 * @license    http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 * @author     Daniel Hürtgen <huertgen@rheinschafe.de>
 */
abstract class Tx_RsLock_Locking_AbstractLocker implements Tx_RsLock_Locking_LockerInterface {

	/**
	 * SysLogging enabled?
	 *
	 * @var boolean
	 */
	protected $_doSysLogging = FALSE;

	/**
	 * Syslog facility.
	 *
	 * @var string
	 */
	protected $_sysLoggingFacility = 'rs_lock';

	/**
	 * Instance driver instance by name.
	 *
	 * @param string $name String with driver-name.
	 * @param array  $args Args passed to driver constructor (required: $id, optional: $loops, $steps)
	 * @throws InvalidArgumentException
	 * @todo maybe use typo3 services to manage overloading
	 * @return Tx_RsLock_Locking_Driver_DriverInterface
	 */
	protected function _getDriverInstance($name, array $args = array()) {
		$driverMapping = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['rs_lock']['driverMapping'];

		$driverClass = 'Tx_RsLock_Locking_Driver_FileDriver';
		if (isset($driverMapping[$name])) {
			$driverClass = $driverMapping[$name];

			$r = new ReflectionClass($driverClass);
			if (!$r->implementsInterface('Tx_RsLock_Locking_Driver_DriverInterface')) {
				throw new InvalidArgumentException(
					sprintf(
						'Class "%s" must implement "Tx_RsLock_Locking_Driver_DriverInterface".',
						$driverClass
					)
				);
			}
		}

		/** @var $driver Tx_RsLock_Locking_Driver_DriverInterface */
		array_unshift($args, $driverClass);
		$driver = call_user_func_array(
			array(
				't3lib_div',
				'makeInstance'
			),
			$args
		);

		return $driver;
	}

	/**
	 * Get the facility (extension name) for the syslog entry.
	 *
	 * @return string
	 * @see Tx_RsLock_Locking_LockerInterface::getSyslogFacility()
	 */
	public function getSyslogFacility() {
		$this->_sysLoggingFacility;
	}

	/**
	 * Sets the facility (extension name) for the syslog entry.
	 *
	 * @param string $sysLogFacility
	 * @return void
	 * @see Tx_RsLock_Locking_LockerInterface::setSyslogFacility()
	 */
	public function setSyslogFacility($sysLogFacility) {
		$this->_sysLoggingFacility = (string) $sysLogFacility;
	}

	/**
	 * Enable / disable logging.
	 *
	 * @param boolean $state TRUE to enable, FALSE to disable.
	 * @return void
	 * @see Tx_RsLock_Locking_LockerInterface::setEnableSysLogging()
	 */
	public function setEnableSysLogging($state = TRUE) {
		$this->_doSysLogging = (boolean) $state;
	}

	/**
	 * Return if syslogging is enabled.
	 *
	 * @return boolean
	 * @see Tx_RsLock_Locking_LockerInterface::isSysLoggingEnabled()
	 */
	public function isSysLoggingEnabled() {
		return $this->_doSysLogging;
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
		t3lib_div::sysLog($message, $this->getSyslogFacility(), $severity);
	}

}
