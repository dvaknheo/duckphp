<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

namespace DuckPhp\GlobalAdmin;

use DuckPhp\Component\PhaseProxy;
use DuckPhp\Core\App;
use DuckPhp\Core\ComponentBase;
use DuckPhp\Core\DuckPhpSystemException;
use DuckPhp\Core\Route;
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
        'admin_callback_for_id' => null, //[AdminAction::class,'id'],
        'admin_callback_for_name' => null, //[AdminAction::class,'name'],
        'admin_callback_for_data' => null, //[AdminAction::class,'data'],
        'admin_callback_for_local_service' => null, //[AdminAction::class,'service'],
        'admin_callback_for_add_ext_view_data' => null, //[AdminAction::class,'addExtViewData'],

        'admin_callback_for_url_for_home' => null,
        'admin_callback_for_url_for_login' => null,
        'admin_callback_for_url_for_logout' => null,
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
        DuckPhpSystemException::ThrowOn(!isset($this->options['admin_callback_for_id']), "No GlobalAdmin Provider.");
        return $this->run_callback_by_key('admin_callback_for_id', $check_login);
    }
    public function name(bool $check_login = true): string
    {
        return $this->run_callback_by_key('admin_callback_for_name', $check_login);
    }
    public function data(bool $check_login = true): array
    {
        return $this->run_callback_by_key('admin_callback_for_data', $check_login);
    }
    public function localService()
    {
        return $this->run_callback_by_key('admin_callback_for_local_service');
    }
    /**
     * @param array<string, mixed> $ext
     */
    protected function go_url(string $key_callback, string $key_url, ?string $url_back, ?array $ext)
    {
        if (isset($this->options[$key_callback])) {
            return $this->run_callback_by_key($key_callback, $url_back, $ext);
        }
        DuckPhpSystemException::ThrowOn(!isset($this->options[$key_url]), "need app options '$key_url'");
        $url = $this->options[$key_url];
        return __url($url);
    }
    /**
     * @param array<string, mixed> $ext
     */
    public function urlForHome(?string $url_back = null, ?array $ext = null): string
    {
        return $this->go_url('admin_callback_for_url_for_home', 'admin_url_home', $url_back, $ext);
    }
    /**
     * @param array<string, mixed> $ext
     */
    public function urlForLogin(?string $url_back = null, ?array $ext = null): string
    {
        return $this->go_url('admin_callback_for_url_for_login', 'admin_url_login', $url_back, $ext);
    }
    /**
     * @param array<string, mixed> $ext
     */
    public function urlForLogout(?string $url_back = null, ?array $ext = null):string
    {
        return $this->go_url('admin_callback_for_url_for_logout', 'admin_url_logout', $url_back, $ext);
    }
    ///////////////
    public function service()
    {
        $service = $this->localService();
        return PhaseProxy::CreatePhaseProxy($this->context()::Phase(), $service);
    }
    /**
     * @param array<string, mixed> $input
     * @param array<string, mixed> $input
     * @param array<string, mixed> $input
     * @param array<string, mixed> $input
     */
    protected function addExtViewData(array $input): array
    {
        if (isset($this->options['admin_callback_for_add_ext_view_data'])) {
            return $this->run_callback_by_key('admin_callback_for_add_ext_view_data', $input);
        }
        $input['__logined_id'] ??= $this->id(true);
        $input['__logined_name'] ??= $this->name(true);
        $input['__logined_url_logout'] ??= $this->urlForLogout();

        return $input;
    }
    /**
     * @param array<string, mixed> $input
     */
    public function mergeViewData(array $input): array
    {
        $input = $this->addExtViewData($input);
        $header = '';
        $footer = '';
        if (isset($this->options['admin_view_file_header'])) {
            $header = View::_()->_Render($this->options['admin_view_file_header'], $input);
        }
        if (isset($this->options['admin_view_file_footer'])) {
            $footer = View::_()->_Render($this->options['admin_view_file_footer'], $input);
        }
        $input['__view_data']['header'] = $header;
        $input['__view_data']['footer'] = $footer;
        return $input;
    }
    /**
     * @param array<string, mixed> $data
     */
    public function _Show(array $data = [], string $view = '')
    {
        $last_phase = App::_()->getLastPhase();
        $data = $this->mergeViewData($data);

        $full_header_file = App::_()->getOverrideableFile('view', $this->options['admin_view_file_header'], true);
        $full_footer_file = App::_()->getOverrideableFile('view', $this->options['admin_view_file_footer'], true);

        $old_phase = App::Phase($last_phase);
        App::_()->onBeforeOutput();
        View::_()->setViewHeadFoot($full_header_file, $full_footer_file);
        $ret = View::_()->_Show($data, $view);
        App::Phase($old_phase);
        return $ret;
    }
    ///////////////
    public function canAccess(?string $class = null, ?string $method = null, ?string $url = null): bool
    {
        $id = $this->id(false);
        if(empty($id)) {
            return false;
        }
        if (is_null($class) && is_null($method) && is_null($url)) {
            $last_phase = App::_()->getLastPhase();
            $old_phase = App::Phase($last_phase);

            $class = Route::_()->getRouteCallingClass();
            $method = Route::_()->getRouteCallingMethod();
            $url = Route::_()->_PathInfo();
            $url = __url($url);

            App::Phase($old_phase);
        }
        return $this->localService()->canAccess($id, $class, $method, $url);
    }
    /**
     * @param array<string, mixed> $ext
     */
    public function log(string $string, ?string $type = null, array $ext = [])
    {
        return $this->localService()->log($this->id(), $string, $type, $ext);
    }
    public function isSuper(): bool
    {
        return $this->localService()->isSuper($this->id());
    }
}
