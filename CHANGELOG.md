# 更新日志

本文件记录**用户可见**的变化：破坏性变更、新增、改进、修复。版本号沿用 `主.次.修订`。

> 1.4.0 未单独发版，**1.4.1 是 1.4 系列的首个正式版本**，因此下面的 1.4.1 一节覆盖相对 1.3.5 的全部变化。
> 1.3.x 及更早的历史见 [README 的「版本历程」](README.zh_CN.md)；本文件从 1.4.1 起建立。
> English version is in the second half of this file.

## 1.4.1（2026-10-17）

### 破坏性变更（升级前请先读这一节）

**删除的类**（共 34 个）：

- `DuckPhp\Ext\MiniRoute`、`DuckPhp\Ext\Misc` —— 作者判定无人使用 / 不需要
- `DuckPhp\Ext\StrictCheck`、`DuckPhp\Ext\FinderForController`、`DuckPhp\Component\RouteHookCheckStatus`、`DuckPhp\Component\ZCallTrait`
- `DuckPhp\FastInstaller\*` 整个命名空间（`FastInstaller`、`DatabaseInstaller`、`RedisInstaller`、`Supporter`、`SupporterBy{Mysql,Pgsql,Sqlite}`）—— 安装能力由 `DuckPhp\Ext\DuckPhpInstaller` 与 `DuckPhp\Ext\RouteHookWebInstaller` 承担
- `DuckPhp\GlobalAdmin\AdminException`、`DuckPhp\GlobalUser\UserException` —— 登录/权限语义改用 `Admin` / `User` 上的 `EXCEPTION_CODE_*` / `EXCEPTION_MESSAGE_*` 常量，未登录由 `throwLoginOn()` 走 302 / Ajax JSON / 自定义回调后 `exit()`
- `DuckPhp\Foundation\Simple{Business,Controller,Model,Session,Singleton,Exception}Trait`、`DuckPhp\Foundation\{Business,Controller,Model,System}\Helper`、`DuckPhp\Foundation\ExceptionReporterTrait`、`DuckPhp\Helper\{App,Business,Controller,Model}HelperTrait` —— `Foundation\*` 与 `Helper\*` 两族在 1.4 重组，替代物见下面的「新增」

**改名的类**：

| 旧 | 新 |
| --- | --- |
| `DuckPhp\Core\SingletonTrait` | `DuckPhp\Core\SingletonExTrait` |
| `DuckPhp\Core\EventManager` | `DuckPhp\Ext\EventManager` |
| `DuckPhp\Core\ThrowOnTrait` | `DuckPhp\Ext\ThrowOnTrait` |
| `DuckPhp\FastInstaller\SqlDumper` | `DuckPhp\Ext\SqlDumper`（配套 `Ext\SqlDumperSupporter*`） |
| `DuckPhp\Foundation\Model\Helper` | `DuckPhp\Foundation\Model\ModelHelper` |
| `DuckPhp\Helper\ModelHelperTrait` | `DuckPhp\Foundation\Model\ModelHelperTrait` |
| `DuckPhp\Helper\ControllerHelperTrait` | `DuckPhp\Foundation\Controller\ControllerHelper` |
| `DuckPhp\Helper\AppHelperTrait` | `DuckPhp\Foundation\System\SystemHelper` |
| `DuckPhp\Foundation\Controller\Helper` | `DuckPhp\Foundation\Controller\ActionBase` |
| `DuckPhp\Foundation\SimpleModelTrait` | `DuckPhp\Foundation\Model\ModelTrait` |
| `DuckPhp\Foundation\SimpleSessionTrait` | `DuckPhp\Foundation\Controller\SessionTrait` |
| `DuckPhp\Foundation\SimpleExceptionTrait` | `DuckPhp\Foundation\SingletonTrait` |

**行为变更**：

- `View` 的同族命名统一：`setViewHeadFoot()` → `setViewHeaderFooter()`，相关 `*_head` / `*_foot` 选项与技术术语一并改成 `header` / `footer`
- `PhaseContainer` 的 `public` 术语全部改为 `shared`（`#public` → `#shared` 等），影响 dump 文案
- `ExtOptionsLoader` 的根路径改为强制打开（运行时可改的选项落在 `runtime/DuckPhpData.config.json`）
- `Lang` 的语言文件名改为 `lang-<locale>.php`（不再用 `lang-<locale>.php` 之外的写法），新增 `lang_frags` 与缺句告警开关
- `Logger` 改回 `EXT_DEFAULT` 装配（应用选项里的日志设置终于生效）
- `GlobalAdmin` / `GlobalUser` 默认不自动加载，统一由 `DuckPhp` 的 `class_admin` / `class_user` 选项启用

### 新增

- 用户系统：`DuckPhp\GlobalUser\User`、`UserActionInterface`、`UserLoginActionInterface`、`UserLoginServiceInterface`、`UserServiceInterface`、`UserSessionInterface`、`UserSessionTrait`，配套 `DuckPhp\Foundation\Controller\UserControllerBase`
- 管理系统：`DuckPhp\GlobalAdmin\Admin`、`AdminActionInterface`、`AdminLoginActionInterface`、`AdminLoginServiceInterface`、`AdminServiceInterface`、`AdminSessionInterface`、`AdminSessionTrait`，配套 `DuckPhp\Foundation\Controller\AdminControllerBase`
- 扩展：`DuckPhp\Ext\RouteLister`、`DuckPhp\Ext\RouteHookWebInstaller`（+ `RouteHookWebInstallerView`）、`DuckPhp\Ext\PermissionMenu`（+ `PermissionMenuMetaInterface`）、`DuckPhp\Ext\DuckPhpInstaller`
- 组件：`DuckPhp\Component\GlobalEvent`、`DuckPhp\Component\Validator`、`DuckPhp\Component\CommandMetaInterface`
- `DuckPhp\HttpServer` 增强（含 Windows 兼容）
- `demo/` 多语言化：默认英文，`?lang=` / cookie / `Accept-Language` 可切到 `zh_CN`，语言文件在 `demo/config/lang-en.php`、`lang-zh_CN.php`

### 改进

- **文档**：中文用户指南 47 章 + 4 附录；中文参考手册 112 篇（107 篇逐类 + 5 篇生成页：`index`/`options`/`options-by-class`/`options-index`/`setting`）；英文平行树同名对应；生成的选项页与架构图（`docs/duckphp.gv[.svg]`）由 `src/` 直接生成，并配一套校验脚本（断链、英文树、选项、架构图、骨架树、孤儿页、`src/` ASCII、README markdownlint）
- **测试**：95 个用例、892 断言、`src/` 行覆盖率 **4737/4737（100%）**
- `php-cs-fixer fix --dry-run src` → 0 处需修；`composer validate` 通过

### 修复

- `__url('?x')` / `__url('#x')` 在根路径下拼成 `//?x`（双斜杠，链接失效）—— 现在只保留一个斜杠
- `Pager::getDefaultPageNo()` 违背 `int` 返回类型（`?page=abc` 时 `TypeError` 导致整页 500）
- `RestAllContainerForTesting` 子类化后失效
- `MyMiddlewareManager` 的中间件短路拦不住请求
- `HttpServer` 在 Windows 下的兼容问题
- `Logger` 的应用选项不生效
- `SqlDumperSupporter` 的默认驱动映射漏了 pgsql
- 安装器：生成的 CLI 入口指向不存在的 App 类等 4 处连带问题
- `demo/`：`/dbtest.php` 在 PHP 8 下 500、`/db_test/` 与 `/cover_test.php` 404、`show?id=`（库里没有这行）500

### 发布/环境

- `composer.json` 的 `post-install-cmd` 原来写在顶层（非法 schema，Packagist 拒收），已移入 `scripts`
- `docker/test-php74` 的 `apt-get install` 曾因 Debian bullseye-security 撤源而 404，Dockerfile 已改指 `archive.debian.org` 并关掉 `Valid-Until` 校验；两份 compose 给 Composer 子进程放宽了超时（`COMPOSER_PROCESS_TIMEOUT`）
- 去掉 `composer.json` 里与 tag 重复的 `version` 字段（版本由 git tag 决定），并把 2018 年的占位描述换成一句真实描述（`composer validate` 现在零警告）

## Changelog (English)

This file records **user-visible** changes: breaking changes, additions, improvements, fixes.

> 1.4.0 was never released on its own — **1.4.1 is the first official release of the 1.4 line**, so the
> 1.4.1 section below covers everything since 1.3.5. History before that is in
> [the README's version history](README.md); this file starts at 1.4.1.

### 1.4.1 (2026-10-17)

#### Breaking changes

- **34 classes removed**, including `Ext\MiniRoute`, `Ext\Misc`, `Ext\StrictCheck`, `Ext\FinderForController`,
  `Component\RouteHookCheckStatus`, `Component\ZCallTrait`, the whole `FastInstaller\*` namespace (replaced by
  `Ext\DuckPhpInstaller` and `Ext\RouteHookWebInstaller`), `GlobalAdmin\AdminException` / `GlobalUser\UserException`
  (login/permission semantics now use the `EXCEPTION_CODE_*` / `EXCEPTION_MESSAGE_*` constants on `Admin` / `User`
  plus `throwLoginOn()`), and the `Foundation\Simple*Trait`, `Foundation\{Business,Controller,Model,System}\Helper`
  and `Helper\*HelperTrait` families (the `Foundation\*` layer was reorganized).
- **Renamed classes**: `Core\SingletonTrait` → `Core\SingletonExTrait`, `Core\EventManager` → `Ext\EventManager`,
  `Core\ThrowOnTrait` → `Ext\ThrowOnTrait`, `FastInstaller\SqlDumper` → `Ext\SqlDumper` (with
  `Ext\SqlDumperSupporter*`), `Foundation\Model\Helper` → `Foundation\Model\ModelHelper`,
  `Helper\ModelHelperTrait` → `Foundation\Model\ModelHelperTrait`, `Helper\ControllerHelperTrait` →
  `Foundation\Controller\ControllerHelper`, `Helper\AppHelperTrait` → `Foundation\System\SystemHelper`,
  `Foundation\Controller\Helper` → `Foundation\Controller\ActionBase`, `Foundation\SimpleModelTrait` →
  `Foundation\Model\ModelTrait`, `Foundation\SimpleSessionTrait` → `Foundation\Controller\SessionTrait`,
  `Foundation\SimpleExceptionTrait` → `Foundation\SingletonTrait`.
- **Behavior changes**: `View::setViewHeadFoot()` → `setViewHeaderFooter()` (and the `*_head` / `*_foot` option
  names/terms became `header` / `footer`); `PhaseContainer`'s `public` terminology became `shared`;
  `ExtOptionsLoader`'s root path is now always enabled; `Lang` language files are `lang-<locale>.php` with new
  `lang_frags` and missing-key warnings; `Logger` goes back to `EXT_DEFAULT` wiring (log options from the app
  finally take effect); `GlobalAdmin` / `GlobalUser` are no longer loaded automatically — enable them with the
  `class_admin` / `class_user` options of `DuckPhp`.

#### Added

- User system: `GlobalUser\User`, `UserActionInterface`, `UserLoginActionInterface`, `UserLoginServiceInterface`,
  `UserServiceInterface`, `UserSessionInterface`, `UserSessionTrait`, plus `Foundation\Controller\UserControllerBase`.
- Admin system: `GlobalAdmin\Admin`, `AdminActionInterface`, `AdminLoginActionInterface`,
  `AdminLoginServiceInterface`, `AdminServiceInterface`, `AdminSessionInterface`, `AdminSessionTrait`, plus
  `Foundation\Controller\AdminControllerBase`.
- Extensions: `Ext\RouteLister`, `Ext\RouteHookWebInstaller` (+ `RouteHookWebInstallerView`),
  `Ext\PermissionMenu` (+ `PermissionMenuMetaInterface`), `Ext\DuckPhpInstaller`.
- Components: `Component\GlobalEvent`, `Component\Validator`, `Component\CommandMetaInterface`.
- A strengthened `DuckPhp\HttpServer` (including Windows compatibility).
- A multilingual `demo/`: English by default, `zh_CN` via `?lang=` / a cookie / `Accept-Language`
  (`demo/config/lang-en.php`, `lang-zh_CN.php`).

#### Improvements

- **Docs**: 47 guide chapters + 4 appendices in Chinese; 112 reference pages (107 per-class + 5 generated pages);
  a parallel English tree with identical file names; the option pages and the architecture diagram
  (`docs/duckphp.gv[.svg]`) are generated from `src/`, backed by a set of gate scripts (broken links, English
  tree, options, architecture diagram, skeleton tree, orphan pages, `src/` ASCII, README markdownlint).
- **Tests**: 95 cases, 892 assertions, `src/` line coverage **4737/4737 (100%)**.
- `php-cs-fixer fix --dry-run src` → 0 files to fix; `composer validate` passes.

#### Fixed

- `__url('?x')` / `__url('#x')` produced `//?x` at the root (a double slash that broke the link).
- `Pager::getDefaultPageNo()` violated its `int` return type (`TypeError` → HTTP 500 on `?page=abc`).
- `RestAllContainerForTesting` broke when subclassed.
- `MyMiddlewareManager` could not short-circuit a request from middleware.
- `HttpServer` compatibility on Windows.
- `Logger` app options did not take effect.
- `SqlDumperSupporter`'s default driver map missed pgsql.
- The installer generated a CLI entry pointing at a non-existent App class (and 4 related issues).
- `demo/`: `/dbtest.php` returned 500 on PHP 8, `/db_test/` and `/cover_test.php` returned 404, and `show?id=`
  for a missing row returned 500.

#### Release / environment

- `composer.json` had `post-install-cmd` at the top level (invalid schema, rejected by Packagist); it now lives
  under `scripts`.
- `docker/test-php74`'s `apt-get install` started failing with 404s because Debian retired bullseye-security;
  the Dockerfile now points at `archive.debian.org` and disables the `Valid-Until` check, and both compose files
  give Composer sub-processes a longer timeout (`COMPOSER_PROCESS_TIMEOUT`).
- Removed the `version` field from `composer.json` (the git tag is authoritative) and replaced the 2018 placeholder
  description with a real one — `composer validate` is now warning-free.
