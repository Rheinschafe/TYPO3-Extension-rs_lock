<?php

namespace Rheinschafe\RsLock\Locking\Unit\Locking\Strategy;

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

use Rheinschafe\RsLock\Locking\Strategy\FileLockStrategy;
use TYPO3\CMS\Core\Tests\UnitTestCase;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Testcase for \Rheinschafe\RsLock\Locking\Strategy\FileLockStrategy
 */
class FileLockStrategyTest extends UnitTestCase {

	/**
	 * @test
	 */
	public function constructorCreatesLockDirectoryIfNotExisting() {
		GeneralUtility::rmdir(PATH_site . FileLockStrategy::FILE_LOCK_FOLDER, TRUE);
		new FileLockStrategy('999999999');
		$this->assertTrue(is_dir(PATH_site . FileLockStrategy::FILE_LOCK_FOLDER));
	}

	/**
	 * @test
	 */
	public function constructorSetsFilePathToExpectedValue() {
		$lock = $this->getAccessibleMock("Rheinschafe\\RsLock\\Locking\\Strategy\\FileLockStrategy", ['dummy'], ['999999999']);
		$this->assertSame(PATH_site . FileLockStrategy::FILE_LOCK_FOLDER . 'flock_' . md5('999999999'), $lock->_get('filePath'));
	}

}
