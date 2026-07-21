# DuckPHP 项目编码规则

## 标准项目结构

使用 Composer 脚本快速创建项目（安装后可用）：

```bash
php vendor/bin/duckphp new
```

将会使用 `skeleton/` 目录下的脚手架模板创建工程。

工程根目录结构：


```
project/
├── composer.json
├── config/
│   └── DuckPhpSettings.config.php    # 全局设置
├── public/
│   └── index.php                     # Web 入口
├── src/
│   ├── Controller/                   # 控制器层：HTTP/CLI 请求入口
│   │   ├── Base.php
│   │   ├── * ConsoleCommand.php     # CLI 子命令示例（默认未启用）
│   │   ├── * ExceptionReporter.php  # 异常报告器（默认未启用）
│   │   ├── Helper.php
│   │   ├── MainController.php
│   │   ├── Session.php              # Session 管理
│   │   ├── * SomeAction.php            # Action 示例
│   │   └── * testController.php        # 测试控制器
│   ├── Business/                     # 业务层：业务逻辑
│   │   ├── Base.php
│   │   ├── * DemoBusiness.php       # Business 示例
│   │   ├── Helper.php
│   │   └── * SomeService.php        # Service 示例
│   ├── Model/                        # 模型层：数据访问
│   │   ├── Base.php
│   │   └── DemoModel.php            # Model 示例
│   └── System/                       # 系统层：应用配置与异常
│       ├── App.php                   # 应用核心配置
│       ├── * BusinessException.php   # Business 异常（默认未启用）
│       ├── * ControllerException.php # Controller 异常（默认未启用）
│       └── * ProjectException.php    # 项目异常基类（默认未启用）
├── view/                              # 视图目录
│   ├── _sys/                         # 系统视图
│   │   ├── error_404.php
│   │   └── error_500.php
│   └── main.php                      # 默认视图示例
├── runtime/                           # 运行时目录（日志等）
├── cli.php                            # CLI 入口
└── vendor/
```

> **注意**：
> - `SomeAction.php`、`testController.php`、`DemoBusiness.php`、`SomeService.php`、`DemoModel.php` 是示例文件，实际项目中应删除并根据业务需求编写类似的类。
> - `ConsoleCommand.php`、`ExceptionReporter.php`、`BusinessException.php`、`ControllerException.php`、`ProjectException.php` 默认未启用。你可以：
>   - 精简工程：直接删除这些不用的文件。
>   - 启用功能：在 `src/System/App.php` 中取消对应的选项注释（`cli_command_classes`、`exception_reporter`、`exception_for_project` / `exception_for_business` / `exception_for_controller`）。
>
> `runtime/` 目录需要可写权限。



## 层级调用规范

```
Controller 层 (可处理请求上下文)
  ├── 所有 Business 为后缀的类调用者集中在这一层，请勿违反.
  ├── 可调用: Action, Business, Helper, Session
  └── 禁止调用 Service,Model

Business 层 (纯无状态)
  ├── 可调用: Model, Service, Helper
  └── 禁止: 读写 Session, 访问 $_GET/$_POST/$_SERVER/$_FILES

Model 层 (纯无状态)
  ├── 用于数据持久化，类名和表名相关
  ├── 可调用: 仅数据访问相关
  └── 禁止: 业务逻辑, 抛异常

System 层
  └── 处理框架相关调用, 异常定义, 应用配置
```

## 编码规则

### 命名规范

| 类型 | 命名规则 | 示例 | 说明 |
|------|---------|------|------|
| 控制器类 | `{Name}Controller` | `UserController` | 路由入口，处理输入/输出 |
| 控制器方法 | `{action_prefix}{method}` | `{action_prefix}index()` | 路由方法前缀 |
| CLI 子方法 | `command_{method}` | `command_hello()` | 命令行方法前缀 |
| 动作类 | `{Name}Action` | `UserAction` | 控制器通用功能复用 |
| Session 类 | `Session` | `Session` | 状态容器 |
| 业务类 | `{Name}Business` | `UserBusiness` | 业务逻辑编排 |
| Service 类 | `{Name}Service` | `CommonService` | 业务通用功能复用 |
| Model 类 | `{Name}Model` | `UserModel` | 数据访问 |
| 异常类 | `{Name}Exception` | `ProjectException` | 异常层级 |

{action_prefix} 在应用选项 `controller_method_prefix` 里设置， DuckPhp 1.3.6 版本起默认值得由 `action_` 改为 ``

### 核心原则

各层级的 `Helper` 类一般情况下不需要增加方法。

#### 系统层（System）

1. 框架相关调用集中在 **System 层** 处理。
2. `System` 命名空间负责处理框架相关调用、异常定义和应用配置。
3. 系统层一般不能调用 业务层（Business）和 模型层（Model），由 Controller 层 `AppAction` 类中转。

#### 控制器层（Controller）

1. Controller 层请勿直接调用 `DuckPhp` 命名空间下的类。
2. Session 操作集中在 `Session` 类中处理。
3. 控制器类作为 HTTP 请求入口，处理输入/输出，控制器之间不互相调用。
4. 控制器之间共享的操作可封装成 **Action 类**。
5. 控制器类和动作类应继承 `Base` 类。
6. 控制器类和动作类调用 **Business 类**。
7. 动作类是无状态的，因此必须有空的 `__construct()` 覆盖基类的构造函数。
8. 控制器类和动作类使用 `Helper::ControllerThrowOn()` 抛出异常。

#### 业务层（Business）

1. 业务层请勿直接调用 `DuckPhp` 命名空间下的类。
2. 业务层保持**纯无状态**，可被 CLI、测试、API 等任意环境复用。
3. 业务类之间共享的操作可封装成 **Service 类**。
4. 业务类和 Service 类应继承 `Base` 类。
5. 业务类和 Service 类调用 **Model 类**。
6. 业务层和 Service 类使用 `Helper::BusinessThrowOn()` 抛出异常。
7. 业务层其他独立的类放在这里。 后缀是 Business ， Service 的类是和系统相关的类，反之则不是。
8. 只有 `Controller` 目录下的后缀为 `Controller`,`Action`,`Command`, `Base`的类可以调用 `Business` 目录下后缀为 `Business` 的类


#### 模型层（Model）

1. 模型层请勿直接调用 `DuckPhp` 命名空间下的类。
2. 模型层保持**纯无状态**，仅负责数据持久化访问
3. Model 一般按数据库表名对应。
4. 工程规范中，Model 层的 `Helper` 类并入了 `Base` 类。
5. Model 类禁止抛异常，由调用者处理异常。
9. 只有 `Business` 目录下的后缀为 `Service`,`Business`,`Base` 的类可以调用 `Model` 目录下后缀为 `Model` 的类

#### 视图层（View）

1. 视图文件禁止做复杂计算，只用于显示输出。
2. `view/_sys/` 存放系统视图（如错误页面）。
3. `view/{ControllerName}/{ActionName}` 用于存放控制器对应的视图。
4. view 层使用 全局函数，一般不要使用任何系统相关的类。
5. 技巧： view 可以`var_dump($this)` 看当前页面有什么变量

### 路由规则
#### 根路由与欢迎页

DuckPHP 的默认欢迎页控制器是 `MainController`。访问根路径 `/` 时，等价于访问 `/Main/index`，会调用 `MainController::action_index()`。

```
/                  → 命名空间\Controller\MainController::action_index()
/index             → 命名空间\Controller\MainController::action_index()
```

同样，访问 `/foo` 时，等价于访问 `/Main/foo`，会调用 `MainController::action_foo()`。

```
/foo               → 命名空间\Controller\MainController::action_foo()
```

因此，你可以把所有顶层短路由集中放在 `MainController` 中，例如：

```
## 默认路由规则

URL 路径格式：

```
/{Controller类名}/{action方法名}
```

映射到控制器类的方法：

```
/Main/index        → 命名空间\Controller\MainController::action_index()
/user/profile      → 命名空间\Controller\UserController::action_profile()
/admin/user/list   → 命名空间\Controller\Admin\UserController::action_list()
```

### 关键约定

| 约定 | 默认值 | 说明 |
|---|---|---|
| 控制器类后缀 | `Controller` | `FooController` |
| 方法前缀 | `action_` | `action_index()` |
| 欢迎页类 | `Main` | `/` 或 `/index` 路由到 |
| 欢迎页方法 | `index` | 控制器默认方法 |
| 命名空间 | 自动检测 | 项目中 `Controller` 段 |
| 类名大小写调整 | 空 | 默认不会把 URL 中的 `user` 转成 `User`，需要配置 `controller_class_adjust` |
