<?php

namespace Rheinschafe\RsLock\Locking\Strategy;

/*
 * This file is part of the Rheinschafe/Lock project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use Rheinschafe\RsLock\Locking\Exception\LockAcquireException;
use Rheinschafe\RsLock\Locking\Exception\LockAcquireWouldBlockException;
use Rheinschafe\RsLock\Locking\Exception\LockCreateException;

class RedisLockStrategy implements LockingStrategyInterface {

	/**
	 * Unique identifier for locking
	 *
	 * @var string
	 */
	protected $id;

	/**
	 * True if lock is acquired
	 *
	 * @var bool
	 */
	protected $isAcquired = FALSE;

	/**
	 * Instance of the PHP redis class
	 *
	 * @var \Redis
	 */
	protected $redis;

	/**
	 * Indicates whether the server is connected
	 *
	 * @var boolean
	 */
	protected $connected = FALSE;

	/**
	 * Number of times a locked resource is tried to be acquired. Only used in manual locks method "simple".
	 *
	 * @var integer
	 */
	protected $loops = 150;

	/**
	 * Milliseconds after lock acquire is retried. $loops * $step results in the maximum delay of a lock
	 *
	 * @var integer
	 */
	protected $step = 200;

	/**
	 * Hostname / IP of the Redis server, defaults to 127.0.0.1.
	 *
	 * @var string
	 */
	protected $hostname = '127.0.0.1';

	/**
	 * Port of the Redis server, defaults to 6379
	 *
	 * @var integer
	 */
	protected $port = 6379;

	/**
	 * Number of selected database, defaults to 0
	 *
	 * @var integer
	 */
	protected $database = 0;

	/**
	 * Password for redis authentication
	 *
	 * @var string
	 */
	protected $password = '';

	/**
	 * @param string $subject ID to identify this lock in the system
	 * @throws LockCreateException if the lock could not be created
	 */
	public function __construct($subject) {
		if (!extension_loaded('redis')) {
			throw new LockCreateException(
				'The PHP extension "redis" must be installed and loaded in order to use the redis locking strategy.', 1444304485
			);
		}

		$this->id = md5((string)$subject);

		if (is_array($GLOBALS['TYPO3_CONF_VARS']['SYS']['locking']['lockingConfigurations']['redis'])) {
			$lockingConfiguration = $GLOBALS['TYPO3_CONF_VARS']['SYS']['locking']['lockingConfigurations']['redis'];
			if (isset($lockingConfiguration['hostname']) && !empty($lockingConfiguration['hostname'])) {
				$this->hostname = (string)$lockingConfiguration['hostname'];
			}
			if (isset($lockingConfiguration['port']) && !empty($lockingConfiguration['port'])) {
				$this->port = (int)$lockingConfiguration['port'];
			}
			if (isset($lockingConfiguration['database']) && !empty($lockingConfiguration['database'])) {
				$this->database = (int)$lockingConfiguration['database'];
			}
			if (isset($lockingConfiguration['password']) && !empty($lockingConfiguration['password'])) {
				$this->password = (string)$lockingConfiguration['password'];
			}
		}
	}

	/**
	 * @param int $loops Number of times a locked resource is tried to be acquired.
	 * @param int $step  Milliseconds after lock acquire is retried. $loops * $step results in the maximum delay of a lock.
	 * @return void
	 */
	public function init($loops = 0, $step = 0) {
		$this->loops = (int)$loops;
		$this->step = (int)$step;
	}

	/**
	 * Connect to the redis server
	 *
	 * @throws LockCreateException
	 * @return void
	 */
	protected function connect() {
		$this->redis = new \Redis();
		try {
			$this->connected = $this->redis->connect($this->hostname, $this->port);
		} catch (\Exception $e) {
			throw new LockCreateException('Could not connect to redis server.', 1444315334);
		}
		if ($this->connected) {
			if (strlen($this->password)) {
				$success = $this->redis->auth($this->password);
				if (!$success) {
					throw new LockCreateException('The given password was not accepted by the redis server.', 1444314337);
				}
			}
			if ($this->database > 0) {
				$success = $this->redis->select($this->database);
				if (!$success) {
					throw new LockCreateException(
						'The given database "' . $this->database . '" could not be selected.',
						1444314341
					);
				}
			}
		}
	}

	/**
	 * Disconnect to the redis server
	 *
	 * @return void
	 */
	protected function disconnect() {
		if ($this->connected) {
			$this->redis->close();
			$this->connected = FALSE;
		}
	}

	/**
	 * @return int LOCK_CAPABILITY_* elements combined with bit-wise OR
	 */
	static public function getCapabilities() {
		if (!extension_loaded('redis')
			|| !isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['locking']['lockingConfigurations']['redis']['enable'])
			|| !$GLOBALS['TYPO3_CONF_VARS']['SYS']['locking']['lockingConfigurations']['redis']['enable']
		) {
			return 0;
		}

		return LockingStrategyInterface::LOCK_CAPABILITY_EXCLUSIVE | LockingStrategyInterface::LOCK_CAPABILITY_NOBLOCK;
	}

	/**
	 * @return int Returns a priority for the method. 0 to 100, 100 is highest
	 */
	static public function getPriority() {
		return 85;
	}

	/**
	 * Try to acquire a lock
	 *
	 * @param int $mode LOCK_CAPABILITY_EXCLUSIVE or LOCK_CAPABILITY_SHARED
	 * @return bool Returns TRUE if the lock was acquired successfully
	 * @throws LockAcquireException if the lock could not be acquired
	 * @throws LockAcquireWouldBlockException if the acquire would have blocked and NOBLOCK was set
	 */
	public function acquire($mode = self::LOCK_CAPABILITY_EXCLUSIVE) {
		if ($this->isAcquired) {
			return TRUE;
		}

		if (!$this->connected) {
			$this->connect();
		}

		$maxExecutionTime = ini_get('max_execution_time') ?: NULL;
		$ttl = ($maxExecutionTime ?: 60) * 1000;

		$strLuaLockScript = <<<LUA
--
-- Set a lock
--
-- ARGV[1]  - key
-- ARGV[2]  - ttl in ms
local key   = ARGV[1]
local ttl   = ARGV[2]

local lockSet = redis.call('setnx', key, '')

if lockSet == 1 then
  redis.call('pexpire', key, ttl)
end

return lockSet
LUA;

		$this->isAcquired = FALSE;
		$wouldBlock = FALSE;
		for ($i = 0; $i < $this->loops; $i++) {
			$succeed = (bool)$this->redis->eval($strLuaLockScript, array($this->id, $ttl));
			if ($succeed) {
				$this->isAcquired = TRUE;
				break;
			}
			if ($mode & self::LOCK_CAPABILITY_NOBLOCK) {
				$wouldBlock = TRUE;
				break;
			}
			usleep($this->step * 1000);
		}

		if ($mode & self::LOCK_CAPABILITY_NOBLOCK && !$this->isAcquired && $wouldBlock) {
			throw new LockAcquireWouldBlockException('Failed to acquire lock because the request would block.', 1428700748);
		}

		return $this->isAcquired;
	}

	/**
	 * Release the lock
	 *
	 * @return bool Returns TRUE on success or FALSE on failure
	 */
	public function release() {
		if (!$this->isAcquired) {
			return TRUE;
		}

		$strLuaUnlockScript = <<<LUA
--
-- Release a lock
--
-- ARGV[1]   - key
local key   = ARGV[1]

return redis.call("DEL", key)
LUA;

		$succeed = (bool)$this->redis->eval($strLuaUnlockScript, array($this->id));
		$this->isAcquired = FALSE;

		return $succeed;
	}

	/**
	 * Destructor:
	 * Releases lock automatically when instance is destroyed and release resources
	 *
	 * @return void
	 */
	public function __destruct() {
		$this->release();
		$this->disconnect();
	}

	/**
	 * Get status of this lock
	 *
	 * @return bool Returns TRUE if lock is acquired by this locker, FALSE otherwise
	 */
	public function isAcquired() {
		return $this->isAcquired;
	}

	/**
	 * Destroys the resource associated with the lock
	 *
	 * @return void
	 */
	public function destroy() {
	}

}
