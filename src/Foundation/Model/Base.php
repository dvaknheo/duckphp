<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

namespace DuckPhp\Foundation\Model;

abstract class Base extends Helper
{
    use ModelTrait;

    ////////// Model-layer static helpers, declared explicitly (not __callStatic
    ////////// magic) so that both `Base::Db()` and `$model->Db()` keep working.
    /**
     *
     * @param mixed $tag
     * @return \DuckPhp\Db\Db
     */
    public static function Db($tag = null)
    {
        return Helper::Db($tag);
    }
    /**
     *
     * @return \DuckPhp\Db\Db
     */
    public static function DbForRead()
    {
        return Helper::DbForRead();
    }
    /**
     *
     * @return \DuckPhp\Db\Db
     */
    public static function DbForWrite()
    {
        return Helper::DbForWrite();
    }
    public static function SqlForPager(string $sql, int $pageNo, int $pageSize = 10): string
    {
        return Helper::SqlForPager($sql, $pageNo, $pageSize);
    }
    public static function SqlForCountSimply(string $sql): string
    {
        return Helper::SqlForCountSimply($sql);
    }
    /**
     * @return string
     */
    public static function DatabaseDriver(): string
    {
        return Helper::DatabaseDriver();
    }
}
