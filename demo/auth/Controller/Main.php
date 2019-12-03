<?php
namespace Project\Controller;

use Project\Base\Helper\ControllerHelper as C;
use Project\Base\BaseController;
use Project\Base\App;
use Project\Service\SessionService;
use Project\Service\UserService;

use FilesystemIterator;
use RecursiveDirectoryIterator;
class Main // extends BaseController
{
    public function __construct()
    {
        $this->setLayoutData();
    }
    protected function setLayoutData()
    {
        $locale='en';
        $app_name='Laravel';
        $asset_js='/js/app.js';
        $asset_css='/css/app.css';
        
        $url_root=C::URL('/');
        $url_login=C::URL('login');
        $url_register=C::URL('register');
        $url_logout=C::URL('logout');
        
        $has_route_register=true;           //Route::has('register');
        
        $csrf_token=SessionService::G()->csrf_token();
        $csrf_field=SessionService::G()->csrf_field();
        $current_user=SessionService::G()->getCurrentUser(); //auth()->guard()->guest()
        
        $user_name=$current_user? $current_user['name']:''; // Auth::user()->name
        unset($current_user);
        
        C::assignViewData(get_defined_vars());
    }
    public function indexx()
    {
        $has_route_login=true;              //Route::has('login')
        $has_route_register=true;           //Route::has('register');
        
        $has_logined=SessionService::G()->hasLogin(); //auth()->guard()->check()
        
        $url_home=C::URL('home');           //url('/home')
        $url_login=C::URL('login');         //route('login');
        $url_register=C::URL('register');   //route('register')
        
        C::Show(get_defined_vars(),'welcome');
    }
    public function registerx()
    {
        $url_register=C::URL('register');
        
        $csrf_field=SessionService::G()->csrf_field();
        $olds=SessionService::G()->getRegisterOldInfo();
        $errors=SessionService::G()->getRegisterErrors();
        
        C::Show(get_defined_vars(),'auth/register');
    }
    public function do_registerxxx()
    {
        $post=C::SG()->_POST;
        $errors=UserService::G()->validateRegister($post);
        if($errors){
            SessionService::G()->setRegisterOldInfo($post);
            SessionService::G()->setRegisterErrors($errors);
            
            C::ExitRouteTo('auth/register',false);
            //C::Show(get_defined_vars(),'auth/register');
            return;
        }
        
        $user=UserService::G()->register($data);
        SessionService::G()->setCurrentUser($user);
    }
    public function login()
    {
        $csrf_field=SessionService::G()->csrf_field();
        $olds=SessionService::G()->getLoginOldInfo();
        $errors=SessionService::G()->getLoginErrors();
        
        $has_route_password_request=true; //Route::has('password.request');
        
        $url_login=C::URL('login'); 
        $url_password_request=('password/reset');
        
        C::Show(get_defined_vars(),'auth/login');
    }
    public function do_loginx()
    {
        $post=C::SG()->_POST;
        
        $errors=UserServive::G()->validateLogin($post);
        if($errors){
            SessionService::G()->setLoginError($errors,$post['remmeber']);
            
            C::ExitRouteTo('auth/login');
            return;
        }
        if (false){
            //$this->hasTooManyLoginAttempts($request)) {
            //$this->fireLockoutEvent($request);
            //return $this->sendLockoutResponse($request);
        }
        $user=UserServive::G()->login($post);
        if(empty($user)){
            $this->incrementLoginAttempts($request);
            return $this->sendFailedLoginResponse($request);
        }
        //
        //$this->sendLoginResponse($request);
    }
    public function homex()
    {
        $is_guest=false;
        $session_status='';
        C::Show(get_defined_vars(),'home');
    }
    ////////////////////////////////////
    public function i()
    {
        var_dump($errors);
        var_dump(DATE(DATE_ATOM));
    }
    public function ittt()
    {
        $path=App::G()->options['path'];
        $folder=$path.'storage/framework/views';
        $flags = FilesystemIterator::CURRENT_AS_PATHNAME | FilesystemIterator::SKIP_DOTS | FilesystemIterator::UNIX_PATHS;
        $Directory =new \RecursiveDirectoryIterator($folder, $flags);
        $Iterator = new \RecursiveIteratorIterator($Directory);
        $regex = new \RegexIterator($Iterator, '/^.+\.php$/i', \RecursiveRegexIterator::MATCH);
        foreach($regex as $file){
           $this->run($file);
        }
    }
    protected function run($file)
    {
        $data = file_get_contents($file);
        $path=App::G()->options['path'];
        preg_match('/PATH (.*?) ENDPATH/',$data,$m);
        $viewname=basename($m[1],'.blade.php');
        file_put_contents($path."project/view/blade/{$viewname}.php",$data);
        var_dump($viewname);
    }
}
//        $this->post('email/resend', 'Auth\VerificationController@resend')->name('verification.resend');
