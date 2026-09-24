# 用户指南重写 · 进度 Checklist

> 配套阅读：[用户指南重写 · 交接与维护指南](guide-maintenance-guide.md) —— 那份讲**怎么做**（目标、约束、写作模板、校验命令、已知坑），本文件只管**做到哪了**。
>
> **勾选纪律**：一章只在「按模板重写完 + 示例实跑过 + 校验命令通过」之后，才把 ⏳/📝 改成 ✅ 并勾选；只改错别字、只调格式不算完成。
>
> 图例：**✅ 已完成** ｜ **📝 文件在，内容仍旧，待改写** ｜ **⏳ 未写**

## 里程碑

- [x] **M0** 一页总目录 + 站点首页瘦身 + 附录 A 术语表 + `ZThirdDemo` 示例工程与冒烟测试
- [x] **M1** 第三卷 3-1–3-7（7 章，含示例被测试兜底）
- [x] **M2** 第二卷 2-1–2-17（17 章：11 篇改写 + 6 篇新写）
- [x] **M3** 第一卷 1-1–1-7（2 篇新写 + 5 篇改写）
- [x] **M4** 第四卷 4-1–4-10 + 附录 B/C/D（已全部落稿；全局函数参考由 `reference/Core-Functions.md` 承接，见「参考手册侧」小节）
- [x] **M5** 收尾：删掉被吸收的 `architecture.md` / `components.md` 与 guide 侧两篇附录；`reference/index.md` 登记；全量校验（**已完成**，见下）
- [x] **M6** 章号形式统一为**卷-章号**（`1-1`…`4-10`）+ 类名首次出现链接参考手册 + 指南里不再提旧文档的过时内容

---

## 第一卷 · 入门 —— ✅ 已完成

- [x] 1-1 DuckPHP 是什么 —— `intro.md`（含与 Laravel/Yii2/CI 的定位对比）
- [x] 1-2 安装与最小示例 —— `install.md`（两条安装路线 + 3 文件起步）
- [x] 1-3 目录结构与编码规则 —— `project-structure.md`（含四层调用铁律矩阵）
- [x] 1-4 第一个页面 —— `quickstart.md`（便签列表，五层走通）
- [x] 1-5 配置与设置 —— `configuration.md`（options vs settings 的分工与白名单坑）
- [x] 1-6 调试、日志与 CLI 初体验 —— `debugging.md`
- [x] 1-7 上线最小清单 —— `deployment.md`（含内置服务器 router 写法，实测过）
- [x] 本卷收尾校验：`docs/zh` 站内链接 **1005 条 0 死链**、`docs/zh` 153 篇 md 全 UTF-8、7 章 **101–226 行**（≤400）、全量 `php vendor/bin/phpunit --no-coverage` → **`OK (92 tests, 556 assertions)`**

## 第二卷 · 单一应用 —— ✅ 已完成

> 章序按「先立规范 → 再走请求路径 → 再补横切能力 → 最后框架机制与进阶」重排（作者裁定）。
> **章号形式**：全库统一用**卷-章号**——第一卷 `1-1`–`1-7`、第二卷 `2-1`–`2-19`（2-9 会话 + 末尾 2-18 用户体系 / 2-19 管理员体系）、第三卷 `3-1`–`3-7`、第四卷 `4-1`–`4-10`，附录 A/B/C/D。**单数字章号已废除**（历史上第二卷曾用 8–24 的单数字，与最初 9/14/15/16/17 的排法做过一次置换）。
> 「中间件与钩子链」**不再单独成章**（中间件只是兼容性扩展，不是主推能力）——已并入 2-10 作为一节，其余章顺次前移；全书共 43 章 + 4 附录（第 5 轮把原 2-9「会话与用户/管理员体系」拆成 2-9 会话 + 2-18 用户体系 + 2-19 管理员体系）。

- [x] 2-1 四层架构与调用规范 —— `layers.md`（226 行：本卷导读 + 五层职责 + 越界矩阵 + Helper 分层 + 全局函数表）
- [x] 2-2 路由进阶 —— `routing.md`（221 行：默认规则表、PATH_INFO 来源、`__url()` 规则、重写与路由映射、错误码速查）
- [x] 2-3 控制器 —— `controllers.md`（204 行：四种输出方式、输入表、`get_defined_vars()` 惯用法、Action）
- [x] 2-4 视图与模板 —— `views.md`（187 行：视图定位与相位回退、头→视图→尾、转义、三种替换引擎）
- [x] 2-5 数据库 —— `database.md`（242 行：连接选项表、读写分离、`` `'TABLE'` `` 宏、事务、分页、SqlDumper）
- [x] 2-6 模型层 —— `model.md`（259 行：`ModelTrait` 成员表、「CRUD 为什么是 protected」、表名推导、跨库 tag 写法）
- [x] 2-7 Helper 与全局函数 —— `helper.md`（第 5 轮重写为 198 行：四层四个类对照表、`extends` / `use … as Helper` 两种写法、并集 `__callStatic`、全局函数全表）
- [x] 2-8 表单与验证 —— `validator.md`（180 行：三种口径、`valid/check/filter`、skipEmpty、未知规则即抛）
- [x] 2-9 会话 —— `session.md`（137 行：SessionTrait 三方法、session_prefix 隔离、会话里放什么、会话≠登录态）
- [x] 2-18 用户体系（第 5 轮从 2-9 拆出，放在卷二末尾）—— `user.md`（198 行：GlobalUser 接入（xt）、选项表、登录/注册/登出流程、UserSessionTrait、未登录与登录后视图）
- [x] 2-19 管理员体系（同上）—— `admin.md`（185 行：GlobalAdmin、canAccess/log/isSuper、AdminSessionTrait、后台菜单 PermissionMenu）
- [x] 2-10 请求生命周期与钩子点 —— `lifecycle.md`（293 行：init 六步 + serve 时序 + 钩子六位置 + 内置钩子位置表 + 中间件兼容节 + HookChain + 选型表）
  - [x] 17 附节「兼容性扩展：洋葱中间件」：`Ext\MyMiddlewareManager` 配置与四种写法 + 实测洋葱输出 + **短路无效的坑**（按作者裁定保持源码现状，当坑写）
  - [x] 17 附节「钩子链」：`Route::addRouteHook()` 六位置与短路语义、内置钩子位置清单、`RouteHookManager` 增删改排序与 `dump()`、`Ext\HookChain`
- [x] 2-11 异常与错误处理 —— `exception.md`（207 行：`DuckPhpSystemException` 只给框架内部用的铁律 + 错误页三分支 + 报告器）
- [x] 2-12 事件系统 —— `events.md`（127 行：`GlobalEvent` 相位绑定、`$EVENT_*` 常量全表、与钩子的对比表）
- [x] 2-13 缓存与 Redis —— `cache.md`（137 行：`Cache` 空实现降级、`RedisCache` 自动替换、`local_redis`、失效策略）
- [x] 2-14 国际化与文案 —— `i18n.md`（123 行：`__l()` 调用链、五级检测顺序、占位翻译与回落链、`lang_handler`）
- [x] 2-15 命令行与定时任务 —— `cli.md`（217 行：内置七命令、`regConsoleCommand()`、命令组前缀、参数解析、crontab）
- [x] 2-16 测试 —— `testing.md`（151 行：WSL、`data_for_tests` 约定、`system_wrapper_replace`/`PathInfo` 可测性、`LibCoverage` 与 `@codeCoverageIgnore` 语义）
- [x] 2-17 安全与性能清单 —— `security-performance.md`（181 行：框架做了/不提供两张表 + 安全清单 16 项 + 性能清单 10 项 + 常见写法）
- [x] 本卷收尾校验：`docs/zh` 站内链接 **1281 条 0 死链**、17 章全部 **≤400 行**（123–293）、示例全部指向现成 `demo/`／`ZAllDemo`／`ZThirdDemo`、`demo/cli.php help|routes|DbTestApp:version` 实跑过、`regConsoleCommand()` 注册命令用临时脚本实测过

## 第三卷 · 使用第三方应用 —— ✅ 已完成

- [x] 3-1 应用树与相位基础 —— `advanced-phase.md`
- [x] 3-2 把外部应用挂进来 —— `mount-app.md`
- [x] 3-3 静态资源与文档根 —— `static-resources.md`
- [x] 3-4 组件共享与应用间通信 —— `component-sharing.md`
- [x] 3-5 重写与覆盖 —— `overriding.md`
- [x] 3-6 安装器与 Web 安装流程 —— `installer.md`
- [x] 3-7 综合实战：前台 + 后台 + API —— `case-multi-app.md`
- [x] 示例工程 `tests/data_for_tests/ZThirdDemo` + `tests/ZThirdDemoTest.php`（36 断言）

## 第四卷 · 高级话题 —— ✅ 已完成

- [x] 4-1 容器与相位内部机制 —— `container-phases.md`（126 行：`PhaseContainer` 分桶/查找、`EXT_*` 五值、`#public` 桶、`Dump()` 排错）
- [x] 4-2 开发组件与扩展 —— `custom-component.md`（161 行：组件 vs 扩展、`$options` 白名单、`initOptions/initContext`、替换点）
- [x] 4-3 替换框架行为 —— `replace-behavior.md`（181 行：五种替换层次、`override_class`/`controller_class_map`/`SingletionExTrait::_($new)`、`SystemWrapper` 十个可替换函数、优先级与排查）
- [x] 4-4 无 Composer·单文件·内嵌 —— `embed.md`（153 行：`AutoLoader` 用法、根 `autoload.php`、`DuckPhpAllInOne`、真实单文件入口）
- [x] 4-5 常驻进程与内嵌 HTTP —— `http-server.md`（164 行：`HttpServer` 选项/短参数/起停、`php -S` 本质、workers 与 RPC 回环、状态残留、生产别用）
- [x] 4-6 多入口·多域名·多 SAPI —— `multi-entry.md`（152 行：`demo/public/*` 各入口、分流点、多站点、子目录部署）
- [x] 4-7 测试基建与覆盖率流水线 —— `coverage.md`（154 行：phpunit.xml/bootstrap/support.php 三段、LibCoverage、`test_coveragedumps/`、docker 双版本、生成器脚本）
- [x] 4-8 文档与参考手册维护 —— `doc-maintenance.md`（158 行：三层文档结构、漂移扫描、改名同步清单、0 死链约定、不提交清单）
- [x] 4-9 性能调优与排错手册 —— `troubleshooting.md`（206 行：工具箱 + A–J 十类症状表 + 排查方法本身的坑）
- [x] 4-10 设计取舍与已知坑 —— `design-notes.md`（80 行：刻意设计 11 条 vs 已知坑 13 条 + 「是不是 bug」判断流程 + 改框架纪律）

## 附录 —— ✅ 已完成

- [x] A 术语表 —— `appendix-glossary.md`
- [x] B 代码片段库 —— `appendix-snippets.md`（288 行：CRUD/分页/表单校验/JSON/登录/权限/缓存/事务/跨应用/钩子/HTTPS/CSRF/上传/定时任务/建表，⚠️ 标注了示意片段）
- [x] C 从 Yii2 / CodeIgniter / Laravel 迁移 —— `appendix-migration.md`（123 行：概念对照大表 + 一个功能的写法对照 + 迁移步骤 + 易踩差异）
- [x] D FAQ 与排错索引 —— `appendix-faq.md`（117 行：分主题 FAQ 30+ 条 + 症状索引表 + 「还是找不到」的路径）

## 参考手册侧（作者在新对话完成，本任务只等结果）

- [x] 全局函数参考：**不新建 `GlobalFunctions.md`**，由现成的 `docs/zh/reference/Core-Functions.md`（208 行，含全集签名表）承接（作者裁定）
- [x] 重建 `docs/zh/reference/options.md` / `options-by-class.md` / `options-index.md`（已由作者侧完成；本轮复查 `class_admin`/`class_user`/`FastInstaller` 残留 **= 0**）
- [x] M5 收尾（本轮完成）：
  - [x] 删除 `docs/zh/guide/appendix-global-functions.md` 与 `docs/zh/guide/appendix-options.md`（原文可用 `git show <提交>:docs/zh/guide/appendix-options.md` 取回）
  - [x] 把指向它们的链接改到参考手册：`helper.md`、`layers.md` → `../reference/Core-Functions.md`
  - [x] `docs/zh/guide/index.md` 底部那行改成精确链接（Core-Functions / options 三页）
  - [x] `configuration.md` 里本来就没有附录链接（原文假设有，实际无需改）
  - [x] 删除已被吸收的 `docs/zh/guide/architecture.md`（532 行）与 `components.md`
  - [x] `reference/index.md` 已登记 `Core-Functions.md`（无需新增登记项）

## 待决策（勾掉即已定，定完请写进交接指南对应节）

- [x] **Q1 写作顺序**：先做第二卷 2-1–2-17（章序与章号已定，见上方「第二卷」小节；Q2 的拆解顺序也随之确定）
- [x] **Q2 长章拆解**：`layers.md` 已拆完（581 → 226 行）；`architecture.md` 内容被第 32/17/8 章吸收后**已删除**；`components.md` 被第 4-2 章吸收后**已删除**
- [x] **Q3 `skeleton/` 的失真内容** —— 已修（本轮）：
  - `skeleton/agent-zh.md` 的 `class_user`/`class_admin` → 改为真实选项 `user_provider`/`admin_provider` + `user_callback_for_*` 回调写法，并补上真实接口名（`UserActionInterface`/`UserServiceInterface`/`UserSessionInterface`/`UserLoginActionInterface`/`UserLoginServiceInterface`）；
  - `skeleton/RULES.md`：方法前缀默认值由错写的 `action_` 改成 **空串**（并注明 1.3.6 起的变化）、`/Main/index` 示例改为「默认被拒（E009），需 `controller_welcome_class_visible`」、关键约定表补一行；
  - 顺带查出并修掉**更大的失真**：`skeleton/src/System/ProjectException.php` 与 `demo/src/System/ProjectException.php` 只 `use ExceptionTrait` **没有 `extends \Exception`** ⇒ 抛它时致命错误；现已 `extends \Exception`（并加回归测试，见下）；
  - `skeleton/src/System/App.php` 里 `cmd` 选项的注释示例由 `[CommandAction::class]` 改成 `[CommandAction::class => true]`（真实形态是「类名 => 方法前缀/true」）。
- [x] **Q4 新发现的源码残留** —— 已确认修好并**补了防回归测试**：`DuckPhp::_Show()` 里的死表达式已在提交 `9635a77b`（"文档推进"）中删除，现在 `App::_Show()` 是 `$view = ($view === '') ? Route::_()->getRouteCallingPath() : $view;`（正确赋值）。本轮在 `tests/DuckPhpTest.php` 加了断言：把 `Route::_()->calling_path` 设为 `block` 后调 `_Show($data, '')` 必须渲染出 `view/block.php`——把 bug 改回去跑，测试会以 `ValueError: Path cannot be empty` 变红（已实测），再还原。

## 每轮收尾自检（每卷结束时勾一遍；下表为**M5 收尾**时的实测值）

- [x] `python3 docs/scripts/check-doc-links.py docs` → `docs/zh` 0 坏链（M5 收尾实测：**1622 条 0 死链**；`docs/old/`、`docs/en/index.md` 的历史死链不属本任务，`docs/en/` 有自己的同名副本不受本次删除影响）
- [x] 新增/修改的 md 全是 UTF-8（无 GBK）（`docs/zh` 逐字节校验通过）
- [x] 本卷新写的章都 ≤400 行（第四卷 10 章 **80–206 行**；附录 B/C/D 117–288 行；全书 41 章 7001 行）
- [x] 改过的示例都在 WSL 里实跑通过（各卷示例仍挂在 `demo/`、`ZAllDemo`、`ZThirdDemo` 上；第四卷新增片段均为现有资产的真实引用或明确标注 ⚠️ 示意）
- [x] 全量 `php vendor/bin/phpunit --no-coverage` 通过（当前基线 `OK (93 tests, 565 assertions)`——Q3/Q4 各补了回归断言后的新基线；原基线为 `92/556`）
- [x] 本文件的复选框与状态图例已更新

---

## 全部里程碑状态

| 里程碑 | 状态 |
|---|---|
| M0 一页总目录 + 站点首页瘦身 + 附录 A + `ZThirdDemo` | ✅ |
| M1 第三卷 3-1–3-7 | ✅ |
| M2 第二卷 2-1–2-17 | ✅ |
| M3 第一卷 1-1–1-7 | ✅ |
| M4 第四卷 4-1–4-10 + 附录 B/C/D | ✅ |
| M5 收尾（删被吸收旧文、参考手册侧迁入、全量校验） | ✅ |
| M6 章号改卷-章形式 + 类名首现链参考手册 + 清掉过时内容 | ✅ |
| M7 第 4 轮重构后的指南同步（Helper 章重写 + `__logined_enable_view` 机制改写 + 各章旧名/旧选项） | ✅ |
| M8 新增 4-11「过时与冷门的扩展类」+ 参考页孤儿清零（`find-unmentioned-classes.py` 反查 → 0） | ✅ |
| M9 `RouteHookApiServer` 选项改名 `apiserver_*`（含 `src/` 与指南） | ✅ |
| M10 落实 `helper.md` 的两条 `//TODO`（工程 Helper 的动态方法 + 静态覆盖） | ✅ |
| M11 跟随 `GlobalAdmin`/`GlobalUser` 重写同步指南（2-18/2-19 重写、2-12 事件常量表、3-5 视图级开关、附录 B 登录片段、2-11 登录/权限表述） | ✅ |
| Q1–Q4 待决策 | ✅ 全部已定并落地 |

> **M11 收尾实测**（2026-09-24，详见 [用户指南维护指南](guide-maintenance-guide.md) §20）：`check-doc-links.py docs/zh` → **2109 条 0 死链**；`checkchap.py`（本轮临时脚本）→ 「链接文字章号 vs 目标 H1」**0 处不一致**、`index.md` 之外无缺章号 H1；`find-unmentioned-classes.py` → **109/109 类页全被链到、孤儿 0**；改动章行数 117–294（全部 ≤400）；`docs/zh/guide` 里同步轮留下的 `//TODO` **清零**；全量测试 `OK (96 tests, 823 assertions)`、覆盖率 `4895/4895`。

> **用户指南重写任务至此完成**：`docs/zh/guide/` = `index.md` + 41 章 + 4 附录（章号 `1-1`…`4-10`，单数字形式已废弃）；`docs/zh` 站内链接 **1973 条 0 死链**、全 UTF-8；每个类名的首次出现都链到参考手册（新增 359 处，代码块内不插链接）；全量测试绿。三条新写作约定见 [用户指南维护指南](guide-maintenance-guide.md) §1 第 6/7/8 条；M6 的踩坑与校验结果见其 §14 与 [参考手册维护指南](reference-maintenance-guide.md) 第 14 条。
