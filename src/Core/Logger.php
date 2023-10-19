<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Core;

use DuckPhp\Core\ComponentBase;

class Logger extends ComponentBase //implements Psr\Log\LoggerInterface;
{
    const EMERGENCY = 'emergency';
    const ALERT = 'alert';
    const CRITICAL = 'critical';
    const ERROR = 'error';
    const WARNING = 'warning';
    const NOTICE = 'notice';
    const INFO = 'info';
    const DEBUG = 'debug';

    public $options = [
        'path' => '',
        'path_log' => 'runtime',
        'log_file_template' => 'log_%Y-%m-%d_%H_%i.log',
        'log_prefix' => 'DuckPhpLog',
    ];
    protected $init_once = true;
    public function log($level, $message, array $context = array())
    {
        //if (!$this->is_inited) {
        //    $this->init([], null);
        //}
        $file = preg_replace_callback('/%(.)/', function ($m) {
            return date($m[1]);
        }, $this->options['log_file_template']);
        
        if (static::IsAbsPath($file)) {
            $full_file = $file;
        } elseif (static::IsAbsPath($this->options['path_log'])) {
            $full_file = static::SlashDir($this->options['path_log']) . $file;
        } else {
            $full_file = static::SlashDir($this->options['path']) . static::SlashDir($this->options['path_log']) . $file;
        }
        $full_file = '' . $full_file;
        
        $prefix = $this->options['log_prefix'];
        
        $a = [];
        foreach ($context as $k => $v) {
            $a["{$k}"] = var_export($v, true);
        }
        $message = str_replace(array_keys($a), array_values($a), $message);
        $date = date('Y-m-d H:i:s');
        $message = ($_SERVER['PATH_INFO'] ?? '') .' : '.$message;
        $message = "[{$level}][{$prefix}][$date]: ".$message."\n";
        
        try {
            $type = $full_file ? 3:0;
            $ret = error_log($message, $type, $full_file);
        } catch (\Throwable $ex) { // @codeCoverageIgnore
            return false;  // @codeCoverageIgnore
        }  // @codeCoverageIgnore
        return $ret; // @codeCoverageIgnore
    }
    ////////////////////
    
    public function emergency($message, array $context = array())
    {
        $this->log(static::EMERGENCY, $message, $context);
    }
    public function alert($message, array $context = array())
    {
        $this->log(static::ALERT, $message, $context);
    }
    public function critical($message, array $context = array())
    {
        $this->log(static::CRITICAL, $message, $context);
    }
    public function error($message, array $context = array())
    {
        $this->log(static::ERROR, $message, $context);
    }
    public function warning($message, array $context = array())
    {
        $this->log(static::WARNING, $message, $context);
    }
    public function notice($message, array $context = array())
    {
        $this->log(static::NOTICE, $message, $context);
    }
    public function info($message, array $context = array())
    {
        $this->log(static::INFO, $message, $context);
    }
    public function debug($message, array $context = array())
    {
        $this->log(static::DEBUG, $message, $context);
    }
}
