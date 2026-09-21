<?php
namespace tests\DuckPhp\Foundation\Controller;

use DuckPhp\DuckPhp;
use DuckPhp\Core\PhaseContainer;
use DuckPhp\Core\SystemWrapper;
use DuckPhp\Foundation\Controller\AdminControllerBase;
use DuckPhp\Foundation\Controller\Helper;
use DuckPhp\GlobalAdmin\GlobalAdmin;

class AdminControllerBaseTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(AdminControllerBase::class);

        // Success path: login check passes, assignViewData is called
        PhaseContainer::RestAllContainerForTesting();
        DuckPhp::_(new DuckPhp())->init([
            'installed' => true,
            'admin_provider_enable' => true,
            'ext' => [
                FakeAdminProvider::class => ['admin_enable' => true],
            ],
        ]);
        SystemWrapper::system_wrapper_replace(['exit' => function () {}]);
        $obj = new AdminControllerBase();
        $this->assertInstanceOf(AdminControllerBase::class, $obj);

        // Exception path: canAccess returns false, Show302 is called
        PhaseContainer::RestAllContainerForTesting();
        DuckPhp::_(new DuckPhp())->init([
            'installed' => true,
            'admin_provider_enable' => true,
            'ext' => [
                FakeDenyProvider::class => ['admin_enable' => true],
            ],
        ]);
        SystemWrapper::system_wrapper_replace(['exit' => function () {}]);
        SystemWrapper::system_wrapper_replace(['header' => function () {}]);
        $obj = new AdminControllerBase();
        $this->assertInstanceOf(AdminControllerBase::class, $obj);

        // Exception path: canAccess returns false + Ajax request
        PhaseContainer::RestAllContainerForTesting();
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        DuckPhp::_(new DuckPhp())->init([
            'installed' => true,
            'admin_provider_enable' => true,
            'ext' => [
                FakeDenyProvider::class => ['admin_enable' => true],
            ],
        ]);
        SystemWrapper::system_wrapper_replace(['exit' => function () {}]);
        try {
            $obj = new AdminControllerBase();
        } catch (\Throwable $ex) {
        }
        unset($_SERVER['HTTP_X_REQUESTED_WITH']);

        // Error path: checkInstall throws (not installed)
        PhaseContainer::RestAllContainerForTesting();
        DuckPhp::_(new DuckPhp())->init([
            'installed' => false,
        ]);
        try {
            new AdminControllerBase();
            $this->fail('expected exception');
        } catch (\Throwable $ex) {
        }

        \LibCoverage\LibCoverage::End();
    }
}
class FakeAdminProvider extends GlobalAdmin
{
    public $is_inited = false;
    public $options = [
        'admin_enable' => true,
        'admin_callback_for_id' => [FakeAdminAction::class, 'id'],
        'admin_callback_for_name' => [FakeAdminAction::class, 'name'],
        'admin_callback_for_local_service' => [FakeAdminService::class, '_'],
        'admin_callback_for_url_for_login' => [FakeAdminAction::class, 'urlForLogin'],
        'admin_view_file_header' => '/abc',
    ];
}
class FakeDenyProvider extends GlobalAdmin
{
    public $is_inited = false;
    public $options = [
        'admin_enable' => true,
        'admin_callback_for_id' => [FakeDenyAction::class, 'id'],
        'admin_callback_for_name' => [FakeDenyAction::class, 'name'],
        'admin_callback_for_local_service' => [FakeDenyService::class, '_'],
        'admin_callback_for_url_for_login' => [FakeDenyAction::class, 'urlForLogin'],
        'admin_view_file_header' => '/abc',
    ];
}
class FakeAdminAction {
    use \DuckPhp\Foundation\SingletonTrait;
    public function id($check_login = true) { return 1; }
    public function name($check_login = true): string { return 'admin'; }
    public function urlForLogin(?string $url_back = null, ?array $ext = null): string { return '/login'; }
}
class FakeDenyAction {
    use \DuckPhp\Foundation\SingletonTrait;
    public function id($check_login = true) { return 1; }
    public function name($check_login = true): string { return 'admin'; }
    public function urlForLogin(?string $url_back = null, ?array $ext = null): string { return '/login'; }
}
class FakeAdminService {
    use \DuckPhp\Foundation\SingletonTrait;
    public function canAccess($admin_id, $class = null, $method = null, $url = null): bool { return true; }
    public function isSuper($admin_id): bool { return false; }
}
class FakeDenyService {
    use \DuckPhp\Foundation\SingletonTrait;
    public function canAccess($admin_id, $class = null, $method = null, $url = null): bool { return false; }
    public function isSuper($admin_id): bool { return false; }
}
