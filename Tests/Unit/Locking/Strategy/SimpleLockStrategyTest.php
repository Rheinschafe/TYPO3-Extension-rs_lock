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

use Rheinschafe\RsLock\Locking\Strategy\SimpleLockStrategy;
use TYPO3\CMS\Core\Tests\UnitTestCase;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Testcase for \Rheinschafe\RsLock\Locking\Strategy\SimpleLockStrategy
 */
class SimpleLockStrategyTest extends UnitTestCase {

	/**
	 * @test
	 */
	public function constructorCreatesLockDirectoryIfNotExisting() {
		GeneralUtility::rmdir(PATH_site . SimpleLockStrategy::FILE_LOCK_FOLDER, TRUE);
		new SimpleLockStrategy('999999999');
		$this->assertTrue(is_dir(PATH_site . SimpleLockStrategy::FILE_LOCK_FOLDER));
	}

	/**
	 * @test
	 */
	public function constructorSetsResourceToPathWithIdIfUsingSimpleLocking() {
		$lock = $this->getAccessibleMock("Rheinschafe\\RsLock\\Locking\\Strategy\\SimpleLockStrategy", ['dummy'], ['999999999']);
		$this->assertSame(
			PATH_site . SimpleLockStrategy::FILE_LOCK_FOLDER . 'simple_' . md5('999999999'),
			$lock->_get('filePath')
		);
	}

	/**
	 * @test
	 */
	public function acquireFixesPermissionsOnLockFile() {
		if (TYPO3_OS === 'WIN') {
			$this->markTestSkipped('Test not available on Windows.');
		}
		// Use a very high id to be unique
		/** @var SimpleLockStrategy|\PHPUnit_Framework_MockObject_MockObject|\TYPO3\CMS\Core\Tests\AccessibleObjectInterface $lock */
		$lock = $this->getAccessibleMock("Rheinschafe\\RsLock\\Locking\\Strategy\\SimpleLockStrategy", ['dummy'], ['999999999']);

		$pathOfLockFile = $lock->_get('filePath');

		$GLOBALS['TYPO3_CONF_VARS']['BE']['fileCreateMask'] = '0777';

		// Acquire lock, get actual file permissions and clean up
		$lock->acquire();
		clearstatcache();
		$resultFilePermissions = substr(decoct(fileperms($pathOfLockFile)), 2);
		$lock->release();
		$this->assertEquals($resultFilePermissions, '0777');
	}

	/**
	 * @test
	 */
	public function releaseRemovesLockfileInTypo3TempLocks() {
		/** @var SimpleLockStrategy|\PHPUnit_Framework_MockObject_MockObject|\TYPO3\CMS\Core\Tests\AccessibleObjectInterface $lock */
		$lock = $this->getAccessibleMock("Rheinschafe\\RsLock\\Locking\\Strategy\\SimpleLockStrategy", ['dummy'], ['999999999']);

		$pathOfLockFile = $lock->_get('filePath');

		$lock->acquire();
		$lock->release();

		$this->assertFalse(is_file($pathOfLockFile));
	}

	/**
	 * Dataprovider for releaseDoesNotRemoveFilesNotWithinTypo3TempLocksDirectory
	 */
	public function invalidFileReferences() {
		return array(
			'not withing PATH_site' => array('/tmp/TYPO3-Lock-Test'),
			'directory traversal'   => array(PATH_site . 'typo3temp/../typo3temp/locks/foo'),
			'directory traversal 2' => array(PATH_site . 'typo3temp/locks/../locks/foo'),
			'within uploads'        => array(PATH_site . 'uploads/TYPO3-Lock-Test'),
		);
	}

	/**
	 * @test
	 * @dataProvider invalidFileReferences
	 * @param string $file
	 * @throws \PHPUnit_Framework_SkippedTestError
	 */
	public function releaseDoesNotRemoveFilesNotWithinTypo3TempLocksDirectory($file) {
		if (TYPO3_OS === 'WIN') {
			$this->markTestSkipped('releaseDoesNotRemoveFilesNotWithinTypo3TempLocksDirectory() test not available on Windows.');
		}
		// Create test file
		touch($file);
		if (!is_file($file)) {
			$this->markTestIncomplete(
				'releaseDoesNotRemoveFilesNotWithinTypo3TempLocksDirectory() skipped: Test file could not be created'
			);
		}
		// Create instance, set lock file to invalid path
		/** @var SimpleLockStrategy|\PHPUnit_Framework_MockObject_MockObject|\TYPO3\CMS\Core\Tests\AccessibleObjectInterface $lock */
		$lock = $this->getAccessibleMock("Rheinschafe\\RsLock\\Locking\\Strategy\\SimpleLockStrategy", ['dummy'], ['999999999']);
		$lock->_set('filePath', $file);
		$lock->_set('isAcquired', TRUE);

		// Call release method
		$lock->release();
		// Check if file is still there and clean up
		$fileExists = is_file($file);
		if (is_file($file)) {
			unlink($file);
		}
		$this->assertTrue($fileExists);
	}

}
