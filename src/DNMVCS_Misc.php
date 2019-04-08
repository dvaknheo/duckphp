<?php
namespace DNMVCS;

trait DNMVCS_Misc
{
    public static function H($str)
    {
        return static::G()->_H($str);
    }
    
    public function _ExitJson($ret)
    {
        DNMVCS::header('Content-Type:text/json');
        // DNMVCS::G()->onBeforeShow([],'');
        echo json_encode($ret, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK);
        DNMVCS::exit_system();
    }
    public function _ExitRedirect($url, $only_in_site=true)
    {
        if ($only_in_site && parse_url($url, PHP_URL_HOST)) {
            //something  wrong
            DNMVCS::exit_system();
            return;
        }
        // DNMVCS::G()->onBeforeShow([],'');
        DNMVCS::header('location: '.$url, true, 302);
        DNMVCS::exit_system();
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
