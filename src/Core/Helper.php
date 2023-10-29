<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Core;

use DuckPhp\Core\ComponentBase;
use DuckPhp\Core\Logger;

class Helper extends ComponentBase
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
    public static function var_dump(...$args)
    {
        return static::_()->_var_dump(...$args);
    }
    public static function TraceDump()
    {
        return static::_()->_TraceDump();
    }
    public static function VarLog($var)
    {
        return static::_()->_VarLog($var);
    }

    public static function Logger($object = null)
    {
        return Logger::G($object);
    }
    public static function DebugLog($message, array $context = array())
    {
        return static::_()->_DebugLog($message, $context);
    }
    
    public function _H(&$str)
    {
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
        if (App::IsDebug()) {
            $flag = $flag | JSON_PRETTY_PRINT;
        }
        return json_encode($data, $flag);
    }
    public function _IsAjax()
    {
        $_SERVER = defined('__SUPERGLOBAL_CONTEXT') ? (__SUPERGLOBAL_CONTEXT)()->_SERVER : $_SERVER;
        $ref = $_SERVER['HTTP_X_REQUESTED_WITH'] ?? null;
        return $ref && 'xmlhttprequest' == strtolower($ref) ? true : false;
    }
    ///////////////
    protected function is_debug()
    {
        return App::_()->_IsDebug();
    }
    public function _TraceDump()
    {
        if (!$this->is_debug()) {
            return;
        }
        echo "<pre>\n";
        echo (new \Exception('', 0))->getTraceAsString();
        echo "</pre>\n";
    }
    public function _VarLog($var)
    {
        if (!$this->is_debug()) {
            return;
        }
        return Logger::_()->debug(var_export($var, true));
    }
    public function _var_dump(...$args)
    {
        if (!$this->is_debug()) {
            return;
        }
        echo "<pre>\n";
        var_dump(...$args);
        echo "</pre>\n";
    }
    public function _DebugLog($message, array $context = array())
    {
        if (!$this->is_debug()) {
            return false;
        }
        return Logger::_()->debug($message, $context);
    }
    ////
    public function _SqlForPager($sql, $pageNo, $pageSize = 10)
    {
        $pageSize = (int)$pageSize;
        $start = ((int)$pageNo - 1) * $pageSize;
        $start = (int)$start;
        $sql .= " LIMIT $start,$pageSize";
        return $sql;
    }
    public function _SqlForCountSimply($sql)
    {
        $sql = preg_replace_callback('/^\s*select\s(.*?)\sfrom\s/is', function ($m) {
            return 'SELECT COUNT(*) as c FROM ';
        }, $sql);
        return $sql;
    }
    public static function XpCall($callback, ...$args)
    {
        return static::G()->_XpCall($callback, ...$args);
    }
    public function _XpCall($callback, ...$args)
    {
        try {
            return ($callback)(...$args);
        } catch (\Exception $ex) {
            return $ex;
        }
    }
}
