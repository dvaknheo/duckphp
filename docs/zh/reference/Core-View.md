# DuckPhp\Core\View

视图组件：负责把控制器/逻辑要展示的数据渲染成输出（模板 include），支持头/脚包裹、数据委托与“渲染成字符串”拾取。

## 简介

`View` 是 DuckPHP 的默认视图实现（`class View extends ComponentBase`）。它不依赖任何模板引擎，而是直接用 PHP 模板文件（include `.php`）渲染 —— 这类文件就是我们通常说的 `view/*.php`。

核心能力：

- 把一段数据 `extract()` 成“局部变量作用出来”，再 `include` 视图(以及可选页眉页脚)输出；
- 渲染接口三兄弟 `Show`(立即输出) `Display`(输出指定模板) `Render`(捕获 → 字符串) —— 每个都有静态壳（入 Controller/Helper 里调 `View::Show(...)` 等最容易用）；
- 数据相关：`assignViewData()`/`getViewData()`，可把数据事先预分配到实例的上下文中；
- 视图文件定位基于 `options[path]/options[path_view]` + `getOverrideableFile()`（支持 Phase 级覆盖子目录）。

通常不做单根 `View()`;只需在 `<controller>_Show(...)`、或全局 `View::_()->_Show(...)`/static 壳里指定“视图名/文件”即可。它也在 App/DuckPhpAllInOne 里被包装成更上层的 `_Show`。

## 类信息

- 命名空间：`DuckPhp\Core`
- 声明：`class View extends ComponentBase`
- 常用获取：`View::_()` 返回当前应用 Phase 的实例；或者用对称静态壳 `View::Show/Display/Render`。
- 公共属性：`$options`；`$data`（当前预分配数据）。

## 选项

`View::$options`：(默认值)

| 选项 | 默认值 | 说明 |
|---|---|---|
| `path` | `''` | 项目根路径（组件层对“相对路径”的基准）。 |
| `path_view` | `'view'` | 视图目录相对名（相对 `path`；可绝对则用之）。`getViewFile()` 用它拼 `{$path}/{$path_view}/{$file}.php`。 |
| `view_skip_notice_error` | `true` | 渲染时是否临时屏蔽 `E_NOTICE` 噪声。为 true 时 `_Show` 会临时去掉 E_NOTICE 再到结束恢复。 |

## 使用方式

### 直接渲染输出（最常见）

```php
use DuckPhp\Core\View;

// 视图名（相对 view/ 不带 .php）
View::Show(['user' => $user], 'user/profile');
```

等价于静态转发：

```php
View::_()->_Show(['msg'=>'hi'], 'welcome');   // 输出 welcome.php
View::Display('welcome', ['x'=>1]);           // 同位置的一次输出（带可选 data）
$html = View::Render('mail/body', ['order'=>$o]); // 捕获到变量
```

### 预分配数据 + 取回

```php
View::_()->assignViewData('shop_name', 'DemoShop');
View::_()->assignViewData(['title'=>'首页', 'extra'=>1]);  // 数组批量
$viewData = View::_()->getViewData();
```

> `_Show()` 会 array_merge 自带的 `$data` 与已 assign 的数据后 `extract` —— 对应 view 文件内这些名字就是变量。

### 页眉页脚包裹

```php
View::_()->setViewHeaderFooter('_layout/head', '_layout/foot');
View::_()->_Show(['name'=>'D'], 'another'); // 页眉、主体、页脚依次输出
```

注意 `_Show` 用传入（对象级的 `$header_file`/`$footer_file`）默认取当前设置；不设时只有主体。

#### reset

处理完一个周期后调用 `View::_()->reset()` 清除页眉页脚/data/view 状态，便于每个请求/测试零状态。

## 配置示例

```php
// config (通常在 app options)
$viewOptions = [
    'path_view' => 'view',           // view/ 
];
```

例：`View::Show(['count'=>$n],'list')` 会尝试 include `<root>/view/list.php`，模板内直接 `$count` 用之。

## 注意事项

1. 视图文件若不存在并不会报错——include 抛 warning/视 engine 而定；建议结构上放好 .php。
2. 自定义 `path_view` 允许多层路径 `mails/digest`。
3. 输出即回显：要点不 echo View 值。若需拿到字符串用 `Render`（内部 ob 捕获）。
4. 静态壳 `Show/Display/Render` 与实例 `_Show/_Display/_Render` 一一对应；壳总是作用于当前 Phase 实例。

## 全部选项

```php
    public $options = [
        'path' => '',
        'path_view' => 'view',
        'view_skip_notice_error' => true,
    ];
```

## 方法列表

> 源码定义的全部 public/protected（无 static label 时说明 instance member）。static/none static 混排按源码顺序给出，并在壳上注明“静态”。

### 公共方法

    public static function Show(array $data = [], ?string $view = null)
静态壳 → `static::_()->_Show($data,$view)`；最常用渲染入口（立即输出）

    public static function Display(string $view, ?array $data = null): void
静态壳 → `static::_()->_Display(...)`

    public static function Render(string $view, ?array $data = null): string
静态壳：渲染并捕获返回字符串

    public function _Show(array $data, string $view)
(核心) 若 view_skip_notice_error 临时降 E_NOTICE；解析 view/页眉/页脚文件、合并数据并 extract；按 页眉 → 主view → 页脚 顺序 include 输出；渲染后恢复 reporting 边界

    public function _Display(string $view, ?array $data = null): void
单文件输出指定模板（合并好 data,排除 'this' 键）并 include

    public function _Render(string $view, ?array $data = null): string
捕获"输出"为字符串：ob start→ _Display → ob 取内容 → 结束

    public function reset()
重置实例：清空页眉页脚/view 与临时文件/旧 error level，让每个周期零状态

    public function getViewData(): array
返回当前已 assign 数据数组

    public function setViewHeaderFooter(?string $header_file, ?string $footer_file): void
设置渲染包裹用的页眉/页脚模板（视图名；`_Show()` 时按 头 → 主体 → 尾 依次输出）

    public function assignViewData($key, $value = null): void
预分配变量：数组($value null)整体 merge，或单项 $key=>$value

### 受保护方法

    protected function getViewFile(?string $view): string
把视图名补成 `<path>/<path_view>/<name>.php`（`.php` 已带时不加）返回绝对；空则空串

## 相关链接

- [DuckPhp\Core\ComponentBase](Core-ComponentBase.md) — 组件 init
- [DuckPhp\Core\App](Core-App.md) — host 层 `_Show` 视图入口会调用 View
- [DuckPhp\Ext\CallableView](Ext-CallableView.md)、[DuckPhp\Ext\JsonView](Ext-JsonView.md) — 其它视图风格组件
- 层级解释：[Foundation\Controller](Foundation-Controller-Base.md) ; 页面 shell 见 DuckPhpAllInOne 的 view_header/view_footer
- guide：[layers](../guide/layers.md)
