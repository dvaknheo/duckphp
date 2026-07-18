<?php
namespace tests\DuckPhp\Component;

use DuckPhp\Component\GlobalEvent;
use DuckPhp\Core\App;
use DuckPhp\DuckPhp;

class GlobalEventTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(GlobalEvent::class);
        $options = [
            'app'=>[
                GemAppChild::class => [
                    'not empty',
                ]
            ],
        ];
        GemApp::_()->init($options,null);
        
        \LibCoverage\LibCoverage::End();
    }
    
}
class GemApp extends DuckPhp
{
    public function onInited(): void
    {
        GlobalEvent::_()->fire('GEMvent1',"abc");
        GlobalEvent::_()->fire('NoExists',"abc");
        GlobalEvent::_()->all();
        GlobalEvent::_()->fire('GEMvent2');
    }
}
class GemAppChild extends DuckPhp
{
    public static function Callback($data)
    {
        var_dump(App::Phase(),$data);
    }
    public static function Callback2()
    {
        GlobalEvent::_()->remove('NoExists');
        
        GlobalEvent::_()->remove('GEMvent1',App::Phase(),[static::class,'Callback']);
        
        GlobalEvent::_()->remove('GEMvent2');
        GlobalEvent::_()->all();
    }
    public function onInited(): void
    {
        GlobalEvent::_()->on('GEMvent1',App::Phase(),[static::class,'Callback']);
        GlobalEvent::_()->on('GEMvent1',App::Phase(),[static::class,'Callback']);
        GlobalEvent::_()->on('GEMvent2',App::Phase(),[static::class,'Callback2']);
    }
}
