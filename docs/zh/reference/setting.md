# 应用设置（Setting）

> `options` 管「**框架怎么跑**」（行为开关），`setting` 管「**这套环境是什么**」（数据库口令、Redis 地址、平台标识、调试与维护状态）。
> 设置**不参与** `$options` 合并，用 `App::Setting()` 读；两者谁说了算见文末。

## 简介

设置是「跟着环境走、通常不进版本库」的那一层配置。它有三个来源、按顺序合并（后者盖前者），并且**只在根应用加载一次**，子应用与子 Phase 读到的都是根应用的那份。

## 设置从哪来（三步合并）

| 次序 | 来源 | 何时读 | 说明 |
|---|---|---|---|
| 1 | 选项 `setting`（数组） | 总是 | `App::$core_options` 里默认 `[]`；直接在应用选项里写数组即可 |
| 2 | `<path>/.env` | `use_env_file` 为真 | `parse_ini_file()` 读根目录下的 `.env`（INI 语法） |
| 3 | `<path>/<setting_file>` | `setting_file_enable` 为真 | `require` 该文件，须 `return` 数组；`setting_file` 缺省 `config/DuckPhpSettings.config.php`，可写绝对路径 |

- 第 3 步文件不存在时：`setting_file_ignore_exists` 为真（缺省）**静默跳过**，为假则抛 `ErrorException('DuckPhp: no Setting File')`。
- 实现都在 `App::loadSetting()`（以及 `dealWithEnvFile()` / `dealWithSettingFile()`）。
- ⚠️ **只在根应用加载**：`App::onPrepare()` 里判断 `is_root` 后才调 `loadSetting()`；子应用、子 Phase **不重复加载**，读到的永远是根应用那份。

## 怎么读

```php
$dsn  = App::Setting('database_list');     // 取一个键，缺省返回 null
$all  = App::Setting();                    // 不传键 → 返回整个设置数组
$home = App::Setting('my_home', '/');      // 带缺省值
```

- 静态入口 `App::Setting($key = null, $default = null)`；实例形式 `App::_()->_Setting(...)`。
- 实现是 `static::Root()->setting[$key] ?? $default` —— **永远读根应用的设置**，所以在子 Phase 里也能拿到同一份值。
- Helper 里也有：`Helper::Setting()`（[Foundation\Business\BusinessHelper](Foundation-Business-BusinessHelper.md) 与 [Foundation\Controller\ControllerHelper](Foundation-Controller-ControllerHelper.md) 都提供），业务/控制器层不必直接依赖 `App`。

## 框架自己认的设置键

<!-- GEN:settingkeys start -->
| 设置键 | 谁在读 | 作用 |
|---|---|---|
| `duckphp_is_debug` | Core\App::_IsDebug() | 调试开关；与选项 `is_debug` 取或。 |
| `duckphp_platform` | Core\App::_Platform() | 平台标识，由 App::Platform() 读出。 |
| `duckphp_is_maintain` | Core\App::prepareServe() | 维护模式；与选项 `is_maintain` 取或，为真时直接输出维护页。 |
| `database_list` | Component\DbManager | 数据库连接列表（`dsn/username/password/driver_options`）；`database_list_reload_by_setting` 与「选项未给」共同决定是否采用。 |
| `database` | Component\DbManager | 单个数据库连接的简写，`database_list_try_single` 为真时生效。 |
| `redis_list` | Component\RedisManager | Redis 连接列表；`redis_list_reload_by_setting` 与「选项未给」共同决定是否采用。 |
| `redis` | Component\RedisManager / Ext\RouteHookWebInstaller | 单个 Redis 连接的简写，`redis_list_try_single` 为真时生效。 |
<!-- GEN:settingkeys end -->

> 除上表之外，**其余键完全归工程自定义**（`Setting('my_key')`），框架不碰、也不会校验。

## 相关选项

<!-- GEN:settingoptions start -->
| 选项 | 默认值 | 说明 |
|---|---|---|
| `setting` | `[]` | 直接以数组形式给出的应用设置（setting）初值；作为 `loadSetting()` 的起点，随后再合并 `.env`（可选）与设置文件。 |
| `setting_file` | `'config/DuckPhpSettings.config.php'` | Setting 文件（相对根或绝对）。 |
| `setting_file_enable` | `true` | 是否加载设置文件。 |
| `setting_file_ignore_exists` | `true` | 设置文件缺失时是否忽略（并不抛错）。 |
| `use_env_file` | `false` | 为真则在 loadSetting 一并读根 `.env`(INI) 合并。 |
<!-- GEN:settingoptions end -->

- `setting` 写数组 = 「第 1 步」，跟写设置文件等价（只是不进版本库之外的文件）；
- 想让设置文件可选：保持 `setting_file_ignore_exists = true`（缺省）；
- 用 `.env` 放口令：把 `use_env_file` 置真。

## `options` 与 `setting` 谁说了算

| 场景 | 规则 |
|---|---|
| 调试开关 | `Setting('duckphp_is_debug')` **或** `options['is_debug']` —— 谁真谁生效（`App::IsDebug()`） |
| 维护模式 | `Setting('duckphp_is_maintain')` **或** `options['is_maintain']` —— 为真时直接输出维护页 |
| 平台标识 | 只认设置里的 `duckphp_platform`（`App::Platform()`） |
| 数据库 / Redis | **选项优先**：`options['database_list']`（或 `options['database']`）非空就用它；为空且 `database_list_reload_by_setting`（Redis 是 `redis_list_reload_by_setting`）为真时才回落设置 |
| 安装器写入 | `RouteHookWebInstaller` 安装完成后会把 `database_list` / `redis_list` **写进设置文件**，并把对应的 `*_reload_by_setting` 置为 false，避免被选项覆盖 |

## 环境区分与部署

- 设置文件**通常不入 git**：脚手架里的 `skeleton/config/DuckPhpSettings.config.php` 文件头就写着 `Do no save me in git`。
- `.env` 走 INI 语法（`parse_ini_file`），适合放口令；`#` 开头是注释。
- 生产环境建议：`installed` 置真、`is_debug` 关掉（选项或设置任一处关都行，但两处取或，所以**要关就两处都关**）。
- 部署细节见用户指南的 configuration / deployment 两章；本章只讲机制与键。

## 仓库里的真实样例

| 文件 | 用途 |
|---|---|
| `skeleton/config/DuckPhpSettings.config.php` | 脚手架生成的模板（`duckphp_*` 与 `database_list`/`redis_list` 都注释着） |
| `demo/config/DuckPhpSettings.config.php` | 演示应用用 |
| `tests/data_for_tests/setting.sample.php` | 测试用的样例（`database_list` 双库、`redis_list`） |

## 相关链接

- [应用选项总览（首页）](options.md) —— `options` 那一套
- [应用选项（按类分组）](options-by-class.md) / [应用选项（按字母顺序索引）](options-index.md)
- [DuckPhp\Core\App](Core-App.md) —— `loadSetting()`、`Setting()`、`setting*` 选项
- [DuckPhp\Component\DbManager](Component-DbManager.md) / [DuckPhp\Component\RedisManager](Component-RedisManager.md) —— 谁在读 `database_list` / `redis_list`
