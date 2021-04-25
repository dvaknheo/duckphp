<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Helper;

use DuckPhp\Core\App;

trait ModelHelperTrait
{
    /**
     *
     * @param mixed $tag
     * @return \DuckPhp\Db\Db
     */
    public static function Db($tag = null)
    {
        return App::Db($tag);
    }
    /**
     *
     * @return \DuckPhp\Db\Db
     */
    public static function DbForRead()
    {
        return App::DbForRead();
    }
    /**
     *
     * @return \DuckPhp\Db\Db
     */
    public static function DbForWrite()
    {
        return App::DbForWrite();
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
