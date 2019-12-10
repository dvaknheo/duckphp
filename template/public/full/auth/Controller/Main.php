<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace UserSystemDemo\Controller;

use UserSystemDemo\Base\App;
use UserSystemDemo\Base\Helper\ControllerHelper as C;
use UserSystemDemo\Service\SessionService;
use UserSystemDemo\Service\SessionServiceException;
use UserSystemDemo\Service\UserService;
use UserSystemDemo\Service\UserServiceException;

class Main
{
    public function __construct()
    {
        $method = C::getRouteCallingMethod();
        if (in_array($method, ['index','register','login','logout','test'])) {
            return;
        }
        C::assignExceptionHandler(SessionServiceException::class, function () {
            C::ExitRouteTo('login');
        });
        
        $this->setLayoutData();
    }
    public function index()
    {
        $url_reg = C::URL('register');
        $url_login = C::URL('login');
        C::Show(get_defined_vars(), 'main');
    }
    protected function setLayoutData()
    {
        $csrf_token = SessionService::G()->csrf_token();
        $csrf_field = SessionService::G()->csrf_field();
        
        $current_user = SessionService::G()->getCurrentUser();
        $user_name = $current_user? $current_user['username']:'';
        unset($current_user);
        
        C::assignViewData(get_defined_vars());
    }
    public function home()
    {
        $url_logout = C::URL('logout');
        C::Show(get_defined_vars(), 'home');
    }
    public function register()
    {
        $csrf_field = SessionService::G()->csrf_field();
        $url_register = C::URL('register');
        C::Show(get_defined_vars(), 'auth/register');
    }
    public function login()
    {
        $csrf_field = SessionService::G()->csrf_field();
        $url_login = C::URL('login');
        C::Show(get_defined_vars(), 'auth/login');
    }
    public function logout()
    {
        SessionService::G()->logout();
        C::ExitRouteTo('index');
    }
    public function test()
    {
        $name = 'DKTest4';
        $user = UserService::G()->login(['name' => $name,'password' => '123456']);
        SessionService::G()->setCurrentUser($user);
        $user = SessionService::G()->getCurrentUser();
        SessionService::G()->logout();
        
        var_dump(DATE(DATE_ATOM));
    }
    ////////////////////////////////////////////
    public function do_register()
    {
        $post = C::SG()->_POST;
        try {
            $post['password'] = $post['password'] ?? '';
            $post['password_confirm'] = $post['password_confirm'] ?? '';
            UserServiceException::ThrowOn($post['password'] != $post['password_confirm'], '重复密码不一致');
            $user = UserService::G()->register($post);
            SessionService::G()->setCurrentUser($user);
        } catch (UserServiceException $ex) {
            $error = $ex->getMessage();
            $name = $post['name'] ?? '';
            C::Show(get_defined_vars(), 'auth/register');
            return;
        }
        C::ExitRouteTo('home');
    }
    public function do_login()
    {
        $post = C::SG()->_POST;
        try {
            $user = UserService::G()->login($post);
            SessionService::G()->setCurrentUser($user);
        } catch (UserServiceException $ex) {
            $error = $ex->getMessage();
            $name = $post['name'] ?? '';
            C::Show(get_defined_vars(), 'auth/login');
            return;
        }
        C::ExitRouteTo('home');
    }
}
