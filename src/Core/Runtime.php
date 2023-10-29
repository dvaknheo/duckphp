<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Core;

use DuckPhp\Core\ComponentBase;
use DuckPhp\Core\Logger;

class Runtime extends ComponentBase
{
    public $options = [
        'use_output_buffer' => false,
        'path_runtime' => 'runtime',
    ];
    public $context_class;
    
    protected $is_running = false;
    protected $is_in_exception = false;
    protected $is_outputed = false;
    
    public $last_phase;
    protected $init_ob_level = 0;

    public function isRunning()
    {
        return $this->is_running;
    }
    public function isInException()
    {
        return $this->is_in_exception;
    }
    public function isOutputed()
    {
        return $this->is_outputed;
    }
    public function run()
    {
        if ($this->options['use_output_buffer']) {
            $this->init_ob_level = ob_get_level();
            ob_implicit_flush(0);
            ob_start();
        }
        $this->is_running = true;
    }

    public function clear()
    {
        if ($this->options['use_output_buffer']) {
            for ($i = ob_get_level();$i > $this->init_ob_level;$i--) {
                ob_end_flush();
            }
        }
        $this->is_in_exception = false;
        $this->is_running = false;
        $this->is_outputed = true;
    }
    public function onException($skip_exception_check)
    {
        if ($skip_exception_check) {
            $this->clear();
        }
        $this->is_in_exception = true;
        ;
    }
    //////////////////////
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
        return $this->context()->_IsDebug();
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
