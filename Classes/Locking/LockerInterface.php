<?php

namespace Rheinschafe\RsLock\Locking;

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
 * Locker interface.
 *
 * @package    rs_lock
 * @subpackage Locking
 * @license    http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
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
