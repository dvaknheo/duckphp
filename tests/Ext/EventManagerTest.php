<?php
namespace tests\DuckPhp\Ext;

use DuckPhp\Ext\EventManager;

class EventManagerTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(EventManager::class);
        EventManager::OnEvent('MyEvent',function(...$args){ var_dump($args);});
        EventManager::FireEvent('MyEvent','A','B','C');
        EventManager::FireEvent('NoExist','A','B','C');
        \MyCodeCoverage::G()->end();
    }
    
}
