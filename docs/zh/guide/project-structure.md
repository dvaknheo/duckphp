# 1-3 目录结构与四层架构

> 解决什么问题：把项目摆成「框架期望的样子」（目录、命名、编码规则），并弄清四层各管什么、谁不能调谁、越界之后会失去什么。
> 前置：[第 1-2 章](install.md)。预计 25 分钟。
> 本章结构与 `skeleton/`（脚手架实际生成的工程）一致；示例取自 `tests/data_for_tests/ZAllDemo` 与 `demo/public/demo.php`。
> 卷二开头还有一页[第 2-1 章 四层架构与调用规范](layers.md)——那是**指路页**，内容只在本章。

## 最小示例

四层的全部骨架，就是下面五个片段（取自 `tests/data_for_tests/ZAllDemo`，每个文件都很短，这就是它的全部内容）：

```php
// public/index.php —— 入口只做一件事：把请求交给应用类
\YourProjectName\System\App::RunQuickly([]);
```

```php
// src/Controller/MainController.php —— 控制器：拿输入、调业务、出输出
namespace YourProjectName\Controller;

use YourProjectName\Business\DemoBusiness;

class MainController extends Base
{
    public function index()
    {
        $var = __h(DemoBusiness::_()->foo());   // 业务给数据，__h() 做 HTML 转义
        Helper::Show(get_defined_vars(), 'main'); // 交给视图渲染（数据在前、视图名在后）
    }
}
```

```php
// src/Business/DemoBusiness.php —— 业务：编排逻辑，不碰请求上下文
namespace YourProjectName\Business;

use YourProjectName\Model\DemoModel;

class DemoBusiness extends Base
{
    public function foo()
    {
        return "<" . DemoModel::_()->foo() . ">";
    }
}
```

```php
// src/Model/DemoModel.php —— 模型：只做数据访问
namespace YourProjectName\Model;

class DemoModel extends Base
{
    public function foo()
    {
        return DATE(DATE_ATOM);
    }
}
```

```php
<!-- view/main.php —— 视图：只显示 -->
<h1><?= $var ?></h1>
```

调用链是一条直线，没有回头路：

```
HTTP 请求 → 路由 → MainController::index()
                        │  取输入（Helper::GET/POST/Parameter）
                        ▼
                  DemoBusiness::foo()      ← 业务编排、校验、拼装
                        │
                        ▼
                  DemoModel::foo()         ← 数据访问（Db）
                        │
                        ▼
                  Helper::Show(数据, 视图)  ← 渲染，结束
```

想看得更全一点，`demo/public/demo.php` 把五层塞进了一个文件里（应用类 `MySpace\System\App`、控制器 `MySpace\Controller\MainController`、业务 `MySpace\Business\MyBusiness`、模型 `MySpace\Model\MyModel`、可调用视图 `MySpace\View\Views`），适合对照着看「同一件事在四层里分别长什么样」。

## 机制说明

### 1. 标准结构

```
project/
├── public/
│   └── index.php          ← Web 入口。只有几行「加载 + 启动」，勿改
├── bin/
│   └── cli.php            ← CLI 入口。同样是「加载 + 启动」，勿改
├── config/
│   └── DuckPhpSettings.config.php   ← 设置文件：数据库/Redis 等敏感信息（第 1-5 章）
├── src/
│   ├── System/
│   │   └── App.php        ← 应用类：项目的配置中枢（选项都写这里）
│   ├── Controller/
│   │   ├── Base.php       ← 控制器基类（勿改）
│   │   ├── Helper.php     ← 控制器侧助手（勿改；或直接用 Foundation\Controller\ControllerHelper as Helper）
│   │   ├── MainController.php       ← 欢迎页/短路由
│   │   ├── Session.php    ← 会话读写集中在这里
│   │   └── *Controller.php / *Action.php   ← 你的控制器与可复用动作类
│   ├── Business/
│   │   ├── Base.php  Helper.php      ← 勿改
│   │   └── *Business.php / *Service.php    ← 你的业务类
│   └── Model/
│       ├── Base.php       ← 模型基类（勿改，已集成 table()/find()/add()/getList() 等）
│       └── *Model.php     ← 你的模型类（一个数据库表一个）
├── view/                  ← 视图
│   ├── _sys/              ← 系统视图：error_404.php / error_500.php
│   └── {控制器名}/{动作名}.php        ← 普通页面
├── runtime/               ← 日志等可写目录（要可写，别提交进版本库）
└── vendor/
```

**框架为什么能找得到这些东西**（四条例）：

| 找什么   | 依据                                                         |
| ----- | ---------------------------------------------------------- |
| 控制器目录 | 从**应用类文件所在目录**推算 + `namespace_controller`（默认 `Controller`） |
| 视图文件  | 应用 `path` + `path_view`（默认 `view`）+ 视图名                    |
| 配置文件  | 应用 `path` + `path_config`（默认 `config`）                     |
| 设置文件  | 选项 `setting_file`（默认 `config/DuckPhpSettings.config.php`）  |

所以：**类文件必须落在与命名空间一致的目录里**（`MyProj\Controller\NoteController` → `src/Controller/NoteController.php`，且 `path` 指向项目根）。

### 2. 命名规范

| 类型          | 规则                                    | 例子                                        |
| ----------- | ------------------------------------- | ----------------------------------------- |
| 控制器         | `{名字}Controller`，方法名就是 URL 段          | `NoteController::index()` → `/Note/index` |
| 动作类（控制器层复用） | `{名字}Action`，必须有无参 `__construct()`    | `ExportAction`                            |
| 业务类         | `{名字}Business`                        | `NoteBusiness`                            |
| 服务类（业务层复用）  | `{名字}Service`                         | `MailService`                             |
| 模型类         | `{名字}Model`，**类名决定表名**：去掉 `Model` 再小写 | `NoteModel` → 表 `note`                    |
| 异常类         | `{名字}Exception`                       | `ProjectException`                        |
| 会话类         | `Session`                             | `Session`                                 |
| CLI 命令方法    | `command_{名字}`                        | `command_sync()` → `php bin/cli.php sync` |

容易踩的细节：

- **URL 大小写敏感**：默认不会把 `/note/list` 自动转成 `/Note/list`；要宽松匹配就配 `controller_class_adjust`（[第 2-3 章](routing.md)）。

### 3. 五层各自的职责与边界

| 层                                     | 职责        | 可以做                                            | **不可以做**                                      |
| ------------------------------------- | --------- | ---------------------------------------------- | --------------------------------------------- |
| **Controller**                        | 请求的入口与出口  | 取输入、调 Business、把数据交给视图、跳转、404                  | 写业务规则、直接查数据库、拼 SQL                            |
| **Business**                          | 业务逻辑编排    | 调 Model、调 Service、条件抛业务异常                      | 读写 `$_GET`/`$_POST`/`$_SERVER`/Session，依赖当前请求 |
| **Model**                             | 数据访问      | 调 [Db](../reference/Db-Db.md)、按表做 CRUD、返回数组/对象 | 写业务判断、抛业务异常、调 Business                        |
| **[View](../reference/Core-View.md)** | 显示        | 用 Helper 与全局函数输出、读控制器给的数据                      | 查数据库、调 Business、写业务逻辑                         |
| **System**                            | 接线（不属于四层） | 配置、注册事件/命令、装配应用类                               | 混进业务代码里被四层反向依赖                                |

> **编码规则**：`Controller`、`Business`、`Model`、`View` 四层里，除 Helper 与全局函数外，**不要直接 `use` `DuckPhp\*` 的框架类**；框架相关的调用集中在 `System` 层，或由 `Helper` 代劳。这条规则的意义在第三卷会体现：包装配（`ext`、覆盖、相位）全都发生在 `System` 层，业务代码因此可以整片复用。

四层各有一个框架基类可以继承（都只做一件事：给本层的类装上单例入口 `_()`）：控制器 [`Controller\Base`](../reference/Foundation-Controller-Base.md)、业务 [`Business\Base`](../reference/Foundation-Business-Base.md)、模型 [`Model\Base`](../reference/Foundation-Model-Base.md)（模型基类多一层：它同时 `use` 了 [`ModelHelperTrait`](../reference/Foundation-Model-ModelHelperTrait.md)，所以模型里 `$this->Db()` 直接可用）。Helper 的分工见本节 §7。

### 4. 层级调用铁律

```
System        框架相关调用、异常定义、应用配置
  ↑
Controller    请求入口：收输入、出输出（可调 Action / Business / Helper / Session；禁止直接调 Model、Service）
  ↑
Business      业务逻辑（纯无状态；可调 Model / Service / Helper；禁止读写 Session、禁止碰 $_GET/$_POST）
  ↑
Model         数据访问（纯无状态；只做数据存取；禁止业务逻辑、禁止抛异常）
View          只做展示（只用全局函数）
```

**违规示例**（这些写法会让代码无法在 CLI / 测试里复用）：

```php
// ❌ 控制器直接调 Model，绕过了业务层
class NoteController extends Base
{
    public function index()
    {
        $list = NoteModel::_()->getList();      // 违规：应经 NoteBusiness
    }
}

// ❌ 业务层读超全局变量（到了 CLI / 队列里就跑不通）
class NoteBusiness extends Base
{
    public function save()
    {
        $title = $_POST['title'];               // 违规：应由控制器传入参数
    }
}

// ❌ 模型层抛业务异常（模型不该知道业务规则）
class NoteModel extends Base
{
    public function addNote($t)
    {
        if (!$t) { throw new \Exception('标题不能为空'); }   // 违规：交给 Business 层
    }
}
```

正确的抛异常方式：`Helper::ThrowOn(...)`（[第 2-12 章](exception.md)）。

### 5. 越界矩阵（本章最重要的一张表）

左列「调用方」去调右列「被调方」，✅ 允许、⚠️ 有条件、❌ 禁止：

| 调用方 ↓ / 被调方 →  | Controller       | Business       | Service | Model     | Db         | View                 | Session      |
| -------------- | ---------------- | -------------- | ------- | --------- | ---------- | -------------------- | ------------ |
| **Controller** | ⚠️ 仅同层复用走 Action | ✅              | ✅       | ❌         | ❌          | ✅（通过 `Helper::Show`） | ⚠️ 只经 Helper |
| **Business**   | ❌                | ⚠️ 同层走 Service | ✅       | ✅         | ❌（经 Model） | ❌                    | ❌            |
| **Service**    | ❌                | ❌              | ✅       | ✅         | ❌          | ❌                    | ❌            |
| **Model**      | ❌                | ❌              | ❌       | ⚠️ 跨库模型例外 | ✅          | ❌                    | ❌            |
| **View**       | ❌                | ❌              | ❌       | ❌         | ❌          | —                    | ❌            |
| **System**     | ⚠️ 只在接线时         | ⚠️ 只在接线时       | ⚠️      | ⚠️        | ✅          | ✅                    | ✅            |

矩阵的行/列按**目录 + 后缀**判定归属：`Controller/` 下的 `*Controller`、`Business/` 下的 `*Business` 与 `*Service`、`Model/` 下的 `*Model`（目录与后缀约定见 `skeleton/RULES.md`）。**不带这些层后缀、也不在上述目录里的类不属于四层**——它没有「本层」可依托，因此不能被别的层跨层调用，只能放进 `System/`（接线处）或与调用方同层。

`System` 那一行标 ⚠️ 的含义是「只在接线时」：它可以装配和调用各层，但**正规做法是调 Controller 的 Action**（CLI 命令、异常报告器都是这个路子），不要跳过 Action 直接去读 Business——否则「请求入口」的职责会摊到接线层。

三条最容易记错的：

- **控制器不碰 Db/Model**：一次「顺手查一下」就是越界，因为它绕过了业务规则（校验、权限、事务边界都写在 Business 里）。
- **业务不碰请求上下文**：`Business` 拿到的一切都应该由参数传进来。理由见下一节。
- **视图只读不写**：视图里查库或调业务，等于把渲染变成了第二次业务执行。

### 6. 为什么 Business 必须无状态

这不是洁癖，有三个很具体的后果：

1. **同一个 Business 会被多个入口复用**：Web 请求、CLI 命令（[第 2-16 章](cli.md)）、定时任务、测试（[第 2-17 章](testing.md)）都会调它。一旦它读 `$_GET` 或 Session，CLI 下就必然出错。
2. **可测性**：无状态 + 参数入、返回值出，才能不起服务器直接单测（`demo/` 与 `tests/data_for_tests/*` 的测试就是这么写的）。

所以约定是：**请求上下文只允许出现在 Controller 层与 Helper 里**（`Helper::GET()`、`Helper::Parameter()`、`Helper::Session()` 之类），Business 的入参一律显式传。

### 7. Helper 的分层：四层各有一套

框架把「能用什么便捷方法」也按层切开了——`Helper` 不是一个大杂烩，而是按层拆成四个类：

| 层          | 工程侧的类（`YourProjectName\<层>\Helper`） | 框架里对应的类                                                                                                                                                                      | 典型方法                                                   |
| ---------- | ----------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------ |
| Controller | `Controller\Helper`                 | [`DuckPhp\Foundation\Controller\ControllerHelper`](../reference/Foundation-Controller-ControllerHelper.md)                                                                   | `Show()`、`ShowJson()`、`Show302()`、`GET()`、`POST()`     |
| Business   | `Business\Helper`                   | [`DuckPhp\Foundation\Business\BusinessHelper`](../reference/Foundation-Business-BusinessHelper.md)                                                                           | `Setting()`、`Config()`、`BusinessThrowOn()`、`XpCall()`  |
| Model      | `Model\Helper`                      | [`DuckPhp\Foundation\Model\ModelHelper`](../reference/Foundation-Model-ModelHelper.md)（薄壳；方法在 [`Model\ModelHelperTrait`](../reference/Foundation-Model-ModelHelperTrait.md)） | `Db()`、`DbForRead()`、`SqlForPager()`                   |
| 应用/接线      | `System\Helper`                     | [`DuckPhp\Foundation\System\SystemHelper`](../reference/Foundation-System-SystemHelper.md)                                                                                   | `addRouteHook()`、`OnGlobalEvent()`、`FireGlobalEvent()` |

工程侧的 `Xxx\Helper` 类本身极短（`demo/src/Controller/Helper.php` 就是 `extends` 一行 + 一个空类），也可以直接用框架现成的类；想把四层并成一个入口，用 [`DuckPhp\Foundation\Helper`](../reference/Foundation-Helper.md)（`__callStatic` 派发，见[第 2-9 章](helper.md)）。**反过来更重要**：某个方法不在你这一层的 Helper 里，通常就是框架在提示你「这件事不该在这一层做」。

视图里则用**全局函数**（`src/Core/Functions.php` 定义，见 [全局函数参考](../reference/Core-Functions.md)）：

| 函数                                         | 用途                                        |
| ------------------------------------------ | ----------------------------------------- |
| `__h($str)`                                | HTML 转义输出（防 XSS 的第一道）                     |
| `__url($url)` / `__domain()`               | 生成站内 URL / 域名前缀                           |
| `__res($url)`                              | 生成静态资源 URL（[第 3-3 章](static-resources.md)） |
| `__json($data)`                            | JSON 编码                                   |
| `__l($str)` / `__langtext()` / `__hl()`    | 多语言与转义组合（[第 2-15 章](i18n.md)）               |
| `__logger()`、`__debug_log()`、`__var_log()` | 日志（[第 1-6 章](debugging.md)）                 |

### 8. 框架其实不强制这套约定

要说清楚：DuckPHP **没有**运行时拦截器去阻止你在控制器里 `new DemoModel()`。违反约定的代价不是报错，而是这些能力悄悄失效——

- 覆盖（[第 3-5 章](overriding.md)）失效：覆盖靠「替换类/替换文件」，越界直连的调用链绕过了替换点；
- 多应用（[第 3-1 章](advanced-phase.md)）失效：跨相位直连拿到的是别的相位（或根相位）的实例；
- 测试与 CLI 复用困难（[第 2-16](cli.md)、[第 2-17 章](testing.md)）；
- 业务规则出现第二份实现：一边在 Business 里校验，一边在控制器里也校验。

所以这一章的铁律，本质是「为了保住框架那几个杀手锏，请把边界守住」。

## 常见写法

**① Service：给多个 Business 共享的逻辑**
Service 没有专门的基类或注册机制，就是「放在 `Business/` 目录下、被多个 Business 调用、且不碰请求上下文」的普通类；`demo/src/Business/CommonService.php` 是它的占位样板（目前是空壳，用来告诉你文件该放哪）：

```php
namespace MyProj\Business;

class CommonService
{
    public function writeAuditLog(string $action, array $data): void
    {
        // 只做一件事：把审计日志写进模型层
    }
}
```

**② Action：给多个 Controller 共享的编排**
控制器之间要复用的**编排**（不是业务规则）抽成 Action，避免控制器互相继承；同样只是约定位置（`demo/src/Controller/CommonAction.php` 也是空壳样板），关键是 Action **只能调 Business 与 Session，不能直接调 Model**。

**③ System 层只做接线**
配置、异常/错误页、命令注册、事件注册、以及 `app` 里的子应用声明都写在 `src/System/`：`demo/src/System/App.php` 就是全部接线的样板（选项、异常类、`controller_method_prefix`、`app`）。

**④ 层内复用靠 `::_()` 单例，而不是 `new`**

```php
DemoBusiness::_()->foo();   // ✅ 当前相位的单例，可被覆盖/替换
new DemoBusiness();         // ❌ 绕过容器：覆盖与共享都失效
```

**⑤ 跨相位调用（第三卷的内容，这里先立规矩）**
真要跨应用取东西，用[第 3-1 章](advanced-phase.md) 的相位 API 或相位代理，而不是 `new` 另一个应用的类。

## 常见错误

| 现象                                       | 原因                                           | 改法                                                               |
| ---------------------------------------- | -------------------------------------------- | ---------------------------------------------------------------- |
| 新增控制器 404                                | 文件名/命名空间/后缀不匹配          | `src/Controller/NoteController.php` + `class NoteController` + 后缀默认 `Controller` |
| 模型找不到表                                   | 表名与类名推导不一致              | `NoteModel` → 表 `note`；表名不同就在模型里 `protected $table_name = 'notes';` |
| 视图找不到                                    | 视图名与文件路径不一致             | `Helper::Show($data)` 用当前路由路径；显式指定时写 `'note/index'` → `view/note/index.php` |
| 改了 `Base.php`/`Helper.php` 后框架异常          | 那是框架约定文件                | 要扩展就在自己的子类里加，别改基类 |
| 控制器里出现 `DemoModel::_()` 或 SQL            | 越界：跳过了业务层                                    | 把查询挪进 Business，控制器只调 Business                                    |
| Business 里 `$_GET['id']` 报「未定义」          | Business 读了请求上下文，CLI/测试下没有这些超全局              | 由控制器取值后**当参数传进** Business                                        |
| 视图里查库，页面变得很慢或数据不一致                       | 视图里又跑了一次业务                                   | 数据由控制器准备，视图只渲染                                                   |
| `Helper::Show('main', $data)` 页面白屏或视图找不到 | 参数顺序写反了：真实签名是 `Show($data = [], $view = '')` | 改成 `Helper::Show($data, 'main')`；用 `get_defined_vars()` 传当前变量最省事 |
| 覆盖类/覆盖文件后没生效                             | 调用链上有 `new`、或直接从别的相位取实例                      | 全程用 `::_()`，跨相位用相位 API（[第 3-1 章](advanced-phase.md)）                                   |
| 控制器里 [`use DuckPhp\Core\App;`](../reference/Core-App.md) 越写越多        | 框架细节渗进了业务层                                   | 框架调用收进 System 层或对应层的 Helper                                      |
| 同一个业务规则在控制器和 Business 里各写一份              | 边界没守住，规则有了第二实现                               | 规则只留在 Business，控制器只做参数整形                                         |
| Model 里抛业务异常、写权限判断                       | 模型层做了业务层的活                                   | 判断留在 Business；Model 只返回数据（异常处理见[第 2-12 章](exception.md)）           |

## 下一步

- [第 1-4 章 第一个页面](quickstart.md)：按这套结构写出第一个完整功能。
- [第 1-5 章 配置与设置](configuration.md)：`App.php` 里的 options 与 `config/` 里的 settings 有何区别。
- [第 2-2 章 请求生命周期](lifecycle.md)：这套分层在运行时是怎么被装配起来的。
- [第 2-3 章 路由进阶](routing.md)：请求怎么落到某个控制器方法。
- [第 2-5 章 控制器](controllers.md) / [第 2-6 章 视图与模板](views.md)：输入怎么取、输出有哪几种方式。
- [第 2-7 章 数据库](database.md) 与 [第 2-8 章 模型层](model.md)：模型层这一列往下的全部内容。
- 参考手册：[DuckPhp\Foundation\Helper](../reference/Foundation-Helper.md)、[DuckPhp\Foundation\Controller\ControllerHelper](../reference/Foundation-Controller-ControllerHelper.md)、[DuckPhp\Foundation\Business\BusinessHelper](../reference/Foundation-Business-BusinessHelper.md)、四层基类 [Controller\Base](../reference/Foundation-Controller-Base.md) / [Business\Base](../reference/Foundation-Business-Base.md) / [Model\Base](../reference/Foundation-Model-Base.md)。
