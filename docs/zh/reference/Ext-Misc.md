# DuckPhp\Ext\Misc

杂项工具扩展组件。

## 简介

`Misc` 扩展提供了一组常用的静态辅助方法，用于导入库文件、数据集处理（URL 转换与 HTML 转义）、依赖注入容器和反射式 API 调用。这些方法在控制器和视图中比较常见。


## 选项

| 选项 | 默认值 | 说明 |
|---|---|---|
| `path` | `''` | 项目根路径。 |
| `path_lib` | `'lib'` | 外部库文件目录，相对于 `path`。 |

## 使用方式

### 导入外部库文件

```php
use DuckPhp\Ext\Misc;

Misc::Import('Util');  // 导入 lib/Util.php
```

导入路径规则：

- `path_lib` 以 `/` 开头时视为绝对路径
- 否则相对于 `path` 拼接

### 数据集 URL 转换

对二维数组的指定列生成 URL：

```php
use DuckPhp\Ext\Misc;

$data = [
    ['id' => 1, 'name' => 'A'],
    ['id' => 2, 'name' => 'B'],
];

$ret = Misc::RecordsetUrl($data, ['url' => 'user/detail/{id}']);
// $ret[0]['url'] 为 /user/detail/1
```

### 数据集 HTML 转义

```php
use DuckPhp\Ext\Misc;

$data = [
    ['name' => '<script>', 'title' => 'A'],
];

$ret = Misc::RecordsetH($data, ['name']);
// name 列被 HTML 转义
```

### 依赖注入容器

```php
use DuckPhp\Ext\Misc;

$service = new MyService();
Misc::DI('my_service', $service);

$service = Misc::DI('my_service');
```

### 反射式 API 调用

```php
use DuckPhp\Ext\Misc;

$ret = Misc::_()->CallAPI(MyService::class, 'doSomething', ['id' => 1]);
```

`_CallAPI` 会根据方法参数反射，从 `$input` 中按参数名取值，并校验基础类型（`bool`、`int`、`float`、`string`）。

## 配置示例

```php
class App extends DuckPhp
{
    public $options = [
        'path' => __DIR__ . '/../',
        'path_lib' => 'lib',
    ];
}
```

## 注意事项

1. `Import()` 用于引入项目自定义的库文件，不是 Composer 自动加载的一部分。
2. `RecordsetUrl()` 和 `RecordsetH()` 主要用于处理需要在模板中循环输出的数据集。
3. `DI()` 是一个简单的名称-对象映射容器，不是完整的依赖注入容器。
4. `_CallAPI()` 会对基础类型做简单校验，但不会对复杂对象类型校验。

## 全部选项

```php
public $options = [
    'path' => '',
    'path_lib' => 'lib',
];
```

## 方法列表

### 公共方法

    public static function Import($file)
导入 `path_lib` 目录下的 PHP 文件

    public static function RecordsetUrl($data, $cols_map = [])
将数据集中的指定列转换为 URL

    public static function RecordsetH($data, $cols = [])
对数据集中的指定列进行 HTML 转义

    public static function DI($name, $object = null)
依赖注入容器：设置或获取对象

    public function CallAPI($class, $method, $input, $interface = '')
反射式调用类方法，按参数名从输入数组取值

### 实例方法

    public function _DI(string $name, $object = null)
DI 容器内部实现

    public function _Import(string $file): void
导入文件内部实现

    public function _RecordsetUrl($data, $cols_map = [])
数据集 URL 转换内部实现

    public function _RecordsetH($data, $cols = [])
数据集 HTML 转义内部实现

    public function _CallAPI($class, $method, $input, $interface = '')
反射式 API 调用内部实现

## 相关链接

- [DuckPhp\Core\ComponentBase](Core-ComponentBase.md)
- [DuckPhp\Core\CoreHelper](Core-CoreHelper.md)
- [DuckPhp\Core\Route](Core-Route.md)
