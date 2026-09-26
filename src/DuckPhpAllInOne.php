<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
//dvaknheo@github.com
//OK, Lazy

namespace DuckPhp;

use DuckPhp\Component\Command;
use DuckPhp\Foundation\Helper;

/**
 * DuckPhp
 *
 * All-in-one entry: it embeds the four-layer helper union exactly like
 * DuckPhp\Foundation\Helper does (__callStatic dispatch, same lookup order:
 * System -> Controller -> Business -> Model). The @method tags below exist for
 * IDE / static-analysis visibility only.
 *
 * ---- resolved from Foundation\System\SystemHelper (40) ----
 * @method static mixed CallException(\Throwable $ex)
 * @method static mixed RemoveEvent($event, $callback = null)
 * @method static bool isRunning()
 * @method static bool isInException()
 * @method static mixed addRouteHook($callback, $position = 'append-outter', $once = true)
 * @method static mixed replaceController(string $old_class, string $new_class)
 * @method static array getViewData()
 * @method static mixed DbCloseAll()
 * @method static mixed SESSION($key = null, $default = null)
 * @method static mixed FILES($key = null, $default = null)
 * @method static mixed SessionSet($key, $value)
 * @method static mixed SessionUnset($key)
 * @method static mixed SessionGet($key, $default = null)
 * @method static mixed CookieSet($key, $value, $expire = 0)
 * @method static mixed CookieGet($key, $default = null)
 * @method static mixed system_wrapper_replace(array $funcs)
 * @method static array system_wrapper_get_providers()
 * @method static mixed header($output, bool $replace = true, int $http_response_code = 0)
 * @method static mixed setcookie(string $key, string $value = '', int $expire = 0, string $path = '/', string $domain = '', bool $secure = false, bool $httponly = false)
 * @method static mixed exit($code = 0)
 * @method static mixed set_exception_handler(callable $exception_handler)
 * @method static mixed register_shutdown_function(callable $callback, ...$args)
 * @method static mixed session_start(array $options = [])
 * @method static mixed session_id($session_id = null)
 * @method static mixed session_destroy()
 * @method static mixed session_set_save_handler(\SessionHandlerInterface $handler)
 * @method static mixed mime_content_type($file)
 * @method static mixed setBeforeGetDbHandler($db_before_get_object_handler)
 * @method static mixed Redis($tag = 0)
 * @method static mixed getRouteMaps()
 * @method static mixed assignRoute($key, $value = null)
 * @method static mixed assignImportantRoute($key, $value = null)
 * @method static mixed assignRewrite($key, $value = null)
 * @method static mixed getRewrites()
 * @method static mixed getCliParameters()
 * @method static mixed FireGlobalEvent($event, ...$args)
 * @method static mixed OnGlobalEvent($event, $callback)
 * @method static void saveExtOptions(array $options)
 * @method static mixed ProjectThrowOn(bool $flag, string $message, int $code = 0, $exception_class = null)
 * @method static mixed ThrowOn(bool $flag, string $message, int $code = 0, $exception_class = null)
 *
 * ---- resolved from Foundation\Controller\ControllerHelper (42) ----
 * @method static mixed Setting($key = null, $default = null)
 * @method static mixed AppOptions(string $key, $default = null)
 * @method static mixed XpCall($callback, ...$args)
 * @method static mixed Config($file_basename, $key = null, $default = null)
 * @method static ?string getRouteCallingClass()
 * @method static ?string getRouteCallingMethod()
 * @method static ?string PathInfo()
 * @method static mixed Url($url = null)
 * @method static string Domain(bool $use_scheme = false)
 * @method static mixed Res($url = null)
 * @method static mixed Parameter($key = null, $default = null)
 * @method static mixed Render($view, $data = null)
 * @method static mixed Show($data = [], $view = '')
 * @method static mixed checkInstall(?string $url_install = null)
 * @method static mixed setViewHeaderFooter($head_file = null, $foot_file = null)
 * @method static mixed assignViewData($key, $value = null)
 * @method static mixed IsAjax()
 * @method static mixed Show302($url)
 * @method static mixed Show404()
 * @method static mixed ShowJson($ret, $flags = 0)
 * @method static mixed assignExceptionHandler($classes, $callback = null)
 * @method static mixed setMultiExceptionHandler(array $classes, $callback)
 * @method static mixed setDefaultExceptionHandler($callback)
 * @method static mixed ControllerThrowOn(bool $flag, string $message, int $code = 0, $exception_class = null)
 * @method static mixed IsPost()
 * @method static mixed GET($key = null, $default = null)
 * @method static mixed POST($key = null, $default = null)
 * @method static mixed REQUEST($key = null, $default = null)
 * @method static mixed COOKIE($key = null, $default = null)
 * @method static mixed SERVER($key = null, $default = null)
 * @method static mixed Pager($new = null)
 * @method static mixed PageNo($new_value = null)
 * @method static mixed PageWindow($new_value = null)
 * @method static mixed PageHtml($total, $options = [])
 * @method static mixed Admin()
 * @method static mixed AdminId(bool $check_login = true)
 * @method static mixed AdminName(bool $check_login = true)
 * @method static mixed AdminService()
 * @method static mixed User()
 * @method static mixed UserId(bool $check_login = true)
 * @method static mixed UserName(bool $check_login = true)
 * @method static mixed UserService()
 *
 * ---- resolved from Foundation\Business\BusinessHelper (8) ----
 * @method static mixed BusinessThrowOn(bool $flag, string $message, int $code = 0, $exception_class = null)
 * @method static mixed Cache($object = null)
 * @method static string PathOfProject()
 * @method static string PathOfRuntime()
 * @method static mixed Validator($new = null)
 * @method static mixed ValidatorFilter($data, $rules, $messages = [])
 * @method static mixed ValidatorCheck($data, $rules, $messages = [])
 * @method static mixed ValidatorValid($data, $rules, $messages = [])
 *
 * ---- resolved from Foundation\Model\ModelHelper (6) ----
 * @method static mixed Db($tag = null)
 * @method static mixed DbForRead()
 * @method static mixed DbForWrite()
 * @method static string SqlForPager(string $sql, int $pageNo, int $pageSize = 10)
 * @method static string SqlForCountSimply(string $sql)
 * @method static string DatabaseDriver()
 */
class DuckPhpAllInOne extends DuckPhp
{
    public static function __callStatic($method, $args)
    {
        $classes = [
            \DuckPhp\Foundation\System\SystemHelper::class,
            \DuckPhp\Foundation\Controller\ControllerHelper::class,
            \DuckPhp\Foundation\Business\BusinessHelper::class,
            \DuckPhp\Foundation\Model\ModelHelper::class,
        ];
        foreach ($classes as $class) {
            if (method_exists($class, $method)) {
                return $class::$method(...$args);
            }
        }
        trigger_error("Call to undefined method " . static::class . "::$method()", E_USER_ERROR);
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
