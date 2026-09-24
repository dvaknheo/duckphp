<?php
namespace tests\DuckPhp\GlobalUser;

use DuckPhp\Component\PhaseProxy;
use DuckPhp\Core\DuckPhpSystemException;
use DuckPhp\Core\PhaseContainer;
use DuckPhp\Core\Route;
use DuckPhp\Core\SingletonExTrait;
use DuckPhp\DuckPhp;
use DuckPhp\GlobalUser\User;
use DuckPhp\GlobalUser\GlobalUser;
use DuckPhp\GlobalUser\UserException;
use DuckPhp\GlobalUser\UserLoginServiceInterface;
use DuckPhp\GlobalUser\UserServiceInterface;
use DuckPhp\GlobalUser\UserSessionInterface;

class GlobalUserTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(GlobalUser::class);
        // 借 DuckPhp 的测试数据目录：里面有 view/block.php，可以当页眉页脚渲染
        $path = \LibCoverage\LibCoverage::G()->getClassTestPath(DuckPhp::class);

        $__SERVER = $_SERVER;
        // urlFor* 最终会走到 Route::getUrlBasePath()，它直读 $_SERVER 的这两个键（没有 ?? 兜底）
        $_SERVER['DOCUMENT_ROOT'] = '/';
        $_SERVER['SCRIPT_FILENAME'] = $__SERVER['SCRIPT_FILENAME'] ?? __FILE__;
        $_SERVER['PATH_INFO'] = '/path-info';

        ////////////////////////////////////////////////////////////////////
        // 1) init()：ext 装载 → 真身被包成 PhaseProxy 登记为单例
        ////////////////////////////////////////////////////////////////////
        $options = [
            'path' => $path,
            'path_view' => 'view',
            'user_provider_enable' => true,
            'ext' => [GlobalUser::class => true],
        ];
        PhaseContainer::RestAllContainerForTesting();
        UserTestApp::_()->init($options);
        $this->assertInstanceOf(PhaseProxy::class, User::_());
        $user = User::_()->self();   // 真身：PhaseProxy 只转发方法调用，改 options 必须碰真身
        $this->assertInstanceOf(GlobalUser::class, $user);

        // user_provider_enable = false 时不登记代理
        PhaseContainer::RestAllContainerForTesting();
        UserTestApp::_()->init([
            'path' => $path,
            'user_provider_enable' => false,
            'ext' => [GlobalUser::class => true],
        ]);
        $this->assertNotInstanceOf(PhaseProxy::class, GlobalUser::_());

        // 回到「有代理」的那份，后面都用它
        PhaseContainer::RestAllContainerForTesting();
        UserTestApp::_()->init($options);
        $user = User::_()->self();

        ////////////////////////////////////////////////////////////////////
        // 2) run_callback_by_key()：没配回调就抛
        ////////////////////////////////////////////////////////////////////
        try {
            $user->localService();
            $this->fail('未配置 globaluser_local_service 时应抛异常');
        } catch (DuckPhpSystemException $ex) {
            $this->assertStringContainsString("need ext options 'globaluser_local_service'", $ex->getMessage());
        }

        ////////////////////////////////////////////////////////////////////
        // 3) id()/name()/data()：只认会话；未登录抛 UserException，已登录给值
        ////////////////////////////////////////////////////////////////////
        // [类名, 方法] 形式的回调：run_callback_by_key 会把类名换成单例（覆盖该分支）
        $user->options['globaluser_login_session'] = [UserTestSession::class, '_'];
        UserTestSession::_()->unsetCurrentUser();
        foreach (['id', 'name', 'data'] as $method) {
            try {
                $user->$method(true);
                $this->fail("$method(true) 未登录时应抛 UserException");
            } catch (\DuckPhp\Core\ExitException $ex) {
            }
        }
        // 未登录 + check_login=false：不抛，各自返回空值
        $this->assertSame(0, $user->id(false));
        $this->assertSame('', $user->name(false));
        $this->assertSame([], $user->data(false));
        // 已登录
        UserTestSession::_()->setCurrentUser(['id' => 7, 'username' => 'user7']);
        $this->assertSame(7, $user->id());
        $this->assertSame('user7', $user->name());
        $this->assertSame(['id' => 7, 'username' => 'user7'], $user->data());

        ////////////////////////////////////////////////////////////////////
        // 4) urlForHome() / urlForRegister() / urlForLogin() / urlForLogout()
        ////////////////////////////////////////////////////////////////////
        $app = UserTestApp::_();
        $app->options['url_user_home'] = 'ctx-home';
        $app->options['url_user_logout'] = 'ctx-logout';
        $user->options['globaluser_url_home'] = 'opt-home';
        $user->options['globaluser_url_logout'] = 'opt-logout';
        $user->options['globaluser_url_register'] = 'opt-register';
        $user->options['globaluser_url_login'] = 'opt-login';
        // 上下文（App）的选项优先于组件自己的选项
        $this->assertStringContainsString('ctx-home', $user->urlForHome());
        $this->assertStringContainsString('ctx-logout', $user->urlForLogout());
        $this->assertStringContainsString('opt-register', $user->urlForRegister());
        $this->assertStringContainsString('opt-login', $user->urlForLogin());
        // 带 $url_back 时拼 ?b=
        $this->assertStringContainsString('?b=' . urlencode('/back/url'), $user->urlForLogin('/back/url'));
        // $ext 作为附加查询参数，$url_back 排在最后
        $this->assertStringContainsString('from=mail&b=' . urlencode('/back/url'), $user->urlForLogin('/back/url', ['from' => 'mail']));
        $this->assertStringContainsString('from=mail', $user->urlForHome(null, ['from' => 'mail']));
        // 上下文没配时回落到组件选项
        unset($app->options['url_user_home'], $app->options['url_user_logout']);
        $this->assertStringContainsString('opt-home', $user->urlForHome());
        $this->assertStringContainsString('opt-logout', $user->urlForLogout());

        ////////////////////////////////////////////////////////////////////
        // 5) mergeViewData()：扩展视图数据回调 + 页眉页脚渲染 + 父类字段
        ////////////////////////////////////////////////////////////////////
        $ext_calls = 0;
        $user->options['globaluser_ext_view_data_callback'] = function (array $data) use (&$ext_calls) {
            $ext_calls++;
            $data['ext_mark'] = true;
            return $data;
        };
        $user->options['globaluser_view_file_header'] = 'block.php';
        $user->options['globaluser_view_file_footer'] = 'block.php';
        $data = $user->mergeViewData(['A' => 'b']);
        $this->assertSame(1, $ext_calls);
        $this->assertTrue($data['ext_mark']);
        $this->assertStringContainsString('Block', (string) $data['__view_data']['header']);
        $this->assertStringContainsString('Block', (string) $data['__view_data']['footer']);
        $this->assertStringContainsString('block.php', (string) $data['__logined_header_file']);
        $this->assertStringContainsString('block.php', (string) $data['__logined_footer_file']);
        // 父类 User::mergeViewData 填的字段
        $this->assertSame(7, $data['__logined_id']);
        $this->assertSame('user7', $data['__logined_name']);
        $this->assertArrayHasKey('__logined_data', $data);
        $this->assertArrayHasKey('__logined_url_home', $data);
        $this->assertArrayHasKey('__logined_url_logout', $data);
        // 页眉页脚与扩展回调都不配 → null，且不再回调
        $user->options['globaluser_view_file_header'] = null;
        $user->options['globaluser_view_file_footer'] = null;
        $user->options['globaluser_ext_view_data_callback'] = null;
        $data = $user->mergeViewData([]);
        $this->assertSame(1, $ext_calls);
        $this->assertNull($data['__view_data']['header']);
        $this->assertNull($data['__view_data']['footer']);

        ////////////////////////////////////////////////////////////////////
        // 6) canAccess() / batchGetUsernames() / service()
        ////////////////////////////////////////////////////////////////////
        $user->options['globaluser_local_service'] = [UserTestService::class, '_'];
        // 未登录 → 直接 false，不碰 service
        UserTestSession::_()->unsetCurrentUser();
        $this->assertFalse($user->canAccess('/a', 'C', 'm'));
        $this->assertNull(UserTestService::_()->last_args);
        // 已登录 + 显式三参 → 不去读当前路由；参数顺序统一 $url 在前
        UserTestSession::_()->setCurrentUser(['id' => 7, 'username' => 'user7']);
        $this->assertTrue($user->canAccess('/a', 'C', 'm'));
        $this->assertSame([7, '/a', 'C', 'm'], UserTestService::_()->last_args);
        // globaluser_enable_callback_singleton=false：类名不再自动换成单例，
        // 用实例方法做回调就会静态调用失败 —— 反证这个选项现在真的被读到了
        $user->options['globaluser_enable_callback_singleton'] = false;
        $user->options['globaluser_local_service'] = [UserTestService::class, 'canAccess'];
        try {
            $user->localService();
            $this->fail('关掉 singleton 转换后，实例方法不该还能被静态调用');
        } catch (\Error $ex) {
            $this->assertStringContainsString('cannot be called statically', $ex->getMessage());
        }
        $user->options['globaluser_enable_callback_singleton'] = true;
        $user->options['globaluser_local_service'] = [UserTestService::class, '_'];
        // 已登录 + 全 null → 从当前路由取（临时切 Phase 再切回）
        Route::_()->calling_class = 'CallingClassX';
        Route::_()->calling_method = 'callingMethodX';
        Route::_()->_PathInfo('/path-info-x');
        $this->assertTrue($user->canAccess());
        $this->assertSame([7, '/path-info-x', 'CallingClassX', 'callingMethodX'], UserTestService::_()->last_args);
        // batchGetUsernames()：转给 service
        $this->assertSame(['u7', 'u8'], $user->batchGetUsernames([7, 8]));
        $this->assertSame([7, 8], UserTestService::_()->last_batch_ids);
        // service()：包成当前 Phase 的 PhaseProxy
        $proxy = $user->service();
        $this->assertInstanceOf(PhaseProxy::class, $proxy);
        $this->assertSame(UserTestService::_(), $proxy->self());

        ////////////////////////////////////////////////////////////////////
        // 7) register() / login() / logout()
        ////////////////////////////////////////////////////////////////////
        $user->options['globaluser_login_service'] = [UserTestLoginService::class, '_'];
        // 默认就是 true（globaluser_is_authed_redirect），先关掉走「不跳转」分支
        $this->assertTrue($user->options['globaluser_is_authed_redirect']);
        $user->options['globaluser_is_authed_redirect'] = false;
        $user->register(['username' => 'u1']);
        $this->assertSame(['username' => 'u1'], UserTestLoginService::_()->last_register_post);
        $this->assertSame(100, UserTestSession::_()->getCurrentUserId());
        $user->login(['username' => 'u2']);
        $this->assertSame(['username' => 'u2'], UserTestLoginService::_()->last_login_post);
        $this->assertSame(100, UserTestSession::_()->getCurrentUserId());
        $user->logout();
        $this->assertSame(100, UserTestLoginService::_()->last_logout_id);
        $this->assertSame(0, UserTestSession::_()->getCurrentUserId());
        // 打开自动跳转：CLI 下 SystemWrapper::_header() 直接 return，Show302 不会真的发头
        $user->options['globaluser_is_authed_redirect'] = true;
        $user->register(['username' => 'u3']);
        $user->login(['username' => 'u4']);
        $user->logout();

        $_SERVER = $__SERVER;
        PhaseContainer::RestAllContainerForTesting();
        \LibCoverage\LibCoverage::End();
    }
}

class UserTestApp extends DuckPhp
{
    public $options = [
        'path' => '',
    ];
}

class UserTestSession implements UserSessionInterface
{
    use SingletonExTrait;

    protected $user = [];

    public function setCurrentUser($user)
    {
        $this->user = $user;
    }
    public function unsetCurrentUser()
    {
        $this->user = [];
    }
    public function getCurrentUser()
    {
        return $this->user;
    }
    public function getCurrentUserName()
    {
        return $this->user['username'] ?? '';
    }
    public function getCurrentUserId()
    {
        return $this->user['id'] ?? 0;
    }
}

class UserTestService implements UserServiceInterface
{
    use SingletonExTrait;

    public $last_args = null;
    public $last_batch_ids = null;

    public function canAccess($user_id, $url, $class, $method): bool
    {
        $this->last_args = [$user_id, $url, $class, $method];
        return true;
    }
    public function log($user_id, string $string, ?string $type = null, array $ext = [])
    {
        return 'logged';
    }
    public function batchGetUsernames(array $ids): array
    {
        $this->last_batch_ids = $ids;
        return array_map(function ($id) {
            return 'u' . $id;
        }, $ids);
    }
}

class UserTestLoginService implements UserLoginServiceInterface
{
    use SingletonExTrait;

    public $last_register_post = null;
    public $last_login_post = null;
    public $last_logout_id = null;

    public function register(array $post)
    {
        $this->last_register_post = $post;
        return ['id' => 100, 'username' => $post['username'] ?? 'u100'];
    }
    public function login(array $post)
    {
        $this->last_login_post = $post;
        return ['id' => 100, 'username' => $post['username'] ?? 'u100'];
    }
    public function logout($id)
    {
        $this->last_logout_id = $id;
    }
}
