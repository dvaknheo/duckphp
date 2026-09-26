# 2-5 控制器

> 解决什么问题：控制器里怎么取输入、怎么把结果送出去（四种方式）、怎么跳转和报 404，以及哪些东西**不该**写在控制器里。
> 前置：[第 2-1 章 四层架构与调用规范](layers.md)、[第 2-3 章 路由进阶](routing.md)。预计 20 分钟。
> 示例：`skeleton/src/Controller/MainController.php`（五层骨架里的真实控制器）与 `demo/public/demo.php`（单文件版，含登录/跳转写法）。

## 最小示例

`skeleton/src/Controller/MainController.php` 的控制器全文（这就是「控制器该有多薄」的样板）：

```php
namespace YourProjectName\Controller;

use YourProjectName\Business\DemoBusiness;

class MainController extends Base
{
    public function __construct()
    {
        $this->initController();
    }
    protected function initController()   // 骨架留的钩子：写「本控制器共用的初始化」
    {
    }
    public function index()
    {
        $var = __h(DemoBusiness::_()->foo());          // 业务给数据，__h() 转义
        Helper::Show(get_defined_vars(), 'main');       // 交给视图渲染
    }
}
```

`Base` 是工程自己的控制器基类（`skeleton/src/Controller/Base.php`），只有 [`use SingletonTrait;`](../reference/Foundation-SingletonTrait.md) 一行——**为了 `MainController::_()` 能当单例用**。

## 机制说明

### 1. 控制器只做三件事

1. **取输入**：从请求里拿参数（`Helper::GET()`/`POST()`/`REQUEST()`、路由参数 `Helper::Parameter()`）；
2. **调业务**：把参数整理好交给 Business（后面几章都在讲这条线：[第 2-7 章 数据库](database.md)、[第 2-8 章 模型层](model.md)、[第 2-9 章 Helper](helper.md)）；
3. **出输出**：把结果交给视图 / JSON / 重定向。

业务规则、SQL、权限判断都不属于这一层（越界的后果见[第 2-1 章](layers.md)的越界矩阵）。

### 2. 类与基类

| 基类 | 位置 | 用途 |
|---|---|---|
| [`DuckPhp\Foundation\Controller\Base`](../reference/Foundation-Controller-Base.md) | `src/Foundation/Controller/Base.php` | 最简：只 `use SingletonTrait` |
| [`DuckPhp\Foundation\Controller\ActionBase`](../reference/Foundation-Controller-ActionBase.md) | 同上目录 | Action 的基类 |
| [`DuckPhp\Foundation\Controller\UserControllerBase`](../reference/Foundation-Controller-UserControllerBase.md) | 同上目录 | 前台用户控制器（[第 2-19 章](user.md)） |
| [`DuckPhp\Foundation\Controller\AdminControllerBase`](../reference/Foundation-Controller-AdminControllerBase.md) | 同上目录 | 后台管理员控制器（[第 2-20 章](admin.md)） |

方法名前缀由 `controller_method_prefix` 决定（默认 `''`）。`demo/src/System/App.php` 配的是 `'action_'`，所以 demo 里的方法写成 `action_login()`；`skeleton/` 那套骨架没配前缀，所以是 `index()`。**改了前缀，URL 不变，但控制器方法名要跟着改**。

### 3. 输入的获取

| 想拿什么            | 写法                                                                                          | 说明                                                    |
| --------------- | ------------------------------------------------------------------------------------------- | ----------------------------------------------------- |
| GET 参数          | `Helper::GET('id')` / `Helper::GET()`（全部）                                                   | 底层是 [`SuperGlobal`](../reference/Core-SuperGlobal.md)，可被测试替换                              |
| POST 参数         | `Helper::POST('name')`                                                                      | 同上                                                    |
| GET+POST 合并     | `Helper::REQUEST('q')`                                                                      | 按 PHP 的 `$_REQUEST` 语义                                |
| Cookie / Server | `Helper::COOKIE('k')` / `Helper::SERVER('HTTP_HOST')`                                       |                                                       |
| **路由参数**        | `Helper::Parameter('id')`                                                                   | 路由映射里 `{id}`、`*` 通配、正则捕获组匹配出来的参数（[第 2-3 章](routing.md)） |
| 判断请求类型          | `Helper::IsPost()` / `Helper::IsAjax()`                                                     |                                                       |
| 当前路由信息          | `Helper::getRouteCallingClass()` / `Helper::getRouteCallingMethod()` / `Helper::PathInfo()` | 排错与日志常用                                               |

> 别在控制器里直接读 `$_GET`/`$_POST`：`SuperGlobal` 包装的存在意义是「命令行、测试、常驻进程下也能替换数据源」。

### 4. 输出的四种方式

| 方式           | 写法                                             | 适用                                |
| ------------ | ---------------------------------------------- | --------------------------------- |
| **① 视图**     | `Helper::Show($data, $view)`                   | 常规 HTML 页面；`$view` 省略时用当前路由路径当视图名 |
| **② 渲染成字符串** | `$html = Helper::Render('mail/body', $data);`  | 邮件正文、片段缓存、二次加工后再输出                |
| **③ JSON**   | `Helper::ShowJson($data)`（可带 `$flags`）         | 接口                                |
| **④ 直接输出**   | `echo` / `Helper::header()` / `Helper::exit()` | 极简接口、流式输出、健康检查                    |

另外两个「出口」动作：

```php
Helper::Show302('/user/login');   // 302 跳转（记得用 Helper::Url() 生成站内地址）
Helper::Show404();                // 直接以 404 结束（框架默认 404 机制见第 2-12 章）
```

### 5. 数据怎么传给视图

三种传法，按场景选：

```php
Helper::Show(get_defined_vars(), 'main');     // ① 把当前作用域全部变量交给视图（骨架惯用法）
Helper::Show(['title' => $t], 'main');        // ② 显式数组
Helper::assignViewData('site_name', 'MyProj'); // ③ 预置变量，之后每次 Show 都带上
```

页眉页脚（布局）用 `Helper::setViewHeaderFooter('header', 'footer')` 指定，通常写在控制器的 `__construct()` 里——`demo/public/demo.php` 的 `MainController::__construct()` 就是这么做的（渲染顺序是 头 → 视图 → 尾，详见[第 2-6 章](views.md)）。

### 6. Action：控制器之间复用「编排」

两个控制器要做同一串编排（取输入 → 调若干 Business → 存 Session），抽成 Action，而**不是让控制器互相继承**：

```php
namespace MyProj\Controller;

class NoteAction extends ActionBase      // 只调 Business 与 Session，不直接调 Model
{
    public function save(array $post): array
    {
        $note = NoteBusiness::_()->save($post);
        Session::_()->set('last_note_id', $note['id']);   // 带前缀的会话读写（第 2-11 章）
        return $note;
    }
}
```

控制器里只留输入输出：`$note = NoteAction::_()->save(Helper::POST());`（分层规则见[第 2-1 章](layers.md)；登录这类标准动作不用自己写 Action，直接用[第 2-19 章](user.md)的 `Helper::User()->login()`）。

## 常见写法

**① 表单提交 + 校验失败回显**

```php
public function create()
{
    $post = Helper::POST();
    if (!Helper::IsPost()) {
        Helper::Show([], 'note/form');       // GET：只渲染表单
        return;
    }
    $errors = Helper::Validator()->init([
        'title' => 'required|maxLen:64',
    ])->valid($post);
    if ($errors) {
        Helper::Show(['errors' => $errors, 'post' => $post], 'note/form');  // 回显
        return;
    }
    $id = NoteBusiness::_()->create($post);
    Helper::Show302(Helper::Url('note/show?id=' . $id));
}
```

（校验的三种口径见[第 2-10 章](validator.md)。）

**② JSON 接口**

```php
public function list()
{
    $page = (int)Helper::GET('page', 1);
    Helper::ShowJson(['code' => 0, 'data' => NoteBusiness::_()->paginate($page)]);
}
```

**③ 同一个方法兼顾 Ajax 与页面**

```php
public function show()
{
    $note = NoteBusiness::_()->get((int)Helper::GET('id'));
    if (Helper::IsAjax()) {
        Helper::ShowJson($note);
        return;
    }
    Helper::Show(get_defined_vars(), 'note/show');
}
```

**④ 用路由参数（而不是查询串）取资源 id**

```php
// 路由：'/note/{id:\d+}' => '...\NoteController@show'（第 2-3 章）
public function show()
{
    $id = (int)Helper::Parameter('id');       // ← 路由捕获的参数
    // ...
}
```

**⑤ 邮件/片段：先渲染再加工**

```php
$body = Helper::Render('mail/welcome', ['user' => $user]);
Mailer::_()->send($user['email'], '欢迎', $body);
```

## 常见错误

| 现象                                         | 原因                                          | 改法                                                |
| ------------------------------------------ | ------------------------------------------- | ------------------------------------------------- |
| `Helper::Show('main', $data)` 视图找不到 / 白屏   | 参数顺序反了：真实签名是 `Show($data = [], $view = '')` | 数据在前、视图名在后；惯用 `Show(get_defined_vars(), 'main')`  |
| 视图里 `$var` 未定义                             | 变量没进 `Show()` 的数据里                          | 用 `get_defined_vars()` 或 `assignViewData()` 显式给   |
| 改了 `controller_method_prefix` 后全部 404      | URL 不变，但方法名要加前缀                             | 方法名加前缀（如 `action_index`），或把前缀配回 `''`              |
| `Helper::Parameter('id')` 拿不到 `?id=1`      | 它只给**路由捕获**的参数                              | 查询串用 `Helper::GET('id')`                          |
| 控制器里 `DemoModel::_()->...` 查库              | 越界：跳过了业务层                                   | 挪进 Business（[第 2-1 章](layers.md)）                   |
| `Helper::Show302('/user/login')` 部署到子目录后跳错 | 手写绝对路径                                      | 用 `Helper::Url('user/login')`                     |
| 在 `echo` 之后又 `Helper::header()` 报「已发送输出」   | 输出已经开始                                      | 先发头再输出；或用 [`Runtime`](../reference/Core-Runtime.md) 的输出缓冲（[第 2-2 章](lifecycle.md)） |
| 控制器方法里 `exit()` 导致测试/CLI 中断                | 直接结束进程                                      | 用 `Helper::exit()`（可替换的系统包装）或正常 `return`          |

## 下一步

- [第 2-6 章 视图与模板](views.md)：视图文件怎么定位、页眉页脚、转义。
- [第 2-10 章 表单与数据验证](validator.md)：输入校验的三种口径。
- [第 2-19 章 使用用户系统](user.md) / [第 2-20 章 使用管理员系统](admin.md)：`UserControllerBase` / `AdminControllerBase` 与 Action。
- 参考手册：[DuckPhp\Foundation\Controller\ControllerHelper](../reference/Foundation-Controller-ControllerHelper.md)、[DuckPhp\Foundation\Controller\Base](../reference/Foundation-Controller-Base.md)、[DuckPhp\Core\SuperGlobal](../reference/Core-SuperGlobal.md)。
