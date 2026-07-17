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
