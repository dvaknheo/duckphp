# 异常处理

DuckPHP 提供了一套完整的异常处理机制，包括异常层级、条件抛异常、异常报告和自定义异常处理。

## 异常层级

DuckPHP 推荐按以下层级组织异常：

```
\Exception                              # PHP 内置异常
  └─ {project}\System\ProjectException     # 项目异常基类
       ├─ {project}\System\BusinessException    # Business 层异常
       └─ {project}\System\ControllerException  # Controller 层异常
```

### 创建异常类

在 `src/System/` 目录下定义项目异常：

```php
<?php
// src/System/ProjectException.php
namespace MyProject\System;

use DuckPhp\Foundation\ExceptionTrait;

class ProjectException
{
    use ExceptionTrait;  // 提供 ThrowOn() 方法
}
```

```php
<?php
// src/System/BusinessException.php
namespace MyProject\System;

class BusinessException extends ProjectException
{
}
```

```php
<?php
// src/System/ControllerException.php
namespace MyProject\System;

class ControllerException extends ProjectException
{
}
```

### 配置异常类

在 `App` 中配置各层异常类：

```php
class App extends DuckPhp
{
    public $options = [
        'exception_for_project'    => ProjectException::class,
        'exception_for_business'   => BusinessException::class,
        'exception_for_controller' => ControllerException::class,
        'exception_reporter'       => ExceptionReporter::class,
    ];
}
```

| 选项 | 默认值 | 说明 |
|---|---|---|
| `exception_for_project` | `\Exception::class` | 项目异常基类 |
| `exception_for_business` | `null`（继承自 `exception_for_project`） | Business 层异常 |
| `exception_for_controller` | `null`（继承自 `exception_for_project`） | Controller 层异常 |
| `exception_reporter` | `null` | 异常报告器类名 |

## 条件抛异常

### 在 Controller 层

```php
use DuckPhp\Foundation\Controller\Helper;

class UserController
{
    public function action_profile()
    {
        $user = UserAction::_()->getCurrentUser();
        
        // 条件抛异常：未登录时抛出 ControllerException
        Helper::ControllerThrowOn(!$user, '请先登录', 403);
        
        Helper::Show(get_defined_vars(), 'user/profile');
    }
}
```

### 在 Business 层

```php
use DuckPhp\Foundation\Business\Helper;

class UserBusiness
{
    public function login($username, $password)
    {
        $user = UserModel::_()->findByUsername($username);
        
        // 条件抛异常：用户不存在或密码错误时抛出 BusinessException
        Helper::BusinessThrowOn(!$user, '用户不存在', 1001);
        Helper::BusinessThrowOn(!password_verify($password, $user['password']), '密码错误', 1002);
        
        return $user;
    }
}
```

### 在异常类上直接抛异常

```php
// 使用 ThrowOn 方法（需要 ExceptionTrait）
BusinessException::ThrowOn($balance < $amount, '余额不足', 2001);
```

## 异常报告器

异常报告器负责捕获并处理抛出的异常，可以按异常类型分发到不同的处理方法。

### 创建异常报告器

```php
<?php
// src/Controller/ExceptionReporter.php
namespace MyProject\Controller;

use DuckPhp\Foundation\ExceptionReporterTrait;

class ExceptionReporter
{
    use ExceptionReporterTrait;
    
    // 处理 BusinessException
    public function onBusinessException($ex)
    {
        // 记录日志、发送通知等
        Logger::_()->warning('Business error: ' . $ex->getMessage());
        
        // 返回错误响应
        Helper::ShowJson(['error' => $ex->getMessage(), 'code' => $ex->getCode()]);
    }
    
    // 处理 ControllerException
    public function onControllerException($ex)
    {
        // 权限错误等
        Helper::Show302('login');
    }
    
    // 处理其他项目异常（兜底）
    public function defaultException($ex)
    {
        // 调用框架默认处理
        App::Current()->_OnDefaultException($ex);
    }
}
```

### 异常报告器工作原理

`ExceptionReporterTrait` 的 `OnException` 方法按以下逻辑分发：

1. 获取异常类名（如 `MyProject\System\BusinessException`）
2. 检查异常是否属于当前项目命名空间
3. 如果不是项目异常，调用 `defaultException()`
4. 如果是项目异常，提取类名（如 `BusinessException`）
5. 查找对应的处理方法（如 `onBusinessException()`）
6. 如果找到则调用，否则调用 `defaultException()`

### 方法命名规则

| 异常类名 | 处理方法 |
|---------|---------|
| `BusinessException` | `onBusinessException($ex)` |
| `ControllerException` | `onControllerException($ex)` |
| `ProjectException` | `onProjectException($ex)` |
| 其他（非项目异常） | `defaultException($ex)` |

## 框架默认异常处理

当未配置 `exception_reporter` 或异常未被报告器处理时，框架使用默认处理：

### 调试模式（`is_debug = true`）

- 显示详细的错误信息（异常类、消息、堆栈跟踪）
- 便于开发和调试

### 生产模式（`is_debug = false`）

- 显示 `error_500` 配置的视图（如 `_sys/error_500`）
- 记录错误日志（如果启用 `default_exception_do_log`）
- 不暴露敏感信息

## 自定义异常处理

### 覆盖默认异常处理

在 `App` 中覆盖 `_OnDefaultException` 方法：

```php
class App extends DuckPhp
{
    public function _OnDefaultException($ex): void
    {
        // 自定义错误处理逻辑
        
        // 记录日志
        Logger::_()->error($ex->getMessage());
        
        // 发送错误通知（如邮件、钉钉）
        // NotifyService::_()->sendErrorAlert($ex);
        
        // 调用父类默认处理
        parent::_OnDefaultException($ex);
    }
}
```

### 使用 ExceptionManager 注册自定义处理器

```php
use DuckPhp\Core\ExceptionManager;

// 注册特定异常类型的处理器
ExceptionManager::_()->assignExceptionHandler(
    ValidationException::class,
    function ($ex) {
        // 返回验证错误响应
        Helper::ShowJson(['errors' => $ex->errors]);
    }
);

// 注册多个异常类型的统一处理器
ExceptionManager::_()->setMultiExceptionHandler(
    [BusinessException::class, ControllerException::class],
    function ($ex) {
        // 统一处理
    }
);

// 设置默认异常处理器
ExceptionManager::_()->setDefaultExceptionHandler(
    function ($ex) {
        // 处理所有未被捕获的异常
    }
);
```

## 开发错误处理

框架自动捕获 PHP 错误（Notice、Warning 等）并转换为异常：

```php
// 触发一个 Notice
$undefined = $nonexistent_var;  // 被转换为 ErrorException
```

### 配置开发错误处理

```php
$options = [
    'handle_all_dev_error' => true,   // 是否处理 PHP 错误
    'handle_all_exception' => true,   // 是否处理未捕获异常
];
```

## 异常处理流程图

```
抛出异常
  │
  ▼
ExceptionManager._CallException()
  │
  ├─ 匹配 exceptionHandlers 中的注册处理器
  │     └─ 找到匹配 → 执行自定义处理器
  │
  └─ 未匹配 → 执行 default_exception_handler
        │
        ├─ 配置了 exception_reporter
        │     └─ ExceptionReporter::OnException()
        │           ├─ 匹配 on{ExceptionClass}() → 执行
        │           └─ 未匹配 → defaultException()
        │
        └─ 未配置 exception_reporter
              └─ App::_OnDefaultException()
                    ├─ 调试模式 → 显示详细错误
                    └─ 生产模式 → 显示 error_500 视图
```

## 最佳实践

1. **按层使用对应异常**：Controller 层用 `ControllerException`，Business 层用 `BusinessException`
2. **使用条件抛异常**：用 `Helper::ControllerThrowOn()` / `Helper::BusinessThrowOn()` 替代 `if + throw`
3. **异常消息用户友好**：异常消息最终会展示给用户，应使用易懂的语言
4. **异常代码有含义**：使用有意义的错误代码，便于前端根据 code 做不同处理
5. **异常报告器做日志**：在 `ExceptionReporter` 中记录异常日志，便于排查问题
6. **生产环境不暴露细节**：确保 `is_debug = false` 时不会泄露敏感信息
