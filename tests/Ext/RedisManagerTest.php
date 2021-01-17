<?php 
namespace tests\DuckPhp\Ext;
use DuckPhp\Ext\RedisManager;
use DuckPhp\Core\App;

class RedisManagerTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(RedisManager::class);
        
        $path_setting = \MyCodeCoverage::GetClassTestPath(RedisManager::class);
        $setting = include $path_setting . 'setting.php';
        $redis_list = $setting['redis_list'];
        
        //code here
        $options=[
            'redis'=>$redis_list[0],
            //'redis_list'=>$redis_list,
        ];
        RedisManager::G()->init($options,App::G()->init(['redis_list'=>$redis_list,]));
        RedisManager::G()->Redis();
        
        \MyCodeCoverage::G()->end();
        /*
        RedisManager::G()->init($options = [], $context = null);
        RedisManager::G()->Redis();
        //*/
    }
}
