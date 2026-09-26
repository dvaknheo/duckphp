# DuckPhp\Component\Lang

多语言（i18n）组件：按可配置策略自动检测当前语言，从语言文件（或内联句子）取翻译并支持 `{key}` 参数替换。

## 简介

`Lang extends ComponentBase` 提供简单而完整的界面翻译：

- `lang_final` 一步到底：非根、且 follow_root 命中时直接用根 final；否则 `detectLanguage()`。
- 检测顺序经 `lang_detect_mode` 列表：`url`（参数）→`cookie`→`header`(Accept-Language)→`cli`(环境)→`default`（lang_default）；
- 取句：先看当前语言的配置（默认由 `Configer` 读 `config/lang-{locale}.php`，或从简单模式脚本 `lang_simple_mode_only_sentences`），再合并各 frag 文件（`lang_frags` 选项或 `loadLanguageFrag()`；同一 key 主文件赢、后加载的 frag 赢），最后回退 `importDefaultSentences()` 注入的默认集；
- `language()` 返回带 `{param}` 替换；`replaceText()`（或 App.langText）处理文本里的 `[[key|fallback]]`。

DuckPhp 默认把 `Lang` 放进 `ext`（DuckPhp.php），因此绝大多数 App 可用全局 `__l`/`__langtext`/Helper 的 `LangText`。

## 类信息

- 命名空间：`DuckPhp\Component`
- 声明：`class Lang extends ComponentBase`

## 选项

`Lang::$options`：

| 选项 | 默认值 | 说明 |
|---|---|---|
| `lang_final` | null | 最终语言，设就别再探测；非根子层 follow root 会取根值。 |
| `lang_default` | null | 兜底：别的方式都不中时返回它。 |
| `lang_detect_mode` | `['url','cookie','header','cli','default']` | 探测顺序与可用入口名单。 |
| `lang_follow_root` | true | 子应用时候跟随根 Final。 |
| `lang_url_param` | `'lang'` | url 探测参数名（例如 `?lang=zh_CN`）。 |
| `lang_cookie_name` | `'lang'` | cookie 名。 |
| `lang_file_path` | `'lang-'` | 语言文件名前缀：`lang-zh_CN.php`（实际由 Configer 拼 `{前缀}{locale}.php`）。老工程要 `config/lang/<locale>.php` 就设回 `'lang/'`。 |
| `lang_frags` | `[]` | 一次性加入的 frag 名列表：`['for_myext1']` 读 `lang-zh_CN-for_myext1.php`。 |
| `lang_simple_mode_only_sentences` | `[]` | 简单模式句子集：语言=>[key=>sentence]。非空则不读任何文件（frag 也不读）直接用它。 |
| `lang_warn_on_missing` | false | 缺句时是否 `Logger::_()->warning("No Language sentence Dectected {key}")`；默认关。 |

## 使用方式

直接经组件：

```php
use DuckPhp\Component\Lang;

Lang::_()->init([
    'lang_default' => 'zh_CN',
    'lang_final'   => 'zh_CN',
]);
echo Lang::_()->language('welcome', ['name' => 'Duck']);   // 欢迎，Duck
echo Lang::_()->replaceText('登录：[[login.fail|失败]]');    // [[…]] 片段转译
```

### 语言文件结构

默认按 locale 存成 `config/lang-zh_CN.php`，返回句子关联数组：

```php
return [
    'welcome' => '欢迎，{name}',
];
```

扩展可以再带一个 frag 文件，文件名是 `{lang_file_path}{locale}-{frag}.php`：

```php
// config/lang-zh_CN-for_myext1.php
return [
    'myext1.title' => '我的扩展',
];

// 两种加入方式等价
Lang::_()->init(['lang_frags' => ['for_myext1']]);
Lang::_()->loadLanguageFrag('for_myext1');
```

- 一个 key 同时出现在主文件和 frag 里：**主文件赢**（应用自己的文案优先）。
- 一个 key 同时出现在多个 frag 里：**后加载的 frag 赢**（`lang_frags` 列表里靠后的、或后调 `loadLanguageFrag()` 的）。
- frag 文件不存在不是错误，跳过；`$default` 参数给的是「这套 frag 的内置句子」，等价于 `importDefaultSentences()`（见 `RouteHookWebInstaller` 的 `config/lang-zh_CN-for_webinstaller.php`）。

### 探测/规范化

- locale 码统一为 `xx_YY`（`-`→`_`，语言小写、地区大写；`zh-cn`→`zh_CN`）。
- 探测顺序可调（`lang_detect_mode`）；CLI 读 `LANG/LC_ALL/…`；header 读 HTTP_ACCEPT_LANGUAGE（q 降序后取第一个）。

## 注意事项

1. final 只在 init 时算一次；lang_follow_root 需 root 已定（先 init 根）。
2. 找不到句子回退 默认集→ `$str`；若有 fallback 参数用之；`language()` 无 fallback 且无 key 时，只有 `lang_warn_on_missing` 打开才会 warning 记录（默认关，不写日志）。
3. 简单模式给语言文件免 filesystem 的场景；开了它就不读任何语言文件，`lang_frags` 一并失效（只有默认集仍生效）。
4. 语言“配置只根生效”逻辑见代码注释——子层跟随根设置需在子 init(其 root 后)。
5. frag 只补主文件没有的 key，别指望用 frag 覆盖主文件里的同 key。

## 方法列表

### 公共方法

    public function importDefaultSentences(array $sentences)
把回退句子合入 default_sentences（当语言层无翻译时用）；返回 this。

    public function loadLanguageFrag(string $filename, array $default = [])
给扩展加一个额外的翻译文件：文件名是 `{lang_file_path}{locale}-{filename}.php`；`$default` 作为内置句子合入默认集（frag 没有对应文件时兜底）。返回 this。`''` 是空操作。

    public function init(array $options, ?object $context = null)
父 init；non-root 且 follow_root 用根 lang_final，否则 detectLanguage 生成自己的 final；并把 final 写回 context options.

    public function language(string $str, array $args = [], ?string $fallback = null): string
主翻译口：loadLanguage 取句后 format（{k} 替换）；不回退原语。

    public function replaceText(string $text, array $args = []): string
把文本中 `[[key|fallback]]`/`[[key]]` 片段语法经 language() 替换。 (实现为逐段 preg_replace_callback——实际调用 language() 并带 fallback)

### 受保护方法

    protected function getSentenceFromConfig(string $language): ?array
简单模式则返回 `lang_simple_mode_only_sentences[$language]`；否则经 Configer 读主文件 `{lang_file_path}{language}.php` 内容。

    protected function getFragSentenceFromConfig(string $language, string $frag): ?array
读一个 frag 文件的句子；文件不存在返回 null。

    protected function getFragNames(): array
汇总 `lang_frags` 选项和 `loadLanguageFrag()` 加进来的 frag 名：去重、允许带 `.php` 后缀、跳过空名。

    protected function getSentences(string $language): array
主文件与各 frag 合并成一张句子表（主文件赢、后加载的 frag 赢）；简单模式直接返回内联句子，不读文件。

    protected function loadLanguage(string $str, ?string $fallback = null): ?string
按 language 查合并后的句子表 → 命中即返回；否则查 default_sentences；language 为 null→返回 fallback；都没命中且 lang_warn_on_missing 打开时记一条 warning。

    protected function format(string $str, array $args): string
`{k}` → $args[k] 替换实现。

    protected function normalizeLocale(string $locale): string
把 zh-cn/zh_CN 规范化成 zh_CN 格式。

    protected function detectLanguage(): ?string
沿 lang_detect_mode 试各 detect*，返回首个非 null 并规范化。

    protected function detectFromUrl(): ?string
取 URL 参数。

    protected function detectFromCookie(): ?string
取 cookie。

    protected function detectFromHeader(): ?string
parse Accept-Language，q 排序后取第一个。

    protected function detectFromCli(): ?string
读 LANG/LC_ALL/LC_MESSAGES/LANGUAGE（去 .UTF-8 尾）。

    protected function detectFromDefault(): ?string
返回 lang_default。

## 相关链接

- [DuckPhp\DuckPhp](DuckPhp.md) —— 默认 ext 启用了 Lang
- [DuckPhp\Component\Configer](Component-Configer.md) —— 读语言文件
- [DuckPhp\Core\Functions](Core-Functions.md) —— `__l/__langtext` 走它
- App::langtext/ format 说明（Core-App/lang）
