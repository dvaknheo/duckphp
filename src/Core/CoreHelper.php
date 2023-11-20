<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Core;

use DuckPhp\Core\ComponentBase;
use DuckPhp\Core\Logger;

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
    public static function Json($data)
    {
        return static::_()->_Json($data);
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
    public static function ExitJson($ret, $exit = true)
    {
        return static::_()->_ExitJson($ret, $exit);
    }
    public static function ExitRedirect($url, $exit = true)
    {
        return static::_()->_ExitRedirect($url, $exit);
    }
    public static function ExitRedirectOutside($url, $exit = true)
    {
        return static::_()->_ExitRedirectOutside($url, $exit);
    }
    public static function ExitRouteTo($url, $exit = true)
    {
        return static::_()->_ExitRedirect(static::Url($url), $exit);
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
    public static function ThrowByFlag($exception, $flag, $message, $code = 0)
    {
        return static::_()->_ThrowByFlag($exception, $flag, $message, $code);
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
        if (empty($args)) {
            return $str;
        }
        $a = [];
        foreach ($args as $k => $v) {
            $a["{$k}"] = $v;
        }
        $ret = str_replace(array_keys($a), array_values($a), $str);
        return $ret;
    }
    public function _Hl($str, $args)
    {
        $t = $this->_L($str, $args);
        return $this->_H($t);
    }
    public function _Json($data)
    {
        $flag = JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK;
        if (App::_()->_IsDebug()) {
            $flag = $flag | JSON_PRETTY_PRINT;
        }
        return json_encode($data, $flag);
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
    public static function Exit404($exit = true)
    {
        App::On404();
        if ($exit) {
            SystemWrapper::_()->_exit();
        }
    }
    public function _ExitJson($ret, $exit = true)
    {
        SystemWrapper::_()->_header('Content-Type:application/json; charset=utf-8');
        echo static::_()->_Json($ret);
        if ($exit) {
            SystemWrapper::_()->_exit();
        }
    }
    public function _ExitRedirect($url, $exit = true)
    {
        if (parse_url($url, PHP_URL_HOST)) {
            SystemWrapper::_()->_exit();
            return;
        }
        SystemWrapper::_()->_header('location: '.$url, true, 302);
        if ($exit) {
            SystemWrapper::_()->_exit();
        }
    }
    public function _ExitRedirectOutside($url, $exit = true)
    {
        SystemWrapper::_()->_header('location: '.$url, true, 302);
        if ($exit) {
            SystemWrapper::_()->_exit();
        }
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
        $phase = is_object($phase) ? get_class($phase) : $phase;
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
    public function _ThrowByFlag($exception, $flag, $message, $code = 0)
    {
        if ($flag) {
            throw new $exception($message, $code);
        }
    }
}
