# 30 重写与覆盖

> 解决什么问题：被挂进来的应用**一行代码都不改**，怎么换掉它的视图、配置、资源、控制器、甚至 URL。
> 前置：[第 27 章](mount-app.md)、[第 29 章](component-sharing.md)。预计 20 分钟。
> 示例：`tests/data_for_tests/ZThirdDemo` —— 有覆盖与没覆盖两条路径都被 `ZThirdDemoTest.php` 断言过。

## 五个层次，一张总表

| 层次 | 手段 | 覆盖谁 | 本卷示例位置 |
|---|---|---|---|
| **文件级** | 父应用按「子应用 name」建同名子目录 | 子应用的视图 / 配置 / 资源 | `view/shop/index.php`、`config/shop/greet.php`、`res/shop/third.css` |
| **类级** | `controller_class_map`（可从父应用注入给子应用） | 某个控制器类的实现 | `src/Override/ShopControllerOverride.php` |
| **路由级** | `RouteHookRewrite` / `RouteHookRouteMap` | URL 的指向 | `MainApp::onInit()` 里的 `/legacy-shop` |
| **视图级** | `use_admin_view` / `use_user_view` + 头尾视图选项 | `_Show()` 的渲染方式 | 第 17 章 |
| **组件级** | `ext` 表（`true` / 数组 / `'@方法'` / 选项键名 / `EXT_*`） | 组件与扩展的装配 | 第 34 章 |

## 文件级覆盖：先讲清「谁赢」

查找文件的顺序由 `getOverrideableFile()` 决定，它沿相位链**从根往外**逐层试：

```
当前相位 ':shop' → explode(':') = ['', 'shop']，于是依次试：
  ① 根相位 + 名字后缀：  <父应用 path>/<path_sub>/shop/<file>   ← 父应用放这里就赢
  ② 自己（子相位）：     <子应用 path>/<path_sub>/<file>        ← 父应用没有则用它的
```

所以规则一句话：**父应用在自己目录下，按子应用 name 建一个同名子目录，把同名文件放进去，就覆盖了子应用的那份**；不放则用子应用自己的。

三个 `path_sub` 的对应关系：

| 覆盖什么 | 放在父应用的 | 覆盖子应用的 |
|---|---|---|
| 视图 | `view/<name>/<视图名>.php` | `view/<视图名>.php` |
| 配置（`Configer`，文件名为 `<名>.php`） | `config/<name>/<名>.php` | `config/<名>.php` |
| 资源（`RouteHookResource`） | `res/<name>/<文件>` | `res/<文件>` |

实测（ZThirdDemo，子应用 `name => 'shop'`）：

```
GET /shop/            → PARENT-OVERRIDE-VIEW index   ← 父应用 view/shop/index.php 赢
GET /shop/native      → CHILD-OWN-VIEW native        ← 没人覆盖，用子应用自己的
visit 动作读配置       → own_config=child-own-config  ← 只覆盖了 greet，这个仍是子应用的
                        greet=parent-greet            ← 父应用 config/shop/greet.php 赢
GET /shop/res/third.css  → parent-overridden-third-css  ← 父应用 res/shop/third.css 赢
GET /shop/res/native.css → child-owned-native-css    ← 子应用自己的
```

**排错**：想知道某个文件到底命中了谁，用带 `$must_exist` 的查询（第 7 章讲过这个参数）：

```php
App::_()->getOverrideableFile('view', 'index.php', true, true);   // 命中则返回路径，没有则 null
App::_()->getConfigFile('greet.php', true);
```

## 类级覆盖：换掉控制器实现

在父应用的 `app` 选项里给子应用注入映射（**不改子应用的任何文件**）：

```php
ThirdApp::class => [
    'name' => 'shop',
    'controller_url_prefix' => 'shop/',
    'controller_class_map' => [
        ThirdMainController::class => ShopControllerOverride::class,
    ],
],
```

```php
// ZThirdDemo/src/Override/ShopControllerOverride.php
namespace ZThirdDemo\Override;

class ShopControllerOverride extends MainController   // 继承它原本的控制器
{
    public function index()
    {
        // 想保留原逻辑就先调 parent::index()，这里直接给出新行为
        Helper::Show([...], 'index');
    }
}
```

要点：
- 映射发生在**路由派发**时（`Route::getRouteCallback()`），所以 URL 不变、只有执行的类变了；
- 覆盖类**故意放在 `Controller/` 之外**，否则它自己会被当成父应用的一个控制器多出一条路由；
- 同理可以用它换掉子应用的业务类（`ShopBusiness` → 你的子类），只要在各自被 `::_()` 取用的位置生效。

## 路由级覆盖：改了 URL 的指向

```php
// ZThirdDemo/src/System/MainApp.php :: onInit()
RouteHookRewrite::_()->assignRewrite('/legacy-shop', 'shop/');
```

实测：`GET /legacy-shop` 渲染出子应用的首页（也就是被父应用覆盖后的那个视图）。

> ⚠️ **重写表的键必须带前导 `/`**：钩子内部是拿 `'/'.$path_info` 跟你的键比较的。写 `'legacy-shop'` 永远不命中（这是很容易踩的坑；`Helper::assignRewrite()` 也一样）。
>
> 另一个常见用法是把 URL 直接绑到「类@方法」：`RouteHookRouteMap::_()->assignRoute($url, $callback)`，重要路由用 `assignImportantRoute()`，它们在路由匹配前生效（第 9 章）。

## 视图级覆盖：换渲染方式与页头页脚

```php
MainApp::_()->options['use_admin_view'] = true;    // _Show 交给 GlobalAdmin（带后台头尾）
MainApp::_()->options['use_user_view']  = true;    // 交给 GlobalUser（带前台头尾）
```

命中条件是「当前路由的控制器实现了 `AdminControllerInterface` / `UserControllerInterface`」；头尾文件由 `admin_view_file_header/footer`、`user_view_file_header/footer` 指定（都是**相位可覆盖**的视图名，所以第三个应用也能换掉后台的头尾）。空视图名由 `GlobalAdmin::_Show()` / `GlobalUser::_Show()` 内部兜底成当前路由路径。

## 组件级覆盖：换装配

```php
'ext' => [
    JsonView::class => true,                     // 打开
    RouteHookRewrite::class => '@myRewriteOptions',   // 用本应用的方法取选项
    PermissionMenu::class => 'permission_menu_on',    // 用某个选项的值决定（开/关/配置）
    DbManager::class => App::EXT_SKIP_INIT,       // 只取实例不 init
    Logger::class => App::EXT_FOLLOW_APP,         // 跟随应用选项初始化
],
```

`EXT_*` 常量与四种取值形态见第 34 章「开发组件与扩展」（⏳ M4 落稿）与 [参考手册](../reference/Core-KernelTrait.md)。

## 优先级与冲突排查

1. **同一种资源在同一相位链里**：先命中的赢，顺序是「父应用的 `<name>` 子目录 → 子应用自己」。
2. **同一层里同名目录合并**时（例如菜单树），以**先创建者**的 `url`/`icon` 为准（见 [Ext-PermissionMenu](../reference/Ext-PermissionMenu.md) 注意事项）。
3. **路由钩子**按注册位置决定先后：外层的 pre → 内层 pre → 默认路由 → 内层 post → 外层 post。
4. 排查工具：`PhaseContainer::_()->dumpAllObject()`（相位里到底有哪些实例）、`getOverrideableFile(..., true)`（文件到底命中谁）、`RouteLister::_()->listAll()`（现在有哪些路由，第 9 章）。

## 覆盖的代价：三条纪律

- **别改 `vendor/` 或被挂应用的源码**：覆盖的入口全在父应用的选项与自己的目录里，这样对方升级时你只需要替换目录。
- **覆盖要留痕**：在父应用里集中写一处注释/清单（哪个文件覆盖了谁的什么），否则一年后没人知道为什么子应用的视图「不对」。
- **升级后回归**：对方换了视图变量名/配置键，你的覆盖文件不会自动跟进 —— 把第 27 章的冒烟清单（`ZThirdDemoTest` 那几条）纳进 CI。

## 下一步

- [第 31 章 安装器与 Web 安装流程](installer.md)
- 第 34 章「开发组件与扩展」（⏳ M4 落稿）：`ext` 表与 `EXT_*` 常量
