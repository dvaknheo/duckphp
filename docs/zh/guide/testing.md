# 2-16 测试

> 解决什么问题：给自己的应用写测试该放哪、怎么在不起服务器的情况下测业务、端到端怎么测、覆盖率怎么跑，以及本仓库测试基建的两个硬约束（WSL、`data_for_tests` 约定）。
> 前置：[第 2-1 章 四层架构与调用规范](layers.md)、[第 2-6 章 模型层](model.md)。预计 20 分钟。
> 示例：`tests/bootstrap.php`、`tests/ZThirdDemoTest.php`（端到端冒烟）、`tests/data_for_tests/`（示例数据目录）。

```bash
# 跑单个测试文件（推荐：全量很慢）
wsl -e bash -lc "cd /mnt/e/ProjectGoat/DNMVCS && php vendor/bin/phpunit --no-coverage tests/ZThirdDemoTest.php"
```

## 最小示例

给业务层写测试**不需要服务器、不需要路由**——四层架构的直接好处（[第 2-1 章](layers.md)）：

```php
namespace tests;

class NoteBusinessTest extends \PHPUnit\Framework\TestCase
{
    public function testCreateRejectsEmptyTitle()
    {
        $this->expectException(\MyProj\System\BusinessException::class);

        \MyProj\Business\NoteBusiness::_()->create(['title' => '']);   // 空标题应抛业务异常
    }
}
```

端到端（跑真正的路由）则用仓库现成的两个重型样板当模板：

| 样板 | 干什么 | 适合抄的场景 |
|---|---|---|
| `tests/ZThirdDemoTest.php` | 一次 `init`、多次 `serve()`，断言「覆盖生效 / 没覆盖时回落」 | 多应用、覆盖、安装流程 |
| `tests/ZAllDemoTest.php` | 起 [`HttpServer`](../reference/HttpServer-HttpServer.md) + `curl` 各入口，比对输出字节长度 | 多入口冒烟（见「常见错误」里的注意点） |

## 机制说明

### 1. 测试放哪：`tests/` 镜像 `src/`

本仓库的约定（`phpunit.xml` 里 testsuite 就是整个 `./tests/`）：

```
tests/
  bootstrap.php          ← 自动加载 + LibCoverage 初始化
  Core/ Component/ Db/ Ext/ Foundation/ Helper/   ← 与 src/ 同名目录，一个类一个 *Test.php
  data_for_tests/        ← 测试用的示例工程/配置文件（可以当作"可运行的文档"）
  ZAllDemoTest.php / ZThirdDemoTest.php           ← 端到端冒烟
```

`tests/bootstrap.php` 做三件事：加载自动加载器（`vendor/autoload.php` 或根 `autoload.php`）、定义 `_lc()` 这个临时调试输出函数、把 `tests/data_for_tests/setting.php` 里的选项喂给 `LibCoverage`。

**示例工程放 `tests/data_for_tests/`** 是这套测试的关键约定：需要在「真实文件布局」下验证的东西（视图覆盖、多应用、安装流程）都做成一个可运行的小工程放在这里，测试直接引用它。用户的指南示例也因此可以直接指向这些目录（本指南第一/二/三卷的示例就是这么来的）。

### 2. 框架给测试的三件武器

| 手段 | 解决什么 |
|---|---|
| `system_wrapper_replace()` | 替换 `header()`/`setcookie()`/`exit()` 等系统调用，让「输出/跳转」可断言（[第 2-7 章](helper.md)） |
| [`Route::_()->PathInfo('note/show')`](../reference/Core-Route.md) | 不起 HTTP 也能把「当前请求路径」设成任意值，直接测路由与控制器（[第 2-2 章](routing.md)） |
| [`SuperGlobal`](../reference/Core-SuperGlobal.md) / [`Runtime`](../reference/Core-Runtime.md) 等组件的可替换单例 | GET/POST/Session 都能喂假数据；`Runtime` 还能开输出缓冲 |

```php
// 让 exit() 变成异常，从而断言"跳转确实发生了"
Helper::system_wrapper_replace([
    'exit' => function ($code = 0) { throw new \RuntimeException('exit(' . $code . ')'); },
]);
```

### 3. `LibCoverage`：本仓库的覆盖率机制

每次测试开始/结束会调用：

```php
\LibCoverage\LibCoverage::Begin(MyClass::class);
// …跑被测代码…
\LibCoverage\LibCoverage::End();
```

- 结果按**类**落到 `test_coveragedumps/<类名>.php`（要看的覆盖率数字就在这里）；
- 汇总报告在 `test_reports/index.html`（另有 `dashboard.html`）；
- 跑覆盖率需要 `XDEBUG_MODE=coverage`，且**必须在 WSL 里跑**——完整的命令与 `cov.py` 用法见 [参考手册维护指南 §5](../reference-maintenance-guide.md)。

**`@codeCoverageIgnore` 的语义**（踩过坑）：php-code-coverage 只认**整条注释恰好等于** `// @codeCoverageIgnore`，而且忽略的是**注释所在的那一行**——所以要贴在 `catch (...)` 行与 `return` 行的行尾，不能单独占一行。

### 4. 测试纪律：回归测试要「证明抓得住 bug」

写完断言后，**把 bug 临时改回源码跑一遍**，确认测试真的变红，再还原。没做这一步的回归测试等于没写——本仓库修 [`GlobalUser`](../reference/GlobalUser-GlobalUser.md) 死选项、[`PermissionMenu`](../reference/Ext-PermissionMenu.md) 合并 bug 时都是这么验证的。

另外两条：

- `phpunit.xml` 开了 `convertNoticesToExceptions`/`convertWarningsToExceptions`：**测试里的 notice 就是失败**，别用 `@` 压掉问题；
- `processIsolation="true"`：每个测试文件独立进程，所以文件之间不要共享状态（也意味着启动成本高，全量跑很慢——这就是「日常只跑单个文件」的原因）。

## 常见写法

**① 单文件测试（日常）**

```bash
wsl -e bash -lc "cd /mnt/e/ProjectGoat/DNMVCS && php vendor/bin/phpunit --no-coverage tests/Ext/PermissionMenuTest.php"
```

**② 带覆盖率跑一个类**

```bash
wsl -e bash -lc "cd /mnt/e/ProjectGoat/DNMVCS && XDEBUG_MODE=coverage php vendor/bin/phpunit tests/Ext/PermissionMenuTest.php"
# 然后看 test_coveragedumps/ 里对应类的 dump
```

**③ 断言一个控制器在给定路径下的输出**（用输出缓冲截获）

```php
\DuckPhp\Core\Route::_()->PathInfo('note/show');
ob_start();
NoteController::_()->show();
$html = ob_get_clean();
$this->assertStringContainsString('便签', $html);
```

**④ 给示例工程写冒烟：一次 init、多次 serve**

```php
$app = MyApp::_()->init(['path' => __DIR__ . '/data_for_tests/MyDemo/']);
// 逐个断言不同请求
$this->assertSame('...', $this->fetch($app, '/shop/index'));
```

（`tests/ZThirdDemoTest.php` 是这套写法的完整样板。）

**⑤ 临时调试：`_lc()`**

`tests/bootstrap.php` 里的 `_lc()` 会把「调用位置 + 调用栈」打到 `tests/_lc.log`，比到处 `var_dump` 干净。

## 常见错误

| 现象 | 原因 | 改法 |
|---|---|---|
| Windows 侧跑测试大量失败（`Class 'Redis' not found`） | Windows 的 PHP 没有 redis 扩展 | 测试一律走 WSL（`wsl -e bash -lc "…"`） |
| 全量测试很慢/踩端口 | `processIsolation` + `ZAllDemoTest` 起内置服务器固定端口 9802 | 日常只跑单个文件；端到端测试不要并发跑 |
| 改完 `src/` 后 `ZAllDemoTest` 的 `files` 路由变红 | 该用例比的是**输出字节长度**，方法表/行号一变长度就变 | 把 `tests/data_for_tests/ZAllDemoTest.config.php` 里的期望值改成实际值（dump 会存成 `ZAllDemoTest-<长度>.txt`） |
| 加了 `@codeCoverageIgnore` 但没生效 | 注释不是整行、或没贴在要忽略的行尾 | 贴到目标行行尾，注释内容就是 `@codeCoverageIgnore` |
| 覆盖率看着有，但 dump 里没有 | 忘了 `XDEBUG_MODE=coverage`，或在 Windows 侧跑 | 在 WSL 里带 `XDEBUG_MODE=coverage` 跑 |
| 测试之间互相污染 | 共享了单例/数据库文件 | 用 `LibCoverage` 的清理钩子（`cleanTestDb()`）或每个测试自建临时数据 |
| 回归测试「一直绿」 | 没验证过断言能抓住 bug | 临时把 bug 改回去跑一遍，确认变红，再还原 |
| 断言里出现 notice 导致失败 | `convertNoticesToExceptions` 开着 | 修数据/初始化，别用 `@` 压警告 |

## 下一步

- [第 2-17 章 安全与性能清单](security-performance.md)：上线前逐项自查。
- [参考手册维护指南](../reference-maintenance-guide.md)：`docs/zh/reference/` 与覆盖率流水线的完整流程。
- 参考手册：[DuckPhp\HttpServer\HttpServer](../reference/HttpServer-HttpServer.md)、[DuckPhp\Core\Runtime](../reference/Core-Runtime.md)。
