# DuckPhp\Component\Lang

多语言（i18n）组件。

## 简介

`Lang` 组件负责框架的国际化翻译。它根据配置的检测模式自动识别当前语言，从对应的语言文件中加载翻译句子，并支持参数替换。

该组件默认通过 `DuckPhp\DuckPhp` 的 `ext` 选项自动加载。

## 选项

| 选项 | 默认值 | 说明 |
|---|---|---|
| `lang_final` | `null` | 最终语言。如果设置，则跳过自动检测，直接使用该语言。子应用默认会跟随根应用的 `lang_final`。 |
| `lang_default` | `null` | 默认语言。当其他检测方式都失败时回退使用该语言。 |
| `lang_detect_mode` | `['url', 'cookie', 'header', 'cli', 'default']` | 语言检测顺序。可选值：`url`、`cookie`、`header`、`cli`、`default`。 |
| `lang_follow_root` | `true` | 子应用是否跟随根应用的语言设置。为 `true` 时，子应用直接使用根应用的 `lang_final`。 |
| `lang_url_param` | `'lang'` | URL 参数名，用于 `url` 检测模式。例如 `?lang=zh_CN`。 |
| `lang_cookie_name` | `'lang'` | Cookie 名称，用于 `cookie` 检测模式。 |
| `lang_file_path` | `'lang/'` | 语言文件目录（相对于 `config/` 目录）。 |
| `lang_simple_mode_only_sentences` | `[]` | 简单模式句子数组。如果非空，则直接从该数组读取翻译，不加载语言文件。 |

## 语言文件

默认从 `config/lang/{locale}.php` 加载语言配置。文件返回一个关联数组：

```php
<?php
// config/lang/zh_CN.php
return [
    'hello' => '你好',
    'welcome_message' => '欢迎，{name}',
    'user_not_found' => '用户不存在',
];
```

```php
<?php
// config/lang/en_US.php
return [
    'hello' => 'Hello',
    'welcome_message' => 'Welcome, {name}',
    'user_not_found' => 'User not found',
];
```

## 语言代码格式

组件内部会将语言代码统一标准化为 `zh_CN` 格式：

- 将 `-` 替换为 `_`
- 语言代码小写，地区代码大写
- 例如：`zh-cn` → `zh_CN`，`en-us` → `en_US`

## 语言检测顺序

默认按以下顺序检测：

1. **URL 参数**：`?lang=zh_CN`
2. **Cookie**：`$_COOKIE['lang']`
3. **HTTP Header**：`Accept-Language`
4. **CLI 环境变量**：`LANG` / `LC_ALL` / `LC_MESSAGES` / `LANGUAGE`
5. **默认语言**：`lang_default`

可以通过 `lang_detect_mode` 调整顺序或禁用某些检测方式。

## 使用方式

### 全局函数

```php
__l('hello');                              // 输出 '你好'
__l('welcome_message', ['name' => '世界']);  // 输出 '你好，世界'
__l('not_exists');                         // 键不存在，原样返回 'not_exists'

__hl('hello');                             // 国际化 + HTML 转义
```

### 通过 Lang 组件

```php
use DuckPhp\Component\Lang;

$text = Lang::_()->lang('hello');                              // '你好'
$text = Lang::_()->lang('welcome_message', ['name' => '世界']); // '你好，世界'
```

### 在 Controller 中使用

```php
use DuckPhp\Foundation\Controller\Helper;

$message = Helper::lang('welcome_message', ['name' => $userName]);
```

### 在 Business 中使用

```php
use DuckPhp\Foundation\Business\Helper;

$error = Helper::lang('user_not_found');
```

## 配置示例

### 基础配置

```php
class App extends DuckPhp
{
    public $options = [
        'lang_default' => 'zh_CN',
        'lang_detect_mode' => ['url', 'cookie', 'header', 'default'],
    ];
}
```

### 仅使用 URL 参数

```php
class App extends DuckPhp
{
    public $options = [
        'lang_detect_mode' => ['url', 'default'],
        'lang_default' => 'en_US',
    ];
}
```

### 简单模式

如果不使用语言文件，可以直接在配置中定义翻译：

```php
class App extends DuckPhp
{
    public $options = [
        'lang_final' => 'zh_CN',
        'lang_simple_mode_only_sentences' => [
            'zh_CN' => [
                'hello' => '你好',
            ],
            'en_US' => [
                'hello' => 'Hello',
            ],
        ],
    ];
}
```

## 子应用语言设置

默认情况下，子应用会跟随根应用的语言：

```php
class App extends DuckPhp
{
    public $options = [
        'app' => [
            \ApiApp\System\ApiApp::class => [
                'lang_follow_root' => true,  // 默认
            ],
            \ApiApp\System\ApiApp::class => [
                'lang_follow_root' => false,
                'lang_default' => 'en_US',  // 子应用独立语言
            ],
        ],
    ];
}
```

## 注意事项

1. 语言配置在根应用生效。子应用默认跟随根应用，除非设置 `lang_follow_root => false`。
2. 找不到翻译键时，会原样返回传入的字符串，并记录 warning 日志。
3. 语言文件路径相对于 `config/` 目录，默认是 `config/lang/`。
4. 参数替换使用 `{key}` 格式，例如 `'{name}'` 会被替换为 `$args['name']`。

## 全部选项

        'lang_final' => null,
        'lang_default' => null,
        'lang_detect_mode' => ['url', 'cookie','header', 'cli','default'],
        'lang_follow_root' => true,
        'lang_url_param' => 'lang',
        'lang_cookie_name' => 'lang',
        'lang_file_path' => 'lang/',
        'lang_simple_mode_only_sentences' => [],

## 方法列表

### 公共方法

    public function init(array $options, ?object $context = null)
初始化组件，检测并设置最终语言

    public function lang(string $str, array $args = []): string
翻译指定键，支持参数替换。如果找不到翻译，则返回原字符串

### 受保护方法

    protected function getSentenceFromConfig(string $language): ?array
从配置或语言文件加载指定语言的句子。如果 `lang_simple_mode_only_sentences` 非空，则直接从中读取；否则从 `config/lang/{language}.php` 读取

    protected function loadLanguage(string $str): ?string
加载指定键的翻译。找不到时返回 `null`，并记录 warning 日志

    protected function format(string $str, array $args): string
替换 `{key}` 格式的参数

    protected function normalizeLocale(string $locale): string
标准化语言代码为 `xx_XX` 格式。将 `-` 替换为 `_`，语言部分小写，地区部分大写

    protected function detectLanguage(): ?string
按 `lang_detect_mode` 顺序自动检测语言

    protected function detectFromUrl(): ?string
从 URL 参数 `lang_url_param` 检测语言

    protected function detectFromCookie(): ?string
从 Cookie `lang_cookie_name` 检测语言

    protected function detectFromHeader(): ?string
从 `HTTP_ACCEPT_LANGUAGE` 头检测语言，按优先级返回第一个匹配项

    protected function detectFromCli(): ?string
从 CLI 环境变量 `LANG`/`LC_ALL`/`LC_MESSAGES`/`LANGUAGE` 检测语言

    protected function detectFromDefault(): ?string
返回 `lang_default` 配置的语言

## 相关链接

- [DuckPhp\Component\Configer](Component-Configer.md)
- [DuckPhp\Component\Core\Logger](Core-Logger.md)

