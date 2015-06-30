<?php

namespace Rheinschafe\RsLock\Locking\Driver;

use Rheinschafe\RsLock\Locking\LockerInterface;

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
 * Locking-Driver interface.
 *
 * @package    rs_lock
 * @subpackage Locking/Driver
 * @license    http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 * @author     Daniel Hürtgen <huertgen@rheinschafe.de>
 */
interface DriverInterface extends DriverApiInterface {

	/**
	 * Constructor.
	 *
	 * @param LockerInterface $locker
	 * @param string          $id
	 * @param string          $context
	 * @param int|null        $retries
	 * @param int|null        $retryInterval
	 * @return DriverInterface
	 */
	public function __construct(LockerInterface $locker, $id, $context, $retries = NULL, $retryInterval = NULL);

	/**
	 * Perform shutdown tasks.
	 *
	 * @return boolean True if succeeded, otherwise false.
	 */
	public function shutdown();

	/**
	 * Get parent lock api class.
	 *
	 * @return LockerInterface
	 */
	public function getLocker();

	/**
	 * Revalidate if locking type is usable/available.
	 *
	 * @return boolean TRUE if locking type is usable/available, FALSE if not.
	 */
	public function isAvailable();

}
