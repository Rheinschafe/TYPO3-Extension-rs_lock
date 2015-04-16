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
	'title' => 'RS | Advanced TYPO3 Locking',
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
	'_md5_values_when_last_written' => '',
);

?>
