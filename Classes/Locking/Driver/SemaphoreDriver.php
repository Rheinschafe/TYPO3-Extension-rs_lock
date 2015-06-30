<?php

namespace Rheinschafe\RsLock\Locking\Driver;

/*
 * This file is part of the Rheinschafe/Lock project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

/**
 * Semaphore-Locking-Driver class.
 *  Main locking method: function sem_acquire()
 *
 * @package    rs_lock
 * @subpackage Locking/Driver
 * @author     Daniel Hürtgen <huertgen@rheinschafe.de>
 */
class SemaphoreDriver extends AbstractTypo3Driver {

	/**
	 * Constructor.
	 *
	 * @param mixed    $id
	 * @param int|null $loops
	 * @param int|null $step
	 * @return SemaphoreDriver
	 */
	public function __construct($id, $loops = NULL, $step = NULL) {
		// set fixed method
		$method = 'semaphore';

		return parent::__construct($id, $method, $loops, $step);
	}

}
