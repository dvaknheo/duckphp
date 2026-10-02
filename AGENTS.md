# AGENTS.md —— DuckPHP 框架本体仓库

> 读者：在这个仓库（**框架本体**，不是用户工程）里干活的 AI / 工程师。**先读本文件**，需要细节再跳两篇维护指南。
> ⚠️ 别和 [`skeleton/AGENTS.md`](skeleton/AGENTS.md) 搞混：那份是**发给用户工程**的约定（目录树、命名后缀、分层与越界、加功能四步），随包发给使用者；本文件只管"怎么维护这个仓库"。

## 0. 这是什么

| 项 | 值 |
|---|---|
| 项目 | DuckPhp（前身 DNMVCS），零依赖、全组件可替换的**库模式** PHP 框架 |
| Composer 包 | `dvaknheo/duckphp` |
| PHP 要求 | `>= 7.4.0`（**写代码别用 8.x 语法**；实测环境是 PHP 8.2） |
| 框架本体 | `src/`（纯 ASCII，见 §3） |
| 测试 | `tests/`（PHPUnit 9.6，`phpunit.xml` 开了 `processIsolation`） |
| 文档 | `docs/zh/`（**事实来源**）+ `docs/en/`（同名的平行树） |
| 演示 / 骨架 | `demo/`（又是活文档又是 E2E 被测宿主）、`skeleton/`（生成用户工程的模板） |
| 命令行 / 容器 | `bin/duckphp`、`docker/test-php74`、`docker/test-php84` |

## 1. 环境前提（最容易卡住的地方；2026-10-02 实测整理）

**一律在 WSL 里跑**（Windows 侧 PHP 没有 redis 扩展，redis 相关用例会假失败）。仓库在 Windows 是 `E:\ProjectGoat\DNMVCS`，WSL 里是 `/mnt/e/ProjectGoat/DNMVCS`，命令都在仓库根执行。

1. **`vendor/` 必须和 `composer.json` 对得上**。本仓**不提交 `composer.lock`**（库项目不锁依赖），本机依赖是 `composer update` 装出来的。正确版本：`phpunit 9.6.37`、`php-code-coverage 9.2.32`、`dvaknheo/libcoverage 1.0.8`、`nikic/php-parser 5.x`。
   版本错位时的典型报错 → 说明什么：

   | 报错 | 缺/错的东西 |
   |---|---|
   | `Call to undefined method LibCoverage\LibCoverage::_()` | libcoverage 太旧（< 1.0.8） |
   | `Class "PHP_Token_AMPERSAND_FOLLOWED_BY_VAR_OR_VARARG" not found` | `php-token-stream` 太旧；`covagg.php` 与 `tests/support.php` 会读不出任何 dump |
   | `Class "PhpParser\ParserFactory" not found` | 缺 `nikic/php-parser`（`gen-architecture-gv.php` 要用） |
   | `Class "Redis" not found` / `RedisException: Connection refused` | 见下一条 |

2. **全量测试还要 redis**：`apt-get install -y php8.2-redis redis-server`，再
   `redis-server --requirepass 123456 --save "" --appendonly no --daemonize yes`
   ——端口/口令与 `tests/data_for_tests/setting.php` 的 `redis_list` 一致（`docker/test-php74` 的 compose 也是这么起的）。缺这两样会打红 `RedisCacheTest`/`RedisManagerTest`/`DuckPhpTest`/`Ext/RouteHookWebInstallerTest`。

3. **全量用 `XDEBUG_MODE=coverage php vendor/bin/phpunit`**：`phpunit.xml` 是进程隔离，子进程里的 `LibCoverage` 需要 xdebug 的 coverage 模式；本机 `xdebug.mode` 默认是 `develop`，所以**不要**用 `--no-coverage` 跑全量（会让一批用例在子进程里报 `Code coverage needs to be enabled`）。

4. `vendor/`、`composer.lock`、`test_reports/`、`test_coveragedumps/`、`backup/` 都在 `.gitignore` 里——**别提交它们**。

## 2. 目录

```
src/           框架本体（Core / Component / Ext / Foundation / Db / Helper / GlobalAdmin / GlobalUser / HttpServer + 入口类）
tests/         PHPUnit 用例 + tests/data_for_tests/ 夹具；tests/bootstrap.php 起 LibCoverage
demo/          示例应用：多入口 + 多语言（config/lang-en.php、lang-zh_CN.php）+ 被 ZAllDemoTest 端到端跑
skeleton/      用户工程骨架（含 skeleton/AGENTS.md，随包发给用户）
docs/zh|en/    中文（事实来源）与英文平行树：guide/ 52 章、reference/ 112 篇
docs/scripts/  文档生成器与门禁脚本（§5）
docker/        php74 / php84 的完整测试容器
bin/duckphp    安装器 CLI（`./vendor/bin/duckphp new`）
```

## 3. 代码约定

- **PHP >= 7.4**：不要用 8.x 语法（`match`、枚举、构造器属性提升、`?->` 等）。
- **`src/` 保持纯 ASCII**：注释、字符串一律英文。提交前 `bash docs/scripts/check-non-ascii.sh`，期望 `Total non-ASCII lines: 0`。
- **分层单向**：`System → Controller → Business → Model`；组件放 `src/Component/`（可替换），扩展放 `src/Ext/`（**不自动装配**，要用得写进 `ext`）。详细规范（面向用户工程）见 `skeleton/AGENTS.md`。
- **覆盖率是硬指标**：`src/` 必须 100%（当前 `4737/4737`）。改了/加了代码，就要在 `tests/` 里给出**真的会执行到它**的断言；改之前先跑一遍看它是绿的（红绿验证），别只看"测试通过"。
- 改 `src/` 后的顺手动作：`php -l <file>` → `check-non-ascii.sh` → 跑受影响用例 → 动过类关系/类名时 `php docs/scripts/gen-architecture-gv.php --check`。

## 4. 测试与覆盖率

| 目的 | 命令 |
|---|---|
| 全量（权威） | `XDEBUG_MODE=coverage php vendor/bin/phpunit`（约 6 分钟） |
| 单个文件 | `XDEBUG_MODE=coverage php vendor/bin/phpunit tests/Core/RouteTest.php` |
| 不碰覆盖率的用例 | `php vendor/bin/phpunit --no-coverage tests/ZAllDemoTest.php` |
| 覆盖率聚合判定 | `php docs/scripts/covagg.php` → `TOTAL 80 files, 86 dumps, lines …`，`0 files with gaps` |
| HTML 报告 | `XDEBUG_MODE=coverage php vendor/bin/phpunit tests/support.php` → `test_reports/index.html` |

**当前基线（2026-10-02 WSL 实测）**：`OK (95 tests, 889 assertions)`；覆盖率 `80 files, 86 dumps, 4737/4737 (100.00%)`。
数字会随用例增加而变——**以 `docs/zh/reference-maintenance-guide.md` §8 的基线为准**，那里还记着"跑全量的环境前提"。

**`ZAllDemoTest` 是字节长度比对**：它起内置服务器 + `curl` 各路由，逐一比**输出长度**。改 demo 的可见文案、改 `src/` 里 dump 用到的文案/类名/路径，都会让它变红。红了的处理：
- 期望值在 `tests/data_for_tests/ZAllDemoTest.config.php`（注释里记着每次变化的来历），失败时实际内容会 dump 成 `tests/data_for_tests/ZAllDemoTest-<长度>.txt`，可 `diff` 新旧两份；
- 测试 URL 一律带 `?lang=en`（demo 是多语言的：默认英文，`zh_CN` 靠 `?lang=`/cookie/`Accept-Language` 检测），**别把语言钉死这一点去掉**，否则长度会随跑测机器的 `LANG`/头抖动；
- `files` 分区的期望长度同时受"根应用声明的选项数"和"被加载的文件清单"影响（`demo/config/lang-*.php` 也会进清单）。

**红灯怎么读**：先确认是不是上面 §1 的环境问题（5 个 redis/覆盖率相关用例），再怀疑自己的改动；把 `src/` 临时 stash 起来跑一遍可以快速区分"本来就红"还是"我改红的"（`git stash push -- src/` → 跑 → `git stash pop`；⚠️ 工作区里可能还有别的 AI 会话留下的 stash，`pop` 前先 `git stash list` 确认栈顶是自己那条）。

## 5. 文档（改中文就得同改英文）

- **中文是唯一事实来源**（`docs/zh/`），`docs/en/` 是**同名平行树**（52/52 章、112/112 篇），改中文必须在**同一次改动里**改英文；只有两篇维护指南是中文-only。翻译口径见 [`docs/en/TRANSLATION.md`](docs/en/TRANSLATION.md)。
- **生成页不许手改**；改 `src/` 或选项后重跑生成器：
  - `options.md` / `options-by-class.md` / `options-index.md` / `setting.md` / `index.md`：`php docs/scripts/gen-options-docs.php`（英文 `--lang=en`）；
  - 架构图：`php docs/scripts/gen-architecture-gv.php` → `docs/duckphp.gv`，再重渲染 `docs/duckphp.gv.svg`（渲染办法见参考手册维护指南 §5；这台机器没装 graphviz，用的是 WASM 版）。
- **提交前门禁**（都在仓库根跑，期望值见括号）：

  | 命令 | 期望 |
  |---|---|
  | `python3 docs/scripts/check-doc-links.py docs/zh` | `broken: 0` |
  | `python3 docs/scripts/check-en-docs.py --all` | `0 error`（`--missing` 只剩 2 篇维护指南） |
  | `php docs/scripts/gen-options-docs.php --check`（再跑一次 `--lang=en --check`） | `up to date` |
  | `php docs/scripts/gen-architecture-gv.php --check` | `docs/duckphp.gv is up to date` |
  | `python3 docs/scripts/check-skeleton-tree.py` | 一致（改过 `skeleton/` 必须跑） |
  | `python3 docs/scripts/find-unmentioned-classes.py` | `从没被链到: 0` |
  | `python3 docs/scripts/check-md-layout.py` | 报 `layout-only` 的可以 `git checkout --` 丢掉；`CONTENT` 才看 diff |
  | `bash docs/scripts/check-non-ascii.sh` | `Total non-ASCII lines: 0`（改过 `src/`） |

- ⚠️ **别拿 `php docs/scripts/gen-reference.php verify --all` 当一致性判据**：它要求方法条目行首是反引号，而本仓统一 4 空格缩进，会把几乎每篇都报成 `method missing in md`（假报上千条），还会漏掉真过期条目（见维护指南 §10）。

## 6. 提交与工作区卫生

- **按显式路径 `git add`，永远不要 `git add .`**：这会带上测试产物和作者的未跟踪文件（踩过：一次带进 52 个 `tests/**/runtime/*.log`，只能 `reset --soft` 重做）。
- 提交信息用**中文**，说清"改了什么 + 为什么"（历史过程写进 commit message，**不写进文档**）；涉及破坏性改动、基线变化、环境变化时把验证命令与结果一并写进去。
- **测试产物一律不提交**：`tests/data_for_tests/*/runtime/`、`tests/data_for_tests/ZAllDemoTest-*.txt`、`demo/runtime/`、`test_reports/`、`test_coveragedumps/`、`dump.rdb`。
- 工作区里有作者/别的 AI 会话留下的未跟踪文件（`github.ppk`、`docker/**/… - 副本*`、`docs/新建文本文档.txt`、`fix_iterable_types.php`、`path_of调整.txt`…）——与本任务无关就别动，需要动先问作者。
- 提交前 `git status --short` 复核；收尾确认"无未提交的已跟踪改动"。

## 7. 常见任务怎么走

| 任务 | 步骤与去处 |
|---|---|
| 改一个类 | `src/` → 对应 `docs/zh/reference/<模块>-<类>.md`（+ en 同名）→ 用例 → 门禁；长流程见参考手册维护指南 §6 |
| 改 / 加一章文档 | 用户指南维护指南 §2（模板）与 §6（标准动作）；章号用「卷-章号」，唯一事实来源是 `docs/zh/guide/index.md` |
| 删一个类 | 四处一起清：`src/` 类文件、`tests/` 用例、`docs/{zh,en}/reference/` 参考页、指南里讲它的段落（参考手册维护指南 §8） |
| 加/改一个选项 | `src/` 的 `$options` → 该类的参考页「选项」表 → `php docs/scripts/gen-options-docs.php`（zh + `--lang=en`） |
| 动 `demo/` | 记得 `ZAllDemoTest` 基线（§4）；可见文案要走 `__l()` + `demo/config/lang-*.php`（中文放 `lang-zh_CN.php`），注释保持英文 |
| 动脚手架 `skeleton/` | `python3 docs/scripts/check-skeleton-tree.py`；`skeleton/AGENTS.md` 是发给用户的唯一权威清单，别在别处再抄一份 |
| 动架构图 / 门禁脚本 | 参考手册维护指南 §5（脚本表）与 §9（冒烟自检） |

## 8. 权威文档索引

| 想知道 | 去哪 |
|---|---|
| 文档怎么写、怎么校验、章号怎么排 | [`docs/zh/guide-maintenance-guide.md`](docs/zh/guide-maintenance-guide.md) |
| 参考页怎么写、脚本怎么用、基线是多少 | [`docs/zh/reference-maintenance-guide.md`](docs/zh/reference-maintenance-guide.md) |
| 中译英的口径（术语表、版式、允许保留中文的文件） | [`docs/en/TRANSLATION.md`](docs/en/TRANSLATION.md) |
| 有哪些类、有哪些选项 | [`docs/zh/reference/index.md`](docs/zh/reference/index.md)（生成页） |
| 怎么用这个框架 | [`docs/zh/guide/index.md`](docs/zh/guide/index.md) |
| 项目介绍与联系方式 | [`README-zh.md`](README-zh.md) / [`README.md`](README.md) |
| 给用户工程的约定 | [`skeleton/AGENTS.md`](skeleton/AGENTS.md) |

## 9. 容器（换 PHP 版本时才需要动）

`docker/test-php74`（PHP 7.4）与 `docker/test-php84`（PHP 8.4）两份 compose 逐行对齐，各自起 redis + 跑全量；本地现存的 `test-php74_fulltest` / `test-php84_fulltest84` 镜像可用，但**重建会失败**——Dockerfile 里的 `apt-get install redis-server` 现在命中 404（Debian bullseye-security 仓库已过期），容器内 `composer update` 也可能卡在 GitHub 克隆上超时。日常全量以 WSL 为准；要修容器，先更新 Dockerfile 的 apt 源或基础镜像。
