<?php
namespace tests\DuckPhp\Component;

use DuckPhp\Component\EventManager;

class EventManagerTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(EventManager::class);
        EventManager::OnEvent('MyEvent',function(...$args){ var_dump($args);});
        EventManager::OnEvent('MyEvent',[static::class, 'callit']);
        EventManager::OnEvent('MyEvent',[static::class, 'callit']);
        EventManager::FireEvent('MyEvent','A','B','C');
        EventManager::FireEvent('NoExist','A','B','C');
        var_dump(EventManager::AllEvents());
        EventManager::RemoveEvent('MyEvent',[static::class, 'callit']);
        EventManager::RemoveEvent('NoExist');
        EventManager::RemoveEvent('MyEvent');
        \LibCoverage\LibCoverage::End();
    }
    public static function callit()
    {
        return;
    }
    
}
