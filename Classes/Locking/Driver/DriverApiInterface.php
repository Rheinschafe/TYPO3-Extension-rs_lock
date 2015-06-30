<?php

namespace Rheinschafe\RsLock\Locking\Driver;

/*
 * This file is part of the Rheinschafe/Lock project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

/**
 * Locking-Driver-API interface.
 *
 * @package    rs_lock
 * @subpackage Locking/Driver
 * @author     Daniel Hürtgen <huertgen@rheinschafe.de>
 */
interface DriverApiInterface {

	/**
	 * Acquire lock.
	 *  Tries to acquire locking. It is very important, that the lock will be generated. If something went wrong,
	 *  throw an runtime exception, but do NOT return FALSE on fail!
	 *
	 * @return boolean TRUE, if lock was acquired without waiting for other clients/instances, otherwise, if the client was
	 *                 waiting, return FALSE.
	 */
	public function acquire();

	/**
	 * Release lock.
	 *
	 * @return boolean TRUE if locked was release, otherwise throw lock exception.
	 */
	public function release();

	/**
	 * Is lock aquired?
	 *
	 * @return boolean TRUE if lock was acquired, otherwise FALSE.
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
