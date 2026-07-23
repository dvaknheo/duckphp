<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\GlobalAdmin;

use DuckPhp\Component\GlobalEvent;
use DuckPhp\Component\PhaseProxy;
use DuckPhp\Core\App;
use DuckPhp\Core\ComponentBase;
use DuckPhp\Core\View;
use DuckPhp\GlobalAdmin\AdminActionInterface;
use DuckPhp\GlobalAdmin\AdminException;

class GlobalAdmin extends ComponentBase implements AdminActionInterface
{
    const EVENT_LOGINED = 'logined';
    const EVENT_LOGOUTED = 'logouted';
    const EVENT_ACCESSED = 'accessed';
    
    public function localService()
    {
        throw new AdminException("No Impelment:".__METHOD__);
        // return $object;
    }
    public function service()
    {
        $service = $this->localService();
        if (!$service) {
            throw new AdminException("No Impelment:".__METHOD__);
        }
        return PhaseProxy::CreatePhaseProxy($service, App::Phase());
    }
    public function id($check_login = true)
    {
        throw new AdminException("No Impelment:".__METHOD__);
    }
    public function name($check_login = true)
    {
        throw new AdminException("No Impelment:".__METHOD__);
    }
    public function login(array $post)
    {
        throw new AdminException("No Impelment:".__METHOD__);
    }
    public function logout(): void
    {
        throw new AdminException("No Impelment:".__METHOD__);
    }
    public function urlForLogin($url_back = null, $ext = null)
    {
        throw new AdminException("No Impelment:".__METHOD__);
    }
    public function urlForLogout($url_back = null, $ext = null)
    {
        throw new AdminException("No Impelment:".__METHOD__);
    }
    public function urlForHome($url_back = null, $ext = null)
    {
        throw new AdminException("No Impelment:".__METHOD__);
    }
    public function on($event, $callback)
    {
        $phase = App::_()->getLastPhase();
        GlobalEvent::_()->on(GlobalAdmin::class . '::' . $event, $phase, $callback);
    }
    public function fire($event, ...$args)
    {
        GlobalEvent::_()->fire(GlobalAdmin::class . '::' . $event, ...$args);
    }
    ///////////////
    public function checkAccess($class, string $method, ?string $url = null)
    {
        return $this->localService()->doCheckAccess($this->id(), $class, $method, $url);
    }
    public function isSuper(): bool
    {
        return $this->localService()->doIsSuper($this->id());
    }
    public function log(string $string, ?string $type = null)
    {
        return $this->localService()->doLog($this->id(), $string, $type);
    }
    ///////////////
    public function getHeaderFooterData(array $input): array
    {
        // merget
        return [
            'header' => '', //View::_()->_Render('admin/header',$inner_data);
            'footer' => '', //View::_()->_Render('admin/footer',$inner_data);
        ];
    }
    public function mergeView(array $data, bool $with_set_head_foot = true, ?string $header = null, ?string $footer = null): array
    {
        $phase = App::Phase();
        $last_phase = App::_()->getLastPhase();

        $admin_view = $this->getHeaderFooterData($data);

        App::Phase($last_phase);
        $data['admin_view'] = $admin_view;

        if ($with_set_head_foot) {
            View::_()->setViewHeadFoot($header, $footer);
        }

        App::Phase($phase);
        return $data;
    }
}
