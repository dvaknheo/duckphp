<?php
namespace tests\DuckPhp\Foundation\Controller;

use DuckPhp\Core\ExitException;
use DuckPhp\Core\PhaseContainer;
use DuckPhp\Core\SystemWrapper;
use DuckPhp\Core\View;
use DuckPhp\DuckPhp;
use DuckPhp\Foundation\Controller\UserControllerBase;
use DuckPhp\Foundation\SingletonTrait;
use DuckPhp\GlobalUser\GlobalUser;
use DuckPhp\GlobalUser\UserException;
use DuckPhp\GlobalUser\UserServiceInterface;
use DuckPhp\GlobalUser\UserSessionInterface;

class UserControllerBaseTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(UserControllerBase::class);

        $__SERVER = $_SERVER;
        // urlForLogin() 最终会走到 Route::getUrlBasePath()，它直读 $_SERVER 的这几个键（没有 ?? 兜底）
        $_SERVER['DOCUMENT_ROOT'] = '/';
        $_SERVER['SCRIPT_FILENAME'] = $__SERVER['SCRIPT_FILENAME'] ?? __FILE__;
        $_SERVER['PATH_INFO'] = '/path-info';

        ////////////////////////////////////////////////////////////////////
        // 1) 放行：已登录 + canAccess() 为真 → 两笔 assignViewData
        ////////////////////////////////////////////////////////////////////
        PhaseContainer::RestAllContainerForTesting();
        DuckPhp::_(new DuckPhp())->init([
            'installed' => true,
            'ext' => [FakeUserProvider::class => true],
        ]);
        SystemWrapper::system_wrapper_replace(['exit' => function () {}]);
        $obj = new UserControllerBase();
        $this->assertInstanceOf(UserControllerBase::class, $obj);
        $view_data = View::_()->getViewData();
        $this->assertTrue($view_data['__use_logined_view_data']);
        $this->assertTrue($view_data['__use_logined_header_footer_file']);

        ////////////////////////////////////////////////////////////////////
        // 2) 无权 + 非 Ajax → Show302 到登录页，然后 exit
        ////////////////////////////////////////////////////////////////////
        PhaseContainer::RestAllContainerForTesting();
        $headers = [];
        DuckPhp::_(new DuckPhp())->init([
            'installed' => true,
            'ext' => [FakeUserDenyProvider::class => true],
        ]);
        SystemWrapper::system_wrapper_replace([
            'exit' => function () {},
            'header' => function ($output, $replace = true, $code = 0) use (&$headers) {
                $headers[] = [$output, $code];
            },
        ]);
        $obj = new UserControllerBase();
        $this->assertInstanceOf(UserControllerBase::class, $obj);
        $this->assertStringContainsString('login-url', (string) $headers[0][0]);
        $this->assertSame(302, $headers[0][1]);

        ////////////////////////////////////////////////////////////////////
        // 3) 无权 + Ajax → ShowJson（走 onLoginedException 的 JSON 分支）
        ////////////////////////////////////////////////////////////////////
        PhaseContainer::RestAllContainerForTesting();
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        DuckPhp::_(new DuckPhp())->init([
            'installed' => true,
            'ext' => [FakeUserDenyProvider::class => true],
        ]);
        SystemWrapper::system_wrapper_replace(['exit' => function () {}]);
        ob_start();
        $obj = new UserControllerBase();
        $json = ob_get_clean();
        unset($_SERVER['HTTP_X_REQUESTED_WITH']);
        $this->assertInstanceOf(UserControllerBase::class, $obj);
        $this->assertStringContainsString('error_code', $json);
        $this->assertStringContainsString((string) UserException::CODE_NEED_PERMISSION, $json);
        $this->assertStringContainsString(UserException::MESSAGE_NEED_PEMISSION, $json);

        ////////////////////////////////////////////////////////////////////
        // 4) 未安装 → checkInstall(null) 里 302 + exit 中断（退出走 __EXIT_EXCEPTION）
        ////////////////////////////////////////////////////////////////////
        PhaseContainer::RestAllContainerForTesting();
        DuckPhp::_(new DuckPhp())->init(['installed' => false]);
        $caught = null;
        try {
            new UserControllerBase();
        } catch (\Throwable $ex) {
            $caught = $ex;
        }
        $this->assertInstanceOf(ExitException::class, $caught, '未安装时应在 checkInstall 阶段就中断');

        $_SERVER = $__SERVER;
        PhaseContainer::RestAllContainerForTesting();
        \LibCoverage\LibCoverage::End();
    }
}

/**
 * 一个「放行」的前台 provider：会话里有用户，service 的 canAccess 返回 true。
 */
class FakeUserProvider extends GlobalUser
{
    public $options = [
        'globaluser_login_session' => [FakeUserSession::class, '_'],
        'globaluser_local_service' => [FakeUserService::class, '_'],
        'globaluser_url_login' => 'login-url',
    ];
}

/**
 * 一个「拒绝」的前台 provider：能登录，但 service 的 canAccess 返回 false。
 */
class FakeUserDenyProvider extends GlobalUser
{
    public $options = [
        'globaluser_login_session' => [FakeUserSession::class, '_'],
        'globaluser_local_service' => [FakeUserDenyService::class, '_'],
        'globaluser_url_login' => 'login-url',
    ];
}

class FakeUserSession implements UserSessionInterface
{
    use SingletonTrait;

    public function setCurrentUser($user)
    {
    }
    public function unsetCurrentUser()
    {
    }
    public function getCurrentUser()
    {
        return ['id' => 1, 'name' => 'user'];
    }
    public function getCurrentUserName()
    {
        return 'user';
    }
    public function getCurrentUserId()
    {
        return 1;
    }
}

class FakeUserService implements UserServiceInterface
{
    use SingletonTrait;

    public function canAccess($user_id, $url, $class, $method): bool
    {
        return true;
    }
    public function log($user_id, string $string, ?string $type = null, array $ext = [])
    {
        return null;
    }
    public function batchGetUsernames(array $ids): array
    {
        return [];
    }
}

class FakeUserDenyService implements UserServiceInterface
{
    use SingletonTrait;

    public function canAccess($user_id, $url, $class, $method): bool
    {
        return false;
    }
    public function log($user_id, string $string, ?string $type = null, array $ext = [])
    {
        return null;
    }
    public function batchGetUsernames(array $ids): array
    {
        return [];
    }
}
