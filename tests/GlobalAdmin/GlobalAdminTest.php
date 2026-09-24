<?php
namespace tests\DuckPhp\GlobalAdmin;

use DuckPhp\Component\PhaseProxy;
use DuckPhp\Core\PhaseContainer;
use DuckPhp\Core\Route;
use DuckPhp\Core\SingletonExTrait;
use DuckPhp\DuckPhp;
use DuckPhp\GlobalAdmin\AdminException;
use DuckPhp\GlobalAdmin\AdminLoginServiceInterface;
use DuckPhp\GlobalAdmin\AdminServiceInterface;
use DuckPhp\GlobalAdmin\AdminSessionInterface;
use DuckPhp\GlobalAdmin\GlobalAdmin;

class GlobalAdminTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(GlobalAdmin::class);
        // 借 DuckPhp 的测试数据目录：里面有 view/block.php，可以当页眉页脚渲染
        $path = \LibCoverage\LibCoverage::G()->getClassTestPath(DuckPhp::class);

        $__SERVER = $_SERVER;
        // urlForHome/urlForLogin/urlForLogout 最终会走到 Route::getUrlBasePath()，
        // 它直接读 $_SERVER 的 DOCUMENT_ROOT / SCRIPT_FILENAME（没有 ?? 兜底），CLI 下要自己补齐
        $_SERVER['DOCUMENT_ROOT'] = '/';
        $_SERVER['SCRIPT_FILENAME'] = $__SERVER['SCRIPT_FILENAME'] ?? __FILE__;
        $_SERVER['PATH_INFO'] = '/path-info';

        ////////////////////////////////////////////////////////////////////
        // 1) init()：ext 装载 → 真身被包成 PhaseProxy 登记为单例
        ////////////////////////////////////////////////////////////////////
        $options = [
            'path' => $path,
            'path_view' => 'view',
            'admin_provider_enable' => true,
            'ext' => [GlobalAdmin::class => true],
        ];
        PhaseContainer::RestAllContainerForTesting();
        FakeAdminApp::_()->init($options);
        $this->assertInstanceOf(PhaseProxy::class, GlobalAdmin::_());
        $admin = GlobalAdmin::_()->self();   // 真身：PhaseProxy 只转发方法调用，改 options 必须碰真身
        $this->assertInstanceOf(GlobalAdmin::class, $admin);

        // admin_provider_enable = false 时不登记代理
        PhaseContainer::RestAllContainerForTesting();
        FakeAdminApp::_()->init([
            'path' => $path,
            'admin_provider_enable' => false,
            'ext' => [GlobalAdmin::class => true],
        ]);
        $this->assertNotInstanceOf(PhaseProxy::class, GlobalAdmin::_());

        // 回到「有代理」的那份，后面都用它
        PhaseContainer::RestAllContainerForTesting();
        FakeAdminApp::_()->init($options);
        $admin = GlobalAdmin::_()->self();

        ////////////////////////////////////////////////////////////////////
        // 2) run_callback_by_key()：没配回调就抛
        ////////////////////////////////////////////////////////////////////
        try {
            $admin->localService();
            $this->fail('未配置 globaladmin_local_service 时应抛异常');
        } catch (\DuckPhp\Core\DuckPhpSystemException $ex) {
            $this->assertStringContainsString("need ext options 'globaladmin_local_service'", $ex->getMessage());
        }

        ////////////////////////////////////////////////////////////////////
        // 3) id()/name()/data()：未登录抛 AdminException，已登录给值
        ////////////////////////////////////////////////////////////////////
        // [类名, 方法] 形式的回调：run_callback_by_key 会把类名换成单例（覆盖该分支）
        $admin->options['globaladmin_login_session'] = [FakeAdminSession::class, '_'];
        FakeAdminSession::_()->unsetCurrentAdmin();
        foreach (['id', 'name', 'data'] as $method) {
            try {
                $admin->$method(true);
                $this->fail("$method(true) 未登录时应抛 AdminException");
            } catch (AdminException $ex) {
                $this->assertSame(AdminException::CODE_NEED_LOGIN, $ex->getCode());
                $this->assertSame(AdminException::MESSAGE_NEED_LOGIN, $ex->getMessage());
            }
        }
        // 未登录 + check_login=false：不抛，各自返回空值
        $this->assertSame(0, $admin->id(false));
        $this->assertSame('', $admin->name(false));
        $this->assertSame([], $admin->data(false));
        // 已登录
        FakeAdminSession::_()->setCurrentAdmin(['id' => 7, 'name' => 'admin7']);
        $this->assertSame(7, $admin->id());
        $this->assertSame('admin7', $admin->name());
        $this->assertSame(['id' => 7, 'name' => 'admin7'], $admin->data());

        ////////////////////////////////////////////////////////////////////
        // 4) urlForHome() / urlForLogin() / urlForLogout()
        ////////////////////////////////////////////////////////////////////
        $app = FakeAdminApp::_();
        $app->options['url_admin_home'] = 'ctx-home';
        $app->options['url_admin_logout'] = 'ctx-logout';
        $admin->options['globaladmin_url_home'] = 'opt-home';
        $admin->options['globaladmin_url_logout'] = 'opt-logout';
        $admin->options['globaladmin_url_login'] = 'opt-login';
        // 上下文（App）的选项优先于组件自己的选项
        $this->assertStringContainsString('ctx-home', $admin->urlForHome());
        $this->assertStringContainsString('ctx-logout', $admin->urlForLogout());
        $this->assertStringContainsString('opt-login', $admin->urlForLogin());
        // 带 $url_back 时拼 ?b=
        $this->assertStringContainsString('?b=' . urlencode('/back/url'), $admin->urlForLogin('/back/url'));
        // 上下文没配时回落到组件选项
        unset($app->options['url_admin_home'], $app->options['url_admin_logout']);
        $this->assertStringContainsString('opt-home', $admin->urlForHome());
        $this->assertStringContainsString('opt-logout', $admin->urlForLogout());

        ////////////////////////////////////////////////////////////////////
        // 5) mergeViewData()：扩展视图数据回调 + 页眉页脚渲染 + 父类字段
        ////////////////////////////////////////////////////////////////////
        $ext_calls = 0;
        $admin->options['globaladmin_ext_view_data_callback'] = function (array $data) use (&$ext_calls) {
            $ext_calls++;
            $data['ext_mark'] = true;
            return $data;
        };
        $admin->options['globaladmin_view_file_header'] = 'block.php';
        $admin->options['globaladmin_view_file_footer'] = 'block.php';
        $data = $admin->mergeViewData(['A' => 'b']);
        $this->assertSame(1, $ext_calls);
        $this->assertTrue($data['ext_mark']);
        $this->assertStringContainsString('Block', (string) $data['__view_data']['header']);
        $this->assertStringContainsString('Block', (string) $data['__view_data']['footer']);
        $this->assertStringContainsString('block.php', (string) $data['__logined_header_file']);
        $this->assertStringContainsString('block.php', (string) $data['__logined_footer_file']);
        // 父类 Admin::mergeViewData 填的字段
        $this->assertSame(7, $data['__logined_id']);
        $this->assertSame('admin7', $data['__logined_name']);
        $this->assertArrayHasKey('__logined_url_home', $data);
        // 页眉页脚与扩展回调都不配 → null，且不再回调
        $admin->options['globaladmin_view_file_header'] = null;
        $admin->options['globaladmin_view_file_footer'] = null;
        $admin->options['globaladmin_ext_view_data_callback'] = null;
        $data = $admin->mergeViewData([]);
        $this->assertSame(1, $ext_calls);
        $this->assertNull($data['__view_data']['header']);
        $this->assertNull($data['__view_data']['footer']);

        ////////////////////////////////////////////////////////////////////
        // 6) canAccess()
        ////////////////////////////////////////////////////////////////////
        $admin->options['globaladmin_local_service'] = [FakeAdminService::class, '_'];
        // 未登录 → 直接 false，不碰 service
        FakeAdminSession::_()->unsetCurrentAdmin();
        $this->assertFalse($admin->canAccess('/a', 'C', 'm'));
        $this->assertNull(FakeAdminService::_()->last_args);
        // 已登录 + 显式三参 → 不去读当前路由
        FakeAdminSession::_()->setCurrentAdmin(['id' => 7, 'name' => 'admin7']);
        $this->assertTrue($admin->canAccess('/a', 'C', 'm'));
        // 参数顺序统一为 $url 在前（与 AdminActionInterface / AdminServiceInterface 一致）
        $this->assertSame([7, '/a', 'C', 'm'], FakeAdminService::_()->last_args);
        // globaladmin_enable_callback_singleton=false：类名不再自动换成单例，
        // 用实例方法做回调就会静态调用失败 —— 反证这个选项现在真的被读到了
        $admin->options['globaladmin_enable_callback_singleton'] = false;
        $admin->options['globaladmin_local_service'] = [FakeAdminService::class, 'canAccess'];
        try {
            $admin->localService();
            $this->fail('关掉 singleton 转换后，实例方法不该还能被静态调用');
        } catch (\Error $ex) {
            $this->assertStringContainsString('cannot be called statically', $ex->getMessage());
        }
        $admin->options['globaladmin_enable_callback_singleton'] = true;
        $admin->options['globaladmin_local_service'] = [FakeAdminService::class, '_'];
        // 已登录 + 全 null → 从当前路由取（临时切 Phase 再切回）
        Route::_()->calling_class = 'CallingClassX';
        Route::_()->calling_method = 'callingMethodX';
        Route::_()->_PathInfo('/path-info-x');
        $this->assertTrue($admin->canAccess());
        $this->assertSame([7, '/path-info-x', 'CallingClassX', 'callingMethodX'], FakeAdminService::_()->last_args);

        ////////////////////////////////////////////////////////////////////
        // 7) login() / logout()
        ////////////////////////////////////////////////////////////////////
        $admin->options['globaladmin_login_service'] = [FakeAdminLoginService::class, '_'];
        // globaladmin_is_authed_redirect 默认 true；先关掉走「不跳转」分支，再打开走 Show302 分支
        $this->assertTrue($admin->options['globaladmin_is_authed_redirect']);
        $admin->options['globaladmin_is_authed_redirect'] = false;
        $admin->login(['username' => 'u1']);
        $this->assertSame(['username' => 'u1'], FakeAdminLoginService::_()->last_login_post);
        $this->assertSame(100, FakeAdminSession::_()->getCurrentAdminId());
        $admin->logout();
        $this->assertSame(100, FakeAdminLoginService::_()->last_logout_id);
        $this->assertSame(0, FakeAdminSession::_()->getCurrentAdminId());
        // 打开自动跳转：CLI 下 SystemWrapper::_header() 直接 return，Show302 不会真的发头
        $admin->options['globaladmin_is_authed_redirect'] = true;
        $admin->login(['username' => 'u2']);
        $this->assertSame(['username' => 'u2'], FakeAdminLoginService::_()->last_login_post);
        $admin->logout();

        $_SERVER = $__SERVER;
        PhaseContainer::RestAllContainerForTesting();
        \LibCoverage\LibCoverage::End();
    }
}

class FakeAdminSession implements AdminSessionInterface
{
    use SingletonExTrait;

    protected $admin = [];

    public function setCurrentAdmin($admin)
    {
        $this->admin = $admin;
    }
    public function unsetCurrentAdmin()
    {
        $this->admin = [];
    }
    public function getCurrentAdmin()
    {
        return $this->admin;
    }
    public function getCurrentAdminName()
    {
        return $this->admin['name'] ?? '';
    }
    public function getCurrentAdminId()
    {
        return $this->admin['id'] ?? 0;
    }
}

class FakeAdminLoginService implements AdminLoginServiceInterface
{
    use SingletonExTrait;

    public $last_login_post = null;
    public $last_logout_id = null;

    public function login(array $post)
    {
        $this->last_login_post = $post;
        return ['id' => 100, 'name' => 'admin100'];
    }
    public function logout($id)
    {
        $this->last_logout_id = $id;
    }
}

class FakeAdminService implements AdminServiceInterface
{
    use SingletonExTrait;

    public $last_args = null;

    /**
     * 参数顺序与 AdminServiceInterface::canAccess() 一致：$url 在 class/method 之前。
     */
    public function canAccess($admin_id, $url, $class, $method): bool
    {
        $this->last_args = [$admin_id, $url, $class, $method];
        return true;
    }
    public function log($admin_id, string $string, ?string $type = null, array $ext = [])
    {
        return 'logged';
    }
    public function isSuper($admin_id): bool
    {
        return true;
    }
}

class FakeAdminApp extends DuckPhp
{
    public $options = [
        'path' => '',
    ];
}
