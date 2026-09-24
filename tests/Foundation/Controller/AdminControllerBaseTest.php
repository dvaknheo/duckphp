<?php
namespace tests\DuckPhp\Foundation\Controller;

use DuckPhp\Core\ExitException;
use DuckPhp\Core\PhaseContainer;
use DuckPhp\Core\SystemWrapper;
use DuckPhp\Core\View;
use DuckPhp\DuckPhp;
use DuckPhp\Foundation\Controller\AdminControllerBase;
use DuckPhp\Foundation\SingletonTrait;
use DuckPhp\GlobalAdmin\AdminException;
use DuckPhp\GlobalAdmin\AdminServiceInterface;
use DuckPhp\GlobalAdmin\AdminSessionInterface;
use DuckPhp\GlobalAdmin\GlobalAdmin;

class AdminControllerBaseTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(AdminControllerBase::class);

        $__SERVER = $_SERVER;
        // urlForLogin() 最终会走到 Route::getUrlBasePath()，它直读 $_SERVER 的这两个键（没有 ?? 兜底）
        $_SERVER['DOCUMENT_ROOT'] = '/';
        $_SERVER['SCRIPT_FILENAME'] = $__SERVER['SCRIPT_FILENAME'] ?? __FILE__;
        $_SERVER['PATH_INFO'] = '/path-info';

        ////////////////////////////////////////////////////////////////////
        // 1) 放行：已登录 + canAccess() 为真 → 两笔 assignViewData
        ////////////////////////////////////////////////////////////////////
        PhaseContainer::RestAllContainerForTesting();
        DuckPhp::_(new DuckPhp())->init([
            'installed' => true,
            'ext' => [FakeAdminProvider::class => true],
        ]);
        SystemWrapper::system_wrapper_replace(['exit' => function () {}]);
        $obj = new AdminControllerBase();
        $this->assertInstanceOf(AdminControllerBase::class, $obj);
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
            'ext' => [FakeDenyProvider::class => true],
        ]);
        SystemWrapper::system_wrapper_replace([
            'exit' => function () {},
            'header' => function ($output, $replace = true, $code = 0) use (&$headers) {
                $headers[] = [$output, $code];
            },
        ]);
        $obj = new AdminControllerBase();
        $this->assertInstanceOf(AdminControllerBase::class, $obj);
        $this->assertStringContainsString('login-url', (string) $headers[0][0]);
        $this->assertSame(302, $headers[0][1]);

        ////////////////////////////////////////////////////////////////////
        // 3) 无权 + Ajax → ShowJson（不改 $_SERVER 的 exit，走 onLoginedException 的 JSON 分支）
        ////////////////////////////////////////////////////////////////////
        PhaseContainer::RestAllContainerForTesting();
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        DuckPhp::_(new DuckPhp())->init([
            'installed' => true,
            'ext' => [FakeDenyProvider::class => true],
        ]);
        SystemWrapper::system_wrapper_replace(['exit' => function () {}]);
        ob_start();
        $obj = new AdminControllerBase();
        $json = ob_get_clean();
        unset($_SERVER['HTTP_X_REQUESTED_WITH']);
        $this->assertInstanceOf(AdminControllerBase::class, $obj);
        $this->assertStringContainsString('error_code', $json);

        ////////////////////////////////////////////////////////////////////
        // 4) 未安装 → checkInstall(null) 里 302 + exit 中断（退出走 __EXIT_EXCEPTION）
        ////////////////////////////////////////////////////////////////////
        PhaseContainer::RestAllContainerForTesting();
        DuckPhp::_(new DuckPhp())->init(['installed' => false]);
        $caught = null;
        try {
            new AdminControllerBase();
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
 * 一个「放行」的后台 provider：会话里有管理员，service 的 canAccess 返回 true。
 */
class FakeAdminProvider extends GlobalAdmin
{
    public $options = [
        'globaladmin_login_session' => [FakeAdminSession::class, '_'],
        'globaladmin_local_service' => [FakeAdminService::class, '_'],
        'globaladmin_url_login' => 'login-url',
    ];
}

/**
 * 一个「拒绝」的后台 provider：能登录，但 service 的 canAccess 返回 false。
 */
class FakeDenyProvider extends GlobalAdmin
{
    public $options = [
        'globaladmin_login_session' => [FakeAdminSession::class, '_'],
        'globaladmin_local_service' => [FakeDenyService::class, '_'],
        'globaladmin_url_login' => 'login-url',
    ];
}

class FakeAdminSession implements AdminSessionInterface
{
    use SingletonTrait;

    public function setCurrentAdmin($admin)
    {
    }
    public function unsetCurrentAdmin()
    {
    }
    public function getCurrentAdmin()
    {
        return ['id' => 1, 'name' => 'admin'];
    }
    public function getCurrentAdminName()
    {
        return 'admin';
    }
    public function getCurrentAdminId()
    {
        return 1;
    }
}

class FakeAdminService implements AdminServiceInterface
{
    use SingletonTrait;

    public function canAccess($admin_id, $url, $class, $method): bool
    {
        return true;
    }
    public function log($admin_id, string $string, ?string $type = null, array $ext = [])
    {
        return null;
    }
    public function isSuper($admin_id): bool
    {
        return false;
    }
}

class FakeDenyService implements AdminServiceInterface
{
    use SingletonTrait;

    public function canAccess($admin_id, $url, $class, $method): bool
    {
        return false;
    }
    public function log($admin_id, string $string, ?string $type = null, array $ext = [])
    {
        return null;
    }
    public function isSuper($admin_id): bool
    {
        return false;
    }
}
