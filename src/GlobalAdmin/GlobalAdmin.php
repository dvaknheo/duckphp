<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\GlobalAdmin;

use DuckPhp\Component\PhaseProxy;
use DuckPhp\Core\ComponentBase;
use DuckPhp\Core\DuckPhpSystemException;
use DuckPhp\Core\View;
use DuckPhp\GlobalAdmin\AdminActionInterface;

class GlobalAdmin extends ComponentBase implements AdminActionInterface
{
    public $options = [
        'admin_url_home' => null,
        'admin_url_login' => null,
        'admin_url_logout' => null,
        
        'admin_view_file_header' => null, // 'inc-head',
        'admin_view_file_footer' => null, // 'inc-foot',
        
        'admin_enable_callback_singleton' => true,
        'admin_callback_get_id' => null, //[UserAction::class,'id'],
        'admin_callback_get_name' => null, //[UserAction::class,'name'],
        'admin_callback_get_data' => null, //[UserAction::class,'data'],
        'admin_callback_get_service' => null, //[UserAction::class,'service'],

        'admin_callback_url_home' => null,
        'admin_callback_url_login' => null,
        'admin_callback_url_logout' => null,
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
        return $this->run_callback_by_key('admin_callback_get_id', $check_login);
    }
    public function name(bool $check_login = true): string
    {
        return $this->run_callback_by_key('admin_callback_get_name', $check_login);
    }
    public function data(bool $check_login = true): array
    {
        return $this->run_callback_by_key('admin_callback_get_data', $check_login);
    }
    public function localService()
    {
        return $this->run_callback_by_key('admin_callback_get_service');
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
        return $this->go_url('admin_callback_url_home', 'admin_url_home', $url_back, $ext);
    }
    public function urlForLogin(?string $url_back = null, ?array $ext = null): string
    {
        return $this->go_url('admin_callback_url_login', 'admin_url_login', $url_back, $ext);
    }
    public function urlForLogout(?string $url_back = null, ?array $ext = null):string
    {
        return $this->go_url('admin_callback_url_logout', 'admin_url_logout', $url_back, $ext);
    }
    ///////////////
    public function service()
    {
        $service = $this->localService();
        return PhaseProxy::CreatePhaseProxy($this->context()::Phase(), $service);
    }
    public function mergeViewData(array $input): array
    {
        $header = !isset($this->options['admin_view_file_header']) ?  '' : View::_()->_Render($this->options['admin_view_file_header'], $input);
        $footer = !isset($this->options['admin_view_file_footer']) ?  '' : View::_()->_Render($this->options['admin_view_file_footer'], $input);
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
    public function isSuper(): bool
    {
        return $this->localService()->isSuper($this->id());
    }
}
