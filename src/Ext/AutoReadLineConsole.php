<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Ext;

use DuckPhp\Core\Console;

class AutoReadLineConsole extends Console
{
    public $file_index = 0;
    public $datas = [];
    public function autoFill($datas)
    {
        $datas = is_array($datas)?$datas:[$datas];
        $this->datas += $datas;
    }
    public function readLines($options, $desc, $validators = [], $fp_in = null, $fp_out = null)
    {
        if ($fp_in || $fp_out) {
            return parent::readLines($options, $desc, $validators, $fp_in, $fp_out);
        }
        $str = $this->datas[$this->file_index];
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
        $this->file_index++;
        fclose($fp_out);
        fclose($fp_in);
        
        return $ret;
    }
}
