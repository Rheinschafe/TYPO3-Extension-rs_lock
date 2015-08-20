<?php

namespace Rheinschafe\RsLock\Locking\Tests\Unit\Locking\Strategy;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

/**
 * This file was backported in 2015 from TYPO3 7.4 for usage in older
 * versions by Rheinschafe GmbH <www.rheinschafe.de>
 *
 * @author Florian in der Beek <inderbeek@rheinschafe.de>
 * @author Daniel Hürtgen <huertgen@rheinschafe.de>
 */

use Rheinschafe\RsLock\Locking\Strategy\SemaphoreLockStrategy;
use TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * Testcase for \Rheinschafe\RsLock\Locking\Strategy\SemaphoreLockStrategy
 */
class SemaphoreLockStrategyTest extends UnitTestCase {

	/**
	 * Set up the tests
	 */
	protected function setUp() {
		if (!SemaphoreLockStrategy::getCapabilities()) {
			$this->markTestSkipped('The system does not support semaphore locking.');
		}
	}

	/**
	 * @test
	 */
	public function acquireGetsSemaphore() {
		$lock = new SemaphoreLockStrategy('99999');
		$this->assertTrue($lock->acquire());
		$lock->release();
	}

}
