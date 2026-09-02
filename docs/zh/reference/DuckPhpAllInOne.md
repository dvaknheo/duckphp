# DuckPhp\DuckPhpAllInOne

把 App、路由入口、动作、可调用视图、以及四组业务 Helper 全并入一个类，适合演示、脚手架或极小型单文件应用。

## 简介

`DuckPhpAllInOne extends DuckPhp`，是“整个应用只用一个类”版本的入口：

- 直接把应用的 action（`action_*`）、视图回调（`view_*`）、欢迎动作与 CLI 自注册放进**同一个类**里写；
- `use` 了四个 Helper Trait（`ModelHelperTrait`、`BusinessHelperTrait`、`ControllerHelperTrait`、`AppHelperTrait`），让你在动作里能直接用 `Setting()`、`Db()`、`Session`、`URL`/显示等一系列静态/实例便捷方法；
- 因为 4 个 Trait 存在同名方法，类里用 trait 别名 + `insteadof` 把冲突固定为合理实现（详见「类信息」）；
- 该类不新增 options 数组，而是在编译/运行时由父 `DuckPhp::$common_options` 叠加 `embedMe()` 注入键、并在 `onInited()` 依据是否要头尾 wrap 做设定。

适用对象：想用“一个文件解释整个 demo 应用”时的教程/乐园写法（见 README 的 sample1），或特小型原型。较大项目更倾向于继承 `DuckPhp`、配合 `Foundation/*` 分层。

行为速览：类被解析为自控制器；URL 根路径渲染 `action_index`，调 `view_index` 显示 → 依据 `instanceof`？——不会。它把解析出欢迎动作即自身；显示经 `view_index()`/`view_head()`/`view_foot()` 拼装。

## 类信息

- 命名空间：`DuckPhp`
- 声明：`class DuckPhpAllInOne extends DuckPhp`
- 使用 Trait：`ModelHelperTrait`、`BusinessHelperTrait`、`ControllerHelperTrait`、`AppHelperTrait`

由于 4 个组上一起有同名方法，构造性冲突用 trait 的 `insteadof` 规则判定（规则正文引用中未处理的部分）：

```php
use ModelHelperTrait;
use BusinessHelperTrait, ControllerHelperTrait, AppHelperTrait {
    BusinessHelperTrait::Setting        insteadof ControllerHelperTrait;
    BusinessHelperTrait::Options        insteadof ControllerHelperTrait;
    BusinessHelperTrait::Config         insteadof ControllerHelperTrait;
    BusinessHelperTrait::XpCall         insteadof ControllerHelperTrait;
    BusinessHelperTrait::FireGlobalEvent insteadof ControllerHelperTrait;
    BusinessHelperTrait::OnGlobalEvent  insteadof ControllerHelperTrait;
    BusinessHelperTrait::OnGlobalEvent  insteadof AppHelperTrait;
    BusinessHelperTrait::FireGlobalEvent insteadof AppHelperTrait;
    ControllerHelperTrait::header       insteadof AppHelperTrait;
    ControllerHelperTrait::setcookie    insteadof AppHelperTrait;
    ControllerHelperTrait::exit         insteadof AppHelperTrait;
    ControllerHelperTrait::AdminService insteadof BusinessHelperTrait;
    ControllerHelperTrait::UserService  insteadof BusinessHelperTrait;
}
```

另有 `protected $head_view='head'`、`protected $foot_view='foot'`，作为 `_Show()` 包头脚模板名。

## 运行时由 embedMe / onInited 使用的键

本类**没有**自有 `$options=[]`；下列几个开关是 embedMe 在构造函数里 `array_merge` 进 options，或在 onInited 读取：

| 键 / 用途 | 写入/用途点 | 默认/行为 |
|---|---|---|
| `namespace_controller` | embedMe | 解析为本类短名所在的命名空间（带前导 `\\`）。让 controller 即本项目 namespace。 |
| `name` | embedMe | `'@'`，Phase 名用类名处理。 |
| `controller_welcome_class` | embedMe | 本类 `static::class`（欢迎/根动作就是它自己）。 |
| `controller_class_postfix` | embedMe | `''`（本类名已含 Controller 无 Postfix 追加）。 |
| `controller_method_prefix` | embedMe | `'action_'`。 |
| `cli_enable` | embedMe | true。 |
| `path_info_compact_enable` | embedMe | true（URL 紧凑映射）。 |
| `duckphp_all_in_one_wrap_header_foot` | embedMe/onInited | true；为真时 head_view/foot_view 会参与 `_Show` 拼装。 |
| head_view / foot_view | `onInited()` 依据 wrap 设置 | `'head'`/`'foot'`。 |
| cmd | `onPrepare()` | 把 `static::class` 及（若 cli_command_with_common）`DuckPhp\Component\Command` 都登记为命令入口。 |

> 其它所有可行选项（db/redis/路由重写……）来自父 `DuckPhp::$common_options` + Kernel `kernel_options`：见 `DuckPhp.md` / `Core-KernelTrait.md`。

## 使用方式

```php
use DuckPhp\DuckPhpAllInOne;

class MyApi extends DuckPhpAllInOne {
    public $options = [
        'path' => __DIR__,
        'is_debug' => false,
    ];

    // action（动作）
    public function action_hello() { $this->_Show(['m'=>'Hi'], 'hello'); }

    // 视图回调
    public function view_hello($data) { echo 'hello '.__h($data['m']); }
}
MyApi::RunQuickly([]);
```

因此 `view_head()/view_index()/view_foot()` 类内置模板可用，但你不必按它们写：在子类定义 `view_{name}` 即为 `name` 视图回调。

## 配置示例

```php
class Tiny extends \DuckPhp\DuckPhpAllInOne {
    public $options = [
        'namespace'  => 'Tiny',
        'path'       => __DIR__.'/..',
        'use_user_view' => true,
    ];
    // 可选 action 与 view_* …
}
Tiny::RunQuickly([]);
```

注意：类里写方法以 `action_` 开头即收进“可路由动作”；方法体内要访问业务数据直接用合并进的 helper（`Db()/Setting()/…` 同继承可用）。

## 注意事项

1. DB / Setting / Session 之类的便捷来自于一次性 used 的 4 个 Helper Trait 合成，其中同名近义方法由一列 `insteadof` 决定实现；改写一个方法需要覆写本类、而不是 `use` 那四个。
2. 欢迎类就是 `static::class`：路由内部把空 URL 当作调用本类的 `action_index`。
3. `duckphp_all_in_one_wrap_header_foot=false` 时，`_Show` 仍会匹配一个无 head/foot 的直接回调。
4. 全类不新增 options：想要完整通用配置，仍落在父类一层（下链参考）。

## 可用显示用内置视图方法（作为模板示意）

`view_head($data)` 输出 `<html>…<body>`、`view_foot($data)` 输出 `</body></html>`；欢迎首页 `view_index($data)` 打印 “`类名` main page work at…”——这些可在子类以同名方法覆盖。

## 方法列表

> 只列 `DuckPhpAllInOne.php` 中本类 override/新增方法；父 `DuckPhp`/`Core` shell（RunQuickly、Setting 等）见各自文档，不重复。

### 公共方法

    public function __construct()
先 call embedMe() 注入默认（欢迎类=本类/action_/wrap 等），再 parent::__construct()

    public function onInited(): void
若 duckphp_all_in_one_wrap_header_foot 为真 → 把 head_view='head'、foot_view='foot' 生效（否则 不包）

    public function action_index()
根动作：默认把当前可见变量作 data 渲染 'index' 视图：

    public function _Show(array $data, string $view = '')
视图框口：views 转成类方法回调 view_;匹配失败则让 parent 继续；否则按 head/call/foot 顺序输出

    public function view_head($data)
内置页头字符串（<html><head>…<body>）

    public function view_index($data)
内置欢迎正文：打印 类名 main page …，加实现时间用于扫视图

    public function view_foot($data)
内置页尾（</body></html>）

### 受保护方法

    protected function embedMe(): void
构造即注入 ext_options（namespace_controller\\后 namespace/name @/welcome=本 class/clear postfix/method action_/cli/path compact/wrap），然后 merg 进 options

    protected function onPrepare(): void
父 prepare 之后，把 static::class（与按 cli 可开）与 Command 登记进 options[cmd]

    protected function viewToCallback(?string $func): ?\Closure
把视图名（可含 `/`→`_`）变成 [$this,'view_'+…]，检 is_callable，可则封装为 Closure

## 相关链接

- [DuckPhp\DuckPhp](DuckPhp.md) — 父类；通用 options/组件装配从这里来
- [DuckPhp\Core\App](Core-App.md) / [Core-KernelTrait](Core-KernelTrait.md)
- Helper traits：`AppHelperTrait`(Helper-AppHelperTrait.md)、`ControllerHelperTrait`、`BusinessHelperTrait`、`ModelHelperTrait`
- [DuckPhp\Ext\CallableView](Ext-CallableView.md)（本类把同级能力以 view_ 内建实现）
- guide：[quickstart](../guide/quickstart.md)、[layers](../guide/layers.md)
