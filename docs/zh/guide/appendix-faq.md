# 附录 D · FAQ 与排错索引

> 用途：**带着问题来**时先查这里。上半部分是按主题的 FAQ（一句话答案 + 去哪一章），下半部分是按症状的索引（细节在[第 4-9 章 排错手册](../guide/troubleshooting.md)）。

## 一、FAQ

### 入门与选型

| 问题 | 答案 | 去哪看 |
|---|---|---|
| DuckPHP 适合什么项目？ | 想要「零依赖、单文件也能跑、又能长成多应用」的 PHP 项目；不适合指望 ORM/生态开箱即用的团队 | [第 1-1 章](../guide/intro.md) |
| 必须用 Composer 吗？ | 不必须。框架自带的 [`AutoLoader`](../reference/Core-AutoLoader.md) 能自己找类，根 `autoload.php` 就是最小加载器 | [第 4-4 章](../guide/embed.md) |
| 能不能塞进一个老项目里只加一个页面？ | 能：一个入口文件 + `RunQuickly()`，或用 [`DuckPhpAllInOne`](../reference/DuckPhpAllInOne.md) 单文件形态 | [第 4-4 章](../guide/embed.md) |
| 有 ORM / 迁移工具 / 队列吗？ | 都没有。数据访问用 [`ModelTrait`](../reference/Foundation-Model-ModelTrait.md) + 手写 SQL；建表 SQL 自己放安装流程；队列自己接 | [第 2-7 章](../guide/database.md) |
| 支持 PHP 版本？ | `>=7.4`（仓库同时在 7.4 与 8.4 容器里跑测试） | [第 4-7 章](../guide/coverage.md) |
| 和 Laravel 的心智模型差在哪？ | 没有 PSR-7/15、没有注入容器、没有注释路由；换来相位隔离与覆盖机制 | [附录 C](appendix-migration.md) |

### 结构与规范

| 问题 | 答案 | 去哪看 |
|---|---|---|
| 一定要写 Business 层吗？ | 框架不强制，但**不写就等于放弃**覆盖/多应用/可测性；业务规则只有一份才有意义 | [第 2-1 章](../guide/layers.md) |
| 控制器里能不能直接查数据库？ | 不能（越界）。查询放 Model，规则放 Business | [第 2-1 章](../guide/layers.md) |
| `Helper` 和全局函数怎么选？ | 视图里用全局函数（`__h`/`__url`/`__l`），PHP 代码里用对应层的 `Helper::` | [第 2-9 章](../guide/helper.md) |
| `::_()` 是什么？ | 可变单例入口：`Xxx::_()` 取实例、`Xxx::_($new)` 替换实例 | [第 4-1 章](../guide/container-phases.md) |
| 为什么某层 Helper 没有我要的方法？ | 那是边界提示：该事不该在这一层做 | [第 2-9 章](../guide/helper.md) |

### 路由与视图

| 问题 | 答案 | 去哪看 |
|---|---|---|
| URL 怎么映射到控制器？ | 最后一段是方法名，前面是类路径，类名自动补 `Controller` 后缀；单段路径落到欢迎类的方法 | [第 2-4 章](../guide/routing.md) |
| 怎么把 URL 绑到指定类@方法？ | `route_map_important`（优先）或 `route_map`（兜底），回调写 `Class@method` | [第 2-4 章](../guide/routing.md) |
| 旧链接怎么兼容？ | `assignRewrite('/old', 'new/path')`——**键必须带前导 `/`** | [第 2-4 章](../guide/routing.md) |
| 视图文件放哪、怎么被找到？ | `view/<视图名>.php`，查找按相位逐层回退（可被父应用覆盖） | [第 2-6 章](../guide/views.md) |
| 怎么加统一的页眉页脚？ | 控制器构造函数里 `Helper::setViewHeadFoot('header', 'footer')` | [第 2-6 章](../guide/views.md) |
| 输出只有「渲染视图」一种吗？ | 四种：视图、`Render()` 取字符串、`ShowJson()`、直接 `echo`；另有 `Show302`/`Show404` | [第 2-5 章](../guide/controllers.md) |
| 怎么防 XSS？ | 所有输出用 `__h()`（翻译+转义用 `__hl()`） | [第 2-6 章](../guide/views.md) |

### 数据

| 问题 | 答案 | 去哪看 |
|---|---|---|
| 数据库怎么配？ | `database`（单条）或 `database_list`（多条/读写分离）；密码走 settings | [第 2-7 章](../guide/database.md) |
| 表前缀怎么处理？ | SQL 里写 `` `'TABLE'` `` 宏，执行前替换成「前缀+表名」 | [第 2-7 章](../guide/database.md) |
| 为什么 `ModelTrait` 的 `add()` 不能直接调？ | 它是 `protected`（刻意）：对外暴露什么由你自己开 public 方法 | [第 2-8 章](../guide/model.md) |
| 分页怎么做？ | 模型返回 `[$total, $list]`，视图用 `Helper::PageHtml($total)` | [第 2-7 章](../guide/database.md) |
| 事务怎么写？ | [`Helper::Db()->PDO()`](../reference/Db-Db.md) 上 `beginTransaction/commit/rollBack`；注意同一连接 | [第 2-7 章](../guide/database.md) |
| 缓存怎么生效？ | 默认 [`Cache`](../reference/Component-Cache.md) 是空实现；装 [`RedisCache`](../reference/Component-RedisCache.md) 才有缓存（`Helper::Cache()` 用法不变） | [第 2-14 章](../guide/cache.md) |

### 横切：钩子、事件、中间件

| 问题 | 答案 | 去哪看 |
|---|---|---|
| 想在请求前后做事，用哪个？ | 拦截用路由钩子（pre 链返回真值）；前后置装饰用中间件 | [第 2-3 章 路由钩子](../guide/route-hooks.md) |
| 中间件里 `return` 了响应怎么没生效？ | 已知坑：短路无效，控制器照样执行；要拦截请用钩子 | [第 2-3 章 路由钩子](../guide/route-hooks.md) |
| 想在所有控制器前做点什么，要继承基类吗？ | 先考虑钩子；继承会把可变能力变成血缘 | [第 2-3 章 路由钩子](../guide/route-hooks.md) |
| 事件和钩子区别？ | 事件是广播（无返回值、可跨相位）；钩子是单链（有返回值、可短路） | [第 2-13 章](../guide/events.md) |
| 怎么知道谁挂了钩子、顺序如何？ | [`RouteHookManager::_()->dump()`](../reference/Ext-RouteHookManager.md) | [第 2-3 章 路由钩子](../guide/route-hooks.md) |

### 多应用与运维

| 问题 | 答案 | 去哪看 |
|---|---|---|
| 一个进程跑多个应用怎么做到的？ | 相位（相位名 + 容器分桶），子应用挂在 `app` 选项里 | [第 3-1 章](../guide/advanced-phase.md) |
| 组件在应用之间共享吗？ | 根应用初始化的部分组件是共享的（`#public` 桶）；要独立就 `local_*` 或 `createLocalObject()` | [第 3-4 章](../guide/component-sharing.md) |
| 怎么替换框架某个行为？ | 覆盖类/文件/单例/系统包装，看「想改什么 → 动哪里」的决策表 | [第 4-3 章](../guide/replace-behavior.md) |
| 生产环境怎么部署？ | 文档根指向 `public/`，rewrite 到入口；关 `is_debug`；`runtime/` 可写 | [第 1-7 章](../guide/deployment.md)、[第 2-18 章](../guide/security-performance.md) |
| 内置 HTTP 服务器能上生产吗？ | 不能，只适合开发/内网 | [第 4-5 章](../guide/http-server.md) |
| 怎么加定时任务？ | 写 `command_xxx()` 命令，crontab 里 `cd 项目目录 && php cli.php xxx` | [第 2-16 章](../guide/cli.md) |

### 测试与文档

| 问题 | 答案 | 去哪看 |
|---|---|---|
| 测试怎么跑？ | 测试一律在 WSL/容器里跑；日常只跑单个文件 | [第 2-17 章](../guide/testing.md) |
| 为什么 Windows 下测试失败？ | Windows PHP 没有 redis 扩展等环境差异 | [第 2-17 章](../guide/testing.md) |
| 覆盖率数据在哪？ | 单类 dump 在 `test_coveragedumps/`，报告在 `test_reports/index.html` | [第 4-7 章](../guide/coverage.md) |
| 改了源码，文档要同步改哪些？ | 参考页（方法/选项表）+ 指南相关章 + 必要的维护记录 | [第 4-8 章](../guide/doc-maintenance.md) |
| 站内链接怎么保证不坏？ | `python3 docs/scripts/check-doc-links.py docs/zh`，未写的章不挂链接 | [第 4-8 章](../guide/doc-maintenance.md) |

## 二、按症状索引

细节步骤在[第 4-9 章 性能调优与排错手册](../guide/troubleshooting.md)，这里只做「症状 → 先看哪」。

| 症状 | 先去哪 | 最可能的原因 |
|---|---|---|
| 404 | [第 2-4 章](../guide/routing.md) §7 | 前缀不符（E001）/ 类不存在（E003）/ 单段路径规则 |
| 路由命中了错的控制器 | [第 3-5 章](../guide/overriding.md) | `controller_class_map`、同名类、覆盖顺序 |
| 白屏 | [第 4-9 章](../guide/troubleshooting.md) §B | 视图找不到 / 路由没命中 / include 失败 |
| 视图里变量是 `null` | [第 2-6 章](../guide/views.md) | 没传进 `Show()`；`view_skip_notice_error` 掩盖了 notice |
| 覆盖没生效 | [第 3-5 章](../guide/overriding.md) | 用了 `new`；相位名不匹配 |
| 拿到别的应用的单例 | [第 3-4 章](../guide/component-sharing.md) | 组件是共享的（`#public`） |
| 选项传了没反应 | [第 1-5 章](../guide/configuration.md) | 白名单没声明 / 拼错键 / `data_file_enable` 覆盖 |
| 一直跳安装页 | [第 3-6 章](../guide/installer.md) | `installed` 为 `false` |
| 生产看到堆栈 | [第 2-18 章](../guide/security-performance.md) | `is_debug`（含子应用）或 `duckphp_is_debug` |
| 连不上数据库 / 表不存在 | [第 2-7 章](../guide/database.md) | DSN、表前缀宏、目录权限 |
| 分页数字不对 | [第 2-7 章](../guide/database.md) | 计数 SQL 带了 limit |
| 缓存不生效 | [第 2-14 章](../guide/cache.md) | 没装 `RedisCache`（默认空实现） |
| Session 读不到 | [第 2-11 章](../guide/session.md) | `session_prefix` 不一致；未启动 |
| 登录后还是未登录 | [第 2-19 章](../guide/user.md) | `globaluser_login_session` 键名/相位不对（旧名 `user_callback_for_*` 已失效） |
| 命令找不到 | [第 2-16 章](../guide/cli.md) | 没注册 / 方法名缺 `command_` / 缺命令组前缀 |
| 事件监听不到 | [第 2-13 章](../guide/events.md) | [`GlobalEvent`](../reference/Component-GlobalEvent.md) 默认关闭（`EXT_DISABLE`） |
| 翻译不生效 | [第 2-15 章](../guide/i18n.md) | 语言文件名与 `lang_final` 不匹配 |
| 页面慢 | [第 4-9 章](../guide/troubleshooting.md) §I | 调试开关、OPcache、SQL、缓存、多余 ext |
| 子目录部署后链接 404 | [第 2-4 章](../guide/routing.md) | 手写绝对路径，没用 `__url()` |
| 测试在 Windows 上假失败 | [第 2-17 章](../guide/testing.md) | 环境差异（redis 等），换 WSL |
| `ZAllDemoTest` 的 `files` 变红 | [第 2-17 章](../guide/testing.md) | 输出字节长度随源码变化，更新期望值 |
| 文档链接校验报坏链 | [第 4-8 章](../guide/doc-maintenance.md) | 指向了未写的页；未写的用「纯文本 + ⏳」 |
| 参考页与源码不一致 | [第 4-8 章](../guide/doc-maintenance.md) | 只改源码没改文档；跑漂移扫描 |

## 三、还是找不到？

1. **查参考手册**：`docs/zh/reference/` 一类一页，另有 `options.md`（选项速查）与 `index.md`（目录）；
2. **查维护指南的陷阱表**：`docs/zh/reference-maintenance-guide.md` §7、`docs/zh/guide-maintenance-guide.md` §5；
3. **确认是不是刻意设计**：[第 4-10 章 设计取舍与已知坑](../guide/design-notes.md)；
4. **仍然卡住**：按[第 4-10 章](../guide/design-notes.md) §3 的流程做「是 bug 还是设计」的判断，并把结论补进维护指南的陷阱表（这样下一个人不用再踩）。
