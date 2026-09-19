# 38 测试基建与覆盖率流水线

> 解决什么问题：这套仓库的测试怎么跑、覆盖率报告是怎么来的（`test_coveragedumps/`、`test_reports/`）、多 PHP 版本怎么验、以及几个生成器脚本是干什么的。
> 前置：[第 23 章 测试](testing.md)（给应用写测试）；本章讲**框架仓库自己的流水线**。预计 20 分钟。
> 相关：`docs/zh/reference-maintenance-guide.md` §5（覆盖率与文档一致性流程的完整命令）。

```bash
# 日常：只跑单个测试文件（全量很慢，见下）
wsl -e bash -lc "cd /mnt/e/ProjectGoat/DNMVCS && php vendor/bin/phpunit --no-coverage tests/Core/AppTest.php"
# 全量（约 5~6 分钟，最后会打印 Test Lines 与报告路径）
wsl -e bash -lc "cd /mnt/e/ProjectGoat/DNMVCS && php vendor/bin/phpunit --no-coverage"
```

## 最小示例

一次全量跑完的真实结尾（实测）：

```
 All Done  Test Done!
Test Lines: 4815/4815(100.00%)
Time: 05:37.255, Memory: 14.00 MB
OK (92 tests, 556 assertions)
```

- `Test Lines: 4815/4815` 来自 `dvaknheo/libcoverage`（本仓库的覆盖率组件），意思是**测试代码行**全部被执行；
- 报告文件：`test_reports/index.html`（另有 `test_reports/dashboard.html`）；
- 单类 dump：`test_coveragedumps/<类名>.php`（例如 `test_coveragedumps/Core/App.php`）。

## 机制说明

### 1. 三件事拼起来的流水线

```
phpunit.xml                 → 测试套件定义（bootstrap、testsuite、覆盖率收集范围）
tests/bootstrap.php         → 自动加载 + LibCoverage 初始化（喂 tests/data_for_tests/setting.php 的选项）
测试里的 Begin/End          → 每个测试自己声明"我在测哪个类"
tests/support.php           → CodeCovar 套件：汇总生成整份报告
```

`phpunit.xml` 里两个关键设置：

- `<testsuite>` 指向整个 `./tests/`（外加一个 `CodeCovar` 套件指向 `tests/support.php`）；
- `<coverage><include><directory>./src/</directory>` —— 覆盖率**只统计 `src/`**（`tests/` 自身不计入覆盖率分母）；
- `processIsolation="true"`：每个测试文件独立进程（隔离干净，代价是慢）；
- `convertNoticesToExceptions` / `convertWarningsToExceptions`：**notice/warning 直接算失败**。

### 2. 覆盖率是怎么产生的

```php
// 每个测试文件的开头/结尾（仓库惯例）
\LibCoverage\LibCoverage::Begin(MyClass::class);
// …跑被测代码…
\LibCoverage\LibCoverage::End();
```

- `LibCoverage` 是**独立组件**（composer 里是 `dvaknheo/libcoverage`），不依赖 xdebug 也能统计「行是否被执行」；
- `tests/bootstrap.php` 用 `tests/data_for_tests/setting.php` 的选项初始化它，并定义了 `MyLibCoverage` 子类（多了一个「清理测试数据库」的钩子 `cleanTestDb()`）；
- 生成 HTML 报告的是 `tests/support.php` 里的 `LibCoverage::G()->showAllReport()`；它只负责**汇总**，所以要先把各个测试跑出 dump 来。

**要真正的 xdebug 行覆盖**时，环境变量必须走外部设置：

```bash
XDEBUG_MODE=coverage php vendor/bin/phpunit tests/Ext/PermissionMenuTest.php
```

> 为什么不能在 `phpunit.xml`/`ini_set` 里设：`xdebug.mode` 属于 `PHP_INI_SYSTEM`，PHP 8+（尤其 8.4）下运行期改不动——`tests/support.php` 里那句 `ini_set` 只在 PHP < 8 生效，注释也写明了这点。

### 3. `@codeCoverageIgnore` 的真实语义（踩过的坑）

- php-code-coverage 只认**整条注释恰好是** `// @codeCoverageIgnore`；
- 它忽略的是**注释所在的那一行**——所以要贴在 `catch (...)` / `return` 的**行尾**，不能单独占一行；
- 忽略一段区间才用 `// @codeCoverageIgnoreStart` … `// @codeCoverageIgnoreEnd`。

### 4. 多 PHP 版本与容器

| 资产 | 内容 |
|---|---|
| `docker/docker-compose.yml` | 顶层编排 |
| `docker/test-php74/` | PHP 7.4 的 Dockerfile + compose（**注意：这个目录里没有 `.sh` 脚本**） |
| `docker/test-php84/` | PHP 8.4 的 Dockerfile + compose + `start-docker.sh`/`exec-docker.sh`/`stop-docker.sh`/`end-docker.sh` |
| `composer-test-php84.json` | 8.4 环境下的 composer 脚本：`fulltest`（cs-fixer + phpstan + phpunit + genoptions）、`singletest`、`genoptions` |

容器化的意义：**redis 扩展**、不同 PHP 版本的语法差异（本框架支持 `>=7.4`）都要在真实环境里验一遍。Windows 侧跑会在 redis 相关用例上假失败（详见[第 23 章](testing.md)）。

### 5. 生成器与闸门脚本

| 脚本 | 用途 |
|---|---|
| `docs/scripts/gen-reference.php` | 参考手册工具：`facts`（打印源码解析结果）、`skeleton`（生成文档骨架）、`verify`（比对方法/选项） |
| `docs/scripts/gen-route.php` | 极简骨架生成（Route 风格） |
| `docs/scripts/gen-options-docs.php` | 由源码生成选项文档（`options*.md`） |
| `docs/scripts/scan-options.py` | 扫描 `$options`/`$hidden_options`，报「读了没声明」的键与拼写变体 |
| `tests/genoptions.php` | 生成选项相关的校验数据（composer 脚本 `genoptions`） |
| `docs/scripts/check-doc-links.py` | 站内 md 链接校验（0 死链是本仓库的硬指标） |
| `docs/scripts/check-non-ascii.sh` | `src/` 纯 ASCII 校验（改 `src/` 后必跑） |

> 这些脚本统一放在 **`docs/scripts/`** 下、跟文档一起提交：它们只服务文档生成与校验（不是框架运行时的一部分），放在 `docs/` 里能让「文档改动 + 生成器改动」同一次提交、同一个位置找到。命令一律从**仓库根目录**执行（如 `python3 docs/scripts/check-doc-links.py docs/zh`）。

`composer run-script fulltest`（在 8.4 容器/环境里）把 **php-cs-fixer → phpstan → phpunit → genoptions** 串起来，是提交前最完整的闸门。

> ⚠️ `gen-reference.php verify` 对「`use Trait { … as … }` 并 override」的大文件（如 `Core/App.php`）**会漏列方法**，从而把正确文档误报成「多了方法」。判定一致性以漂移扫描脚本为准（见 `reference-maintenance-guide.md` §5）。

## 常见写法

**① 只跑改了的那一块**

```bash
wsl -e bash -lc "cd /mnt/e/ProjectGoat/DNMVCS && php vendor/bin/phpunit --no-coverage tests/Ext/MyMiddlewareManagerTest.php"
```

**② 跑完看某一类的覆盖 dump**

```bash
ls -la test_coveragedumps/Core/ | head          # 每类一个 php 文件
# 报告：test_reports/index.html（浏览器打开）
```

**③ 只重建报告（不重跑全部测试）**

```bash
php vendor/bin/phpunit tests/support.php        # CodeCovar 套件：汇总已有 dump
```

**④ 改完文档做三项自检**

```bash
python3 docs/scripts/check-doc-links.py docs/zh      # 期望 broken: 0
bash docs/scripts/check-non-ascii.sh                 # 改过 src/ 时期望 Total non-ASCII lines: 0
python3 docs/scripts/scan-options.py                 # 选项表相关校验
```

**⑤ 提交前的完整闸门（8.4 环境）**

```bash
composer run-script fulltest
```

## 常见错误

| 现象 | 原因 | 改法 |
|---|---|---|
| Windows 侧跑测试报 `Class 'Redis' not found` | Windows PHP 无 redis 扩展 | 一律在 WSL / 容器里跑（[第 23 章](testing.md)） |
| 覆盖率数字全是 0 | 没设 `XDEBUG_MODE=coverage`，或跑的是 `--no-coverage` 且没跑 `CodeCovar` 套件 | 见 §2；报告需要 dump + `showAllReport()` 两步 |
| `@codeCoverageIgnore` 没生效 | 注释不是整行恰好、或没贴在目标行尾 | 贴到目标行尾（§3） |
| 全量测试偶发失败 | `ZAllDemoTest` 起内置服务器占固定端口 9802，与其它实例/残留进程冲突 | 确认没有并行跑；必要时改为顺序执行 |
| 改了 `src/` 后 `ZAllDemoTest` 的 `files` 变红 | 它比的是输出**字节长度**（含选项表与方法表） | 改 `tests/data_for_tests/ZAllDemoTest.config.php` 里对应期望值（dump 存成 `ZAllDemoTest-<长度>.txt`） |
| `gen-reference.php verify` 报「多了方法」 | 大文件的 trait 别名 override 让脚本漏列（已知缺陷） | 以漂移扫描为准，不要照着删文档 |
| docker 目录下找不到 `start-docker.sh`（php74） | 脚本**只在 `docker/test-php84/`** 里 | 用 8.4 的那套脚本，或直接用 `docker-compose` 起 php74 |
| `tests/support.php` 跑不起来 | 没先跑过任何测试（没有 dump 可汇总）或 bootstrap 失败 | 先跑至少一个测试文件 |

## 下一步

- [第 39 章 文档与参考手册维护](doc-maintenance.md)：这套脚本怎么用在文档上。
- [第 40 章 性能调优与排错手册](troubleshooting.md)：测试全绿但线上出问题的排查路径。
- 维护指南：[参考手册维护指南](../reference-maintenance-guide.md)（§5 覆盖率流程、§10 漂移判读）、[用户指南维护指南](../guide-maintenance-guide.md)。
- 参考手册：[DuckPhp\HttpServer\HttpServer](../reference/HttpServer-HttpServer.md)。
