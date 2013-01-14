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
 * Abstract-File-Locking-Driver class.
 *
 * @package    rs_lock
 * @subpackage Locking/Driver
 * @license    http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 * @author     Daniel Hürtgen <huertgen@rheinschafe.de>
 */
abstract class Tx_RsLock_Locking_Driver_AbstractFileDriver extends Tx_RsLock_Locking_Driver_AbstractDriver {

	/**
	 * File path.
	 *
	 * @var string
	 */
	protected $_path;

	/**
	 * Get file locking working directory.
	 *
	 * @return string
	 */
	public function getPath() {
		if (empty($this->_path)) {
			$this->_path = PATH_site . 'typo3temp/locks/';
		}

		return $this->_path;
	}

	/**
	 * Returns valid path, otherwise throws exception.
	 *
	 * @throws RuntimeException
	 * @return string
	 */
	public function getValidPath() {
		if (!t3lib_div::isAllowedAbsPath($this->getPath())) {
			throw new RuntimeException(sprintf('Current path "%s" is not a valid path.', $this->getPath()));
		}

		return $this->getPath();
	}

	/**
	 * Get full file locking path.
	 *
	 * @return string
	 */
	public function getFilePath() {
		return $this->getPath() . $this->getIdHash();
	}

	/**
	 * Returns valid file path, otherwise throws exception.
	 *
	 * @throws RuntimeException
	 * @return string
	 */
	public function getValidFilePath() {
		if (!t3lib_div::isFirstPartOfStr($this->getFilePath(), $this->getPath())
		) {
			throw new RuntimeException(sprintf('Current file-path "%s" is not a valid file-path.',
											   $this->getFilePath()));
		}

		return $this->getFilePath();
	}

	/**
	 * Checks wheater lock file exists.
	 *
	 * @return boolean
	 */
	public function fileExists() {
		return is_file($this->getValidFilePath());
	}

	/**
	 * Is lock aquired?
	 *
	 * @return boolean TRUE if lock was acquired, otherwise FALSE.
	 * @see Tx_RsLock_Locking_Driver_DriverInterface::isAcquired()
	 */
	public function isAcquired() {
		return $this->fileExists() && parent::isAcquired();
	}

	/**
	 * Get required php functions.
	 *
	 * @return array
	 */
	protected function _getRequiredPHPFunctions() {
		return array(
			'unlink',
			'fopen',
			'fclose',
			'filectime',
			'is_file',
			'is_dir'
		);
	}

	/**
	 * Revalidate if locking type is usable/available.
	 *
	 * @return boolean TRUE if locking type is usable/available, FALSE if not.
	 */
	public function isAvailable() {
		if (!parent::isAvailable()) {
			return FALSE;
		}

		$path = $this->getValidPath();

		if (!is_dir($path) && !t3lib_div::mkdir($path)) {
			return FALSE;
		}

		if (!is_writable($path)) {
			return FALSE;
		}

		return TRUE;
	}

}
