<?php 
namespace tests\DNMVCS\Ext;
use DNMVCS\Ext\RedisSimpleCache;
use DNMVCS\Ext\RedisManager;

class RedisSimpleCacheTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(RedisSimpleCache::class);
        
        $options=[
            'redis_list'=>[[
                'host'=>'127.0.0.1',
                'port'=>'6379',
                'auth'=>'cgbauth',
                'select'=>'2',
            ]],
        ];
        RedisManager::G()->init($options);
        
        RedisSimpleCache::G()->init([]);
        RedisSimpleCache::G()->initWithServer(RedisManager::Redis(),'');
        
        $key="ABC";
        $keys=["A","B"];
        $value=DATE(DATE_ATOM);
        $values=['1',"2"];
        $ttl=3600;
        $default="111111";
        RedisSimpleCache::G()->get($key, $default);
        RedisSimpleCache::G()->set($key, $value, $ttl );
        RedisSimpleCache::G()->delete($key);
        RedisSimpleCache::G()->has($key);
        RedisSimpleCache::G()->clear();
        RedisSimpleCache::G()->getMultiple($keys, $default);
        RedisSimpleCache::G()->setMultiple($values, $ttl);
        RedisSimpleCache::G()->deleteMultiple($keys);
        
        \MyCodeCoverage::G()->end(RedisSimpleCache::class);
        $this->assertTrue(true);
        /*

        //*/
    }
}
