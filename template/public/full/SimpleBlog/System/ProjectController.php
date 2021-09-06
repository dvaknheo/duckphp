<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

namespace SimpleBlog\System;

use DuckPhp\Foundation\SimpleControllerTrait;
use DuckPhp\Helper\ControllerHelperTrait;

class ProjectController
{
    use SimpleControllerTrait;
    use ControllerHelperTrait;
    ////
    
    public static function CheckInstall()
    {
        $flag = App::G()->isInstalled();
        if (!$flag) {
            static::ExitRouteTo('install/index');
        }
    }
    
    public static function RecordsetUrl($data, $cols_map = [])
    {
        return static::G()->_RecordsetUrl($data, $cols_map);
    }
    
    public static function RecordsetH($data, $cols = [])
    {
        return static::G()->_RecordsetH($data, $cols);
    }
    
    public function _RecordsetUrl($data, $cols_map = [])
    {
        //need more quickly;
        if ($data === []) {
            return $data;
        }
        if ($cols_map === []) {
            return $data;
        }
        $keys = array_keys($data[0]);
        array_walk(
            $keys,
            function (&$val, $k) {
                $val = '{'.$val.'}';
            }
        );
        foreach ($data as &$v) {
            foreach ($cols_map as $k => $r) {
                $values = array_values($v);
                $changed_value = str_replace($keys, $values, $r);
                $v[$k] = __url($changed_value);
            }
        }
        unset($v);
        return $data;
    }
    public function _RecordsetH($data, $cols = [])
    {
        if ($data === []) {
            return $data;
        }
        $cols = is_array($cols)?$cols:array($cols);
        if ($cols === []) {
            $cols = array_keys($data[0]);
        }
        foreach ($data as &$v) {
            foreach ($cols as $k) {
                $v[$k] = __h($v[$k], ENT_QUOTES);
            }
        }
        return $data;
    }
}
