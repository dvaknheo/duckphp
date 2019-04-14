<?php
namespace DNMVCS;

trait DNMVCS_Misc
{
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
