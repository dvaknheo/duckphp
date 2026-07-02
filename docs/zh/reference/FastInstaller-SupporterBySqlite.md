# DuckPhp\FastInstaller\SupporterBySqlite

SQLite 数据库安装支持器。

## 简介

`SupporterBySqlite` 继承自 `Supporter`，实现了 SQLite 数据库在安装过程中的 DSN 读写、表结构查询与安装提示等功能。SQLite 不需要网络连接参数，配置项主要是数据库文件路径。

## 选项

`SupporterBySqlite` 没有自定义选项，依赖父类的 `database_driver_supporter_map`。

## 使用方式

```php
use DuckPhp\FastInstaller\Supporter;

// 当 DbManager 的驱动为 sqlite 时，返回 SupporterBySqlite 实例
$supporter = Supporter::Current();

$desc = $supporter->getInstallDesc();
$options = $supporter->readDsnSetting($options);
$options = $supporter->writeDsnSetting($options);
$tables = $supporter->getAllTable();
$sql = $supporter->getSchemeByTable($table);
```

## 配置示例

```php
class App extends DuckPhp
{
    public $options = [
        'database_driver_supporter_map' => [
            'sqlite' => \DuckPhp\FastInstaller\SupporterBySqlite::class,
        ],
    ];
}
```

## 注意事项

1. 如果未设置 DSN 文件路径，本地数据库会按命名空间生成文件名，否则使用默认 `database.db`。
2. `writeDsnSetting()` 会清空 `username` 和 `password`，因为 SQLite 通常不需要。
3. `getAllTable()` 会过滤掉 `sqlite_` 开头的系统表。
4. `getSchemeByTable()` 会将 SQLite 的双引号表名转换为反引号形式。

## 全部选项

```php
public $options = [
    // 继承自 Supporter，无独立选项
];
```

## 方法列表

### 公共方法

    public function getRuntimePath()
获取当前应用的运行时路径，用于安装提示显示

    public function readDsnSetting($options)
从 DSN 中解析文件路径，未设置时按规则生成默认文件名

    public function writeDsnSetting($options)
根据 `file` 生成 `sqlite:...` 形式的 DSN，并清空用户名密码

    public function getAllTable()
从 `sqlite_master` 查询用户表，排除系统表

    public function getSchemeByTable($table)
从 `sqlite_master` 查询指定表的建表 SQL，并转换表名引号风格

    public function getInstallDesc()
返回 SQLite 安装时的交互提示文本，包含运行时路径和数据库文件

## 相关链接

- [DuckPhp\FastInstaller\Supporter](FastInstaller-Supporter.md)
- [DuckPhp\FastInstaller\DatabaseInstaller](FastInstaller-DatabaseInstaller.md)
- [DuckPhp\FastInstaller\SqlDumper](FastInstaller-SqlDumper.md)
- [DuckPhp\Core\App](Core-App.md)
- [DuckPhp\Component\DbManager](Component-DbManager.md)
