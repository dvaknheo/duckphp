# 四层架构（Controller → Business → Model → View）

DuckPHP 采用 **Controller（控制器）→ Business（业务）→ Model（模型）→ View（视图）** 四层架构。

### 完整层级总览

```
Controller 层 (可处理请求上下文)
  ├── MainController      路由入口，输入输出（调 Business）
  ├── Helper              静态辅助方法
  ├── Session             纯状态容器（不调其他类）
  └── Action              功能复用
                                │
Business 层 (纯无状态)           ▼
  ├── UserBusiness         业务逻辑（调 Model）
  ├── Service              通用功能（调 Model）
  └── Helper               静态辅助方法
                                │
Model 层 (纯无状态)              ▼
  └── UserModel            数据访问（单表 CRUD）
                                │
View 层                         ▼
  └── *.php                模板渲染
```

> **编码规则**：`Controller`、`Business`、`Model` 层除基础代码外，请勿直接调用 `DuckPhp` 命名空间下的类。框架相关调用集中在 `System` 层处理。

## 控制器（Controller）

控制器是 HTTP 请求的入口，负责：
- 接收用户输入（GET/POST 等）
- 调用 Business 层处理业务
- 决定输出（视图/JSON/重定向等）

### 基础用法

```php
<?php
namespace MyProject\Controller;

use MyProject\Business\MyBusiness;
use DuckPhp\Foundation\Controller\Helper;

class MyController
{
    public function action_index()
    {
        // 获取业务数据
        $data = MyBusiness::_()->getList();
        
        // 渲染视图
        Helper::Show(get_defined_vars(), 'my/index');
    }
    
    public function action_detail()
    {
        $id = Helper::GET('id');
        $item = MyBusiness::_()->getDetail($id);
        
        Helper::Show(get_defined_vars());
        // 视图文件默认为 控制器类名/方法名，即 MyController/action_detail
    }
}
```

### 使用 Foundation Base 类（推荐）

```php
<?php
namespace MyProject\Controller;

use DuckPhp\Foundation\Controller\Base;

class MainController extends Base
{
    public function action_index()
    {
        Helper::Show(get_defined_vars(), 'main');
    }
}
```

继承 `Foundation\Controller\Base` 后，自动获得：
- `_()` 可变单例调用
- 控制器 URL 自动替换功能
- 类名检查（`controller_class_postfix` + `controller_class_base`）

### Helper 方法速查

| 方法 | 说明 |
|---|---|
| `Helper::Show($data, $view)` | 渲染视图 |
| `Helper::Display($view, $data)` | 直接显示视图片段 |
| `Helper::Render($view, $data)` | 渲染为字符串 |
| `Helper::GET($key)` | 获取 `$_GET` |
| `Helper::POST($key)` | 获取 `$_POST` |
| `Helper::REQUEST($key)` | 获取 `$_REQUEST` |
| `Helper::SERVER($key)` | 获取 `$_SERVER` |
| `Helper::COOKIE($key)` | 获取 `$_COOKIE` |
| `Helper::Url($url)` | 生成 URL |
| `Helper::Res($url)` | 生成资源 URL |
| `Helper::ShowJson($data)` | 输出 JSON |
| `Helper::Show302($url)` | 重定向 |
| `Helper::Show404()` | 显示 404 |
| `Helper::header(...)` | 设置 HTTP 头 |
| `Helper::exit()` | 终止请求 |
| `Helper::Parameter($key)` | 获取路由参数 |
| `Helper::PageNo()` | 获取当前页码 |
| `Helper::PageHtml($total)` | 生成分页 HTML |

### Session 管理（纯状态容器）

Session 属于 Controller 层的职责。推荐的 `Controller\Session` 类只做状态存取，**不调用任何其他类**
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

### Action （Controller 通用功能）

#### 为什么需要 Action

Controller 的路由方法（`action_xxx`）常常需要重复的编排逻辑。例如：

- 多个路由都需要"获取当前登录用户"
- 登录、注册都需要"验证凭据 → 写入 Session"
- 多个 Controller 都可能用到同一套编排

把这些通用逻辑提取到 `Controller\UserAction` 等 **Action 类**中，让路由方法保持轻薄。

#### Action 示例

```php
<?php
namespace MyProject\Controller;

use MyProject\Business\UserBusiness;
use MyProject\Controller\Session;

class UserAction
{
    // 登录：验证凭据 + 写入 Session
    public function login(string $username, string $password): array
    {
        $user = UserBusiness::_()->login($username, $password);
        Session::_()->setUserId($user['id']);
        return $user;
    }
    
    // 获取当前用户：读 Session → 通过 Business 查数据
    public function getCurrentUser(): ?array
    {
        $id = Session::_()->getUserId();
        return $id ? UserBusiness::_()->getUser($id) : null;
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

何时使用 Action  同一套编排被 **多个路由方法** 或 **多个 Controller** 共用

#### 路由方法委托给 Action

```php
class MainController
{
    public function action_login()
    {
        if (UserAction::_()->isLoggedIn()) {
            Helper::Show302(''); Helper::exit();
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                UserAction::_()->login(
                    trim(Helper::POST('username', '')),
                    Helper::POST('password', '')
                );
                Helper::Show302(''); Helper::exit();
            } catch (\Exception $ex) {
                $error = $ex->getMessage();
            }
        }
        Helper::Show(get_defined_vars(), 'user_login');
    }
    
    public function action_profile()
    {
        $user = UserAction::_()->getCurrentUser();
        if (!$user) { Helper::Show302('login'); Helper::exit(); }
        Helper::Show(get_defined_vars(), 'user_profile');
    }
    
    public function action_logout()
    {
        UserAction::_()->logout();
        Helper::Show302(''); Helper::exit();
    }
}
```

#### 三层职责对照

| 类 | 定位 | 能调用的对象 | 不能调用的对象 |
|---|---|---|---|
| `Session` | 状态容器 | 无（纯容器） | — |
| `UserAction` | Controller 通用功能 | `Session`, `Business`, `Helper` | **❌ Model** |
| `MainController` | 路由入口 | `Action`, `Business`, `Helper` | — |

#### 调用链

```
注册:
  MainController::action_register()
    → UserAction::register()           ← 编排
        → Business::register()         ← 纯数据验证+入库
        → Session::setUserId()         ← 存状态

查个人资料:
  MainController::action_profile()
    → UserAction::getCurrentUser()     ← 编排
        → Session::getUserId()         ← 读状态
        → Business::getUser()          ← 通过 Business 查数据
```

### Setting 和 Config 读取

```php
// 从 DuckPhpSettings.config.php 读取设置
Helper::Setting('database_list');

// 从 config/{name}.php 读取配置
Helper::Config('app', 'key');
```

## 业务层（Business）

Business 层是 DuckPHP **特别强调**的中间层，负责业务逻辑的编排。

### 核心原则：无状态

Business 层必须是**纯无状态**的 —— 不依赖任何请求上下文、不读写 Session、不操作 `$_GET`/`$_POST`/`$_SERVER` 等超全局变量。

当多个 Business 类需要共享同一段逻辑时，提取到 **Service** 类中。Service 位于 Business 层，**可以调 Model 和其他 Service**。

```
Controller 层 (有状态)
  ├─ 解析 HTTP 输入
  ├─ 管理 Session / 登录状态
  ├─ 决定输出格式
  └─ 调用 Business
       │
       ▼
Business 层 (纯无状态) ✓
  ├─ 业务规则与校验
  ├─ 调用 Model 读写数据
  ├─ 返回纯数据结果
  └─ ❌ 不接触 $_GET / $_POST
       │
       ▼
Model 层 (无状态)
  └─ 数据访问
```

### 正确示例

```php
<?php
namespace MyProject\Business;

use DuckPhp\Foundation\Business\Base;

class MyBusiness extends Base
{
    public function getList()
    {
        // 调用 Model 获取数据
        $data = MyModel::_()->getAll();
        
        // 组装业务逻辑
        $processed = array_map(function ($item) {
            $item['display_name'] = strtoupper($item['name']);
            return $item;
        }, $data);
        
        return $processed;
    }
}
```

作为单例调用：

```php
MyBusiness::_()->getList();    // 当前相位下的单例
```

### Business Helper 方法

| 方法 | 说明 |
|---|---|
| `Helper::Setting($key)` | 读取全局设置 |
| `Helper::Config($file, $key)` | 读取配置文件 |
| `Helper::BusinessThrowOn($flag, $message)` | 条件抛业务异常 |
| `Helper::Cache()` | 获取缓存实例 |
| `Helper::XpCall()` | 安全调用（捕获异常） |
| `Helper::AdminService()` | 获取管理员服务 |
| `Helper::UserService()` | 获取用户服务 |





```php
<?php
namespace MyProject\Business;

use MyProject\Model\LogModel;

class CommonService
{
    use \DuckPhp\Foundation\SimpleBusinessTrait;
    
    public function writeAuditLog(string $action, array $data): void
    {
        LogModel::_()->addLog([
            'action' => $action,
            'data' => json_encode($data),
            'ip' => $_SERVER['REMOTE_ADDR'] ?? '',  // 注意：Service 也不读写 Session
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
```

Business 中调用 Service：

```php
class OrderBusiness
{
    public function createOrder(array $cart)
    {
        // ... 订单逻辑 ...
        CommonService::_()->writeAuditLog('order_created', ['order_id' => $id]);
    }
}
```

#### Action 与 Service 的对应关系

```
Controller 层               Business 层
─────────────────────────────────────────────
MainController               UserBusiness
  (路由入口)                   (业务逻辑)
       │                           │
       ▼                           ▼
UserAction                     CommonService
  (Controller 通用功能)          (Business 通用功能)
  → 调 Business + Session        → 调 Model + Business
  → ❌ 不调 Model                → ❌ 不碰 Session
```

## 模型层（Model）

Model 层负责数据访问。

Model 按**数据表**组织。它的职责是：

- 封装与当前表相关的所有数据库操作。
- 提供参数化查询，防止 SQL 注入。
- 返回原始数据（数组），不做业务判断。

**Model 里不写业务规则、不抛异常。**

### 继承 Foundation\Model\Base（推荐）

```php
<?php
namespace MyProject\Model;

use DuckPhp\Foundation\Model\Base;

class DemoModel extends Base
{
    // 自动表名：demo（类名去掉 "Model" 后缀并转小写）
    // 可通过 $table_name 覆盖
    // 可通过 $table_prefix 指定前缀
}
```

### ⚠️ 重要说明：方法可见性

`Foundation\Model\Base`（通过 `SimpleModelTrait`）提供的内置方法**全部为 `protected`**，不能在 Model 外部直接调用。正确的做法是：

1. **在 Model 子类中暴露公共方法**（推荐）
2. **直接使用 `Helper` 类的 `Db()` 方法操作数据库**

### 方式一：在 Model 中暴露公共方法

```php
<?php
namespace MyProject\Model;

use DuckPhp\Foundation\Model\Base;

class DemoModel extends Base
{
    // 在模型子类中封装公共方法
    public function findUser($id)
    {
        return $this->find($id);  // find() 是 protected，只能内部调用
    }
    
    public function findUserBy(array $condition)
    {
        return $this->find($condition);
    }
    
    public function addUser(array $data)
    {
        return $this->add($data);
    }
    
    public function updateUser($id, array $data)
    {
        return $this->update($id, $data, 'id');
    }
    
    public function getUserList(array $where = [], int $page = 1, int $page_size = 10): array
    {
        return $this->getList($where, $page, $page_size); // 返回 [$total, $data]
    }
    
    // SQL 查询也可以封装
    public function search($keyword)
    {
        return $this->fetchAll(
            "SELECT * FROM `'TABLE'` WHERE name LIKE ?",
            '%' . $keyword . '%'
        );
    }
}

// 外部调用
DemoModel::_()->findUser(1);
DemoModel::_()->addUser(['name' => 'foo', 'age' => 18]);
DemoModel::_()->updateUser(1, ['name' => 'bar']);
[$total, $data] = DemoModel::_()->getUserList([], 1, 10);
```

### 方式二：直接使用 Helper/Db

```php
use DuckPhp\Foundation\Model\Helper;

// 读写分离
$rows = Helper::DbForRead()->fetchAll("SELECT * FROM users WHERE status=?", 1);
Helper::DbForWrite()->execute("UPDATE users SET name=? WHERE id=?", 'new', 1);

// 分页
$sql = Helper::SqlForPager("SELECT * FROM users", $page, 10);
$sql = Helper::SqlForCountSimply("SELECT * FROM users");
```

### Model 内置方法速查（protected，仅限 Model 内部使用）

| 方法 | 说明 |
|---|---|
| `find($id)` / `find($condition)` | 按主键或条件查找 |
| `add($data)` | 插入数据（关联数组） |
| `update($id, $data, $pk)` | 更新数据 |
| `getList($where, $page, $size)` | 分页列表，返回 `[$total, $data]` |
| `fetchAll($sql, ...$args)` | 查询多行 |
| `fetch($sql, ...$args)` | 查询单行 |
| `fetchColumn($sql, ...$args)` | 查询单列值 |
| `fetchObject($sql, ...$args)` | 查询单行对象 |
| `fetchObjectAll($sql, ...$args)` | 查询多行对象 |
| `execute($sql, ...$args)` | 执行 SQL |
| `prepare($sql)` | 替换 `'TABLE'` 占位符为实际表名 |
| `table()` | 获取完整表名（前缀 + 名称） |

`'TABLE'` 占位符会自动替换为实际的表名（`$table_prefix . $table_name`）。

### 不继承 Base，直接使用 Db

```php
use DuckPhp\Foundation\Model\Helper;

// 获取数据库连接
$db = Helper::DbForRead();    // 读连接
$db = Helper::DbForWrite();   // 写连接

// 直接 SQL
$rows = Helper::DbForRead()->fetchAll("SELECT * FROM users WHERE status=?", 1);
Helper::DbForWrite()->execute("UPDATE users SET name=? WHERE id=?", 'foo', 1);

// 分页 SQL
$sql = Helper::SqlForPager("SELECT * FROM users", $pageNo, $pageSize);
$sql = Helper::SqlForCountSimply("SELECT * FROM users"); // 自动转为 COUNT(*)
```

### Model Helper 方法

| 方法 | 说明 |
|---|---|
| `Helper::Db($tag)` | 获取指定数据库连接 |
| `Helper::DbForRead()` | 获取读连接 |
| `Helper::DbForWrite()` | 获取写连接 |
| `Helper::SqlForPager($sql, $page, $size)` | SQL 添加分页 |
| `Helper::SqlForCountSimply($sql)` | SQL 转为 COUNT 查询 |

## 视图层（View）

视图是普通的 PHP 文件，放在 `view/` 目录下。

### 视图文件位置

```
view/
├── main.php                  # MainController/action_index 的视图
├── my/
│   └── index.php             # MyController/action_index 的视图
└── _sys/
    ├── error_404.php         # 404 错误页
    └── error_500.php         # 500 错误页
```

### 视图内容

```php
<?php
// view/main.php
// $data 数组中的变量自动展开为 PHP 变量
?>
<!DOCTYPE html>
<html>
<head>
    <title><?= __h($title) ?></title>
</head>
<body>
    <h1><?= $content ?></h1>
    <a href="<?= __url('user/login') ?>">登录</a>
</body>
</html>
```

### 页眉页脚

```php
// 在控制器构造函数中设置
public function __construct()
{
    Helper::setViewHeadFoot('_sys/header', '_sys/footer');
}

// 或直接赋值
Helper::assignViewData('site_name', 'MySite');
```

### 视图渲染方式

| 方法 | 说明 |
|---|---|
| `Helper::Show($data, $view)` | 渲染视图（含页眉页脚） |
| `Helper::Display($view, $data)` | 渲染视图片段（不含页眉页脚） |
| `Helper::Render($view, $data)` | 渲染为字符串 |

视图文件查找规则：
1. 如果 `$view` 为空，使用 `{ControllerClass}/{actionMethod}` 自动推断
2. 如果 `$view` 不以 `.php` 结尾，自动追加
3. 在多应用嵌套时，子应用可以覆盖父应用的视图

### 切换视图引擎

通过扩展可以切换视图引擎：

```php
$options = [
    'ext' => [
        \DuckPhp\Ext\CallableView::class => true,  // 函数式视图
        \DuckPhp\Ext\JsonView::class => true,       // JSON 视图
    ],
];
```

- **CallableView**：使用类方法代替视图文件，适合 API 接口或简单项目
- **JsonView**：所有视图自动输出为 JSON，适合纯 API 项目
