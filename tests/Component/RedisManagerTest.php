<?php 
namespace tests\DuckPhp\Component;
use DuckPhp\Component\RedisManager;
use DuckPhp\Core\App;

class RedisManagerTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(RedisManager::class);
        $setting_file=realpath(__DIR__.'/../').'/data_for_tests/setting.php';
        $setting = include($setting_file);
        $redis_list = $setting['redis_list'];

        
        //code here
        $options=[
            'redis'=>$redis_list[0],
            'force' =>true,
            //'redis_list'=>$redis_list,
        ];
        RedisManager::_()->init($options,App::_()->init(['redis_list'=>$redis_list,]));
        $options['redis_force_reinit'] = true; 
        RedisManager::_()->init($options,App::_()->init(['redis_list'=>$redis_list,]));
        RedisManager::_()->getRedisConfigList();

        //*
        RedisManager::_()->init($options = [], $context = null);
        RedisManager::_()->Redis();
        //*/
        \LibCoverage\LibCoverage::End();
    }
}
