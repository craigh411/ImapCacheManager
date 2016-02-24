<?php

namespace Humps\ImapCacheManager\Contracts;

interface Cache
{
    /**
     * Returns all the cached items
     * @return array
     */
    public function getAllCached();

    /**
     * Returns the item by the given key
     * @param $key
     * @return array|string
     */
    public function getItemByKey($key);

    /**
     * Returns all the keys for the cache
     * @return array
     */
    public function getKeys();
}