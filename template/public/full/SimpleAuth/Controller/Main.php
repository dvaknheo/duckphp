<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace SimpleAuth\Controller;

use SimpleAuth\Base\App;
use SimpleAuth\Base\Helper\ControllerHelper as C;
use SimpleAuth\Service\SessionService;
use SimpleAuth\Service\SessionServiceException;
use SimpleAuth\Service\UserService;
use SimpleAuth\Service\UserServiceException;

class Main
{
    public function __construct()
    {
        $method = C::getRouteCallingMethod();
        
        if (in_array($method, ['index','register','login','logout','test'])) {
            return;
        }
        C::assignExceptionHandler(SessionServiceException::class, function ($ex) {
            $code = $ex->getCode();
            if ($code == 419) {
                C::var_dump(419);
                C::DumpTrace();
                
                exit;
            }
            C::Logger()->warning(''.(get_class($ex)).'('.$ex->getCode().'): '.$ex->getMessage());
            C::ExitRouteTo('login');
        });
        if (!empty(C::POST())) {
            $referer = C::SERVER('HTTP_REFERER','');
            $domain = C::Domain().'/';
            if (substr($referer, 0, strlen($domain)) !== $domain) {
                SessionServiceException::ThrowOn(true, "CRSF", 419);
                //防止 csrf 攻击，用于站内无跳板的简单情况
            }
            //$flag=SessionService::G()->checkCsrf($_POST['_token']??null);
        }
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
        
        try{
            $current_user = SessionService::G()->getCurrentUser();
            $user_name = $current_user? $current_user['username']:'';
            unset($current_user);
        }catch(\Throwable $ex){
            $user_name='';
        }
        
        C::assignViewData(get_defined_vars());
    }
    public function home()
    {
        $token = SessionService::G()->csrf_token();
        $url_logout = C::URL('logout'.'?_token='.$token);
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
    public function password()
    {
        $user = SessionService::G()->getCurrentUser();

        C::Show(get_defined_vars(), 'auth/password');
    }
    public function logout()
    {
        //$flag = SessionService::G()->checkCsrf(C::GET('_token'));
        SessionService::G()->logout();
        C::ExitRouteTo('index');
    }
    ////////////////////////////////////////////
    public function do_register()
    {
        $post = C::POST();
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
        $post = C::POST();
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
    public function do_password()
    {
        $post = C::POST();
        
        $old_pass = $post['oldpassword'] ?? '';
        $new_pass = $post['newpassword'] ?? '';
        $confirm_pass = $post['newpassword_confirm'] ?? '';
        
        $uid = SessionService::G()->getCurrentUid();
        $user = SessionService::G()->getCurrentUser();
        
        try {
            UserServiceException::ThrowOn($new_pass !== $confirm_pass, '重复密码不一致');
            UserService::G()->changePassword($uid, $old_pass, $new_pass);
            
            $error = "密码修改完毕";
        } catch (UserServiceException $ex) {
            $error = $ex->getMessage();
        }
        C::Show(get_defined_vars(), 'auth/password');
    }
}
