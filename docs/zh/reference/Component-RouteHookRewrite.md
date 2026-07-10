# DuckPhp\Component\RouteHookRewrite

路由钩子：URL 重写。

## 简介

`RouteHookRewrite` 组件提供 URL 重写功能。它允许你将某些 URL 路径映射为其他路径，支持精确匹配和正则表达式匹配。重写后的 URL 会继续参与后续路由匹配。

该组件默认通过 `DuckPhp\DuckPhp` 的 `ext` 选项自动加载，挂在 `prepend-outter` 位置。

## 选项

| 选项 | 默认值 | 说明 |
|---|---|---|
| `controller_url_prefix` | `''` | 控制器 URL 前缀。重写只对匹配该前缀的 URL 生效。 |
| `rewrite_map` | `[]` | URL 重写规则映射。 |

## 重写规则格式

`rewrite_map` 是一个关联数组，键是模板 URL，值是目标 URL：

```php
class App extends DuckPhp
{
    public $options = [
        'rewrite_map' => [
            'old/page' => 'new/page',
            '~about/(.+)' => 'article/detail/$1',
        ],
    ];
}
```

### 精确匹配

键不以 `~` 开头，表示精确匹配 URL 路径：

```php
'old/page' => 'new/page',
```

- `/old/page` → `/new/page`

### 正则匹配

键以 `~` 开头，表示正则表达式匹配：

```php
'~about/(.+)' => 'article/detail/$1',
```

- `/about/company` → `/article/detail/company`

正则表达式使用 `~` 作为分隔符，匹配的是 URL 路径（不含查询参数）。

## 运行时添加规则

通过 `assignRewrite()` 方法可以在运行时添加或修改重写规则：

```php
use DuckPhp\Component\RouteHookRewrite;

RouteHookRewrite::_()->assignRewrite('old/page', 'new/page');

// 批量添加
RouteHookRewrite::_()->assignRewrite([
    'old/page' => 'new/page',
    'legacy/url' => 'modern/url',
]);
```

## 获取当前规则

```php
$rules = RouteHookRewrite::_()->getRewrites();
```

## 使用场景

### 旧 URL 兼容

```php
'class-name' => 'ClassName',
'user/profile' => 'account/profile',
```

### 伪静态

```php
'~article/(\d+)' => 'article/detail?id=$1',
```

访问 `/article/123` 会被重写为 `/article/detail?id=123`。

## 注意事项

1. 重写钩子挂在 `prepend-outter` 位置，在路由匹配之前执行。
2. 重写只影响路径部分和查询参数，不会改变请求方法或 HTTP 头。
3. 如果 URL 不匹配 `controller_url_prefix` 前缀，重写不会生效。
4. 正则重写中，`$1`、`$2` 等反向引用会被替换为对应的匹配组。
5. 重写后的 URL 会重新设置 `$_GET` 和 `PATH_INFO`，供后续路由组件使用。

## 全部选项

```php
public $options = [
    'controller_url_prefix' => '',
    'rewrite_map' => [],
];
```

## 方法列表

### 公共方法

    public static function Hook($path_info)
路由钩子入口，内部调用 `doHook()`

    public function assignRewrite($key, $value = null)
添加单条或多条重写规则

    public function getRewrites(): array
获取当前所有重写规则

    public function replaceRegexUrl($input_url, $template_url, $new_url)
使用正则表达式替换 URL

    public function replaceNormalUrl($input_url, $template_url, $new_url)
使用精确匹配替换 URL

    public function filteRewrite($input_url)
依次尝试所有重写规则，返回重写后的 URL 或 `null`

### 受保护方法

    protected function initOptions(array $options): void
合并 `rewrite_map` 选项

    protected function initContext(object $context): void
将 `Hook` 方法注册到 `prepend-outter` 路由钩子位置

    protected function changeRouteUrl(string $url): void
根据重写后的 URL 更新 `$_GET` 和 `PATH_INFO`

    protected function doHook(string $path_info): ?bool
执行重写逻辑

## 相关链接

- [DuckPhp\Core\Route](Core-Route.md)
- [DuckPhp\Component\RouteHookRouteMap](Component-RouteHookRouteMap.md)
