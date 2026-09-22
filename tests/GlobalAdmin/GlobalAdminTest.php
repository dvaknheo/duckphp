<?php
namespace tests\DuckPhp\GlobalAdmin;

use DuckPhp\GlobalAdmin\GlobalAdmin;
use DuckPhp\GlobalAdmin\AdminActionInterface;
use DuckPhp\GlobalAdmin\AdminServiceInterface;
use DuckPhp\DuckPhp;
use DuckPhp\Core\App;
use DuckPhp\Foundation\Helper;
use DuckPhp\Foundation\SingletonTrait;

class GlobalAdminTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(GlobalAdmin::class);
        DuckPhp::_()->init([
            'admin_provider_enable'=>true,
            'ext'=>[
                MyAdmin::class=>[
                    'admin_enable'=>true
                ]
            ],
        ]);
        Helper::AdminId();
        try{
        (Helper::AdminId());
        }catch(\Exception $ex){}
        try{
        (Helper::AdminName());
        }catch(\Exception $ex){}
        try{
        Helper::Admin()->data(false);
        }catch(\Exception $ex){}

        Helper::Admin()->urlForHome();
        Helper::Admin()->urlForLogin();
        try{
        Helper::Admin()->urlForLogout();
        }catch(\Exception $ex){}

        Helper::Admin()->service();
        $data = [];
        $path = \LibCoverage\LibCoverage::G()->getClassTestPath(DuckPhp::class);
        // mergeViewData(): 自作者提交 838b42c9 起只做 addExtViewData，不再渲染 header/footer
        unset(MyAdmin::_()->options['admin_view_file_header']);
        $res = Helper::Admin()->mergeViewData([]);
        \PHPUnit\Framework\Assert::assertSame('', $res['__view_data']['header'] ?? '');
        MyAdmin::_()->options['admin_view_file_header']=$path.'view/block';
        MyAdmin::_()->options['admin_view_file_footer']=$path.'view/block';
        $data3 = Helper::Admin()->mergeViewData($data);
        \PHPUnit\Framework\Assert::assertArrayNotHasKey('__view_data', $data3, 'mergeViewData() 不再注入 __view_data');
        // test admin_callback_for_add_ext_view_data
        MyAdmin::_()->options['admin_callback_for_add_ext_view_data'] = [MyAction::class, 'myAddExtViewData'];
        $data4 = Helper::Admin()->mergeViewData($data);
        \PHPUnit\Framework\Assert::assertTrue($data4['__view_data']['custom'] ?? false, 'addExtViewData 回调仍生效');
        Helper::Admin()->canAccess('class','method','url');
        // canAccess() 无参分支：获取路由上下文
        Helper::Admin()->canAccess();
        // canAccess(): id 为空 → return false 分支
        $old_id_cb = MyAdmin::_()->options['admin_callback_for_id'];
        MyAdmin::_()->options['admin_callback_for_id'] = function ($check_login = false) { return null; };
        \PHPUnit\Framework\Assert::assertFalse(Helper::Admin()->canAccess('class', 'method', 'url'));
        MyAdmin::_()->options['admin_callback_for_id'] = $old_id_cb;
        try{
        Helper::Admin()->log('a','b');
        }catch(\Throwable $ex){}

        // show() 分支：渲染视图；header/footer 的渲染结果写进 View::_()->data['__view_data']
        ob_start();
        App::_()->options['use_admin_view_header_footer'] = true;
        Helper::Admin()->_Show([], $path.'view/block');
        ob_get_clean();
        \PHPUnit\Framework\Assert::assertStringContainsString('Block', \DuckPhp\Core\View::_()->data['__view_data']['header'] ?? '');
        \PHPUnit\Framework\Assert::assertStringContainsString('Block', \DuckPhp\Core\View::_()->data['__view_data']['footer'] ?? '');

        $admin = Helper::Admin();
        try{
        $admin->isSuper();
        }catch(\Throwable $ex){}

        ///////////// 新增测试 /////////////

        // Test login() without auto redirect
        MyAdmin::_()->options['admin_loginout_auto_redirect'] = false;
        MyAdmin::_()->options['admin_callback_for_session'] = [MyAdminSession::class, '_'];
        MyAdmin::_()->options['admin_callback_for_login_service'] = [MyService::class, '_'];
        ob_start();
        Helper::Admin()->login(['name' => 'test', 'password' => '123456']);
        $output = ob_get_clean();
        \PHPUnit\Framework\Assert::assertSame('', $output);

        // Test login() WITH auto redirect (uses URL with host to avoid header issues)
        MyAdmin::_()->options['admin_loginout_auto_redirect'] = true;
        MyAdmin::_()->options['admin_url_home'] = 'http://localhost/home';
        ob_start();
        Helper::Admin()->login(['name' => 'test', 'password' => '123456']);
        $output = ob_get_clean();

        // Test logout() without auto redirect
        ob_start();
        Helper::Admin()->logout();
        $output = ob_get_clean();
        \PHPUnit\Framework\Assert::assertSame('', $output);

        // Test logout() WITH auto redirect
        MyAdmin::_()->options['admin_url_login'] = 'http://localhost/login';
        ob_start();
        Helper::Admin()->logout();
        $output = ob_get_clean();

        // Test id() with session callback
        MyAdmin::_()->options['admin_callback_for_session'] = [MyAdminSession::class, '_'];
        MyAdmin::_()->options['admin_callback_for_login_service'] = [MyService::class, '_'];
        MyAdminSession::_()->unsetCurrentAdmin();
        MyAdminSession::_()->setCurrentAdmin(['id' => 1, 'name' => 'session_admin']);
        $id = Helper::Admin()->id(false);
        \PHPUnit\Framework\Assert::assertEquals(1, $id);
        // id() with check_login=true should throw when not logged in
        MyAdminSession::_()->unsetCurrentAdmin();
        try {
            Helper::Admin()->id(true);
            \PHPUnit\Framework\Assert::fail("Should throw AdminException");
        } catch (\DuckPhp\GlobalAdmin\AdminException $ex) {
            \PHPUnit\Framework\Assert::assertTrue(true);
        }

        // Test name() with session callback
        MyAdminSession::_()->setCurrentAdmin(['id' => 1, 'name' => 'session_admin']);
        $name = Helper::Admin()->name(false);
        \PHPUnit\Framework\Assert::assertEquals('session_admin', $name);

        // Test addExtViewData() 默认分支（无回调）：作者 838b42c9 起 __logined_* 改由 _Show() 填，
        // mergeViewData()/addExtViewData() 不再注入
        unset(MyAdmin::_()->options['admin_callback_for_add_ext_view_data']);
        MyAdminSession::_()->setCurrentAdmin(['id' => 99, 'name' => 'extadmin']);
        $extData = Helper::Admin()->mergeViewData(['test' => 'value']);
        \PHPUnit\Framework\Assert::assertEquals('value', $extData['test'] ?? null);
        \PHPUnit\Framework\Assert::assertArrayNotHasKey('__logined_id', $extData, 'mergeViewData() 默认分支不再填 __logined_*');
        // ??= 只在未设置时赋值，先清掉前面 _Show() 留下的值才能重新观察
        \DuckPhp\Core\View::_()->reset();
        ob_start();
        Helper::Admin()->_Show([], $path.'view/block');
        ob_end_clean();
        \PHPUnit\Framework\Assert::assertEquals(99, \DuckPhp\Core\View::_()->data['__logined_id'] ?? null);
        \PHPUnit\Framework\Assert::assertEquals('extadmin', \DuckPhp\Core\View::_()->data['__logined_name'] ?? null);
        \PHPUnit\Framework\Assert::assertArrayHasKey('__logined_url_logout', \DuckPhp\Core\View::_()->data);

        // Test go_url() fallback branch (when callback not set but URL is set)
        $old_url_for_home_cb = MyAdmin::_()->options['admin_callback_for_url_for_home'];
        MyAdmin::_()->options['admin_callback_for_url_for_home'] = null; // clear callback
        MyAdmin::_()->options['admin_url_home'] = 'admin_home'; // but URL is set
        $homeUrl = Helper::Admin()->urlForHome();
        \PHPUnit\Framework\Assert::assertStringContainsString('admin_home', $homeUrl);
        MyAdmin::_()->options['admin_callback_for_url_for_home'] = $old_url_for_home_cb;

        // Test go_url() exception branch (when neither callback nor URL is set)
        MyAdmin::_()->options['admin_callback_for_url_for_home'] = null;
        MyAdmin::_()->options['admin_url_home'] = null;
        try {
            Helper::Admin()->urlForHome();
            \PHPUnit\Framework\Assert::fail("Should throw DuckPhpSystemException");
        } catch (\DuckPhp\Core\DuckPhpSystemException $ex) {
            \PHPUnit\Framework\Assert::assertStringContainsString("need app options", $ex->getMessage());
        }
        MyAdmin::_()->options['admin_url_home'] = 'admin_home'; // restore

        // Test _Show() with __logined_enable_header_footer
        $old_header = MyAdmin::_()->options['admin_view_file_header'];
        $old_footer = MyAdmin::_()->options['admin_view_file_footer'];
        MyAdmin::_()->options['admin_view_file_header'] = $path.'view/block';
        MyAdmin::_()->options['admin_view_file_footer'] = $path.'view/block';
        ob_start();
        Helper::Admin()->_Show(['__logined_enable_header_footer' => true], $path.'view/block');
        ob_get_clean();
        MyAdmin::_()->options['admin_view_file_header'] = $old_header;
        MyAdmin::_()->options['admin_view_file_footer'] = $old_footer;

        // Test exception paths: id() and name() when no provider is set

        // Unset both id and name callbacks, and session callback
        MyAdmin::_()->options['admin_callback_for_id'] = null;
        MyAdmin::_()->options['admin_callback_for_name'] = null;
        MyAdmin::_()->options['admin_callback_for_session'] = null;
        MyAdmin::_()->options['admin_callback_for_login_service'] = null;
        try {
            Helper::Admin()->id(false);
            \PHPUnit\Framework\Assert::fail("Should throw DuckPhpSystemException");
        } catch (\DuckPhp\Core\DuckPhpSystemException $ex) {
            \PHPUnit\Framework\Assert::assertStringContainsString("No GlobalAdmin Provider", $ex->getMessage());
        }
        try {
            Helper::Admin()->name(false);
            \PHPUnit\Framework\Assert::fail("Should throw DuckPhpSystemException");
        } catch (\DuckPhp\Core\DuckPhpSystemException $ex) {
            \PHPUnit\Framework\Assert::assertStringContainsString("No GlobalAdmin Provider", $ex->getMessage());
        }
        MyAdmin::_()->is_inited = false;
        try {
            Helper::Admin()->id(false);
        } catch (\DuckPhp\Core\DuckPhpSystemException $ex) {
            //\PHPUnit\Framework\Assert::assertStringContainsString("Provider", $ex->getMessage());
        }
        try {
            Helper::Admin()->name(false);
        } catch (\DuckPhp\Core\DuckPhpSystemException $ex) {
            //\PHPUnit\Framework\Assert::assertStringContainsString("Provider", $ex->getMessage());
        }
        try {
            Helper::Admin()->data(false);
        } catch (\DuckPhp\Core\DuckPhpSystemException $ex) {
            //\PHPUnit\Framework\Assert::assertStringContainsString("Provider", $ex->getMessage());
        }

        \LibCoverage\LibCoverage::End();
    }
}
class MyAdmin extends GlobalAdmin
{
    public $is_inited = false;
    public $options =[
        'admin_url_home' => 'home',
        'admin_callback_for_id' => [MyAction::class,'id'],
        'admin_callback_for_name' => [MyAction::class,'name'],
        'admin_callback_for_url_for_login' => [MyAction::class,'urlForLogin'],
        'admin_callback_for_url_for_home' => [MyAction::class,'urlForHome'],
        'admin_callback_for_url_for_logout' => [MyAction::class,'urlForLogout'],
        'admin_url_logout' => 'logout',
        'admin_callback_for_local_service'=>[MyService::class,'_'],
        'admin_view_file_header'=>'/abc',
        'admin_loginout_auto_redirect' => false,
    ];
}
class MyAction {
    use SingletonTrait;
    public function id(bool $check_login = true)
    {
        return 1;
    }
    public function name(bool $check_login = true):string
    {
        return "test_admin";
    }
    public function data(bool $check_login = true): array
    {
        return ['id' => 1, 'name' => 'test'];
    }

    public function urlForLogin(?string $url_back = null, ?array $ext = null): string
    {
        return 'abc';
    }
    public function urlForHome(?string $url_back = null, ?array $ext = null): string
    {
        return 'home';
    }
    public function urlForLogout(?string $url_back = null, ?array $ext = null): string
    {
        return 'logout';
    }
    public function myAddExtViewData(array $data): array
    {
        $data['__view_data']['custom'] = true;
        return $data;
    }
}
class MyService {
    use SingletonTrait;
    protected $sessionData = [];
    public function canAccess($admin_id, string $class, string $method, ?string $url = null): bool
    {
        return true;
    }
    public function login(array $post): array
    {
        $id = $post['id'] ?? 1;
        $admin = ['id' => $id, 'name' => $post['name'] ?? 'admin_' . $id];
        $this->sessionData[$id] = $admin;
        return $admin;
    }
    public function logout($admin_id): void
    {
        unset($this->sessionData[$admin_id]);
    }
    public function isSuper($admin_id): bool
    {
        return true;
    }
}
class MyAdminSession implements \DuckPhp\GlobalAdmin\AdminSessionInterface {
    use SingletonTrait;
    protected $currentAdminId = null;
    protected $currentAdminName = null;
    public function getCurrentAdminId()
    {
        return $this->currentAdminId;
    }
    public function getCurrentAdminName(): string
    {
        return $this->currentAdminName ?? '';
    }
    public function setCurrentAdmin($admin)
    {
        $this->currentAdminId = $admin['id'] ?? null;
        $this->currentAdminName = $admin['name'] ?? '';
    }
    public function unsetCurrentAdmin()
    {
        $this->currentAdminId = null;
        $this->currentAdminName = null;
    }
    public function getCurrentAdmin()
    {
        return ['id' => $this->currentAdminId, 'name' => $this->currentAdminName];
    }
}
