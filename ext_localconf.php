<?php

/*
 * This file is part of the Rheinschafe/Lock project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

/**
 * Configuration of the rs_lock package.
 *
 * @package    rs_lock
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
