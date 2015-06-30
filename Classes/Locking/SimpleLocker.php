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
 * Locker wrapper class for single (simple) locking.
 *
 * @package    rs_lock
 * @subpackage Locking
 * @author     Daniel Hürtgen <huertgen@rheinschafe.de>
 */
class SimpleLocker extends AbstractLocker implements SimpleLockerInterface {

	/**
	 * @var DriverInterface
	 */
	private $driver;

	/**
	 * Constructor.
	 *
	 * @param mixed                  $id      Unique id used for locking.
	 * @param string|DriverInterface $driver  Driver class object or string.
	 * @param string                 $context Locking context/prefix.
	 * @param null                   $loops   Times a lock is tried to acuqire.
	 * @param null                   $steps   Milliseconds to sleep between looping.
	 * @throws \InvalidArgumentException
	 * @return SimpleLocker
	 * @see Tx_RsLock_Locking_LockerInterface::__construct()
	 */
	public function __construct($id, $driver, $context, $loops = NULL, $steps = NULL) {
		if ($driver instanceof DriverInterface) {
			$this->driver = $driver;
		} else if (!$driver instanceof DriverInterface && is_string($driver)) {
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
			throw new \InvalidArgumentException(
				sprintf(
					'Invalid driver "%s" given. Driver must be implement "Rheinschafe\\RsLock\\Locking\\Driver\\DriverInterface".',
					(is_object($driver) ? get_class($driver) : $driver)
				)
			);
		}
	}

	/**
	 * Get driver.
	 *
	 * @return DriverInterface
	 * @see Locker::getDriver()
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
		GeneralUtility::sysLog(
			'[' . $this->getDriver()->getContext() . ' : ' . $this->getDriver()->getIdHash() . '] ' . $message,
			$this->getSyslogFacility(),
			$severity
		);
	}

}
