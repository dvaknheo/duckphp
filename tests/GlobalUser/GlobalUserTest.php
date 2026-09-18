<?php
namespace tests\DuckPhp\GlobalUser;

use DuckPhp\GlobalUser\GlobalUser;
use DuckPhp\GlobalUser\UserActionInterface;
use DuckPhp\GlobalUser\UserServiceInterface;
use DuckPhp\GlobalUser\UserException;
use DuckPhp\DuckPhp;
use DuckPhp\Core\App;
use DuckPhp\Foundation\Helper;
use DuckPhp\Foundation\SingletonTrait;

class GlobalUserTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(GlobalUser::class);
        DuckPhp::_()->init(['user_provider'=>MyUser::class]);
        Helper::UserId();
        try{
        (Helper::UserId());
        }catch(\Exception $ex){}
        try{
        (Helper::UserName());
        }catch(\Exception $ex){}
        try{
        Helper::User()->data(false);
        }catch(\Exception $ex){}

        Helper::User()->urlForHome();
        Helper::User()->urlForLogin();
        try{
        Helper::User()->urlForLogout();
        }catch(\Exception $ex){}
        try{
        Helper::User()->urlForRegister();
        }catch(\Exception $ex){}
        try{
        }catch(\Exception $ex){}

        Helper::User()->service();
        $data = [];
        $path = \LibCoverage\LibCoverage::G()->getClassTestPath(DuckPhp::class);
        // mergeViewData: without header
        unset(MyUser::_()->options['user_view_file_header']);
        $res = Helper::User()->mergeViewData([]);
        \PHPUnit\Framework\Assert::assertSame('', $res['__view_data']['header'] ?? '');
        // mergeViewData: with header
        MyUser::_()->options['user_view_file_header']=$path.'view/block';
        Helper::User()->mergeViewData($data);
        // mergeViewData: with header + footer
        MyUser::_()->options['user_view_file_footer']=$path.'view/block';
        $data3 = Helper::User()->mergeViewData($data);
        \PHPUnit\Framework\Assert::assertStringContainsString('Block', $data3['__view_data']['footer'] ?? '');
        // test user_callback_for_add_ext_view_data
        MyUser::_()->options['user_callback_for_add_ext_view_data'] = [MyUserAction::class, 'myAddExtViewData'];
        Helper::User()->canAccess('class','method','url');
        // canAccess() 无参分支：获取路由上下文
        Helper::User()->canAccess();
        // canAccess(): id 为空 → return false 分支
        $old_id_cb = MyUser::_()->options['user_callback_for_id'];
        MyUser::_()->options['user_callback_for_id'] = function ($check_login = false) { return null; };
        \PHPUnit\Framework\Assert::assertFalse(Helper::User()->canAccess('class', 'method', 'url'));
        MyUser::_()->options['user_callback_for_id'] = $old_id_cb;
        try{
        Helper::User()->log('a','b');
        }catch(\Throwable $ex){}

        // show() 分支：渲染视图
        ob_start();
        App::_()->options['use_user_view_header_footer'] = true;
        Helper::User()->_Show([], $path.'view/block');
        ob_get_clean();


        $User = Helper::User()->batchGetUsernames([]);

        ///////////// 新增测试 /////////////

        // Test register() without auto redirect
        MyUser::_()->options['user_loginout_auto_redirect'] = false;
        MyUser::_()->options['user_callback_for_session'] = [MyUserSession::class, '_'];
        MyUser::_()->options['user_callback_for_login_service'] = [MyUserService::class, '_'];
        ob_start();
        Helper::User()->register(['username' => 'test', 'password' => '123456']);
        $output = ob_get_clean();
        \PHPUnit\Framework\Assert::assertSame('', $output);

        // Test register() WITH auto redirect (uses URL with host to avoid header issues)
        MyUser::_()->options['user_loginout_auto_redirect'] = true;
        MyUser::_()->options['user_url_home'] = 'http://localhost/home';
        ob_start();
        Helper::User()->register(['username' => 'test2', 'password' => '123456']);
        $output = ob_get_clean();

        // Test login() without auto redirect
        ob_start();
        Helper::User()->login(['username' => 'test', 'password' => '123456']);
        $output = ob_get_clean();
        \PHPUnit\Framework\Assert::assertSame('', $output);

        // Test login() WITH auto redirect
        MyUser::_()->options['user_url_home'] = 'http://localhost/home';
        ob_start();
        Helper::User()->login(['username' => 'test', 'password' => '123456']);
        $output = ob_get_clean();

        // Test logout() without auto redirect
        ob_start();
        Helper::User()->logout();
        $output = ob_get_clean();
        \PHPUnit\Framework\Assert::assertSame('', $output);

        // Test logout() WITH auto redirect
        MyUser::_()->options['user_url_login'] = 'http://localhost/login';
        ob_start();
        Helper::User()->logout();
        $output = ob_get_clean();

        // Test id() with session callback
        MyUser::_()->options['user_callback_for_session'] = [MyUserSession::class, '_'];
        MyUser::_()->options['user_callback_for_login_service'] = [MyUserService::class, '_'];
        MyUserService::_()->resetSession();
        MyUserSession::_()->setCurrentUser(['id' => 1, 'username' => 'session_user']);
        $id = Helper::User()->id(false);
        \PHPUnit\Framework\Assert::assertEquals(1, $id);
        // id() with check_login=true should throw when not logged in
        MyUserSession::_()->unsetCurrentUser();
        try {
            Helper::User()->id(true);
            \PHPUnit\Framework\Assert::fail("Should throw UserException");
        } catch (UserException $ex) {
            \PHPUnit\Framework\Assert::assertTrue(true);
        }

        // user_default_exception_class must be honored: id()/name() have to read the
        // "user_" key (they used to read admin_default_exception_class by mistake)
        MyUser::_()->options['user_default_exception_class'] = MyUserDefaultException::class;
        MyUserSession::_()->unsetCurrentUser();
        try {
            Helper::User()->id(true);
            \PHPUnit\Framework\Assert::fail("id() should throw MyUserDefaultException");
        } catch (\Exception $ex) {
            \PHPUnit\Framework\Assert::assertEquals(MyUserDefaultException::class, get_class($ex));
        }
        try {
            Helper::User()->name(true);
            \PHPUnit\Framework\Assert::fail("name() should throw MyUserDefaultException");
        } catch (\Exception $ex) {
            \PHPUnit\Framework\Assert::assertEquals(MyUserDefaultException::class, get_class($ex));
        }
        // back to the default exception class
        MyUser::_()->options['user_default_exception_class'] = null;

        // Test name() with session callback
        MyUserSession::_()->setCurrentUser(['id' => 1, 'username' => 'session_user']);
        $name = Helper::User()->name(false);
        \PHPUnit\Framework\Assert::assertEquals('session_user', $name);

        // Test addExtViewData() default branch (without callback) - via mergeViewData
        unset(MyUser::_()->options['user_callback_for_add_ext_view_data']);
        // Set session user first since addExtViewData calls $this->id(true) and $this->name(true)
        MyUserSession::_()->setCurrentUser(['id' => 99, 'username' => 'extuser']);
        $extData = Helper::User()->mergeViewData(['test' => 'value']);
        \PHPUnit\Framework\Assert::assertEquals('value', $extData['test'] ?? null);
        \PHPUnit\Framework\Assert::assertEquals(99, $extData['__logined_id'] ?? null);
        \PHPUnit\Framework\Assert::assertEquals('extuser', $extData['__logined_name'] ?? null);
        \PHPUnit\Framework\Assert::assertArrayHasKey('__logined_url_logout', $extData);

        // Test go_url() fallback branch (when callback not set but URL is set)
        MyUser::_()->options['user_callback_for_url_for_register'] = null; // clear callback
        MyUser::_()->options['user_url_register'] = 'register'; // but URL is set
        $registerUrl = Helper::User()->urlForRegister();
        \PHPUnit\Framework\Assert::assertStringContainsString('register', $registerUrl);

        // Test batchGetUsernames() with data
        MyUserService::_()->resetSession();
        $usernames = Helper::User()->batchGetUsernames([1, 2]);
        \PHPUnit\Framework\Assert::assertCount(2, $usernames);

        // Test exception paths: id() and name() when no provider is set
        // We need a separate test class for this since we need to unset callbacks
        // Unset both id and name callbacks, and session callback
        MyUser::_()->options['user_callback_for_id'] = null;
        MyUser::_()->options['user_callback_for_name'] = null;
        MyUser::_()->options['user_callback_for_session'] = null;
        MyUser::_()->options['user_callback_for_login_service'] = null;
        try {
            Helper::User()->id(false);
            \PHPUnit\Framework\Assert::fail("Should throw DuckPhpSystemException");
        } catch (\DuckPhp\Core\DuckPhpSystemException $ex) {
            \PHPUnit\Framework\Assert::assertStringContainsString("No GlobalUser Provider", $ex->getMessage());
        }
        try {
            Helper::User()->name(false);
            \PHPUnit\Framework\Assert::fail("Should throw DuckPhpSystemException");
        } catch (\DuckPhp\Core\DuckPhpSystemException $ex) {
            \PHPUnit\Framework\Assert::assertStringContainsString("No GlobalUser Provider", $ex->getMessage());
        }

        \LibCoverage\LibCoverage::End();
    }
}
class MyUserDefaultException extends UserException
{
}
class MyUser extends GlobalUser
{
    public $options =[
        'user_url_home' => 'home',
        'user_url_register' => 'register',
        'user_url_login' => 'login',
        'user_url_logout' => 'logout',

        'user_callback_for_id' => [MyUserAction::class,'id'],
        'user_callback_for_name' => [MyUserAction::class,'name'],
        'user_callback_for_url_for_login' => [MyUserAction::class,'urlForLogin'],
        'user_callback_for_url_for_home' => [MyUserAction::class,'urlForHome'],
        'user_callback_for_url_for_register' => [MyUserAction::class,'urlForRegister'],
        'user_callback_for_url_for_logout' => [MyUserAction::class,'urlForLogout'],
        'user_callback_for_local_service'=>[MyUserService::class,'_'],
        'user_callback_for_data' => [MyUserAction::class, 'data'],
        'user_view_file_header'=>'/abc',
        'user_loginout_auto_redirect' => false,
    ];
}
class MyUserAction {
    use SingletonTrait;
    public function id(bool $check_login = true)
    {
        return 1;
    }
    public function name(bool $check_login = true):string
    {
        return "test_user";
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
    public function urlForRegister(?string $url_back = null, ?array $ext = null): string
    {
        return 'register';
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
class MyUserService {
    use SingletonTrait;
    protected $sessionData = [];
    public function canAccess($user_id, string $class, string $method, ?string $url = null): bool
    {
        return true;
    }
    public function batchGetUsernames(array $ids): array
    {
        return array_combine($ids, array_map(fn($id) => "user_$id", $ids));
    }
    public function register(array $post): array
    {
        $id = count($this->sessionData) + 1;
        $user = ['id' => $id, 'username' => $post['username'] ?? 'user_' . $id];
        $this->sessionData[$id] = $user;
        return $user;
    }
    public function login(array $post): array
    {
        $id = $post['id'] ?? 1;
        $user = ['id' => $id, 'username' => $post['username'] ?? 'user_' . $id];
        $this->sessionData[$id] = $user;
        return $user;
    }
    public function logout($user_id): void
    {
        unset($this->sessionData[$user_id]);
    }
    public function setCurrentUser(array $user): void
    {
        $this->sessionData[$user['id']] = $user;
    }
    public function resetSession(): void
    {
        $this->sessionData = [];
    }
}
class MyUserSession implements \DuckPhp\GlobalUser\UserSessionInterface {
    use SingletonTrait;
    protected $currentUserId = null;
    protected $currentUserName = null;
    public function getCurrentUserId()
    {
        return $this->currentUserId;
    }
    public function getCurrentUserName(): string
    {
        return $this->currentUserName ?? '';
    }
    public function setCurrentUser($user)
    {
        $this->currentUserId = $user['id'] ?? null;
        $this->currentUserName = $user['username'] ?? '';
    }
    public function unsetCurrentUser()
    {
        $this->currentUserId = null;
        $this->currentUserName = null;
    }
    public function getCurrentUser()
    {
        return ['id' => $this->currentUserId, 'name' => $this->currentUserName];
    }
}
