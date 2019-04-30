<?php
namespace DNMVCS\Glue;

use DNMVCS\Core\App;

trait GlueForController
{
    public static function Import($file)
    {
        return App::G()->_Import($file);
    }
    public static function RecordsetUrl(&$data, $cols_map=[])
    {
        return App::G()->_RecordsetUrl($data, $cols_map);
    }
    
    public static function RecordsetH(&$data, $cols=[])
    {
        return App::G()->_RecordsetH($data, $cols);
    }
}
