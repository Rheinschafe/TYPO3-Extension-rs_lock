<?php
// DO NOT CHANGE THIS FILE! It is automatically generated by extdeveval::buildAutoloadRegistry.
// This file was generated on 2013-01-10 17:08

$extensionPath = t3lib_extMgm::extPath('rs_lock');
$extensionClassesPath = t3lib_extMgm::extPath('rs_lock') . 'Classes/';
return array(
	'tx_rslock_locking_locker' => $extensionClassesPath . 'Locking/Locker.php',
	'tx_rslock_locking_lockerinterface' => $extensionClassesPath . 'Locking/LockerInterface.php',
	'tx_rslock_locking_typo3locker' => $extensionClassesPath . 'Locking/Typo3Locker.php',
	'tx_rslock_locking_driver_abstractdriver' => $extensionClassesPath . 'Locking/Driver/AbstractDriver.php',
	'tx_rslock_locking_driver_abstracttypo3driver' => $extensionClassesPath . 'Locking/Driver/AbstractTypo3Driver.php',
	'tx_rslock_locking_driver_driverapiinterface' => $extensionClassesPath . 'Locking/Driver/DriverApiInterface.php',
	'tx_rslock_locking_driver_driverinterface' => $extensionClassesPath . 'Locking/Driver/DriverInterface.php',
	'tx_rslock_locking_driver_filedriver' => $extensionClassesPath . 'Locking/Driver/FileDriver.php',
	'tx_rslock_locking_driver_fileflockdriver' => $extensionClassesPath . 'Locking/Driver/FileFlockDriver.php',
	'tx_rslock_locking_driver_semaphoredriver' => $extensionClassesPath . 'Locking/Driver/SemaphoreDriver.php',
);
?>