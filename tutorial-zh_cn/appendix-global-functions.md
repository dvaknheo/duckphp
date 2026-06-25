# 附录：全局函数参考

以下全局函数在框架启动时通过 `src/Core/Functions.php` 定义，开箱即用。

## HTML / 国际化

| 函数 | 说明 |
|---|---|
| `__h($str)` | HTML 转义（`htmlspecialchars`），自动递归数组 |
| `__l($str, $args = [])` | 国际化翻译 |
| `__hl($str, $args = [])` | 国际化 + HTML 转义 |

## URL / 路径

| 函数 | 说明 |
|---|---|
| `__url($url)` | 生成应用内 URL |
| `__res($url)` | 生成资源 URL（支持 CDN） |
| `__domain($use_scheme = false)` | 获取当前域名 |

## 输出 / 数据

| 函数 | 说明 |
|---|---|
| `__json($data, $options = 0)` | JSON 编码（自动添加 `JSON_UNESCAPED_UNICODE`） |
| `__display($view, $data = null)` | 渲染视图片段 |

## 调试（仅调试模式有效）

| 函数 | 说明 |
|---|---|
| `__var_dump(...$args)` | 页面输出 var_dump |
| `__var_log($var)` | 记录变量到日志 |
| `__trace_dump()` | 打印调用栈 |
| `__debug_log($msg, $args = [])` | 写入调试日志 |
| `__is_debug()` | 判断当前是否为调试模式 |
| `__is_real_debug()` | 判断是否为真实调试模式 |
| `__platform()` | 获取平台标识 |
| `__logger()` | 获取日志实例 |

## 使用方法

```php
// 在视图中
<a href="<?= __url('user/login') ?>">登录</a>
<h1><?= __h($title) ?></h1>
<p><?= __l('welcome_message') ?></p>

// 在控制器中
__var_dump($data);
__debug_log('User {id} logged in', ['id' => $userId]);
```

> 这些函数均通过 `DuckPhp\Core\CoreHelper` 代理到实际组件，所以在框架初始化完毕后才可用。
