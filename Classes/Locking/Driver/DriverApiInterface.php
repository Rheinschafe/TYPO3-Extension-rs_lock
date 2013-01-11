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
 * Locking-Driver-API interface.
 *
 * @package    rs_lock
 * @subpackage Locking/Driver
 * @license    http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 * @author     Daniel Hürtgen <huertgen@rheinschafe.de>
 */
interface Tx_RsLock_Locking_Driver_DriverApiInterface {

	/**
	 * Acquire lock.
	 *
	 * @return boolean
	 */
	public function acquire();

	/**
	 * Release lock.
	 *
	 * @return boolean
	 */
	public function release();

	/**
	 * Is lock aquired?
	 *
	 * @return boolean
	 */
	public function isAcquired();

	/**
	 * Returns string with driver name.
	 *
	 * @return string
	 */
	public function getType();

	/**
	 * Returns unique lock identifier.
	 *
	 * @return mixed
	 */
	public function getId();

	/**
	 * Return unique id hash.
	 * 40 chars long string sha1().
	 *
	 * @return string
	 */
	public function getIdHash();

	/**
	 * Get acquire retries setting.
	 *
	 * @return int
	 */
	public function getRetries();

	/**
	 * Set acquire fail retries setting.
	 *
	 * @param int $retries
	 * @return void
	 */
	public function setRetries($retries);

	/**
	 * Get acquire retry interval setting.
	 *
	 * @return int
	 */
	public function getRetryInterval();

	/**
	 * Set acquire retry interval setting.
	 *
	 * @param int $retryInterval
	 * @return void
	 */
	public function setRetryInterval($retryInterval);

	/**
	 * Get context/prefix for hash.
	 *
	 * @return string
	 */
	public function getContext();

}
