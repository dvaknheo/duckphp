<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

namespace DuckPhp\GlobalUser;

use DuckPhp\Component\GlobalEvent;
use DuckPhp\Component\PhaseProxy;
use DuckPhp\Core\App;
use DuckPhp\Core\CoreHelper;
use DuckPhp\Core\DuckPhpSystemException;
use DuckPhp\Core\Route;
use DuckPhp\Core\View;
use DuckPhp\GlobalUser\UserSessionInterface;

class GlobalUser extends User implements UserLoginActionInterface
{
    const EVENT_ACTION_USER_REGISTERING = 'ACTION_USER_REGISTERING';
    const EVENT_ACTION_USER_REGISTERED = 'ACTION_USER_REGISTERED';
    const EVENT_ACTION_USER_LOGINING = 'ACTION_USER_LOGINING';
    const EVENT_ACTION_USER_LOGINED = 'ACTION_USER_LOGINED';
    const EVENT_ACTION_USER_LOGOUTING = 'ACTION_USER_LOGOUTING';
    const EVENT_ACTION_USER_LOGOUTED = 'ACTION_USER_LOGOUTED';
    const EVENT_SERVICE_USER_REGISTERING = 'SERVICE_USER_REGISTERING';
    const EVENT_SERVICE_USER_REGISTERED = 'SERVICE_USER_REGISTERED';
    const EVENT_SERVICE_USER_LOGINING = 'SERVICE_USER_LOGINING';
    const EVENT_SERVICE_USER_LOGINED = 'SERVICE_USER_LOGINED';
    const EVENT_SERVICE_USER_LOGOUTING = 'SERVICE_USER_LOGOUTING';
    const EVENT_SERVICE_USER_LOGOUTED = 'SERVICE_USER_LOGOUTED';

    public $options = [
        'globaluser_is_authed_redirect' => true,

        'globaluser_url_home' => null,
        'globaluser_url_register' => null,
        'globaluser_url_login' => null,
        'globaluser_url_logout' => null,

        // 'inc-head',
        'globaluser_view_file_header' => null,
        // 'inc-foot',
        'globaluser_view_file_footer' => null,


        'globaluser_enable_callback_singleton' => true,
        //[UserAction::class,'service'],
        'globaluser_local_service' => null,
        //[UserAction::class,'loginservice'],
        'globaluser_login_service' => null,
        //[UserAction::class,'loginsession'],
        'globaluser_login_session' => null,
        //[UserAction::class,'addExtViewData'],
        'globaluser_ext_view_data_callback' => null,

    ];
    public function init(array $options, ?object $context = null)
    {
        parent::init($options, $context);
        if ($context->options['user_provider_enable'] ?? true) {
            GlobalUser::_(PhaseProxy::CreatePhaseProxy($context->getThisPhaseName(), $this));
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
            $flag = $this->options['globaluser_enable_callback_singleton'] ?? true;
            if ($flag) {
                $callback[0] = $class::_();
            }
        }
        return \call_user_func($callback, ...$args);
    }
    protected function throwLoginOn($flag)
    {
        CoreHelper::ControllerThrowOn($flag, UserException::MESSAGE_NEED_LOGIN, UserException::CODE_NEED_LOGIN, UserException::class);
    }
    /**
     * @param bool $check_login
     * @return int|string
     */
    public function id(bool $check_login = true)
    {
        $ret = $this->getSession()->getCurrentUserId();
        $this->throwLoginOn($check_login && !$ret);
        return $ret;
    }
    public function name(bool $check_login = true): string
    {
        $ret = $this->getSession()->getCurrentUserName();
        $this->throwLoginOn($check_login && !$ret);
        return $ret;
    }
    public function data(bool $check_login = true): array
    {
        $ret = $this->getSession()->getCurrentUser();
        $this->throwLoginOn($check_login && !$ret);
        return $ret;
    }
    public function localService()
    {
        return $this->run_callback_by_key('globaluser_local_service');
    }
    /**
     * @param array<string, mixed> $ext reserved for the caller's own use
     */
    public function urlForHome(?string $url_back = null, ?array $ext = null): string
    {
        $url = $this->context()->options['url_user_home'] ?? null;
        $url ??= $this->options['globaluser_url_home'] ?? '/';
        return __url($url) . $this->buildUrlBackQuery($url_back, $ext);
    }
    /**
     * @param array<string, mixed> $ext reserved for the caller's own use
     */
    public function urlForRegister(?string $url_back = null, ?array $ext = null): string
    {
        $url = $this->options['globaluser_url_register'] ?? '/';
        return __url($url) . $this->buildUrlBackQuery($url_back, $ext);
    }
    /**
     * @param array<string, mixed> $ext reserved for the caller's own use
     */
    public function urlForLogin(?string $url_back = null, ?array $ext = null): string
    {
        $url = $this->options['globaluser_url_login'] ?? '/';
        return __url($url) . $this->buildUrlBackQuery($url_back, $ext);
    }
    /**
     * @param array<string, mixed> $ext reserved for the caller's own use
     */
    public function urlForLogout(?string $url_back = null, ?array $ext = null): string
    {
        $url = $this->context()->options['url_user_logout'] ?? null;
        $url ??= $this->options['globaluser_url_logout'] ?? '/';
        return __url($url) . $this->buildUrlBackQuery($url_back, $ext);
    }
    /**
     * @param array<string, mixed>|null $ext reserved for the caller's own use
     */
    protected function buildUrlBackQuery(?string $url_back, ?array $ext): string
    {
        if ($url_back === null && !$ext) {
            return '';
        }
        $query = $ext ?? [];
        if ($url_back !== null) {
            $query['b'] = $url_back;
        }
        return '?' . http_build_query($query);
    }
    ///////////////
    /**
     * @param array<string, mixed> $data
     */
    public function mergeViewData(array $data): array
    {
        if (isset($this->options['globaluser_ext_view_data_callback'])) {
            $data = $this->run_callback_by_key('globaluser_ext_view_data_callback', $data);
        }

        $full_header_file = $this->options['globaluser_view_file_header'] ? App::_()->getOverrideableFile('view', $this->options['globaluser_view_file_header'], true) : null;
        $full_footer_file = $this->options['globaluser_view_file_footer'] ? App::_()->getOverrideableFile('view', $this->options['globaluser_view_file_footer'], true) : null;
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
    /**
     * @param array<string, mixed> $ids
     */
    public function batchGetUsernames(array $ids): array
    {
        return $this->localService()->batchGetUsernames($ids);
    }

    //////////////// UserLoginActionInterface
    /**
     * Summary of getLoginService
     * @return UserLoginServiceInterface
     */
    protected function getLoginService()
    {
        return $this->run_callback_by_key('globaluser_login_service');
    }

    /**
     * Summary of getSession
     * @return UserSessionInterface
     */
    protected function getSession()
    {
        return $this->run_callback_by_key('globaluser_login_session');
    }
    public function register(array $post)
    {
        GlobalEvent::_()->fire(self::EVENT_ACTION_USER_REGISTERING, $post);
        $user = $this->getLoginService()->register($post);
        $this->getSession()->setCurrentUser($user);
        GlobalEvent::_()->fire(self::EVENT_ACTION_USER_REGISTERED, $post);

        if ($this->options['globaluser_is_authed_redirect']) {
            CoreHelper::Show302($this->urlForHome());
        }
    }
    public function login(array $post)
    {
        GlobalEvent::_()->fire(self::EVENT_ACTION_USER_LOGINING, $post);
        $user = $this->getLoginService()->login($post);
        $this->getSession()->setCurrentUser($user);
        GlobalEvent::_()->fire(self::EVENT_ACTION_USER_LOGINED, $post);

        if ($this->options['globaluser_is_authed_redirect']) {
            CoreHelper::Show302($this->urlForHome());
        }
    }
    public function logout()
    {
        $user_id = $this->id(false);
        GlobalEvent::_()->fire(self::EVENT_ACTION_USER_LOGOUTING, $user_id);
        $this->getLoginService()->logout($user_id);
        $this->getSession()->unsetCurrentUser();
        GlobalEvent::_()->fire(self::EVENT_ACTION_USER_LOGOUTED, $user_id);
        if ($this->options['globaluser_is_authed_redirect']) {
            CoreHelper::Show302($this->urlForLogin());
        }
    }
    ///////////////
}
