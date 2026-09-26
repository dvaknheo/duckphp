# 2-17 Testing

> What this solves: where to put tests for your own app, how to test business code without booting a server, how end-to-end testing and coverage work, plus this repo's two hard testing constraints (WSL, the `data_for_tests` convention).
> Prerequisites: [Chapter 2-1 The Four-Layer Architecture and Calling Conventions](layers.md), [Chapter 2-8 The Model Layer](model.md). About 20 minutes.
> Examples: `tests/bootstrap.php`, `tests/ZThirdDemoTest.php` (end-to-end smoke), `tests/data_for_tests/` (sample data directory).

```bash
# All commands below run from the repo root (on Windows, run them via WSL — see above for why)
# Run a single test file (recommended: the full suite is very slow)
wsl -e bash -lc "php vendor/bin/phpunit --no-coverage tests/ZThirdDemoTest.php"
```

## Minimal example

Testing the business layer needs **no server and no routing** — a direct payoff of the four-layer architecture ([Chapter 2-1](layers.md)):

```php
namespace tests;

class NoteBusinessTest extends \PHPUnit\Framework\TestCase
{
    public function testCreateRejectsEmptyTitle()
    {
        $this->expectException(\MyProj\System\BusinessException::class);

        \MyProj\Business\NoteBusiness::_()->create(['title' => '']);   // an empty title must throw a business exception
    }
}
```

For end-to-end tests (running real routing), use the repo's two heavy fixtures as templates:

| Fixture | What it does | When to copy it |
|---|---|---|
| `tests/ZThirdDemoTest.php` | one `init`, many `serve()` calls; asserts "override takes effect / falls back when not overridden" | multi-app, overriding, install flows |
| `tests/ZAllDemoTest.php` | boots [`HttpServer`](../reference/HttpServer-HttpServer.md) + `curl`s every entry point, compares output byte lengths | multi-entry smoke tests (see the notes under "Common errors") |

## How it works

### 1. Where tests live: `tests/` mirrors `src/`

This repo's convention (in `phpunit.xml` the testsuite is the whole `./tests/`):

```
tests/
  bootstrap.php          ← 自动加载 + LibCoverage 初始化
  Core/ Component/ Db/ Ext/ Foundation/ Helper/   ← 与 src/ 同名目录，一个类一个 *Test.php
  data_for_tests/        ← 测试用的示例工程/配置文件（可以当作"可运行的文档"）
  ZAllDemoTest.php / ZThirdDemoTest.php           ← 端到端冒烟
```

`tests/bootstrap.php` does three things: loads the autoloader (`vendor/autoload.php` or the root `autoload.php`), defines the ad-hoc debug-output function `_lc()`, and feeds the options from `tests/data_for_tests/setting.php` to `LibCoverage`.

**Putting the sample projects in `tests/data_for_tests/`** is the key convention of this setup: anything that must be verified under a "real file layout" (view overriding, multi-app, install flows) is built as a small runnable project there, and tests reference it directly. That is also why this guide's examples can point straight at those directories (the examples in Volumes 1/2/3 of this guide are built exactly this way).

### 2. The three weapons the framework gives tests

| Tool                                                                                                      | What it solves                                                                         |
| ------------------------------------------------------------------------------------------------------- | ---------------------------------------------------------------------------- |
| `system_wrapper_replace()`                                                                              | replaces system calls like `header()`/`setcookie()`/`exit()`, making "output/redirect" assertable ([Chapter 2-9](helper.md)) |
| [`Route::_()->PathInfo('note/show')`](../reference/Core-Route.md)                                       | sets the "current request path" to any value without HTTP, so you can test routing and controllers directly ([Chapter 2-3](routing.md))                    |
| Replaceable singletons of components like [`SuperGlobal`](../reference/Core-SuperGlobal.md) / [`Runtime`](../reference/Core-Runtime.md) | you can feed fake data into GET/POST/Session; `Runtime` can also enable output buffering                                    |

```php
// turn exit() into an exception, so you can assert "the redirect really happened"
Helper::system_wrapper_replace([
    'exit' => function ($code = 0) { throw new \RuntimeException('exit(' . $code . ')'); },
]);
```

### 3. `LibCoverage`: this repo's coverage mechanism

At the start/end of every test:

```php
\LibCoverage\LibCoverage::Begin(MyClass::class);
// ...run the code under test...
\LibCoverage\LibCoverage::End();
```

- results land per **class** in `test_coveragedumps/<class-name>.php` (this is where the coverage numbers live);
- the summary report is at `test_reports/index.html` (plus `dashboard.html`);
- running coverage needs `XDEBUG_MODE=coverage` and **must run inside WSL** — for the full command and `cov.py` usage see [the reference manual maintenance guide §5](../reference-maintenance-guide.md).

**The semantics of `@codeCoverageIgnore`** (a known pitfall): php-code-coverage only accepts a comment that is **exactly** `// @codeCoverageIgnore`, and what gets ignored is **the line the comment sits on** — so stick it at the end of the `catch (...)` line and the `return` line; it must not occupy a line by itself.

### 4. Test discipline: a regression test must "prove it can catch the bug"

After writing the assertion, **temporarily put the bug back into the source and run the test once**, confirm it really goes red, then revert. A regression test without this step is as good as unwritten — that is how this repo verified the fixes for the [`GlobalUser`](../reference/GlobalUser-GlobalUser.md) dead option and the [`PermissionMenu`](../reference/Ext-PermissionMenu.md) merge bug.

Two more rules:

- `phpunit.xml` enables `convertNoticesToExceptions`/`convertWarningsToExceptions`: **a notice in a test is a failure** — don't suppress problems with `@`;
- `processIsolation="true"`: every test file runs in its own process, so don't share state between files (it also means high startup cost and a very slow full run — which is why "day to day, run single files only").

## Common patterns

**① Single-file testing (day to day)**

```bash
wsl -e bash -lc "php vendor/bin/phpunit --no-coverage tests/Ext/PermissionMenuTest.php"
```

**② Run one class with coverage**

```bash
wsl -e bash -lc "XDEBUG_MODE=coverage php vendor/bin/phpunit tests/Ext/PermissionMenuTest.php"
# then look at the dump for that class in test_coveragedumps/
```

**③ Assert a controller's output at a given path** (captured via output buffering)

```php
\DuckPhp\Core\Route::_()->PathInfo('note/show');
ob_start();
NoteController::_()->show();
$html = ob_get_clean();
$this->assertStringContainsString('便签', $html);
```

**④ Smoke tests for a sample project: one init, many serves**

```php
$app = MyApp::_()->init(['path' => __DIR__ . '/data_for_tests/MyDemo/']);
// assert the different requests one by one
$this->assertSame('...', $this->fetch($app, '/shop/index'));
```

(`tests/ZThirdDemoTest.php` is the complete fixture for this pattern.)

**⑤ Ad-hoc debugging: `_lc()`**

The `_lc()` in `tests/bootstrap.php` writes "call site + call stack" to `tests/_lc.log` — cleaner than sprinkling `var_dump` everywhere.

## Common errors

| Symptom | Cause | Fix |
|---|---|---|
| Mass test failures on the Windows side (`Class 'Redis' not found`) | Windows PHP has no redis extension | Always run tests via WSL (`wsl -e bash -lc "…"`) |
| Full suite is very slow / port conflicts | `processIsolation` + `ZAllDemoTest` boots a built-in server on fixed port 9802 | Run single files day to day; never run end-to-end tests concurrently |
| After changing `src/`, `ZAllDemoTest`'s `files` route goes red | The case compares **output byte length**; any change to method tables/line numbers changes the length | Update the expected values in `tests/data_for_tests/ZAllDemoTest.config.php` to the actual ones (the dump is saved as `ZAllDemoTest-<length>.txt`) |
| Added `@codeCoverageIgnore` but it has no effect | The comment is not the whole comment on that line, or it is not appended to the line to ignore | Stick it at the end of the target line; the comment content is exactly `@codeCoverageIgnore` |
| Coverage seems to run, but nothing lands in the dumps | Forgot `XDEBUG_MODE=coverage`, or ran on the Windows side | Run in WSL with `XDEBUG_MODE=coverage` |
| Tests pollute each other | Shared singletons / database files | Use `LibCoverage`'s cleanup hook (`cleanTestDb()`), or have each test build its own temp data |
| A regression test that is "always green" | Never verified the assertion can catch the bug | Temporarily put the bug back, run once, confirm red, then revert |
| A notice inside an assertion fails the test | `convertNoticesToExceptions` is on | Fix the data/initialization; don't suppress warnings with `@` |

## Next steps

- [Chapter 2-18 Security and Performance Checklist](security-performance.md): item-by-item self-review before going live.
- [The reference manual maintenance guide](../reference-maintenance-guide.md): the full workflow for `docs/zh/reference/` and the coverage pipeline.
- The reference manual: [DuckPhp\HttpServer\HttpServer](../reference/HttpServer-HttpServer.md), [DuckPhp\Core\Runtime](../reference/Core-Runtime.md).
