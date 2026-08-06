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
    public static function L($str, $args = [], $fallback = null)
    {
        return static::_()->_L($str, $args, $fallback);
    }
    public static function Hl($str, $args = [])
    {
        return static::_()->_Hl($str, $args);
    }
    public static function LangText($desc, $args = [])
    {
        return static::_()->_LangText($desc, $args);
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
    /**
     * @param array<string, mixed> $context
     */
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
    public function _L($str, $args = [], $fallback = null)
    {
        return App::_()->lang($str, $args, $fallback);
    }
    public function _Hl($str, $args)
    {
        $t = $this->_L($str, $args);
        return $this->_H($t);
    }
    public function _LangText($desc, $args = [])
    {
        return App::_()->langText($desc, $args);
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
    /**
     * @param array<string, mixed> $context
     */
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
        $my_server = defined('__SUPERGLOBAL_CONTEXT') ? (__SUPERGLOBAL_CONTEXT)()->_SERVER : $_SERVER;
        $ref = $my_server['HTTP_X_REQUESTED_WITH'] ?? null;
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
        $old_phase = App::Phase($phase);
        $ret = ($callback)(...$args);
        App::Phase($old_phase);
        return $ret;
    }
    public function _BusinessThrowOn(bool $flag, string $message, int $code = 0, $exception_class = null)
    {
        if (!$flag) {
            return;
        }
        $exception_class = $exception_class ?? (App::_()->options['exception_for_business'] ?? (App::_()->options['exception_for_project'] ?? \Exception::class));

        /** @phpstan-ignore-next-line */
        throw new $exception_class($message, $code);
    }
    public function _ControllerThrowOn(bool $flag, string $message, int $code = 0, $exception_class = null)
    {
        if (!$flag) {
            return;
        }
        $exception_class = $exception_class ?? (App::_()->options['exception_for_controller'] ?? (App::_()->options['exception_for_project'] ?? \Exception::class));

        /** @phpstan-ignore-next-line */
        throw new $exception_class($message, $code);
    }
    public function recursiveApps(&$arg, $callback, $parent_app = null, $auto_switch_phase = true)
    {
        if (!isset($parent_app)) {
            $parent_app = App::Root();
        }
        $last_phase = $parent_app->Phase();

        $callback($parent_app, $arg);

        foreach ($parent_app->options['app'] as $class => $options) {
            if ($options === false) {
                continue;
            }
            $app = $parent_app->getThisChild($class);
            $this->recursiveApps($arg, $callback, $app, $auto_switch_phase);

            App::Phase($last_phase);
        }
    }

    /**
     * @param mixed $class
     * @param mixed $default_method
     */
    public function regCommandClass(string $class, string $default_method = 'command_')
    {
        return App::_()->regConsoleCommand($class, $default_method);
    }
}
