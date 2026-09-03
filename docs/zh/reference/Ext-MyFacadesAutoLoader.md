# DuckPhp\Ext\MyFacadesAutoLoader

## 简介

`MyFacadesAutoLoader` 实现“Facade（门面）命名空间自动加载”：当代码访问 `facades_namespace`（默认 `MyFacades\`）下的某个未定义类，或命中 `facades_map` 中的键时，组件动态 `eval` 生成一个继承 `MyFacadesBase` 的类，使该类的任意静态调用能经 `MyFacadesBase::__callStatic` 转给真实类。

用法上：把某实现类注册到 `facades_map`（如 `MyFacades\Foo => RealFoo::class`），或直接把真实类名放在 `MyFacades\` 前缀下，即可用门面方式静态调用。

## 类信息

- 命名空间：`DuckPhp\Ext`
- 声明：`class MyFacadesAutoLoader extends DuckPhp\Core\ComponentBase`

## 选项

| 选项 | 默认值 | 说明 |
|---|---|---|
| `facades_namespace` | `'MyFacades'` | 门面类命名空间（会 trim 反斜杠）。 |
| `facades_map` | `[]` | 门面类名 → 真实类名的映射。 |
| `facades_enable_autoload` | `true` | 是否注册 `spl_autoload`（关闭则需自行触发）。 |

## 使用方式

```php
\DuckPhp\Ext\MyFacadesAutoLoader::_()->init([
    'facades_namespace' => 'MyFacades',
    'facades_map' => ['MyFacades\User' => \MyProject\UserService::class],
], $app);

// 之后静态调用会被转发到 UserService::_() 实例的对应方法：
MyFacades\User::getById(1);
```

## 注意事项

- `_autoload()`：类名以 `MyFacades\` 开头或位于 `facades_map` 键时，生成 `class X extends \DuckPhp\Ext\MyFacadesBase {}`（`eval` 方式）。
- `getFacadesCallback($input_class,$name)`：先在 `facades_map` 精确匹配，否则按前缀剥离得到真实类；真实类需可 `_()`（`is_callable([$class,'_'])`），返回 `[$object,$name]`。
- `clear()`：清空 map 并注销 autoload。

## 方法列表

### 公共方法

    public function _autoload($class): void
自动加载钩子：为门面命名空间动态生成门面类。

    public function getFacadesCallback(string $input_class, string $name): ?array
解析门面静态调用目标：返回 `[真实对象, 方法名]` 或 `null`。

    public function clear(): void
清空映射并注销自动加载。

### 受保护方法

    protected function initOptions(array $options): void
初始化前缀、映射；按选项注册 `spl_autoload`。

## 相关链接

- [DuckPhp\Ext\MyFacadesBase](Ext-MyFacadesBase.md) — 门面基类（__callStatic 入口）
- [DuckPhp\Core\ComponentBase](Core-ComponentBase.md) — 组件基类
