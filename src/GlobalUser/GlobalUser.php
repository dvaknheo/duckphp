<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\GlobalUser;

use DuckPhp\Component\PhaseProxy;
use DuckPhp\Core\App;
use DuckPhp\Core\ComponentBase;

class GlobalUser extends ComponentBase
{
    const EVENT_LOGINED = 'logined';
    const EVENT_LOGOUTED = 'logouted';
    public $actionClass = null; // UserAction::class
    public $serviceClass = null;// UserService::class
    public static function ReplaceTo($class)
    {
        GlobalUser::_(PhaseProxy::CreatePhaseProxy(App::Phase(), $class));
    }
    public function action()
    {
        return $this->proxy($this->actionClass);
    }
    public function service()
    {
        return $this->proxy($this->serviceClass);
    }
    protected function proxy($class)
    {
        return PhaseProxy::CreatePhaseProxy(App::Phase(), $class::_());
    }
    
    public function id()
    {
        return $this->action()->id();
    }
    public function name()
    {
        return $this->action()->name();
    }
    public function login(array $post)
    {
        return $this->action()->login($post);
    }
    public function logout()
    {
        return $this->action()->logout();
    }
    public function regist(array $post)
    {
        return $this->action()->login($post);
    }
    ///////////////
    public function urlForLogin($url_back = null, $ext = null)
    {
        return $this->service()->urlForLogin($url_back, $ext);
    }
    public function urlForLogout($url_back = null, $ext = null)
    {
        return $this->service()->urlForLogout($url_back, $ext);
    }
    public function urlForHome($url_back = null, $ext = null)
    {
        return $this->service()->urlForHome($url_back, $ext);
    }
    public function urlForRegist($url_back = null, $ext = null)
    {
        return $this->service()->urlForRegist($url_back, $ext);
    }
    public function getUsernames($ids)
    {
        return $this->service()->getUsernames($ids);
    }
}
