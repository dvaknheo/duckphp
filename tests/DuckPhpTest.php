<?php
namespace tests\DuckPhp;

use DuckPhp\DuckPhp;
use DuckPhp\Core\SingletonExTrait as SingletonExTrait;
use DuckPhp\Ext\Misc;
use DuckPhp\Component\Configer;
use DuckPhp\Core\View;
use DuckPhp\Core\Route;
use DuckPhp\Core\PhaseContainer;

class DuckPhpTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(DuckPhp::class);
        $LibCoverage = \LibCoverage\LibCoverage::G();
        $path = \LibCoverage\LibCoverage::G()->getClassTestPath(DuckPhp::class);

        $path_view= $path.'view/';
        $options=[
            'log_sql_query'=>true,
            'path_view'=>$path_view,
            'path_info_compact_enable'=>true,
            'cli_command_with_app' => true,

        ];
        DuckPhp::_()->init($options);

        $options['path'] = $path;
        $options['data_file_enable']=true;
        
        @unlink($path.'config/DuckPhpApps.config.php');
        DuckPhp_Sub::_(new DuckPhp_Sub())->init($options);

        

        $options['ext'][DuckPhp_Sub::class]=['test'=>DATE(DATE_ATOM)];
        DuckPhp::_(new DuckPhp())->init($options);
        
        /////////////
        
        $options=[
            'is_debug' => true,
            'cli_enable'=>false,
            'path' =>$path,
            
            
            'ext'=>[
                DuckPhp_Sub::class =>[
                    'cli_enable'=>false,
                    'cli_mode' => 'hook',
                    'controller_url_prefix'=>'advance/',
                    'exception_reporter' => FakeReporter::class,
                    
                ],
            ],
        ];
$options = [
            'data_file_enable'=>true,
            'ext_options_file'=>'NoExits.php',
            'cli_enable'=>true,
        ];
        DuckPhp::_(new DuckPhp())->init($options);
PhaseContainer::RestAllContainerForTesting();

        DuckPhp_Sub::_(new DuckPhp_Sub());
        DuckPhp::_(new DuckPhp())->init([
                'app' => [ 
                    DuckPhp_Sub::class => [
                        'local_database'=>true,'local_redis'=>true
                    ]
                ]
            ]
        );
PhaseContainer::RestAllContainerForTesting();
        $data = include(__DIR__.'/data_for_tests/setting.php');
        $database_list=$data['database_list'];
        DuckPhp::_(new DuckPhp())->init([
                'database_list'=> $database_list,
                'app' => [ 
                    DuckPhp_Sub::class => [
                        'database_driver'=>'sqlite'
                    ]
                ]
            ]
        );
PhaseContainer::RestAllContainerForTesting();
        $data = include(__DIR__.'/data_for_tests/setting.php');
        $database_list=$data['database_list'];
        DuckPhp::_(new DuckPhp())->init([
                'use_user_view' => true,
                'use_admin_view' => true,
                'database_list'=> $database_list,
                'app' => [ 
                    DuckPhp_Sub::class => [
                        'database_driver'=>'xx'
                    ]
                ]
            ]
        );
        
        
        DuckPhp::_()->regConsoleCommand('MyClass','prefix_');
        
        __l("xx");
        DuckPhp::_()->options['lang_handler']=function($str, $args = []){ return $str;};
        __l("xx");
        //////////////////////
        // _Show() 三分支测试
        Route::_()->calling_class = '';
        ob_start();
        DuckPhp::_()->_Show(['A'=>'b'], $path.'view/block');
        $out_show = ob_get_clean();
        $this->assertStringContainsString('Block', $out_show);
        
        // use_user_view 分支：路由调用类实现 UserControllerInterface
        DuckPhp::_(new DuckPhp())->init([
            'user_provider' => FakeUser::class,
            'admin_provider' => FakeAdmin::class,
                'use_user_view' => true,
                'use_admin_view' => true,

            'path_view' => $path.'view/',
        ]);
        Route::_()->calling_class = FakeUserController::class;
        try {
            DuckPhp::_()->_Show(['A'=>'b'], $path.'view/block');
        } catch (\Throwable $ex) {
        }
        // use_admin_view 分支：路由调用类实现 AdminControllerInterface
        Route::_()->calling_class = FakeAdminController::class;
        try {
            DuckPhp::_()->_Show(['A'=>'b'], $path.'view/block');
        } catch (\Throwable $ex) {
        }
        Route::_()->calling_class = '';
        //////////////////////
        // 回归测试：$view 为空串时必须回落到「当前路由调用路径」当视图名
        // （此处曾是没赋值的死表达式：`$view === '' ? Route::_()->getRouteCallingPath() : $view;`）
        Route::_()->calling_path = 'block';
        ob_start();
        DuckPhp::_()->_Show(['A' => 'b'], '');
        $out_fallback = ob_get_clean();
        $this->assertStringContainsString('Block', $out_fallback);
        Route::_()->calling_path = '';
        //////////////////////

        \LibCoverage\LibCoverage::G($LibCoverage);
        \LibCoverage\LibCoverage::End(DuckPhp::class);

    }

}
class FakeController
{
    public function action_hitme()
    {
        var_dump("hit!!!!!!!!!!!!!!");
    }
}
class DuckPhp_Sub extends DuckPhp
{
    public $options =[
        'class_session' => FakeSession::class,
        'admin_provider' => FakeAdmin::class,
        'user_provider' => FakeUser::class,
        
        'namespace_controller' => 'zz',
        'database_driver' =>'unknown',
    ];

}
class fakeSwooleHttpd
{
    public static function system_wrapper_get_providers()
    {
        return [];
    }
    public function is_with_http_handler_root()
    {
        return true; // return false;
    }
    public function set_http_exception_handler(callable $callback)
    {
        return;
    }
    public function set_http_404_handler(callable $callback)
    {
        return;
    }
}
class FakeSession
{
    use SingletonExTrait;
    public function init($options = [], $context = null)
    {
        
    }
}
class FakeService
{
    use SingletonExTrait;
}
class FakeObject 
{
    use SingletonExTrait;
}
class FakeAdmin
{
    use SingletonExTrait;

    public function init($options = [], $context = null)
    {
        return $this;
    }
    public function id()
    {
        return 1;
    }
    public function data()
    {
        return 1;
    }
    public function _Show(array $data, string $view = '')
    {
        return;
    }
}
class FakeUser
{
    use SingletonExTrait;

    public function init($options = [], $context = null)
    {
        return $this;
    }
    public function id()
    {
        return 1;
    }
    public function data()
    {
        return 1;
    }
    public function _Show(array $data, string $view = '')
    {
        return;
    }
}
class FakeReporter
{
    use \DuckPhp\Foundation\ExceptionReporterTrait;
}
class FakeUserController implements \DuckPhp\GlobalUser\UserControllerInterface
{
}
class FakeAdminController implements \DuckPhp\GlobalAdmin\AdminControllerInterface
{
}

