<?php

namespace Rheinschafe\RsLock\Locking\Strategy;

/*
 * This file is part of the Rheinschafe/Lock project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

/**
 * Abstract lock strategy class.
 *
 * @package Rheinschafe\RsLock\Locking\Strategy
 */
abstract class AbstractLockStrategy implements LockingStrategyInterface {

	/**
	 * Lock subject.
	 *
	 * @var string
	 */
	protected $subject;

	/**
	 * Holds the current lock status.
	 *
	 * @var boolean
	 */
	protected $isAcquired = FALSE;

	/**
	 * Class constructor.
	 *
	 * @param string $subject ID to identify this lock in the system
	 * @return AbstractLockStrategy
	 */
	public function __construct($subject) {
		$this->subject = $subject;
	}

	/**
	 * Get status of this lock.
	 *
	 * @return boolean Returns TRUE if lock is acquired by this locker, FALSE otherwise.
	 */
	public function isAcquired() {
		return $this->isAcquired;
	}

	/**
	 * Gets the lock subject.
	 *
	 * @return string The lock subject.
	 */
	public function getSubject() {
		return $this->subject;
	}

}
