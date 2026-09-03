# DuckPhp\Ext\Misc

## 简介

`Misc` 收集若干“杂项”工具：外部库文件引入（`Import`）、数据集后处理（`RecordsetUrl`/`RecordsetH`）、迷你 DI（`DI`）、反射式方法调用（`CallAPI`）。每个能力都提供静态便捷入口与实例实现（`_` 前缀方法），便于经组件单例调用。

## 类信息

- 命名空间：`DuckPhp\Ext`
- 声明：`class Misc extends DuckPhp\Core\ComponentBase`

## 选项

| 选项 | 默认值 | 说明 |
|---|---|---|
| `path` | `''` | 项目根路径（拼接相对 `path_lib` 用）。 |
| `path_lib` | `'lib'` | 库目录；以 `/` 开头视为绝对目录，否则拼在 `path` 下。 |

## 使用方式

```php
use DuckPhp\Ext\Misc;

Misc::Import('helper');                 // include_once lib/helper.php
Misc::DI('logger', new Logger());       // 登记
$logger = Misc::DI('logger');           // 读取

$rows = Misc::RecordsetH($rows, ['title']);          // 指定列转义
$rows = Misc::RecordsetUrl($rows, ['url' => '/item/{id}']); // 生成 URL
$ret  = Misc::CallAPI(Service::class, 'doIt', $_POST, SomeInterface::class);
```

## 注意事项

- `_Import`：把 `lib/{file}`（自动补 `.php`）`include_once`。
- `_RecordsetUrl`：`$cols_map` 的值为含 `{列名}` 模板，逐行替换数据后经 `Route::Url` 生成 URL。
- `_RecordsetH`：对指定列（缺省全部列）做 `CoreHelper::H`（HTML 转义）。
- `_CallAPI`：按反射参数名从 `$input` 取值并按 `bool/int/float/string` 类型过滤；缺参/类型不符抛 `ReflectionException`；`$interface` 非空时校验类实现。

## 方法列表

### 公共方法

    public static function Import($file)
静态引入库文件（等价 `_Import`）。

    public static function RecordsetUrl($data, $cols_map = [])
静态：数据集 URL 化（等价 `_RecordsetUrl`）。

    public static function RecordsetH($data, $cols = [])
静态：数据集 HTML 转义（等价 `_RecordsetH`）。

    public static function DI($name, $object = null)
静态：迷你 DI 读写（等价 `_DI`）。

    public function CallAPI($class, $method, $input, $interface = '')
反射式调用（等价 `_CallAPI`）。

    public function _DI(string $name, $object = null)
迷你 DI：传对象为写、不传为读。

    public function _Import(string $file): void
引入 `lib/{file}.php`。

    public function _RecordsetUrl($data, $cols_map = [])
按模板替换并 `Route::Url` 生成 URL 列。

    public function _RecordsetH($data, $cols = [])
对指定列做 HTML 转义。

    public function _CallAPI($class, $method, $input, $interface = '')
反射调用类方法（类型过滤/缺参检查）。

## 相关链接

- [DuckPhp\Core\Route](Core-Route.md) — Url 生成
- [DuckPhp\Core\CoreHelper](Core-CoreHelper.md) — H() 转义
