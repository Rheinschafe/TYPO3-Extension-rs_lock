<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012-2015 Daniel Hürtgen <huertgen@rheinschafe.de>, Rheinschafe GmbH
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Abstract TYPO3 Locking-Driver class.
 *
 * @package    rs_lock
 * @subpackage Locking/Driver
 * @license    http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 * @author     Daniel Hürtgen <huertgen@rheinschafe.de>
 */
abstract class Tx_RsLock_Locking_Driver_AbstractTypo3Driver extends t3lib_lock
	implements Tx_RsLock_Locking_Driver_DriverInterface {

	/**
	 * Is lock aquired?
	 * Wrapper method for t3lib_lock::getLockStatus() method.
	 *
	 * @return bool|string
	 */
	public function isAcquired() {
		return $this->getLockStatus();
	}

	/**
	 * Perform shutdown tasks.
	 * Wrapper method for old t3lib_lock::__destructor().
	 * Calls t3lib_lock::release() method.
	 *
	 * @return void
	 */
	public function shutdown() {
		$this->release();
	}

	/**
	 * Returns string with driver name.
	 * Wraps to t3lib_lock::getMethod() method.
	 *
	 * @return string
	 */
	public function getType() {
		return $this->getMethod();
	}

}
