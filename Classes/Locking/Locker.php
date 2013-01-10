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
 * Locker wrapper class.
 *
 * @package    rs_lock
 * @subpackage Locking
 * @license    http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 * @author     Daniel Hürtgen <huertgen@rheinschafe.de>
 */
class Tx_RsLock_Locking_Locker implements Tx_RsLock_Locking_LockerInterface {

	/**
	 * @var Tx_RsLock_Locking_Driver_DriverInterface
	 */
	private $driver;

	/**
	 * Constructor.
	 *
	 * @param mixed                                           $id     Unique id used for locking.
	 * @param string|Tx_RsLock_Locking_Driver_DrvierInterface $driver Driver class object or string.
	 * @param null                                            $loops  Times a lock is tried to acuqire.
	 * @param null                                            $steps  Milliseconds to sleep between looping.
	 * @throws InvalidArgumentException
	 * @return Tx_RsLock_Locking_Locker
	 * @see Tx_RsLock_Locking_LockerInterface::__construct()
	 */
	public function __construct($id, $driver, $loops = NULL, $steps = NULL) {
		$this->id = $id;

		if ($driver instanceof Tx_RsLock_Locking_Driver_DriverInterface) {
			$this->driver = $driver;
		} else if (!$driver instanceof Tx_RsLock_Locking_Driver_DriverInterface && is_string($driver)) {
			$this->driver = $this->_getDriverInstance($driver, array(
																	$id,
																	$loops,
																	$steps
															   ));
		} else {
			throw new InvalidArgumentException('Invalid driver given.');
		}
	}

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
				throw new InvalidArgumentException(sprintf('Class "%s" must implement "Tx_RsLock_Locking_Driver_DriverInterface".',
														   $driverClass));
			}
		}

		/** @var $driver Tx_RsLock_Locking_Driver_DriverInterface */
		array_unshift($args, $driverClass);
		$driver = call_user_func_array(array(
											't3lib_div',
											'makeInstance'
									   ), $args);

		return $driver;
	}

	/**
	 * Destructor.
	 * Perform shutdown tasks.
	 *
	 * @return void
	 * @see Tx_RsLock_Locking_LockerInterface::__destruct()
	 */
	public function __destruct() {
		$this->shutdown();
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
	 * Returns string with driver name.
	 *
	 * @return string
	 * @see Tx_RsLock_Locking_Driver_DriverApiInterface::getType()
	 */
	public function getType() {
		return $this->driver->getType();
	}

	/**
	 * Acquire lock.
	 *
	 * @return boolean
	 * @see Tx_RsLock_Locking_Driver_DriverApiInterface::acquire()
	 */
	public function acquire() {
		return $this->driver->acquire();
	}

	/**
	 * Release lock.
	 *
	 * @return boolean
	 * @see Tx_RsLock_Locking_Driver_DriverApiInterface::release()
	 */
	public function release() {
		return $this->driver->release();
	}

	/**
	 * Is lock aquired?
	 *
	 * @return boolean
	 * @see Tx_RsLock_Locking_Driver_DriverApiInterface::isAcquired()
	 */
	public function isAcquired() {
		return $this->driver->isAcquired();
	}

	/**
	 * Perform shutdown tasks.
	 *
	 * @return void
	 * @see Tx_RsLock_Locking_Driver_DriverApiInterface::shutdown()
	 */
	public function shutdown() {
		$this->driver->shutdown();
	}

}
