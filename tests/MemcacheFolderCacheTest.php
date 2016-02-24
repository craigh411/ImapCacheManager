<?php
use Humps\ImapCacheManager\Memcache\MemcacheFolderCache;
use Humps\ImapCacheManager\Memcache\MemcacheServer;
use Humps\MailManager\Components\Folder;
use Mockery as m;

class MemcacheFolderCacheTest extends PHPUnit_Framework_TestCase
{

	protected $folderCache = null;

	/**
	 * @test
	 */
	public function it_adds_a_Folder_to_the_cache() {
		$memcache = m::mock(Memcache::class);
		$memcache->shouldReceive('connect')->andReturn(true);
		$server = m::mock(MemcacheServer::class, [$memcache, 'foo', 12345]);
		$folder = m::mock(Folder::class);

		$folder->shouldReceive('getName')->andReturn('INBOX');
		$server->shouldReceive('set')->once()->with('folder:INBOX', $folder, 0);
		$server->shouldReceive('set')->once()->with('folders', ['folder:INBOX']);
		$server->shouldReceive('get')->andReturn(false);
		$folderCache = new MemcacheFolderCache($server);
		$folderCache->cacheFolder($folder);
	}

	/**
	 * @test
	 */
	public function it_returns_a_list_of_cached_folders() {
		$memcache = m::mock(Memcache::class);
		$memcache->shouldReceive('connect')->andReturn(true);
		$server = m::mock(MemcacheServer::class, [$memcache, 'foo', 12345]);
		$folder = m::mock(Folder::class);
		$folder->shouldReceive('getName')->andReturn('INBOX');
		$server->shouldReceive('set')->once()->with('folder:INBOX', $folder, 0);
		$server->shouldReceive('set')->once()->with('folders', ['folder:INBOX']);
		$server->shouldReceive('get')->once()->andReturn(false);
		$server->shouldReceive('get')->once()->andReturn($folder);
		$folderCache = new MemcacheFolderCache($server);
		$folderCache->cacheFolder($folder);
		$this->assertEquals([$folder], $folderCache->getCachedFolders());
	}

	/**
	 * @test
	 */
	public function it_returns_the_folder_with_the_given_name() {
		$memcache = m::mock(Memcache::class);
		$memcache->shouldReceive('connect')->andReturn(true);
		$server = m::mock(MemcacheServer::class, [$memcache, 'foo', 12345]);
		$inbox = m::mock(Folder::class);
		$inbox->shouldReceive('getName')->andReturn('INBOX');
		$trash = m::mock(Folder::class);
		$trash->shouldReceive('getName')->andReturn('Trash');
		$server->shouldReceive('set')->once()->with('folder:Trash', $trash, 0);
		$server->shouldReceive('set')->once()->with('folder:INBOX', $inbox, 0);
		$server->shouldReceive('set')->once()->with('folders', ['folder:Trash']);
		$server->shouldReceive('set')->once()->with('folders', ['folder:Trash', 'folder:INBOX']);
		$server->shouldReceive('get')->once()->andReturn(false);
		$server->shouldReceive('get')->once()->andReturn($trash);
		$server->shouldReceive('get')->once()->andReturn($inbox);
		$folderCache = new MemcacheFolderCache($server);
		$folderCache->cacheFolder($trash);
		$folderCache->cacheFolder($inbox);
		$folder = $folderCache->getFolder('INBOX');
		$this->assertInstanceOf(Folder::class, $folder);
		$this->assertEquals($inbox, $folder);
	}

	/**
	 * @test
	 */
	public function it_returns_false_when_the_folder_cannot_be_found() {
		$memcache = m::mock(Memcache::class);
		$memcache->shouldReceive('connect')->andReturn(true);
		$server = m::mock(MemcacheServer::class, [$memcache, 'foo', 12345]);

		$server->shouldReceive('get')->once()->andReturn(false);
		$folderCache = new MemcacheFolderCache($server);
		$folder = $folderCache->getFolder('INBOX');
		$this->assertFalse($folder);
	}

	/**
	 * @test
	 */
	public function it_deletes_the_given_folder_from_the_cache() {
		$memcache = m::mock(Memcache::class);
		$memcache->shouldReceive('connect')->andReturn(true);

		$server = m::mock(MemcacheServer::class, [$memcache, 'foo', 12345]);

		$cache = ['folder:INBOX', 'folder:Trash'];
		// Mock cached folders
		$server->shouldReceive('get')->andReturn($cache);
		// Deal with the automatic purging of expired values
		$server->shouldReceive('get')->twice()->andReturn(true);
		$server->shouldReceive('set')->once()->with('folders', $cache);

		// Deal with the actual deletion
		$server->shouldReceive('delete')->with('folder:Trash');
		$server->shouldReceive('set')->once()->with('folders', ['folder:INBOX']);
		// Let's create the object and delete the Trash folder
		$folderCache = new MemcacheFolderCache($server);
		$folderCache->uncacheFolder('Trash');

		$this->assertEquals(['folder:INBOX'], $folderCache->getKeys());
	}

}
