# 生命周期与事件

## 应用生命周期

DuckPHP 应用从启动到结束经历一系列阶段，每个阶段都有对应的钩子方法供开发者介入。

### 完整流程

```
App::RunQuickly($options)
  │
  ├─ new App() + init($options)
  │    │
  │    ├─ initOptions()          ← 合并选项
  │    ├─ initContainer()        ← 初始化相位容器
  │    ├─ initException()        ← 设置异常/错误处理器
  │    ├─ onPrepare()            ← 准备回调 ①
  │    ├─ prepareComponents()    ← 准备组件
  │    ├─ initComponents()       ← 初始化组件（路由、视图等）
  │    ├─ initExtentions(ext)    ← 初始化扩展
  │    ├─ onInit()               ← 初始化完成回调 ②
  │    ├─ onBeforeChildrenInit() ← 子应用初始化前回调 ③
  │    ├─ initExtentions(app)    ← 初始化子应用
  │    ├─ is_inited = true
  │    └─ onInited()             ← 全部就绪回调 ④
  │
  ├─ run()
  │    │
  │    ├─ onBeforeRun()          ← 运行前回调 ⑤
  │    ├─ Route 执行
  │    │    ├─ prepend-outter 钩子（检查状态、重写）
  │    │    ├─ prepend-inner 钩子（路由映射）
  │    │    ├─ 默认路由 → 控制器方法
  │    │    ├─ append-inner 钩子
  │    │    └─ append-outter 钩子（资源、路由映射）
  │    ├─ 如果路由未处理：→ _On404()
  │    └─ onAfterRun()           ← 运行后回调 ⑥
  │
  └─ 返回 true/false
```

### 覆盖生命周期方法

在 `src/System/App.php` 中覆盖这些方法：

```php
namespace MyProject\System;

use DuckPhp\DuckPhp;

class App extends DuckPhp
{
    protected function onPrepare()
    {
        parent::onPrepare();
        // 1. 准备阶段：加载配置、注册扩展
        // 此时组件尚未完全初始化
    }

    protected function onInit()
    {
        parent::onInit();
        // 2. 框架初始化完毕，可以操作组件
        $this->assignRoute('home', function () {
            echo "Custom home";
        });
    }

    protected function onInited()
    {
        parent::onInited();
        // 3. 所有初始化完成（包括子应用）
        // 这是运行前最后的回调
    }

    protected function onBeforeRun()
    {
        parent::onBeforeRun();
        // 4. 即将开始路由分发
    }

    protected function onAfterRun()
    {
        parent::onAfterRun();
        // 5. 请求处理完成
    }
}
```

## Session 管理

 推荐方式：使用 `SimpleSessionTrait`（惰性启动）

Session 属于 Controller 层，与 Action、MainController 的分工如下：

```
Controller 层
  ├── MainController    路由入口（输入输出）
  ├── Action            通用编排（调 Business + Session）
  └── Session          纯状态容器（不调其他类）
                              │
Business 层                   ▼
  └── UserBusiness      业务逻辑（调 Model，不碰 Session）
```

`SimpleSessionTrait` 在首次读写 Session 时自动调用 `session_start()`，无需手动启动：

```php
<?php
namespace MyProject\Controller;

use DuckPhp\Foundation\SimpleSessionTrait;

class Session
{
    use SimpleSessionTrait;  // 自带惰性 session_start() + get/set/unset
    
    const KEY_USER_ID = 'user_id';
    
    public function setUserId($id): void
    {
        $this->set(static::KEY_USER_ID, $id);
    }
    
    public function getUserId(): int
    {
        return (int)$this->get(static::KEY_USER_ID, 0);
    }
    
    public function clearUserId(): void
    {
        $this->unset(static::KEY_USER_ID);
    }
    
    public function isLoggedIn(): bool
    {
        return $this->getUserId() > 0;
    }
}
```

Session 之上，创建 Action 类封装可复用的编排逻辑。Action **只能调 Business 和 Session，不能直接调 Model**：

```php
<?php
namespace MyProject\Controller;

use MyProject\Business\UserBusiness;
use MyProject\Controller\Session;

class UserAction
{
    public function login(string $username, string $password): array
    {
        $user = UserBusiness::_()->login($username, $password);
        Session::_()->setUserId($user['id']);
        return $user;
    }
    
    public function getCurrentUser(): ?array
    {
        $id = Session::_()->getUserId();
        return $id ? UserBusiness::_()->getUser($id) : null;  // → Business，不直接调 Model
    }
    
    public function isLoggedIn(): bool
    {
        return Session::_()->isLoggedIn();
    }
    
    public function logout(): void
    {
        Session::_()->clearUserId();
    }
}
```

Controller 中只做输入输出，委托给 Action：

```php
class MainController
{
    public function action_login()
    {
        if (UserAction::_()->isLoggedIn()) { /* redirect */ }
        try {
            UserAction::_()->login($username, $password);
        } catch (\Exception $ex) { /* show error */ }
    }
    
    public function action_profile()
    {
        $user = UserAction::_()->getCurrentUser();
        // ...
    }
    
    public function action_logout()
    {
        UserAction::_()->logout();
        Helper::Show302('');
    }
}
```

`SimpleSessionTrait` 提供了三个 protected 方法：

| 方法 | 说明 |
|---|---|
| `get($key, $default)` | 读取 Session，自动 `session_start()` |
| `set($key, $value)` | 写入 Session，自动 `session_start()` |
| `unset($key)` | 删除 Session 键，自动 `session_start()` |

这样 Business 层保持纯无状态，可被 CLI、测试、API 等任意环境复用。

### 底层 Session 读写

如果需要在 Controller 层直接读写 Session（不使用 `SimpleSessionTrait`）：

```php
use DuckPhp\Core\SuperGlobal;

// 注意：直接使用 SuperGlobal 前需要自行 session_start()
\DuckPhp\Core\SystemWrapper::_()->_session_start();

// 写入 Session
SuperGlobal::_()->_SessionSet('cart_items', $items);

// 读取 Session
$items = SuperGlobal::_()->_SessionGet('cart_items', []);

// 删除 Session 键
SuperGlobal::_()->_SessionUnset('cart_items');
```

## 事件系统

框架内置了事件管理器 `DuckPhp\Core\EventManager`，支持自定义事件的监听和触发。

### 触发事件

```php
use DuckPhp\Core\EventManager;

EventManager::FireEvent('user_registered', $userId, $username);
// 或
Helper::FireEvent('user_registered', $userId, $username);
```

### 监听事件

```php
// 在 App::onInit() 或其他初始化阶段注册
EventManager::OnEvent('user_registered', function ($userId, $username) {
    // 发送欢迎邮件
    // 记录日志
});
```

### 移除事件

```php
EventManager::RemoveEvent('user_registered');
// 或移除特定回调
EventManager::RemoveEvent('user_registered', $callback);
```

### 框架内置事件

框架自身在某些生命周期点触发事件：

```php
App::FireEvent('onPrepare');      // 对应 onPrepare()
App::FireEvent('onInit');          // 对应 onInit()
App::FireEvent('onInited');       // 对应 onInited()
App::FireEvent('onBeforeRun');    // 对应 onBeforeRun()
App::FireEvent('onAfterRun');     // 对应 onAfterRun()
App::FireEvent('onBeforeOutput'); // 输出前
App::FireEvent('On404');          // 404 时
```

## 异常处理

### 异常层级

```
\Exception                     # PHP 内置
  └─ {project}\ProjectException     # 项目异常基类
       ├─ {project}\BusinessException    # Business 层异常
       └─ {project}\ControllerException  # Controller 层异常
```

### 配置异常处理

```php
class App extends DuckPhp
{
    public $options = [
        'exception_for_project' => ProjectException::class,
        'exception_for_business' => BusinessException::class,
        'exception_for_controller' => ControllerException::class,
        'exception_reporter' => ExceptionReporter::class,
    ];
}
```

### 条件抛异常

```php
// 在 Controller 中
Helper::ControllerThrowOn($flag, '权限不足', 403);

// 在 Business 中
Helper::BusinessThrowOn($flag, '余额不足', 1001);
```

### 自定义异常报告器

```php
namespace MyProject\Controller;

class ExceptionReporter
{
    public static function OnException(\Throwable $ex)
    {
        // 记录异常日志
        // 返回错误响应
    }
}
```

## 调试模式

启用调试模式后，框架会显示详细的错误信息：

```php
$options = [
    'is_debug' => true,
];
```

调试模式下可用的全局函数：

```php
__var_dump($var);     // 在页面中 var_dump（仅调试模式可见）
__var_log($var);      // 记录变量到日志
__trace_dump();       // 打印调用栈
__debug_log('msg');   // 写入调试日志
__is_debug();         // 判断是否调试模式
```
