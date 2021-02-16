<?php
namespace tests\DuckPhp\Ext;

use DuckPhp\Ext\MyMiddlewareManager;
use DuckPhp\Core\App;

class MyMiddlewareManagerTest extends \PHPUnit\Framework\TestCase
{

    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(MyMiddlewareManager::class);
        
        App::RunQuickly([
            'namespace'=>'tests\DuckPhp\Ext',
            'namespcae_controller'=>'',
            'is_debug'=>true,
            'ext'=>[
                MyMiddlewareManager::class => true,
            ],
            'middleware' =>[
                X::class . '@handle',
                Y::class . '->handle',
                Z::class . '::handle',
            ],
        ]);
        \LibCoverage\LibCoverage::End();
    }
}
class X
{
    use \DuckPhp\SingletonEx\SingletonExTrait;
    public function handle($request, \Closure $next)
    {
        var_dump('[[XXXX[['); 

		$response = $next($request);
        var_dump(']]XXXX]]');
        return $response;
    }
}
class Y
{
    public function handle($request, \Closure $next)
    {
        var_dump('[[YYYY[[');

		$response = $next($request);
        var_dump(']]YYYY]]');
        return $response;
    }
}
class Z
{
    public static function handle($request, \Closure $next)
    {
        var_dump('[[ZZZZ[[<pre>');

		$response = $next($request);

        var_dump(']]ZZZZ]]');
        return $response;
    }
}
class Main
{
    public function index(){}
}
