# DuckPhp\Core\AutoLoader

PSR-4 风格的简化自动加载器（不依赖 Composer），供应用在框架启动前加载自己的命名空间与类文件。

## 简介

`Core\AutoLoader` 提供一套很轻的“命名空间 → 目录”自动加载：把一个前缀映射到某个目录，之后遇到该前缀下的类就按 PSR-4 规则去对应子路径找同名 `.php` 并 `include_once`。

- 支持把 `DuckPhp\` 本身映射到 `src/`，使框架核心无需 `autoload.php` 也能用（另一精简回调 `DuckPhpSystemAutoLoader` 专做此活）。
- 支持一组 `path=>prefix` 的映射（实例 `namespacePaths`）、PSR-4 数组（`psr-4`，这里参数是 namespace=>path）、以及相对/绝对目录。
- 不带 Composer 的哲学下，框架常自己启动它而不是依赖 vendor 加载。

它**不继承 `ComponentBase`**，因为它要在应用/上下文还没建立前就先可用；故以独立 `_()` + `init/run` 自己管理单例、初始与启用。

## 类信息

- 命名空间：`DuckPhp\Core`
- 声明：`class AutoLoader`（非 ComponentBase 子类）
- 关键属性：`public $options`；`public $namespace_paths`（加载映射）；`protected $is_inited / $is_running`。

## 选项

`AutoLoader::$options`（默认值见“全部选项”）：

| 选项 | 默认值 | 说明 |
|---|---|---|
| `path` | `''` | 项目根目录。缺省尝试 `realpath(getcwd().'/../')`。用于把相对路径归一。 |
| `namespace` | `''` | 应用主命名空间（如 `App`）。非空且 `skip_app_autoload` 为假时，会把 `namespace` 指到 `path_namespace`。 |
| `path_namespace` | `'app'` | 应用目录（主命名空间对应目录），可绝对；相对则相对 `path`。默认把 `app/` → `${namespace}\`。 |
| `skip_app_autoload` | `false` | 为真则不再自动把主 namespace 映射到 app 目录。 |
| `autoload_cache_in_cli` | `false` | 为真时 CLI 模式 `run()` 里会先 `cacheClasses()`（opcache 预编译）。 |
| `autoload_path_namespace_map` | `[]` | 额外 `path => namespace` 映射（不会翻转方向）。 |
| `psr-4` | `[]` | PSR-4 风格映射：`namespace => path`（初始化时翻转成 path=>namespace 装入 `namespace_paths`）。 |

## 使用方式

普通项目启动（等价于一个应用根能自动加载自己源码）：

```php
\DuckPhp\Core\AutoLoader::RunQuickly([
    'path'           => __DIR__ . '/..',
    'namespace'      => 'MyApp',
    'path_namespace' => 'app',          // 把 MyApp\ 指到 <root>/app
]);
```

之后引用 `MyApp\Controller\Home` 时，会尝试 `include <root>/app/Controller/Home.php`。

### 添加一个命名空间（常见）

```php
use DuckPhp\Core\AutoLoader;

// 方式一：静态便捷（注意参数是 namespace => path）
AutoLoader::addPsr4('Vendor\\', __DIR__ . '/vendor');

// 方式二：实例映射（手动）
AutoLoader::_()->assignPathNamespace(['/abs/src/' => 'Vendor\\src\\']);
```

### 框架核心无 vendor 起跑

框架或不带 Composer 的场景，可只注册精简回调：

```php
spl_autoload_register([\DuckPhp\Core\AutoLoader::class, 'DuckPhpSystemAutoLoader']);
```

`DuckPhpSystemAutoLoader($class)` 只处理 `DuckPhp\` 前缀 → 对应 `src/.../...php`（普通类在 vendor/autoload 场景未必需要它）。

## 配置示例

完整可作为 composer 替代的最小例子：见 `RunQuickly` 调用。若为插件库加入自己的命名空间：

```php
AutoLoader::_()->init(['path' => __DIR__])->assignPathNamespace([
    '/vendor/mylib/' => 'Mylib\\',
]);
AutoLoader::_()->run();   // 注册 spl_autoload_register
```

## 注意事项

1. 它不是 `ComponentBase` 子类：要在应用还没起来就启动；别假设它有 `PhaseContainer`。
2. 同一文件名会被 `include_once`，类重复定义以首次生效，手动 include 其他副本需小心。
3. `namespace_paths` 的实际键是“目录”，值是“前缀”；写入时若用 `psr-4`(namespace=>path) 会被 `array_flip` 后再入。
4. `addPsr4` 签名是 `namespace => path`，内部翻转成内部表示。
5. `autoload_cache_in_cli` 只预编译（`opcache_compile_file`），不做“收录为需 include”行为，非 CLI 用 `cacheNamespacePath/cacheClasses` 手动亦可。
6. `clear()` 会 `spl_autoload_unregister` 该回调，可在‘结束使用后’调用。

## 全部选项

```php
    public $options = [
        'path' => '',
        'namespace' => '',
        'path_namespace' => 'app',
        'skip_app_autoload' => false,

        'autoload_cache_in_cli' => false,
        'autoload_path_namespace_map' => [],
        'psr-4' => [],
    ];
```

## 方法列表

> 源码中自带方法与（若需可注册的）静态回调用法统一如下。可见字段：静态便捷 `_/RunQuickly/addPsr4`、自动回调 `AutoLoad/DuckPhpSystemAutoLoader`、实例方法 init/run/等。

### 公共静态方法

    public static function _($object = null)
获取该 AutoLoader 单例；传入对象可对当前类登记/替换实例。

    public static function RunQuickly(array $options = [])
`static::_()->init($options)->run()`：把 初始化与注册 一气合成。

    public static function addPsr4($namespace, $input_path = null)
按 `namespace => path` 添加 PSR-4 映射；多个命名空间可传数组（值=路径）。内部翻转成 path=>prefix。

### 公共方法

    public function __construct()
空构造。

    public function init(array $options, ?object $context = null)
初始化：幂等（已含先返回自身）；补/归一 path，设 namespace/path_namespace。skip=false 且给 namespace 时 regist app dir→namespace；并合入 autoload_path_namespace_map 与翻转后 psr-4。

    public function isInited(): bool
是否已 did init。

    public function run()
注册自动加载到 `spl_autoload_register([static::class,'AutoLoad'])`；`autoload_cache_in_cli` 时先 cacheClasses；防重复（is_running）。

    public function runAutoLoader()
`run()` 的代理（个别书写偏好）。

    public static function AutoLoad(string $class): void
spl 回调外壳：转发给 `_Autoload($class)`。

    public function _Autoload(string $class): void
按 `namespace_paths` 前缀匹配，把余下段转成 相对文件路径并 `include_once`（找不到静默返回）。

    public function assignPathNamespace($input_path, $namespace = null)
向 `namespace_paths` 合并增加记录：支持 `path=>namespace` 数组或 单 path+namespace 形式；path 归一以 `/` 结尾、namespace 以 `\` 结尾。

    public function cacheClasses()
遍历全部 `namespace_paths` 的目录（absolute）并把其下 .php 逐个 `opcache_compile_file` 预编译；返回收集到的文件。

    public function cacheNamespacePath($path)
对单一目录递归取文件并预编译（同上语义，单目录版）。

    public function clear(): void
从 PHP 自动加载栈注销 `[AutoLoader,'AutoLoad']` 回调。

    public static function DuckPhpSystemAutoLoader(string $class): void
独立精简回调：只处理 `DuckPhp\` 前缀，映射到 `<framework>/src/<…>.php` 后 include_once（供框架无 vendor 自加载）。

    public function slashDir($path)
把路径尾部统一成目录分隔符结尾（`''` 原样返回，否则 `rtrim('/\\')` 后补 `DIRECTORY_SEPARATOR`）。

### 受保护方法

    protected function isAbsPath($path)
判断路径是否为绝对路径：`/` 开头、盘符（如 `C:\`、`C:/`）、或 `\\` 开头；参数可为 null。

    protected function getNamespacePath(string $sub_path, string $main_path): string
把相对/绝对子路径解析为目录路径：相对则基于 main_path 返回绝对并补目录分隔符。

## 相关链接

- [DuckPhp\Core\App](Core-App.md) 应用入口（对 Composer/autoload 的上层策略）
- composer.json 的 `autoload psr-4` 段 — 由 Composer 场景下的自动加载写照
- [Core-CoreHelper](Core-CoreHelper.md)（若涉及类名查找对象）
