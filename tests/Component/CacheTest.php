<?php 
namespace tests\DuckPhp\Component;
use DuckPhp\Component\Cache;
use DuckPhp\Component\RedisManager;

class CacheTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(Cache::class);

        $key="ABC";
        $keys=["A","B"];
        $value=['z'=>DATE(DATE_ATOM)];
        $values=['1',"2"];
        $ttl=3600;
        $default="111111";
        Cache::_()->set($key, $value, $ttl );
        $t=Cache::_()->get($key, $default);
        var_dump($t);
        Cache::_()->delete($key);
        Cache::_()->has($key);
        Cache::_()->clear();
        Cache::_()->getMultiple($keys, $default);
        Cache::_()->setMultiple($values, $ttl);
        Cache::_()->deleteMultiple($keys);
        Cache::_()->redis=null;
        Cache::_()->get($key, $default);
        Cache::_()->set($key, $value, $ttl );
        Cache::_()->delete($key);
        Cache::_()->has($key);
        

        \LibCoverage\LibCoverage::End();
        /*

        //*/
    }
}
