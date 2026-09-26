# 1-3 目录结构与四层架构

> 解决什么问题：把项目摆成「框架期望的样子」（目录、命名、编码规则），并弄清四层各管什么、谁不能调谁、越界之后会失去什么。
> 前置：[第 1-2 章](install.md)。预计 25 分钟。
> 本章结构与 `skeleton/`（脚手架实际生成的工程）一致；下面的骨架片段就取自 `skeleton/`。
> 卷二开头还有一页[第 2-1 章 四层架构与调用规范](layers.md)——那是**指路页**，内容只在本章。

## 最小示例

四层的全部骨架，就是下面五个片段（取自 `skeleton/`，每个文件都很短，这就是它的全部内容）：

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

想看得更全一点：`skeleton/` 里每个文件都很短，顺着读一遍就是完整的一条链路——入口 `public/index.php` → 应用类 `src/System/App.php` → `src/Controller/MainController.php` → `src/Business/DemoBusiness.php` → `src/Model/DemoModel.php` → 视图 `view/main.php`（`view/test/done.php` 是另一条同样短的链路，对应 URL `/test/done`）。

## 机制说明

### 1. 标准结构

```
project/
├── public/index.php     ← Web 入口：只有几行「加载 + 启动」，勿改
├── bin/cli.php          ← CLI 入口：同样是「加载 + 启动」，勿改
├── config/              ← 设置文件：数据库/Redis 等敏感信息（第 1-5 章）
├── src/                 ← 代码：System / Controller / Business / Model（下一节讲谁不能调谁）
├── view/                ← 视图（含 _sys/ 错误页）
├── runtime/             ← 日志等可写目录（要可写，别提交进版本库）
└── vendor/
```

每个目录里**具体有哪些文件**（哪些「勿改」、哪些是示例、哪些默认未启用）只在随工程的 [`skeleton/AGENTS.md`](../../../skeleton/AGENTS.md) 里维护一份——用脚手架建项目时它就在你的工程根目录，是唯一一份文件级清单。上面这张只画到「顶层七个目录」，讲的是为什么这么分。

**框架为什么能找得到这些东西**（四条例）：

| 找什么   | 依据                                                         |
| ----- | ---------------------------------------------------------- |
| 控制器目录 | 从**应用类文件所在目录**推算 + `namespace_controller`（默认 `Controller`） |
| 视图文件  | 应用 `path` + `path_view`（默认 `view`）+ 视图名                    |
| 配置文件  | 应用 `path` + `path_config`（默认 `config`）                     |
| 设置文件  | 选项 `setting_file`（默认 `config/DuckPhpSettings.config.php`）  |

所以：**类文件必须落在与命名空间一致的目录里**（`MyProj\Controller\NoteController` → `src/Controller/NoteController.php`，且 `path` 指向项目根）。

### 2. 命名规范

各类文件的后缀表（`Controller` / `Action` / `Business` / `Service` / `Model` / `Exception` / `Session` / `command_`）与逐个例子，只在 [`skeleton/AGENTS.md`](../../../skeleton/AGENTS.md) 里维护一份。这里只说三条最容易踩的：

- **`{名字}Model` 的类名决定表名**：`NoteModel` → 表 `note`；表名不同就在模型里写 `protected $table_name = 'notes';`。
- **Action 必须有无参 `__construct()`**：用来覆盖基类的构造，避免它被当成控制器入口去初始化（见「常见写法 ②」）。
- **URL 大小写敏感**：默认不会把 `/note/list` 自动转成 `/Note/list`；要宽松匹配就配 `controller_class_adjust`（[第 2-3 章](routing.md)）。

### 3. 各层的职责与边界

| 层                                     | 职责        | 可以做                                            | **不可以做**                                      |
| ------------------------------------- | --------- | ---------------------------------------------- | --------------------------------------------- |
| **System**                            | 接线 + 共享内核（不属于四层） | 配置、注册事件/命令、**经 `*Action` 调用业务**、装配应用类；四层可以**引用**它里面的定义（项目异常类、配置） | 被四层反向调用它的**接线动作**（注册路由/事件/命令）                  |
| **Controller**                        | 请求的入口与出口  | 取输入、调 Business、把数据交给视图、跳转、404                  | 写业务规则、直接查数据库、拼 SQL                            |
| **Business**                          | 业务逻辑编排    | 调 Model、调 Service、条件抛业务异常                      | 读写 `$_GET`/`$_POST`/`$_SERVER`/Session，依赖当前请求 |
| **Model**                             | 数据访问      | 调 [Db](../reference/Db-Db.md)、按表做 CRUD、返回数组/对象 | 写业务判断、抛业务异常、调 Business                        |
| **[View](../reference/Core-View.md)** | 显示        | 用 Helper 与全局函数输出、读控制器给的数据                      | 查数据库、调 Business、写业务逻辑                         |

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

| 调用方 ↓ / 被调方 →  | Controller           | Business             | Model                    |
| -------------- | -------------------- | -------------------- | ------------------------ |
| **System**     | ✅ **只经 `*Action`**    | ❌（要经 Action）          | ❌（要经 Action）              |
| **Controller** | ⚠️ 仅同层复用走 Action     | ✅                    | ❌                        |
| **Business**   | ❌                    | ⚠️ 同层复用抽 `*Service`   | ✅                        |
| **Model**      | ❌                    | ❌                    | ⚠️ 跨库模型例外                |

`Db` / `Session` / `Service` / `View` 都不是「会互相调用的层」，所以不占行列，规矩用下面几句说清：

- **`Db`**：数据库只有 Model 碰——[`Model\Base`](../reference/Foundation-Model-Base.md) 已经给了 `Db()` / `find()` / `add()` / `getList()`；别的层里出现 `Helper::Db()` 或裸 SQL 就是越界。System 层只在接线时做连接级动作（例如 `Helper::DbCloseAll()`）。
- **`Session`**：只在 Controller 层经 `Controller\Session` 读写——[`SessionTrait`](../reference/Foundation-Controller-SessionTrait.md) 的 `get()`/`set()`/`unset()` 是 `protected`，所以要在那个类里加公开方法，外面用 `Session::_()->你的方法()`；System 层只在装配登录体系时碰；Business / Service / Model / View 都不碰（Business 为什么必须无状态见 §6）。
- **`Service`**：不是第五层，是**业务层内部**的复用——由 `*Business` 调用；**Controller 不直接调 Service**（要经 Business）。
- **View**：控制器用 `Helper::Show($data, $view)` 把数据交出去；视图只显示（`__h()`、`__url()` 这类全局函数），不查库、不调 Business。

矩阵的行按**目录 + 后缀**判定归属：`Controller/` 下的 `*Controller` 与 `*Action`、`Business/` 下的 `*Business` 与 `*Service`（`*Service` 归业务层）、`Model/` 下的 `*Model`（目录与后缀清单见随工程的 `AGENTS.md`）。**不带这些后缀、也不在上述目录里的类不属于四层**——它没有「本层」可依托，因此不能被别的层跨层调用，只能放进 `System/`（接线处）或与调用方同层。

`System` 那一行是**硬要求**：它**必须**经 Controller 层的 `*Action` 去调业务——CLI 命令（[第 2-16 章](cli.md)）、异常报告器（[第 2-12 章](exception.md)）、事件回调、路由钩子都写在这个位置，骨架里的 `AppAction`、`CommandAction`、`ExceptionAction` 就是它。不要跳过 Action 直接去读 Business/Model，否则「请求入口」的职责会摊到接线层。

**被 System 层调用的 Action 可以引用 System 层的东西**（项目异常类、配置、装配代码）——这是它和普通控制器的差别：Action 是接线的一部分。反过来，**普通控制器与业务代码不要反向调用 System 的接线动作**（注册路由、事件、命令），否则会出现循环装配。

三条最容易记错的：

- **控制器不碰 Db/Model**：一次「顺手查一下」就是越界，因为它绕过了业务规则（校验、权限、事务边界都写在 Business 里）。
- **业务不碰请求上下文**：`Business` 拿到的一切都应该由参数传进来。理由见下一节。
- **视图只读不写**：视图里查库或调业务，等于把渲染变成了第二次业务执行。

### 6. 为什么 Business 必须无状态

这不是洁癖，有两个很具体的后果：

1. **同一个 Business 会被多个入口复用**：Web 请求、CLI 命令（[第 2-16 章](cli.md)）、定时任务、测试（[第 2-17 章](testing.md)）都会调它。一旦它读 `$_GET` 或 Session，CLI 下就必然出错。
2. **可测性**：无状态 + 参数入、返回值出，才能不起服务器直接单测（[第 2-17 章](testing.md) 里的业务/模型测试就是这么写的）。

所以约定是：**请求上下文只允许出现在 Controller 层与 Helper 里**（`Helper::GET()`、`Helper::Parameter()`；会话在 `Controller\Session` 里包一层公开方法），Business 的入参一律显式传。

### 7. Helper 的分层：四层各有一套

框架把「能用什么便捷方法」也按层切开了——`Helper` 不是一个大杂烩，而是按层拆成四个类：

| 层          | 工程侧的类（`YourProjectName\<层>\Helper`） | 框架里对应的类                                                                                                                                                                      | 典型方法                                                   |
| ---------- | ----------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------ |
| Controller | `Controller\Helper`                 | [`DuckPhp\Foundation\Controller\ControllerHelper`](../reference/Foundation-Controller-ControllerHelper.md)                                                                   | `Show()`、`ShowJson()`、`Show302()`、`GET()`、`POST()`     |
| Business   | `Business\Helper`                   | [`DuckPhp\Foundation\Business\BusinessHelper`](../reference/Foundation-Business-BusinessHelper.md)                                                                           | `Setting()`、`Config()`、`BusinessThrowOn()`、`XpCall()`  |
| Model      | `Model\Helper`                      | [`DuckPhp\Foundation\Model\ModelHelper`](../reference/Foundation-Model-ModelHelper.md)（薄壳；方法在 [`Model\ModelHelperTrait`](../reference/Foundation-Model-ModelHelperTrait.md)） | `Db()`、`DbForRead()`、`SqlForPager()`                   |
| 应用/接线      | `System\Helper`                     | [`DuckPhp\Foundation\System\SystemHelper`](../reference/Foundation-System-SystemHelper.md)                                                                                   | `addRouteHook()`、`OnGlobalEvent()`、`FireGlobalEvent()` |

工程侧的 `Xxx\Helper` 类本身极短：`extends` 框架对应层的 Helper 一行，其余按需加。但**自定义方法要写成动态方法**——`public function foo()`，调用点写 `Helper::_()->foo()`；**别加静态方法**，因为框架自己的 Helper 方法全是静态的，混在一起既容易撞名，也丢掉实例侧才有的「相位 / 被覆盖替换」语义（`skeleton/src/Controller/Helper.php` 里就放了一个这样的示例方法）。也可以直接用框架现成的类；想把四层并成一个入口，用 [`DuckPhp\Foundation\Helper`](../reference/Foundation-Helper.md)（`__callStatic` 派发，见[第 2-9 章](helper.md)）。**反过来更重要**：某个方法不在你这一层的 Helper 里，通常就是框架在提示你「这件事不该在这一层做」。

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
Service 没有专门的基类或注册机制，就是「放在 `Business/` 目录下、被多个 Business 调用、且不碰请求上下文」的普通类——`skeleton/src/Business/SomeService.php` 就在这个位置上（骨架的示例文件，用完删掉）。形状大致是：

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
控制器之间要复用的**编排**（不是业务规则）抽成 Action，避免控制器互相继承；`skeleton/src/Controller/SomeAction.php` 是它的样板（无参 `__construct()` 是必须的），关键是 Action **只能调 Business 与 Session，不能直接调 Model**。

**③ System 层只做接线**
配置、异常/错误页、命令注册、事件注册、以及 `app` 里的子应用声明都写在 `src/System/`：`skeleton/src/System/App.php` 就是全部接线的样板（选项、错误页、`cmd`/`exception_reporter` 等注释示例、`app`）。

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
| 视图找不到                                    | 视图名与文件路径不一致             | `Helper::Show($data)` 用当前路由路径；显式指定时写 `'note/index'` → `view/note/index.php` |
| `Helper::Show('main', $data)` 页面白屏或视图找不到 | 参数顺序写反了：真实签名是 `Show($data = [], $view = '')` | 改成 `Helper::Show($data, 'main')`；用 `get_defined_vars()` 传当前变量最省事 |
| 改了 `Base.php`/`Helper.php` 后框架异常          | 那是框架约定文件                | 要扩展就在自己的子类里加，别改基类 |
| 控制器里出现 `DemoModel::_()` 或 SQL            | 越界：跳过了业务层                                    | 把查询挪进 Business，控制器只调 Business                                    |
| Business 里 `$_GET['id']` 报「未定义」          | Business 读了请求上下文，CLI/测试下没有这些超全局              | 由控制器取值后**当参数传进** Business                                        |
| 视图里查库，页面变得很慢或数据不一致                       | 视图里又跑了一次业务                                   | 数据由控制器准备，视图只渲染                                                   |
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
