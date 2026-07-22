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
use DuckPhp\Core\DuckPhpSystemException;

class GlobalUser extends ComponentBase implements UserActionInterface
{
    const EVENT_LOGINED = 'logined';
    const EVENT_LOGOUTED = 'logouted';
    
    protected function getService()
    {
        return null; //override me
    }
    public function service()
    {
        $service = $this->getService();
        if(!$service){
            throw new DuckPhpSystemException("No Impelment");
        }
        return PhaseProxy::CreatePhaseProxy($service, App::Phase());
    }
    public function id($check_login = true) : int
    {
        throw new DuckPhpSystemException("No Impelment");
    }
    public function name($check_login = true) : string
    {
        throw new DuckPhpSystemException("No Impelment");
    }
    public function login(array $post)
    {
        throw new DuckPhpSystemException("No Impelment");
    }
    public function logout(): void
    {
        throw new DuckPhpSystemException("No Impelment");
    }
    public function regist(array $post)
    {
        throw new DuckPhpSystemException("No Impelment");
    }
    public function on($event, ?string $phase = null, $callback)
    {
        $phase = App::_()->getLastPhase();
        GlobalEvent::on(GlobalUser::class . '::' . $event, $phase, $callback)
    }
    public function fire($event)
    {
        GlobalEvent::on(GlobalUser::class . '::' . $event);
    }
    ///////////////
    public function urlForLogin($url_back = null, $ext = null) : string
    {
        throw new DuckPhpSystemException("No Impelment");
    }
    public function urlForLogout($url_back = null, $ext = null) : string
    {
        throw new DuckPhpSystemException("No Impelment");
    }
    public function urlForHome($url_back = null, $ext = null) : string
    {
        throw new DuckPhpSystemException("No Impelment");
    }
    public function urlForRegist($url_back = null, $ext = null) : string
    {
        throw new DuckPhpSystemException("No Impelment");
    }
    ///////////////
    public function log(string $string, ?string $type = null)
    {
        return $this->service()->doLog($this->id(), $string, $type);
    }
    public function batchGetUsernames($ids)
    {
        return $this->service()->doBatchGetUsernames($ids);
    }
    public function checkAccess($class, string $method, ?string $url = null)
    {
        return $this->service()->doCheckAccess($class, $method, $url);
    }
    public function getHeaderFooterData(array $input): array
    {
        return [
            'user_view'=> [
                'header' =>'',
                'footer' =>'',
            ]
        ];
    }
    public function mergeView($data,$header,$footer)
    {
        $phase = App::Phase();
        $last_phase = App::_()->getLastPhase();

        $user_view = $this->getHeaderFooterData($data);

        App::Phase($last_phase);
        $data['user_view'] = $user_view;

        View::_()->setViewHeadFoot($header,$footer);

        App::Phase($phase);
        return $data;
    }
}
