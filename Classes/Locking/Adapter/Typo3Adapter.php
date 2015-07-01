<?php

namespace Rheinschafe\RsLock\Locking\Adapter;

/*
 * This file is part of the Rheinschafe/Lock project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use Rheinschafe\RsLock\Locking\Driver\DriverApiInterface;
use Rheinschafe\RsLock\Locking\Driver\DriverInterface;
use Rheinschafe\RsLock\Locking\SimpleLockerInterface;
use TYPO3\CMS\Core\Locking\Locker as CoreLocker;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Typo3 adapter to fit function 'instanceof' \TYPO3\CMS\Core\Locking\Locker.
 *  X-Class must extend \TYPO3\CMS\Core\Locking\Locker!
 *
 * @package    rs_lock
 * @subpackage Locking/Adapter
 * @author     Daniel Hürtgen <huertgen@rheinschafe.de>
 */
class Typo3Adapter extends CoreLocker implements Typo3AdapterInterface {

	/**
	 * Get real simple locker object.
	 *
	 * @var SimpleLockerInterface
	 */
	private $locker;

	/**
	 * Constructor.
	 *
	 * @param mixed                  $id     Unique id used for locking.
	 * @param string|DriverInterface $driver Driver class object or string.
	 * @param null                   $loops  Times a lock is tried to acuqire.
	 * @param null                   $steps  Milliseconds to sleep between looping.
	 * @return Typo3Adapter
	 */
	public function __construct($id, $driver, $loops = NULL, $steps = NULL) {
		$context = $this->_determineLockingContext();
		$this->locker = GeneralUtility::makeInstance(
			'Rheinschafe\\RsLock\\Locking\\SimpleLocker',
			$id,
			$driver,
			$context,
			$loops,
			$steps
		);
	}

	/**
	 * Tries to resolve the current locking context.
	 *
	 * @return string
	 * @todo hate me :( but need context for unique id hash generation
	 */
	protected function _determineLockingContext() {
		// set default value
		$default = $this->_getDefaultLockingContext();
		$context = $default;

		// tries by backtrace
		$contextMapping = $this->_getLockingContextMapping();
		if (version_compare(PHP_VERSION, '5.3.6', '<')) {
			$bt = debug_backtrace(FALSE);
		} else {
			$bt = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
		}
		if (is_array($bt)) {
			foreach ($bt as $btItem) {
				if (!isset($btItem['class']) || !isset($btItem['function']) || !isset($btItem['type'])) {
					continue;
				}

				$matchAgainstArray = array(
					'class'    => $btItem['class'],
					'function' => $btItem['function']
				);
				$matchedContext = array_search($matchAgainstArray, $contextMapping, TRUE);
				if ($matchedContext !== FALSE) {
					$context = $contextMapping[$matchedContext]['class'] . '->' . $contextMapping[$matchedContext]['function'];
					break;
				}
			}
		}

		return $context;
	}

	/**
	 * Returns the default locking context/prefix.
	 *
	 * @return string
	 */
	protected function _getDefaultLockingContext() {
		return 'typo3-legacy-api';
	}

	/**
	 * Returns an array of context/prefix mapping against debug_backtrage.
	 *  Use the following format:
	 *   array(
	 *     'class'    => 'className to search for',
	 *     'function' => 'function call to search for',
	 *   )
	 *
	 * @return array
	 */
	protected function _getLockingContextMapping() {
		return array(
			array(
				'class'    => 'TYPO3\\CMS\\Core\\Utility\\GeneralUtility',
				'function' => 'sysLog'
			),
			array(
				'class'    => 'TYPO3\\CMS\\Frontend\\Controller\\TypoScriptFrontendController',
				'function' => 'getFromCache'
			),
			array(
				'class'    => 'TYPO3\\CMS\\Core\\Mail\\MboxTransport',
				'function' => 'send'
			),
		);
	}

	/**
	 * Get real locker object.
	 *
	 * @return SimpleLockerInterface
	 */
	public function getLocker() {
		return $this->locker;
	}

	/**
	 * Get driver object.
	 *
	 * @return DriverInterface
	 */
	public function getDriver() {
		return $this->locker->getDriver();
	}

	/**
	 * Acquire lock.
	 *  Tries to acquire locking. It is very important, that the lock will be generated. If something went wrong,
	 *  throw an runtime exception, but do NOT return FALSE on fail!
	 *
	 * @return boolean Return TRUE, if lock was acquired without waiting for other clients/instances, otherwise, if the client
	 *                 was waiting, return FALSE.
	 * @see Typo3AdapterInterface::acquire()
	 */
	public function acquire() {
		$this->getDriver()->acquire();
	}

	/**
	 * Release lock.
	 *
	 * @return boolean TRUE if locked was release, otherwise throw lock exception.
	 * @see Typo3AdapterInterface::release()
	 */
	public function release() {
		$this->getDriver()->release();
	}

	/**
	 * Return the unique identifier used for locking.
	 *
	 * @return string
	 * @see Typo3AdapterInterface::getMethod()
	 */
	public function getMethod() {
		return $this->getDriver()->getType();
	}

	/**
	 * Return the unique identifier used for locking.
	 *
	 * @return string
	 * @see Typo3AdapterInterface::getId()
	 */
	public function getId() {
		$this->getDriver()->getId();
	}

	/**
	 * Return the resource which is currently used.
	 * Depending on the locking method this can be a filename or a semaphore resource.
	 *
	 * @return mixed
	 * @see Typo3AdapterInterface::getResource()
	 */
	public function getResource() {
		if ($this->getDriver() instanceof DriverApiInterface) {
			return $this->getDriver()->getResource();
		}

		return FALSE;
	}

	/**
	 * Return the lock status.
	 *
	 * @return string Returns TRUE if lock is acquired, otherwise FALSE.
	 * @see Typo3AdapterInterface::getLockStatus()
	 */
	public function getLockStatus() {
		return $this->getDriver()->isAcquired();
	}

	/**
	 * Sets the facility (extension name) for the syslog entry.
	 *
	 * @param string $syslogFacility
	 * @return void
	 * @see Typo3AdapterInterface::setSyslogFacility()
	 */
	public function setSyslogFacility($syslogFacility) {
		$this->getLocker()->setSyslogFacility($syslogFacility);
	}

	/**
	 * Enable / disable logging.
	 *
	 * @param boolean $isLoggingEnabled TRUE to enable, FALSE to disable.
	 * @return void
	 * @see Typo3AdapterInterface::setEnableLogging()
	 */
	public function setEnableLogging($isLoggingEnabled) {
		$this->setEnableSysLogging($isLoggingEnabled);
	}

	/**
	 * Adds a common log entry for this locking API using t3lib_div::sysLog().
	 * Example: 01-01-13 20:00 - cms: Locking [simple::0aeafd2a67a6bb8b9543fb9ea25ecbe2]: Acquired
	 *
	 * @param string  $message  The message to be logged.
	 * @param integer $severity Severity - 0 is info (default), 1 is notice, 2 is warning, 3 is error, 4 is fatal error.
	 * @return void
	 * @see Typo3AdapterInterface::sysLog()
	 */
	public function sysLog($message, $severity = 0) {
		$this->getLocker()->_log($message, $severity);
	}

}
