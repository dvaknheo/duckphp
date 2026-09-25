# 4-1 容器与相位内部机制

> 解决什么问题：`ClassName::_()` 拿到的实例到底存在哪、按什么规则找；多应用/多相位下实例为什么「同名不同物」；出问题时怎么看容器里有什么、怎么排查。
> 前置：[第 3-1 章 应用树与相位基础](advanced-phase.md)、[第 3-4 章 组件共享与应用间通信](component-sharing.md)。预计 20 分钟。
> 本章示例来自 `tests/data_for_tests/ZThirdDemo`，可用 `wsl -e bash -lc "php vendor/bin/phpunit --no-coverage tests/ZThirdDemoTest.php"` 实跑（**在仓库根目录下**跑）。

## 最小示例

`ZThirdDemoTest.php` 里这条断言就是本章机制的缩影：

```php
$root_logger = spl_object_id(Logger::_());
$root_lang   = spl_object_id(Lang::_());
App::_()->toThisChild(ThirdApp::class);        // 切到子应用相位 ':shop'
spl_object_id(Logger::_()) === $root_logger;  // true  → 共享实例
spl_object_id(Lang::_())   !== $root_lang;    // true  → 各相位一份
App::Phase('');                                // 切回根相位
```

同一个 [`Logger::_()`](../reference/Core-Logger.md)、同一个 [`Lang::_()`](../reference/Component-Lang.md)，为什么一个到处相同、一个随相位而变？答案全在 [`DuckPhp\Core\PhaseContainer`](../reference/Core-PhaseContainer.md) 这一个类里（参考手册 Core-PhaseContainer）。

## 机制说明

### 容器只有一个，按相位分桶

整个进程里 `PhaseContainer` 是全局唯一的（`PhaseContainer::$instance`）。它内部有三样东西（`src/Core/PhaseContainer.php` 13-16 行）：

```php
public $containers = [];   // 所有桶：containers[相位名][类名] = 实例
public $current = '';      // 当前相位（桶名）
public $default = '';      // 「公共桶」名，根应用初始化后是 '#public'
public $publics = [];      // 标记为 public 的类名表
```

「相位」就是 `$containers` 的一个键。根相位是空串 `''`；子应用是 `:shop` 这类名字（第 3-1 章）。`#public` 也是一个桶名——**共享实例并不放在某个相位里，而是放在这个专门的公共桶里**。

### 实例怎么被存和取：`_GetObject()` 三步

`::_()` 最终都走到 `PhaseContainer::_()->_GetObject($class, $object)`（`src/Core/PhaseContainer.php` 42-58 行）：

```
1. 在当前相位桶里找 $class → 命中就返回（传了 $object 则先替换再返回）
2. 该类标记为 public（isset($this->publics[$class])）→ 去公共桶（$this->default，即 '#public'）找
3. 都没有 → new $class()，存进「第 2 步选定的桶」（public 类进 '#public'，其余进当前相位桶），返回
```

所以一条规则覆盖全部情况：**非 public 类按当前相位各存一份；public 类全进程只有一份，存在 `#public` 桶里**。`Lang` 属于前者，`Logger`/[`Console`](../reference/Core-Console.md)/[`DbManager`](../reference/Component-DbManager.md) 属于后者——共享与否不取决于类写在哪，而取决于它有没有被 `addPublicClasses()` 标记（见下）。

### 谁被标成 public：装配时决定

标记发生在应用初始化时（`src/Core/KernelTrait.php` 325-346 行、`src/DuckPhp.php` 109-127 行）：

- 根应用的 `initComponents()` 把 `Console` 以 `EXT_FOLLOW_APP` 交给 `initComponentsOfRoot()`，后者先 `addPublicClasses()` 再初始化——**凡走 `initComponentsOfRoot()` 的类都被标为 public**。
- [`DuckPhp::initComponentsOfRoot()`](../reference/DuckPhp.md) 在此基础上又并入 `DbManager`/[`RedisManager`](../reference/Component-RedisManager.md)（`EXT_DEFAULT`）与 [`GlobalAdmin`](../reference/GlobalAdmin-GlobalAdmin.md)/[`GlobalUser`](../reference/GlobalUser-GlobalUser.md)/[`GlobalEvent`](../reference/Component-GlobalEvent.md)（默认 `EXT_DISABLE`，打开后也是 public）。
- 每个应用自己的 [`Route`](../reference/Core-Route.md) 走 `initComponentsOfInner()`，**不标 public**——所以每个相位有自己的路由表。
- `options['ext']` 里的扩展走 `initComponentsOfExt()`，同样不标 public——默认各相位一份。

### 可变单例：`::_()` 与 `::_($new)`

[`ComponentBase`](../reference/Core-ComponentBase.md) 使用 [`SingletonExTrait`](../reference/Core-SingletonExTrait.md)，其 `::_()` 只有一行（`src/Core/SingletonExTrait.php` 16-19 行）：

```php
public static function _($object = null)
{
    return PhaseContainer::GetObject(static::class, $object);
}
```

- `::_()` 不带参：按上面三步取实例。
- `::_($object)` 带参：**把 `$object` 塞进对应桶**（已存在则替换），并返回它。这就是「可变单例」——实例不是写死的，谁都能换。框架自己就这么干：`JsonView::init()` 里 `View::_(static::_())` 用自己的实例替换了 `View` 单例（`src/Ext/JsonView.php` 34 行）。

[`Foundation\SingletonTrait`](../reference/Foundation-SingletonTrait.md) 只是 `SingletonExTrait` 的别名（`src/Foundation/SingletonTrait.php` 全文就是 `use SingletonExTrait`），两者完全等价；工程四层基类用的是前者，框架组件用的是后者，语义一模一样。

### 相位名规则与切换 API

| 规则 | 说明 |
|---|---|
| 根相位 | 空串 `''`（[`KernelTrait::$ROOT_PHASE`](../reference/Core-KernelTrait.md)，`src/Core/KernelTrait.php` 69 行） |
| 公共桶名 | `'#public'`（`$ROOT_PHASE_OF_SHARED`，70 行）；`SwitchRootPhase($p)` 会把它改成 `$p.'#public'`（131-139 行） |
| 子相位名 | `<父相位>:<name>`，`name` 取子应用选项 `name`，未写时取 `namespace`，写 `'@'` 时取类名 basename（`initContainer()`，`src/Core/KernelTrait.php` 243-249 行） |
| 同名相位冲突 | 子应用相位名已被占用时抛 [`DuckPhpSystemException`](../reference/Core-DuckPhpSystemException.md)，提示改 `name` 选项（251-256 行） |

切换入口（都在 `KernelTrait`）：[`App::Phase($new)`](../reference/Core-App.md) 切桶并返回旧相位（165-175 行）；`App::Root(true)` 取根实例并把相位切回根（104-111 行）；`toThisChild($class)` 按 `options['app'][类]['__phase__']` 切到子相位并返回该子应用（203-212 行）；`SwitchRootPhase()` 用于嵌套场景里重设「谁是根」（131-139 行）。

### 测试辅助：`RestAllContainerForTesting()`

`PhaseContainer::RestAllContainerForTesting()`（`src/Core/PhaseContainer.php` 33-36 行）把整个容器换成一个全新的——测试套件每个用例开头调用它，保证单例不跨用例泄漏。写自己的多应用测试时照抄这个模式即可（第 2-17 章）。

## 常见写法

```php
// 1) 看容器里现在有什么（排错第一招）：直接打印全部桶
PhaseContainer::Dump();        // 静态便捷方法，等价于 PhaseContainer::_()->dumpAllObject()

// 2) 判断某类是不是 public（决定它会不会跨相位共享）
$is_public = isset(PhaseContainer::_()->publics[Logger::class]);

// 3) 子应用误拿了根应用的实例？给它建一个本相位的局部实例
$this->createLocalObject(DbManager::class);   // 框架对 local_database 就是这么做的
//    （src/DuckPhp.php 140-145 行：createLocalObject 后在当前相位重新 init）

// 4) 手动换某个组件的单例（替换框架行为的底层手段，第 4-3 章展开）
View::_(new MyView())->init(App::_()->options, App::_());

// 5) 彻底清空重来（测试用）
PhaseContainer::RestAllContainerForTesting();
```

`dumpAllObject()` 的输出格式（`src/Core/PhaseContainer.php` 124-154 行）：先打印 `current`/`default`，再列出 publics 表，然后**逐桶**列出每个实例——public 的类名前带 `*`，类名与实际对象类不一致时括号标出真实类（例如 [`DuckPhp\Core\View (DuckPhp\Ext\CallableView)`](../reference/Ext-CallableView.md)，说明 [View](../reference/Core-View.md) 单例被替换过了）。`tests/data_for_tests/ZAllDemoTest-10360.txt` 就是一份真实 dump，可直接对照。

## 常见错误

| 现象 | 原因 | 改法 |
|---|---|---|
| 子应用里 `Xxx::_()` 拿到的是主应用的实例 | 该类被标为 public，实例放在 `#public` 桶 | 这是设计；要隔离就用 `createLocalObject()`（或 `local_database`/`local_redis` 开关） |
| 两个应用「共享」的组件状态互相覆盖 | 共享实例是**同一个对象**，选项也是一份 | 改选项前先想清楚是不是该 `createLocalObject()` 各用各的 |
| 切了相位后面代码全跑错应用 | `App::Phase($new)` 后没切回来 | `$old = App::Phase($new); ... App::Phase($old);` |
| dump 里某实例的类名带括号 `(OtherClass)` | 该单例被 `::_(新对象)` 替换过 | 正常（如 [JsonView](../reference/Ext-JsonView.md) 替换 View）；排查替换来源（第 4-3 章） |
| 子应用初始化报 `Phase Short name ... is used by ...` | 两个子应用 `name`（或 namespace）相同，相位名撞车 | 给其中一个显式写不同的 `'name'` 选项 |
| 测试里单例状态串到下一个用例 | 没重置容器 | 用例开头 `PhaseContainer::RestAllContainerForTesting()` |

## 下一步

- [第 4-2 章 开发组件与扩展](custom-component.md)：组件怎么声明选项、初始化、挂进路由——本章的容器规则就是它们的运行环境。
- [第 4-3 章 替换框架行为](replace-behavior.md)：`::_(新实例)`、`system_wrapper_replace()` 等替换手段的系统化。
- 参考手册：[DuckPhp\Core\PhaseContainer](../reference/Core-PhaseContainer.md)、[DuckPhp\Core\ComponentBase](../reference/Core-ComponentBase.md)、[DuckPhp\Core\SingletonExTrait](../reference/Core-SingletonExTrait.md)、[DuckPhp\Core\KernelTrait](../reference/Core-KernelTrait.md)
