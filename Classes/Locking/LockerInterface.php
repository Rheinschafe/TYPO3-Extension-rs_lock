<?php

namespace Rheinschafe\RsLock\Locking;

/*
 * This file is part of the Rheinschafe/Lock project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

/**
 * Locker interface.
 *
 * @package    rs_lock
 * @subpackage Locking
 * @author     Daniel Hürtgen <huertgen@rheinschafe.de>
 */
interface LockerInterface {

	/**
	 * Get the facility (extension name) for the syslog entry.
	 *
	 * @return string
	 */
	public function getSyslogFacility();

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
	 * @param boolean $state TRUE to enable, FALSE to disable.
	 * @return void
	 */
	public function setEnableSysLogging($state = TRUE);

	/**
	 * Return if syslogging is enabled.
	 *
	 * @return boolean
	 */
	public function isSysLoggingEnabled();

}
