<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\GlobalUser;

use DuckPhp\Core\ComponentBase;

class GlobalUser extends ComponentBase
{
    const EVENT_LOGINED = 'logined';
    const EVENT_LOGOUTED = 'logouted';
    public $action_class = null; // UserAction::class
    public $service_class = null;// UserService::class
    public static function ReplaceTo($class)
    {
        GlobalUser::_(PhaseProxy::CreatePhaseProxy(App::Phase(), $class));
    }
    public function action()
    {
        return $this->proxy($this->action_class);
    }
    public function service()
    {
        return $this->proxy($this->service_class);
    }
    protected function proxy($object)
    {
        return PhaseProxy::Create(App::Phase(), $object);
    }
    
    public function id()
    {
        return $this->action()->id();
    }
    public function name()
    {
        return $this->action()->name();
    }
    public function login($post)
    {
        return $this->action()->login($post);
    }
    public function logout(array $post)
    {
        return $this->action()->logout($post);
    }
    public function regist($post)
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
