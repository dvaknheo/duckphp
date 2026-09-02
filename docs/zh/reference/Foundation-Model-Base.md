# DuckPhp\Foundation\Model\Base

`DuckPhp\Foundation\Model\Base` 模型层基类。

## 简介

`Base` 是 `DuckPhp\Foundation\Model` 命名空间下的 类，由 DuckPhp 框架提供。

## 选项

无。

## 使用方式

### 基本用法

```php
use DuckPhp\Foundation\Model\Base;

$obj = Base::_();
```

## 注意事项

1. 本类为框架内部或扩展组件，通常由框架自动加载。
2. 如需自定义行为，可继承本类并覆盖相应方法。

## 方法列表

### 公共方法

    function table(): string

    function prepare(string $sql): string

    static function _($object = null)
@return static

    static function Db($tag = null)
@param mixed $tag
 @return \DuckPhp\Db\Db

    static function DbForRead()
@return \DuckPhp\Db\Db

    static function DbForWrite()
@return \DuckPhp\Db\Db

    static function SqlForPager(string $sql, int $pageNo, int $pageSize = 10): string

    static function SqlForCountSimply(string $sql): string

    static function DatabaseDriver(): string
@return string

### 受保护方法

    function getTableNameByClass(string $class): string

    function getTablePrefixByClass(string $class): string

    function getList(array $where = [], int $page = 1, int $page_size = 10): array
@param array<string, mixed> $where

    function find($a)

    function add(array $data)
@param array<string, mixed> $data

    function update($id, array $data, ?string $key = null)
@param array<string, mixed> $data

    function execute(string $sql, ...$args)

    function fetchAll(string $sql, ...$args): array

    function fetch(string $sql, ...$args)

    function fetchColumn(string $sql, ...$args)

    function fetchObject(string $sql, ...$args)

    function fetchObjectAll(string $sql, ...$args): array

## 相关链接

- [中文参考手册目录](index.md)
