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
use DuckPhp\Core\CoreHelper;
use DuckPhp\Core\DuckPhpSystemException;
use DuckPhp\Core\Route;
use DuckPhp\Core\View;
use DuckPhp\GlobalAdmin\AdminActionInterface;
use DuckPhp\GlobalAdmin\AdminSessionInterface;

class GlobalAdmin extends ComponentBase implements AdminActionInterface, AdminLoginActionInterface
{
    const EVENT_ACTION_ADMIN_REGISTERING = 'ACTION_ADMIN_REGISTERING';
    const EVENT_ACTION_ADMIN_REGISTERED = 'ACTION_ADMIN_REGISTERED';
    const EVENT_ACTION_ADMIN_LOGINING = 'ACTION_ADMIN_LOGINING';
    const EVENT_ACTION_ADMIN_LOGED = 'ACTION_ADMIN_LOGINED';
    const EVENT_ACTION_ADMIN_LOGOUTING = 'ACTION_ADMIN_LOGOUTING';
    const EVENT_ACTION_ADMIN_LOGOUTED = 'ACTION_ADMIN_LOGOUTED';
    const EVENT_SERVICE_ADMIN_REGISTERING = 'SERVICE_ADMIN_REGISTERING';
    const EVENT_SERVICE_ADMIN_REGISTERED = 'SERVICE_ADMIN_REGISTERED';
    const EVENT_SERVICE_ADMIN_LOGINING = 'SERVICE_ADMIN_LOGINING';
    const EVENT_SERVICE_ADMIN_LOGINED = 'SERVICE_ADMIN_LOGINED';
    const EVENT_SERVICE_ADMIN_LOGOUTING = 'SERVICE_ADMIN_LOGOUTING';
    const EVENT_SERVICE_ADMIN_LOGOUTED = 'SERVICE_ADMIN_LOGOUTED';

    public $options = [
        'admin_loginout_auto_redirect' => true,

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
        'admin_callback_for_login_service' => null,
        'admin_callback_for_session' => null,

        'admin_callback_for_url_for_home' => null,
        'admin_callback_for_url_for_login' => null,
        'admin_callback_for_url_for_logout' => null,
    ];
    public function init(array $options, ?object $context = null)
    {
        parent::init($options, $context);
        if ($context->options['admin_provider_enable'] ?? true) {
            GlobalAdmin::_(PhaseProxy::CreatePhaseProxy($context->getThisPhaseName(), $this));
        }
        return $this;
    }
    protected function run_callback_by_key(string $key, ...$args)
    {
        if (!isset($this->options[$key])) {
            throw new DuckPhpSystemException(" need app options '$key'", -1);
        }

        $callback = $this->options[$key];

        if (\is_array($callback) && \is_string($callback[0])) {
            $class = $callback[0];
            $flag = $this->options['admin_enable_callback_singleton'] ?? true;
            if ($flag) {
                $callback[0] = $class::_();
            }
        }
        return \call_user_func($callback, ...$args);
    }
    /**
     * @param bool $check_login
     * @return int|string
     */
    public function id(bool $check_login = true)
    {
        if (!$this->is_inited) {
            throw new DuckPhpSystemException("Need Provider", -1);
        }

        if (isset($this->options['admin_callback_for_session'])) {
            $id = $this->getSession()->getCurrentAdminId();
            CoreHelper::ControllerThrowOn($check_login && !$id, AdminException::MESSAGE_NEED_LOGIN, AdminException::CODE_NEED_LOGIN, AdminException::class);
            return $id ?? 0;
        } elseif (isset($this->options['admin_callback_for_id'])) {
            return $this->run_callback_by_key('admin_callback_for_id', $check_login);
        }
        throw new DuckPhpSystemException("No GlobalAdmin Provider.", -1);
    }
    public function name(bool $check_login = true): string
    {
        if (!$this->is_inited) {
            throw new DuckPhpSystemException("Need Provider", -1);
        }
        if (isset($this->options['admin_callback_for_session'])) {

            $name = $this->getSession()->getCurrentAdminName();
            CoreHelper::ControllerThrowOn($check_login && !$name, AdminException::MESSAGE_NEED_LOGIN, AdminException::CODE_NEED_LOGIN, AdminException::class);
            return $name;
        } elseif (isset($this->options['admin_callback_for_name'])) {
            return $this->run_callback_by_key('admin_callback_for_name', $check_login);
        }
        throw new DuckPhpSystemException("No GlobalAdmin Provider.", -2);
    }
    public function data(bool $check_login = true): array
    {
        if (!$this->is_inited) {
            throw new DuckPhpSystemException("Need Provider", -1);
        }
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
        if (!isset($this->options[$key_url])) {
            throw new DuckPhpSystemException("need app options '$key_url'", -1);
        }
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
    public function mergeViewData(array $input): array
    {
        $input = $this->addExtViewData($input);
        return $input;
    }
    /**
     * @param array<string, mixed> $input
     */
    protected function addExtViewData(array $input): array
    {
        if (isset($this->options['admin_callback_for_add_ext_view_data'])) {
            return $this->run_callback_by_key('admin_callback_for_add_ext_view_data', $input);
        }
        return $input;
    }
    /**
     * @param array<string, mixed> $data
     */
    public function _Show(array $data = [], string $view = '')
    {
        $last_phase = App::_()->getLastPhase();

        $data = $this->addExtViewData($data);
        $header = '';
        $footer = '';
        $full_header_file = null;
        $full_footer_file = null;
        if (isset($this->options['admin_view_file_header'])) {
            $header = View::_()->_Render($this->options['admin_view_file_header'], $data);
            $full_header_file = $this->options['admin_view_file_header'] ? App::_()->getOverrideableFile('view', $this->options['admin_view_file_header'], true) : '';

        }
        if (isset($this->options['admin_view_file_footer'])) {
            $footer = View::_()->_Render($this->options['admin_view_file_footer'], $data);
            $full_footer_file = $this->options['admin_view_file_footer'] ? App::_()->getOverrideableFile('view', $this->options['admin_view_file_footer'], true) : '';
        }

        View::_()->data['__view_data']['header'] = $header;
        View::_()->data['__view_data']['footer'] = $footer;

        View::_()->data['__logined_id'] ??= $this->id(true);
        View::_()->data['__logined_name'] ??= $this->name(true);
        View::_()->data['__logined_url_logout'] ??= $this->urlForLogout();
        View::_()->data['__logined_enable_header_footer'] ??= false;

        $old_phase = App::Phase($last_phase);
        App::_()->onBeforeOutput();
        $enable_header_footer = $data['__logined_enable_header_footer'] ?? (View::_()->data['__logined_enable_header_footer'] ?? null);
        if ($enable_header_footer ?? false) {
            View::_()->setViewHeadFoot($full_header_file, $full_footer_file);
        }
        $view = ($view === '') ? Route::_()->getRouteCallingPath() : $view;
        $ret = View::_()->_Show($data, $view);
        App::Phase($old_phase);
        return $ret;
    }
    ///////////////
    protected function getLoginBusiness()
    {
        return $this->run_callback_by_key('admin_callback_for_login_service');
    }

    /**
     * Summary of getSession
     * @return AdminSessionInterface
     */
    protected function getSession()
    {
        return $this->run_callback_by_key('admin_callback_for_session');
    }
    public function login(array $post)
    {
        GlobalEvent::_()->fire(self::EVENT_ACTION_ADMIN_LOGINING, $post);
        $admin = $this->getLoginBusiness()->login($post);
        $this->getSession()->setCurrentAdmin($admin);
        GlobalEvent::_()->fire(self::EVENT_ACTION_ADMIN_LOGED, $post);

        if ($this->options['admin_loginout_auto_redirect']) {
            CoreHelper::Show302($this->urlForHome());
        }
    }
    public function logout()
    {
        $admin_id = $this->id(false);
        GlobalEvent::_()->fire(self::EVENT_ACTION_ADMIN_LOGOUTING, $admin_id);
        $this->getLoginBusiness()->logout($admin_id);
        $this->getSession()->unsetCurrentAdmin();
        GlobalEvent::_()->fire(self::EVENT_ACTION_ADMIN_LOGOUTED, $admin_id);
        if ($this->options['admin_loginout_auto_redirect']) {
            CoreHelper::Show302($this->urlForLogin());
        }
    }
    ///////////////
    public function canAccess(?string $class = null, ?string $method = null, ?string $url = null): bool
    {
        $id = $this->id(false);
        if (empty($id)) {
            return false;
        }
        if (\is_null($class) && \is_null($method) && \is_null($url)) {
            $last_phase = App::_()->getLastPhase();
            $old_phase = App::Phase($last_phase);

            $class = Route::_()->getRouteCallingClass();
            $method = Route::_()->getRouteCallingMethod();
            $url = Route::_()->_PathInfo();

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
