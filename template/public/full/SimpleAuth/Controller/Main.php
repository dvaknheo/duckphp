<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace SimpleAuth\Controller;

use SimpleAuth\Business\UserBusiness;
use SimpleAuth\Business\UserBusinessException;
use SimpleAuth\Helper\ControllerHelper as C;

class Main
{    
    public function __construct()
    {
        $this->initController();
    }
    protected function initController()
    {
        $method = C::getRouteCallingMethod();
        
        C::SessionManager()->checkCsrf();
        if (in_array($method, ['index','register','login','logout'])) {
            return;
        }
        C::assignExceptionHandler(C::SessionManager()->getExceptionClass(), [static::class, 'OnSessionException']);
        $this->setLayoutData();
    }
    public static function OnSessionException($ex = null)
    {
        if(!isset($ex)){
            C::Exit404();
            return;
        }
        $code = $ex->getCode();
        __logger()->warning(''.(get_class($ex)).'('.$ex->getCode().'): '.$ex->getMessage());
        if (C::SessionManager()->isCsrfException($ex) && C::IsDebug()) {
            C::exit(0);
        }
        C::ExitRouteTo('login');
    }

    protected function setLayoutData()
    {
        $csrf_token = C::SessionManager()->csrf_token();
        $csrf_field = C::SessionManager()->csrf_field();
        
        try{
            $user_name = C::SessionManager()->getCurrentUser()['username'] ?? '';
        }catch(\Throwable $ex){
            $user_name='';
        }
        C::setViewHeadFoot('inc-head','inc-foot');
        C::assignViewData(get_defined_vars());
    }
    public function index()
    {
        C::setViewHeadFoot(null,null);
        $url_reg = C::Url('register');
        $url_login = C::Url('login');
        C::Show(get_defined_vars(), 'main');
    }
    public function home()
    {
        $url_logout = C::Url('logout');
        C::Show(get_defined_vars(), 'home');
    }
    public function register()
    {
        $csrf_field = C::SessionManager()->csrf_field();
        $url_register = C::Url('register');
        C::Show(get_defined_vars(), 'register');
    }
    public function login()
    {
        $csrf_field = C::SessionManager()->csrf_field();
        $url_login = C::Url('login');
        C::Show(get_defined_vars(), 'login');
    }
    public function password()
    {
        $user = C::SessionManager()->getCurrentUser();

        C::Show(get_defined_vars(), 'password');
    }
    public function logout()
    {
        C::SessionManager()->logout();
        C::ExitRouteTo('index');
    }
    ////////////////////////////////////////////
    public function do_register()
    {
        $post = C::POST();
        try {
            $post['password'] = $post['password'] ?? '';
            $post['password_confirm'] = $post['password_confirm'] ?? '';
            
            $user = UserBusiness::G()->register($post);
            C::SessionManager()->setCurrentUser($user);
            C::ExitRouteTo('home');
        } catch (UserBusinessException $ex) {
            $error = $ex->getMessage();
            $name = C::POST('name', '');
            C::Show(get_defined_vars(), 'register');
            return;
        }
        ;
    }
    public function do_login()
    {
        $post = C::POST();
        try {
            $user = UserBusiness::G()->login($post);
            C::SessionManager()->setCurrentUser($user);
            C::ExitRouteTo('home');
        } catch (\Exception $ex) {
            $error = $ex->getMessage();
            $name = $post['name'] ?? '';
            C::Show(get_defined_vars(), 'login');
            return;
        }
        
    }
    public function do_password()
    {
        $post = C::POST();
        try {
        
            $old_pass = C::POST('oldpassword','');
            $uid = C::SessionManager()->getCurrentUid();
            $new_pass = $post['newpassword'] ?? '';
            $confirm_pass = $post['newpassword_confirm'] ?? '';
            
            UserBusinessException::ThrowOn($new_pass !== $confirm_pass, '重复密码不一致'); // 放到 Business 里？
            UserBusiness::G()->changePassword($uid, $old_pass, $new_pass);
            $error = "密码修改完毕";            
        } catch (UserBusinessException $ex) {
            $error = $ex->getMessage();
        }
        C::Show(get_defined_vars(), 'password');
        
    }
}
