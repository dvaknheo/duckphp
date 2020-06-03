<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

namespace DuckPhp\Helper;

use DuckPhp\Helper\HelperTrait;
use DuckPhp\Core\App;

class ModelHelper
{
    use HelperTrait;
    
    public function DB($tag = null)
    {
        return App::DB($tag);
    }
    public function DB_R()
    {
        return App::DB_R();
    }
    public function DB_W()
    {
        return App::DB_W();
    }
    public static function SqlForPager($sql, $pageNo, $pageSize = 10)
    {
        return App::SqlForPager($sql, $pageNo, $pageSize);
    }
    public static function SqlForCountSimply($sql)
    {
        return App::SqlForCountSimply($sql);
    }
}
