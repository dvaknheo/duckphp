<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\GlobalUser;

use DuckPhp\Component\GlobalEvent;
use DuckPhp\Component\PhaseProxy;
use DuckPhp\Core\App;
use DuckPhp\Core\ComponentBase;
use DuckPhp\Core\View;
use DuckPhp\GlobalUser\UserActionInterface;
use DuckPhp\GlobalUser\UserException;

class GlobalUser extends ComponentBase implements UserActionInterface
{
    const EVENT_LOGINED = 'logined';
    const EVENT_LOGOUTED = 'logouted';
    
    public function localService()
    {
        throw new UserException("No Impelment:".__METHOD__);
        // return $object;
    }
    public function service()
    {
        $service = $this->localService();
        if (!$service) {
            throw new UserException("No Impelment:".__METHOD__);
        }
        return PhaseProxy::CreatePhaseProxy($service, App::Phase());
    }
    public function id($check_login = true)
    {
        throw new UserException("No Impelment:".__METHOD__);
    }
    public function name($check_login = true) : string
    {
        throw new UserException("No Impelment:".__METHOD__);
    }
    public function login(array $post)
    {
        throw new UserException("No Impelment:".__METHOD__);
    }
    public function logout(): void
    {
        throw new UserException("No Impelment:".__METHOD__);
    }
    public function regist(array $post)
    {
        throw new UserException("No Impelment:".__METHOD__);
    }
    public function on($event, $callback)
    {
        $phase = App::_()->getLastPhase();
        GlobalEvent::_()->on(GlobalUser::class . '::' . $event, $phase, $callback);
    }
    public function fire($event, ...$args)
    {
        GlobalEvent::_()->fire(GlobalUser::class . '::' . $event, ...$args);
    }
    ///////////////
    public function urlForLogin($url_back = null, $ext = null) : string
    {
        throw new UserException("No Impelment:".__METHOD__);
    }
    public function urlForLogout($url_back = null, $ext = null) : string
    {
        throw new UserException("No Impelment:".__METHOD__);
    }
    public function urlForHome($url_back = null, $ext = null) : string
    {
        throw new UserException("No Impelment:".__METHOD__);
    }
    public function urlForRegist($url_back = null, $ext = null) : string
    {
        throw new UserException("No Impelment:".__METHOD__);
    }
    ///////////////
    public function log(string $string, ?string $type = null)
    {
        return $this->localService()->doLog($this->id(), $string, $type);
    }
    public function batchGetUsernames($ids)
    {
        return $this->localService()->doBatchGetUsernames($ids);
    }
    public function checkAccess($class, string $method, ?string $url = null)
    {
        return $this->localService()->doCheckAccess($this->id(), $class, $method, $url);
    }
    public function getHeaderFooterData(array $input): array
    {
        return [
            'user_view' => [
                'header' => '',
                'footer' => '',
            ]
        ];
    }
    public function mergeView(array $data, bool $with_set_head_foot = true, ?string $header = null, ?string $footer = null): array
    {
        $phase = App::Phase();
        $last_phase = App::_()->getLastPhase();

        $user_view = $this->getHeaderFooterData($data);

        App::Phase($last_phase);
        $data['user_view'] = $user_view;

        if ($with_set_head_foot) {
            View::_()->setViewHeadFoot($header, $footer);
        }

        App::Phase($phase);
        return $data;
    }
}
