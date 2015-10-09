<?php

namespace Rheinschafe\RsLock\Locking\Tests\Unit\Locking\Strategy;

/*
 * This file is part of the Rheinschafe/Lock project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use Rheinschafe\RsLock\Locking\Strategy\AbstractLockStrategy;
use TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * Testcase for \Rheinschafe\RsLock\Locking\Strategy\AbstractLockStrategy
 */
class AbstractLockStrategyTest extends UnitTestCase {

	/**
	 * @var AbstractLockStrategy|\PHPUnit_Framework_MockObject_MockObject|\TYPO3\CMS\Core\Tests\AccessibleObjectInterface
	 */
	protected $lockObject;

	/**
	 * @return void
	 */
	protected function setUp() {
		$this->lockObject = $this->getAccessibleMockForAbstractClass(
			'Rheinschafe\\RsLock\\Locking\\Strategy\\AbstractLockStrategy',
			['dummy']
		);
	}

	/**
	 * @test
	 */
	public function constructorArgmumentSubjectWillSetAsExpected() {
		$this->assertEquals('dummy', $this->lockObject->_get('subject'));
	}

	/**
	 * @test
	 */
	public function getterForSubjectWillReturnTheSubject() {
		$this->assertEquals('dummy', $this->lockObject->getSubject());
	}

	/**
	 * @test
	 */
	public function getterForIsAquiredWillReturnTheAquiredStatusAsExpected() {
		$this->lockObject->_set('isAcquired', FALSE);
		$this->assertFalse($this->lockObject->isAcquired());
		$this->lockObject->_set('isAcquired', TRUE);
		$this->assertTrue($this->lockObject->isAcquired());
	}

}
