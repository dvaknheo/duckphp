# DuckPhp\Ext\RouteLister

路由枚举：把当前应用的路由集合（Rewrite / important 地图 / 控制器自动路由 / 普通地图，以及子应用）整理成统一记录集 —— 供权限导出、路由展示（如 CLI `routes`）使用。

## 简介

`RouteLister extends ComponentBase` 提供“把系统能答应的 URL 全列出来”的能力，输出一行行如下结构：

```php
['url','phase','controller','method',
 'is_admin','is_user',
 'route_map','route_map_important','rewrite_map'];
```

- 顺序固定：rewrite_map → route_map_important → 控制器方法 → route_map；
- 控制器 URL 由「查配置文件目录下所有控制器类 + 反射公共方法」反推（见 `pathInfoFromClassAndMethod`）；
- 分别带 admin/user 标记；可用 `only_controller/only_admin/only_user` 过滤；
- `with_children` 开时递归第子应用（children）各行，phase 也各自正确浮现。

## 类信息

- 命名空间：`DuckPhp\Ext`
- 声明：`class RouteLister extends ComponentBase`
- CLI 展示由本类的 `command_routes()` 实现（把路由表打印成带高亮的彩行），`DuckPhp\Component\Command` 的 CLI 命令转调它。

## 选项

`RouteLister::$options`：

| 选项 | 默认 | 说明 |
|---|---|---|
| `classes_to_get_controller_path` | `[]` | 额外“待尝试的类/控制器文件”候选：仅用于**寻找控制器目录**（同 welcome。config path；缺文件会继续下一个），被找到后再递归枚举其下 .php 判定 Controller）。 |

补充：它不承担“哪些方法被路由”（判断基于 controller_class_postfix/method_prefix, controller_class_base 检查依 Route），它只是索引其 path；欢迎/Helper/Base也被试优先定位目录。

## 使用方式

```php
use DuckPhp\Ext\RouteLister;
$rows = RouteLister::_()->listAll();
// CLI: 彩色输出用 RouteLister::_()->command_routes()。
$rows_admin = RouteLister::_()->listAll(true, true, true, false);  // 仅 admin 控制器
```

## 生成 URL 的方式

控制器的“一行 url”由 `pathInfoFromClassAndMethod(全名, 动作名)` 反推：类 postfix、方法 prefix 处理后，首部分=`namespace_controller…/…`，method 尾段 URL 前缀并补 method/欢迎，返回 url 字符串（含 ext & url_prefix）。（方法内部还支持 route `controller_class_adjust` 的逆向还原。）

## 注意事项

- 需要控制器目录能反射到文件名（真实类已 autoload）走反射；失败返回 null 的行会跳过。
- 过滤 only_admin/only_user 不能同时 true（抛 `InvalidArgumentException`）。
- list 结果中 controller rows 总是存在（忽略 only_controller 只给 rows），而 route 三块（map/…）会被 only_controller 隐跳过。

## 方法列表

### 公共方法

    public function command_routes(bool $with_children = true, bool $only_controller = false, bool $only_admin = false, bool $only_user = false): void
把 `listAll()` 的结果按 URL/controller/route-map/admin-user/phase 分块、带颜色打印到命令行（`Command` 的 `command_routes` 命令转调它）。

    public function pathInfoFromClassAndMethod($class, $method, $adjuster = null)
根据 控制器全名+方法 → 该路由可写 URL（或欢迎/欢迎方法特殊短文/空 return prefix）。实现去反向 controller_class_adjust。

    public function listAll(bool $with_children = true,
                   bool $only_controller = false,
                   bool $only_admin = false,
                   bool $only_user = false): array
核心：组装 rewrite_important 路由/rows+控制器方法（可滤 admin/user），再普通 route_map；如 with_children 递归附加子应用记录。

### 受保护方法

    protected function doControllerClassAdjust(string $first, string $method): array
还原路径段：uc_method/uc_class(lcfirst of last)/uc_full_class（各段 lcfirst）等。

    protected function getControllerPathByApp($prefix)
优先用 App 类文件位置推算控制器目录（`$prefix` 以 `\\` 开头则返回 null；目录不存在返回 null）。

    protected function getControllerPathByDetected($prefix)
回退探测：用 welcome class / `Helper` / `Base` 等候选类反射定位控制器目录（失败返回 null）。

    protected function getAllControllerClasses(): array
用候选类定 controller 目录（先 `getControllerPathByApp`、后 `getControllerPathByDetected`），递归扫目录中 `.php`（去 postfix）返回键 class=>absfile 映射。

    protected function getControllerMethods(string $full_class, ?callable $adjuster = null): array
反射 public 非 static 非构造方法，可逆成 URL 并跳过 pathInfo 返回 null 的。

    protected function listControllerRows(bool $only_admin, bool $only_user): array
遍历 classes 过滤 接口归属后，为每个方法合成一行记录。

    protected function parseRouteMapCallback(string $callback): array
把 route_map 目标 `~Class@method`/`Class@method` 拆 [class,method]。

    protected function isSubclassOf(string $class, string $interface): bool
反射判断是否实现接口子类（捕获反射异常→false）。

## 相关链接

- 命令侧输出者 [DuckPhp\Component\Command](Component-Command.md)
- 数据源两个路由地图组件：Rewrite / RouteMap（见本目录参考）
- 用户/管理判定：GlobalUser / GlobalAdmin interfaces
