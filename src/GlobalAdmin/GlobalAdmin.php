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
use DuckPhp\Foundation\Controller\ControllerHelper;
use DuckPhp\GlobalAdmin\AdminActionInterface;
use DuckPhp\GlobalAdmin\AdminSessionInterface;

class GlobalAdmin extends Admin implements AdminActionInterface, AdminLoginActionInterface
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
        'globaladmin_is_authed_redirect' => true,

        'globaladmin_url_home' => null,
        'globaladmin_url_login' => null,
        'globaladmin_url_logout' => null,

        // 'inc-head',
        'globaladmin_view_file_header' => null,
        // 'inc-foot',
        'globaladmin_view_file_footer' => null,


        'globaladmin_enable_callback_singleton' => true,
        //[AdminAction::class,'service'],
        'globaladmin_local_service' => null,
        //[AdminAction::class,'loginservice'],
        'globaladmin_login_service' => null,
        //[AdminAction::class,'loginsession'],
        'globaladmin_login_session' => null,
        //[AdminAction::class,'addExtViewData'],
        'globaladmin_ext_view_data_callback' => null,

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
            throw new DuckPhpSystemException(" need ext options '$key'", -1);
        }

        $callback = $this->options[$key];

        if (\is_array($callback) && \is_string($callback[0])) {
            $class = $callback[0];
            $flag = $this->options['globaladmin_enable_callback_singleton'] ?? true;
            if ($flag) {
                $callback[0] = $class::_();
            }
        }
        return \call_user_func($callback, ...$args);
    }
    protected function throwLoginOn($flag)
    {
        CoreHelper::ControllerThrowOn($flag, AdminException::MESSAGE_NEED_LOGIN, AdminException::CODE_NEED_LOGIN, AdminException::class);
    }
    /**
     * @param bool $check_login
     * @return int|string
     */
    public function id(bool $check_login = true)
    {
        $ret = $this->getSession()->getCurrentAdminId();
        $this->throwLoginOn($check_login && !$ret);
        return $ret;
    }
    public function name(bool $check_login = true): string
    {
        $ret = $this->getSession()->getCurrentAdminName();
        $this->throwLoginOn($check_login && !$ret);
        return $ret;
    }
    public function data(bool $check_login = true): array
    {
        $ret = $this->getSession()->getCurrentAdmin();
        $this->throwLoginOn($check_login && !$ret);
        return $ret;
    }
    public function localService()
    {
        return $this->run_callback_by_key('globaladmin_local_service');
    }
    public function urlForHome(): string
    {
        $url = $this->context()->options['url_admin_home'] ?? null;
        $url ??= $this->options['globaladmin_url_home'] ?? '/';
        return __url($url);
    }
    public function urlForLogin(?string $url_back = null): string
    {
        $url = $this->options['globaladmin_url_login'] ?? '/';
        $ext = isset($url_back) ? '?b=' . urlencode((string) $url_back) : '';
        return __url($url) . $ext;
    }
    public function urlForLogout(): string
    {
        $url = $this->context()->options['url_admin_logout'] ?? null;
        $url ??= $this->options['globaladmin_url_logout'] ?? '/';
        return __url($url);
    }
    ///////////////
    /**
     * @param array<string, mixed> $data
     */
    public function mergeViewData(array $data): array
    {
        if (isset($this->options['globaladmin_ext_view_data_callback'])) {
            $data = $this->run_callback_by_key('globaladmin_ext_view_data_callback', $data);
        }

        $full_header_file = $this->options['globaladmin_view_file_header'] ? App::_()->getOverrideableFile('view', $this->options['globaladmin_view_file_header'], true) : null;
        $full_footer_file = $this->options['globaladmin_view_file_footer'] ? App::_()->getOverrideableFile('view', $this->options['globaladmin_view_file_footer'], true) : null;
        $header = $full_header_file ? View::_()->_Render($full_header_file, $data) : null;
        $footer = $full_footer_file ? View::_()->_Render($full_footer_file, $data) : null;
        $data['__view_data']['header'] = $header;
        $data['__view_data']['footer'] = $footer;
        $data['__logined_header_file'] = $full_header_file;
        $data['__logined_footer_file'] = $full_footer_file;
        return parent::mergeViewData($data);
    }
    public function canAccess(?string $url = null, ?string $class = null, ?string $method = null): bool
    {
        $id = $this->id(false);
        if (!$id) {
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
        return $this->localService()->canAccess($id, $url, $class, $method);
    }

    //////////////// AdminLoginActionInterface
    /**
     * Summary of getLoginService
     * @return AdminLoginServiceInterface
     */
    protected function getLoginService()
    {
        return $this->run_callback_by_key('globaladmin_login_service');
    }

    /**
     * Summary of getSession
     * @return AdminSessionInterface
     */
    protected function getSession()
    {
        return $this->run_callback_by_key('globaladmin_login_session');
    }
    public function login(array $post)
    {
        GlobalEvent::_()->fire(self::EVENT_ACTION_ADMIN_LOGINING, $post);
        $admin = $this->getLoginService()->login($post);
        $this->getSession()->setCurrentAdmin($admin);
        GlobalEvent::_()->fire(self::EVENT_ACTION_ADMIN_LOGED, $post);

        if ($this->options['globaladmin_is_authed_redirect']) {
            CoreHelper::Show302($this->urlForHome());
        }
    }
    public function logout()
    {
        $admin_id = $this->id(false);
        GlobalEvent::_()->fire(self::EVENT_ACTION_ADMIN_LOGOUTING, $admin_id);
        $this->getLoginService()->logout($admin_id);
        $this->getSession()->unsetCurrentAdmin();
        GlobalEvent::_()->fire(self::EVENT_ACTION_ADMIN_LOGOUTED, $admin_id);
        if ($this->options['globaladmin_is_authed_redirect']) {
            CoreHelper::Show302($this->urlForLogin());
        }
    }
    ///////////////
}
