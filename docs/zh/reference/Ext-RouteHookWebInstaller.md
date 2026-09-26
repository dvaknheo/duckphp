# DuckPhp\Ext\RouteHookWebInstaller

## 简介

`RouteHookWebInstaller` 是**网页安装向导**：应用未完成安装时，把访问引导到一个表单页，让用户填写数据库/Redis 等配置并“一键安装”（写配置、建表）。它挂在 Route 的 `prepend-inner`，用于框架的 web 安装场景（与 CLI 的 `DuckPhpInstaller` 互补）。

主要流程：`_Hook` 判断“是否需要安装/当前是否安装请求” → `installAction()` 处理表单（环境检查 → 可选 custom 回调 → 写配置 → 执行建表 SQL）→ `show()` 渲染安装视图（默认内置视图 `RouteHookWebInstallerView`，文案经 `__hl('webinstaller.*')` 多语言）。

## 类信息

- 命名空间：`DuckPhp\Ext`
- 声明：`class RouteHookWebInstaller extends DuckPhp\Core\ComponentBase`

## 选项

| 选项 | 默认值 | 说明 |
|---|---|---|
| `web_installer_use_database` | `true` | 安装流程是否包含数据库。 |
| `web_installer_use_redis` | `true` | 安装流程是否包含 Redis。 |
| `web_installer_database_drivers` | `['sqlite'=>true,'pgsql'=>true,'duckdb'=>false]` | 可选的数据库驱动。 |
| `web_installer_view` | `''` | 自定义安装视图文件（空用内置视图）。 |
| `web_installer_view_block_custom` | `null` | 自定义区块模板（视图内嵌）。 |
| `web_installer_force` | `false` | 是否强制重装（先清表）。 |
| `web_installer_check_custom_callback` | `null` | 自定义“环境检查”回调。 |
| `web_installer_do_custom_callback` | `null` | 自定义“安装执行”回调。 |
| `web_installer_render_custom_callback` | `null` | 自定义“渲染”回调。 |
| `web_installer_default_sentences` | `[]` | 界面文案覆盖（空则用内置英文默认文案 `builtin_default_sentences`）。 |

## 使用方式

```php
\DuckPhp\Ext\RouteHookWebInstaller::_()->init([
    'web_installer_use_database' => true,
    'web_installer_use_redis'    => false,
    'web_installer_database_drivers' => ['sqlite' => true],
], $app);
// 访问站点时若未安装 → 显示安装表单；提交 → 环境检查 + 建库建表 + 写配置
```

## 注意事项

- 文案体系：视图内所有 UI 文本用 `__hl('webinstaller.*')`；默认句由 `builtin_default_sentences` 提供，`init()` 时经 `Lang::_()->loadLanguageFrag('for_webinstaller', …)` 导入 Lang，于是每语言都能用 `config/lang-{locale}-for_webinstaller.php` 给安装页配文案。真实翻译优先：主语言文件 > 该 frag 文件 > 默认句。
- 安装动作：先 `checkEnv()`（PHP 扩展等）→ Redis/数据库检查 → custom 回调（若配置）→ 写配置（经 `ExtOptionsLoader` 等）→ 执行 `{driver}.sql` 建表（`doSchema`/`executeSqlFile`）；`web_installer_force` 时先执行 clean。
- 未安装/已安装的判定与跳转细节以源码 `_Hook`/`installAction` 为准；本组件主要面向框架内置安装页场景。

## 方法列表

### 公共方法

    public static function Hook($path_info)
静态钩子入口，转发 `_Hook`。

    public function init(array $options, ?object $context = null)
初始化（父类流程 + 导入默认文案）。

    public function _Hook($path_info)
路由钩子体：按“是否安装页/安装请求/已安装”决定展示向导或放行。

    public function installAction()
处理安装表单提交：检查 → 写配置 → 执行建表 SQL。

    public function installBusiness()
安装业务入口（供 installAction 内部调用）。

### 受保护方法

    protected function initContext(object $context): void
把 `Hook` 挂到 Route `prepend-inner`。

    protected function getInstallPath()
计算安装配置路径。

    protected function buildPageData()
组装视图数据（环境检查项、配置项等）。

    protected function renderCustom()
执行 `web_installer_render_custom_callback`。

    protected function checkRootHasRedis()
检查根应用是否配置 Redis。

    protected function checkRootHasDatabase()
检查根应用是否配置数据库。

    protected function checkCustom()
执行 `web_installer_check_custom_callback`。

    protected function doCustom()
执行 `web_installer_do_custom_callback`。

    protected function checkEnv()
环境检查（返回检查项列表）。

    protected function checkRedis()
检查/测试 Redis 连接。

    protected function getEnabledDatabaseDrivers()
返回可用的数据库驱动列表。

    protected function checkDatabase()
检查/测试数据库连接。

    protected function makeDsn()
按表单生成 DSN。

    protected function getSchemaSqlFile()
定位建表 SQL 文件。

    protected function doSchema()
执行建表（force 时先 clean）。

    protected function executeSqlFile()
执行 SQL 文件。

    protected function getCurrentDriver()
取当前驱动名。

    protected function executeSql()
执行单条 SQL。

    protected function show()
渲染安装页面（默认内置视图）。

## 相关链接

- [DuckPhp\Ext\RouteHookWebInstallerView](Ext-RouteHookWebInstallerView.md) — 内置安装视图
- [DuckPhp\Ext\DuckPhpInstaller](Ext-DuckPhpInstaller.md) — CLI 安装器
