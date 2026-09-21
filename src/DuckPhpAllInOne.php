<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
//dvaknheo@github.com
//OK, Lazy

namespace DuckPhp;

use DuckPhp\Component\Command;

/**
 * All-in-one entry: it also carries the four-layer helper union by forwarding
 * every call to DuckPhp\Foundation\Helper (see that class for the conflict rules).
 */
class DuckPhpAllInOne extends DuckPhp
{
    ////////// Model layer (Foundation\Helper) //////////
    public static function Db($tag = null)
    {
        return \DuckPhp\Foundation\Helper::Db($tag);
    }
    public static function DbForRead()
    {
        return \DuckPhp\Foundation\Helper::DbForRead();
    }
    public static function DbForWrite()
    {
        return \DuckPhp\Foundation\Helper::DbForWrite();
    }
    public static function SqlForPager(string $sql, int $pageNo, int $pageSize = 10): string
    {
        return \DuckPhp\Foundation\Helper::SqlForPager($sql, $pageNo, $pageSize);
    }
    public static function SqlForCountSimply(string $sql): string
    {
        return \DuckPhp\Foundation\Helper::SqlForCountSimply($sql);
    }
    public static function DatabaseDriver(): string
    {
        return \DuckPhp\Foundation\Helper::DatabaseDriver();
    }

    ////////// Business layer (Foundation\Helper) //////////
    public static $EVENT_REGISTERING = 'registering';
    public static $EVENT_REGISTERED = 'registered';
    public static $EVENT_LOGINING = 'logining';
    public static $EVENT_LOGINED = 'logined';
    public static function Setting($key = null, $default = null)
    {
        return \DuckPhp\Foundation\Helper::Setting($key, $default);
    }
    public static function AppOptions(string $key, $default = null)
    {
        return \DuckPhp\Foundation\Helper::AppOptions($key, $default);
    }
    public static function Config($file_basename, $key = null, $default = null)
    {
        return \DuckPhp\Foundation\Helper::Config($file_basename, $key, $default);
    }
    public static function XpCall($callback, ...$args)
    {
        return \DuckPhp\Foundation\Helper::XpCall($callback, ...$args);
    }
    public static function BusinessThrowOn(bool $flag, string $message, int $code = 0, $exception_class = null)
    {
        return \DuckPhp\Foundation\Helper::BusinessThrowOn($flag, $message, $code, $exception_class);
    }
    public static function Cache($object = null)
    {
        return \DuckPhp\Foundation\Helper::Cache($object);
    }
    public static function PathOfProject(): string
    {
        return \DuckPhp\Foundation\Helper::PathOfProject();
    }
    public static function PathOfRuntime(): string
    {
        return \DuckPhp\Foundation\Helper::PathOfRuntime();
    }
    public static function FireGlobalEvent($event, ...$args)
    {
        return \DuckPhp\Foundation\Helper::FireGlobalEvent($event, ...$args);
    }
    public static function OnGlobalEvent($event, $callback)
    {
        return \DuckPhp\Foundation\Helper::OnGlobalEvent($event, $callback);
    }
    public static function Validator($new = null)
    {
        return \DuckPhp\Foundation\Helper::Validator($new);
    }
    public static function ValidatorFilter($data, $rules, $messages = [])
    {
        return \DuckPhp\Foundation\Helper::ValidatorFilter($data, $rules, $messages);
    }
    public static function ValidatorCheck($data, $rules, $messages = [])
    {
        return \DuckPhp\Foundation\Helper::ValidatorCheck($data, $rules, $messages);
    }
    public static function ValidatorValid($data, $rules, $messages = [])
    {
        return \DuckPhp\Foundation\Helper::ValidatorValid($data, $rules, $messages);
    }

    ////////// Controller layer (Foundation\Helper) //////////
    public static $EVENT_ACTION_REGISTERING = 'action_registering';
    public static $EVENT_ACTION_REGISTERED = 'action_registered';
    public static $EVENT_ACTION_LOGINING = 'action_logining';
    public static $EVENT_ACTION_LOGINED = 'action_logined';
    public static $EVENT_ACTION_LOGOUTING = 'action_logouting';
    public static $EVENT_ACTION_LOGOUTED = 'action_logouted';
    public static function getRouteCallingClass(): ?string
    {
        return \DuckPhp\Foundation\Helper::getRouteCallingClass();
    }
    public static function getRouteCallingMethod(): ?string
    {
        return \DuckPhp\Foundation\Helper::getRouteCallingMethod();
    }
    public static function PathInfo(): ?string
    {
        return \DuckPhp\Foundation\Helper::PathInfo();
    }
    public static function Url($url = null)
    {
        return \DuckPhp\Foundation\Helper::Url($url);
    }
    public static function Domain(bool $use_scheme = false): string
    {
        return \DuckPhp\Foundation\Helper::Domain($use_scheme);
    }
    public static function Res($url = null)
    {
        return \DuckPhp\Foundation\Helper::Res($url);
    }
    public static function Parameter($key = null, $default = null)
    {
        return \DuckPhp\Foundation\Helper::Parameter($key, $default);
    }
    public static function Render($view, $data = null)
    {
        return \DuckPhp\Foundation\Helper::Render($view, $data);
    }
    public static function Show($data = [], $view = '')
    {
        return \DuckPhp\Foundation\Helper::Show($data, $view);
    }
    public static function checkInstall(?string $url_install = null)
    {
        return \DuckPhp\Foundation\Helper::checkInstall($url_install);
    }
    public static function setViewHeadFoot($head_file = null, $foot_file = null)
    {
        return \DuckPhp\Foundation\Helper::setViewHeadFoot($head_file, $foot_file);
    }
    public static function assignViewData($key, $value = null)
    {
        return \DuckPhp\Foundation\Helper::assignViewData($key, $value);
    }
    public static function IsAjax()
    {
        return \DuckPhp\Foundation\Helper::IsAjax();
    }
    public static function Show302($url)
    {
        return \DuckPhp\Foundation\Helper::Show302($url);
    }
    public static function Show404()
    {
        return \DuckPhp\Foundation\Helper::Show404();
    }
    public static function ShowJson($ret, $flags = 0)
    {
        return \DuckPhp\Foundation\Helper::ShowJson($ret, $flags);
    }
    public static function header($output, bool $replace = true, int $http_response_code = 0)
    {
        return \DuckPhp\Foundation\Helper::header($output, $replace, $http_response_code);
    }
    public static function setcookie(string $key, string $value = '', int $expire = 0, string $path = '/', string $domain = '', bool $secure = false, bool $httponly = false)
    {
        return \DuckPhp\Foundation\Helper::setcookie($key, $value, $expire, $path, $domain, $secure, $httponly);
    }
    public static function exit($code = 0)
    {
        return \DuckPhp\Foundation\Helper::exit($code);
    }
    public static function assignExceptionHandler($classes, $callback = null)
    {
        return \DuckPhp\Foundation\Helper::assignExceptionHandler($classes, $callback);
    }
    public static function setMultiExceptionHandler(array $classes, $callback)
    {
        return \DuckPhp\Foundation\Helper::setMultiExceptionHandler($classes, $callback);
    }
    public static function setDefaultExceptionHandler($callback)
    {
        return \DuckPhp\Foundation\Helper::setDefaultExceptionHandler($callback);
    }
    public static function ControllerThrowOn(bool $flag, string $message, int $code = 0, $exception_class = null)
    {
        return \DuckPhp\Foundation\Helper::ControllerThrowOn($flag, $message, $code, $exception_class);
    }
    public static function IsPost()
    {
        return \DuckPhp\Foundation\Helper::IsPost();
    }
    public static function GET($key = null, $default = null)
    {
        return \DuckPhp\Foundation\Helper::GET($key, $default);
    }
    public static function POST($key = null, $default = null)
    {
        return \DuckPhp\Foundation\Helper::POST($key, $default);
    }
    public static function REQUEST($key = null, $default = null)
    {
        return \DuckPhp\Foundation\Helper::REQUEST($key, $default);
    }
    public static function COOKIE($key = null, $default = null)
    {
        return \DuckPhp\Foundation\Helper::COOKIE($key, $default);
    }
    public static function SERVER($key = null, $default = null)
    {
        return \DuckPhp\Foundation\Helper::SERVER($key, $default);
    }
    public static function Pager($new = null)
    {
        return \DuckPhp\Foundation\Helper::Pager($new);
    }
    public static function PageNo($new_value = null)
    {
        return \DuckPhp\Foundation\Helper::PageNo($new_value);
    }
    public static function PageWindow($new_value = null)
    {
        return \DuckPhp\Foundation\Helper::PageWindow($new_value);
    }
    public static function PageHtml($total, $options = [])
    {
        return \DuckPhp\Foundation\Helper::PageHtml($total, $options);
    }
    public static function Admin()
    {
        return \DuckPhp\Foundation\Helper::Admin();
    }
    public static function AdminId(bool $check_login = true)
    {
        return \DuckPhp\Foundation\Helper::AdminId($check_login);
    }
    public static function AdminName(bool $check_login = true)
    {
        return \DuckPhp\Foundation\Helper::AdminName($check_login);
    }
    public static function AdminService()
    {
        return \DuckPhp\Foundation\Helper::AdminService();
    }
    public static function User()
    {
        return \DuckPhp\Foundation\Helper::User();
    }
    public static function UserId(bool $check_login = true)
    {
        return \DuckPhp\Foundation\Helper::UserId($check_login);
    }
    public static function UserName(bool $check_login = true)
    {
        return \DuckPhp\Foundation\Helper::UserName($check_login);
    }
    public static function UserService()
    {
        return \DuckPhp\Foundation\Helper::UserService();
    }

    ////////// System layer (Foundation\Helper) //////////
    public static function CallException(\Throwable $ex)
    {
        return \DuckPhp\Foundation\Helper::CallException($ex);
    }
    public static function RemoveEvent($event, $callback = null)
    {
        return \DuckPhp\Foundation\Helper::RemoveEvent($event, $callback);
    }
    public static function isRunning(): bool
    {
        return \DuckPhp\Foundation\Helper::isRunning();
    }
    public static function isInException(): bool
    {
        return \DuckPhp\Foundation\Helper::isInException();
    }
    public static function addRouteHook($callback, $position = 'append-outter', $once = true)
    {
        return \DuckPhp\Foundation\Helper::addRouteHook($callback, $position, $once);
    }
    public static function replaceController(string $old_class, string $new_class)
    {
        return \DuckPhp\Foundation\Helper::replaceController($old_class, $new_class);
    }
    public static function getViewData(): array
    {
        return \DuckPhp\Foundation\Helper::getViewData();
    }
    public static function DbCloseAll()
    {
        return \DuckPhp\Foundation\Helper::DbCloseAll();
    }
    public static function SESSION($key = null, $default = null)
    {
        return \DuckPhp\Foundation\Helper::SESSION($key, $default);
    }
    public static function FILES($key = null, $default = null)
    {
        return \DuckPhp\Foundation\Helper::FILES($key, $default);
    }
    public static function SessionSet($key, $value)
    {
        return \DuckPhp\Foundation\Helper::SessionSet($key, $value);
    }
    public static function SessionUnset($key)
    {
        return \DuckPhp\Foundation\Helper::SessionUnset($key);
    }
    public static function SessionGet($key, $default = null)
    {
        return \DuckPhp\Foundation\Helper::SessionGet($key, $default);
    }
    public static function CookieSet($key, $value, $expire = 0)
    {
        return \DuckPhp\Foundation\Helper::CookieSet($key, $value, $expire);
    }
    public static function CookieGet($key, $default = null)
    {
        return \DuckPhp\Foundation\Helper::CookieGet($key, $default);
    }
    public static function system_wrapper_replace(array $funcs)
    {
        return \DuckPhp\Foundation\Helper::system_wrapper_replace($funcs);
    }
    public static function system_wrapper_get_providers(): array
    {
        return \DuckPhp\Foundation\Helper::system_wrapper_get_providers();
    }
    public static function set_exception_handler(callable $exception_handler)
    {
        return \DuckPhp\Foundation\Helper::set_exception_handler($exception_handler);
    }
    public static function register_shutdown_function(callable $callback, ...$args)
    {
        return \DuckPhp\Foundation\Helper::register_shutdown_function($callback, ...$args);
    }
    public static function session_start(array $options = [])
    {
        return \DuckPhp\Foundation\Helper::session_start($options);
    }
    public static function session_id($session_id = null)
    {
        return \DuckPhp\Foundation\Helper::session_id($session_id);
    }
    public static function session_destroy()
    {
        return \DuckPhp\Foundation\Helper::session_destroy();
    }
    public static function session_set_save_handler(\SessionHandlerInterface $handler)
    {
        return \DuckPhp\Foundation\Helper::session_set_save_handler($handler);
    }
    public static function mime_content_type($file)
    {
        return \DuckPhp\Foundation\Helper::mime_content_type($file);
    }
    public static function setBeforeGetDbHandler($db_before_get_object_handler)
    {
        return \DuckPhp\Foundation\Helper::setBeforeGetDbHandler($db_before_get_object_handler);
    }
    public static function Redis($tag = 0)
    {
        return \DuckPhp\Foundation\Helper::Redis($tag);
    }
    public static function getRouteMaps()
    {
        return \DuckPhp\Foundation\Helper::getRouteMaps();
    }
    public static function assignRoute($key, $value = null)
    {
        return \DuckPhp\Foundation\Helper::assignRoute($key, $value);
    }
    public static function assignImportantRoute($key, $value = null)
    {
        return \DuckPhp\Foundation\Helper::assignImportantRoute($key, $value);
    }
    public static function assignRewrite($key, $value = null)
    {
        return \DuckPhp\Foundation\Helper::assignRewrite($key, $value);
    }
    public static function getRewrites()
    {
        return \DuckPhp\Foundation\Helper::getRewrites();
    }
    public static function getCliParameters()
    {
        return \DuckPhp\Foundation\Helper::getCliParameters();
    }
    public static function saveExtOptions(array $options): void
    {
        \DuckPhp\Foundation\Helper::saveExtOptions($options);
    }
    public static function ProjectThrowOn(bool $flag, string $message, int $code = 0, $exception_class = null)
    {
        return \DuckPhp\Foundation\Helper::ProjectThrowOn($flag, $message, $code, $exception_class);
    }
    public static function ThrowOn(bool $flag, string $message, int $code = 0, $exception_class = null)
    {
        return \DuckPhp\Foundation\Helper::ThrowOn($flag, $message, $code, $exception_class);
    }

    protected $head_view = 'head';
    protected $foot_view = 'foot';
    protected function embedMe(): void
    {
        // embed welcome page to this class
        $path = explode('\\', static::class);
        $short_class = array_pop($path);
        $namespace = implode("\\", $path);
        $ext_options = [
            'namespace_controller' => "\\".$namespace,
            'name' => '@',
            'controller_welcome_class' => $short_class ,
            'controller_class_postfix' => '',
            'controller_method_prefix' => 'action_',
            'cli_enable' => true,
            'path_info_compact_enable' => true,
            'duckphp_all_in_one_wrap_header_foot' => true,
        ];

        $this->options = array_merge($this->options, $ext_options);
    }
    public function __construct()
    {
        $this->embedMe();
        parent::__construct();
    }
    protected function onPrepare(): void
    {
        parent::onPrepare();
        // implements cli_command_with_app=true effect (without depending on the option)
        $this->options['cmd'] = array_merge([static::class => true], $this->options['cmd']);
        if ($this->options['cli_command_with_common']) {
            $this->options['cmd'][Command::class] = true;
        }
    }
    public function onInited(): void
    {
        if ($this->options['duckphp_all_in_one_wrap_header_foot']) {
            $this->head_view = 'head';
            $this->foot_view = 'foot';
        }
    }
    /////////////// controller ///////////////
    public function action_index()
    {
        $this->_Show(get_defined_vars(), 'index');
    }
    /////////////// callable view (was DuckPhp\Ext\CallableView) ///////////////
    protected function viewToCallback(?string $func): ?\Closure
    {
        $func = str_replace('/', '_', 'view_' . $func);
        $ret = [$this, $func];
        if (!is_callable($ret)) {
            return null;
        }
        return \Closure::fromCallable($ret);
    }
    public function _Show(array $data, string $view = '')
    {
        $callback = $this->viewToCallback($view);
        if (null === $callback) {
            return parent::_Show($data, $view);
        }
        $head = $this->viewToCallback($this->head_view ?: 'head');
        $foot = $this->viewToCallback($this->foot_view ?: 'foot');
        if (null !== $head) {
            ($head)($data);
        }
        ($callback)($data);
        if (null !== $foot) {
            ($foot)($data);
        }
    }
    ///////////////
    public function view_head($data)
    {
        echo <<<EOT
<html><head><meta charset="UTF-8"><title>demo</title></head><body>
EOT;
    }
    public function view_index($data)
    {
        echo  static::class. " main page work at".DATE(DATE_ATOM);
    }
    public function view_foot($data)
    {
        echo <<<EOT
</body></html>
EOT;
    }
}
