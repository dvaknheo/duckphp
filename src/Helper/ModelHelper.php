<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace DuckPhp\Helper;

use DuckPhp\Helper\HelperTrait;
use DuckPhp\Core\App;

class ModelHelper
{
    use HelperTrait;
    
    public static function SqlForPager($sql, $pageNo, $pageSize = 10)
    {
        return App::SqlForPager($sql, $pageNo, $pageSize);
    }
    public static function SqlForCountSimply($sql)
    {
        return App::SqlForCountSimply($sql);
    }
}
