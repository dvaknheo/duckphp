<?php 
namespace tests\DuckPhp\Component;
use DuckPhp\Component\RedisManager;
use DuckPhp\Core\PhaseContainer;
use DuckPhp\DuckPhp;
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
            'path' => __DIR__,
            //'redis'=>$redis_list[0],
            //'redis_list'=>$redis_list,
        ];
        DuckPhp::_()->init($options);
PhaseContainer::RestAllContainerForTesting();

        $options['redis']=$redis_list[0];
        DuckPhp::_()->init($options);
        RedisManager::_()->getRedisConfigList();

        //*
        RedisManager::_()->init($options = [], $context = null);
        RedisManager::_()->Redis();
        //*/
        \LibCoverage\LibCoverage::End();
    }
}
