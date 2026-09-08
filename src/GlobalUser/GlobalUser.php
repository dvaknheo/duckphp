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
use DuckPhp\Core\CoreHelper;
use DuckPhp\Core\DuckPhpSystemException;
use DuckPhp\Core\Route;
use DuckPhp\Core\View;
use DuckPhp\Foundation\Controller\Helper;
use DuckPhp\GlobalUser\UserActionInterface;
use DuckPhp\GlobalUser\UserSessionInterface;

class GlobalUser extends ComponentBase implements UserActionInterface
{
    const EVENT_ACTION_USER_REGISTERING = 'ACTION_USER_REGISTERING';
    const EVENT_ACTION_USER_REGISTERED = 'ACTION_USER_REGISTERED';
    const EVENT_ACTION_USER_LOGINING = 'ACTION_USER_LOGINING';
    const EVENT_ACTION_USER_LOGINED = 'ACTION_USER_LOGINED';
    const EVENT_ACTION_USER_LOGOUTING = 'ACTION_USER_LOGOUTING';
    const EVENT_ACTION_USER_LOGOUTED = 'ACTION_USER_LOGOUTED';
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
        'user_callback_for_local_service' => null, //[UserAction::class,'service'],
        'user_callback_for_add_ext_view_data' => null, //[UserAction::class,'addExtViewData'],
        'user_callback_for_session' => null,
        'user_loginout_auto_redirect' => true,

        'user_callback_for_url_for_home' => null,
        'user_callback_for_url_for_regist' => null,
        'user_callback_for_url_for_login' => null,
        'user_callback_for_url_for_logout' => null,
    ];
    protected function run_callback_by_key(string $key, ...$args)
    {
        DuckPhpSystemException::ThrowOn(!isset($this->options[$key]), static::class. " need app options '$key'");

        $callback = $this->options[$key];

        if (\is_array($callback) && \is_string($callback[0])) {
            $class = $callback[0];
            $flag = $this->options['user_enable_callback_singleton'] ?? true;
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
        if (isset($this->options['user_callback_for_session'])) {
            $id = $this->getLoginSession()->getCurrentUserId();
            Helper::ControllerThrowOn($check_login && !$id, " NoLogin 1", -1, UserException::class);
            return $id ?? 0;
        }
        if (isset($this->options['user_callback_for_id'])) {
            return $this->run_callback_by_key('user_callback_for_id', $check_login);
        }
        throw new DuckPhpSystemException("No GlobalUser Provider.", -1);
    }
    public function name(bool $check_login = true): string
    {
        if (isset($this->options['user_callback_for_session'])) {
            $name = $this->getLoginSession()->getCurrentUserName();
            Helper::ControllerThrowOn($check_login && !$name, "NoLogin 2", -2, UserException::class);
            return $name;
        }
        if (isset($this->options['user_callback_for_name'])) {
            return $this->run_callback_by_key('user_callback_for_name', $check_login);
        }
        throw new DuckPhpSystemException("No GlobalUser Provider.", -2);
    }
    public function data(bool $check_login = true): array
    {
        return $this->run_callback_by_key('user_callback_for_data', $check_login);
    }
    public function localService()
    {
        return $this->run_callback_by_key('user_callback_for_local_service');
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
        return $this->go_url('user_callback_for_url_for_home', 'user_url_home', $url_back, $ext);
    }
    /**
     * @param array<string, mixed> $ext
     */
    public function urlForRegist(?string $url_back = null, ?array $ext = null): string
    {
        return $this->go_url('user_callback_for_url_for_regist', 'user_url_regist', $url_back, $ext);
    }
    /**
     * @param array<string, mixed> $ext
     */
    public function urlForLogin(?string $url_back = null, ?array $ext = null): string
    {
        return $this->go_url('user_callback_for_url_for_login', 'user_url_login', $url_back, $ext);
    }
    /**
     * @param array<string, mixed> $ext
     */
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
    /**
     * @param array<string, mixed> $input
     * @param array<string, mixed> $input
     * @param array<string, mixed> $input
     * @param array<string, mixed> $input
     */
    public function mergeViewData(array $input): array
    {
        $input = $this->addExtViewData($input);
        $header = '';
        $footer = '';
        if (isset($this->options['user_view_file_header'])) {
            $header = View::_()->_Render($this->options['user_view_file_header'], $input);
        }
        if (isset($this->options['user_view_file_footer'])) {
            $footer = View::_()->_Render($this->options['user_view_file_footer'], $input);
        }
        $input['__view_data']['header'] = $header;
        $input['__view_data']['footer'] = $footer;
        return $input;
    }
    /**
     * @param array<string, mixed> $input
     */
    protected function addExtViewData(array $input): array
    {
        if (isset($this->options['user_callback_for_add_ext_view_data'])) {
            return $this->run_callback_by_key('user_callback_for_add_ext_view_data', $input);
        }
        $input['__logined_id'] ??= $this->id(true);
        $input['__logined_name'] ??= $this->name(true);
        $input['__logined_url_logout'] ??= $this->urlForLogout();
        return $input;
    }
    /**
     * @param array<string, mixed> $data
     */
    public function _Show(array $data = [], string $view = '')
    {
        $last_phase = App::_()->getLastPhase();
        $data = $this->mergeViewData($data);

        $full_header_file = App::_()->getOverrideableFile('view', $this->options['user_view_file_header'], true);
        $full_footer_file = App::_()->getOverrideableFile('view', $this->options['user_view_file_footer'], true);

        $old_phase = App::Phase($last_phase);
        App::_()->onBeforeOutput();
        View::_()->setViewHeadFoot($full_header_file, $full_footer_file);
        $ret = View::_()->_Show($data, $view);
        App::Phase($old_phase);
        return $ret;
    }
    ///////////////
    protected function getLoginBusiness()
    {
        return $this->localService();
    }
    protected function getLoginSession(): UserSessionInterface
    {
        return $this->run_callback_by_key('user_callback_for_login_session');
    }
    public function register(array $post)
    {
        GlobalEvent::_()->fire(self::EVENT_ACTION_USER_REGISTERING, $post);
        $user = $this->getLoginBusiness()->register($post);
        $this->getLoginSession()->setCurrentUser($user);
        GlobalEvent::_()->fire(self::EVENT_ACTION_USER_REGISTERED, $post);

        if ($this->options['user_loginout_auto_redirect']) {
            CoreHelper::Show302($this->urlForHome());
        }
    }

    public function login(array $post)
    {
        GlobalEvent::_()->fire(self::EVENT_ACTION_USER_LOGINING, $post);
        $user = $this->getLoginBusiness()->login($post);
        $this->getLoginSession()->setCurrentUser($user);
        GlobalEvent::_()->fire(self::EVENT_ACTION_USER_LOGINED, $post);

        if ($this->options['user_loginout_auto_redirect']) {
            CoreHelper::Show302($this->urlForHome());
        }
    }
    public function logout()
    {
        $user_id = $this->id(false);
        GlobalEvent::_()->fire(self::EVENT_ACTION_USER_LOGOUTING, $user_id);
        $this->localService()->logout($user_id);
        $this->getLoginSession()->unsetCurrentUser();
        GlobalEvent::_()->fire(self::EVENT_ACTION_USER_LOGOUTED, $user_id);
        if ($this->options['user_loginout_auto_redirect']) {
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
    /**
     * @param array<string, mixed> $ids
     */
    public function batchGetUsernames(array $ids): array
    {
        return $this->localService()->batchGetUsernames($ids);
    }
}
