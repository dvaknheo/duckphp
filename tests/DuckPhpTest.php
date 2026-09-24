<?php
namespace tests\DuckPhp;

use DuckPhp\DuckPhp;
use DuckPhp\Core\SingletonExTrait as SingletonExTrait;
use DuckPhp\Core\Route;
use DuckPhp\Core\PhaseContainer;
use \DuckPhp\Foundation\Controller\ExceptionReporterTrait;

class DuckPhpTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(DuckPhp::class);
        $LibCoverage = \LibCoverage\LibCoverage::_();
        $path = \LibCoverage\LibCoverage::_()->getClassTestPath(DuckPhp::class);

        $path_view= $path.'view/';
        $options=[
            'log_sql_query'=>true,
            'path_view'=>$path_view,
            'path_info_compact_enable'=>true,
            'cli_command_with_app' => true,
            'exception_reporter' => [FakeReporter::class, 'OnException'],

        ];
        
        DuckPhp::_()->init($options);
        try{
        $options['exception_reporter']="CAN_NO call me";
        DuckPhp::_()->init($options);
        }catch(\Exception $ex){}
        PhaseContainer::RestAllContainerForTesting();
        unset($options['exception_reporter']);
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

        // Test exception_reporter with invalid callable (line 134)
        PhaseContainer::RestAllContainerForTesting();
        try {
            DuckPhp::_(new DuckPhp())->init([
                'installed' => true,
                'exception_reporter' => 'not_a_valid_callable',
            ]);
            $this->fail('Should throw');
        } catch (\Throwable $ex) {
        }

        // Test isLocalDatabase() when local_database is true (line 187)
        PhaseContainer::RestAllContainerForTesting();
        DuckPhp::_(new DuckPhp())->init([
            'installed' => true,
            'local_database' => true,
        ]);

        // Test isLocalDatabase() when database_driver differs (line 191)
        PhaseContainer::RestAllContainerForTesting();
        DuckPhp::_(new DuckPhp())->init([
            'installed' => true,
            'database_driver' => 'mysql',
            'app' => [
                DuckPhp_Sub::class => [
                    'database_driver' => 'sqlite',
                ]
            ]
        ]);

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
        PhaseContainer::RestAllContainerForTesting();
        DuckPhp::_(new DuckPhp())->init([
            'use_user_view' => true,
            'use_admin_view' => true,
            'admin_provider_enable' => true,
            'user_provider_enable' => true,
            'ext' => [
                \DuckPhp\GlobalAdmin\GlobalAdmin::class => true,
                \DuckPhp\GlobalUser\GlobalUser::class => true,
            ],
            'path_view' => $path.'view/',
        ]);
        // 给 GlobalUser/GlobalAdmin 各配一个「追加视图数据」回调：这样 _Show() 内部不必再走
        // id()/name()/urlForLogout() 那串 provider（没配 provider 时会在 return 之前抛异常，
        // 于是 DuckPhp::_Show() 的两个 return 分支永远到不了）。
        // 注意：带 context 初始化后 GlobalUser::_() / GlobalAdmin::_() 返回的是 PhaseProxy
        //（只转发方法调用），要经 self() 才能拿到真身去改 options。
        // 回调顺便记录"是谁接手的"，用来证明三条分支各自走了哪条路（三条都会渲染同一个 block 视图）。
        $called = [];
        $user_cb = function ($input) use (&$called) {
            $called[] = 'user';
            return $input;
        };
        $admin_cb = function ($input) use (&$called) {
            $called[] = 'admin';
            return $input;
        };
        \DuckPhp\GlobalUser\GlobalUser::_()->self()->options['globaluser_ext_view_data_callback'] = $user_cb;
        \DuckPhp\GlobalAdmin\GlobalAdmin::_()->self()->options['globaladmin_ext_view_data_callback'] = $admin_cb;
        // GlobalUser/GlobalAdmin 的 _Show() 会把 __logined_id/name/data/url_* 填进视图数据，
        // 这些值现在统一从「登录会话」取，所以这里要给出会话回调（globaluser_/globaladmin_login_session），
        // 否则没配会话时会抛 " need ext options 'globaluser_login_session'"，两个分支根本走不到。
        \DuckPhp\GlobalUser\GlobalUser::_()->self()->options['globaluser_login_session'] = function () {
            return FakeLoginedSession::_();
        };
        \DuckPhp\GlobalAdmin\GlobalAdmin::_()->self()->options['globaladmin_login_session'] = function () {
            return FakeLoginedSession::_();
        };

        Route::_()->calling_class = FakeUserController::class;
        ob_start();
        DuckPhp::_()->_Show(['__use_logined_view_data' => true, 'A'=>'b'], $path.'view/block');
        $out_user_view = ob_get_clean();
        $this->assertStringContainsString('Block', $out_user_view);
        $this->assertSame(['user'], $called, 'UserControllerInterface 分支应由 GlobalUser::_Show() 接手');

        // use_admin_view 分支：路由调用类实现 AdminControllerInterface
        // 同时带上 __use_logined_header_footer_file（控制器里由 assignViewData 设置），
        // 让 _Show() 顺带走 View::setViewHeadFoot() 那一步；给出真实的头/尾视图文件以便断言。
        \DuckPhp\GlobalAdmin\GlobalAdmin::_()->self()->options['globaladmin_view_file_header'] = $path.'view/block.php';
        \DuckPhp\GlobalAdmin\GlobalAdmin::_()->self()->options['globaladmin_view_file_footer'] = $path.'view/block.php';
        Route::_()->calling_class = FakeAdminController::class;
        ob_start();
        DuckPhp::_()->_Show(['__use_logined_view_data' => true, '__use_logined_header_footer_file' => true, 'A'=>'b'], $path.'view/block');
        $out_admin_view = ob_get_clean();
        $this->assertSame(3, substr_count($out_admin_view, 'Block'), '头/尾文件 + 正文各渲染一次');
        $this->assertSame(['user', 'admin'], $called, 'AdminControllerInterface 分支应由 GlobalAdmin::_Show() 接手');
        \DuckPhp\GlobalAdmin\GlobalAdmin::_()->self()->options['globaladmin_view_file_header'] = null;
        \DuckPhp\GlobalAdmin\GlobalAdmin::_()->self()->options['globaladmin_view_file_footer'] = null;

        // 开了 __use_logined_view_data，但调用类两个接口都不实现 → 回落父类 _Show
        // （_Show() 会把 $data 并进 View::_()->data，上一步的 header/footer 开关会粘住，这里显式关掉）
        Route::_()->calling_class = FakeController::class;
        ob_start();
        DuckPhp::_()->_Show(['__use_logined_view_data' => true, '__use_logined_header_footer_file' => false, 'A'=>'b'], $path.'view/block');
        $out_plain_view = ob_get_clean();
        $this->assertStringContainsString('Block', $out_plain_view);
        $this->assertSame(['user', 'admin'], $called, '两个接口都不是时应回落父类 _Show，不经 GlobalUser/GlobalAdmin');

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

        \LibCoverage\LibCoverage::_($LibCoverage);
        \LibCoverage\LibCoverage::End();

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
class FakeLoginedSession
{
    use SingletonExTrait;

    protected $user = ['id' => 1, 'name' => 'test_user'];
    protected $admin = ['id' => 1, 'name' => 'test_admin'];

    public function init($options = [], $context = null)
    {
        return $this;
    }
    public function setCurrentUser($user)
    {
        $this->user = $user;
    }
    public function unsetCurrentUser()
    {
        $this->user = null;
    }
    public function getCurrentUser()
    {
        return $this->user;
    }
    public function getCurrentUserName()
    {
        return 'test_user';
    }
    public function getCurrentUserId()
    {
        return 1;
    }
    public function setCurrentAdmin($admin)
    {
        $this->admin = $admin;
    }
    public function unsetCurrentAdmin()
    {
        $this->admin = null;
    }
    public function getCurrentAdmin()
    {
        return $this->admin;
    }
    public function getCurrentAdminName()
    {
        return 'test_admin';
    }
    public function getCurrentAdminId()
    {
        return 1;
    }
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
    use SingletonExTrait;
    use ExceptionReporterTrait;
}
class FakeUserController implements \DuckPhp\GlobalUser\UserControllerInterface
{
}
class FakeAdminController implements \DuckPhp\GlobalAdmin\AdminControllerInterface
{
}

