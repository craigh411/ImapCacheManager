<?php
namespace Humps\ImapCacheManager\Contracts;

use Humps\MailManager\Components\Folder;

interface FolderCache extends Cache
{

	/**
	 * Adds the folder to the cache
	 * @param Folder $folder
	 * @param int $expires
	 */
	public function cacheFolder(Folder $folder, $expires = 0);

	/**
	 * Returns all the currently cached folders as an array
	 * @return array An array of Folder objects
	 */
	public function getCachedFolders();

	/**
	 * Removes the folder with the given name from the cache
	 * @param $folder
	 */
	public function uncacheFolder($folder);

	/**
	 * Returns the folder from the cache with the given folder name;
	 * @param string $name
	 * @return Folder|bool Returns the Folder or false if it cannot be found
	 */
	public function getFolder($name);
}