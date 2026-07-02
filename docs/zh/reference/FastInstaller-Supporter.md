# DuckPhp\FastInstaller\Supporter

数据库安装支持器基类。

## 简介

`Supporter` 是数据库驱动相关的安装辅助基类。它根据 `DbManager` 当前的数据库驱动，从 `database_driver_supporter_map` 中匹配对应的实现类（如 `SupporterByMysql`、`SupporterBySqlite`），并委托给该实现处理 DSN 读写、表结构查询等操作。

## 选项

| 选项 | 默认值 | 说明 |
|---|---|---|
| `database_driver_supporter_map` | `['mysql' => SupporterByMysql::class, 'sqlite' => SupporterBySqlite::class]` | 数据库驱动到支持器实现类的映射 |

## 使用方式

### 获取当前支持器

```php
use DuckPhp\FastInstaller\Supporter;

$supporter = Supporter::Current(); // 返回当前驱动对应的支持器实例
```

### 读取 DSN 设置

```php
$options = Supporter::Current()->readDsnSetting($options);
```

### 写入 DSN 设置

```php
$options = Supporter::Current()->writeDsnSetting($options);
```

## 配置示例

### 自定义支持器映射

```php
class App extends DuckPhp
{
    public $options = [
        'database_driver_supporter_map' => [
            'mysql' => \MyApp\Support\SupporterByMysql::class,
            'sqlite' => \MyApp\Support\SupporterBySqlite::class,
            'pgsql' => \MyApp\Support\SupporterByPgsql::class,
        ],
    ];
}
```

## 注意事项

1. `getSupporter()` 会根据当前驱动自动查找映射，未找到时会抛出异常。
2. 基类中的 `getInstallDesc()`、`writeDsnSetting()`、`getAllTable()`、`getSchemeByTable()` 方法直接抛出异常，需要子类实现。
3. `readDsnSetting()` 在基类中已实现，子类通常会通过 `parent::readDsnSetting()` 复用并补充默认值。

## 全部选项

```php
public $options = [
    'database_driver_supporter_map' => [
        'mysql' => SupporterByMysql::class,
        'sqlite' => SupporterBySqlite::class,
    ],
];
```

## 方法列表

### 公共方法

    public static function Current()
获取当前数据库驱动对应的支持器实例，等价于 `static::_()->getSupporter()`

    public function getSupporter()
根据 `database_driver_supporter_map` 返回对应的支持器实例

    public function readDsnSetting($options)
解析 `dsn` 字符串中的键值对，并合并到 `$options` 中

### 受保护方法（基类占位方法，需子类实现）

    protected function getInstallDesc()
返回安装时提示用户输入的描述文本

    protected function writeDsnSetting($options)
根据用户输入生成新的 `dsn` 字符串

    protected function getAllTable()
获取数据库中所有表名

    protected function getSchemeByTable($table)
获取指定表的建表 SQL

## 相关链接

- [DuckPhp\FastInstaller\SupporterByMysql](FastInstaller-SupporterByMysql.md)
- [DuckPhp\FastInstaller\SupporterByPgsql](FastInstaller-SupporterByPgsql.md)
- [DuckPhp\FastInstaller\SupporterBySqlite](FastInstaller-SupporterBySqlite.md)
- [DuckPhp\FastInstaller\DatabaseInstaller](FastInstaller-DatabaseInstaller.md)
- [DuckPhp\FastInstaller\SqlDumper](FastInstaller-SqlDumper.md)
- [DuckPhp\Component\DbManager](Component-DbManager.md)
