<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace DuckPhp\Helper;

use DuckPhp\Core\Helper\ControllerHelper as Helper;
use DuckPhp\Core\App as App;

class ControllerHelper extends Helper
{
    public static function Import($file)
    {
        return App::G()::Import($file);
    }
    public static function RecordsetUrl(&$data, $cols_map = [])
    {
        return App::G()::RecordsetUrl($data, $cols_map);
    }
    public static function RecordsetH(&$data, $cols = [])
    {
        return App::G()::RecordsetH($data, $cols);
    }
    ///////
    public static function Pager()
    {
        return App::G()::Pager();
    }
}
