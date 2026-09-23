# 2-4 视图与模板

> 解决什么问题：视图文件放在哪、怎么被找到；页眉页脚（布局）怎么加；数据怎么传进来；输出怎么转义；以及不用 PHP 文件写视图的几种办法。
> 前置：[第 2-1 章 四层架构与调用规范](layers.md)、[第 2-3 章 控制器](controllers.md)。预计 20 分钟。
> 示例：`tests/data_for_tests/ZAllDemo/view/main.php`（最小视图）、`demo/view/`（含错误页 `_sys/`）、`tests/data_for_tests/ZThirdDemo/view/shop/index.php`（被覆盖的视图）。

```bash
php -S 127.0.0.1:8080 -t demo/public
# 打开 http://127.0.0.1:8080/demo.php 看渲染结果（demo.php 用的是「函数式视图」，见 §5）
```

## 最小示例

控制器（`tests/data_for_tests/ZAllDemo/src/Controller/MainController.php`）：

```php
public function index()
{
    $var = __h(DemoBusiness::_()->foo());
    Helper::Show(get_defined_vars(), 'main');   // 视图名 'main'
}
```

视图（`tests/data_for_tests/ZAllDemo/view/main.php`）：

```php
<h1><?= $var ?></h1>
```

就这两步：`path_view`（默认 `view/`）下的 `main.php` 被 include，控制器传的变量被 `extract()` 成视图里的 `$var`。

## 机制说明

### 1. 视图文件怎么定位

[`View::getViewFile()`](../reference/Core-View.md) 的逻辑很短：

1. 视图名没有 `.php` 后缀就补上（`main` → `main.php`）；
2. 交给 `getOverrideableFile($options['path_view'], $file)` 去找——`path_view` 默认 `'view'`（相对项目根）；
3. 这个查找是**按相位逐层回退**的：子应用的视图可以被父应用在 `view/<子应用名>/xxx.php` 处覆盖（[第 3-5 章](overriding.md)）。

视图名从哪来：

| 场景 | 视图名 |
|---|---|
| `Helper::Show($data, 'note/show')` | `note/show` → `view/note/show.php` |
| `Helper::Show($data)`（省略） | 当前路由路径，如 `/about/me` → `about/me` |
| 错误页 `error_404 => '_sys/error_404'` | `view/_sys/error_404.php` |

> 注意「省略视图名」的边界：访问根路径 `/` 时路由路径是 **`Main/index`**（欢迎类规则补出来的），所以根路径省略视图名会去找 `view/Main/index.php`。想渲染 `view/main.php` 就显式写视图名。

### 2. 渲染流程：头 → 视图 → 尾

`View::_Show($data, $view)` 做的事：

```
1. view_skip_notice_error 默认为 true → 渲染期间屏蔽 E_NOTICE（未定义变量不再刷屏）
2. 解析 view / head_file / foot_file 三个文件路径
3. $this->data = array_merge($this->data, $data)   ← assignViewData 的预置数据 + 本次传入
4. extract($this->data)  → 数组键变成视图里的变量
5. include 头视图 → include 主视图 → include 尾视图
```

三个入口：

| 方法 | 头尾 | 返回 | 用途 |
|---|---|---|---|
| `Helper::Show($data, $view)` | 带 | 直接输出 | 常规页面 |
| `Helper::Render($view, $data)` | **不带** | 字符串 | 邮件、片段、二次加工 |
| `View::_()->_Display($view, $data)` | 不带 | 直接输出 | 极简输出 |

### 3. 页眉页脚：`setViewHeadFoot()`

```php
public function __construct()
{
    Helper::setViewHeadFoot('header', 'footer');   // 视图名，同样相对 path_view
}
```

之后每次 `Helper::Show()` 都会自动渲染 `view/header.php` → 主视图 → `view/footer.php`。`demo/public/demo.php` 的 `MainController::__construct()` 就是这么写的（它的头尾是函数式视图 `Views::header()` / `Views::footer()`）。

### 4. 数据传递与转义

```php
Helper::Show(get_defined_vars(), 'main');          // ① 当前作用域全部变量
Helper::Show(['notes' => $notes], 'note/list');    // ② 显式数组
Helper::assignViewData('site_name', 'MyProj');     // ③ 预置（每次 Show 都带上）
```
`assignViewData()` 与 `Show()` 的 `$data` 最终汇到**同一个地方**：[`View`](../reference/Core-View.md) 组件（`View::_()`，按相位一个单例）的 `$data` 属性。`Helper::assignViewData($k, $v)` 就是 `View::_()->data[$k] = $v`（传数组则 merge）；`Show($data, $view)` 渲染前做 `array_merge($this->data, $data)`，所以**参数里的同名键会覆盖预置值**。

正因为它是「组件上的一个属性」，用法上要留意三点：

- **预置值是相位级的共享状态**：写进去之后，**同一相位后续每一次渲染**都会带上它（包括 head/foot 视图，以及同相位子应用的视图）。适合 `site_name`、当前用户这类「整站一样」的数据；请求独有的数据请走 `Show()` 的 `$data` 参数。
- **别把敏感数据放进去**：它会被所有视图看见；`__h()` 只解决转义，不解决「这个视图该不该看到」。
- **想清干净**：`View::_()->reset()`（清 `$data` 与 head/foot 设置）；或干脆只用每个动作自己的 `$data`，不依赖预置。
视图里读数据就是读变量（`$notes`）。**转义是必须的**，框架提供全局函数：

| 函数 | 用途 |
|---|---|
| `__h($str)` | HTML 转义（防 XSS）——输出用户数据时一律用它 |
| `__url('note/show')` | 站内 URL（部署到子目录也不会错） |
| `__res('app.css')` | 静态资源 URL（[第 3-3 章](static-resources.md)） |
| `__l('hello')` / `__hl('hello')` | 翻译 / 翻译+转义（[第 2-14 章](i18n.md)） |
| `__json($data)` | JSON 编码后输出 |

```php
<h1><?= __h($note['title']) ?></h1>
<a href="<?= __url('note/show?id=' . (int)$note['id']) ?>">详情</a>
<img src="<?= __res('img/logo.png') ?>">
```

视图里**只能用全局函数**，不要查库、不要调 Business——那是[第 2-1 章](layers.md)的越界矩阵里明确禁止的。

### 5. 不用 PHP 文件写视图：三种替换引擎

框架的 `View` 是一个单例，可以被别的实现替换（扩展在自己的 `init()` 里做 `View::_(static::_())`，并各自留了 `*_skip_replace` 开关）：

| 扩展 | 视图长什么样 | 关键选项 |
|---|---|---|
| [`Ext\CallableView`](../reference/Ext-CallableView.md) | **函数/方法**：`Views::main_view($data)` | `callable_view_class`、`callable_view_head/foot`、`callable_view_is_object_call`、`callable_view_prefix` |
| [`Ext\EmptyView`](../reference/Ext-EmptyView.md) | 视图名即要输出的字符串（占位/降级） | `empty_view_key_view`、`empty_view_key_wellcome_class`、`empty_view_trim_view_wellcome` |
| [`Ext\JsonView`](../reference/Ext-JsonView.md) | 把数据直接 JSON 输出 | `json_view_skip_vars` |

`demo/public/demo.php` 用的是第一种：

```php
'ext' => [
    CallableView::class => true,
    // 默认的 View 不支持函数调用，我们开启自带扩展 CallableView 代替系统的 View
],
'callable_view_class' => Views::class,
```

于是 `Helper::Show($data, 'main_view')` 调的是 `MySpace\View\Views::main_view($data)`。

### 6. 视图也能被覆盖

同一个视图名，子应用/父应用可以各有一份；查找会**按相位回退**（`getOverrideableFile()`）。`tests/data_for_tests/ZThirdDemo` 里就有现成的例子：`third/view/shop/index.php` 被主应用的 `view/shop/index.php` 覆盖，规则与排查看[第 3-5 章](overriding.md)。

## 常见写法

**① 头尾拆分，页面骨架只写一次**（见 §3）。

**② 列表 + 分页**（分页数据来自 `Helper::PageHtml()`，[第 2-5 章](database.md)）

```php
<table>
<?php foreach ($notes as $note): ?>
    <tr><td><?= __h($note['title']) ?></td></tr>
<?php endforeach; ?>
</table>
<?= Helper::PageHtml($total) ?>
```

**③ 用 `Render()` 做片段与邮件**

```php
$row = Helper::Render('note/row', ['note' => $note]);   // 片段
$body = Helper::Render('mail/welcome', ['user' => $user]); // 邮件正文
```

**④ 布局变量少传一点：`assignViewData()` 预置公共变量**

```php
// 控制器构造函数里
Helper::assignViewData(['site_name' => 'MyProj', 'year' => date('Y')]);
```

**⑤ 错误页就是普通视图**

`demo/view/_sys/error_404.php`、`_sys/error_500.php`、`_sys/error_maintain.php` 都是普通视图文件，由 `error_404`/`error_500`/`error_maintain` 选项指定（[第 2-11 章](exception.md)）。

## 常见错误

| 现象                                         | 原因                                                    | 改法                                        |
| ------------------------------------------ | ----------------------------------------------------- | ----------------------------------------- |
| 视图找不到（include 失败/白屏）                       | 视图名与文件路径不对，或没放在 `path_view` 下                         | 视图名 = 相对 `view/` 的路径；确认 `view/<名>.php` 存在 |
| 根路径省略视图名，报找不到 `Main/index`                 | 根路径的路由路径是欢迎类补出来的 `Main/index`                         | 显式传视图名（如 `Helper::Show($data, 'main')`）   |
| 视图里 `$var` 是 `null` 且不报错                   | `view_skip_notice_error` 默认 `true`，未定义变量只当 notice 被屏蔽 | 检查变量是否真的传进 `Show()`；调试时可临时关掉该选项           |
| 页面里出现未转义的用户输入                              | 直接 `<?= $title ?>` 输出                                 | 一律 `<?= __h($title) ?>`                   |
| 部署到子目录后视图里的链接 404                          | 视图里手写 `/note/show`                                    | 用 `__url('note/show')`                    |
| `Helper::Render()` 出来的页面没有头尾               | `Render()` **不带**头尾（只渲染单个视图）                          | 页面用 `Show()`；确实要带布局就自己拼头尾或改用视图包含          |
| 开了 `CallableView` 但 `Show()` 还是找 `.php` 文件 | 没配 `callable_view_class`，或该扩展没进 `ext`                 | 两个都要配（见 §5）                               |
| 视图里写业务逻辑，改需求要动模板                           | 越界的典型表现                                               | 数据准备放控制器/Business，视图只做显示                  |

## 下一步

- [第 2-5 章 数据库](database.md) 与 [第 2-6 章 模型层](model.md)：把视图要的数据准备好。
- [第 2-14 章 国际化与文案](i18n.md)：视图里的 `__l()` / `__hl()`。
- [第 3-3 章 静态资源与文档根](static-resources.md)：`__res()` 与资源目录。
- [第 3-5 章 重写与覆盖](overriding.md)：视图级覆盖的完整规则。
- 参考手册：[DuckPhp\Core\View](../reference/Core-View.md)、[DuckPhp\Ext\CallableView](../reference/Ext-CallableView.md)、[DuckPhp\Ext\EmptyView](../reference/Ext-EmptyView.md)、[DuckPhp\Ext\JsonView](../reference/Ext-JsonView.md)。
