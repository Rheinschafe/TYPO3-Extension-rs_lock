<?php

if (!is_array($GLOBALS['TYPO3_CONF_VARS']['EXT']['runtimeActivatedPackages'])) {
	$GLOBALS['TYPO3_CONF_VARS']['EXT']['runtimeActivatedPackages'] = array();
}

$GLOBALS['TYPO3_CONF_VARS']['EXT']['runtimeActivatedPackages'][] = 'phpunit';
$GLOBALS['TYPO3_CONF_VARS']['EXT']['runtimeActivatedPackages'][] = 'rs_lock';
