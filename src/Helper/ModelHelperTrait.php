<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Helper;

use DuckPhp\Component\DbManager;
use DuckPhp\Core\Helper;

trait ModelHelperTrait
{
    /**
     *
     * @param mixed $tag
     * @return \DuckPhp\Db\Db
     */
    public static function Db($tag = null)
    {
        return DbManager::_()->_Db($tag);
    }
    /**
     *
     * @return \DuckPhp\Db\Db
     */
    public static function DbForRead()
    {
        return DbManager::_()->_DbForRead();
    }
    /**
     *
     * @return \DuckPhp\Db\Db
     */
    public static function DbForWrite()
    {
        return DbManager::_()->_DbForWrite();
    }
    public static function SqlForPager(string $sql, int $pageNo, int $pageSize = 10): string
    {
        return Helper::_()->_SqlForPager($sql, $pageNo, $pageSize);
    }
    public static function SqlForCountSimply(string $sql): string
    {
        return Helper::_()->_SqlForCountSimply($sql);
    }
}
