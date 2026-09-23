# 4-11 过时与冷门的扩展类

> `src/Ext/` 下的类不是都"推荐使用"。本章把**框架内部已经不用、也不推荐新代码使用**的扩展类集中讲一遍——它们在参考手册里各有一页，但指南正文此前没有交代过。
>
> 判断依据只有源码里的标记，跑一次就知道当前名单：
>
> ```bash
> grep -rn "@todo deprecate" src/     # 写作时命中 6 个类
> ```

⚠️ **"不推荐"不等于"会删"**：这些类都还有测试兜底（覆盖率是本仓库的硬指标，[第 4-7 章](coverage.md)），1.x 里不会移除。本章的用途是让你在参考手册里翻到一页时，**知道自己站在推荐路径上还是路径外**。

## 1. 一览

| 类 | 源码标记 | 它做什么 | 建议 |
|---|---|---|---|
| [`Ext\ExceptionWrapper`](../reference/Ext-ExceptionWrapper.md) | `@todo deprecate` | 把 PHP 错误包装成异常 | 用 `ExceptionManager`（[第 2-11 章](exception.md)） |
| [`Ext\HookChain`](../reference/Ext-HookChain.md) | `@todo deprecate` | 洋葱式中间件链 | 路由钩子与事件（[第 2-10 章](lifecycle.md)） |
| [`Ext\StaticReplacer`](../reference/Ext-StaticReplacer.md) | `@todo deprecate` | 把 `$GLOBALS`/函数静态/类静态搬进组件 | 新代码别用，见 §2.1 |
| [`Ext\MyFacadesBase`](../reference/Ext-MyFacadesBase.md) | `@todo deprecate` | 门面基类：`__callStatic()` 转发到真实类 | 用 `@method` 注解，见 §2.2 |
| [`Ext\MyFacadesAutoLoader`](../reference/Ext-MyFacadesAutoLoader.md) | `@todo deprecate` | 自动 `eval` 出门面类 | 同上 |
| [`Ext\ExtendableStaticCallTrait`](../reference/Ext-ExtendableStaticCallTrait.md) | `@todo deprecate` | 运行时给类登记静态方法 | 手写 `__callStatic()`，见 §2.3 |
| [`Ext\MiniRoute`](../reference/Ext-MiniRoute.md) | 无 | `Core\Route` 的早期子集 | [`Core\Route`](../reference/Core-Route.md)，见 §2.4 |
| [`Ext\Misc`](../reference/Ext-Misc.md) | 无 | 杂项工具集 | 见 §2.5 |
| [`Ext\ThrowOnTrait`](../reference/Ext-ThrowOnTrait.md) | 无 | 给异常类加一行 `ThrowOn()` | [第 2-11 章](exception.md) 的分层 `ThrowOn()` |

> `MiniRoute` / `Misc` **没有**废弃标记，但它们同样在推荐路径外：`grep -rn "MiniRoute" src/` 只命中它自己的文件——框架内部没有任何使用点。这也是本章要交代它们的原因。

## 2. 逐个说明

### 2.1 Ext\StaticReplacer：把全局状态挪进组件

三个方法都**按引用返回**，用法接近原生结构（`$v = &$sr->_GLOBALS('counter'); $v++;`）：

| 方法 | 仿真对象 |
|---|---|
| `_GLOBALS($k, $v)` | `$GLOBALS[$k]` |
| `_STATICS($name, $value, $parent)` | 函数内 `static` 变量（键由 `debug_backtrace` 的对象 hash + 类 + 函数名推出） |
| `_CLASS_STATICS($class, $var)` | 某类的静态属性（首次经反射读真值，之后返回本地副本） |

**为什么过时**：源码里就是 `@todo deprecate`（`src/Ext/StaticReplacer.php` 12 行），文件内还留着 `//TODO add Replace`（20 行）。它诞生于"测试要隔离全局状态"的年代，而本框架的答案已经换成了**相位容器 + `SingletonExTrait`**（[第 4-1 章](container-phases.md)）：要隔离状态就换相位（`App::Phase()`）或直接 `Class::_(new Class())` 换实例，不需要仿真 `$GLOBALS`。

**两个已知陷阱**（参考页也写了，动手前务必看）：`_CLASS_STATICS()` 返回的是**副本**，改它不会写回真实类静态属性；`_STATICS()` 的槽位按**调用位置**区分，同一个名字在不同函数里是两个槽。

### 2.2 Ext\MyFacadesBase + Ext\MyFacadesAutoLoader：eval 出来的门面

这对类是配套的：`MyFacadesAutoLoader` 注册 `spl_autoload_register()`，遇到 `facades_namespace`（默认 `MyFacades`）前缀或 `facades_map` 里的类名，就 **`eval`** 出一句 `namespace X { class Y extends MyFacadesBase {} }`（`src/Ext/MyFacadesAutoLoader.php` 55-56 行）；`MyFacadesBase::__callStatic()` 再把静态调用转发给 `getFacadesCallback()` 解析出的真实类，解析不到就抛 `\ErrorException("BadCall")`。

**为什么过时**：它要解决的是"静态调用没有 IDE 补全"——为了实现补全而**在运行时 eval 代码**。现在同样的需求用纯注释就能满足：`@method static` 标签。本仓库自己就是这么做的，[`Foundation\Helper`](../reference/Foundation-Helper.md) 与 [`DuckPhpAllInOne`](../reference/DuckPhpAllInOne.md) 上各有 96 条 `@method`（[第 2-7 章](helper.md)）。

⚠️ 反过来说：**`@method` 只是给 IDE 看的注释，`method_exists()`/反射看不到**这些方法。可调用的方法集合要看 `__callStatic()` 的分派顺序（[第 2-7 章](helper.md) 有 96 个方法的实测清单）。

### 2.3 Ext\ExtendableStaticCallTrait：动态登记静态方法

`use` 之后类获得 `AssignExtendStaticMethod($key, $value)` / `GetExtendStaticMethodList()` / `__callStatic()`：后者的流程是"查登记表 → 取回调 → `call_user_func_array()`"。登记值可以是数组、`callable`，也可以是两种字符串简写——`Class@method`（走 `Class::_()`）或 `Class->method`（走 `new Class()`）。

**为什么过时**：这是上面门面机制的低配版，同样用 `__callStatic()` 实现了"反射看不见的方法"。现在要么直接手写 `__callStatic()`（[`Foundation\Helper`](../reference/Foundation-Helper.md) 的真实做法），要么根本不需要——用真实方法就行。

> 框架 `src/` 里已经没有使用点，只有测试夹具还在用它（`tests/Core/AppTest.php` 755 行的别名 `use`）。

### 2.4 Ext\MiniRoute：Core\Route 的早期子集

`MiniRoute` 只做"PATH_INFO → 控制器类/方法 → 反射校验 → 调用"这一件事，**没有钩子链、没有重写、没有资源路由**（[`Core\Route`](../reference/Core-Route.md) 的完整能力见[第 2-2 章](routing.md)）。失败时它**不抛异常**，而是把错误码写进 `$route_error`（`E001` 前缀不符、`E003` 类不存在、`E005` 隐藏方法……），用 `getRouteError()` 读。

**为什么在推荐路径外**：框架的路由是写死的 `Route::_()`（`src/DuckPhp.php` 174 行就是这么取的），**没有"换路由类"的选项**；`MiniRoute` 也不是 `Route` 的子类，无法借 `Route::_(new MiniRoute())` 顶上。要用它只能自己起一套：

```php
\DuckPhp\Ext\MiniRoute::_()->init(['namespace' => 'MyProject'], $app);
$ok = \DuckPhp\Ext\MiniRoute::_()->run();      // 解析并调用控制器
```

也就是说：**进了 DuckPHP 的应用，就别再引 MiniRoute**；只有"我只想要一个路由解析器、不要整个框架"时才可能用它。

### 2.5 Ext\Misc：一个能力都还在，但都不再是唯一选择

`Misc` 是若干杂项工具的大杂烩，逐项看就明白它为什么被边缘化：

| 方法 | 实际行为 | 现在用什么 |
|---|---|---|
| `Import($file)` | `include_once {path}/{path_lib}/{file}.php`，`path_lib` 默认 `'lib'` | Composer 自动加载（`src/` 里除它自己**没有任何地方**读 `path_lib`） |
| `RecordsetH($data, $cols)` | 对指定列做 HTML 转义 | `CoreHelper::H()` / 全局 `__h()`（[第 2-17 章](security-performance.md)） |
| `RecordsetUrl($data, $cols_map)` | 按 `{列名}` 模板替换后经 `Route::Url` 生成 URL | 可留用；等价的还有 `__url()` |
| `DI($name, $object = null)` | 读/写组件**实例内**的一个数组（`$this->_di_container`） | 跨类共享请用相位容器（[第 4-1 章](container-phases.md)）——这个 DI 只在 `Misc::_()` 这一份实例里可见 |
| `CallAPI($class, $method, $input, $interface)` | 反射调用：按参数名从 `$input` 取值、按 `bool/int/float/string` 过滤、缺参抛 `ReflectionException`、可选校验类实现了某接口 | 没有替代品；需要就照用 |

**唯一还值得了解的**是 `CallAPI()`：它做的是"把 `$_POST` 按目标方法的签名喂进去"，这在 RPC / 表单服务化的场景里比手写 `func_get_args()` 稳。其余四项用上一列的现代写法更省事。

### 2.6 Ext\ThrowOnTrait：一行 trait

```php
trait ThrowOnTrait
{
    public static function ThrowOn($flag, $message, $code = 0)
    {
        if (!$flag) { return; }
        throw new static($message, $code);
    }
}
```

给异常类加一个"条件抛"的静态入口。分层的 `Helper::ThrowOn()`（`ProjectThrowOn`/`BusinessThrowOn`/`ControllerThrowOn`）已在[第 2-11 章](exception.md)交代；这个 trait 是它的前身，**框架内部没有使用点**（`grep -rn ThrowOnTrait src/` 只命中 trait 自身）。要自定义异常类又想要同样的写法，直接抄这两行比 `use` 它更清楚。

## 3. 还有几个"不是给业务用"的冷门页

这几类在指南正文里也不常见，但它们**没有过时**，只是用途固定：

| 类/文件 | 定位 | 在哪儿讲到 |
|---|---|---|
| [`Ext\RouteHookWebInstallerView`](../reference/Ext-RouteHookWebInstallerView.md) | 安装向导的**内置视图模板**（文件里没有任何 class/function，被 `RouteHookWebInstaller::show()` `include`） | [第 3-6 章](installer.md) |
| [`Ext\SqlDumperSupporterByMysql`](../reference/Ext-SqlDumperSupporterByMysql.md) / [`BySqlite`](../reference/Ext-SqlDumperSupporterBySqlite.md) | SQL 导出的方言实现，**默认映射里就有**这两个 | [第 2-5 章](database.md) |
| [`Ext\SqlDumperSupporterByPgsql`](../reference/Ext-SqlDumperSupporterByPgsql.md) | 同样是方言实现，但**默认映射里没有**它：要自己加 `database_driver_SqlDumperSupporter_map`（`src/Ext/SqlDumperSupporter.php` 16-19 行） | [第 2-5 章](database.md) |

## 4. 参考手册

- [DuckPhp\Ext\StaticReplacer](../reference/Ext-StaticReplacer.md)、[MyFacadesBase](../reference/Ext-MyFacadesBase.md)、[MyFacadesAutoLoader](../reference/Ext-MyFacadesAutoLoader.md)、[ExtendableStaticCallTrait](../reference/Ext-ExtendableStaticCallTrait.md)、[MiniRoute](../reference/Ext-MiniRoute.md)、[Misc](../reference/Ext-Misc.md)、[ThrowOnTrait](../reference/Ext-ThrowOnTrait.md)
- 已在前文交代过的过时类：[ExceptionWrapper](../reference/Ext-ExceptionWrapper.md)（[第 2-11 章](exception.md)）、[HookChain](../reference/Ext-HookChain.md)（[第 2-10 章](lifecycle.md)）

## 下一步

- [第 2-7 章 Helper 与全局函数](helper.md)：`@method` 与 `__callStatic` 的推荐做法。
- [第 4-10 章 设计取舍与已知坑](design-notes.md)：哪些"看起来该改"的地方是刻意的。
- [参考手册维护指南](../reference-maintenance-guide.md)：过时类怎么在文档里标注。
