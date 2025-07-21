<?php 
namespace tests\DuckPhp\Component;

use DuckPhp\DuckPhpAllInOne as DuckPhp;
use DuckPhp\Component\RedisCache;
use DuckPhp\Component\RedisManager;
use DuckPhp\Core\App;

class RedisCacheTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(RedisCache::class);
        $setting_file=realpath(__DIR__.'/../').'/data_for_tests/setting.php';
        $setting = include($setting_file);
        $redis_list = $setting['redis_list'];
        
        
        $options=[
            //'setting_file' => $setting_file,
            'redis_list'=>$redis_list,
            'ext'=>[
                RedisManager::class => true,
                RedisCache::class => true,
            ],
        ];
        DuckPhp::_()->init($options);
        
        /*
        $options=[
            'redis_cache_skip_replace' => false,
            'redis_cache_prefix' => '',
        ];
        RedisCache::_()->init($options,DuckPhp::_());
        DuckPhp::Cache(RedisCache::_());
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
