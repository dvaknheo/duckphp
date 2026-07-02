# DuckPhp\FastInstaller\DatabaseInstaller

数据库安装器组件。

## 简介

`DatabaseInstaller` 负责在命令行安装过程中引导用户配置数据库，并保存到扩展选项与 `DbManager` 中。它会根据当前应用配置的 `database_driver` 选择对应的 `Supporter` 实现来交互。

该组件通常由 `FastInstaller` 在安装流程中自动调用，一般不需要手动实例化。

## 选项

`DatabaseInstaller` 当前没有自定义选项。

| 选项 | 默认值 | 说明 |
|---|---|---|
| （无） | — | 本组件暂无选项 |

## 使用方式

### 自动调用

在 `FastInstaller::doInstall()` 中会自动执行：

```php
DatabaseInstaller::_()->install($force);
```

### 手动调用

```php
use DuckPhp\FastInstaller\DatabaseInstaller;

DatabaseInstaller::_()->install(true);  // 强制重新安装数据库配置
```

## 注意事项

1. 如果当前应用没有设置 `database_driver`，`install()` 会直接返回 `false`。
2. 当目标数据库驱动与 `DbManager` 当前驱动一致且已存在配置时，非强制模式下会跳过配置。
3. 配置过程中会循环提示用户输入 DSN 信息，直到成功连接至少一个数据库。
4. 数据库配置最终通过 `ExtOptionsLoader` 保存到扩展选项，并重新初始化 `DbManager`。

## 全部选项

```php
public $options = [
    //
];
```

## 方法列表

### 公共方法

    public function install($force = false)
执行数据库安装流程。如果当前应用未配置数据库驱动或已有配置且非强制，则返回 `false`

### 受保护方法

    protected function callResetDatabase($force = false)
重置数据库配置，调用 `configDatabase()` 获取用户输入并写入数据库连接

    protected function changeDatabase($data)
将配置保存到扩展选项并重新初始化 `DbManager`。`$data` 为数据库连接列表

    protected function configDatabase($ref_database_list = [])
循环提示用户输入数据库连接信息，直到成功连接并确认不再添加更多数据库

    protected function checkDb($database)
临时初始化一个 `DbManager` 实例来验证给定的数据库连接是否可用

## 相关链接

- [DuckPhp\FastInstaller\FastInstaller](FastInstaller-FastInstaller.md)
- [DuckPhp\FastInstaller\Supporter](FastInstaller-Supporter.md)
- [DuckPhp\FastInstaller\SupporterByMysql](FastInstaller-SupporterByMysql.md)
- [DuckPhp\FastInstaller\SupporterByPgsql](FastInstaller-SupporterByPgsql.md)
- [DuckPhp\FastInstaller\SupporterBySqlite](FastInstaller-SupporterBySqlite.md)
- [DuckPhp\Component\DbManager](Component-DbManager.md)
- [DuckPhp\Component\ExtOptionsLoader](Component-ExtOptionsLoader.md)
