# DuckPhp\Helper\ModelHelperTrait
[toc]

## 简介
ModelHelperTrait 只有 5 个全静态方法

    public static function Db($tag = null)
获得 Db 对象
参见  [DuckPhp\Component\DbManager::Db](Component-DbManager.md#Db)

    public static function DbForRead()
获得只读用的 Db 对象 public static function DbForRead() 
参见 [DuckPhp\Component\DbManager::DbForRead](Component-DbManager.md#DbForRead)

    public static function DbForWrite()
获得读写用的 Db 对象
参见 [DuckPhp\Component\DbManager::DbForWrite](Component-DbManager.md#DbForWrite)

    public static function SqlForPager(string $sql, int $pageNo, int $pageSize = 10): string
分页 limit 的 sql,补齐 sql用

    public static function SqlForCountSimply(string $sql): string
简单的把 `select ... from ` 替换成 `select count(*)as c from `
用于分页处理。
