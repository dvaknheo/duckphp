<?php
namespace DNMVCS;

trait DNMVCS_Misc
{
    public static function ExitJson($ret)
    {
        return static::G()->_ExitJson($ret);
    }
    public static function ExitRedirect($url, $only_in_site=true)
    {
        return static::G()->_ExitRedirect($url, $only_in_site);
    }
    public static function ExitRouteTo($url)
    {
        return static::G()->_ExitRedirect(static::URL($url), true);
    }
    public static function Exit404()
    {
        static::On404();
        static::exit_system();
    }
    ////
    public function _ExitJson($ret)
    {
        static::header('Content-Type:text/json');
        // DNMVCS::G()->onBeforeShow([],'');
        echo json_encode($ret, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK);
        static::exit_system();
    }
    public function _ExitRedirect($url, $only_in_site=true)
    {
        if ($only_in_site && parse_url($url, PHP_URL_HOST)) {
            //something  wrong
            static::exit_system();
            return;
        }
        // DNMVCS::G()->onBeforeShow([],'');
        static::header('location: '.$url, true, 302);
        static::exit_system();
    }
    ////
    public static function H($str)
    {
        return static::G()->_H($str);
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
        return static::G()->_RecordsetUrl($data, $cols_map);
    }
    
    public static function RecordsetH(&$data, $cols=[])
    {
        return static::G()->_RecordsetH($data, $cols);
    }
    public function _RecordsetUrl(&$data, $cols_map=[])
    {
        //need more quickly;
        if ($data===[]) {
            return $data;
        }
        if ($cols_map===[]) {
            return $data;
        }
        $keys=array_keys($data[0]);
        array_walk($keys, function (&$val, $k) {
            $val='{'.$val.'}';
        });
        foreach ($data as &$v) {
            foreach ($cols_map as $k=>$r) {
                $values=array_values($v);
                $v[$k]=static::URL(str_replace($keys, $values, $r));
            }
        }
        unset($v);
        return $data;
    }
    public function _RecordsetH(&$data, $cols=[])
    {
        if ($data===[]) {
            return $data;
        }
        $cols=is_array($cols)?$cols:array($cols);
        if ($cols===[]) {
            $cols=array_keys($data[0]);
        }
        foreach ($data as &$v) {
            foreach ($cols as $k) {
                $v[$k]=static::H($v[$k], ENT_QUOTES);
            }
        }
        return $data;
    }
}
