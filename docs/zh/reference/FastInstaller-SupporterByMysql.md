# DuckPhp\FastInstaller\SupporterByMysql

MySQL 数据库安装支持器。

## 简介

`SupporterByMysql` 继承自 `Supporter`，实现了 MySQL 数据库在安装过程中的 DSN 读写、表结构查询与安装提示等功能。

## 选项

`SupporterByMysql` 没有自定义选项，依赖父类的 `database_driver_supporter_map`。

## 使用方式

```php
use DuckPhp\FastInstaller\Supporter;

// 当 DbManager 的驱动为 mysql 时，返回 SupporterByMysql 实例
$supporter = Supporter::Current();

$desc = $supporter->getInstallDesc();
$options = $supporter->readDsnSetting($options);
$options = $supporter->writeDsnSetting($options);
$tables = $supporter->getAllTable();
$sql = $supporter->getSchemeByTable($table);
```

## 配置示例

无需单独配置，只需在 `Supporter` 的映射中包含 MySQL：

```php
class App extends DuckPhp
{
    public $options = [
        'database_driver_supporter_map' => [
            'mysql' => \DuckPhp\FastInstaller\SupporterByMysql::class,
        ],
    ];
}
```

## 注意事项

1. 默认 DSN 为 `mysql:host=127.0.0.1;port=3306;dbname=...;charset=utf8mb4;`。
2. `readDsnSetting()` 默认补全 `host=127.0.0.1` 和 `port=3306`。
3. `writeDsnSetting()` 会对输入值进行 `trim` 与 `addslashes` 处理。
4. `getSchemeByTable()` 会将 `AUTO_INCREMENT=...` 重置为 `AUTO_INCREMENT=1`。

## 全部选项

```php
public $options = [
    // 继承自 Supporter，无独立选项
];
```

## 方法列表

### 公共方法

    public function readDsnSetting($options)
解析 `dsn` 并补全 `host` 和 `port` 默认值

    public function writeDsnSetting($options)
根据 `host`、`port`、`dbname` 生成 MySQL DSN 字符串

    public function getAllTable()
执行 `SHOW TABLES` 获取所有表名

    public function getSchemeByTable($table)
执行 `SHOW CREATE TABLE` 获取表结构，并重置 `AUTO_INCREMENT`

    public function getInstallDesc()
返回 MySQL 安装时的交互提示文本

## 相关链接

- [DuckPhp\FastInstaller\Supporter](FastInstaller-Supporter.md)
- [DuckPhp\FastInstaller\DatabaseInstaller](FastInstaller-DatabaseInstaller.md)
- [DuckPhp\FastInstaller\SqlDumper](FastInstaller-SqlDumper.md)
- [DuckPhp\Component\DbManager](Component-DbManager.md)
