<?php
namespace Humps\ImapCacheManager\Memcache;

use Exception;
use Memcache;

class MemcacheServer
{

	protected $cache;

	/**
	 * MemcacheServer constructor.
	 * @param Memcache $cache
	 * @param $host
	 * @param $port
	 */
	public function __construct(Memcache $cache, $host, $port) {
		$this->cache = $cache;
		if(! $cache->connect($host, $port)) {
			throw new Exception('Unable to connect to cache server');
		}
	}

	/**
	 * Create a new connection
	 * @param $host
	 * @param $port
	 * @return static
	 * @throws Exception
	 */
	public static function connect($host, $port) {
		return new static(new Memcache(), $host, $port);
	}

	/**
	 * Returns the value from the cache for the given key
	 * @param $key
	 * @return array|string
	 */
	public function get($key) {
		return $this->cache->get($key);
	}

	/**
	 * Sets the value in the cache for the given key
	 * @param $key
	 * @param $value
	 * @param int $expires
	 * @return bool
	 */
	public function set($key, $value, $expires = 0) {
		$this->setLastCached();

		return $this->cache->set($key, $value, false, $expires);
	}

	/**
	 * Deletes the given key from the cache
	 * @param $key
	 */
	public function delete($key) {
		$this->cache->delete($key);
	}

	/**
	 * Clears the entire cache
	 */
	public function flush() {
		$this->cache->flush();
	}

	/**
	 * Returns the last time a value was added to the cache
	 * @return string
	 */
	public function getLastCached() {
		return $this->get('lastCached');
	}

	/**
	 * Sets the last time a value was added to the cache
	 */
	protected function setLastCached() {
		$this->cache->set('lastCached', time());
	}
}