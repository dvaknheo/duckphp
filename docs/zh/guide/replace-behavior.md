# 4-3 替换框架行为

> 解决什么问题：**想改框架的某个行为，该动哪里**——换类、换文件、换单例、换系统调用、换组件，五种层次的边界与优先级，以及"改完没生效"怎么查。
> 前置：[第 3-5 章 重写与覆盖](overriding.md)（文件/类覆盖）、[第 4-1 章 容器与相位内部机制](container-phases.md)。预计 20 分钟。
> 示例：[`Ext\CallableView`](../reference/Ext-CallableView.md)/[`Ext\JsonView`](../reference/Ext-JsonView.md)（换 [View](../reference/Core-View.md) 实现）、[`Ext\RedisCache`](../reference/Component-RedisCache.md)（换 [Cache](../reference/Component-Cache.md) 实现）、`AppWithAllOptions.php`（选项全表）。

## 最小示例

三个层次的替换，一行搞定一个：

```php
// ① 换某个控制器的实现（类级）
$options = [
    'controller_class_map' => [
        'MyProj\Controller\UserController' => 'MyProj\Controller\UserControllerV2',
    ],
];
// 运行期也能换：
Helper::replaceController(\MyProj\Controller\UserController::class, \MyProj\Controller\UserControllerEx::class);
```

```php
// ② 换单例（实例级）——View 就是这么被换掉的
View::_(new \DuckPhp\Ext\JsonView());
// 等价于选项方式：
$options = ['ext' => [\DuckPhp\Ext\JsonView::class => true]];
```

```php
// ③ 换系统调用（最底层）——测试与常驻进程都靠它
Helper::system_wrapper_replace([
    'exit' => function ($code = 0) { throw new \RuntimeException('exit(' . $code . ')'); },
]);
```

## 机制说明

### 1. 五种层次：想改什么 → 动哪里

| 层次        | 手段                                                                                        | 生效范围       | 典型用途                                     |     |
| --------- | ----------------------------------------------------------------------------------------- | ---------- | ---------------------------------------- | --- |
| **类级**    | `override_class` 选项、`controller_class_map`、`Helper::replaceController()`                  | 整个应用/指定控制器 | 换成自己写的实现（`override_class` 会让应用直接变成另一个类）  |     |
| **文件级**   | `getOverrideableFile()` 的相位回退（`view/`、`config/`、`res/`）                                   | 按相位逐层      | 子应用覆盖视图/配置/资源（[第 3-5 章](overriding.md)）  |     |
| **单例级**   | `Xxx::_($newObject)`                                                                      | 当前相位       | 换 View 引擎、换 Cache 实现、测试替身                |     |
| **系统调用级** | `Helper::system_wrapper_replace()`（[`SystemWrapper`](../reference/Core-SystemWrapper.md)） | 全进程        | 拦 `header`/`exit`/`session_start`，做测试或常驻 |     |
| **组件级**   | 组件自己的选项：`database_class`、`view_*`、`*_skip_replace`                                        | 该组件        | 换 [Db](../reference/Db-Db.md) 实现、关掉自动替换  |     |

### 2. 类级：`override_class` 与 `controller_class_map`

```php
// 入口处把整个应用类换掉（框架在 init() 最前面处理它）
App::RunQuickly(['override_class' => \MyProj\System\AppEx::class]);
```

框架的动作是：把 `override_class` 的值当成新类，`$class::_(new $class)->init($options, $context)`（[`KernelTrait::init()`](../reference/Core-KernelTrait.md) 开头），并把 `override_from` 记成原类——所以覆盖类能拿到"我是从谁被换过来的"。

控制器级更常用的是 `controller_class_map`（[第 2-2 章](routing.md)）：它是**类名 → 类名**的映射，在路由解析出类名之后、实例化之前生效。`Helper::replaceController()` 就是往这个映射里写一条。

### 3. 单例级：`Xxx::_($new)`

所有 [`ComponentBase`](../reference/Core-ComponentBase.md) 子类都通过 [`SingletonExTrait::_($object = null)`](../reference/Core-SingletonExTrait.md) 走容器：不传参取实例，**传对象就替换实例**。这就是扩展换实现的统一姿势：

```php
// View：Ext\CallableView / EmptyView / JsonView 在 init() 里做这件事
View::_(static::_());

// Cache：Ext\RedisCache 在 initContext() 里做这件事
Cache::_($this);

// 自己写的组件同理
Db::_($myDbImplementation);
```

每个扩展都留了"别自动替换我"的开关：`callable_view_skip_replace`、`json_view_skip_replace`、`empty_view_skip_replace`、`redis_cache_skip_replace`——多应用场景里想让某个应用保留原实现时就设它。

### 4. 系统调用级：`SystemWrapper`

`SystemWrapper` 把这 10 个函数包成了可替换的"provider"（`system_wrapper_get_providers()` 可查当前表）：

`header`、`setcookie`、`exit`、`set_exception_handler`、`register_shutdown_function`、`session_start`、`session_id`、`session_destroy`、`session_set_save_handler`、`mime_content_type`

```php
Helper::system_wrapper_replace([
    'header' => function ($output, $replace = true, $code = 0) { /* 收集起来断言 */ },
    'mime_content_type' => fn ($f) => 'text/plain',
]);
```

意义有两个：**测试**（输出/跳转可断言，[第 2-16 章](testing.md)）与**移植**（换掉某个平台不一致的行为）。框架内部所有输出都走它（`Helper::header()`、`Helper::setcookie()`、`Html`/`Json` 输出），所以替换一处、全局生效。

### 5. 组件级：选项比继承更"框架友好"

| 想要 | 用选项 | 而不是 |
|---|---|---|
| 换 Db 实现类 | `database_class` | 改 `Db.php` |
| 换整份 Session 行为 | `session_prefix` + [`SessionTrait`](../reference/Foundation-Controller-SessionTrait.md) 子类 | 覆盖 [`SuperGlobal`](../reference/Core-SuperGlobal.md) |
| 关掉某个扩展 | `ext` 里置 `EXT_DISABLE`（`0`） | 卸载代码 |
| 每次请求重建组件 | `ext` 里置 `EXT_RENEW`（`3`） | 手动 `reInit()` |

`EXT_*` 的五个取值见[第 4-2 章](custom-component.md)；[`DbManager`](../reference/Component-DbManager.md) 的 `database_class`、[`App`](../reference/Core-App.md) 的 `override_class` 都属于这一类。

### 6. 「谁赢」与"改完没生效"的排查

优先级从高到低（同一目标被多处替换时）：

```
override_class（换整个应用类）
  > 子类 override 方法
  > 单例替换 Xxx::_($new)
  > 文件级覆盖（getOverrideableFile 相位回退）
  > 组件选项默认值
```

排查三步：

```php
\DuckPhp\Core\PhaseContainer::Dump();                 // 这个相位里现在到底是哪个类的实例
var_dump(App::_()->options['controller_class_map']);  // 映射真的写进去了吗（选项白名单！第 1-5 章）
App::_()->getOverrideableFile('view', 'main.php');    // 文件级：实际命中的是哪个文件
```

最常见的两个原因：**取实例时用的不是同一个相位**（[第 4-1 章](container-phases.md)），以及**选项根本没活下来**（组件用它自己的 `$options` 白名单过滤，[第 4-2 章](custom-component.md)）。

## 常见写法

**① 测试里拦掉输出与 exit**

```php
Helper::system_wrapper_replace([
    'header' => function (...$a) { TestRecorder::$headers[] = $a; },
    'exit'   => function ($code = 0) { throw new ExitCalled($code); },
]);
```

**② 换视图引擎（函数式视图 / JSON / 空视图）**

```php
$options = [
    'ext' => [\DuckPhp\Ext\CallableView::class => true],
    'callable_view_class' => \MyProj\View\Views::class,
];
```

**③ 让某个应用保留框架原实现**（多应用时）

```php
'ext' => [\DuckPhp\Ext\JsonView::class => true],
'json_view_skip_replace' => true,      // 只在这个应用里不替换
```

**④ 换掉一个控制器而不动原文件**

```php
Helper::replaceController(\MyProj\Controller\AdminController::class, \MyProj\Admin\Controller\AdminController::class);
```

**⑤ 自定义 Db 实现类**

```php
$options = ['database_class' => \MyProj\Db\MyDb::class];
```

## 常见错误

| 现象                               | 原因                                  | 改法                                                                   |
| -------------------------------- | ----------------------------------- | -------------------------------------------------------------------- |
| 替换后完全没反应                         | 调用处用了 `new`，没走容器                    | 一律 `Xxx::_()`（[第 3-5 章](overriding.md)）                               |
| 子应用里替换了，父应用没变                    | 单例是**按相位**存的                        | 替换在目标相位里做，或改用共享组件（[第 3-4 章](component-sharing.md)）                    |
| `system_wrapper_replace()` 拦不住输出 | 代码直接调了原生 `header()`/`echo`+`exit`   | 走 `Helper::header()` / `Helper::exit()`                              |
| 选项写了但没生效                         | 不在该组件的 `$options` 白名单里              | 看[第 4-2 章](custom-component.md)的白名单机制；或用 `$hidden_options` 的读法        |
| `controller_class_map` 不生效       | 键写的是短名                              | 键必须是**类的全限定名**（`MyProj\Controller\UserController`）                   |
| `override_class` 导致配置看起来变了       | 换的是整个应用类                            | 注意新类的 `$options` 与 `override_from`；选项仍会合并（[第 1-5 章](configuration.md)） |
| 覆盖视图没生效                          | 文件名/相位名不匹配                          | `getOverrideableFile()` 打印实际命中文件（[第 3-5 章](overriding.md)）            |
| 换了 `Cache` 实现但取不到缓存              | `RedisCache` 需要 [`RedisManager`](../reference/Component-RedisManager.md) 也初始化 | 两个组件一起声明（[第 2-13 章](cache.md)）                                         |

## 下一步

- [第 4-2 章 开发组件与扩展](custom-component.md)：写出自己的可替换实现。
- [第 4-5 章 常驻进程与内嵌 HTTP](http-server.md)：系统包装在长跑进程里的意义。
- [第 4-9 章 性能调优与排错手册](troubleshooting.md)：替换没生效时按"症状 → 排查路径"走一遍。
- 参考手册：[DuckPhp\Core\SystemWrapper](../reference/Core-SystemWrapper.md)、[DuckPhp\Core\KernelTrait](../reference/Core-KernelTrait.md)、[DuckPhp\Core\SingletionExTrait](../reference/Core-SingletonExTrait.md)。
