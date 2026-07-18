# 测试

DuckPHP 的测试代码位于 `tests/` 目录，采用 PHPUnit 组织。`src/` 下的类与 `tests/` 下的测试文件按“类名 + `Test` 后缀”对应，例如 `src/Core/KernelTrait.php` 对应 `tests/Core/KernelTraitTest.php`。

## 环境要求

- WSL（Windows Subsystem for Linux）
- Docker 与 docker-compose
- 工程目录已挂载到 WSL 中

## 推荐测试方式：常驻容器

为了避免每次测试都重新创建 Docker 容器，我们在 `docker/test-php74/` 下提供了一组脚本：

| 脚本 | 作用 |
|------|------|
| `start-docker.sh` | 启动后台容器 `duckphp-test`，并自动启动 redis |
| `exec-docker.sh`  | 在容器内 `/DATA` 目录执行命令 |
| `stop-docker.sh`  | 停止容器，但不删除 |
| `end-docker.sh`   | 停止并删除容器 |

### 启动容器

```bash
cd <工程目录>/docker/test-php74
./start-docker.sh
```

### 执行单类测试

以 `KernelTrait` 为例：

```bash
./exec-docker.sh vendor/bin/phpunit tests/Core/KernelTraitTest.php
```

`exec-docker.sh` 后面的参数就是要在容器内 `/DATA` 目录下执行的命令。也可以使用镜像里的 composer scripts：

```bash
./exec-docker.sh composer run-script singletest tests/Core/KernelTraitTest.php
```

### 刷新覆盖率报告

```bash
./exec-docker.sh vendor/bin/phpunit tests/support.php
```

刷新后的报告位于 `test_reports/Core/KernelTrait.php.html`。

### 停止与结束

临时停止（保留容器，下次可继续）：

```bash
./stop-docker.sh
```

彻底结束并删除容器：

```bash
./end-docker.sh
```

## 完整示例：测试 KernelTrait 并查看覆盖率

```bash
cd <工程目录>/docker/test-php74

./start-docker.sh
./exec-docker.sh vendor/bin/phpunit tests/Core/KernelTraitTest.php
./exec-docker.sh vendor/bin/phpunit tests/support.php
./end-docker.sh
```

执行完后，打开 `test_reports/Core/KernelTrait.php.html` 查看覆盖率。如果 Lines 不到 100%，在页面中搜索 `Not Executed` 或 `class="danger"` 标记的行，即可找到未执行到的代码行。

修复时一般只需在对应测试类末尾追加新的测试调用，例如 `tests/Core/KernelTraitTest.php`，不需要修改 `src/` 源码。

---

## 修复覆盖测试缺口

当某类的行覆盖率不到 100% 时，可以按以下步骤逐步填补测试缺口。

### 前置条件

- Docker 容器 `duckphp-test` 已启动（`bash docker/test-php74/start-docker.sh`）
- 如果 `vendor/bin/phpunit` 在容器内丢失，先运行 `docker exec duckphp-test composer install --no-interaction --prefer-dist` 重装

### 步骤概览

```
跑单个测试 → 生成报告 → grep 挖未覆盖行 → 分析源码 → 补测试 → 循环直到零 danger
```

### 1. 运行待修复类的测试，确认通过

```bash
bash ./docker/test-php74/exec-docker.sh \
    ./vendor/bin/phpunit tests/Core/XxxTest.php
```

如果测试失败，先修复再继续。**注意：exec-docker.sh 不支持管道/重定向**，所有参数必须是独立参数。

### 2. 生成最新覆盖率报告

```bash
bash ./docker/test-php74/exec-docker.sh \
    ./vendor/bin/phpunit tests/support.php
```

这会跑全部测试，刷新 `test_reports/` 下的 HTML 报告。**单个测试跑不会刷新报告**，必须跑 support.php。

### 3. 用 grep 挖出所有未覆盖行

```bash
grep -n '<tr class="danger">' \
    /mnt/e/ProjectGoat/DNMVCS/test_reports/Core/Xxx.php.html
```

输出示例：
```
745: <tr class="danger">...<a name="112">112</a>...
```

行号对应源码中的行号。grep 返回 **exit code 1**（零匹配）时表示覆盖率达到 100%。

### 4. 对照源码逐行分析缺口

对每个 danger 行号，打开源码文件查看：

```
read_file src/Core/Xxx.php --offset N-3 --limit 6
```

两件事要做：
- **判断缺口原因：** 是整行没执行？还是 if 的某个走向没覆盖？
- **判断是否源码 bug：** 有些 danger 行是因为源码缺少 `return`，而不是测试没写到。例如 `App::prepareServe()` 的 callable 分支和 null 分支都缺少 `return;`，导致代码继续执行到 `View::Show([], $error_maintain)` 而报错。这种情况要先修源码再补测试。

### 5. 设计并编写填补测试

#### 测试组织结构

所有测试必须放在 `testAll()` 的 `\LibCoverage\LibCoverage::Begin()/::End()` 之间。推荐在 `testAll()` 末尾加 `$this->doCoverageGapTest();`，在辅助方法里写具体测试代码。

```php
public function testAll()
{
    \LibCoverage\LibCoverage::Begin(ClassName::class);
    // ... 既有测试 ...
    $this->doCoverageGapTest();  // 新增
    \LibCoverage\LibCoverage::End();
}
```

#### 隔离原则

每次测试场景前调用：

```php
PhaseContainer::RestAllContainerForTesting();
```

防止 Phase 容器状态残留影响后续测试。

#### 捕获输出的方法

```php
ob_start();
// 执行目标代码
$output = ob_get_clean();
$this->assertStringContainsString('期望内容', $output);
```

#### 辅助类的放置位置

新辅助类放在测试文件的尾部，同一个 namespace 内，`}` 结束之前。

#### 常见缺口的填补模式

| 缺口类型 | 填补方法 |
|---------|---------|
| **未调用的公开方法** | 直接调用 + `assert` |
| **if 的某个分支** | 构造触发该分支的参数走 `init()` |
| **异常路径** | `try { ...触发... } catch (ExpectedException $e) { assert }` |
| **Phase 名冲突** | 创建一个带子 app 的父 app，再手动 init 同名子 app |
| **钩子方法（空实现）** | 子类 override + echo 标记 + ob 捕获断言 |
| **缺少 namespace 自动推导** | 不传 `namespace` 选项，init 后检查 `options['namespace']` |
| **`prepareServe()` 维护模式** | 设置 `is_maintain=true` + 三种 `error_maintain`（null/callable/视图名）调用 `serve()` |
| **`_OnDefaultException` 未 init 时** | 在未 init 的 App 实例上直接调用 `_OnDefaultException(...)`，此时 `is_inited=false` |
| **`_OnDevErrorHandler` 默认 HTML** | 用 `is_debug=true` + `error_debug` 留 null 的选项 init App，然后调用 `_OnDevErrorHandler(...)` |
| **`initChildren` class-key mix mode** | `app => ['前缀' => ['class' => ChildClass::class]]` 传入 init |
| **`getProjectPath()`** | 在已 init 的 root App 上直接调用 |

#### 需注意的源码细节

- 有些方法是 **public** 的（如 `_OnDefaultException`, `_OnDevErrorHandler`, `serve()`, `getProjectPath()`），可以直接调
- 有些方法是 **protected** 的，需要通过公开方法间接调用（如 `init()` 触发 `initChildren()`）
- 视图渲染需要 `path_view` 指向一个包含有效 .php 文件的目录
- `_OnDevErrorHandler` 在第一行就检查 `_IsDebug()`，所以必须 `is_debug=true`

### 6. 重复 1→5 直到零 danger

每次修改后：

```bash
# 1) 跑单个测试确认通过
bash ./docker/test-php74/exec-docker.sh \
    ./vendor/bin/phpunit tests/Core/XxxTest.php

# 2) 重新生成报告
bash ./docker/test-php74/exec-docker.sh \
    ./vendor/bin/phpunit tests/support.php

# 3) 检查剩余 danger
grep -n '<tr class="danger">' \
    /mnt/e/ProjectGoat/DNMVCS/test_reports/Core/Xxx.php.html
```

当返回 **exit code 1**（零匹配）时，覆盖率达到 100%。

### 输出要求

- 记录每个缺口行号的源码原因和填补方式
- 记录断言数的变化（如 1 → 8）
- 如果发现了源码 bug（如缺少 return），一并记录
- 最终确认报告中 `<tr class="danger">` 数量为 0
