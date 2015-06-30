<?php

namespace Rheinschafe\RsLock\Locking\Driver;

/*
 * This file is part of the Rheinschafe/Lock project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use Rheinschafe\RsLock\Locking\LockerInterface;

/**
 * Locking-Driver interface.
 *
 * @package    rs_lock
 * @subpackage Locking/Driver
 * @author     Daniel Hürtgen <huertgen@rheinschafe.de>
 */
interface DriverInterface extends DriverApiInterface {

	/**
	 * Constructor.
	 *
	 * @param LockerInterface $locker
	 * @param string          $id
	 * @param string          $context
	 * @param int|null        $retries
	 * @param int|null        $retryInterval
	 * @return DriverInterface
	 */
	public function __construct(LockerInterface $locker, $id, $context, $retries = NULL, $retryInterval = NULL);

	/**
	 * Perform shutdown tasks.
	 *
	 * @return boolean True if succeeded, otherwise false.
	 */
	public function shutdown();

	/**
	 * Get parent lock api class.
	 *
	 * @return LockerInterface
	 */
	public function getLocker();

	/**
	 * Revalidate if locking type is usable/available.
	 *
	 * @return boolean TRUE if locking type is usable/available, FALSE if not.
	 */
	public function isAvailable();

}
