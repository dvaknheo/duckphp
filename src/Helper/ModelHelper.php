<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Helper;

use DuckPhp\Core\App;
use DuckPhp\Helper\HelperTrait;

class ModelHelper
{
    use HelperTrait;
    /**
     *
     * @param mixed $tag
     * @return \DuckPhp\DB\DB
     */
    public static function DB($tag = null)
    {
        return App::DB($tag);
    }
    /**
     *
     * @return \DuckPhp\DB\DB
     */
    public static function DB_R()
    {
        return App::DB_R();
    }
    /**
     *
     * @return \DuckPhp\DB\DB
     */
    public static function DB_W()
    {
        return App::DB_W();
    }
    public static function SqlForPager(string $sql, int $pageNo, int $pageSize = 10): string
    {
        return App::SqlForPager($sql, $pageNo, $pageSize);
    }
    public static function SqlForCountSimply(string $sql): string
    {
        return App::SqlForCountSimply($sql);
    }
}
