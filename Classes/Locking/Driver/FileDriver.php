<?php

namespace Rheinschafe\RsLock\Locking\Driver;

/*
 * This file is part of the Rheinschafe/Lock project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * File-Locking-Driver class.
 *  Main locking method: function is_file(), file_exists(), etc
 *
 * @package    rs_lock
 * @subpackage Locking/Driver
 * @author     Daniel Hürtgen <huertgen@rheinschafe.de>
 */
class FileDriver extends AbstractFileDriver {

	/**
	 * Returns string with driver name.
	 *
	 * @return string
	 * @see Tx_RsLock_Locking_Driver_DriverInterface::getType()
	 */
	public function getType() {
		return 'file';
	}

	/**
	 * Acquire lock.
	 *  Tries to acquire locking. It is very important, that the lock will be generated. If something went wrong,
	 *  throw an runtime exception, but do NOT return FALSE on fail!
	 *
	 * @throws \Exception
	 * @return boolean TRUE, if lock was acquired without waiting for other clients/instances, otherwise, if the client was
	 *                 waiting, return FALSE.
	 * @see  Tx_RsLock_Locking_Driver_DriverInterface::acquire()
	 * @todo implement logging
	 */
	public function acquire() {
		$noWait = TRUE;
		$isAcquired = FALSE;

		$filePath = $this->getValidFilePath();

		// cleanup (GC)
		if ($this->fileExists() && @filectime($filePath) < $this->_getMaxAge()) {
			$this->_deleteFile();
		}

		// try to acquire lock
		for ($i = 0; $i < $this->getRetries(); $i++) {
			$filePointer = @fopen($filePath, 'x');
			if ($filePointer !== FALSE) {
				fclose($filePointer);
				$noWait = ($i === 0);
				$isAcquired = TRUE;
				break;
			}
			// sleep for retryInterval
			$this->_waitForRetry();
		}

		// @todo write own exception class
		if (!$isAcquired) {
			throw new \Exception('Lock file could not be acquired.');
		}

		// fix permissions
		GeneralUtility::fixPermissions($filePath);

		$this->_isAcquired = $isAcquired;

		return $noWait;
	}

	/**
	 * Release lock.
	 *
	 * @return boolean TRUE if locked was release, otherwise throw lock exception.
	 * @see Tx_RsLock_Locking_Driver_DriverInterface::release()
	 */
	public function release() {
		$isReleased = TRUE;

		// if is acquired // release lock
		if ($this->isAcquired()) {
			if ($this->_deleteFile() === FALSE) {
				$isReleased = FALSE;
			}
		}

		$this->_isAcquired = FALSE;

		return $isReleased;
	}

}
