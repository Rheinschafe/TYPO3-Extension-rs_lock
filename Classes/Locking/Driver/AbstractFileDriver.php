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
 * Abstract-File-Locking-Driver class.
 *
 * @package    rs_lock
 * @subpackage Locking/Driver
 * @author     Daniel Hürtgen <huertgen@rheinschafe.de>
 */
abstract class AbstractFileDriver extends AbstractDriver {

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
	 * @throws \RuntimeException
	 * @return string
	 */
	public function getValidPath() {
		if (!GeneralUtility::isAllowedAbsPath($this->getPath())) {
			throw new \RuntimeException(sprintf('Current path "%s" is not a valid path.', $this->getPath()));
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
		if (!GeneralUtility::isFirstPartOfStr($this->getFilePath(), $this->getPath())
		) {
			throw new \RuntimeException(
				sprintf(
					'Current file-path "%s" is not a valid file-path.',
					$this->getFilePath()
				)
			);
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
	 * Delete current lock file.
	 *
	 * @return boolean
	 */
	protected function _deleteFile() {
		return @unlink($this->getValidFilePath());
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

		if (!is_dir($path) && !GeneralUtility::mkdir($path)) {
			return FALSE;
		}

		if (!is_writable($path)) {
			return FALSE;
		}

		return TRUE;
	}

}
