<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012 Daniel Hürtgen <huertgen@rheinschafe.de>, Rheinschafe GmbH
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
 * File-Locking-Driver class.
 *  Main locking method: function is_file(), file_exists(), etc
 *
 * @package    rs_lock
 * @subpackage Locking/Driver
 * @license    http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 * @author     Daniel Hürtgen <huertgen@rheinschafe.de>
 */
class Tx_RsLock_Locking_Driver_FileDriver extends Tx_RsLock_Locking_Driver_AbstractFileDriver {

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
	 * @throws Exception
	 * @return boolean TRUE, if lock was acquired without waiting for other clients/instances, otherwise, if the client was waiting, return FALSE.
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

		for ($i = 0; $i < $this->getRetries(); $i++) {
			$filepointer = @fopen($this->getFilePath(), 'x');
			if ($filepointer !== FALSE) {
				fclose($filepointer);
				$noWait = ($i === 0);
				$isAcquired = TRUE;
				break;
			}
			$this->_waitForRetry();
		}

		// @todo write own exception class
		if (!$isAcquired) {
			throw new Exception('Lock file could not be created');
		}

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

		if ($this->isAcquired() && t3lib_div::isAllowedAbsPath($this->getPath()) && t3lib_div::isFirstPartOfStr($this->getFilePath(), $this->getPath())) {
			if (@unlink($this->getFilePath()) === FALSE) {
				$isReleased = FALSE;
			}
		}

		$this->_isAcquired = FALSE;
		return $isReleased;
	}

}
