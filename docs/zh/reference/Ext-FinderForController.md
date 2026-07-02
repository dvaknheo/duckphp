# DuckPhp\Ext\FinderForController

> ⚠️ 警告：该扩展是实验性的或已废弃，不建议在新项目中使用。

## 简介

`FinderForController` 是一个用于枚举和发现控制器类的扩展。它根据路由配置扫描控制器目录，生成类到路径信息的映射，并支持获取管理员/用户控制器列表。

该扩展尚未经过完整测试，且源码注释建议改名为 `RouteList`，不建议在新项目中使用。

## 选项

| 选项 | 默认值 | 说明 |
|---|---|---|
| `classes_to_get_controller_path` | `[]` | 用于探测控制器目录的候选类名列表。 |

## 使用方式

### 获取路由路径映射

```php
use DuckPhp\Ext\FinderForController;

$map = FinderForController::_()->getRoutePathInfoMap();
// 返回 [class_name->method => path_info, ...]
```

### 包含子应用的路径映射

```php
$map = FinderForController::_()->getRoutePathInfoMapWithChildren();
```

### 获取所有管理员控制器

```php
$adminControllers = FinderForController::_()->getAllAdminController();
```

### 获取所有用户控制器

```php
$userControllers = FinderForController::_()->getAllUserController();
```

## 配置示例

```php
class App extends DuckPhp
{
    public $options = [
        'ext' => [
            DuckPhp\Ext\FinderForController::class => true,
        ],
        'classes_to_get_controller_path' => [
            'Helper',
            'Base',
        ],
    ];
}
```

## 注意事项

1. 该扩展依赖 `DuckPhp\Core\Route` 的控制器相关配置（如 `controller_class_postfix`、`controller_method_prefix` 等）。
2. 控制器目录通过 `classes_to_get_controller_path` 中的类名反推，如果候选类都不存在，则返回空结果。
3. 生成的路径映射会应用 `controller_class_adjust` 中的调整规则（如 `uc_method`、`uc_class`、`uc_full_class`）。
4. 获取管理员/用户控制器时，分别基于 `DuckPhp\GlobalAdmin\AdminControllerInterface` 和 `DuckPhp\GlobalUser\UserControllerInterface` 过滤。
5. 该扩展尚未经过完整测试，接口可能发生变化。

## 方法列表

### 公共方法

    public function pathInfoFromClassAndMethod($class, $method, $adjuster = null)
根据控制器类名和方法名生成对应的 path_info。如果类/方法不符合路由规则，返回 `null`。

    public function getRoutePathInfoMap($adjuster = null)
扫描所有控制器类，返回 `类->方法` 到 `path_info` 的映射。

    public function getRoutePathInfoMapWithChildren($adjuster = null)
递归扫描当前应用及其子应用的控制器，返回合并后的路径映射。

    public function getAllAdminController()
获取所有实现了 `AdminControllerInterface` 的控制器类名。

    public function getAllUserController()
获取所有实现了 `UserControllerInterface` 的控制器类名。

### 受保护方法

    protected function doControllerClassAdjust($first, $method)
根据 `controller_class_adjust` 选项对控制器类名和方法名进行调整。

    protected function getAllControllerClasses()
扫描控制器目录并返回所有控制器类名及其文件路径。

    protected function getControllerMethods($full_class, $adjuster = null)
获取指定控制器类的所有可路由方法及其 path_info。

## 相关链接

- [DuckPhp\Core\Route](Core-Route.md)
