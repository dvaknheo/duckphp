<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Ext;

use DuckPhp\Core\Console;

class AutoReadLineConsole extends Console
{
    public $file_index = 99999;
    public $datas = [];
    public function autoFill($datas)
    {
        $datas = is_array($datas)?$datas:[$datas];
        $this->datas = $datas;
        $this->file_index = 0;
    }
    public function readLines($options, $desc, $validators = [], $fp_in = null, $fp_out = null)
    {
        $str = $this->datas[$this->file_index];
        $fp_in = fopen('php://memory', 'r+');
        fputs($fp_in, $str);
        fseek($fp_in, 0);
        $fp_out = fopen('php://temp', 'w');
        $ret = parent::readLines($options, $desc, $validators, $fp_in, $fp_out);
        $this->file_index++;
        fclose($fp_out);
        fclose($fp_in);
        
        return $ret;
    }
}
