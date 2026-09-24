# 3-3 静态资源与文档根

> 解决什么问题：CSS/JS/图片这些静态文件放在哪、URL 长什么样、多个应用怎么不打架、上线时怎么交给 Web 服务器。
> 前置：[第 3-2 章 把外部应用挂进来](mount-app.md)。预计 12 分钟。
> 示例：`tests/data_for_tests/ZThirdDemo`（主应用 `res/main.css`、第三方应用 `res/third.css` 与 `res/native.css`）。

## 两条路：Web 服务器直出 vs 框架代发

| 方式 | 谁发文件 | 什么时候用 |
|---|---|---|
| **文档根直出** | nginx/Apache 直接读 `public/` 下的文件 | 生产环境（性能最好）。资源放在 `path_document`（默认 `public/`）里，或部署时拷进去 |
| **框架代发** | [`RouteHookResource`](../reference/Component-RouteHookResource.md) 收到请求后读文件并输出 | 开发时不想配 rewrite；或资源要放在 `public/` 之外按应用分目录 |

框架代发靠两个选项：

| 选项 | 说明 |
|---|---|
| `path_resource` | 资源**源目录**（默认 `res/`，相对应用自己的 `path`） |
| `controller_resource_prefix` | 资源 URL 出现在哪个前缀下 |

## URL 与磁盘路径的对应关系

前缀是这么拼出来的（`RouteHookResource::_Hook()`）：

```
前缀 = '/' . controller_url_prefix . controller_resource_prefix
URL  /<前缀>/<文件>   →   <应用 path>/<path_resource>/<文件>
```

于是一个应用里的 `res/main.css` 可以这样暴露：

```php
// ZThirdDemo/src/System/MainApp.php —— 根应用
'controller_resource_prefix' => '/res/',     // 前缀 = '' + '/res/' → URL: /res/main.css
```

```php
// ZThirdDemo/third/System/ThirdApp.php —— 被挂的子应用
'controller_resource_prefix' => 'res/',      // 前缀 = '/shop/' + 'res/' → URL: /shop/res/third.css
```

实测结果（`ZThirdDemoTest.php` 三个断言）：

```
GET /res/main.css            → 主应用的 res/main.css
GET /shop/res/native.css     → 子应用的 third/res/native.css
GET /shop/res/third.css      → 父应用 res/shop/third.css（覆盖了子应用的同名资源，见第 3-5 章）
```

> ⚠️ **这里的斜杠很坑，务必按这两条写**：
> - 根应用写 `'/res/'`（带**前导**斜杠）——因为请求的 path_info 自带前导 `/`，而根应用的 `controller_url_prefix` 是空串；
> - 子应用写 `'res/'`（**不带**前导斜杠）——因为它的 `controller_url_prefix`（`'shop/'`）已经以 `/` 结尾，再带一个就拼成 `/shop//res/` 而永远匹配不上。

> ⚠️ **这里的 URL 在三种环境下都成立**：nginx/Apache（rewrite 把所有请求交给 `index.php`）、进程内调用（`ZThirdDemoTest` 就是直接 `serve()` 断言的）、以及**带 router 脚本的** `php -S`（写法见[第 1-7 章 §二](deployment.md)）。
> 唯一例外是**不带 router 的** `php -S … -t public`（含框架的 `bin/cli.php run`）：内置服务器不把带后缀的 URI 交给 `index.php`，所以 `/res/main.css` 会 404 —— 开发时要么用 router 脚本，要么把资源放进 `public/` 让服务器直出。

## 多应用下的资源组织

推荐按应用分目录，避免同名冲突：

```
public/                 ← 文档根（生产直出、cloneResource 的目标）
res/                    ← 主应用资源源目录
├── main.css
└── shop/               ← ★ 与被挂应用 name 同名的目录 = 覆盖它的资源（第 3-5 章）
    └── third.css
third/res/              ← 第三方应用自己的资源
├── third.css
└── native.css
```

约定：
- 一个应用一个 URL 前缀段（`/res/`、`/shop/res/`），不要挤在根上；
- 通配符/版本号走查询串（`/res/main.css?v=20260918`），框架代发只按路径取文件，不理查询串；
- 千万别把 `.php` 放到资源目录里：`RouteHookResource` 会**拒绝** `.php` 与含 `../` 的路径（这是它的安全检查）。

## 上线：把 `res/` 部署到文档根

生产环境一般让 Web 服务器直出，这时把资源目录同步到 docroot 相应位置：

```php
RouteHookResource::_()->cloneResource();        // force=false：已存在的文件不覆盖
RouteHookResource::_()->cloneResource(true);    // force=true：强制覆盖
```

`cloneResource()` 的落点由这两个选项决定，且**受相位影响**：当前在哪个应用相位，就拷那个应用的资源；CDN 前缀（`http://…`）会被跳过。

配套的 URL 生成用全局函数/Helper：

```php
__res('main.css');                 // 按 controller_resource_prefix 生成资源 URL
Helper::Res('main.css');           // 等价写法（第 2-9 章）
__url('shop/');                    // 生成普通 URL
```

## 与内置 HTTP 服务一起用

[`HttpServer`](../reference/HttpServer-HttpServer.md)（第 4-5 章）的文档根是 `path_document`：

```php
HttpServer::RunQuickly([
    'path' => __DIR__ . '/../',
    'path_document' => 'public',    // → docroot = <path>/public
]);
```

于是「文档根直出」和「框架代发」在开发时是并存的：`/doc.css` 这类文件由内置服务器直接吐，`/res/…` 或 `/shop/res/…` 由框架代发。

## 常见错误

| 现象                   | 原因                                                        | 改法                                                |
| -------------------- | --------------------------------------------------------- | ------------------------------------------------- |
| 资源 404，文件明明存在        | 前缀斜杠不对（多一个 / 或少一个 /）                                      | 根应用 `'/res/'`、子应用 `'res/'`；用第 3-5 章的方法确认命中了哪个文件   |
| 子应用资源 404，主应用正常      | 子应用没配 `controller_resource_prefix`（默认是 `''`，等于所有路径都可能是资源） | 给每个应用显式配资源前缀                                      |
| 想发 `.php` 或 `../` 路径 | 被安全检查拒绝                                                   | 别这么干；动态内容走控制器                                     |
| 生产上资源都 404           | 没把 `res/` 部署到 docroot                                     | 用 `cloneResource()`，或在构建脚本里 `cp -r res/* public/` |
| 两个应用资源同名被互相覆盖        | 共用一个前缀目录                                                  | 按应用分前缀；覆盖是有意为之时才用同名（第 3-5 章）                      |
|                      |                                                           |                                                   |

## 相关参考

- [DuckPhp\Component\RouteHookResource](../reference/Component-RouteHookResource.md)（含 `cloneResource()` 细节）
- [第 2-19 章 使用用户系统](user.md) 的 `__use_logined_view_data`（登录后视图）
- [第 3-5 章 重写与覆盖](overriding.md)：`res/<name>/` 覆盖规则
