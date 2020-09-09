<?php
namespace tests\DuckPhp\Ext;

use DuckPhp\Ext\EventManager;

class EventManagerTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(EventManager::class);
        EventManager::OnEvent('MyEvent',function(...$args){ var_dump($args);});
        EventManager::OnEvent('MyEvent',[static::class, 'callit']);
        EventManager::OnEvent('MyEvent',[static::class, 'callit']);
        EventManager::FireEvent('MyEvent','A','B','C');
        EventManager::FireEvent('NoExist','A','B','C');
        var_dump(EventManager::AllEvents());
        EventManager::RemoveEvent('MyEvent',[static::class, 'callit']);
        EventManager::RemoveEvent('NoExist');
        EventManager::RemoveEvent('MyEvent');
        \MyCodeCoverage::G()->end();
    }
    public static function callit()
    {
        return;
    }
    
}
