<?php 
namespace tests\DuckPhp\Ext;
use DuckPhp\Ext\RedisManager;
use DuckPhp\Core\App;

class RedisManagerTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(RedisManager::class);
        
        //code here
        $options=[
            'redis_list'=>[[
                'host'=>'127.0.0.1',
                'port'=>'6379',
                'auth'=>'cgbauth',
                'select'=>'2',
            ]],
        ];
        RedisManager::G()->init($options,App::G()->init(['skip_setting_file'=>true,'redis_list'=>[[
                'host'=>'127.0.0.1',
                'port'=>'6379',
                'auth'=>'cgbauth',
                'select'=>'2',
            ]]]));
        RedisManager::G()->Redis();
        RedisManager::SimpleCache();
                RedisManager::G()->isInited();

        \MyCodeCoverage::G()->end(RedisManager::class);
        $this->assertTrue(true);
        /*
        RedisManager::G()->init($options = [], $context = null);
        RedisManager::G()->Redis();
        //*/
    }
}
