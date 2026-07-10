# DuckPhp\FastInstaller\FastInstaller

命令行安装器主组件。

## 简介

`FastInstaller` 提供一套命令行安装、更新、卸载、导出 SQL 以及引入子应用的机制。它会联动 `DatabaseInstaller`、`RedisInstaller`、`SqlDumper` 等组件完成安装流程。

## 选项

| 选项 | 默认值 | 说明 |
|---|---|---|
| `install_input_validators` | `[]` | 安装时输入项的验证器数组 |
| `install_default_options` | `[]` | 安装输入项的默认值 |
| `install_input_desc` | `''` | 安装时输入项的提示描述 |
| `install_callback` | `null` | 安装完成后的回调函数，签名 `function($input_options)` |
| `install_support_database_list` | `''` | 支持的数据库列表说明 |
| `allow_require_ext_app` | `false` | 是否允许 `require` 命令引入外部应用 |

## 使用方式

### 命令行入口

```bash
# 安装当前应用
php app.php install

# 强制安装
php app.php install --force

# 仅查看配置，不执行
php app.php install --dry

# 跳过 SQL 导入
php app.php install --skip-sql

# 跳过资源复制
php app.php install --skip-resource

# 跳过子应用安装
php app.php install --skip-children

# 导出 SQL 文件
php app.php dumpsql

# 引入子应用
php app.php require Vendor/ChildApp --

# 更新
php app.php update

# 卸载
php app.php remove
```

### 在应用中配置

```php
class App extends DuckPhp
{
    public $options = [
        'install_input_desc' => "----\ninput app name: [{app_name}]\n",
        'install_default_options' => [
            'app_name' => 'MyApp',
        ],
        'install_callback' => function ($input_options) {
            echo "App name: {$input_options['app_name']}\n";
        },
    ];
}
```

## 配置示例

### 基础安装配置

```php
class App extends DuckPhp
{
    public $options = [
        'install_input_desc' => "----\nPlease input some options:\n",
        'install_default_options' => [
            'admin_name' => 'admin',
        ],
        'install_input_validators' => [],
        'install_callback' => null,
    ];
}
```

### 允许引入外部子应用

```php
class App extends DuckPhp
{
    public $options = [
        'allow_require_ext_app' => true,
    ];
}
```

## 注意事项

1. `install` 命令会触发 `onInstall`、`onBeforeChildrenInstall`、`onInstalled` 事件，以及应用自身的 `onInstalled` 方法。
2. 如果当前应用已安装且未指定 `--force`，会提示使用 `--force` 后跳过。
3. `require` 命令只能由根应用使用，且需要目标类继承自 `DuckPhp\Core\App`。
4. 子应用安装通过事件分发，安装过程中会临时切换 `App::Phase` 到子应用。

## 全部选项

```php
public $options = [
    'install_input_validators' => [],
    'install_default_options' => [],
    'install_input_desc' => '',
    'install_callback' => null,
    'install_support_database_list' => '',
    'allow_require_ext_app' => false,
];
```

## 方法列表

### 公共方法

    public function command_install()
执行 `install` 命令，调用 `doCommandInstall()`

    public function command_dumpsql()
执行 `dumpsql` 命令，导出 SQL 到 `config/{driver}.sql`

    public function command_require()
执行 `require` 命令，引入并安装外部子应用

    public function command_update()
执行 `update` 命令，触发 `OnInstallUpdate` 事件

    public function command_remove()
执行 `remove` 命令，触发 `OnInstallRemove` 事件

    public function command_dump_res()
执行 `dump_res` 命令，克隆资源到新的资源前缀

    public function doCommandInstall()
初始化组件并执行安装流程

    public function doCommandUpdate()
触发当前应用阶段的 `OnInstallUpdate` 事件

    public function doCommandRemove()
触发当前应用阶段的 `OnInstallRemove` 事件

    public function forceFail(): void
标记本次安装失败，安装流程会中断

    public function doInstall()
执行完整的安装流程，包括数据库、Redis、资源、回调、子应用等

    public function getCurrentInput(): array
获取当前安装流程中用户输入的选项

### 受保护方法

    protected function initComponents(): void
初始化 `FastInstaller`、`DatabaseInstaller`、`RedisInstaller`、`SqlDumper` 等组件

    protected function showHelp(array $app_options = [], array $input_options = []): void
显示命令行帮助信息

    protected function getDefaultUrlPrefix(string $ns): string
根据命名空间生成默认的 URL 前缀

    protected function installChildren(): void
递归安装所有子应用

    protected function doInstallAction(array $input_options)
执行安装动作：导入 SQL、修改资源、执行回调

    protected function cloneResource(string $dest): string
克隆资源文件到指定目标前缀

    protected function saveExtOptions($ext_options)
合并并保存扩展选项

    protected function changeResource(): array
交互式修改资源 URL 前缀并决定是否克隆资源

## 相关链接

- [DuckPhp\FastInstaller\DatabaseInstaller](FastInstaller-DatabaseInstaller.md)
- [DuckPhp\FastInstaller\RedisInstaller](FastInstaller-RedisInstaller.md)
- [DuckPhp\FastInstaller\SqlDumper](FastInstaller-SqlDumper.md)
- [DuckPhp\Core\App](Core-App.md)
- [DuckPhp\Core\Console](Core-Console.md)
- [DuckPhp\Component\RouteHookResource](Component-RouteHookResource.md)
- [DuckPhp\Component\ExtOptionsLoader](Component-ExtOptionsLoader.md)
