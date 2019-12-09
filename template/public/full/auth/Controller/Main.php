<?php
namespace UserSystemDemo\Controller;

use UserSystemDemo\Base\App;
use UserSystemDemo\Base\Helper\ControllerHelper as C;
use UserSystemDemo\Service\SessionService;
use UserSystemDemo\Service\UserService;

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
        
        $current_user=SessionService::G()->getCurrentUser();
        $user_name=$current_user? $current_user['name']:'';
        unset($current_user);
        
        C::assignViewData(get_defined_vars());
    }
    public function home()
    {
        C::Show(get_defined_vars(),'home');
    }
    public function register()
    {
        $csrf_field=SessionService::G()->csrf_field();
        $url_register=C::URL('register');
        C::Show(get_defined_vars(),'auth/register');
    }
    public function login()
    {
        $csrf_field=SessionService::G()->csrf_field();
        $url_login=C::URL('login'); 
        C::Show(get_defined_vars(),'auth/login');
    }
    public function test()
    {
    }
    ////////////////////////////////////////////
    public function do_register()
    {
        $post=C::SG()->_POST;
        try{
            $user=UserService::G()->register($post);
            SessionService::G()->setCurrentUser($user);
        }catch(\Exception $ex){
            C::Show(get_defined_vars(),'auth/register');
            return;
        }
        C::ExitRouteTo('home');
    }
    public function do_login()
    {
        $post=C::SG()->_POST;
        try{
            $user=UserService::G()->login($post);
            SessionService::G()->setCurrentUser($user);
        }catch(\Exception $ex){
            C::Show(get_defined_vars(),'auth/login');
            return;
        }
        C::ExitRouteTo('home');
    }
}