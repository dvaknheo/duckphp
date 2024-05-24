<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Ext;

use DuckPhp\Core\Console;

class AutoReadLineConsole extends Console
{
    public $index = 0;
    public $datas = [];
    public $is_logging = false;
    public $log = '';
    public function fill($datas)
    {
        $datas = is_array($datas)?$datas:[$datas];
        $this->datas += $datas;
    }
    public function cleanFill()
    {
        $this->datas = [];
        $this->index = 0;
    }
    public function toggleLog($flag = true)
    {
        $this->is_logging = $flag;
    }
    public function getLog()
    {
        return $this->log;
    }
    public function readLines($options, $desc, $validators = [], $fp_in = null, $fp_out = null)
    {
        if ($fp_in || $fp_out) {
            $options = parent::readLines($options, $desc, $validators, $fp_in, $fp_out);
            if ($this->is_logging) {
                $input = $this->deCompileOptions($options, $desc);
                $this->log = $input;
            }
            return $options;
        }
        $str = $this->datas[$this->index];
        $fp_in = fopen('php://memory', 'r+');
        if (!$fp_in) {
            return; // @codeCoverageIgnore
        }
        fputs($fp_in, $str);
        fseek($fp_in, 0);
        $fp_out = fopen('php://temp', 'w');
        if (!$fp_out) {
            return; // @codeCoverageIgnore
        }
        $ret = parent::readLines($options, $desc, $validators, $fp_in, $fp_out);
        $this->index++;
        fclose($fp_out);
        fclose($fp_in);
        if ($this->is_logging) {
            $input = $this->deCompileOptions($ret, $desc);
            $this->log = $input;
        }

        return $ret;
    }
    protected function deCompileOptions($options, $desc)
    {
        $ret = '';
        $lines = explode("\n", trim($desc));
        foreach ($lines as $line) {
            $line = rtrim($line).' ';
            $flag = preg_match('/\{(.*?)\}/', $line, $m);
            if (!$flag) {
                continue;
            }
            $key = $m[1];
            $ret .= $options[$key]."\n";
        }
        return $ret;
    }
}
