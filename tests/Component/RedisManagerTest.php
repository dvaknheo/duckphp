<?php 
namespace tests\DuckPhp\Component;
use DuckPhp\Component\RedisManager;
use DuckPhp\Core\App;

class RedisManagerTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(RedisManager::class);
        
        $path_setting = \LibCoverage\LibCoverage::G()->getClassTestPath(RedisManager::class);
        $setting = include $path_setting . 'setting.php';
        $redis_list = $setting['redis_list'];
        
        //code here
        $options=[
            'redis'=>$redis_list[0],
            //'redis_list'=>$redis_list,
        ];
        RedisManager::G()->init($options,App::G()->init(['redis_list'=>$redis_list,]));
        $options['redis_force_reinit'] = true; 
        RedisManager::G()->init($options,App::G()->init(['redis_list'=>$redis_list,]));
        //*
        RedisManager::G()->init($options = [], $context = null);
        RedisManager::G()->Redis();
        //*/
        \LibCoverage\LibCoverage::End();
    }
}
