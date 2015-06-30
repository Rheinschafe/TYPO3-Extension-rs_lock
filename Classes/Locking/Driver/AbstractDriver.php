<?php

namespace Rheinschafe\RsLock\Locking\Driver;

use Rheinschafe\RsLock\Locking\LockerInterface;
use TYPO3\CMS\Core\Service\AbstractService;
use TYPO3\CMS\Core\Utility\MathUtility;

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
 * Abstract Locking-Driver class.
 *
 * @package    rs_lock
 * @subpackage Locking/Driver
 * @license    http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 * @author     Daniel Hürtgen <huertgen@rheinschafe.de>
 */
abstract class AbstractDriver extends AbstractService implements DriverInterface {

	/**
	 * Unique identifier.
	 *
	 * @var mixed
	 */
	protected $_id;

	/**
	 * Number of retries, if locking fails.
	 *
	 * @var int
	 */
	protected $_retries = 150;

	/**
	 * Number of milliseconds wait between retries.
	 *
	 * @var int
	 */
	protected $_retryInterval = 200;

	/**
	 * Context/prefix to generate lock for.
	 *
	 * @var string
	 */
	protected $_context;

	/**
	 * Parent lock api class.
	 *
	 * @var LockerInterface
	 */
	protected $_locker;

	/**
	 * Got lock?
	 *
	 * @var boolean
	 */
	protected $_isAcquired = FALSE;

	/**
	 * Constructor.
	 *
	 * @param LockerInterface $locker
	 * @param string          $id
	 * @param string          $context
	 * @param int|null        $retries
	 * @param int|null        $retryInterval
	 * @return AbstractDriver
	 */
	public function __construct(LockerInterface $locker, $id, $context, $retries = NULL, $retryInterval = NULL) {
		$this->_locker = $locker;
		$this->_id = (string) $id;
		$this->_context = (string) $context;

		$this->setRetries($retries);
		$this->setRetryInterval($retryInterval);

		register_shutdown_function(
			array(
				$this,
				'shutdown'
			)
		);
	}

	/**
	 * Get parent lock api class.
	 *
	 * @return LockerInterface
	 */
	public function getLocker() {
		return $this->_locker;
	}

	/**
	 * Send message to log.
	 *
	 * @param string  $message
	 * @param integer $severity Severity - 0 is info (default), 1 is notice, 2 is warning, 3 is error, 4 is fatal error
	 * @return void
	 */
	protected function _log($message, $severity = 0) {
		$this->getLocker()->_log($message, $severity);
	}

	/**
	 * Get acquire retries setting.
	 *
	 * @return int
	 */
	public function getRetries() {
		return $this->_retries;
	}

	/**
	 * Set acquire fail retries setting.
	 *
	 * @param int $retries
	 * @return void
	 */
	public function setRetries($retries) {
		$this->_retries = MathUtility::forceIntegerInRange($retries, 1, 1000, $this->_retries);
	}

	/**
	 * Get acquire retry interval setting.
	 *
	 * @return int
	 */
	public function getRetryInterval() {
		return $this->_retryInterval;
	}

	/**
	 * Set acquire retry interval setting.
	 *
	 * @param int $retryInterval
	 * @return void
	 */
	public function setRetryInterval($retryInterval) {
		$this->_retryInterval = MathUtility::forceIntegerInRange($retryInterval, 1, 9999, $this->_retryInterval);
	}

	/**
	 * Waits milliseconds (interval) for next retry.
	 *
	 * @return void
	 */
	protected function _waitForRetry() {
		usleep($this->getRetryInterval() * 1000);
	}

	/**
	 * Get max age of an existing lock.
	 *
	 * @return integer
	 */
	protected function _getMaxAge() {
		$maxExecutionTime = ini_get('max_execution_time');

		return time() - ($maxExecutionTime ? $maxExecutionTime : 120);
	}

	/**
	 * Is lock aquired?
	 *
	 * @return boolean TRUE if lock was acquired, otherwise FALSE.
	 */
	public function isAcquired() {
		return $this->_isAcquired;
	}

	/**
	 * Get context/prefix for hash.
	 *
	 * @return string
	 */
	public function getContext() {
		return $this->_context;
	}

	/**
	 * Returns unique lock identifier.
	 *
	 * @return mixed
	 */
	public function getId() {
		return $this->_id;
	}

	/**
	 * Return unique id hash.
	 * 40 chars long string sha1().
	 *
	 * @return string
	 */
	public function getIdHash() {
		return sha1($this->_context . ':' . $this->_id);
	}

	/**
	 * Perform shutdown tasks.
	 *
	 * @return boolean True if succeeded, otherwise false.
	 */
	public function shutdown() {
		return $this->release();
	}

	/**
	 * Checks wheater current locking type is usable.
	 *  Called from t3lib_div::makeServiceInstance()
	 *
	 * @return boolean TRUE if locking method is usable/availble.
	 */
	public function init() {
		return $this->isAvailable();
	}

	/**
	 * Get required php functions.
	 *
	 * @return array
	 */
	protected function _getRequiredPHPFunctions() {
		return array();
	}

	/**
	 * Revalidate if locking type is usable/available.
	 *
	 * @return boolean TRUE if locking type is usable/available, FALSE if not.
	 */
	public function isAvailable() {
		// function check
		$requiredPHPFunctions = $this->_getRequiredPHPFunctions();
		foreach ($requiredPHPFunctions as $requiredPHPFunction) {
			if (!function_exists($requiredPHPFunction)) {
				return FALSE;
			}
		}

		return TRUE;
	}

}
