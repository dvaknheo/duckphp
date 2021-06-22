<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace SimpleAuth\Controller;

use SimpleAuth\System\Helper\ControllerHelper as C;
use SimpleAuth\Business\SessionBusiness;
use SimpleAuth\Business\SessionException;
use SimpleAuth\Business\UserBusiness;
use SimpleAuth\Business\UserBusinessException;

use DuckPhp\SingletonEx\SingletonExTrait;

class Main
{
    use SingletonExTrait;
    
    public function __construct()
    {
        $method = C::getRouteCallingMethod();
        
        if (in_array($method, ['index','register','login','logout','test'])) {
            return;
        }
        C::assignExceptionHandler(C::SessionManager()->getExceptionClass(), function ($ex) {
            $code = $ex->getCode();
            C::Logger()->warning(''.(get_class($ex)).'('.$ex->getCode().'): '.$ex->getMessage());
            if (C::SessionManager()->isCsrfException($ex) && C::IsDebug()) {
                C::exit(0);
            }
            C::ExitRouteTo('login');
        });
        if (!empty(C::POST())) {
            $referer = C::SERVER('HTTP_REFERER','');
            $domain = C::Domain(true).'/';
            C::SessionManager()->checkDomain($referer, $domain);

            //$flag=C::SessionManager()->checkCsrf($_POST['_token']??null);
        }
        $this->setLayoutData();
    }

    protected function setLayoutData()
    {
        $csrf_token = C::SessionManager()->csrf_token();
        $csrf_field = C::SessionManager()->csrf_field();
        
        try{
            $current_user = C::SessionManager()->getCurrentUser();
            $user_name = $current_user? $current_user['username']:'';
            unset($current_user);
        }catch(\Throwable $ex){
            $user_name='';
        }
        
        C::assignViewData(get_defined_vars());
    }
    public function index()
    {
        //TODO  首页，如果不是直接运行模式，则 404
        $url_reg = C::URL('register');
        $url_login = C::URL('login');
        C::Show(get_defined_vars(), 'main');
    }
    public function home()
    {
        $token = C::SessionManager()->csrf_token();
        $url_logout = C::URL('logout'.'?_token='.$token);
        C::Show(get_defined_vars(), 'home');
    }
    public function register()
    {
        $csrf_field = C::SessionManager()->csrf_field();
        $url_register = C::URL('register');
        C::Show(get_defined_vars(), 'auth/register');
    }
    public function login()
    {
        $csrf_field = C::SessionManager()->csrf_field();
        $url_login = C::URL('login');
        C::Show(get_defined_vars(), 'auth/login');
    }
    public function password()
    {
        $user = C::SessionManager()->getCurrentUser();

        C::Show(get_defined_vars(), 'auth/password');
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
        } catch (UserBusinessException $ex) {
            $error = $ex->getMessage();
            $name = C::POST('name', '');
            C::Show(get_defined_vars(), 'auth/register');
            return;
        }
        C::ExitRouteTo('home');
    }
    public function do_login()
    {
        $post = C::POST();
        try {
            $user = UserBusiness::G()->login($post);
            C::SessionManager()->setCurrentUser($user);
        } catch (\Exception $ex) {
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
        
        $user = C::SessionManager()->getCurrentUser();
        $uid = C::SessionManager()->getCurrentUid();
        
        try {
            UserBusinessException::ThrowOn($new_pass !== $confirm_pass, '重复密码不一致');
            UserBusiness::G()->changePassword($uid, $old_pass, $new_pass);
            
            $error = "密码修改完毕";
        } catch (UserBusinessException $ex) {
            $error = $ex->getMessage();
        }
        C::Show(get_defined_vars(), 'auth/password');
    }
}
