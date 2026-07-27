# DuckPHP 项目开发规则（AI 即用版）

> 本文件供 AI 辅助开发时参考。目标是让 AI 理解 DuckPHP 的心智模型、目录约定、层级铁律和常见模式，能独立完成功能开发。

---

## 1. 心智模型

DuckPHP 的核心概念用三句话概括：

**① 一个类就是一个单例**
所有类通过 `ClassName::_()` 获取实例，由 PhaseContainer 统一管理。不是自己 `new`。

```php
UserModel::_()->getUser(1);      // ✓ 正确方式
$model = new UserModel();        // ✗ 不要自己 new
```

**② Phase = 实例空间**
每个子应用运行在一个独立的 Phase（命名空间）里。同一个类名在不同 Phase 下可以有不同的实例。
根 App 的 Phase 是空字符串 `""`。`::_()` 总是返回当前 Phase 下的实例。

**③ Controller → Business → Model 单向调用**
不允许反向调用，不允许跨层跳跃。

```
Controller (输入输出)
    ↓
Business   (业务逻辑)
    ↓
Model      (数据访问)
```

---

## 2. 快速上手：添加一个新功能

假设要加一个"用户管理"功能，需要 4 步：

```
第 1 步：写 Model          src/Model/UserModel.php        数据层
第 2 步：写 Business       src/Business/UserBusiness.php  业务层
第 3 步：写 Controller     src/Controller/UserController.php  路由入口
第 4 步：写 View           view/user/index.php            模板
```

### 第 1 步：Model

```php
<?php
namespace YourProjectName\Model;

use DuckPhp\Foundation\Model\Base;

class UserModel extends Base
{
    public function getUser(int $id): ?array
    {
        return $this->find($id);
    }
    public function getUserList(int $page = 1, int $size = 10): array
    {
        return $this->getList([], $page, $size); // [$total, $data]
    }
    public function addUser(array $data): int
    {
        return $this->add($data);
    }
}
```

Model 自动推导表名：`UserModel` → 表 `user`。可通过 `$table_name` 覆盖。

### 第 2 步：Business

```php
<?php
namespace YourProjectName\Business;

use YourProjectName\Model\UserModel;

class UserBusiness extends Base
{
    public function getProfile(int $id): array
    {
        $user = UserModel::_()->getUser($id);
        if (!$user) {
            Helper::BusinessThrowOn(true, '用户不存在');
        }
        // 可以在这里做数据加工
        return $user;
    }
}
```

### 第 3 步：Controller

```php
<?php
namespace YourProjectName\Controller;

use YourProjectName\Business\UserBusiness;

class UserController extends Base
{
    public function index()
    {
        $page = Helper::GET('page', 1);
        [$total, $list] = UserBusiness::_()->getUserList((int)$page);
        Helper::Show(get_defined_vars(), 'user/index');
    }
    public function profile()
    {
        $id = Helper::GET('id');
        $user = UserBusiness::_()->getProfile((int)$id);
        Helper::Show(get_defined_vars(), 'user/profile');
    }
}
```

### 第 4 步：View

```php
<!-- view/user/index.php -->
<h1>用户列表</h1>
<ul>
<?php foreach ($list as $item): ?>
    <li><?= __h($item['name']) ?></li>
<?php endforeach; ?>
</ul>
<p>共 <?= $total ?> 人</p>
```

---

## 3. 目录与文件约定

```
project/
├── config/
│   └── DuckPhpSettings.config.php  # 数据库等全局设置
├── public/
│   └── index.php                   # Web 入口（勿改）
├── src/
│   ├── Controller/                 # —— 控制器层 ——
│   │   ├── Base.php                #     基类（勿改）
│   │   ├── Helper.php              #     静态辅助（勿改）
│   │   ├── MainController.php      #     欢迎页/短路由
│   │   ├── Session.php             #     Session 管理
│   │   ├── *Action.php             # Action 类（可选）
│   │   └── *Controller.php         # 你的控制器
│   ├── Business/                   # —— 业务层 ——
│   │   ├── Base.php                #     基类（勿改）
│   │   ├── Helper.php              #     静态辅助（勿改）
│   │   ├── *Service.php            #     Service 类（可选）
│   │   └── *Business.php           # 你的业务类
│   ├── Model/                      # —— 模型层 ——
│   │   ├── Base.php                #     基类（勿改，内部集成 Db() / find() / add() 等）
│   │   └── *Model.php              # 你的模型类（继承 Base，直接使用 $this->find() / static::Db()）
│   └── System/
│       └── App.php                 # 应用配置
├── view/                           # —— 视图 ——
│   └── {Controller}/{action}.php
├── runtime/                        # 日志缓存（可写）
├── cli.php                         # CLI 入口（勿改）
└── vendor/
```

---

## 4. 层级调用铁律

### 允许/禁止矩阵

| 层 | 可调用 | 禁止调用 |
|---|--------|---------|
| **Controller** | Action, Business, Helper, Session | **Model**（不能绕过 Business） |
| **Business** | Model, Service, Helper | Session, `$_GET`/`$_POST`/`$_SERVER` |
| **Model** | 仅数据访问相关 | 业务逻辑, 抛异常 |
| **View** | 全局函数 `__h()` `__url()` `__l()` | 框架类（尽量不要） |

### 违规示例

```php
// ✗ Controller 直接调 Model
class UserController
{
    public function index()
    {
        $data = UserModel::_()->getUser(1); // 违规！应通过 Business
    }
}

// ✗ Business 读超全局变量
class UserBusiness
{
    public function login()
    {
        $name = $_POST['username']; // 违规！应由 Controller 传入参数
        // 改为 function login(string $username, string $password)
    }
}

// ✗ Model 抛业务异常
class UserModel
{
    public function getUser($id)
    {
        if (!$id) {
            throw new \Exception('ID 不能为空'); // 违规！由 Business 层抛
        }
    }
}
```

---

## 5. 代码模板

### Controller 标准模板

```php
<?php
namespace YourProjectName\Controller;

use YourProjectName\Business\SomeBusiness;

class SampleController extends Base
{
    public function index()
    {
        // 接收输入
        $param = Helper::GET('key', 'default');
        
        // 调 Business
        $data = SomeBusiness::_()->doSomething($param);
        
        // 输出
        Helper::Show(get_defined_vars(), 'sample/index');
    }
}
```

### Business 标准模板

```php
<?php
namespace YourProjectName\Business;

use YourProjectName\Model\SomeModel;

class SampleBusiness extends Base
{
    public function doSomething(string $input): array
    {
        // 从 Model 获取数据
        $result = SomeModel::_()->findByKey($input);
        
        // 业务校验
        if (!$result) {
            Helper::BusinessThrowOn(true, '数据不存在');
        }
        
        // 数据加工后返回
        return $result;
    }
}
```

### Model 标准模板

```php
<?php
namespace YourProjectName\Model;

use DuckPhp\Foundation\Model\Base;

class UserModel extends Base
{
    public function getUser(int $id): ?array
    {
        return $this->find($id);
    }
    public function getUserList(int $page = 1, int $size = 10): array
    {
        return $this->getList([], $page, $size); // [$total, $data]
    }
    public function addUser(array $data): int
    {
        return $this->add($data);
    }
}
```

Model 自动推导表名：`UserModel` → 表 `user`。可通过 `$table_name` 覆盖。
完整方法见**第 6 节 ModelTrait 实例方法**。

### Model 直接写 SQL

如需手写 SQL，用 `static::Db()` 获取数据库连接对象（无读写分离时）或 `static::DbForRead()` / `static::DbForWrite()`（有读写分离时）：

```php
<?php
namespace YourProjectName\Model;

use DuckPhp\Foundation\Model\Base;

class ReportModel extends Base
{
    // 用 Db() 查询多行
    public function getReportsByUser(int $userId): array
    {
        return static::Db()->fetchAll('SELECT * FROM report WHERE user_id = ?', $userId);
    }
    // 用 Db() 查单行
    public function getReport(int $id): ?array
    {
        return static::Db()->fetch('SELECT * FROM report WHERE id = ?', $id);
    }
    // 用 Db() 新增（含事务）
    public function createReport(array $data): int
    {
        static::Db()->execute('INSERT INTO report (user_id, title) VALUES (?, ?)', $data['user_id'], $data['title']);
        return static::Db()->lastInsertId();
    }
    // 用 Db() 更新
    public function updateReport(int $id, string $title): void
    {
        static::Db()->execute('UPDATE report SET title = ? WHERE id = ?', $title, $id);
    }
    // 用 Db() 删除
    public function deleteReport(int $id): void
    {
        static::Db()->execute('DELETE FROM report WHERE id = ?', $id);
    }
    // 分页查询
    public function searchReports(string $keyword, int $page, int $size): array
    {
        $sql = "SELECT * FROM report WHERE title LIKE ?";
        $countSql = static::DbForWrite()->SqlForCountSimply($sql);
        $total = static::DbForWrite()->fetchColumn($countSql, '%' . $keyword . '%');
        
        $pageSql = static::DbForWrite()->SqlForPager($sql, $page, $size);
        $list = static::Db()->fetchAll($pageSql, '%' . $keyword . '%');
        
        return [$total, $list];
    }
}
```

> `static::Db()` 返回数据库连接对象（`DuckPhp\Db\Db`），提供 `fetch`、`fetchAll`、`fetchColumn`、`execute`、`lastInsertId` 等方法。
> `static::DbForWrite()`（写连接）和 `static::DbForRead()`（读连接）在不做读写分离时与 `static::Db()` 行为一致。

---

## 6. 常用 API 速查

### Controller Helper 方法

```php
Helper::GET('key', 'default');          // $_GET
Helper::POST('key', 'default');         // $_POST
Helper::REQUEST('key', 'default');      // $_REQUEST
Helper::SERVER('key');                  // $_SERVER
Helper::Show($data, 'view_name');       // 渲染视图
Helper::ShowJson($data);                // JSON 输出
Helper::Show302('url');                 // 重定向
Helper::Show404();                      // 404
Helper::Url('/path');                   // 生成 URL
Helper::Res('/file');                   // 资源 URL
Helper::exit();                         // 终止
Helper::ControllerThrowOn($flag, $msg); // 抛控制器异常
```

### Business Helper 方法

```php
Helper::Setting('key');                 // 读取 DuckPhpSettings
Helper::Config('file', 'key');          // 读取 config/file.php
Helper::BusinessThrowOn($flag, $msg);   // 抛业务异常
Helper::XpCall($callable);              // 安全调用（捕获异常）
```

### Model 数据连接方法（在 Model 子类内用 `static::` 或 `$this->` 调用）

这些方法由 `DuckPhp\Foundation\Model\Base` 提供（同时使用了 `ModelTrait` 和 `ModelHelperTrait`），**不需要额外引入 Helper 类**：

```php
static::Db();                              // 数据库连接（无读写分离时用这个）
static::DbForRead();                       // 读连接
static::DbForWrite();                      // 写连接
static::Db()->fetch($sql, ...$args);       // 查单行
static::Db()->fetchAll($sql, ...$args);    // 查多行
static::Db()->fetchColumn($sql, ...$args); // 查单列
static::Db()->execute($sql, ...$args);     // 执行 SQL
static::Db()->lastInsertId();              // 最后插入 ID
static::Db()->SqlForPager($sql,$page,$sz); // 分页 SQL
static::Db()->SqlForCountSimply($sql);     // COUNT SQL
```

### ModelTrait 快捷方法（在 Model 子类内用 `$this->` 调用）

```php
$this->find($id);                        // 按主键查
$this->add($data);                       // 新增 → 返回自增 ID
$this->update($id, $data);               // 按主键更新
$this->getList($where, $page, $size);    // 分页 → [$total, $data]
$this->fetchAll($sql, ...$args);         // 自定义 SQL 多行
$this->fetch($sql, ...$args);            // 自定义 SQL 单行
$this->fetchColumn($sql, ...$args);      // 自定义 SQL 单列
$this->execute($sql, ...$args);          // 自定义 SQL 写入
$this->table();                          // 完整表名（前缀+名称）
$this->prepare($sql);                    // 替换 `` `'TABLE'` `` 为完整表名
```

### 全局函数

```php
__h($str);          // HTML 转义
__url($url);        // 生成 URL
__res($file);       // 生成资源 URL
__l($key);          // 多语言
```

---

## 7. 常见陷阱

### 忘记 `::_()` 直接 `new`

```php
$model = new UserModel();               // ✗ Phase 管理失效
$model = UserModel::_();                // ✓ 正确
```

### Business 里读写 Session

```php
class UserBusiness {
    public function login() {
        $_SESSION['user_id'] = $id;      // ✗ Business 无状态
    }
}
// Session 操作放在 Controller\Session 类中
```

### Controller 直接调 Model

```php
class UserController {
    public function index() {
        $data = UserModel::_()->getAll(); // ✗ 绕过 Business
    }
}
```

### 视图文件路径写错

视图文件放在 `view/{Controller名小写}/{action名}.php`。例如 `UserController::index()` → `view/user/index.php`。如果 Helper::Show 指定了第二个参数，则用第二个参数路径。

---

## 8. 参考：当前项目骨架结构

```
skeleton/
├── config/
│   └── DuckPhpSettings.config.php      # 全局设置模板
├── src/
│   ├── Controller/
│   │   ├── Base.php                    # 基类（继承 Foundation\Controller\Base）
│   │   ├── Helper.php                  # 静态辅助（继承 Foundation\Controller\Helper）
│   │   ├── MainController.php          # 欢迎页
│   │   └── Session.php                 # Session 管理
│   ├── Business/
│   │   ├── Base.php                    # 基类（继承 Foundation\Business\Base）
│   │   ├── Helper.php                  # 静态辅助（继承 Foundation\Business\Helper）
│   │   └── DemoBusiness.php            # 示例（可删除）
│   ├── Model/
│   │   ├── Base.php                    # 基类（继承 Foundation\Model\Base）
│   │   └── DemoModel.php               # 示例（可删除）
│   └── System/
│       └── App.php                     # 应用配置入口
├── view/
│   ├── _sys/
│   │   ├── error_404.php
│   │   └── error_500.php
│   └── main.php
├── public/index.php
├── cli.php
└── composer.json
```

---

## 9. 错误报告与调试

### 开发环境 vs 生产环境

| 选项 | 开发环境 | 生产环境 |
|------|---------|---------|
| `is_debug` | `true` | `false` |
| `error_debug` | `'_sys/error-debug.php'`（详细堆栈） | `null`（关闭） |
| `error_500` | `'_sys/error_500.php'` | 自定义友好页面 |
| `error_404` | `'_sys/error_404.php'` | 自定义友好页面 |

在 `App.php` 中按环境设置：

```php
class App extends DuckPhp
{
    public $options = [
        'is_debug' => true,                     // 上线前改为 false
        
        // 开发环境：显示详细错误
        'error_debug' => '_sys/error-debug.php',
        
        // 生产环境：显示友好页面（与 error_debug 互斥）
        //'error_500' => '_sys/error_500.php',
        //'error_404' => '_sys/error_404.php',
    ];
}
```

### 自定义错误页面

在 `view/_sys/` 下放模板文件，框架会在对应错误发生时自动渲染：

```php
<!-- view/_sys/error_404.php -->
<h1>页面不存在</h1>
<p>您访问的页面未找到。</p>
```

```php
<!-- view/_sys/error_500.php -->
<h1>服务器内部错误</h1>
<p>请稍后再试。</p>
```

### 主动抛异常

```php
// Controller 层
Helper::ControllerThrowOn($条件, '错误消息');
Helper::Show404();                    // 直接 404

// Business 层
Helper::BusinessThrowOn($条件, '错误消息');
```

---

## 10. 实现自己的 GlobalUser 组件

DuckPHP 的用户/管理员系统通过**回调 + PhaseProxy 代理**机制实现，可作为一个独立组件提供给其他项目或子应用使用。

### 架构概述

```
你的项目（主 App）
  ├── options['class_user'] = MyUserProvider::class   ← 注册
  │
  └── 其他代码通过 Helper 调用：
        Helper::UserId()          → GlobalUser::_()->id()
        Helper::UserName()        → GlobalUser::_()->name()
        Helper::UserService()->batchGetUsernames([])  → 批量查询
```

### 你需要实现的两个接口

#### `UserActionInterface` — 用户操作（处理 Session/Token）

```php
<?php
namespace YourProject;

use DuckPhp\GlobalUser\UserActionInterface;

class MyUserAction implements UserActionInterface
{
    use \DuckPhp\Foundation\SingletonTrait;

    // 从 Session/Token 获取当前用户 ID
    public function id(bool $check_login = true): mixed
    {
        $id = $_SESSION['user_id'] ?? null;
        if ($check_login && !$id) {
            throw new \DuckPhp\Core\DuckPhpSystemException('未登录');
        }
        return $id;
    }
    // 获取当前用户名
    public function name(bool $check_login = true): mixed
    {
        return $_SESSION['username'] ?? null;
    }
    // 获取当前用户完整数据
    public function data(bool $check_login = true): mixed
    {
        if (!$id = $this->id($check_login)) {
            return null;
        }
        return ['id' => $id, 'name' => $this->name()];
    }

    // —— URL 生成 ——
    public function urlForLogin(?string $url_back = null, ?array $ext = null): string
    {
        return '/login';
    }
    public function urlForLogout(?string $url_back = null, ?array $ext = null): string
    {
        return '/logout';
    }
    public function urlForHome(?string $url_back = null, ?array $ext = null): string
    {
        return '/';
    }
    public function urlForRegist(?string $url_back = null, ?array $ext = null): string
    {
        return '/register';
    }

    // —— 服务层入口 ——
    public function service(): object
    {
        return MyUserService::_();
    }
}
```

#### `UserServiceInterface` — 用户数据服务（批量查询等）

```php
<?php
namespace YourProject;

use DuckPhp\GlobalUser\UserServiceInterface;

class MyUserService implements UserServiceInterface
{
    use \DuckPhp\Foundation\SingletonTrait;

    // 批量获取用户名（给其他 Business 用）
    public function batchGetUsernames(array $ids): array
    {
        return MyUserModel::_()->getUsernamesByIds($ids);
    }
    // 权限检查
    public function checkAccess($user_id, string $class, string $method, ?string $url = null): void
    {
        // 不需要额外检查，默认放行
    }
}
```

### 注册组件

在项目的 `App.php` 中通过 `class_user` 选项注册：

```php
class App extends DuckPhp
{
    public $options = [
        'class_user' => MyUserAction::class,           // ← 注册用户系统
        // 'class_admin' => MyAdminAction::class,      // ← 管理员系统同理
    ];
}
```

### 在 Controller / Business 中使用

```php
// Controller 中获取用户信息
$userId   = Helper::UserId();                   // 未登录则抛异常
$userId   = Helper::UserId(false);              // 不抛异常，null 表未登录
$userName = Helper::UserName();
$userData = Helper::User()->data();

// Business 中批量查询（通过 Service 接口）
$names = Helper::UserService()->batchGetUsernames([1, 2, 3]);

// 获取 User 对象的其他方法
Helper::User()->urlForHome();                    // 首页 URL
Helper::User()->urlForLogin();                   // 登录 URL
Helper::User()->urlForLogout();                  // 登出 URL
```

### 管理员系统（GlobalAdmin）

与 GlobalUser 完全相同的模式，只是接口名不同：

```php
// App.php
'class_admin' => MyAdminAction::class,

// 使用
Helper::AdminId();
Helper::AdminName();
Helper::AdminService()->checkAccess($adminId, $class, $method);
Helper::Admin()->urlForHome();
```

区别：管理员系统无注册接口，通常用于后台管理模块。

