<?php

namespace Rheinschafe\RsLock\Locking;

/*
 * This file is part of the Rheinschafe/Lock project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use Rheinschafe\RsLock\Locking\Driver\DriverInterface;

/**
 * Simple (single) locker interface.
 *
 * @package    rs_lock
 * @subpackage Locking
 * @author     Daniel Hürtgen <huertgen@rheinschafe.de>
 */
interface SimpleLockerInterface extends LockerInterface {

	/**
	 * Constructor.
	 *
	 * @param mixed                  $id      Unique id used for locking.
	 * @param string|DriverInterface $driver  Driver class object or string.
	 * @param string                 $context Locking context/prefix.
	 * @param null                   $loops   Times a lock is tried to acuqire.
	 * @param null                   $steps   Milliseconds to sleep between looping.
	 * @return SimpleLockerInterface
	 */
	public function __construct($id, $driver, $context, $loops = NULL, $steps = NULL);

	/**
	 * Get driver.
	 *
	 * @return DriverInterface
	 */
	public function getDriver();

}
