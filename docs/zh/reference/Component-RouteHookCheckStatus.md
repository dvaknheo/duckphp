# DuckPhp\Component\RouteHookCheckStatus

路由钩子：状态检查。

## 简介

`RouteHookCheckStatus` 是一个路由钩子组件，在请求进入路由匹配之前检查应用状态。主要用于处理维护模式（maintain）和未安装状态（need install）。

该组件默认通过 `DuckPhp\DuckPhp` 的 `ext` 选项自动加载，挂在 `prepend-outter` 位置。

## 选项

| 选项 | 默认值 | 说明 |
|---|---|---|
| `error_maintain` | `null` | 维护模式错误处理。可以是视图模板路径或回调函数。 |
| `error_need_install` | `null` | 未安装状态错误处理。可以是视图模板路径或回调函数。 |

## 使用方式

### 维护模式

当 `duckphp_is_maintain` 在 `DuckPhpSettings.config.php` 中设置为 `true`，或者应用选项 `is_maintain` 为 `true` 时，会触发维护模式：

```php
// config/DuckPhpSettings.config.php
return [
    'duckphp_is_maintain' => true,
];
```

### 未安装模式

当应用选项 `need_install` 为 `true` 且 `isInstalled()` 返回 `false` 时，会触发未安装提示：

```php
class App extends DuckPhp
{
    public $options = [
        'need_install' => true,
        'error_need_install' => 'need_install',  // 使用 view/need_install.php 模板
    ];
}
```

### 自定义错误处理

`error_maintain` 和 `error_need_install` 支持三种形式：

1. 字符串：作为视图模板路径，通过 `View::Show()` 渲染
2. 可调用对象：执行回调函数，由回调负责输出响应
3. 空：显示默认的占位提示信息

```php
class App extends DuckPhp
{
    public $options = [
        'error_maintain' => function () {
            header('HTTP/1.1 503 Service Unavailable');
            echo '系统维护中，请稍后访问';
        },
    ];
}
```

## 注意事项

1. 该钩子挂在 `prepend-outter` 位置，在所有路由匹配之前执行。
2. 如果触发了维护或未安装状态，钩子返回 `true`，表示请求已被处理，后续路由流程不再执行。
3. 使用视图模板时，会临时初始化一个 `View` 组件来渲染。
4. 默认的占位提示信息比较简陋，生产环境建议配置自定义模板或回调。

## 全部选项

```php
public $options = [
    'error_maintain' => null,
    'error_need_install' => null,
];
```

## 方法列表

### 公共方法

    public static function Hook($path_info)
路由钩子入口，内部调用 `doHook()`

    public function doHook($path_info)
检查维护状态和未安装状态，必要时输出响应并返回 `true`

### 受保护方法

    protected function initContext(object $context): void
将 `Hook` 方法注册到 `prepend-outter` 路由钩子位置

    protected function showMaintain(): void
输出默认维护模式提示

    protected function showNeedInstall(): void
输出默认未安装提示

## 相关链接

- [DuckPhp\Core\Route](Core-Route.md)
- [DuckPhp\Core\View](Core-View.md)
