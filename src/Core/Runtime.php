<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Core;

use DuckPhp\Core\ComponentBase;

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
//    protected $pager;

    public function _IsAjax()
    {
        $ref = $this->_SERVER('HTTP_X_REQUESTED_WITH');
        return $ref && 'xmlhttprequest' == strtolower($ref) ? true : false;
    }



    ////
    public function _ExitJson($ret, $exit = true)
    {
        $this->_header('Content-Type:application/json; charset=utf-8');
        echo $this->_Json($ret);
        if ($exit) {
            static::exit();
        }
    }
    public function _ExitRedirect($url, $exit = true)
    {
        if (parse_url($url, PHP_URL_HOST)) {
            static::exit();
            return;
        }
        static::header('location: '.$url, true, 302);
        if ($exit) {
            static::exit();
        }
    }
    public function _ExitRedirectOutside($url, $exit = true)
    {
        static::header('location: '.$url, true, 302);
        if ($exit) {
            static::exit();
        }
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
        if ($this->_IsDebug()) {
            $flag = $flag | JSON_PRETTY_PRINT;
        }
        return json_encode($data, $flag);
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


    public static function var_dump(...$args)
    {
        return static::G()->_var_dump(...$args);
    }
    public function _TraceDump()
    {
        if (!$this->options['is_debug']) {
            return;
        }
        echo "<pre>\n";
        echo (new \Exception('', 0))->getTraceAsString();
        echo "</pre>\n";
    }
    public function _VarLog($var)
    {
        return Logger::G()->debug(var_export($var, true));
    }
    public function _var_dump(...$args)
    {
        if (!$this->options['is_debug']) {
            return;
        }
        echo "<pre>\n";
        var_dump(...$args);
        echo "</pre>\n";
    }

    public static function CheckException($exception_class, $message, $code = 0)
    {
        return static::G()->_CheckException($exception_class, $message, $code);
    }
    public function _CheckException($exception_class, $flag, $message, $code = 0)
    {
        if ($flag) {
            throw new $exception_class($message, $code);
        }
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
    
    public static function Logger($object = null)
    {
        return Logger::G($object);
    }
    public static function DebugLog($message, array $context = array())
    {
        return static::G()->_DebugLog($message, $context);
    }
    public function _DebugLog($message, array $context = array())
    {
        if ($this->options['is_debug']) {
            return Logger::G()->debug($message, $context);
        }
        return false;
    }
    
    
}
