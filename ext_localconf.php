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
 * Configuration of the rs_lock package.
 *
 * @package    rs_lock
 * @license    http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */

// access restriction
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

// X-Class implementation for TYPO3\CMS\Core\Locking\Locker class
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\\CMS\\Core\\Locking\\Locker'] = array(
	'className' => 'Rheinschafe\\RsLock\\Locking\\Adapter\\Typo3Adapter'
);

// basic driver mapping
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$_EXTKEY]['driverMapping'] = array(
	'simple'    => 'Rheinschafe\\RsLock\\Locking\\Driver\\FileDriver',
	'file'      => 'Rheinschafe\\RsLock\\Locking\\Driver\\FileDriver',
	'flock'     => 'Rheinschafe\\RsLock\\Locking\\Driver\\FileFlockDriver',
	'semaphore' => 'Rheinschafe\\RsLock\\Locking\\Driver\\SemaphoreDriver',
	'mysql'     => 'Rheinschafe\\RsLock\\Locking\\Driver\\MySQLDriver',
);
