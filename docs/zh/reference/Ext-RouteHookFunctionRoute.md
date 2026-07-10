# DuckPhp\Ext\RouteHookFunctionRoute

函数路由钩子扩展。

## 简介

`RouteHookFunctionRoute` 是一个路由钩子，允许将 URL 路径直接映射为全局函数或命名空间函数。当路由找不到控制器时，会尝试调用以 `function_route_method_prefix` 为前缀的函数。

## 选项

| 选项 | 默认值 | 说明 |
|---|---|---|
| `function_route` | `false` | 是否启用函数路由。 |
| `function_route_method_prefix` | `'action_'` | 函数名前缀。 |
| `function_route_404_to_index` | `false` | 为 `true` 时，找不到函数则回退到 `action_index`。 |

## 使用方式

### 作为 DuckPhp 扩展加载

```php
class App extends DuckPhp
{
    public $options = [
        'ext' => [
            \DuckPhp\Ext\RouteHookFunctionRoute::class => true,
        ],
        'function_route' => true,
        'function_route_method_prefix' => 'action_',
        'function_route_404_to_index' => true,
    ];
}
```

### 定义函数

```php
function action_user_profile()
{
    echo 'User Profile';
}
```

访问 `/user/profile` 时会调用 `action_user_profile`。

### 表单 POST 支持

当请求为 POST 时，`Route` 的 `controller_prefix_post` 会作为额外前缀拼接，例如 `action_post_user_profile`。

## 配置示例

```php
class App extends DuckPhp
{
    public $options = [
        'ext' => [
            \DuckPhp\Ext\RouteHookFunctionRoute::class => true,
        ],
        'function_route' => true,
        'function_route_method_prefix' => 'do_',
        'function_route_404_to_index' => false,
    ];
}
```

## 注意事项

1. 初始化时会注册 `append-inner` 路由钩子。
2. 路径中的 `/` 会被替换为 `_`，因此多级路径对应下划线分隔的函数名。
3. 空路径会映射为 `index`。
4. 函数不存在时返回 `false`；若开启 `function_route_404_to_index`，则尝试调用 `action_index`。

## 全部选项

```php
    public $options = [
        'function_route' => false,
        'function_route_method_prefix' => 'action_',
        'function_route_404_to_index' => false,
    ];
```

## 方法列表

### 公共方法

    public static function Hook($path_info)
路由钩子入口。

    public function _Hook($path_info = '/')
解析路径并尝试调用对应函数。

### 私有方法

    private function runCallback($callback)
如果回调可调用则执行，并返回 `true`；否则返回 `false`。

## 相关链接

- [DuckPhp\Core\ComponentBase](Core-ComponentBase.md)
- [DuckPhp\Core\Route](Core-Route.md)
