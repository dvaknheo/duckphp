<?php
namespace tests\DuckPhp\Ext;

use DuckPhp\Ext\MyMiddlewareManager;
use DuckPhp\DuckPhp as App;
use DuckPhp\Core\SingletonExTrait as SingletonExTrait;

class MyMiddlewareManagerTest extends \PHPUnit\Framework\TestCase
{

    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(MyMiddlewareManager::class);
        
        App::RunQuickly([
            'cli_enable'=>false,
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

        ////// 短路语义：不调 $next 且给了响应 ⇒ 输出它并返回 true（控制器不再执行） //////
        $manager = new MiddlewareManagerStub();
        $manager->init(['middleware' => [Block::class . '@handle']]);
        ob_start();
        $flag = $manager->doHook('/');
        $out = ob_get_clean();
        $this->assertSame(true, $flag);              // 声明「这个请求已处理」
        $this->assertSame('BLOCKED', $out);          // 管理器把响应吐出来了
        $this->assertSame(0, $manager->innerCount);  // 内层默认路由没跑 ⇒ 控制器不会执行

        // 短路但用布尔值表达「我处理了」（自己已经输出过）⇒ 也是 true，且管理器不重复输出
        $manager = new MiddlewareManagerStub();
        $manager->init(['middleware' => [BoolBlock::class . '@handle']]);
        ob_start();
        $flag = $manager->doHook('/');
        $out = ob_get_clean();
        $this->assertSame(true, $flag);
        $this->assertSame('', $out);
        $this->assertSame(0, $manager->innerCount);

        // 没调 $next 但 return null/false ⇒ 视为「没处理」，保持老行为（放行给默认路由）
        $manager = new MiddlewareManagerStub();
        $manager->init(['middleware' => [NullBlock::class . '@handle']]);
        ob_start();
        $flag = $manager->doHook('/');
        $out = ob_get_clean();
        $this->assertSame(false, $flag);
        $this->assertSame('', $out);
        $this->assertSame(0, $manager->innerCount);

        // 放行：调了 $next ⇒ 内层跑过一次，返回值由内层决定
        $manager = new MiddlewareManagerStub();
        $manager->init(['middleware' => [Pass::class . '@handle']]);
        ob_start();
        $flag = $manager->doHook('/');
        $out = ob_get_clean();
        $this->assertSame(true, $flag);
        $this->assertSame('', $out);
        $this->assertSame(1, $manager->innerCount);

        // 多次调用互不串味：上一轮的 defaultResult 不能留在下一轮
        $manager->init(['middleware' => [NullBlock::class . '@handle']]);
        $this->assertSame(false, $manager->doHook('/'));

        \LibCoverage\LibCoverage::End();
    }
}
class MiddlewareManagerStub extends MyMiddlewareManager
{
    public $innerCount = 0;
    protected function runSelfMiddleware(): string
    {
        $this->innerCount++;
        $this->defaultResult = true;   // 假装跑过默认路由并成功
        return 'INNER';
    }
}
class Block
{
    use SingletonExTrait;
    public function handle($request, \Closure $next)
    {
        return 'BLOCKED';
    }
}
class BoolBlock
{
    use SingletonExTrait;
    public function handle($request, \Closure $next)
    {
        return true;
    }
}
class NullBlock
{
    use SingletonExTrait;
    public function handle($request, \Closure $next)
    {
        return null;   // 既没调 $next，也没给响应
    }
}
class Pass
{
    use SingletonExTrait;
    public function handle($request, \Closure $next)
    {
        return $next($request);
    }
}
class X
{
    use SingletonExTrait;
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
