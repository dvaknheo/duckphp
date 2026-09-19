# 3 目录结构与编码规则

> 解决什么问题：把项目摆成「框架期望的样子」，并知道哪些文件能改、哪些不能改，各类文件该怎么命名。
> 前置：[第 2 章](install.md)。预计 12 分钟。
> 本章结构与 `skeleton/`（脚手架实际生成的工程）一致。

## 标准结构

```
project/
├── public/
│   └── index.php          ← Web 入口。只有几行「加载 + 启动」，勿改
├── bin/
│   └── cli.php            ← CLI 入口。同样是「加载 + 启动」，勿改
├── config/
│   └── DuckPhpSettings.config.php   ← 设置文件：数据库/Redis 等敏感信息（第 5 章）
├── src/
│   ├── System/
│   │   └── App.php        ← 应用类：项目的配置中枢（选项都写这里）
│   ├── Controller/
│   │   ├── Base.php       ← 控制器基类（勿改）
│   │   ├── Helper.php     ← 控制器侧助手（勿改；或直接用 Foundation\Controller\Helper）
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

| 找什么 | 依据 |
|---|---|
| 控制器目录 | 从**应用类文件所在目录**推算 + `namespace_controller`（默认 `Controller`） |
| 视图文件 | 应用 `path` + `path_view`（默认 `view`）+ 视图名 |
| 配置文件 | 应用 `path` + `path_config`（默认 `config`） |
| 设置文件 | 选项 `setting_file`（默认 `config/DuckPhpSettings.config.php`） |

所以：**类文件必须落在与命名空间一致的目录里**（`MyProj\Controller\NoteController` → `src/Controller/NoteController.php`，且 `path` 指向项目根）。

## 命名规范

| 类型 | 规则 | 例子 |
|---|---|---|
| 控制器 | `{名字}Controller`，方法名就是 URL 段 | `NoteController::index()` → `/Note/index` |
| 动作类（控制器层复用） | `{名字}Action`，必须有无参 `__construct()` | `ExportAction` |
| 业务类 | `{名字}Business` | `NoteBusiness` |
| 服务类（业务层复用） | `{名字}Service` | `MailService` |
| 模型类 | `{名字}Model`，**类名决定表名**：去掉 `Model` 再小写 | `NoteModel` → 表 `note` |
| 异常类 | `{名字}Exception` | `ProjectException` |
| 会话类 | `Session` | `Session` |
| CLI 命令方法 | `command_{名字}` | `command_sync()` → `php bin/cli.php sync` |

两条容易踩的细节：

- **`controller_method_prefix` 默认是空串**：方法名直接当 URL 段。想用 `action_` 前缀就显式配 `'controller_method_prefix' => 'action_'`（很多老文档/示例还写着 `action_`，那是早期默认值）。
- **URL 大小写敏感**：默认不会把 `/note/list` 自动转成 `/Note/list`；要宽松匹配就配 `controller_class_adjust`（第 9 章）。

## 层级调用铁律

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

| 层 | 可以调用 | 禁止调用 |
|---|---|---|
| Controller / Action | 同层 Action、Business、Helper、Session | **Model、Service**（必须经 Business） |
| Business / Service | Model、Helper | Session、`$_GET`/`$_POST`/`$_SERVER`/`$_FILES` |
| Model | 仅数据访问（`Db`/`ModelTrait`） | 业务逻辑、抛异常 |
| View | 全局函数 `__h()` / `__url()` / `__res()` / `__l()` | 框架类（尽量不要） |
| 自定义 Helper | System 层、框架、第三方库 | 任何业务层（Controller/Action/Business/Service/Model） |

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

正确的抛异常方式：Controller 层用 `Helper::ControllerThrowOn(...)`，Business 层用 `Helper::BusinessThrowOn(...)`（第 18 章）。

## 常见错误

| 现象 | 原因 | 改法 |
|---|---|---|
| 新增控制器 404 | 文件名/命名空间/后缀不匹配 | `src/Controller/NoteController.php` + `class NoteController` + 后缀默认 `Controller` |
| 模型找不到表 | 表名与类名推导不一致 | `NoteModel` → 表 `note`；表名不同就在模型里 `protected $table_name = 'notes';` |
| 视图找不到 | 视图名与文件路径不一致 | `Helper::Show($data)` 用当前路由路径；显式指定时写 `'note/index'` → `view/note/index.php` |
| CLI 下业务层报错 | 业务层里读了 `$_POST`/Session | 参数由控制器传入（上面的铁律） |
| 改了 `Base.php`/`Helper.php` 后框架异常 | 那是框架约定文件 | 要扩展就在自己的子类里加，别改基类 |

## 下一步

- [第 4 章 第一个页面](quickstart.md)：按这套结构写出第一个完整功能。
- [第 8 章 四层架构与调用规范](layers.md)：为什么这样分层、越界后会出现什么后果。
- [第 5 章 配置与设置](configuration.md)：`App.php` 里的 options 与 `config/` 里的 settings 有何区别。
