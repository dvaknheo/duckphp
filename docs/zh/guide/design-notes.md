# 4-10 设计取舍与已知坑

> 解决什么问题：分清「框架**故意**这么做」和「这确实是个坑」。前者别去"修"，后者要知道怎么绕——排查时先看这一章，能省掉一半瞎猜。
> 前置：[第 2-1 章 四层架构与调用规范](layers.md)、[第 4-9 章 性能调优与排错手册](troubleshooting.md)。预计 20 分钟。

## 最小示例

遇到「怪行为」时的判断顺序（照这个走，基本不会误改框架）：

```php
// 1) 先拿事实，不要先猜
Route::_()->getRouteError();                 // 路由层
\DuckPhp\Core\PhaseContainer::Dump();        // 容器/相位层
var_dump(array_key_exists('我的选项', App::_()->options));  // 选项有没有活下来

// 2) 查是不是刻意设计：本章 §1 / §2 + src 里的注释与 TODO
// 3) 是刻意的 → 按设计用；是真坑 → 绕开并记进维护指南
```

## 机制说明：刻意的设计取舍（别去“修”）

| 设计                                                                | 为什么这么做                                                      | 代价（你得知道）                                                                                                                                                     |
| ----------------------------------------------------------------- | ----------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| **四层只是约定，不强制**                                                    | 没有 AOP/字节码增强，运行期零魔法；覆盖与替换都靠"类/文件/单例"这些朴素手段                  | 越界不报错，只是覆盖、多应用、测试复用会悄悄失效（[第 2-1 章](layers.md)）                                                                                                               |
| **相位 + 可变单例 `::_($new)`**                                         | 一个进程里跑多个应用、且能整体替换实例；实现简单、调试直观                               | 全局可变状态；测试与常驻场景要自己清理（[第 4-1 章](container-phases.md)）                                                                                                          |
| **选项白名单（`array_intersect_key`）**                                  | 组件只接受自己声明的键，避免拼错键被静默接受                                      | **拼错的键会被直接丢掉**，现象是"我传了但没生效"（[第 1-5 章](configuration.md)）                                                                                                     |
| **`$hidden_options` 只作文档元数据**                                     | 有些键框架确实会读（如 `session_prefix`），但不想让它们出现在选项表里                 | 它**不参与 `$options` 合并**：不再有默认值占位，读取点必须写成 `?? 默认值`（源码注释里写明）                                                                                                    |
| **Helper 按层切分**                                                   | 用「方法不存在」表达边界，比文档里的规矩更硬（[第 2-9 章](helper.md)）                | 想跨层调用时会"找不到方法"——那是提示，不是缺功能                                                                                                                                   |
| **不跟 PSR 标准（无 PSR-7/15）**                                         | 保持零依赖、可单文件嵌入；请求/响应就是数组与简单对象                                 | 中间件只是**兼容层**（[`Ext\MyMiddlewareManager`](../reference/Ext-MyMiddlewareManager.md) 的 request 默认是 `stdClass`），别拿 Laravel/PSR 的心智模型硬套（[第 2-4 章](route-hooks.md)） |
| **路由 = 约定 + 钩子链**                                                 | URL 直接映射到 `Controller\Xxx::method`，特例靠钩子                    | 没有注解路由；复杂 URL 用 `route_map`（[第 2-3 章](routing.md)）                                                                                                           |
| **系统调用统一走 [`SystemWrapper`](../reference/Core-SystemWrapper.md)** | `header`/`setcookie`/`exit`/`session_start` 可替换 ⇒ 测试与常驻进程可跑 | 直接写原生函数会绕过包装，测试里就拦不住（[第 2-17 章](testing.md)）                                                                                                                 |
| **错误页默认输出占位文本**                                                   | 框架不猜你的 UI，但保证"不泄漏堆栈"（`is_debug` 为假时）                        | 不配 `error_404`/`error_500` 就会看到英文占位（[第 2-12 章](exception.md)）                                                                                                |
| **settings 与 options 两套机制**                                       | 前者放密码/环境（只读、来自文件），后者是代码里的白名单配置                              | `Setting()` 读的是**根应用**的值；子应用想要自己的值就得用选项（[第 1-5 章](configuration.md)）                                                                                         |
| **不内置 ORM / CSRF / 任务调度 / 模板引擎**                                  | 保持体积与可控性；这些各有主流方案                                           | 你得自己选型与实现（[第 2-18 章](security-performance.md)）                                                                                                               |

## 常见错误：已知坑（真问题，知道就好）

| 坑                                                                                      | 现象                                                                                                                      | 现状 / 怎么绕                                                                                                                                                |
| -------------------------------------------------------------------------------------- | ----------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **中间件短路无效**                                                                            | 中间件里不调 `$next` 直接 `return` 响应：返回值被丢弃，[`Route::run()`](../reference/Core-Route.md) 会**再跑一次默认回调** ⇒ 控制器照样执行；没有控制器时表现为 404 | 实测确认（作者裁定保持现状，作为兼容层的既有语义）。**要真正拦截就用路由钩子并在 pre 链返回真值**（[第 2-4 章](route-hooks.md)）                                                                         |
| `ZAllDemoTest` 比字节长度                                                                   | 改完 `src/` 它就红                                                                                                           | 它比的是 demo 输出的**字节长度**，而 `files` 页会 dump 选项表/方法表/调用栈行号 ⇒ 任何方法增删、行号漂移、**选项在 `$options` 与 `$hidden_options` 之间搬家**都会改长度。按实际值更新期望即可（[第 2-17 章](testing.md)） |
| demo/bin 里存在**死选项**                                                                    | 传了没反应                                                                                                                   | `demo/public/dbtest.php` 的 `cli_command_prefix`、`bin/duckphp` 的 `cli_command_classes` 在 `src/` 里没有任何读取点                                                 |
| `demo/src/Controller/Commands.php` 引用了不存在的 trait                                       | 一旦被加载就致命错误                                                                                                              | 它 `use DuckPhp\Foundation\CommonCommandTrait;`，而该 trait 在当前 `src/` 中不存在——**别照抄这个样板**（[第 2-16 章](cli.md)）                                                |
| `demo/src/Model/CrossModelEx.php` 名不副实                                                 | 以为是"跨库模型"范例                                                                                                             | 里面只有一个 `foo()` 空壳；跨库请用 [`Helper::Db($tag)`](../reference/Db-Db.md)（[第 2-8 章](model.md)）                                                                 |
| [`RouteHookRouteMap`](../reference/Component-RouteHookRouteMap.md) 不支持 `Class::method` | 静态字符串回调不生效                                                                                                              | `adjustCallback()` 里 `::` 分支被注释掉了；用 `Class@method` 或 `Class->method`                                                                                    |
| `Route` 里遗留的 `controller_prefix_post = 'do_'`                                          | 看选项表会疑惑                                                                                                                 | 源码里带 `//TODO remove it`；实际生效的是 `controller_method_prefix`                                                                                               |
| `Route::forceFail()` 的语义未完成                                                            | 想强制本次路由失败                                                                                                               | 源码里就一句 `// TODO . force result ?`；目前只能配合 `defaultToggleRouteCallback(false)` 之类的组合（[第 2-4 章](route-hooks.md)）                                            |
| `view_skip_notice_error` 默认 `true`                                                     | 视图里未定义变量不报警，页面安静地少一块                                                                                                    | 开发期可临时关掉该选项来找 bug（[第 2-6 章](views.md)）                                                                                                                  |
| 根路径省略视图名会找 `Main/index`                                                                | 报视图找不到                                                                                                                  | 根路径的路由路径是欢迎类补出来的；显式传视图名（[第 2-6 章](views.md)）                                                                                                            |
| 子应用的 `is_debug` 是「或」关系                                                                 | 生产环境看到堆栈                                                                                                                | `_IsDebug()` = 设置 or 根 or 本应用；上线前**全局搜 `'is_debug' => true`**（[第 2-18 章](security-performance.md)）                                                      |
| `gen-reference.php verify` 会漏列方法                                                       | 把正确文档误报成"多了方法"                                                                                                          | trait 别名 override 的大文件（如 `Core/App.php`）；判一致性以漂移扫描为准（[第 4-8 章](doc-maintenance.md)）                                                                     |
| 在 `docker/test-php74/` 里找不到 `.sh` 脚本                                                   | 起停脚本只放在 `docker/test-php84/` 里                                                                                          | 用 8.4 那套脚本，或直接用 `docker-compose` 起 php74（[第 4-7 章](coverage.md)）                                                                                        |

## 常见写法：判断流程与改框架的纪律

### 「这是不是 bug」的判断流程

1. **拿证据**：`getRouteError()` / [`PhaseContainer::Dump()`](../reference/Core-PhaseContainer.md) / 打印生效的 `options`；
2. **查本仓库的陷阱表**：两份维护指南都有「已知坑」表，本章 §2 是总集；
3. **查源码注释与 TODO**：`$hidden_options` 的注释、`//TODO remove it`、`// TODO . force result ?` 这类都说明了作者的态度；
4. **看它有没有测试**：有测试且断言了这个行为 ⇒ 大概率是刻意的（改它就要改测试，且要想清楚影响面）；
5. **仍然认为是 bug**：先写一个能复现的测试（并把 bug 改回去验证它真的红），再改源码；**改完必须同步** `tests/` + `docs/zh/reference/` + `docs/zh/guide/`。

> 反例警示：[`Command::getCommandListInfo()`](../reference/Component-Command.md) 里有个"读了不用"的 `$phase`（重构残留）。这种**能清就清源码**；而像 `Root($switch_phase)` 内部硬编码 [`App::Phase`](../reference/Core-App.md) 这种**刻意行为**才写进文档并注明"以源码为准"。

### 改框架时的纪律（本仓库约定）

| 纪律               | 说明                                                                                       |
| ---------------- | ---------------------------------------------------------------------------------------- |
| `src/` 必须纯 ASCII | 改完跑 `bash docs/scripts/check-non-ascii.sh`，期望 `Total non-ASCII lines: 0`（中文只出现在 `docs/`） |
| 改了 `src/` 就跑测试   | 日常跑单个文件；提交前全量（[第 4-7 章](coverage.md)）                                                    |
| 公共名字改动要全仓同步      | `src/` + `tests/` + `docs/zh/reference/` + `docs/zh/guide/`，改完 `grep` 残留 = 0             |
| 重命名要连"清理语句"一起改   | 例如 `App::__construct()` 里对选项表的处理语句，改名不彻底会留下隐形行为差异                                        |
| 文档不写"将来会怎样"      | 只写"现在是什么"，并给依据（源码文件 + 行号）                                                                |

## 下一步

- [第 4-9 章 性能调优与排错手册](troubleshooting.md)：症状 → 排查路径。
- [第 4-11 章 过时与冷门的扩展类](deprecated-exts.md)：`src/Ext/` 里带 `@todo deprecate` 的类，以及"能用但不在推荐路径上"的冷门页。
- [第 4-8 章 文档与参考手册维护](doc-maintenance.md)：改完怎么同步文档。
- [参考手册维护指南](../reference-maintenance-guide.md)：参考手册侧的完整维护流程与陷阱表。
