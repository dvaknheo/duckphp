<?php 
namespace tests\DuckPhp\Ext;

use DuckPhp\DuckPhp;
use DuckPhp\Ext\RedisCache;
use DuckPhp\Ext\RedisManager;

class RedisCacheTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(RedisCache::class);
        $redis_list = include \MyCodeCoverage::G()->options['path_data'] . 'redis_list.php';
        $options=[
            'skip_setting_file'=>true,
            'redis_list'=>$redis_list,
            'ext'=>[
                RedisManager::class => true,
            ],
        ];
        DuckPhp::G()->init($options);
        RedisManager::G()->init($options);
        
        $options=[
            'redis_cache_skip_replace' => false,
            'redis_cache_prefix' => '',
        ];
        RedisCache::G()->init($options,DuckPhp::G());
        
        $key="ABC";
        $keys=["A","B"];
        $value=['z'=>DATE(DATE_ATOM)];
        $values=['1',"2"];
        $ttl=3600;
        $default="111111";
        RedisCache::G()->set($key, $value, $ttl );
        $t=RedisCache::G()->get($key, $default);
        var_dump($t);
        RedisCache::G()->delete($key);
        RedisCache::G()->has($key);
        RedisCache::G()->clear();
        RedisCache::G()->getMultiple($keys, $default);
        RedisCache::G()->setMultiple($values, $ttl);
        RedisCache::G()->deleteMultiple($keys);
        RedisCache::G()->redis=null;
        RedisCache::G()->get($key, $default);
        RedisCache::G()->set($key, $value, $ttl );
        RedisCache::G()->delete($key);
        RedisCache::G()->has($key);
        
        RedisCache::G()->isInited();

        \MyCodeCoverage::G()->end();
        /*

        //*/
    }
}
