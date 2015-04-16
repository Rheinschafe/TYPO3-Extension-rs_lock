<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "rs_lock".
 *
 * Auto generated 15-01-2015 11:09
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array(
	'title' => 'RS | Advanced Locking',
	'description' => 'Features TYPO3 with an advanced and rewritten locking. TYPO3 offers locking methods are: simple (is_file method), flock (filesystem locking attributes) and semaphore (System V IPC Key). These three drivers are rewritten into an adaptive driver api to boost performance and abstraction. There is also a new driver for MySQL Locking through an InnoDB table called \'sys_lock\'. Very extendable structure, even if this is an really really initial version. Try & Fail :-)',
	'category' => 'fe',
	'author' => 'Daniel Hürtgen',
	'author_email' => 'huertgen@rheinschafe.de',
	'author_company' => 'Rheinschafe GmbH',
	'shy' => '',
	'priority' => '',
	'module' => '',
	'state' => 'alpha',
	'internal' => '',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearCacheOnLoad' => 1,
	'lockType' => '',
	'version' => '0.2.0',
	'constraints' => array(
		'depends' => array(
			'typo3' => '4.5-0.0.0',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:20:{s:16:"ext_autoload.php";s:4:"8767";s:12:"ext_icon.gif";s:4:"8b44";s:17:"ext_localconf.php";s:4:"e431";s:14:"ext_tables.sql";s:4:"cd34";s:38:"Classes/Legacy/class.ux_t3lib_lock.php";s:4:"868f";s:34:"Classes/Locking/AbstractLocker.php";s:4:"7739";s:35:"Classes/Locking/LockerInterface.php";s:4:"e9d8";s:32:"Classes/Locking/SimpleLocker.php";s:4:"fb68";s:41:"Classes/Locking/SimpleLockerInterface.php";s:4:"7af0";s:40:"Classes/Locking/Adapter/Typo3Adapter.php";s:4:"a359";s:49:"Classes/Locking/Adapter/Typo3AdapterInterface.php";s:4:"13bf";s:41:"Classes/Locking/Driver/AbstractDriver.php";s:4:"5fd9";s:45:"Classes/Locking/Driver/AbstractFileDriver.php";s:4:"5e3a";s:46:"Classes/Locking/Driver/AbstractTypo3Driver.php";s:4:"c64d";s:45:"Classes/Locking/Driver/DriverApiInterface.php";s:4:"4984";s:42:"Classes/Locking/Driver/DriverInterface.php";s:4:"8234";s:37:"Classes/Locking/Driver/FileDriver.php";s:4:"0a5e";s:42:"Classes/Locking/Driver/FileFlockDriver.php";s:4:"8684";s:38:"Classes/Locking/Driver/MySQLDriver.php";s:4:"4f9e";s:42:"Classes/Locking/Driver/SemaphoreDriver.php";s:4:"5328";}',
);

?>
