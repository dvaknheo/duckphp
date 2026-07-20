<?php
namespace tests\DuckPhp\Core{

use DuckPhp\DuckPhp as App;
use DuckPhp\Core\App as OldApp;
use DuckPhp\Core\KernelTrait;
use DuckPhp\Core\ComponentBase;

use DuckPhp\Core\Runtime;
use DuckPhp\Core\Logger;
use DuckPhp\DuckPhp;
use DuckPhp\Component\Configer;
use DuckPhp\Core\View;
use DuckPhp\Core\Route;
use DuckPhp\Core\SingletonTrait as SingletonExTrait;
use DuckPhp\Ext\Pager;
use DuckPhp\Core\PhaseContainer;

class KernelTraitTest extends \PHPUnit\Framework\TestCase
{
    public static function Blank()
    {
    }
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(KernelTrait::class);
        $LibCoverage = \LibCoverage\LibCoverage::G();
        
        $path_app=\LibCoverage\LibCoverage::G()->getClassTestPath(OldApp::class);
        $path_config=\LibCoverage\LibCoverage::G()->getClassTestPath(Configer::class); //???
        
        $options=[
            'path' => $path_app,
            'path_config' => $path_config,
            'namespace' => __NAMESPACE__,
            'is_debug' => true,
            'error_exception' => NULL,
            'error_500' => NULL,
            'error_404' => NULL,
            'error_debug' => [static::class,'Blank'],
            'use_super_global' => true,
            'override_class'=>'\\'.KernelTestApp::class,
            'on_init' =>function (){ echo 'Inited!';},
            'cli_enable' => true,
            
            'controller_class_postfix' => 'Controller',
            'on_initing' => function(){},
            'on_inited' => function(){},
            'on_serve' => function(){},
        ];
        $options['ext']=[
            KernelTestObjectB::class=>['aa'=>'22'],
        ];
        App::RunQuickly($options,function(){});
        $options['cli_enable'] = false;
        App::RunQuickly($options,function(){});
        App::_()->options['cli_enable'] =false;
        App::_()->getProjectPath();
        
        //App::SG()->_SERVER['PATH_INFO']='/NOOOOOOOOOOOOOOO';
        Route::_()->bind('/NOOOOOOOOOOOOOOO');  // 这两句居然有区别 ,TODO ，分析之
        
        App::_()->options['error_404']=function(){
            echo "noooo 404  ooooooooo\n";
            
        };
        
        App::_()->options['controller_class_postfix']='';
        App::_()->options['controller_method_prefix']='';
        App::_()->run();
        echo "-------------------------------------\n";
            
        Route::_()->bind('/exception');
        App::_()->run();

        try{
            App::_()->options['skip_exception_check']=true;
            App::_()->options['controller_class_postfix']='';
            App::_()->options['controller_method_prefix']='';
            
            Route::_()->bind('/exception');
            App::_()->run();
        }catch(\Throwable $ex){
            echo $ex->getMessage();
        }
        App::_()->options['skip_exception_check']=false;
        
        Route::_()->bind('/exception2');
        App::_()->run();
        //////////////////////////////////////////////////
        App::_(new App());

        App::_()->init([
            'handle_all_dev_error' => false,
            'handle_all_exception' => false,
//          'override_class' => 'no_Exits',
        ]);
        App::_()->init([
            'handle_all_dev_error' => false,
            'handle_all_exception' => false,
            'override_class' => App::class,
        ]);
        App::_()->init([
            'handle_all_dev_error' => false,
            'handle_all_exception' => false,
            'use_autoloader' => true,
        ]);
        ////////////////////////
        MyKernelTrait::OnDefaultException(new \Exception("error"));
        MyKernelTrait::OnDevErrorHandler("", "", "", "");
        MyKernelTrait::On404();

        $options['cli_enable']=false;
        $options['app'][KernelTestApp2::class]=[
            'path'=>null,
            'namespace' => __NAMESPACE__,
            'controller_url_prefix'=>'/child/',
        ];
        PhaseContainer::RestAllContainerForTesting();
        DuckPhp::_()->init($options);

        $_SERVER['PATH_INFO'] = '/child/date';
        //Route::_()->bind();
        App::_()->serve();
        $phase=App::Phase();
        App::_()->getThisChild(KernelTestApp2::class);
        App::_()->getThisChild(KernelTestApp3::class);
        App::Phase($phase);

        /////////////////////
        
        KernelTestApp::_(new KernelTestApp())->override_class = KernelTestApp::class;
        KernelTestApp::_()->createLocalObject2(Logger::class);
        App::Root()->getThisClassName();
        
        
        ////////////////////////
        $this->doCoverageGapTest();
        ////////////////////////
        $this->doMoreTest();
        
        App::SwitchRootPhase('X');
        
        PhaseContainer::RestAllContainerForTesting();
        MyKernelTrait::_(new MyKernelTrait())->init([]);
        
        \LibCoverage\LibCoverage::G($LibCoverage);
        \LibCoverage\LibCoverage::End();
    return;

    }
    // ======== 新增：覆盖缺口测试 ========
    protected function doCoverageGapTest()
    {
        PhaseContainer::RestAllContainerForTesting();
        
        // 1. getThisCommandPrefix — root app returns ""
        $prefix = KernelTestApp::_()->getThisCommandPrefix();
        $this->assertSame('', $prefix);
        
        // 2. regConsoleCommand — register a command class
        KernelTestApp::_()->regConsoleCommand(KernelTestCoverageCmd::class, 'command_');
        // no assertion: regCommandClassSingle has no return value
        
        // 3. toChildPhase — false path (non-existent child)
        $result = KernelTestApp::_()->toChildPhase('NonExistent\\Class');
        $this->assertFalse($result);
        
        // 4. initContainer 非 root 分支: name='@' → 使用类短名
        PhaseContainer::RestAllContainerForTesting();
        $options4 = [
            'path' => \LibCoverage\LibCoverage::G()->getClassTestPath(OldApp::class),
            'skip_exception_check' => true,
            'name' => '@',  // 应被转为类短名
        ];
        // 作为 root 初始化，然后验证 phase_name 为 ""
        $app4 = KernelTestApp::_(new KernelTestApp())->init($options4);
        $this->assertEquals('', $app4->getThisPhaseName());
        
        // 5. getDefaultProjectNameSpace — 不传 namespace 时自动从类名推导
        PhaseContainer::RestAllContainerForTesting();
        $options5 = [
            'path' => \LibCoverage\LibCoverage::G()->getClassTestPath(OldApp::class),
            'skip_exception_check' => true,
            // 故意不传 namespace
        ];
        $app5 = KernelTestDefaultNsApp::_(new KernelTestDefaultNsApp())->init($options5);
        // 类 tests\DuckPhp\Core\KernelTestDefaultNsApp → 上两层 = tests\DuckPhp
        $this->assertEquals('tests\\DuckPhp', $app5->options['namespace']);
        
        // 6. 钩子方法验证：onBeforeCreatePhases / onAfterCreatePhases / onBeforeChildrenInit / onBeforeRun / onAfterRun
        PhaseContainer::RestAllContainerForTesting();
        ob_start();
        $options6 = [
            'path' => \LibCoverage\LibCoverage::G()->getClassTestPath(OldApp::class),
            'skip_exception_check' => true,
            'namespace' => __NAMESPACE__,
            'cli_enable' => false,
        ];
        $app6 = KernelTestHookApp::_(new KernelTestHookApp())->init($options6);
        Route::_()->bind('/');
        $app6->serve();
        $output6 = ob_get_clean();
        // onBeforeCreatePhases + onAfterCreatePhases 在 init 中触发
        $this->assertStringContainsString('beforeCreatePhases', $output6);
        $this->assertStringContainsString('afterCreatePhases', $output6);
        $this->assertStringContainsString('beforeRun', $output6);
        $this->assertStringContainsString('afterRun', $output6);
        
        // 7. Phase 名冲突异常
        PhaseContainer::RestAllContainerForTesting();
        $caught = false;
        try {
            $parentApp = KernelTestApp::_(new KernelTestApp())->init([
                'path' => \LibCoverage\LibCoverage::G()->getClassTestPath(OldApp::class),
                'skip_exception_check' => true,
                'namespace' => __NAMESPACE__,
                'app' => [
                    KernelTestPhaseClone::class => [
                        'namespace' => __NAMESPACE__,
                        'name' => 'same',  // 两个子应用同名 → 冲突
                    ],
                ],
            ]);
            // 再 init 一个同名子 app
            KernelTestPhaseClone::_(new KernelTestPhaseClone())->init([
                'namespace' => __NAMESPACE__,
                'name' => 'same',
            ], $parentApp);
        } catch (\DuckPhp\Core\DuckPhpSystemException $ex) {
            $caught = true;
            $this->assertStringContainsString('Phase Short name', $ex->getMessage());
        }
        $this->assertTrue($caught, 'Phase name collision should throw DuckPhpSystemException');
        
        // 8. getProjectPath — 访问根 App 的 path 属性
        $project_path = KernelTestApp::Root()->getProjectPath();
        $this->assertNotEmpty($project_path);
        
        // 9. initChildren mix mode: 带 class 键的子应用配置（覆盖第 314-316 行）
        PhaseContainer::RestAllContainerForTesting();
        $options9 = [
            'path' => \LibCoverage\LibCoverage::G()->getClassTestPath(OldApp::class),
            'skip_exception_check' => true,
            'namespace' => __NAMESPACE__,
            'app_children_allow_mix_mode' => true,
            'app' => [
                'my-prefix' => [
                    'class' => KernelTestMixModeChild::class,
                    'namespace' => __NAMESPACE__,
                ],
            ],
        ];
        $app9 = KernelTestApp::_(new KernelTestApp())->init($options9);
    }
    // ======== 新增结束 ========

    protected function doMoreTest()
    {
PhaseContainer::RestAllContainerForTesting();
        try{
        KernelTestApp::_()->init([
            'skip_exception_check'=>true,
            'ext' => [
                'noExsits'=>true,
            ],
        ]);
        }catch(\Exception $ex){}
        try{
        KernelTestApp::_(new KernelTestApp())->init([
            'skip_exception_check'=>true,
            'app' => [
                MyKernelTrait::class =>false,
                'noExsitsxxxx'=>['not empty'],
            ],
        ]);
        }catch(\Exception $ex){
        }
PhaseContainer::RestAllContainerForTesting();
        KernelTestApp::_(new KernelTestApp())->init([
            'skip_exception_check'=>true,
            'app' => [
                KernelTestApp3::class =>[
                    'name'=>'not_empty',
                ],
            ],
        ]);
        //$old_phase = KernelTestApp::Phase();
        KernelTestApp::FromCurrentParent();
        KernelTestApp3::FromCurrentParent();
        
        KernelTestApp::Phase(KernelTestApp::Root()->getThisPhaseName());
        KernelTestApp::_()->getThisChild('NotExsits');
        KernelTestApp::_()->getThisChild(KernelTestApp3::class);
        KernelTestApp3::_()->getThisParent();
        
PhaseContainer::RestAllContainerForTesting();
        KernelTestApp::_(new KernelTestApp())->init([
            'cli_enable'=>true,
            'cmd'=>[
                KernelTestApp::class => true,
            ],
        ]);
        $__SERVER = $_SERVER;
        
        $_SERVER['argv']=[
                '-','test',
        ];

        KernelTestApp::_()->run();
            
        $_SERVER = $__SERVER;
        
    }
    protected function do404()
    {
        
        
        echo "------------xxxxxxxxxxxxxx-----------\n";
        $path_app=\LibCoverage\LibCoverage::G()->getClassTestPath(OldApp::class);
        $path_config=\LibCoverage\LibCoverage::G()->getClassTestPath(Configer::class);
        $options=[
            'path' => $path_app,
            'path_config' => $path_config,
            'platform' => 'BJ',
            'is_debug' => true,
            'use_flag_by_setting' => false,
            'skip_view_notice_error' => true,
            'use_super_global' => true,
            'override_class'=>'\\'.KernelTestApp::class,
        ];
        DuckPhp::_(new DuckPhp())->init($options);

        /*
        $options=[
            'path' => $path_app,
            'is_debug'=>false,
        ];
        KernelTestApp::RunQuickly($options);
        
        
        //*/
        KernelTestApp::_()->options['error_404']='_sys/error-404';
        KernelTestApp::On404();
    }
    
}

class MyKernelTrait extends ComponentBase
{
    use SingletonExTrait;
    use KernelTrait;
    protected $common_options=[];
    protected $core_options=[];
    public function __construct()
    {
        $this->options = array_replace_recursive($this->kernel_options, $this->core_options, $this->common_options, $this->options);
        unset($this->kernel_options); // not use again;
        unset($this->core_options); // not use again;
        unset($this->common_options); // not use again;
        $this->this_class = static::class;
    }
}

class KernelTestApp extends App
{
    public $options =[
        'ext'=>[
            KernelTestObjectA::class =>App::EXT_RENEW,
            KernelTestObjectB::class =>'@toEnable',
        ],
    ];
    protected function onInit()
    {
        return parent::onInit();
    }
    public function createLocalObject2($class)
    {
        return $this->createLocalObject($class);
    }
    public function command_hello()
    {
        var_dump("wwwwwwwwwwwwwworld");
    }
    public function toEnable()
    {
        return true;
    }
}
class KernelTestApp2 extends App
{
    protected function onInit()
    {
        return null;
        //throw new \Exception("zzzzzzzzzzzz");
    }
}
class KernelTestApp3 extends App
{

}
class KernelTestObject
{
    static $x;
    use SingletonExTrait;

    public static function Foo()
    {
        return "OK";
    }
}
class KernelTestObjectA
{
    static $x;
    use SingletonExTrait;
    public static function Foo()
    {
        return "OK";
    }
    public function init($options,$context)
    {
    }
}
class KernelTestObjectB
{
    static $x;
    use SingletonExTrait;
    public static function Foo()
    {
        return "OK";
    }
    public function init($options,$context)
    {
    }
}
class KernelTestObjectError
{
    use SingletonExTrait;
    public function init($options,$context)
    {
        throw new \Exception("test");
    }

}
class ExceptionReporter
{
    use \DuckPhp\Foundation\ExceptionReporterTrait;
    public function defaultException($ex)
    {
        var_dump("exception!");
    }
}
// ======== 新增：覆盖测试辅助类 ========
class KernelTestDefaultNsApp extends App
{
    // 用于测试自动推导 namespace
}
class KernelTestHookApp extends App
{
    protected function onBeforeCreatePhases(): void
    {
        echo 'beforeCreatePhases' . "\n";
    }
    protected function onAfterCreatePhases(): void
    {
        echo 'afterCreatePhases' . "\n";
    }
    protected function onBeforeChildrenInit(): void
    {
        echo 'beforeChildrenInit' . "\n";
    }
    protected function onBeforeRun(): void
    {
        echo 'beforeRun' . "\n";
    }
    protected function onAfterRun(): void
    {
        echo 'afterRun' . "\n";
    }
}
class KernelTestPhaseClone extends App
{
    // 用于测试 phase 名冲突
}
class KernelTestCoverageCmd
{
    use SingletonExTrait;
    public function command_test()
    {
        echo 'coverage_cmd_ok';
    }
}
class KernelTestMixModeChild extends App
{
    // 用于测试 initChildren class-key mix mode（第 314-316 行）
}
// ======== 新增结束 ========
}


namespace tests\DuckPhp\Core\Controller{
class MainController
{
    public function index()
    {
        var_dump("OK");
    }
    public function date()
    {
        var_dump(DATE(DATE_ATOM));
    }
    public function xception()
    {
        throw new \Exception("HAHA");
    }
    public function exception2()
    {
        \DuckPhp\Core\App::Phase("MyPhase");
        throw new \Exception("HAHA");
    }
}
}
