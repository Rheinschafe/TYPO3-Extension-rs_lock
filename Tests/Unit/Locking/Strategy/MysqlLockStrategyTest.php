<?php

namespace Rheinschafe\RsLock\Locking\Tests\Unit\Locking\Strategy;

/*
 * This file is part of the Rheinschafe/Lock project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use Rheinschafe\RsLock\Locking\Strategy\MysqlLockStrategy;
use TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * Testcase for \Rheinschafe\RsLock\Locking\Strategy\SimpleLockStrategy
 */
class MysqlLockStrategyTest extends UnitTestCase {

	/**
	 * @test
	 */
	public function acquireGetsMysqlLock() {
		$lock = new MysqlLockStrategy('99999');
		$this->assertTrue($lock->acquire());
		$lock->release();
	}

	/**
	 * @test
	 */
	public function checkIfLockGetsReleased() {
		$lock = new MysqlLockStrategy('99999');
		$lock->acquire();
		$this->assertTrue($lock->release());
		$this->assertFalse($lock->isAcquired());
	}

}
