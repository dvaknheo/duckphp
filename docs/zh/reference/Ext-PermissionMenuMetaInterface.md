# DuckPhp\Ext\PermissionMenuMetaInterface

## 简介

`PermissionMenuMetaInterface` 是「权限菜单元数据」契约接口：实现它的控制器用 **代码** 而不是注释来描述自己的菜单条目，供 [DuckPhp\Ext\PermissionMenu](Ext-PermissionMenu.md) 在 `build()` 时直接取用。

只要控制器有这个接口（准确说：只要有 `__permissionMenuMeta()` 方法且返回非 null 数组），[DuckPhp\Ext\PermissionMenu](Ext-PermissionMenu.md) 就**不再解析**该类上的 `@menu*` 注释——整个类的菜单表由这个方法接管。需要按运行时状态（权限、配置、数据库）动态决定菜单时用它，比注释灵活。

## 类信息

- 命名空间：`DuckPhp\Ext`
- 声明：`interface PermissionMenuMetaInterface`
- 唯一成员是 `__permissionMenuMeta()`，没有常量、没有继承。

## 使用方式

```php
use DuckPhp\Ext\PermissionMenuMetaInterface;
use DuckPhp\GlobalAdmin\AdminControllerInterface;

class AdminController implements AdminControllerInterface, PermissionMenuMetaInterface
{
    public function __permissionMenuMeta(): array
    {
        return [
            // 菜单节点：type 缺省 1
            ['name' => 'Dashboard', 'type' => 1, 'url' => 'Admin/index', 'weight' => 5],
            // 只给名字也行：url 缺省 null、type 缺省 1、weight 缺省 0
            ['name' => 'Reports'],
            // 目录节点：type=0，可带 children
            ['name' => 'System', 'type' => 0, 'url' => 'Admin/#'],
        ];
    }

    public function action_index() { }
}
```

## 注意事项

1. **返回的是条目数组，不是节点树**：每个元素支持 `name`、`url`、`type`、`weight` 四个键，缺省值分别是 `''`、`null`、`1`、`0`；`PermissionMenu` 只做规整，不会替你递归 `children`（要分组请用 `type=0` 的条目并自行安排层级，或改用注释模式）。
2. **接口是「建议」，不是硬性要求**：`PermissionMenu::getClassMenuMeta()` 靠 `method_exists($controller, '__permissionMenuMeta')` 鸭子类型识别——不 `implements` 本接口、只要方法同名一样生效；实现它是为了让契约显式。
3. **实例化方式刻意「不跑构造函数」**：`getClassMenuMeta()` 用 `ReflectionClass::newInstanceWithoutConstructor()` 造实例再调用它——**构造函数不会执行**，方法里不要依赖构造函数初始化过的属性（构建菜单是只读扫描，不该触发控制器构造的副作用）。对比 [CommandMetaInterface](Component-CommandMetaInterface.md) 的命令钩子是用 `new` 的，会跑构造函数。
4. **异常与 null 都算「没有」**：方法抛异常或返回 `null` 时，`PermissionMenu` 静默回退到注释模式（不会把异常抛给调用方）。
5. **接口声明返回 `array`**：返回 `null` 只是「回退注释模式」的约定用法，和接口的类型声明冲突，真要回退就别实现本接口（或让方法干脆不声明返回类型）。
6. 与注释模式的节点不同，元数据条目**不经过** `@menu_directory` 的 `\` 分层与 `@menu_permission` 逻辑，权重排序与 `weight` 剔除仍然照旧。

## 方法列表

### 公共方法

    public function __permissionMenuMeta(): array
返回该控制器的菜单条目数组：每项为 `['name' => …, 'url' => …, 'type' => …, 'weight' => …]`，缺省 `url=null`、`type=1`、`weight=0`。

## 相关链接

- [DuckPhp\Ext\PermissionMenu](Ext-PermissionMenu.md) — 消费方（`getClassMenuMeta()` / `processMenuMetaForController()`）
- [DuckPhp\GlobalAdmin\AdminControllerInterface](GlobalAdmin-AdminControllerInterface.md) — 进菜单还需实现的标记接口
