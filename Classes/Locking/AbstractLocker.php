<?php

namespace Rheinschafe\RsLock\Locking;

/*
 * This file is part of the Rheinschafe/Lock project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use Rheinschafe\RsLock\Locking\Driver\DriverInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Abstract locker wrapper class.
 *
 * @package    rs_lock
 * @subpackage Locking
 * @author     Daniel Hürtgen <huertgen@rheinschafe.de>
 */
abstract class AbstractLocker implements LockerInterface {

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
	 * @throws \InvalidArgumentException
	 * @todo maybe use typo3 services to manage overloading
	 * @return DriverInterface
	 */
	protected function _getDriverInstance($name, array $args = array()) {
		$driverMapping = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['rs_lock']['driverMapping'];

		$driverClass = 'Rheinschafe\\RsLock\\Locking\\Driver\\FileDriver';
		if (isset($driverMapping[$name])) {
			$driverClass = $driverMapping[$name];

			$r = new \ReflectionClass($driverClass);
			if (!$r->implementsInterface('Rheinschafe\RsLock\Locking\Driver\DriverInterface')) {
				throw new \InvalidArgumentException(
					sprintf(
						'Class "%s" must implement "Rheinschafe\\RsLock\\Locking\\Driver\\DriverInterface".',
						$driverClass
					)
				);
			}
		}

		/** @var $driver DriverInterface */
		array_unshift($args, $driverClass);
		$driver = call_user_func_array(
			array(
				'TYPO3\\CMS\\Core\\Utility\\GeneralUtility',
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
	 * @see LockerInterface::getSyslogFacility()
	 */
	public function getSyslogFacility() {
		$this->_sysLoggingFacility;
	}

	/**
	 * Sets the facility (extension name) for the syslog entry.
	 *
	 * @param string $sysLogFacility
	 * @return void
	 * @see LockerInterface::setSyslogFacility()
	 */
	public function setSyslogFacility($sysLogFacility) {
		$this->_sysLoggingFacility = (string) $sysLogFacility;
	}

	/**
	 * Enable / disable logging.
	 *
	 * @param boolean $state TRUE to enable, FALSE to disable.
	 * @return void
	 * @see LockerInterface::setEnableSysLogging()
	 */
	public function setEnableSysLogging($state = TRUE) {
		$this->_doSysLogging = (boolean) $state;
	}

	/**
	 * Return if syslogging is enabled.
	 *
	 * @return boolean
	 * @see LockerInterface::isSysLoggingEnabled()
	 */
	public function isSysLoggingEnabled() {
		return $this->_doSysLogging;
	}

	/**
	 * Adds a common log entry for this locking API using GeneralUtility::sysLog().
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
		GeneralUtility::sysLog($message, $this->getSyslogFacility(), $severity);
	}

}
