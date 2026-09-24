# 4-9 性能调优与排错手册

> 解决什么问题：**症状 → 排查路径**。左边是你的现象，右边是「先看哪里、再看哪里、大概率是什么」。
> 前置：[第 2-10 章 请求生命周期与钩子点](lifecycle.md)（时序图是排查的地图）、[第 2-17 章 安全与性能清单](security-performance.md)（性能开关）。
> 用法：先在下面找症状，按「看什么」逐条执行；每条都会落到具体方法或命令。

## 最小示例

三条命令解决大部分「它为什么不按我想的跑」：

```php
Route::_()->getRouteError();          // 路由为什么没命中（E001/E003/E008/E009…）
Route::_()->PathInfo();               // 框架实际拿到的路径是什么
\DuckPhp\Core\PhaseContainer::Dump(); // 当前容器里有哪些单例（覆盖/共享问题必看）
```

再加一个钩子清单：

```php
echo \DuckPhp\Ext\RouteHookManager::_()->dump();   // 三条钩子链上到底挂了谁、顺序如何
```

## 机制说明

排查的原则只有两条：**先拿证据、每步只改一个变量**。框架的执行路径是确定的（[第 2-10 章](lifecycle.md)的时序图就是地图）：

```
init()（onPrepare → onInit → onInited） → serve()（onRequest → Route::run() → runChildren() → _On404）
                                                    ↑ 三段链：pre 钩子 → 默认路由 → post 钩子（finally 在 clear()）
```

所以问题一定落在四类位置上之一：**选项没生效**、**路由没命中**、**取到的实例/文件不是你以为的那个**、**输出时机不对**。下面这张工具箱就是为分别验证这四类准备的。

### 工具箱

| 工具 | 能回答什么 |
|---|---|
| [`Route::_()->getRouteError()`](../reference/Core-Route.md) | 路由失败的原因码 |
| `Route::_()->PathInfo()` | 框架「以为」的请求路径 |
| `Helper::getRouteCallingClass()` / `getRouteCallingMethod()` | 实际命中了哪个类/方法 |
| [`RouteHookManager::_()->dump()`](../reference/Ext-RouteHookManager.md) | 钩子链的成员与顺序 |
| [`PhaseContainer::Dump()`](../reference/Core-PhaseContainer.md) / `dumpAllObject()` | 容器里有哪些单例、属于哪个相位 |
| [`App::_()->options`](../reference/Core-App.md) | 生效的选项（和被白名单挡掉的对比） |
| `Helper::Setting()` | 设置文件到底读到了什么 |
| `__debug_log()` / `__var_log()` / `__trace_dump()` | 把现场写进日志（[第 1-6 章](debugging.md)） |
| `database_log_sql_query = true` | 实际执行了哪些 SQL |
| `__is_debug()` / `__is_real_debug()` | 调试口径为什么是开的 |

## 症状 → 排查路径

### A. 路由与请求

| 症状 | 看什么 | 大概率原因 / 改法 |
|---|---|---|
| 404，页面提示 `404 File Not Found` | `Route::_()->getRouteError()` | 类不存在（E003）→ 检查命名空间与 `Controller` 后缀；前缀不符（E001）→ 子应用的 `controller_url_prefix`（[第 3-2 章](mount-app.md)） |
| `/about` 报类不存在 | 单段路径的规则 | 单段 = «欢迎类的方法»，不是控制器；控制器至少两段，或改用 `route_map`（[第 2-2 章](routing.md)） |
| `/Main/index` 被拒绝 | 错误码 E009 | `controller_welcome_class_visible` 默认 `false`；真需要就设为 `true` |
| 命中了错误的控制器 | `getRouteCallingClass()` | 同名类在两个命名空间；或 `controller_class_map` 里有映射 |
| `assignRewrite()` 不生效 | 键是否带前导 `/` | 必须写 `'/legacy'`（[第 2-2 章](routing.md)） |
| `route_map` 里 `Class::method` 无效 | 回调写法 | 只支持 `Class@method` / `Class->method` / callable |
| 子应用永远接不到请求 | 入口 URL | 缺 `controller_url_prefix`，或父应用先抢到了（[第 3-2 章](mount-app.md)） |
| 每条请求都跑两遍控制器 | 是否装了中间件 | [`Ext\MyMiddlewareManager`](../reference/Ext-MyMiddlewareManager.md) 的短路坑：不调 `$next` 就 return 会让 `Route::run()` 再跑一次默认回调（[第 2-10 章](lifecycle.md)） |

### B. 输出与视图

| 症状 | 看什么 | 大概率原因 / 改法 |
|---|---|---|
| 白屏、无输出 | 日志 + `Route::_()->getRouteError()` | 路由没命中（走 404 分支）；或视图 include 失败 |
| 视图找不到 | 视图名 vs `path_view` | 视图名 = 相对 `view/` 的路径；根路径省略视图名时会找 `Main/index`（[第 2-4 章](views.md)） |
| 视图里变量全是 `null` 且不报错 | `view_skip_notice_error` 默认 true | 变量没传进 `Show()`；调试期可临时关掉该选项 |
| `Helper::Show('main', $data)` 白屏 | 参数顺序 | 真实签名 `Show($data = [], $view = '')` |
| 页眉页脚不出现 | 是否在构造函数里 `setViewHeadFoot()` | 头尾视图名同样相对 `path_view`；`Render()` **不带**头尾 |
| `header()` 报「已发送输出」 | 输出时序 | 先发头再输出；或开 `use_output_buffer`（注意语义变化） |
| 页面出现 `Maintaining.` / `Internal Error` 占位 | `is_maintain` / 没配 `error_*` | 配 `error_maintain`/`error_500` 视图（[第 2-11 章](exception.md)） |
| 生产环境看到堆栈 | `is_debug`（含子应用）与 setting | `App::_IsDebug()` 是「设置 or 根 or 本应用」的**或**；错误视图里用 `__is_debug()` 包调试块 |

### C. 覆盖、多应用与容器（「谁赢」类问题）

| 症状 | 看什么 | 大概率原因 / 改法 |
|---|---|---|
| 覆盖类没生效 | `PhaseContainer::Dump()` | 调用处用了 `new` 而不是 `::_()`；覆盖机制依赖容器（[第 3-5 章](overriding.md)） |
| 覆盖视图没生效 | 视图查找顺序 | 覆盖是**按相位回退**；确认父应用目录名与子应用相位名一致（[第 3-5 章](overriding.md)） |
| 子应用拿到了父应用的实例 | `dumpAllObject()` 的 `#public` 桶 | 该组件是共享的（Root 初始化时标 public）；要独立用 `createLocalObject()` 或 `local_database`/`local_redis`（[第 3-4 章](component-sharing.md)） |
| 改了共享组件，另一个应用也变了 | 同上 | 预期行为；要么接受，要么各自本地化 |
| `ext` 里加了扩展但没反应 | `EXT_*` 取值 | `EXT_DISABLE` 就是关着；`EXT_RENEW` 每次请求重建（[第 4-1 章](container-phases.md)） |
| 子应用输出 404 而不是交给父应用 | `skip_404` | 子应用里调 `App::_()->skip404Handler()`（[第 2-10 章](lifecycle.md)） |

### D. 配置与设置

| 症状 | 看什么 | 大概率原因 / 改法 |
|---|---|---|
| 选项传了但不生效 | 该组件声明的 `$options` | 组件初始化会 `array_intersect_key` 过滤：**没声明的键被丢掉**（[第 1-5 章](configuration.md)、[第 4-2 章](custom-component.md)） |
| `Helper::Setting()` 拿不到值 | 设置文件名与装载时机 | 文件是 `config/DuckPhpSettings.config.php`；settings 只在根应用 `onPrepare()` 装载（[第 1-5 章](configuration.md)） |
| 改后仍走旧配置 | 是否开了持久化 | `data_file_enable` 会把 ext 选项落成 JSON，清掉它或改来源 |
| 前台/后台 302 到安装页 | `installed` | [`UserControllerBase`](../reference/Foundation-Controller-UserControllerBase.md)/[`AdminControllerBase`](../reference/Foundation-Controller-AdminControllerBase.md) 构造函数会检查；置 `true`（[第 3-6 章](installer.md)） |
| 子应用的设置被父应用覆盖 | `Setting()` 读的是**根应用** | 子应用要自己的值就放选项，或别用 Setting |

### E. 数据库与缓存

| 症状 | 看什么 | 大概率原因 / 改法 |
|---|---|---|
| 连不上 / DSN 报错 | `Helper::DatabaseDriver()` + [`Helper::Db()->PDO()`](../reference/Db-Db.md) | DSN 拼错；`database_list` 与 `database` 混用（[第 2-5 章](database.md)） |
| `no such table` | `table_prefix` 与 SQL 里的表名 | 用 `` `'TABLE'` `` 宏，别手写表名（[第 2-5 章](database.md)） |
| `execute()` 返回 0 | 语义 | 成功=受影响行数、失败=0；`UPDATE` 无变化也 0 |
| 读库看不到刚写的数据 | 读写分离 | 需要强一致时显式用写连接（[第 2-5 章](database.md)） |
| [`Helper::Cache()->set()`](../reference/Component-Cache.md) 返回 false | 是否装了 [`RedisCache`](../reference/Component-RedisCache.md) | 没装时 `Cache` 是空实现（永远不缓存、但也不报错）（[第 2-13 章](cache.md)） |
| 两个应用缓存互相覆盖 | `redis_cache_prefix` | 各应用配不同前缀 |
| 分页总数不对 | 计数 SQL | 用 `Helper::SqlForCountSimply()` |

### F. CLI 与定时任务

| 症状 | 看什么 | 大概率原因 / 改法 |
|---|---|---|
| [`(xxx)Command Not Found In All`](../reference/Component-Command.md) | 命令是否注册 | `regConsoleCommand()`；方法名 `command_` 前缀（[第 2-15 章](cli.md)） |
| 命令在本地能跑、crontab 不能 | 工作目录与 php 路径 | `cd /srv/myproj && /usr/bin/php cli.php …` |
| `--key=value` 取不到 | 参数结构 | 键名不带 `--`；位置参数在 `['--']`（[第 2-15 章](cli.md)） |
| 子应用命令调不到 | 命令组前缀 | 用 `子应用名:命令` |

### G. 会话、用户与安全

| 症状 | 看什么 | 大概率原因 / 改法 |
|---|---|---|
| Session 写了读不到 | `session_prefix` 与启动时机 | 前缀不同＝不同键；[`SessionTrait`](../reference/Foundation-Controller-SessionTrait.md) 首次读写时才 `session_start()`（[第 2-9 章](session.md)） |
| 登录后仍显示未登录 | [`GlobalUser`](../reference/GlobalUser-GlobalUser.md) 的回调配置 | `globaluser_login_session` 等三个必需键要对（旧名 `user_callback_for_session` / `user_callback_get_*` 都已失效）（[第 4-12 章](impl-user.md)） |
| 后台菜单为空 | 控制器的注释 | `@menu_directory` 等注解缺失；[`PermissionMenu`](../reference/Ext-PermissionMenu.md) 只收可导航节点（参考页） |
| 输出里有未转义的用户数据 | 视图里的 `<?=` | 一律 `__h()` |
| 上线后有人能看后台 | 越权判断 | 权限体系只管「是不是管理员」，**资源归属要自己在 Business 判断**（[第 2-17 章](security-performance.md)） |

### H. 国际化、事件

| 症状 | 看什么 | 大概率原因 / 改法 |
|---|---|---|
| 一直显示原 key | `lang_final` 与语言文件名 | 规范化后要是 `zh_CN` 这种；文件放 `config/lang/`（[第 2-14 章](i18n.md)） |
| `?lang=xx` 没反应 | `lang_final` 已算好 | 运行期改 `$_GET` 无效；在 `onPrepare` 期或直接配 `lang_final` |
| 事件监听不到 | [`GlobalEvent`](../reference/Component-GlobalEvent.md) 是否开启 | 默认 `EXT_DISABLE`，要在 `ext` 里打开（[第 2-12 章](events.md)） |
| 事件回调里 `::_()` 拿错实例 | 相位绑定 | `fire()` 会切到**注册时**的相位执行（[第 2-12 章](events.md)） |

### I. 性能（按这个顺序查，通常停在第一步）

| 顺序 | 动作 | 预期收获 |
|---|---|---|
| 1 | 确认 `is_debug = false`、OPcache 已开 | 最常见、收益最大 |
| 2 | 开 `database_log_sql_query`，看是否有重复/全表扫描 | 找出 N+1 与缺索引 |
| 3 | 热数据加缓存（`Helper::Cache()`）并设 TTL | 直接减库压（[第 2-13 章](cache.md)） |
| 4 | 关掉用不上的能力：`path_info_compact_enable`、`data_file_enable`、多余的 `ext` | 减少每次请求的固定开销 |
| 5 | 路由映射规则排序：常用在前，能用精确匹配就别用正则 | 减少匹配次数 |
| 6 | 检查日志量与轮转（异常日志默认开） | 避免磁盘与 IO 拖慢 |
| 7 | 才考虑「换常驻方案」（框架内置的 [`HttpServer`](../reference/HttpServer-HttpServer.md) 只适合开发/内网） | —— |

### J. 测试与工具链

| 症状 | 看什么 | 大概率原因 / 改法 |
|---|---|---|
| Windows 下 redis 用例假失败 | 扩展缺失 | 一律 WSL（[第 2-16 章](testing.md)） |
| `ZAllDemoTest` 的 `files` 变红 | 输出字节长度 | 源码改动导致方法表/选项表/行号变化——按实际值更新期望（[第 2-16 章](testing.md)） |
| 覆盖率一直 0 | `XDEBUG_MODE=coverage` | 见[第 4-7 章](coverage.md) |
| `gen-reference.php verify` 报「多了方法」 | 已知脚本缺陷 | 以漂移扫描为准（[第 4-8 章](doc-maintenance.md)） |

## 常见写法

**① 三行拿证据（先做这个，再谈改代码）**

```php
Route::_()->getRouteError();                  // 路由层事实
\DuckPhp\Core\PhaseContainer::Dump();         // 容器/相位层事实
var_dump(array_key_exists('我的选项', App::_()->options));   // 选项层事实
```

**② 二分法定位「哪一段出的问题」**

- 换个最简单的路径（如 `/`）能不能出页面？能 ⇒ 问题在你的路由/控制器；不能 ⇒ 问题在初始化或环境；
- 起一个最小入口（`demo/public/helloworld.php` 那种）能不能跑？能 ⇒ 框架没问题，是你的工程配置；
- 用 `php cli.php routes` 看路由表里有没有你期望的项 ⇒ 没有就是配置/注册的问题。

**③ 用日志代替打断点**

```php
__debug_log('path=%s opts=%s', $path, json_encode(App::_()->options));
// 输出到 runtime/ 下的日志（第 1-6 章）
```

**④ 找出「谁改了它」**

- 钩子顺序：`RouteHookManager::_()->dump()`；
- 文件被覆盖：`App::_()->getOverrideableFile('view', 'main.php')` 打印实际命中的文件（[第 3-5 章](overriding.md)）；
- 实例被替换：`PhaseContainer::Dump()` 对比类名（[第 4-1 章](container-phases.md)）。

**⑤ 把结论固化成测试**

复现 → 写进 `tests/`（把 bug 临时改回去确认测试会红）→ 再修（[第 2-16 章](testing.md)）。这是本仓库的硬纪律：没有测试的"修好了"不算修好。

## 常见错误（排查方法本身）

| 现象 | 原因 | 改法 |
|---|---|---|
| 一上来就翻源码 | 没有先拿证据 | 先用 §工具箱里的三个调用把「事实」打出来 |
| 改了 `src/` 里「看起来该改」的地方 | 没确认是不是刻意的 | 先查[第 4-10 章](design-notes.md)与两份维护指南的陷阱表——很多行为是刻意设计 |
| 在 Windows 下排查 redis/测试问题 | 环境不对 | 换 WSL 复现再判断 |
| 只看业务日志 | 框架的警告/notice 被忽略 | 开发期把 `is_debug` 打开、错误级别调高（[第 1-6 章](debugging.md)） |
| 反复重启服务而不看日志 | 猜测式排查 | 按本表逐项验证，每步只改一个变量 |

## 下一步

- [第 4-10 章 设计取舍与已知坑](design-notes.md)：哪些行为是刻意的、哪些是真坑。
- [第 2-17 章 安全与性能清单](security-performance.md)：上线前的检查表。
- [第 4-7 章 测试基建与覆盖率流水线](coverage.md)：把排查结论固化成测试。
