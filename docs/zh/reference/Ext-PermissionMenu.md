# DuckPhp\Ext\PermissionMenu

## 简介

`PermissionMenu` 是「后台权限菜单树」构建器：它用 [DuckPhp\Component\RouteLister](Component-RouteLister.md) 扫出**后台控制器**的路由，再按控制器类/方法上的注释（或类自己提供的元数据）整理成多层菜单树，供管理后台渲染侧边栏与权限点。

三种用法：

- **注释模式**：在控制器类/方法上写 `@menu_directory`、`@menu`、`@menu_action`、`@menu_permission` 等注释，`build()` 解析成菜单树；
- **元数据模式**：控制器实现 [DuckPhp\Ext\PermissionMenuMetaInterface](Ext-PermissionMenuMetaInterface.md)（`__permissionMenuMeta()` 返回数组），该类的注释不再解析，整表由它接管；
- **落盘模式**：`buildAndSaveToConfigJsonFile()` 把树写成配置文件，`loadAdminPermissionMenu()` 再读回来，避免每次请求都扫路由 + 反射。

它还与 `App::Phase()` / [DuckPhp\Core\PhaseContainer](Core-PhaseContainer.md) 协作：`loadAll()` 会跨根应用与各子应用把菜单合并成一棵整树。

## 类信息

- 命名空间：`DuckPhp\Ext`
- 声明：`class PermissionMenu extends DuckPhp\Core\ComponentBase`
- 本类**没有自己的 `$options`**（沿用 `ComponentBase` 的空表），因此本文没有「选项」章节：它读的是**所属 App 的选项** `permission_menu_tree_for_admin` 与 `controller_url_prefix`（另有 `app`、`path_config` 等由 App 自身消费）。
- `_()` / `init()` / `context()` 等单例与初始化能力由 `ComponentBase` 提供，本文不重复列出。

## 使用方式

```php
// 1) 由注释重建菜单并落盘（后台「重建菜单」按钮、CLI 命令里跑一次即可）
\DuckPhp\Ext\PermissionMenu::_()->buildAndSaveToConfigJsonFile();

// 2) 运行时取树：配了菜单文件就读文件（并补挂载前缀），没配就即时 build
$tree = \DuckPhp\Ext\PermissionMenu::_()->loadAdminPermissionMenu();
$tree = \DuckPhp\Ext\PermissionMenu::_()->loadAdminPermissionMenu(true); // 强制即时 build

// 3) 连子应用一起合并（在某个子应用 Phase 下调用也安全，返回前会还原 Phase）
$all = \DuckPhp\Ext\PermissionMenu::_()->loadAll();

// 4) 只要「侧边栏」形态（丢掉动作/权限点与空目录）
$side = \DuckPhp\Ext\PermissionMenu::_()->permissionMenuTreeToSideMenuTree($all);
```

注释模式的最小写法：

```php
use DuckPhp\GlobalAdmin\AdminControllerInterface;

/**
 * @menu_directory Admin\System
 * @menu_directory_url admin/system
 * @menu_icon fa fa-folder
 * @menu_weight 10
 */
class AdminController implements AdminControllerInterface
{
    /**
     * @menu Dashboard Panel
     * @menu_icon home
     * @menu_weight 5
     */
    public function action_index() { }

    /**
     * @menu_action User List
     * @menu_directory Users
     * @menu_directory_url users
     */
    public function action_list() { }

    /**
     * @menu_permission #edit Edit User
     * @menu_permission /abs/place Abs Place
     */
    public function action_edit() { }

    /** 没写注释的方法也会成为 type=2 的动作节点，名字就是方法名 */
    public function action_plain() { }
}
```

元数据模式：

```php
use DuckPhp\Ext\PermissionMenuMetaInterface;

class MetaController implements PermissionMenuMetaInterface
{
    public function __permissionMenuMeta(): array
    {
        return [
            ['name' => 'Meta Menu', 'type' => 1, 'url' => 'Meta/index', 'weight' => 3],
            ['name' => 'Meta Action'],   // type 缺省 1、url 缺省 null、weight 缺省 0
        ];
    }
}
```

## 配置示例

```php
class MyApp extends \DuckPhp\DuckPhp
{
    public $options = [
        'namespace' => 'MyProj',

        // 菜单树配置文件（相对 path_config，缺省 config/）：
        // .json 走 json_decode，其它后缀当 php 文件 include（须 return 数组）
        // 不设置则每次 loadAdminPermissionMenu() 都即时 build
        'permission_menu_tree_for_admin' => 'menu.json',

        'ext' => [
            // 可选：登记为扩展；本类没有自己的选项，不登记也能直接 PermissionMenu::_() 用
            \DuckPhp\Ext\PermissionMenu::class => true,
        ],
    ];
}
```

> `permission_menu_tree_for_admin` 是 **App 的选项**（本类自己 `$options` 为空），必须像上面这样在应用类里声明，运行时再往 `App::_()->options` 里塞是无效的。

## 注释一览

| 注释 | 可写位置 | 作用 |
|---|---|---|
| `@menu_directory Name` | 类 / 方法 | 目录名；支持 `\` 分层（值取到行末，允许空格） |
| `@menu_directory_url Url` | 类 / 方法 | 该目录节点自己的 url；类级缺省为「首个方法 url 的目录部分 + `/#`」 |
| `@menu_icon Icon` | 类 / 方法 | 图标名（值取到行末，`fa fa-folder` 这类带空格的值也可） |
| `@menu_weight N` | 类 / 方法 | 同层排序权重（大者靠前，可为负数），输出前会被剔除 |
| `@menu Name` | 方法 | 菜单节点，`type=1` |
| `@menu_action Name` | 方法 | 动作节点，`type=2` |
| `@menu_permission #url Name` | 方法 | 权限点，`type=3`；`#` 开头则拼在方法 url 之后，否则按给定 url |

节点类型：`0` = 目录、`1` = 菜单、`2` = 动作、`3` = 权限点。

## 注意事项

1. **只收后台控制器**：`getRoutes()` 走 `RouteLister::listAll(false, true, true)`（等价于 `only_admin`），所以控制器必须 `implements AdminControllerInterface`，普通控制器不会出现在菜单里。
2. **两种模式互斥**：控制器只要有 `__permissionMenuMeta()` 且返回非 null 数组，注释就完全不解析；该方法抛异常或返回 null 时**回退注释模式**（用「无构造实例化」调用它，所以别在里面依赖构造好的属性）。
3. **注释值取到行末并 trim**：`@menu_action User List` 的名字就是 `User List`；值里不要再塞第二个参数。类级 `@menu_directory` 与 `@menu_directory_url` 是两个独立 tag，不会互相误吞。
4. **未加注释的方法**也会成为 `type=2` 动作，名字就是方法名（含 `controller_method_prefix`，如 `action_orders`），且权重被强制归 0。
5. **`\` 分层与合并**：`@menu_directory Admin\System` 会拆成 `Admin/` → `System/` 两级，叶子名只取最后一段；同名同层目录会**合并成一个节点**（合并时以先创建者的 `url`/`icon` 为准）。方法级 `@menu_directory` 生成的分组目录挂在**顶层**，不在所在控制器的目录里。
6. **`@menu_directory_url` 去重**：同一个目标目录出现多条时**第一条生效**。
7. **权重只作用于同层**，`sortTree()` 之后每层按 `weight` 降序重排，并把 `weight` 字段从输出里删掉。
8. **url 的前缀**：`loadAdminPermissionMenu()` 读配置文件时会用 `resolveUrls($tree, '/'.controller_url_prefix)` 给**所有节点**补挂载前缀；而 `build()` / `loadAdminPermissionMenu(true)` 产出的是**相对 url**，需要时自己调 `resolveUrls()`（`buildAndSaveToConfigJsonFile()` 正是落盘相对 url，方便以后换前缀）。
9. **`loadAll()` 的 Phase 语义**：先加载**当前 Phase** 的菜单，再回到根 Phase 递归合并各子应用，最后还原原来的 Phase。从**根** Phase 调用时根菜单只加载一次（不会重复）；从**子** Phase 调用时该子应用自身不重复加载，但它的子应用仍会被合并进来。`app` 里切不进去的项（如值为 `false`、或没登记过 `__phase__`）会被跳过。
10. **权限点没有图标**（`icon` 恒为 `null`）；`permissionMenuTreeToSideMenuTree()` 会丢掉 `type > 1`（动作与权限点）以及**没有子节点的空目录**，`type` 缺省按目录（`0`）处理。
11. `recordsetToTree()` / `treeToRecordset()` 与菜单本身无关，是配套的「扁平表 ↔ 树」工具：缺 `id` 字段的记录会被丢掉，`pid` 指向不存在的 `id` 时该记录当根节点处理。
12. `getClassDoc()` 里对 `ReflectionClass` 的那个 `catch (\Throwable)` 是不可达的防御分支（前面已经 `class_exists()` 判过），不影响行为。

## 方法列表

### 公共方法

    public function buildAndSaveToConfigJsonFile()
由路由即时构建菜单树（url 已裁掉 `controller_url_prefix`，为相对路径），以 JSON 写入 `permission_menu_tree_for_admin` 指定的配置文件；没配该选项时什么都不做。

    public function loadAdminPermissionMenu(bool $force_build = false)
取后台菜单树：`$force_build` 为真则即时 `build()` 并直接返回；否则读菜单配置文件（`.json` 走 `json_decode`，其它后缀当 php 文件 include，非数组退化成空树），最后用 `resolveUrls()` 补上挂载前缀。

    public function loadAll(bool $force_build = false)
取「整棵应用树」的菜单：先取当前 Phase 的菜单，再切回根 Phase 递归合并各子应用（跳过当前 Phase 自身以免重复），最后还原原 Phase。

    public function build(array $routes): array
核心方法：把路由行按控制器分组，逐个解析 `__permissionMenuMeta()` 或注释，生成「目录 → 菜单/动作/权限点」节点，再做 `\` 分层拆分与同层权重排序。

    public function resolveUrls(array &$tree, string $prefix): array
给树里每个节点的 `url` 加上挂载前缀（原地修改并返回同一棵树），缺 `url` 的节点按空串处理。

    public function walkTree(array &$nodes, callable $callback): array
深度优先遍历整棵树，对每个节点调用 `$callback(&$node, $depth)`（根节点 depth=0，回调可直接改节点），原地修改并返回同一棵树。

    public function permissionMenuTreeToSideMenuTree(array $nodes): array
转成侧边栏菜单树：递归过滤掉 `type > 1`（动作/权限点）与没有子节点的空目录，保留 `name`/`url`/`icon`/`type` 与 `children`。

    public function recordsetToTree(array $recordset, string $idField = 'id', string $pidField = 'pid', int $rootPid = 0): array
把扁平记录集转成树：按 `id` 建索引，`pid` 等于 `$rootPid` 或指向不存在的 `id` 时作为根，缺 `id` 的记录被丢弃。

    public function treeToRecordset(array $tree, string $idField = 'id', string $pidField = 'pid', int $rootPid = 0): array
把树摊平成记录集：每个节点去掉 `children` 并补写 `pid`（根节点的 `pid` 为 `$rootPid`）。

### 受保护方法

    protected function getMenuJsonFileConfig()
读 `App::_()->options['permission_menu_tree_for_admin']`，未设置返回 `null`。

    protected function mergeAppsMenus(array &$tree, string $ignore_phase, bool $force_build): void
递归合并应用树的菜单：当前 Phase 与 `$ignore_phase` 不同才加载本应用菜单并入 `$tree`，然后逐个切到子应用递归，每次回到本 Phase。

    protected function getRoutes(bool $trim_url = false)
取 `RouteLister::listAll(false, true, true)`；`$trim_url` 为真时按 `App::_()->options['controller_url_prefix']` 的长度把 url 裁成相对路径。

    protected function getClassMenuMeta(string $controller): ?array
控制器若有 `__permissionMenuMeta()` 就无构造实例化并调用它；类不存在、没有该方法、抛异常或返回 null 时返回 `null`（表示回退注释模式）。

    protected function processMenuMetaForController(array $meta, array $methods): array
把 `__permissionMenuMeta()` 的条目规整成节点：`name`、`url`（缺省 `null`）、`type`（缺省 `1`）、`weight`（缺省 `0`）。

    protected function collectMethodItems(string $controller, string $method, string $url): array
解析单个方法的注释：定节点名与 `type`（`@menu` → 1，`@menu_action` → 2，都没有则用方法名且权重归 0），带上 `directory`/`directory_url`/`icon`/`weight`，并把多条 `@menu_permission` 追加成 `type=3` 节点。

    protected function parseMultiAnnotatedLine(string $doc, string $tag): array
解析可重复出现的注释（如多条 `@menu_permission`）：每行拆成「第一个 token」与「行末余下并 trim」两段，逐行返回。

    protected function splitSubLevels(array $nodes): array
后处理第一遍：清掉子节点的临时字段（`directory`/`directory_url`），把方法级 `directory` 的分组变成目录节点，并按名字里的 `\` 拆分合并进树。

    protected function mergeNode(array &$tree, array $parts, array $node): void
按 `$parts` 路径把节点合并进树：同层已有同名目录则并入其 `children`（只并子节点），否则逐级新建目录；作为叶子时名字只取最后一段。

    protected function sortTree(array &$nodes): void
后处理第二遍：同层按 `weight` 降序 `uasort` 后重排索引，递归处理子层，并在输出前删除 `weight` 字段。

    protected function getClassDoc(string $class): string
读类 docblock；类名为空或类不存在时返回 `''`。

    protected function getMethodDoc(string $class, string $method): string
读方法 docblock；类名为空、类不存在或方法不存在时返回 `''`。

    protected function parseAnnotatedLine(string $doc, string $tag): ?string
解析单个注释：值取到行末并 `trim`，注释缺失或值为空时返回 `null`。

    protected function parseWeight(string $doc): int
解析 `@menu_weight N`（支持负数），没有该注释时返回 `0`。

    protected function walkTreeRecursive(array &$nodes, callable $callback, int $depth): void
`walkTree()` 的递归实现：先对节点回调，再带着 `depth + 1` 处理 `children`。

    protected function treeToRecordsetRecursive(array $nodes, array &$recordset, string $idField, string $pidField, int $pid): void
`treeToRecordset()` 的递归实现：写出当前节点（`pid` 用传入值），再以本节点 `id` 为 `pid` 处理子层。

## 相关链接

- [DuckPhp\Component\RouteLister](Component-RouteLister.md) — 路由来源（`listAll()`）
- [DuckPhp\Ext\PermissionMenuMetaInterface](Ext-PermissionMenuMetaInterface.md) — 元数据模式接口
- [DuckPhp\GlobalAdmin\AdminControllerInterface](GlobalAdmin-AdminControllerInterface.md) — 会被收进菜单的后台控制器标记接口
- [DuckPhp\Core\App](Core-App.md) — `getConfigFile()` 与 options 来源
- [DuckPhp\Core\PhaseContainer](Core-PhaseContainer.md) — `loadAll()` 依赖的 Phase 机制
- guide：[advanced-phase](../guide/advanced-phase.md)
