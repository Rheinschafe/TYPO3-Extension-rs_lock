<?php

/*
 * This file is part of the Rheinschafe/Lock project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

/*
 * copy this file to the typo3 root folder to run tests.
 */

define('TYPO3_MODE', 'FE');

require __DIR__ . '/typo3/sysext/core/Classes/Core/Bootstrap.php';

\TYPO3\CMS\Core\Core\Bootstrap::getInstance()
                              ->baseSetup('')
                              ->startOutputBuffering()
                              ->loadConfigurationAndInitialize()
                              ->loadTypo3LoadedExtAndExtLocalconf(TRUE)
                              ->applyAdditionalConfigurationSettings()
                              ->initializeTypo3DbGlobal()
                              ->endOutputBufferingAndCleanPreviousOutput();

//Test Modes
$testMode = 1;
$all = 1;
$message = '';

if($testMode == 1 || $all){
    /**
     * Require a Exclusive lock, and then release it
     */
    $message .= "Testing Exclusive Lock...";
    $lockObjectOne = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('Rheinschafe\\RsLock\\Locking\\Locker', "ExclusiveLockTest");
    /** @var \Rheinschafe\RsLock\Locking\Locker $lockObjectOne */
    $lock = $lockObjectOne->acquireExclusiveLock();
    $isLocked = $lockObjectOne->isLocked();
    $lockObjectOne->release();
    $message .= "done!\n";
}

if($testMode == 2 || $all){
    /**
     * Require a Shared lock, and then release it
     */
    $message .= "Testing Shared Lock...";
    $lockObjectTwo = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('Rheinschafe\\RsLock\\Locking\\Locker', "SharedLocktest");
    /** @var \Rheinschafe\RsLock\Locking\Locker $lockObjectTwo */
    $lock = $lockObjectTwo->acquireSharedLock();
    $isLocked = $lockObjectTwo->isLocked();
    $lockObjectTwo->release();
    $message .= "done!\n";
}

if($testMode == 3 || $all){
    /**
     * Require a Shared lock, and then require it
     */
    $message .= "Testing Simultanous Shared Lock...";
    $lockObjectThreeOne = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('Rheinschafe\\RsLock\\Locking\\Locker', "SharedSimultanousLocktest");
    /** @var \Rheinschafe\RsLock\Locking\Locker $lockObjectTwo */
    $lock = $lockObjectThreeOne->acquireSharedLock();
    $isLocked = $lockObjectThreeOne->isLocked();

    $lockObjectThreeTwo = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('Rheinschafe\\RsLock\\Locking\\Locker', "SharedSimultanousLocktest");
    /** @var \Rheinschafe\RsLock\Locking\Locker $lockObjectTwo */
    $lock = $lockObjectThreeTwo->acquireSharedLock();
    $isLocked = $lockObjectThreeTwo->isLocked();

    $lockObjectThreeOne->release();
    $lockObjectThreeTwo->release();
    $message .= "done!\n";
}

header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: max-age=0');
echo $message;
