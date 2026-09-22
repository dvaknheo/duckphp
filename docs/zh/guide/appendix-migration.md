# 附录 C · 从 Yii2 / CodeIgniter / Laravel 迁移

> 用途：给**已有其它框架经验**的人一张对照表——概念怎么对应、写法怎么换、哪些脑子里的默认假设必须丢掉。
> 前置：第一卷（[第 1-1 章](../guide/intro.md) 的定位对比）与[第 2-1 章 四层架构与调用规范](../guide/layers.md)。
> 一句话结论：DuckPHP 保留了你熟悉的「路由 → 控制器 → 视图 + 模型」外形，但**没有 ORM、没有 PSR-7/PSR-15、没有注解路由、没有服务容器自动注入**，换来的是「单文件可跑、相位隔离、覆盖/替换任意一层」。

## 1. 概念对照

| 你要找的东西 | Yii2 | CodeIgniter 4 | Laravel | DuckPHP |
|---|---|---|---|---|
| 入口 | `web/index.php` | `public/index.php` | `public/index.php` | 任意入口文件 → [`App::RunQuickly()`](../reference/Core-App.md)（[第 4-6 章](../guide/multi-entry.md)） |
| 应用类 | `yii\web\Application` 配置 | `Config\App` | `bootstrap/app.php` | 自己的 [`MyProj\System\App extends DuckPhp`](../reference/DuckPhp.md)（[第 1-5 章](../guide/configuration.md)） |
| 路由 | `UrlManager` 规则 | `Config\Routes` | `routes/web.php` | 约定（路径 → `Controller\Xxx::method`）+ `route_map` / 钩子（[第 2-2 章](../guide/routing.md)） |
| 控制器 | `yii\web\Controller` | `BaseController` | `App\Http\Controllers\Controller` | `Controller\XxxController`（建议继承 `Foundation\Controller\Base`）（[第 2-3 章](../guide/controllers.md)） |
| 请求对象 | `Yii::$app->request` | `$this->request` | `Request $request` 注入 | **没有请求对象**：`Helper::GET()/POST()/Parameter()`（[第 2-3 章](../guide/controllers.md)） |
| 响应对象 | `$this->asJson()` 等 | `$this->response` | `response()` / `Response` | `Helper::Show()` / `ShowJson()` / `Show302()` / `Show404()`（[第 2-3 章](../guide/controllers.md)） |
| 视图 | `$this->render()` + `layouts/` | `view()` + `layout` | Blade `view()` + `@extends` | PHP 文件 + `Helper::Show()` + `setViewHeadFoot()`（**没有模板语法**）（[第 2-4 章](../guide/views.md)） |
| 模型 | ActiveRecord（`yii\db\ActiveRecord`） | `Model` + `Entity` | Eloquent | **没有 ORM**：[`ModelTrait`](../reference/Foundation-Model-ModelTrait.md) 提供表名/读写分流/CRUD 原语，SQL 自己写（[第 2-6 章](../guide/model.md)） |
| 迁移 | `yii migrate` | `spark migrate` | `artisan migrate` | 无内置；建表 SQL 写在模型的 `init()` 或安装流程里（[第 2-5 章](../guide/database.md)） |
| 查询构造器 | `Yii::$app->db->createCommand()` | `$db->table()` | `DB::table()` | [`Helper::Db()->fetchAll($sql, ...)`](../reference/Db-Db.md) + `` `'TABLE'` `` 宏 + [`DbAdvanceTrait`](../reference/Db-DbAdvanceTrait.md) 的片段拼装（[第 2-5 章](../guide/database.md)） |
| 配置 | `config/web.php` 数组 | `.env` + `Config\*` | `config/*.php` + `.env` | **分两套**：`options`（白名单，代码里）与 `settings`（文件/`.env`，只读）（[第 1-5 章](../guide/configuration.md)） |
| 中间件 | `behaviors()` / filters | Filters | Middleware | 钩子链为主；中间件只是兼容扩展（[第 2-10 章](../guide/lifecycle.md)） |
| 事件 | `Event::on()` | Events | Events/Listeners | [`GlobalEvent`](../reference/Component-GlobalEvent.md)（回调**绑定相位**）（[第 2-12 章](../guide/events.md)） |
| 依赖注入/容器 | `Yii::$container` | Services | Service Container | **单例容器**：`Xxx::_()` / `Xxx::_($new)`（无自动注入）（[第 4-1 章](../guide/container-phases.md)） |
| 会话/用户 | `Yii::$app->user` | `session()` + 自定义 | `Auth` | [`SessionTrait`](../reference/Foundation-Controller-SessionTrait.md) + [`GlobalUser`](../reference/GlobalUser-GlobalUser.md)/[`GlobalAdmin`](../reference/GlobalAdmin-GlobalAdmin.md)（回调配置）（[第 2-9 章](../guide/session.md)、[第 2-18 章](../guide/user.md)、[第 2-19 章](../guide/admin.md)） |
| 验证 | `Model::rules()` | Validation | FormRequest / `validate()` | [`Validator`](../reference/Component-Validator.md) 组件三种口径（[第 2-8 章](../guide/validator.md)） |
| 缓存 | `Yii::$app->cache` | `cache()` | [`Cache::`](../reference/Component-Cache.md) | `Helper::Cache()`（默认空实现，装 [`RedisCache`](../reference/Component-RedisCache.md) 才生效）（[第 2-13 章](../guide/cache.md)） |
| 国际化 | `Yii::t()` | `lang()` | `__()` | `__l()` / `__hl()`（五级语言检测）（[第 2-14 章](../guide/i18n.md)） |
| 命令行 | `yii` 命令 | `spark` | `artisan` | 自己的 `cli.php` + `command_xxx()`（[第 2-15 章](../guide/cli.md)） |
| 测试 | Codeception/PHPUnit | PHPUnit | PHPUnit + Pest | PHPUnit（`tests/` + `data_for_tests/`）（[第 2-16 章](../guide/testing.md)） |
| 队列 | `yii\queue` | 无 | Queue | **无内置**：自己接 Redis/外部服务 |
| 调度 | 控制台命令 + cron | cron | Scheduler | cron + 自己的命令（[第 2-15 章](../guide/cli.md)） |

## 2. 写法对照：一个「列表 + 新建」功能

**Laravel 写法（对照用，示意）**

```php
Route::get('/notes', [NoteController::class, 'index']);
Route::post('/notes', [NoteController::class, 'store']);

class NoteController extends Controller {
    public function index(Request $r) {
        return view('notes.index', ['notes' => Note::paginate(10)]);
    }
    public function store(Request $r) {
        $data = $r->validate(['title' => 'required|max:64']);
        Note::create($data);
        return redirect()->route('notes.index');
    }
}
```

**DuckPHP 写法（同样的功能）**

```php
// 路由：不用配（/notes → Controller\notesController::index；或写 route_map 明确绑定）
// Controller
namespace MyProj\Controller;

class NoteController extends Base
{
    public function index()
    {
        $page = (int)Helper::GET('page', 1);
        [$total, $notes] = NoteBusiness::_()->paginate($page, 10);   // 业务层负责分页
        $pager = Helper::PageHtml($total);
        Helper::Show(get_defined_vars(), 'notes/index');
    }
    public function store()
    {
        $errors = Helper::Validator()->init(['title' => 'required|maxLen:64'])->valid(Helper::POST());
        if ($errors) {
            Helper::ShowJson(['code' => 1, 'errors' => $errors]);    // 或回显表单
            return;
        }
        NoteBusiness::_()->create(Helper::POST());
        Helper::Show302(Helper::Url('notes'));
    }
}
```

三处必须换掉的思维：

1. **没有 `Request`/`Response` 对象**：输入用 `Helper::GET/POST/Parameter()`，输出用 `Helper::Show*/Show302`；
2. **校验放在哪**：Laravel 的 `$r->validate()` 直接抛；这里更适合「业务层抛 + 控制器把错误数组给视图」（[第 2-8 章](../guide/validator.md)）；
3. **业务层是强制的中间环节**：不要像很多 Laravel 项目那样在控制器里直接 `Model::create()`（[第 2-1 章](../guide/layers.md)）。

## 3. 迁移步骤（建议顺序）

1. **先跑起一个页面**：装依赖 → 建 `src/System/App.php` + 一个控制器 + 一个视图（[第 1-2 章](../guide/install.md)、[第 1-4 章](../guide/quickstart.md)）；
2. **搬路由**：把原框架的路由表对照 `route_map` 写（能靠约定命中的就不写）（[第 2-2 章](../guide/routing.md)）；
3. **搬数据层**：把 ORM 调用改写成「模型方法 + 手写 SQL」，`` `'TABLE'` `` 宏处理表前缀（[第 2-5 章](../guide/database.md)、[第 2-6 章](../guide/model.md)）；
4. **补业务层**：把控制器里的业务判断下移到 Business，控制器只留输入输出（[第 2-1 章](../guide/layers.md)）；
5. **搬视图**：模板语法换成 PHP；输出一律 `__h()`（[第 2-4 章](../guide/views.md)）；
6. **搬横切**：中间件/filter → 钩子或（必要时）[`Ext\MyMiddlewareManager`](../reference/Ext-MyMiddlewareManager.md)（[第 2-10 章](../guide/lifecycle.md)）；
7. **会话与登录**：会话见[第 2-9 章](../guide/session.md)，用户/管理员回调见[第 2-18 章](../guide/user.md)与[第 2-19 章](../guide/admin.md)；
8. **补自己实现的部分**：CSRF、上传、限流、队列（框架不提供）（[第 2-17 章](../guide/security-performance.md)）；
9. **写冒烟测试**：照 `ZAllDemoTest` 的方式起内置服务器 + curl 各入口（[第 2-16 章](../guide/testing.md)）。

## 4. 最容易踩的差异

| 差异 | 说明 |
|---|---|
| **选项白名单** | 键没在该组件 `$options` 里声明 ⇒ 传了**直接被丢**，不报错（[第 1-5 章](../guide/configuration.md)） |
| **没有 ORM / 没有 Active Record** | 关系、懒加载、自动时间戳都要自己写；`ModelTrait` 只给原语（[第 2-6 章](../guide/model.md)） |
| **没有 PSR-7 / PSR-15** | 别指望 `$request->input()` 或标准中间件签名；`Ext\MyMiddlewareManager` 的 request 只是 `stdClass`（[第 2-10 章](../guide/lifecycle.md)） |
| **URL 必须用生成器** | 手写 `/note/show` 在子目录部署时会 404；一律 `__url()`（[第 2-2 章](../guide/routing.md)） |
| **四层是约定** | 越界不报错，但覆盖/多应用/测试复用会悄悄失效（[第 2-1 章](../guide/layers.md)） |
| **单例是全局可变的** | `Xxx::_($new)` 会替换容器里的实例——强大但要注意影响面（[第 4-1 章](../guide/container-phases.md)） |
| **多应用是「同进程多相位」** | 不是多进程/多站点；共享哪一层要显式决定（[第 3-4 章](../guide/component-sharing.md)） |
| **错误页要自己配** | 不配 `error_404`/`error_500` 就是英文占位文本（[第 2-11 章](../guide/exception.md)） |
| **调试开关有两级** | `is_debug` 与 `duckphp_is_debug`（设置），并且是「根 or 子应用」的或关系（[第 2-17 章](../guide/security-performance.md)） |

## 5. 反向迁移（从 DuckPHP 去别的框架）时值得带走的习惯

- **Business 层**：把业务规则集中在无状态类里，换框架时最容易原样搬；
- **`__h()` 的转义纪律**：换到 Blade/Twig 时继续显式转义用户数据；
- **URL 生成器习惯**：目标框架同样有 `route()`/`url()`，别退回硬编码路径。

---

> 配套阅读：附录 D（按症状查）、附录 B（可抄片段）、[第 4-9 章 排错手册](../guide/troubleshooting.md)。
