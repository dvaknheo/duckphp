<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\GlobalUser;

use DuckPhp\Component\PhaseProxy;
use DuckPhp\Core\ComponentBase;
use DuckPhp\Core\DuckPhpSystemException;
use DuckPhp\Core\View;
use DuckPhp\GlobalUser\UserActionInterface;

class GlobalUser extends ComponentBase implements UserActionInterface
{
    public $options = [
        'user_url_home' => null,
        'user_url_regist' => null,
        'user_url_login' => null,
        'user_url_logout' => null,
        
        'user_view_file_header' => null, // 'inc-head',
        'user_view_file_footer' => null, // 'inc-foot',
        
        'user_enable_callback_singleton' => true,
        'user_callback_for_id' => null, //[UserAction::class,'id'],
        'user_callback_for_name' => null, //[UserAction::class,'name'],
        'user_callback_for_data' => null, //[UserAction::class,'data'],
        'user_callback_for_service' => null, //[UserAction::class,'service'],

        'user_callback_for_url_for_home' => null,
        'user_callback_for_url_for_regist' => null,
        'user_callback_for_url_for_login' => null,
        'user_callback_for_url_for_logout' => null,
    ];
    protected function run_callback_by_key(string $key, ...$args)
    {
        DuckPhpSystemException::ThrowOn(!isset($this->options[$key]), static::class. " need app options '$key'");

        $callback = $this->options[$key];

        if (is_array($callback) && is_string($callback[0])) {
            $class = $callback[0];
            $callback[0] = $class::_();
        }
        return call_user_func($callback, ...$args);
    }
    /**
     * @param bool $check_login
     * @return int|string
     */
    public function id(bool $check_login = true)
    {
        return $this->run_callback_by_key('user_callback_for_id', $check_login);
    }
    public function name(bool $check_login = true): string
    {
        return $this->run_callback_by_key('user_callback_for_name', $check_login);
    }
    public function data(bool $check_login = true): array
    {
        return $this->run_callback_by_key('user_callback_for_data', $check_login);
    }
    public function localService()
    {
        return $this->run_callback_by_key('user_callback_for_service');
    }
    protected function go_url(string $key_callback, string $key_url, ?string $url_back, ?array $ext)
    {
        if (isset($this->options[$key_callback])) {
            return $this->run_callback_by_key($key_callback, $url_back, $ext);
        }
        DuckPhpSystemException::ThrowOn(!isset($this->options[$key_url]), "need app options '$key_url'");
        return __url($url_back);
    }
    public function urlForHome(?string $url_back = null, ?array $ext = null): string
    {
        return $this->go_url('user_callback_for_url_for_home', 'user_url_home', $url_back, $ext);
    }
    public function urlForRegist(?string $url_back = null, ?array $ext = null): string
    {
        return $this->go_url('user_callback_for_url_for_regist', 'user_url_regist', $url_back, $ext);
    }
    public function urlForLogin(?string $url_back = null, ?array $ext = null): string
    {
        return $this->go_url('user_callback_for_url_for_login', 'user_url_login', $url_back, $ext);
    }
    public function urlForLogout(?string $url_back = null, ?array $ext = null):string
    {
        return $this->go_url('user_callback_for_url_for_logout', 'user_url_logout', $url_back, $ext);
    }
    ///////////////
    public function service()
    {
        $service = $this->localService();
        return PhaseProxy::CreatePhaseProxy($this->context()::Phase(), $service);
    }
    public function mergeViewData(array $input): array
    {
        $header = !isset($this->options['user_view_file_header']) ?  '' : View::_()->_Render($this->options['user_view_file_header'], $input);
        $footer = !isset($this->options['user_view_file_footer']) ?  '' : View::_()->_Render($this->options['user_view_file_footer'], $input);
        $input['__view_data']['header'] = $header;
        $input['__view_data']['footer'] = $footer;
        return $input;
    }
    ///////////////
    public function checkAccess($class, string $method, ?string $url = null)
    {
        return $this->localService()->checkAccess($this->id(), $class, $method, $url);
    }
    public function log(string $string, ?string $type = null, array $ext = [])
    {
        return $this->localService()->log($this->id(), $string, $type, $ext);
    }
    public function batchGetUsernames(array $ids): array
    {
        return $this->localService()->batchGetUsernames($ids);
    }
}
