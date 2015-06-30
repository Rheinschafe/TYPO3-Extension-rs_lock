<?php

namespace Rheinschafe\RsLock\Locking\Driver;

/*
 * This file is part of the Rheinschafe/Lock project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Locking\Locker as CoreLocker;

/**
 * Abstract TYPO3 Locking-Driver class.
 *
 * @package    rs_lock
 * @subpackage Locking/Driver
 * @author     Daniel Hürtgen <huertgen@rheinschafe.de>
 */
abstract class AbstractTypo3Driver extends CoreLocker implements DriverInterface {

	/**
	 * Is lock aquired?
	 * Wrapper method for Locker::getLockStatus() method.
	 *
	 * @return bool|string
	 */
	public function isAcquired() {
		return $this->getLockStatus();
	}

	/**
	 * Perform shutdown tasks.
	 * Wrapper method for old Locker::__destructor().
	 * Calls Locker::release() method.
	 *
	 * @return void
	 */
	public function shutdown() {
		$this->release();
	}

	/**
	 * Returns string with driver name.
	 * Wraps to Locker::getMethod() method.
	 *
	 * @return string
	 */
	public function getType() {
		return $this->getMethod();
	}

}
