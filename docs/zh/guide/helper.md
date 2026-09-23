# 2-7 Helper 与全局函数

> 解决什么问题：四层的 Helper 分别是什么、该用哪一个、工程里怎么写自己的 Helper、`Helper::` 与全局函数怎么选，以及为什么「这一层 Helper 里没有这个方法」往往是在提示你越界了。
> 前置：[第 2-1 章 四层架构与调用规范](layers.md)。预计 15 分钟。
> 示例：`demo/src/{Controller,Business,Model}/Helper.php`、`skeleton/src/*/Helper.php`、`tests/data_for_tests/ZThirdDemo/src/Controller/Helper.php`（都是几行的薄壳类）、`src/Core/Functions.php`（全局函数定义）。

## 最小示例

工程里的 Helper 就是这么短（`demo/src/Controller/Helper.php` 全文）：

```php
namespace ProjectNameTemplate\Controller;

use DuckPhp\Foundation\Controller\ControllerHelper;

class Helper extends ControllerHelper
{
}
```

四层各有一个这样的类，业务代码里直接用：

```php
// 控制器里
Helper::Show(get_defined_vars(), 'note/list');
// 业务里
Helper::BusinessThrowOn(!$note, '便签不存在', 404);
// 模型里
$rows = Helper::DbForRead()->fetchAll($sql);
// 视图里（全局函数）
<h1><?= __h($note['title']) ?></h1>
```

## 机制说明

### 1. 四层四个类，各管一摊

框架把便捷方法按层分到四个类里（`src/Foundation/<层>/`），**一层的 Helper 里不会出现另一层的专属方法**：

| 层 | 现成类 | 代表方法 |
|---|---|---|
| Controller | [`DuckPhp\Foundation\Controller\ControllerHelper`](../reference/Foundation-Controller-ControllerHelper.md) | `Show()` `ShowJson()` `Show302()` `Show404()` `Render()` `GET()` `POST()` `REQUEST()` `Parameter()` [`Pager()`](../reference/Component-Pager.md) `PageHtml()` `header()` `setcookie()` `ControllerThrowOn()` `UserId()` `AdminId()` |
| Business | [`DuckPhp\Foundation\Business\BusinessHelper`](../reference/Foundation-Business-BusinessHelper.md) | `Setting()` `Config()` `AppOptions()` `XpCall()` `BusinessThrowOn()` [`Cache()`](../reference/Component-Cache.md) [`Validator()`](../reference/Component-Validator.md) `ValidatorFilter()` `FireGlobalEvent()` `OnGlobalEvent()` `AdminService()` `UserService()` `PathOfProject()` `PathOfRuntime()` |
| Model | [`DuckPhp\Foundation\Model\ModelHelper`](../reference/Foundation-Model-ModelHelper.md)（薄壳，方法全在 [`Model\ModelHelperTrait`](../reference/Foundation-Model-ModelHelperTrait.md) 里） | [`Db()`](../reference/Db-Db.md) `DbForRead()` `DbForWrite()` `SqlForPager()` `SqlForCountSimply()` `DatabaseDriver()` |
| 应用/接线（System） | [`DuckPhp\Foundation\System\SystemHelper`](../reference/Foundation-System-SystemHelper.md) | `addRouteHook()` `replaceController()` `assignRoute()` `assignImportantRoute()` `assignRewrite()` `Redis()` `SESSION()` `getCliParameters()` `isRunning()` `isInException()` `system_wrapper_replace()` |

两个「全量版」：

- [`DuckPhp\Foundation\Helper`](../reference/Foundation-Helper.md)：**自己不声明任何方法**，用 `__callStatic` 按 **System → Controller → Business → Model** 的顺序找第一个有该方法的那层并转过去；源码里带 96 条 `@method` 注释（只为 IDE / 静态分析可见，`method_exists()` 与反射看不到）。
- [`DuckPhp\DuckPhpAllInOne`](../reference/DuckPhpAllInOne.md)：同一套派发，把整个应用塞进一个类。

工程侧的 `Xxx\Helper` 有两种写法，任选：

```php
// 写法 A（demo / skeleton 用的）：继承本层的类，之后可以往里加自己的方法
use DuckPhp\Foundation\Controller\ControllerHelper;

class Helper extends ControllerHelper
{
    public static function money(float $v): string
    {
        return '¥' . number_format($v, 2);
    }
}
```

```php
// 写法 B：调用点想保持 `Helper::` 这个名字时，import 起别名
use DuckPhp\Foundation\Controller\ControllerHelper as Helper;

Helper::Show(get_defined_vars(), 'note/list');
```

模型基类走的是另一条路：[`DuckPhp\Foundation\Model\Base`](../reference/Foundation-Model-Base.md) 同时 `use ModelTrait` 与 [`ModelHelperTrait`](../reference/Foundation-Model-ModelHelperTrait.md)，所以模型子类里 `$this->Db()`、`$this->getList()` 都能用（`$model->Db()` 这种「静态方法经实例调用」也成立）。

### 2. Helper 是静态门面，底层是可替换的系统包装

`Helper::` 的方法都很薄，转发给组件单例：

```php
public static function Show($data = [], $view = '')       { return App::_()->_Show($data, $view); }
public static function GET($key = null, $default = null)  { return SuperGlobal::_()->_GET($key, $default); }
public static function Db($tag = null)                    { return DbManager::_()->Db($tag); }
```

好处是**可替换**：[`SuperGlobal`](../reference/Core-SuperGlobal.md)、[`SystemWrapper`](../reference/Core-SystemWrapper.md)（`header()`/`setcookie()`/`exit()` 这些）、[`Runtime`](../reference/Core-Runtime.md) 都能在测试或常驻进程里换掉，业务代码不用改。

### 3. 「这一层没有这个方法」= 框架在提醒你越界

```php
// Business 里想输出页面：
Helper::Show($data, 'x');     // ❌ Business 的 Helper 继承链里没有 Show()
```

`BusinessHelper` 里没有 `Show()`、`ModelHelper` 里没有 `GET()`，这不是漏了，而是**用类型系统表达的边界**：业务层不该直接输出、模型层不该读请求。各层 Helper 就是按这个边界拆开的类，工程里「继承哪一层的 Helper」就等于声明了「我属于哪一层」。正解见下表：

| 想做的事 | 别用 | 该用 |
|---|---|---|
| 业务里读请求数据 | `Helper::GET()` | 由控制器取值后当参数传入 |
| 业务里输出页面 | `Helper::Show()` | 返回数据，控制器负责输出 |
| 模型里抛业务异常 | 业务异常 | 返回数据/`false`，判断留给 Business |
| 任意层里读配置/设置 | 直接读文件 | `Helper::Setting()` / `Helper::Config()`（业务层可用） |

### 4. 全局函数：视图层的「Helper」

`src/Core/Functions.php` 定义了一组全局函数，专为模板与随手调用准备：

| 函数 | 作用 |
|---|---|
| `__h($str)` | HTML 转义 |
| `__l($str, $args, $fallback)` | 翻译（[第 2-14 章](i18n.md)） |
| `__hl($str, $args)` | 翻译 + 转义 |
| `__langtext($desc, $args)` | 一段文本里的 `[[key\|fallback]]` 占位翻译 |
| `__json($data, $options)` | JSON 编码 |
| `__url($url)` / `__domain($use_scheme)` / `__res($url)` | URL / 域名 / 资源地址（[第 2-2 章](routing.md)、[第 3-3 章](static-resources.md)） |
| `__display(...)` | 调试输出 |
| `__var_dump()` / `__var_log()` / `__trace_dump()` / `__debug_log()` | 调试与日志（[第 1-6 章](debugging.md)） |
| `__logger()` | 取日志器 |
| `__is_debug()` / `__is_real_debug()` / `__platform()` | 环境判断 |

放在视图里最自然：

```php
<h1><?= __h($note['title']) ?></h1>
<a href="<?= __url('note/show?id=' . (int)$note['id']) ?>">详情</a>
<p><?= __l('welcome', ['name' => __h($user['name'])]) ?></p>
```

`Helper::` 与全局函数的分工：

| 场景 | 用 |
|---|---|
| 视图模板 | 全局函数（`__h`/`__url`/`__l`） |
| 控制器/业务/模型的 PHP 代码 | 对应层的 `Helper::` |
| 需要能被替换、可测试的系统调用 | `Helper::`（走系统包装） |

## 常见写法

**① 工程 Helper 只继承本层的类**（见开头的薄壳写法）——**这就是边界的一部分**。

**② 需要「一处引入、四层都能用」时**

```php
use DuckPhp\Foundation\Helper;      // 并集门面：__callStatic 派发到四层

Helper::Setting('page_size', 20);   // → Business\BusinessHelper
Helper::Db()->fetch($sql);          // → Model\ModelHelper
Helper::Show($data, 'note/list');   // → Controller\ControllerHelper
```

代价要知道：并集类的方法**在反射层面不存在**（`method_exists()` 为 false、IDE 只能靠 `@method` 注释），而且同名方法按固定顺序判定胜负（见[参考手册](../reference/Foundation-Helper.md#注意事项)）。分层清楚的项目更推荐按层用 A 写法。

**③ 配置与设置一律走 Helper，不直接读文件**

```php
$limit = Helper::Setting('page_size', 20);        // DuckPhpSettings.config.php / .env
$key   = Helper::Config('payment', 'app_id');     // config/payment.php
```

**④ 想替换系统调用（测试、常驻进程）用 `system_wrapper_replace()`**

```php
Helper::system_wrapper_replace([
    'header' => function ($output, $replace = true, $code = 0) { /* 记录而不真的发送 */ },
    'exit'   => function ($code = 0) { throw new \Exception('exit(' . $code . ')'); },
]);
```

**⑤ 路由/重写这类「接线」调用放 System 层**

```php
// src/System/App.php 的 onInited() 里
Helper::addRouteHook($cb, 'prepend-inner');
Helper::assignRewrite('/legacy', 'home/index');
```

## 常见错误

| 现象 | 原因 | 改法 |
|---|---|---|
| `Call to undefined method ...::Show()` | 用的是业务/模型层的 Helper（继承链里没有输出方法） | 输出放控制器；或检查 `extends` 的是哪一层的 Helper |
| 并集 `Helper::Foo()` 报 `Call to undefined method` | 四层都没有这个方法（`__callStatic` 找不到就报错） | 查四层 Helper 的参考页，或看源码里的 `@method` 注释确认名字 |
| 并集 `Helper::Setting()` 行为与预期不符 | 同名方法按 System → Controller → Business → Model 顺序判定 | 需要明确语义时直接用那一层的类（`Business\BusinessHelper::Setting()`） |
| 反射/IDE 找不到并集类的方法 | `__callStatic` 方案下方法是注释而非声明 | 用 `@method` 注释支持的 IDE；或直接依赖四层类 |
| `assignRewrite('article/123', …)` 不生效 | 重写键**少了前导 `/`**：钩子拿 `'/'.$path_info` 比较 | 写成 `'/article/123'` |
| 视图里 `Helper::` 报类不存在 | 视图里没引入 Helper | 视图里用全局函数（`__h`/`__url`/`__l`） |
| 业务层里 `Helper::GET()` 报错 | 越界 | 参数由控制器取好传进来 |
| 换了系统包装但业务没变化 | 业务代码直接用了原生函数或 `new` | 全部走 `Helper::` |
| 各层混用一个「大 Helper」 | 边界失效：业务里能拿到输出/请求方法 | 每层只继承本层的类；确需全量就用 `Foundation\Helper` 并知道代价 |
| 业务层用 `Helper::Db()` 写裸 SQL | 越界：绕过模型层 | SQL 收进模型（[第 2-6 章](model.md)） |
| 找不到某个全局函数 | 它确实没定义（拼写/版本差异） | 以 `src/Core/Functions.php` 为准，或改用 `Helper::` 对应方法 |

## 下一步

- [第 2-8 章 表单与数据验证](validator.md)：`Helper::Validator*` 的用法。
- [第 2-18 章 用户体系](user.md) / [第 2-19 章 管理员体系](admin.md)：`Helper::UserId()` / `AdminService()` 等。
- [第 4-11 章 过时与冷门的扩展类](deprecated-exts.md)：`@method` 之前的那些动态静态调用方案（`MyFacades*`、`ExtendableStaticCallTrait`）与它们的替代。
- [DuckPhp\Core\Functions（全局函数参考）](../reference/Core-Functions.md)：完整函数清单与签名。
- 参考手册：[`Controller\ControllerHelper`](../reference/Foundation-Controller-ControllerHelper.md)、[`Business\BusinessHelper`](../reference/Foundation-Business-BusinessHelper.md)、[`Model\ModelHelper`](../reference/Foundation-Model-ModelHelper.md)、[`Model\ModelHelperTrait`](../reference/Foundation-Model-ModelHelperTrait.md)、[`System\SystemHelper`](../reference/Foundation-System-SystemHelper.md)、[`Foundation\Helper`（并集）](../reference/Foundation-Helper.md)。