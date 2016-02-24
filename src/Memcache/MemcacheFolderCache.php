<?php
namespace Humps\ImapCacheManager\Memcache;

use Humps\ImapCacheManager\Contracts\FolderCache;
use Humps\MailManager\Collections\FolderCollection;
use Humps\MailManager\Components\Folder;

class MemcacheFolderCache extends MemcacheCache implements FolderCache
{

    /**
     * MemcacheFolderCache constructor.
     * @param MemcacheServer $cache
     */
    function __construct(MemcacheServer $cache)
    {
        parent::__construct($cache, 'folders');
    }

    /**
     * Adds the folder to the cache
     * @param Folder $folder
     * @param int $expires
     */
    public function cacheFolder(Folder $folder, $expires = 0)
    {
        $key = $this->getKey($folder->getName());
        $this->cache($key, $folder, $expires);
    }

    /**
     * Returns all the currently cached folders as a FolderCollection
     * @return FolderCollection An Collection of Folder objects
     */
    public function getCachedFolders()
    {
        $folders = new FolderCollection();
        $cache = $this->getAllCached();
        if (count($cache)) {
            foreach ($cache as $folder) {
                $folders[] = $folder;
            }
        }

        return $folders;
    }

    /**
     * Removes the folder with the given name from the cache
     * @param $folder
     */
    public function uncacheFolder($folder)
    {
        $this->uncache($this->getKey($folder));
    }

    /**
     * Returns the folder from the cache with the given folder name;
     * @param string $name
     * @return Folder|bool Returns the Folder or false if it cannot be found
     */
    public function getFolder($name)
    {
        $folders = $this->getCachedFolders();
        /**
         * @var Folder $folder
         */
        if (count($folders)) {
            foreach ($folders as $folder) {
                if (strcasecmp($folder->getName(), $name) == 0) {
                    return $folder;
                }
            }
        }

        return false;
    }

    /**
     * @param string $folder
     * @return string
     */
    protected function getKey($folder)
    {
        return 'folder:' . $folder;
    }

    /**
     * Caches the given folder collection
     * @param FolderCollection $folders
     * @param int $expires
     */
    public function cacheFolders(FolderCollection $folders, $expires = 0)
    {
        if (count($folders)) {
            /**
             * @var Folder $folder
             */
            foreach ($folders as $folder) {
                $this->cacheFolder($folder, $expires);
            }
        }
    }
}