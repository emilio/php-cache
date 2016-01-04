<?php
namespace Benchmarks;

use Athletic\AthleticEvent;
use Cache\Cache;

class CachingEvent extends AthleticEvent
{
    private $key;
    private $big_array;

    public function classSetUp()
    {
        Cache::configure(array(
            'cache_path' => sys_get_temp_dir(),
            'expires' => .5,
        ));

        $this->key = uniqid('cache-test');

        $this->big_array = array();

        $last = $this->big_array;
        for ($i = 0; $i < 10; $i += 1) {
            for ($i = 0; $i < 10000; $i += 1) {
                $last[] = 'heahe';
            }

            $last = $last[0]; // increase depth in one item
        }
    }

    public function tearDown()
    {
        Cache::flush();
    }

    public function putImpl($element, $raw = false)
    {
        Cache::put($this->key, $element, $raw);
    }

    public function getImpl($raw = false)
    {
        Cache::get($this->key, $raw);
    }

    /**
     * @iterations 1000
     */
    public function putSmall()
    {
        $this->putImpl('sdafdsa');
        $this->getImpl();
    }

    /**
     * @iterations 1000
     */
    public function putSmallRaw()
    {
        $this->putImpl('sdafdsa', true);
        $this->getImpl(true);
    }

    /**
     * @iterations 1000
     */
    public function putBig()
    {
        $this->putImpl($this->big_array, true);
        $this->getImpl();
    }

    public function classTearDown()
    {
        Cache::flush();
    }
}
