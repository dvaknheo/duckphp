# 项目结构与编码规则

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
│   ├── Controller/                   # 控制器层 HTTP 请求入口
│   │   ├── Base.php
│   │   ├── ExceptionReporter.php     # 异常报告器
│   │   ├── Helper.php
│   │   ├── MainController.php
│   │   ├── Session.php               # Session 管理
│   │   ├── SomeAction.php            # Action 示例
│   │   └── testController.php        # 测试控制器
│   ├── Business/                     # 业务层 业务逻辑
│   │   ├── Base.php
│   │   ├── DemoBusiness.php          # Business 示例
│   │   ├── Helper.php
│   │   └── SomeService.php           # Service 示例
│   ├── Model/                        # 模型层 数据访问
│   │   ├── Base.php
│   │   ├── DemoModel.php             # Model 示例
│   │   └── Helper.php
│   └── System/                       # 系统层 应用配置与异常
│       ├── App.php                   # 应用核心配置
│       ├── BusinessException.php     # Business 异常
│       ├── ControllerException.php   # Controller 异常
│       └── ProjectException.php      # 项目异常基类
├── view/                             # 视图目录
│   └── _sys/                         # 系统视图
│       ├── error_404.php
│       └── error_500.php
├── runtime/                          # 运行时目录（日志等）
├── cli.php                           # CLI 入口
└── vendor/
```

> **注意**：`SomeAction.php`、`testController.php`、`DemoBusiness.php`、`SomeService.php`、`DemoModel.php` 是示例文件，实际项目中应删除并根据业务需求编写类似的类。
>
> `runtime/` 目录需要可写权限。

## 编码规则

### 命名规范

| 类型 | 命名规则 | 示例 | 说明 |
|------|---------|------|------|
| 控制器类 | `{Name}Controller` | `UserController` | 路由入口，处理输入/输出 |
| 控制器方法 | `action_{method}` | `action_index()` | 路由方法前缀 |
| 动作类 | `{Name}Action` | `UserAction` | 控制器通用功能复用 |
| Session 类 | `Session` | `Session` | 状态容器 |
| 业务类 | `{Name}Business` | `UserBusiness` | 业务逻辑编排 |
| Service 类 | `{Name}Service` | `CommonService` | 业务通用功能复用 |
| Model 类 | `{Name}Model` | `UserModel` | 数据访问 |
| 异常类 | `{Name}Exception` | `ProjectException` | 异常层级 |

### 核心原则

#### 系统层（System）

1. 框架相关调用集中在 **System 层** 处理。
2. `System` 命名空间负责处理框架相关调用、异常定义和应用配置。

#### 控制器层（Controller）

1. Controller 层请勿直接调用 `DuckPhp` 命名空间下的类。
2. Session 操作集中在 `Session` 类中处理。
3. 控制器类作为 HTTP 请求入口，处理输入/输出，控制器之间不互相调用。
4. 控制器之间共享的操作可封装成 **Action 类**。
5. 控制器类和动作类应继承 `Base` 类。
6. 控制器类和动作类调用 **Business 类**。
7. 动作类是无状态的，因此必须有空的 `__construct()` 覆盖基类的构造函数。

#### 业务层（Business）

1. 业务层请勿直接调用 `DuckPhp` 命名空间下的类。
2. 业务层保持**纯无状态**，可被 CLI、测试、API 等任意环境复用。
3. 业务类之间共享的操作可封装成 **Service 类**。
4. 业务类和服务类应继承 `Base` 类。
5. 业务类和服务类调用 **Model 类**。

#### 模型层（Model）

1. 模型层请勿直接调用 `DuckPhp` 命名空间下的类。
2. 模型层保持**纯无状态**，仅负责数据访问。
3. Model 一般按数据库表名对应。
4. Model 类禁止抛异常，由调用者处理异常。

#### 视图层（View）

1. 视图文件禁止做复杂计算，只用于显示输出。
2. `view/_sys/` 存放系统视图（如错误页面）。
3. `view/{ControllerName}/{ActionName}` 用于存放控制器对应的视图。

### 辅助规范

1. 控制器类和动作类使用 `Helper::ControllerThrowOn()` 抛出异常。
2. 业务层和服务类使用 `Helper::BusinessThrowOn()` 抛出异常。
3. 各层级的 `Helper` 类一般情况下不需要增加方法。
4. 工程规范中，Model 层的 `Helper` 类可以并入 `Base` 类。
