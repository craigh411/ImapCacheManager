<?php
use Humps\ImapCacheManager\Helpers\Sorter;
use Humps\ImapCacheManager\Memcache\MemcacheServer;
use Humps\MailManager\Components\Folder;
use Mockery as m;
/**
 * Created by PhpStorm.
 * User: Craig
 * Date: 30/01/16
 * Time: 11:41
 */
class MemcacheServerTest extends PHPUnit_Framework_TestCase
{

	/**
	 * @test
	 */
	public function it_returns_a_new_MemcacheServer_object() {
		$server = MemcacheServer::connect('localhost','11211');
		$this->assertInstanceOf(MemcacheServer::class, $server);
	}

	/**
	 * @test
	 */
	public function it_throws_an_exception_when_it_cannot_connect_to_the_cache_server() {
		$memcache = m::mock(Memcache::class);
		$memcache->shouldReceive('connect')->andReturn(false);
		$this->setExpectedException(Exception::class);
		new MemcacheServer($memcache, 'foo',1234);
	}

	/**
	 * @test
	 */
	public function foo()
	{
		$array = [
			new Folder('foo', 'bar', 'baz', 'qux', 'quux'),
			new Folder('bar', 'bar', 'baz', 'qux', 'quux'),
			new Folder('baz', 'bar', 'baz', 'qux', 'quux'),
			new Folder('qux', 'bar', 'baz', 'qux', 'quux'),
			new Folder('quux', 'bar', 'baz', 'qux', 'quux'),
		];

		if(Sorter::sort($array, 'name')){
			array_map(function($arr){
				echo $arr->getName()."\n";
			}, $array);
		}


	}
}
