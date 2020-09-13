<?php 
namespace tests\DuckPhp\Ext;
use DuckPhp\Ext\RedisManager;
use DuckPhp\Core\App;

class RedisManagerTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(RedisManager::class);
        $redis_list = include \MyCodeCoverage::G()->options['path_data'] . 'redis_list.php';
        //code here
        $options=[
            'redis'=>$redis_list[0],
            //'redis_list'=>$redis_list,
        ];
        RedisManager::G()->init($options,App::G()->init(['skip_setting_file'=>true,'redis_list'=>$redis_list,]));
        RedisManager::G()->Redis();
        
        \MyCodeCoverage::G()->end();
        /*
        RedisManager::G()->init($options = [], $context = null);
        RedisManager::G()->Redis();
        //*/
    }
}
