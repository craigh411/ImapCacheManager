<?php
namespace Humps\ImapCacheManager\Memcache;

use Humps\ImapCacheManager\Contracts\Cache;

abstract class MemcacheCache implements Cache
{

	protected $cache;
	protected $key;
	protected $keys;

	/**
	 * MemcacheCache constructor.
	 * @param MemcacheServer $cache
	 * @param string $key The key for the array of keys this cache is using
	 */
	function __construct(MemcacheServer $cache, $key) {
		$this->cache = $cache;
		// Load the keys from the cache
		$this->keys = ($keys = $this->cache->get($key)) ? $keys : [];
		$this->key = $key;
		$this->purgeExpired();
	}

	/**
	 * Caches the given key value pair
	 * @param $key
	 * @param $value
	 * @param $expires
	 */
	protected function cache($key, $value, $expires) {
		$key = strtolower($key);
		$this->cache->set($key, $value, $expires);
		$this->addKey($key);
	}

	/**
	 * Removes all expired keys from the keys list
	 */
	protected function purgeExpired() {
		if(count($this->keys)) {
			foreach($this->keys as $i => &$key) {
				if(! $this->cache->get($key)) {
					unset($this->keys[$i]);
				}
			}
			$this->cacheKeys();
		}
	}

	/**
	 * Caches the keys array to the given key value
	 */
	protected function cacheKeys() {
		// Normalise the array
		$this->keys = array_values($this->keys);
		$this->cache->set($this->key, $this->keys);
	}

	/**
	 * Adds a key to the keys array
	 * @param $key
	 */
	protected function addKey($key) {
		if(! in_array($key, $this->keys)) {
			$this->keys[] = $key;
			$this->cacheKeys();
		}
	}

	/**
	 * Returns all the cached items
	 * @return array
	 */
	public function getAllCached() {
		$cache = [];
		if(count($this->keys)) {
			foreach($this->keys as $i => &$key) {
				if($item = $this->cache->get($key)) {
					$cache[] = $item;
				}
			}
		}
		return $cache;
	}

	/**
	 * Returns the item by the given key
	 * @param $key
	 * @return array|string
	 */
	public function getItemByKey($key) {
		return $this->cache->get(strtolower($key));
	}

	/**
	 * Deletes an item and removes it from the cache
	 * @param $key
	 */
	protected function uncache($key) {
		$key = strtolower($key);
		$this->cache->delete($key);
		$this->purge($key);
	}

	/**
	 * Deletes the key from the keys list and updates the cache
	 * @param $key
	 */
	protected function purge($key){
		if($key = array_search($key, $this->keys) !== false) {
			unset($this->keys[$key]);
			$this->cacheKeys();
		}
	}

	/**
	 * Returns all the keys for the cache
	 * @return array
	 */
	public function getKeys() {
		return $this->keys;
	}
}