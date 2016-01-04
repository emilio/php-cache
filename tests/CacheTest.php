<?php
namespace Tests;

use \Cache\Cache;

class CacheTest extends \PHPUnit_Framework_TestCase
{
    protected $key;

    public static function setUpBeforeClass()
    {
        $temp_dir = sys_get_temp_dir();
        Cache::configure(array(
          'cache_path' => $temp_dir,
          'expires' => 0.5, // Half a minute
        ));

        Cache::flush();
    }

    public function assertPreconditions()
    {
        $this->assertTrue(is_writable(Cache::$config['cache_path']));
    }

    public function testSimpleStorage()
    {
        $key = $this->key = uniqid('cache-test-');
        $this->assertTrue(Cache::get($key) === null);

        $this->assertTrue(Cache::put($key, array('abc' => 'def')));

        $item = Cache::get($key);

        $this->assertTrue($item !== null);
        $this->assertTrue(is_array($item));
        $this->assertTrue($item['abc'] === 'def');
    }

    public function testOverrideExample()
    {
        $key = $this->key;
        $this->assertTrue(Cache::put($key, array('abc' => 'fed')));

        $item = Cache::get($key);
        $this->assertTrue($item !== null);
        $this->assertTrue(is_array($item));
        $this->assertTrue($item['abc'] === 'fed');
    }

    public function testRawStorage()
    {
        $key = $this->key;
        $this->assertTrue(Cache::put($key, 'abcd', true));
        $this->assertTrue(Cache::get($key, true) === 'abcd');
    }

    public function testDelete()
    {
        $this->assertTrue(Cache::delete($this->key));
        $this->assertTrue(Cache::get($this->key) === null);
    }

    public function testFlush()
    {
        $this->assertTrue(Cache::put($this->key, 'whatever'));
        $this->assertTrue(Cache::flush());
        $this->assertTrue(Cache::get($this->key) === null);
    }
}
