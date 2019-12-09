<?php
namespace Project\Controller;

use Project\Base\App;
use Project\Base\Helper\ControllerHelper as C;
use Project\Service\SessionService;
use Project\Service\UserService;

class Main
{
    public function __construct()
    {
        if(C::getRouteCallingMethod()==='index'){
            return;
        }
        $this->setLayoutData();
    }
    public function index()
    {
        $url_reg=C::URL('register');
        $url_login=C::URL('login');
        C::Show(get_defined_vars(),'main');
    }
    protected function setLayoutData()
    {
        $csrf_token=SessionService::G()->csrf_token();
        $csrf_field=SessionService::G()->csrf_field();
        $current_user=[];//=SessionService::G()->getCurrentUser(); //auth()->guard()->guest()
        
        $user_name=$current_user? $current_user['name']:''; // Auth::user()->name
        unset($current_user);
        
        C::assignViewData(get_defined_vars());
    }
    public function register()
    {
        $url_register=C::URL('register');
        
        $csrf_field=SessionService::G()->csrf_field();
        list($olds,$errors)=SessionService::G()->getRegisterInfo();
        
        C::Show(get_defined_vars(),'auth/register');
    }
    public function do_register()
    {
        $post=C::SG()->_POST;
        $errors=UserService::G()->validateRegister($post);
        if($errors){
            SessionService::G()->setRegisterInfo($post, $errors);
            C::ExitRouteTo('register',false);
            
            return;
        }
        
        $user=UserService::G()->register($post);
        SessionService::G()->setCurrentUser($user);
        C::ExitRouteTo('home',false);
    }
    public function login()
    {
        $csrf_field=SessionService::G()->csrf_field();
        list($olds,$errors)=SessionService::G()->getLoginInfo();
        
        $has_route_password_request=true; //Route::has('password.request');
        
        $url_login=C::URL('login'); 
        $url_password_request=('password/reset');
        
        C::Show(get_defined_vars(),'auth/login');
    }
    public function do_login()
    {
        $post=C::SG()->_POST;
        
        $errors=UserServive::G()->validateLogin($post);
        if($errors){
            SessionService::G()->setLoginError($errors,$post['remmeber']);
            C::ExitRouteTo('login',false);
            return;
        }
        $user=UserServive::G()->login($post);
        if(empty($user)){
            $this->incrementLoginAttempts($request);
            return $this->sendFailedLoginResponse($request);
        }
        C::ExitRouteTo('home',false);
    }
    public function home()
    {
        $is_guest=false;
        $session_status='';
        C::Show(get_defined_vars(),'home');
    }
    public function test()
    {
        $t=[
            'email'=>'t9@xx.com',
            'password'=>'123456',
        ];
        $user=UserService::G()->login($t);
        SessionService::G()->setCurrentUser($user);
        $user=SessionService::G()->getCurrentUser();
        SessionService::G()->logout();
        return;
    }
}
