<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Core;

use DuckPhp\Core\App;
use DuckPhp\Core\ComponentBase;
use DuckPhp\Core\Logger;
use DuckPhp\Core\SystemWrapper;

class CoreHelper extends ComponentBase
{
    public static function H($str)
    {
        return static::_()->_H($str);
    }
    public static function L($str, $args = [])
    {
        return static::_()->_L($str, $args);
    }
    public static function Hl($str, $args = [])
    {
        return static::_()->_Hl($str, $args);
    }
    public static function Json($data, $flags = 0)
    {
        return static::_()->_Json($data, $flags);
    }
    public static function Url($url = null)
    {
        return Route::_()->_Url($url);
    }
    public static function Domain($use_scheme = false)
    {
        return Route::_()->_Domain($use_scheme);
    }
    public static function Res($url = null)
    {
        return Route::_()->_Res($url);
    }
    public static function Display($view, $data = null)
    {
        return View::_()->_Display($view, $data);
    }
    public static function var_dump(...$args)
    {
        return static::_()->_var_dump(...$args);
    }
    public static function VarLog($var)
    {
        return static::_()->_VarLog($var);
    }
    public static function TraceDump()
    {
        return static::_()->_TraceDump();
    }
    public static function DebugLog($message, array $context = array())
    {
        return static::_()->_DebugLog($message, $context);
    }
    public static function Logger($object = null)
    {
        return Logger::_($object);
    }
    public static function IsDebug()
    {
        return static::_()->_IsDebug();
    }
    public static function IsRealDebug()
    {
        return static::_()->_IsRealDebug();
    }
    public static function Platform()
    {
        return static::_()->_Platform();
    }
    //////////////////////
    public static function IsAjax()
    {
        return static::_()->_IsAjax();
    }
    public static function ShowJson($ret, $flags = 0)
    {
        return static::_()->_ShowJson($ret, $flags);
    }
    public static function Show302($url)
    {
        return static::_()->_Show302($url);
    }
    public static function SqlForPager($sql, $page_no, $page_size = 10)
    {
        return static::_()->_SqlForPager($sql, $page_no, $page_size);
    }
    public static function SqlForCountSimply($sql)
    {
        return static::_()->_SqlForCountSimply($sql);
    }
    public static function XpCall($callback, ...$args)
    {
        return static::_()->_XpCall($callback, ...$args);
    }
    public static function PhaseCall($phase, $callback, ...$args)
    {
        return static::_()->_PhaseCall($phase, $callback, ...$args);
    }
    public static function BusinessThrowOn(bool $flag, string $message, int $code = 0, $exception_class = null)
    {
        return static::_()->_BusinessThrowOn($flag, $message, $code, $exception_class);
    }
    public static function ControllerThrowOn(bool $flag, string $message, int $code = 0, $exception_class = null)
    {
        return static::_()->_ControllerThrowOn($flag, $message, $code, $exception_class);
    }
    public static function PathOfProject()
    {
        return static::_()->_PathOfProject();
    }
    public static function PathOfRuntime()
    {
        return static::_()->_PathOfRuntime();
    }
    ////////////////////////////////////////////
    public function _H(&$str)
    {
        $handler = App::_()->options['html_handler'] ?? null;
        if ($handler) {
            return $handler($str);
        }
        if (is_string($str)) {
            $str = htmlspecialchars($str, ENT_QUOTES);
            return $str;
        }
        if (is_array($str)) {
            foreach ($str as $k => &$v) {
                static::_H($v);
            }
            return $str;
        }
        return $str;
    }
    public function _L($str, $args = [])
    {
        $handler = App::_()->options['lang_handler'] ?? null;
        if ($handler) {
            return $handler($str, $args);
        }
        //Override for locale and so do
        return $this->formatString($str, $args);
    }
    public function formatString($str, $args)
    {
        if (empty($args)) {
            return $str;
        }
        $a = [];
        foreach ($args as $k => $v) {
            $a["{".$k."}"] = $v;
        }
        $ret = str_replace(array_keys($a), array_values($a), $str);
        return $ret;
    }
    public function _Hl($str, $args)
    {
        $t = $this->_L($str, $args);
        return $this->_H($t);
    }
    public function _Json($data, $flags = 0)
    {
        $flags = $flags | JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK;
        if (App::_()->_IsDebug()) {
            $flags = $flags | JSON_PRETTY_PRINT;
        }
        return json_encode($data, $flags);
    }
    public function _VarLog($var)
    {
        if (!App::_()->_IsDebug()) {
            return;
        }
        return Logger::_()->debug(var_export($var, true));
    }
    public function _var_dump(...$args)
    {
        if (!App::_()->_IsDebug()) {
            return;
        }
        echo "<pre>\n";
        var_dump(...$args);
        echo "</pre>\n";
    }
    public function _TraceDump()
    {
        if (!App::_()->_IsDebug()) {
            return;
        }
        echo "<pre>\n";
        echo (new \Exception('', 0))->getTraceAsString();
        echo "</pre>\n";
    }
    public function _DebugLog($message, array $context = array())
    {
        if (!App::_()->_IsDebug()) {
            return false;
        }
        return Logger::_()->debug($message, $context);
    }
    public function _IsDebug()
    {
        return App::_()->_IsDebug();
    }
    public function _IsRealDebug()
    {
        return App::_()->_IsRealDebug();
    }
    public function _Platform()
    {
        return App::_()->_Platform();
    }
    ////////////////////////////////////////////
    public function _IsAjax()
    {
        $_SERVER = defined('__SUPERGLOBAL_CONTEXT') ? (__SUPERGLOBAL_CONTEXT)()->_SERVER : $_SERVER;
        $ref = $_SERVER['HTTP_X_REQUESTED_WITH'] ?? null;
        return $ref && 'xmlhttprequest' == strtolower($ref) ? true : false;
    }
    public static function Show404()
    {
        App::On404();
    }
    public function _ShowJson($ret, $flags = 0)
    {
        SystemWrapper::_()->_header('Content-Type:application/json; charset=utf-8');
        SystemWrapper::_()->_header('Cache-Control: no-store, no-cache, must-revalidate');
        echo static::_()->_Json($ret, $flags);
    }
    public function _Show302($url)
    {
        if (parse_url($url, PHP_URL_HOST)) {
            return;
        }
        SystemWrapper::_()->_header('location: '.static::Url($url), true, 302);
    }
    ////////////////////////////////////////////
    public function _XpCall($callback, ...$args)
    {
        try {
            return ($callback)(...$args);
        } catch (\Exception $ex) {
            return $ex;
        }
    }
    public function _PhaseCall($phase, $callback, ...$args)
    {
        //$phase = is_object($phase) ? $phase->getOverridingClass() : $phase;
        $current = App::Phase();
        if (!$phase || !$current) {
            return ($callback)(...$args);
        }
        
        App::Phase($phase);
        $ret = ($callback)(...$args);
        App::Phase($current);
        return $ret;
    }
    public function _SqlForPager($sql, $page_no, $page_size = 10)
    {
        $page_size = (int)$page_size;
        $start = ((int)$page_no - 1) * $page_size;
        $start = (int)$start;
        $sql .= " LIMIT $start,$page_size";
        return $sql;
    }
    public function _SqlForCountSimply($sql)
    {
        $sql = preg_replace_callback('/^\s*select\s(.*?)\sfrom\s/is', function ($m) {
            return 'SELECT COUNT(*) as c FROM ';
        }, $sql);
        return $sql;
    }
    public function _BusinessThrowOn(bool $flag, string $message, int $code = 0, $exception_class = null)
    {
        if (!$flag) {
            return;
        }
        $exception_class = $exception_class ?? (App::Current()->options['exception_for_business'] ?? (App::Current()->options['exception_for_project'] ?? \Exception::class));
        
        throw new $exception_class($message, $code);
    }
    public function _ControllerThrowOn(bool $flag, string $message, int $code = 0, $exception_class = null)
    {
        if (!$flag) {
            return;
        }
        $exception_class = $exception_class ?? (App::Current()->options['exception_for_controller'] ?? (App::Current()->options['exception_for_project'] ?? \Exception::class));
        
        throw new $exception_class($message, $code);
    }
    public function _PathOfProject()
    {
        return App::Root()->options['path'];
    }
    public function _PathOfRuntime()
    {
        $path = static::SlashDir(App::Root()->options['path']);
        $path_runtime = static::SlashDir(App::Root()->options['path_runtime']);
        return static::IsAbsPath($path_runtime) ? $path_runtime : $path.$path_runtime;
    }
    public function recursiveApps(&$arg, $callback, ?string $app_class = null, $auto_switch_phase = true)
    {
        if (!isset($app_class)) {
            $app_class = App::Root()->getOverridingClass();
        }
        $callback($app_class, $arg);
        $object = $app_class::_();
        foreach ($object->options['app'] as $app => $options) {
            if ($auto_switch_phase) {
                $last_phase = App::Phase($app);
                $this->recursiveApps($arg, $callback, $app, $auto_switch_phase);
                App::Phase($last_phase);
            } else {
                $this->recursiveApps($arg, $callback, $app, $auto_switch_phase);
            }
        }
    }
    public function getAllAppClass()
    {
        $ret = [];
        $this->recursiveApps(
            $ret,
            function ($app_class, &$ret) {
                $ret[$app_class::_()->getOverridingClass()] = $app_class;
            }
        );
        return $ret;
    }
    public function getAppClassByComponent(string $class)
    {
        $all_class = $this->getAllAppClass();
        foreach ($all_class as $phase => $app_name) {
            $namespace = $app_name::_()->options['namespace'];
            $prefix = $namespace .'\\';
            if (substr($class, 0, strlen($prefix)) === $prefix) {
                return $app_name;
            }
        }
        return App::Root()->getOverridingClass();
    }
    public function regExtCommandClass(string $class)
    {
        $prefix = App::Current()->options['cli_command_prefix'] ?? App::Phase();
        $prefix = App::IsRoot()?'':$prefix;
        $classes = App::Current()->options['cli_command_classes'];
        $classes[] = $class;
        Console::_()->regCommandClass($prefix, App::Phase(), $classes);
    }
}
