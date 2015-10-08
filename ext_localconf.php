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
	'className' => 'Rheinschafe\\RsLock\\Locking\\Locker'
);

if (!is_array($GLOBALS['TYPO3_CONF_VARS']['SYS']['locking'])) {
	$GLOBALS['TYPO3_CONF_VARS']['SYS']['locking'] = array();
}

if (!is_array($GLOBALS['TYPO3_CONF_VARS']['SYS']['locking']['lockingConfigurations'])) {
	$GLOBALS['TYPO3_CONF_VARS']['SYS']['locking']['lockingConfigurations'] = array();
}

if (!is_array($GLOBALS['TYPO3_CONF_VARS']['SYS']['locking']['lockingConfigurations']['redis'])) {
	$GLOBALS['TYPO3_CONF_VARS']['SYS']['locking']['lockingConfigurations']['redis'] = array(
		'enable' => FALSE
	);
}
