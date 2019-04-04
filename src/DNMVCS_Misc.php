<?php
namespace DNMVCS;

trait DNMVCS_Misc
{
    public static function H($str)
    {
        return static::G()->_H($str);
    }
    public function _Import($file)
    {
        $file=rtrim($file, '.php').'.php';
        require_once($this->path_lib.$file);
    }
    
    public function _H(&$str)
    {
        if (is_string($str)) {
            $str=htmlspecialchars($str, ENT_QUOTES);
            return $str;
        }
        if (is_array($str)) {
            foreach ($str as $k =>&$v) {
                self::_H($v);
            }
            return $str;
        }
        
        if (is_object($str)) {
            $arr=get_object_vars($str);
            foreach ($arr as $k =>&$v) {
                self::_H($v);
            }
            return $arr;
        }

        return $str;
    }
    public static function RecordsetUrl(&$data, $cols_map=[])
    {
        return DNMVCSExt::G()->_RecordsetUrl($data, $cols_map);
    }
    
    public static function RecordsetH(&$data, $cols=[])
    {
        return DNMVCSExt::G()->_RecordsetH($data, $cols);
    }
}
