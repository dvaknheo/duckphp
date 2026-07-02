# DuckPhp\Component\RouteHookResource

路由钩子：静态资源处理。

## 简介

`RouteHookResource` 组件用于处理通过 `controller_resource_prefix` 前缀访问的静态资源文件。它可以直接响应资源文件请求，也可以将资源文件从子应用复制到主应用的 `public` 目录。

该组件默认通过 `DuckPhp\DuckPhp` 的 `ext` 选项自动加载，但只在配置了 `controller_resource_prefix` 时才会注册路由钩子。

## 选项

| 选项 | 默认值 | 说明 |
|---|---|---|
| `path` | `''` | 应用根目录。 |
| `path_resource` | `'res'` | 资源文件目录名。 |
| `path_document` | `'public'` | 文档根目录名。 |
| `controller_url_prefix` | `''` | 控制器 URL 前缀。 |
| `controller_resource_prefix` | `''` | 资源文件 URL 前缀。为空时不启用资源钩子。 |

## 资源文件访问

当请求路径以 `controller_resource_prefix` 开头时，路由钩子会尝试从 `path/path_resource` 目录中查找并返回对应文件：

```php
class App extends DuckPhp
{
    public $options = [
        'controller_resource_prefix' => 'res/',
    ];
}
```

请求 `/res/js/app.js` 会映射到 `path/res/js/app.js`。

## 子应用资源处理

子应用通常有自己的资源文件。`RouteHookResource` 提供了 `cloneResource()` 方法，用于将子应用的资源文件复制到主应用的 `public` 目录：

```php
use DuckPhp\Component\RouteHookResource;

$info = '';
RouteHookResource::_()->cloneResource(false, $info);
echo $info;
```

### 命令行工具

DuckPHP 的命令行工具通常包含资源复制命令：

```bash
php vendor/bin/duckphp resource --app=AdminApp
```

## 安全限制

资源钩子对请求路径有以下安全限制：

1. 禁止 `../` 路径穿越。
2. 禁止访问 `.php` 文件。
3. 文件不存在时返回 `false`，让后续路由或错误处理接管。

## 使用方式

### 在视图模板中引用资源

```html
<script src="/res/js/app.js"></script>
<link rel="stylesheet" href="/res/css/app.css">
```

### 复制资源到 public 目录

```php
$force = false;  // 是否覆盖已有文件
$info = '';
RouteHookResource::_()->cloneResource($force, $info);
```

## 注意事项

1. 生产环境中建议将资源文件直接复制到 Web 服务器目录，而不是通过 PHP 响应。
2. `cloneResource()` 会切换相位到根应用，确保资源目录正确。
3. 如果 `controller_resource_prefix` 是 `http://` 或 `https://` 开头的外部 URL，则不会复制资源。
4. 资源文件响应会自动设置 `Content-Type` 头。

## 全部选项

```php
public $options = [
    'path' => '',
    'path_resource' => 'res',
    'path_document' => 'public',
    'controller_url_prefix' => '',
    'controller_resource_prefix' => '',
];
```

## 方法列表

### 公共方法

    public static function Hook($path_info)
路由钩子入口，内部调用 `_Hook()`

    public function _Hook($path_info)
处理资源文件请求，验证路径并输出文件内容

    public function cloneResource($force = false, &$info = '')
将资源文件复制到文档根目录

### 受保护方法

    protected function initContext(object $context)
如果配置了 `controller_resource_prefix`，则注册 `append-outter` 路由钩子

    protected function get_dest_dir($path_parent, $path)
根据目标路径递归创建目录

    protected function copy_dir($source, $dest, $force = false, &$info = '')
递归复制源目录到目标目录

    protected function check_files_exist($source, $dest, $files, &$info)
检查目标文件是否已存在

    protected function create_directories($dest, $files, &$info)
为要复制的文件创建目标目录

## 相关链接

- [DuckPhp\Core\Route](Core-Route.md)
- [DuckPhp\Core\SystemWrapper](Core-SystemWrapper.md)
