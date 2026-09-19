# 26 应用树与相位基础

> 解决什么问题：一个进程里如何跑起**多个应用**（主应用 + 挂进来的子应用），它们各自的单例、路由、视图怎么互不干扰。
> 前置：[第 8 章 生命周期](lifecycle.md)、[第 14 章 四层架构](layers.md)。预计 15 分钟。
> 本卷全部代码来自 `tests/data_for_tests/ZThirdDemo`，可用 `wsl -e bash -lc "cd /mnt/e/ProjectGoat/DNMVCS && php vendor/bin/phpunit --no-coverage tests/ZThirdDemoTest.php"` 实跑。

## 心智模型：应用树 + 相位

```
MainApp                    相位 ''          ← 根应用（Root）
└── ThirdApp (name=shop)   相位 ':shop'     ← 子应用
```

**相位（Phase）= 实例空间**。`::_()` 取的是**当前相位**里的实例；切相位就换了一整套单例：

```
MainApp 相位          子应用相位 ':shop'
App::_()      →MainApp      App::_()      →ThirdApp
Route::_()    →主应用路由     Route::_()    →子应用路由
ShopBusiness::_() →主应用那份  ShopBusiness::_() →子应用那份
```

两条必须记住的规则：

1. **只影响 `::_()`**。`new SomeClass()` 出来的对象不受相位影响。
2. **相位名不是类名**。根相位是空串 `''`；子相位是 `<父相位>:<子应用 name>`，所以子应用 `name => 'shop'` 的相位名是 **`:shop`**，再下一层是 `:shop:another`。`name` 不写时用子应用的 `namespace`；写 `'@'` 时取类名 basename。

## 最小示例

主应用声明子应用（`ZThirdDemo/src/System/MainApp.php`）：

```php
class MainApp extends DuckPhp
{
    public $options = [
        'path' => __DIR__ . '/../../',
        'namespace' => 'ZThirdDemo',
        'app' => [
            ThirdApp::class => [
                'name' => 'shop',                   // → 相位 ':shop'
                'controller_url_prefix' => 'shop/', // → URL /shop/...
            ],
        ],
    ];
}
```

观察结果：

```php
$app = MainApp::_(new MainApp())->init(['path' => $path]);
App::Phase();                                              // ''      ← 当前在根相位
App::_()->options['app'][ThirdApp::class]['__phase__'];     // ':shop' ← 子应用登记后的相位名
App::_()->options['app'][ThirdApp::class]['controller_url_prefix']; // 'shop/'
```

> 子应用初始化完成后，框架会把它的相位名写回 `options['app'][<类名>]['__phase__']`，这是拿到子应用相位的**正规途径**；不要去拼字符串。

## 相位 API 一览

| 想做什么 | 怎么写 |
|---|---|
| 查当前相位 | `App::Phase()` |
| 切到子应用（并拿到它） | `App::_()->toThisChild(ThirdApp::class)`（不存在返回 `null`） |
| 按类切子相位 | `App::_()->toChildPhase(ThirdApp::class)`（返回 bool） |
| 从子应用切回父 | `ThirdApp::FromCurrentParent()`（不在子相位时返回 `null`） |
| 取根实例并切回根 | `App::Root(true)`；只取值不切相位用 `App::Root()` |
| 重设根相位名 | `App::SwitchRootPhase($phase)` |
| 当前应用的相位名 | `App::_()->getThisPhaseName()` |
| 当前应用的 CLI 命令前缀 | `App::_()->getThisCommandPrefix()`（相位名里的 `/` 换成 `-`） |
| 看容器里都有什么 | `PhaseContainer::_()->dumpAllObject()` |

## 子应用的声明：`app` 选项

`app` 的值是 `[子应用类 => 选项数组]`。**关键点：这个数组会原样传给子应用的 `init()`**，所以父应用可以在这层「不碰子应用代码」地调它（第 30 章正是靠这一点做覆盖）：

```php
ThirdApp::class => [
    'name' => 'shop',                     // 相位名
    'controller_url_prefix' => 'shop/',   // URL 前缀（会与父应用的前缀拼接）
    'controller_resource_prefix' => 'res/',// 资源前缀（第 28 章）
    'local_database' => true,             // 让它用独立的数据库连接
    'database_list' => [['dsn' => 'sqlite:' . __DIR__ . '/shop.db']],
    'controller_class_map' => [           // 换掉它的控制器实现（第 30 章）
        ThirdMainController::class => MyController::class,
    ],
    'ext' => [JsonView::class => true],   // 给它单独加扩展
],
```

写法上的两个便利：

- **简写（mix mode，默认开）**：`'blog' => ['class' => BlogApp::class]` 会把键 `'blog'` 当作 URL 前缀。
- **禁用某个子应用**：值给 `false`（运行期把 `options['app'][类]` 设为 `false` 也可，路由与菜单合并都会跳过它）。

## 子应用的目录与命名空间

子应用是**一个完整的 DuckPHP 应用**，通常自带 `path` 与 `namespace`，可以放在项目的任意目录（`vendor/` 下的第三方包、或项目内的 `third/`）：

```
ZThirdDemo/
├── src/…            ← 主应用（命名空间 ZThirdDemo\…，path = 项目根）
├── view/ config/ res/
└── third/           ← 子应用（命名空间 ZThirdDemo\Third\…，path = third/）
    ├── System/ThirdApp.php
    ├── Controller/ Business/
    ├── view/ config/ res/
```

它的类要能被自动加载。项目里放第三方应用时，除了主命名空间外要给子应用单独加一条映射（`ZThirdDemoTest.php`）：

```php
AutoLoader::_()->init(['path' => $path . 'src/', 'namespace' => 'ZThirdDemo', 'path_namespace' => ''])->run();
AutoLoader::_()->assignPathNamespace($path . 'third/', 'ZThirdDemo\Third');
```

> 换成 Composer 包时这一步不需要：包的 `autoload` 已经把它自己的命名空间注册好了。

## 生命周期：子应用在什么时候初始化

```
MainApp::init()
  ├── onPrepare()
  ├── initComponents()        ← 主应用自己的组件/扩展
  ├── onInit()                ← 主应用「自己的事」做完
  ├── initChildren(options['app'])  ← 逐个 init() 子应用（子应用内部同样跑一遍上面的流程）
  └── onInited()              ← 全部就绪，可以放心访问子应用
```

所以：**要在 `onInited()` 之后再去碰子应用**。在 `onInit()` 里访问子应用会拿到「还没 init」的实例。

## 常见写法

```php
// 1) 临时切到子应用做事，然后切回来
$phase = App::Phase();
App::_()->toThisChild(ThirdApp::class);
$name = ThirdApp::_()->options['shop_name'];   // 子应用相位里的实例
App::Phase($phase);                            // 一定要切回来

// 2) 在子应用内部拿根应用（而不是「当前应用」）
$root = App::Root();           // 只取实例
$root = App::Root(true);       // 取实例并把当前相位切回根

// 3) 在子应用里知道「我是谁」
$name = App::_()->getThisPhaseName();     // 例如 ':shop'
$cmd_prefix = App::_()->getThisCommandPrefix();  // CLI 命令前缀

// 4) CLI：子应用的命令带前缀（phase 名里的 '/' 变成 '-'）
//   php cli.php shop-<命令>     ← 命令组按相位区分，详见第 23 章
```

## 常见错误

| 现象 | 原因 | 改法 |
|---|---|---|
| `App::Phase('ZThirdDemo\Third\System\ThirdApp')` 后行为不变 | 相位名不是类名 | 用 `App::_()->toThisChild(ThirdApp::class)`，或读 `options['app'][类]['__phase__']` |
| 后面所有代码都跑到子应用里去了 | 切了相位没切回来 | 记住 `$phase = App::Phase();` … `App::Phase($phase);` |
| 子应用里 `App::_()` 不是主应用 | 它就是**当前（子）应用** | 要根应用用 `App::Root()` |
| `Call to undefined method ...::getOverridingClass()` | 这是早期版本的 API | 用 `getThisClassName()` / `getThisPhaseName()` |
| 子应用 404、路由前缀重复 | `controller_url_prefix` 少了或多了 `/` | 子应用前缀写 `'shop/'`（带尾斜杠），URL 才是 `/shop/` 与 `/shop/list` |
| 子应用的类找不到 | 没给它的命名空间加自动加载映射 | 见上面的 `assignPathNamespace()`（Composer 包则自带） |

## 常见问题

- **子应用能再挂子应用吗？** 能，`app` 里继续写即可，相位名会变成 `:shop:sub`。
- **视图能共享吗？** 默认各自用各自的 `path_view`；但父应用可以在自己的视图目录里**按子应用 name 建子目录覆盖子应用的视图**（第 30 章）。
- **怎么读主应用的配置？** `App::Root()->options`、`App::_()->getProjectPath()`（见 [参考手册 Core-App](../reference/Core-App.md)）。
- **子应用的异常谁处理？** 统一交给异常管理器（第 19 章），子应用不自己兜底。

## 下一步

- [第 27 章 把外部应用挂进来](mount-app.md)：把上面的机制用在「一个现成的、不是自己写的应用」上。
- 参考手册：[DuckPhp\Core\App](../reference/Core-App.md)、[DuckPhp\Core\KernelTrait](../reference/Core-KernelTrait.md)、[DuckPhp\Core\PhaseContainer](../reference/Core-PhaseContainer.md)
