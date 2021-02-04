<?php 
namespace tests\DuckPhp\Ext;

use DuckPhp\DuckPhp;
use DuckPhp\Ext\RedisCache;
use DuckPhp\Ext\RedisManager;

class RedisCacheTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(RedisCache::class);
        
        $path_setting = \LibCoverage\LibCoverage::G()->getClassTestPath(RedisCache::class);
        $setting = include $path_setting . 'setting.php';
        $redis_list = $setting['redis_list'];

        $options=[
            'redis_list'=>$redis_list,
            'ext'=>[
                RedisManager::class => true,
                RedisCache::class => true,
            ],
        ];
        DuckPhp::G()->init($options);
        
        /*
        $options=[
            'redis_cache_skip_replace' => false,
            'redis_cache_prefix' => '',
        ];
        RedisCache::G()->init($options,DuckPhp::G());
        DuckPhp::Cache(RedisCache::G());
        */
        
        $key="ABC";
        $keys=["A","B"];
        $value=['z'=>DATE(DATE_ATOM)];
        $values=['1',"2"];
        $ttl=3600;
        $default="111111";
        DuckPhp::Cache()->set($key, $value, $ttl );
        $t=DuckPhp::Cache()->get($key, $default);
        var_dump($t);
        DuckPhp::Cache()->delete($key);
        DuckPhp::Cache()->has($key);
        DuckPhp::Cache()->clear();
        DuckPhp::Cache()->getMultiple($keys, $default);
        DuckPhp::Cache()->setMultiple($values, $ttl);
        DuckPhp::Cache()->deleteMultiple($keys);
        DuckPhp::Cache()->redis=null;
        DuckPhp::Cache()->get($key, $default);
        DuckPhp::Cache()->set($key, $value, $ttl );
        DuckPhp::Cache()->delete($key);
        DuckPhp::Cache()->has($key);
        
        DuckPhp::Cache()->isInited();

        \LibCoverage\LibCoverage::End();
        /*

        //*/
    }
}
