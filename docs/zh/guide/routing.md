# 2-4 路由进阶

> 解决什么问题：URL 是怎么变成「某个控制器方法」的；参数从哪来；怎么重写旧链接、怎么把 URL 绑到指定类@方法；以及多应用前缀是怎么参与匹配的。
> 前置：[第 2-1 章 四层架构与调用规范](layers.md)。预计 20 分钟。
> 示例：`demo/public/demo.php`（真实路由：URL `about/me` → `aboutController::me()`）。跑法：

```bash
php -S 127.0.0.1:8080 -t demo/public
# 打开 http://127.0.0.1:8080/demo.php ，点页面里的 “go to about/me”
```

## 最小示例

`demo/public/demo.php` 里真实存在的两段代码，构成了最小路由闭环：

```php
namespace MySpace\Controller
{
    class MainController
    {
        public function index()
        {
            $url_about = __url('about/me');   // 生成 URL
            Helper::Show(get_defined_vars(), 'main_view');
        }
    }
    class aboutController
    {
        public function me()                  // URL 的最后一段就是方法名
        {
            Helper::Show(get_defined_vars()); // 不传视图名 → 用当前路由路径当视图名（about/me）
        }
    }
}
```

访问 `/about/me` 时：`about/me` 被拆成「类路径 `about` + 方法 `me`」→ 类名拼成 `MySpace\Controller\aboutController`（`Controller` 后缀自动加）→ 调 `me()`。不传视图名时，视图名就是路由路径 `about/me`（即视图文件 `view/about/me.php`）。

## 机制说明

### 1. URL → 类@方法的默认规则

[`Route::pathToClassAndMethod()`](../reference/Core-Route.md) 与 `adjustClassBaseName()` 的行为可以用一张表说清（`namespace_controller` 默认 `Controller`，`controller_class_postfix` 默认 `Controller`，`controller_welcome_class` 默认 `Main`，`controller_welcome_method` 默认 `index`）：

| URL（PATH_INFO） | 拆法 | 落到 |
|---|---|---|
| `/`（空） | 落到欢迎类 | `Controller\MainController::index()` |
| `/about` | 只有一段 → 该段是**欢迎类的方法** | `Controller\MainController::about()` |
| `/about/me` | 最后一段是方法，前面是类路径 | `Controller\aboutController::me()` |
| `/test/done` | 同上 | `Controller\testController::done()` |
| `/admin/user/list` | 类路径可多层 | `Controller\admin\userController::list()` |

几个开关（写在应用选项里）：

```php
$options = [
    'namespace_controller' => 'Controller',      // 控制器所在子命名空间（默认值）
    'controller_class_postfix' => 'Controller',  // 类名后缀，默认自动补
    'controller_method_prefix' => 'action_',     // 方法前缀（demo/src/System/App.php 就是这么配的）
    'controller_welcome_class' => 'Main',        // 欢迎类
    'controller_welcome_method' => 'index',      // 欢迎方法
    'controller_welcome_class_visible' => false, // false 时 /Main/xxx 这类显式写法会被拒绝（E009）
    'controller_path_ext' => '',                 // 需要 .html 之类后缀时设它（不匹配则 E008）
    'controller_class_adjust' => '',             // 额外调整，如 'uc_method;uc_class'
    'controller_class_map' => [                  // 直接换掉某个控制器的实现
        'MyProj\Controller\UserController' => 'MyProj\Controller\UserControllerV2',
    ],
];
```

`controller_class_map` 也可以在运行期用 `Helper::replaceController($old, $new)` 追加——它是覆盖机制的基石之一（[第 3-5 章](overriding.md)）。

### 2. PATH_INFO 从哪来

框架统一从 `Route::PathInfo()` 读，实际来源是 `$_SERVER['PATH_INFO']`。两个现实问题它替你处理了：

- **Nginx/PHP-FPM 默认没有 PATH_INFO**：改用 `?_r=about/me` 形式传递，打开 [`RouteHookPathInfoCompat`](../reference/Component-RouteHookPathInfoCompat.md) 扩展即可（见「常见写法 ②」）。
- **PHP 内置服务器**：`controller_fix_mistake_path_info`（默认 `true`）会在 `SCRIPT_NAME === '/index.php'` 且 PATH_INFO 为空时，从 `REQUEST_URI` 里补出路径——这是 `php -S` 下能直接跑的原因（[第 1-7 章](deployment.md)）。

CLI 或测试里想伪造请求路径：`Route::_()->PathInfo('about/me')`。

### 3. URL 生成：`__url()` 的规则

`__url()`（= `Helper::Url()` = `Route::_()->Url()`）不是字符串拼接，规则如下（`defaultUrlHandler()`）：

| 传入                           | 结果                          |
| ---------------------------- | --------------------------- |
| `'/other/app.php'`（以 `/` 开头） | **原样返回**（跨应用/绝对路径用这种）       |
| `''`                         | 当前 basepath（部署在子目录时就是那个子目录） |
| `'?page=2'` / `'#top'`       | 当前路径 + 该后缀                  |
| `'about/me'`                 | basepath + `/about/me`      |

所以「站内另一个页面」写 `__url('about/me')`；要原样输出绝对路径写 `__url('/res/logo.png')`，或更明确的 `__res('logo.png')`（[第 3-3 章](static-resources.md)）。

想完全接管 URL 生成（接 CDN、自定义规则）：`Route::_()->url_handler` 是可替换的回调，设了之后 `Url()` 直接调它。

### 4. 重写：`RouteHookRewrite`

把「用户看到的 URL」映射到「内部路由」，地址栏不变：

```php
$options = [
    'ext' => [\DuckPhp\Component\RouteHookRewrite::class => true],
    'rewrite_map' => [
        '/legacy-shop' => 'shop/',            // 精确匹配
        '~^/old/(\d+)$~' => 'article/$1',     // 以 ~ 开头 = 正则模板
    ],
];
// 运行期追加
Helper::assignRewrite('/promo', 'activity/index');
```

> ⚠️ **键必须带前导 `/`**。钩子内部拿 `'/'.$path_info` 与模板比较，写成 `'legacy-shop'` 永远匹配不上——重写不生效时先看这里。

### 5. 路由映射：`RouteHookRouteMap`

想把 URL 直接绑到「类@方法」（不遵守默认命名规则），用路由映射：

```php
$options = [
    'ext' => [\DuckPhp\Component\RouteHookRouteMap::class => true],
    'route_map_important' => [                        // 抢在默认路由之前匹配
        '/health'            => 'MyProj\Controller\HealthController@check',
        '/user/{id:\d+}'     => 'MyProj\Controller\UserController@show',
        '^/api/v(\d+)/ping$' => 'MyProj\Controller\ApiController@ping',
    ],
    'route_map' => [                                  // 默认路由没命中时的兜底
        'legacy*' => 'MyProj\Controller\LegacyController@dispatch',
    ],
];
```

匹配规则（`matchRoute()`）：

| 模式写法 | 含义 |
|---|---|
| `health` / `/health` | 精确匹配（前导 `/` 可有可无） |
| `legacy*` | 前缀通配，剩余路径按 `/` 拆成参数交给回调 |
| `^/api/v(\d+)/ping$` | 以 `^` 开头 = 正则，捕获组按顺序成为参数 |
| `/user/{id:\d+}` | `{名:规则}` 占位写法，编译成命名捕获组（`:规则` 可省，默认 `\w+`；`{id?}` 表示可选） |

回调写法（`adjustCallback()`）：`Class@method`（用 `::_()` 单例）、`Class->method`（用 `new`）、或任意 callable；**`Class::method` 这种静态字符串不支持**。

`route_map_important` 挂在 pre 链（`prepend-inner`），`route_map` 挂在 post 链（`append-outter`）——位置与短路语义见[第 2-3 章](route-hooks.md)。

### 6. 多应用前缀：`controller_url_prefix`

子应用挂载时会带自己的 `controller_url_prefix`（[第 3-2 章](mount-app.md)）。匹配规则是「**前缀必须完全对上**」：`pathToClassAndMethod()` 先比前缀，不匹配就直接失败并把原因写进 `route_error`（`E001`），父应用才有机会把请求交给其它子应用。

同一个应用里也可以设它，效果是「这个应用的所有 URL 都强制带该前缀」。

### 7. 排错：路由为什么没命中

```php
Route::_()->getRouteError();          // 最近一次失败原因（E001/E003/E008/E009…）
Route::_()->getRouteCallingClass();   // 命中的类
Helper::getRouteCallingMethod();      // 命中的方法（控制器里可用）
Route::_()->PathInfo();               // 框架实际拿到的路径
```

错误码速查：`E001` 前缀不匹配、`E003` 控制器类不存在（反射失败）、`E008` 路径后缀不符合 `controller_path_ext`、`E009` 显式写了不可见的欢迎类。

## 常见写法

**① 干净的 URL 用路由映射，兼容旧链接用重写**

```php
Helper::assignRewrite('/p/42', 'product/show?id=42');                  // 旧链接 → 新路由
Helper::assignImportantRoute('/product/{id:\d+}', 'MyProj\Controller\ProductController@show');
Helper::assignRoute('sitemap*', 'MyProj\Controller\SitemapController@dispatch');
```

**② 没有 PATH_INFO 的服务器：开兼容扩展**

```php
$options = [
    'ext' => [\DuckPhp\Component\RouteHookPathInfoCompat::class => 'path_info_compact_enable'],
    'path_info_compact_enable' => true,
    'path_info_compact_action_key' => '_r',   // URL 形如 /index.php?_r=about/me
];
```

**③ 换掉某个控制器实现而不动原文件**

```php
Helper::replaceController(
    \MyProj\Controller\UserController::class,
    \MyProj\Controller\UserControllerEx::class
);
```

**④ URL 一律用生成器，别手写字符串**

```php
// 视图里
<a href="<?=__url('about/me')?>">关于</a>
// 代码里
Helper::Show302(Helper::Url('user/login'));
```

部署到子目录时，手写的 `/about/me` 会 404（少了子目录前缀），`__url('about/me')` 会自动带上。

## 常见错误

| 现象                                  | 原因                                            | 改法                                                |
| ----------------------------------- | --------------------------------------------- | ------------------------------------------------- |
| `assignRewrite('legacy', …)` 怎么都不生效 | 键少了前导 `/`，钩子拿 `'/'.$path_info` 比较             | 写成 `'/legacy'`                                    |
| 访问 `/about` 报类不存在                   | 单段路径被当成**欢迎类的方法**，不是控制器                       | 控制器至少两段：`/about/me`；单段需求改用 `route_map`            |
| `/Main/index` 被拒绝（E009）             | `controller_welcome_class_visible` 默认 `false` | 用 `/` 访问欢迎页；确实需要显式路径就设为 `true`                    |
| 路由映射里写 `Class::method` 不生效          | `::` 形式**不支持**                                | 用 `Class@method`（`::_()`）或 `Class->method`（`new`） |
| 普通 `route_map` 里的规则抢不过默认路由          | 位置不同：important 在默认路由**之前**，普通 map 是**兜底**     | 需要优先匹配就放进 `route_map_important`                   |
| 子应用里访问得到 404、错误码 E001               | URL 没带子应用的 `controller_url_prefix`            | URL 加上前缀，或调整子应用配置（[第 3-2 章](mount-app.md)）        |
| 部署到子目录后所有站内链接 404                   | 手写了 `/xxx` 绝对路径                               | 一律用 `__url()`/`Helper::Url()` 生成                  |
| 开了 `_r=` 兼容模式，原 PATH_INFO 路由全失效     | 兼容模式下路径改从查询串取                                 | 只在没有 PATH_INFO 的服务器上开（[第 1-7 章](deployment.md)）   |

## 下一步

- [第 2-5 章 控制器](controllers.md)：路由命中之后，控制器里怎么写。
- [第 2-3 章 路由钩子](route-hooks.md)：钩子位置、短路语义，以及完整的「谁先命中」顺序。
- [第 3-5 章 重写与覆盖](overriding.md)：`controller_class_map` 背后的整套覆盖机制。
- 参考手册：[DuckPhp\Core\Route](../reference/Core-Route.md)、[DuckPhp\Component\RouteHookRewrite](../reference/Component-RouteHookRewrite.md)、[DuckPhp\Component\RouteHookRouteMap](../reference/Component-RouteHookRouteMap.md)、[DuckPhp\Component\RouteHookPathInfoCompat](../reference/Component-RouteHookPathInfoCompat.md)。
