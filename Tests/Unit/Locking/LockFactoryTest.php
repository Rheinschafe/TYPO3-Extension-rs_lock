<?php

namespace Rheinschafe\RsLock\Locking\Tests\Unit\Locking;

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

use Rheinschafe\RsLock\Locking\LockFactory;
use Rheinschafe\RsLock\Locking\Strategy\LockingStrategyInterface;
use TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * Testcase for \Rheinschafe\RsLock\Locking\LockFactory
 */
class LockFactoryTest extends UnitTestCase {

	/**
	 * @var LockFactory|\PHPUnit_Framework_MockObject_MockObject|\TYPO3\CMS\Core\Tests\AccessibleObjectInterface
	 */
	protected $mockFactory;

	/**
	 * Set up the tests
	 */
	protected function setUp() {
		$this->mockFactory = $this->getAccessibleMock("Rheinschafe\\RsLock\\Locking\\LockFactory", ['dummy']);
	}

	/**
	 * @test
	 */
	public function addLockingStrategyAddsTheClassNameToTheInternalArray() {
		$this->mockFactory->addLockingStrategy("Rheinschafe\\RsLock\\Tests\\Unit\\Locking\\Fixtures\\DummyLock");
		$this->assertArrayHasKey(
			"Rheinschafe\\RsLock\\Tests\\Unit\\Locking\\Fixtures\\DummyLock",
			$this->mockFactory->_get('lockingStrategy')
		);
	}

	/**
	 * @test
	 * @expectedException \InvalidArgumentException
	 * @expectedExceptionCode 1425990198
	 */
	public function addLockingStrategyThrowsExceptionIfInterfaceIsNotImplemented() {
		$this->mockFactory->addLockingStrategy("stdClass");
	}

	/**
	 * @test
	 */
	public function getLockerReturnsExpectedClass() {
		$this->mockFactory->_set(
			'lockingStrategy',
			[
				"Rheinschafe\\RsLock\\Locking\\Strategy\\FileLockStrategy"       => TRUE,
				"Rheinschafe\\RsLock\\Tests\\Unit\\Locking\\Fixtures\\DummyLock" => TRUE,
			]
		);
		$locker = $this->mockFactory->createLocker(
			'id',
			LockingStrategyInterface::LOCK_CAPABILITY_EXCLUSIVE | LockingStrategyInterface::LOCK_CAPABILITY_SHARED
		);
		$this->assertInstanceOf("Rheinschafe\\RsLock\\Locking\\Strategy\\FileLockStrategy", $locker);
	}

	/**
	 * @test
	 */
	public function getLockerReturnsClassWithHighestPriority() {
		$this->mockFactory->_set(
			'lockingStrategy',
			[
				"Rheinschafe\\RsLock\\Locking\\Strategy\\SemaphoreLockStrategy"  => TRUE,
				"Rheinschafe\\RsLock\\Tests\\Unit\\Locking\\Fixtures\\DummyLock" => TRUE,
			]
		);
		$locker = $this->mockFactory->createLocker('id');
		$this->assertInstanceOf("Rheinschafe\\RsLock\\Tests\\Unit\\Locking\\Fixtures\\DummyLock", $locker);
	}

	/**
	 * @test
	 * @expectedException \Rheinschafe\RsLock\Locking\Exception\LockCreateException
	 */
	public function getLockerThrowsExceptionIfNoMatchFound() {
		$this->mockFactory->createLocker('id', 32);
	}
}
