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
                $this->setLayoutData();
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
    public function index()
    {
        $url_reg = C::Url('register');
        $url_login = C::Url('login');
        C::Show(get_defined_vars(), 'main');
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
            $user = UserBusiness::G()->register($post);
            C::SessionManager()->setCurrentUser($user);
            C::ExitRouteTo('home');
        } catch (\Exception $ex) {
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
            C::ExitRouteTo('home'); // TO change.
        } catch (\Exception $ex) {
            $error = $ex->getMessage();
            $name =  __h( C::POST('name', ''));
            C::Show(get_defined_vars(), 'login');
            return;
        }
        
    }
}
