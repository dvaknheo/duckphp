<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\GlobalAdmin;

use DuckPhp\Component\PhaseProxy;
use DuckPhp\Component\ZCallTrait;
use DuckPhp\Core\App;
use DuckPhp\Core\ComponentBase;

class GlobalAdmin extends ComponentBase
{
    const EVENT_LOGINED = 'logined';
    const EVENT_LOGOUTED = 'logouted';
    const EVENT_ACCESSED = 'accessed';
    public $action_class = null; // UserAction::class
    public $service_class = null;// UserService::class
    public static function ReplaceTo($class)
    {
        GlobalAdmin::_(PhaseProxy::CreatePhaseProxy(App::Phase(), $class));
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
    public function login(array $post)
    {
        return $this->action()->login($post);
    }
    public function logout(array $post)
    {
        return $this->action()->logout($post);
    }
    public function checkAccess($class, string $method, ?string $url = null)
    {
        return $this->action()->checkAccess($class, $method, $url);
    }
    public function isSuper()
    {
        return $this->action()->isSuper();
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
}
