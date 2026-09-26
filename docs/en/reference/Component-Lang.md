# DuckPhp\Component\Lang

Internationalization (i18n) component: auto-detects the current language per a configurable strategy, takes translations from language files (or inline sentences), and supports `{key}` parameter replacement.

## Introduction

`Lang extends ComponentBase` provides simple but complete UI translation:

- `lang_final` settles it in one step: non-root with a follow_root hit uses the root final directly; otherwise `detectLanguage()`.
- Detection order follows the `lang_detect_mode` list: `url` (parameter) → `cookie` → `header` (Accept-Language) → `cli` (environment) → `default` (lang_default);
- Sentence lookup: first the current language's config (by default `config/lang-{locale}.php` read via `Configer`, or the simple-mode script `lang_simple_mode_only_sentences`), then the merged frag files (the `lang_frags` option or `loadLanguageFrag()`; the main file wins on the same key, a later-loaded frag wins), finally falling back to the default set injected by `importDefaultSentences()`;
- `language()` returns with `{param}` replacement; `replaceText()` (or App.langText) handles `[[key|fallback]]` in text.

DuckPhp puts `Lang` into `ext` by default (DuckPhp.php), so most Apps can use the global `__l`/`__langtext`/Helper's `LangText`.

## Class info

- Namespace: `DuckPhp\Component`
- Declaration: `class Lang extends ComponentBase`

## Options

`Lang::$options`:

| Option | Default | Description |
|---|---|---|
| `lang_final` | null | The final language; once set, no more detection; a non-root child layer following root takes the root value. |
| `lang_default` | null | Fallback: returned when no other method hits. |
| `lang_detect_mode` | `['url','cookie','header','cli','default']` | Detection order and the list of available entries. |
| `lang_follow_root` | true | Child apps follow the root Final. |
| `lang_url_param` | `'lang'` | URL detection parameter name (e.g. `?lang=zh_CN`). |
| `lang_cookie_name` | `'lang'` | Cookie name. |
| `lang_file_path` | `'lang-'` | Language file name prefix: `lang-zh_CN.php` (actually Configer composes `{prefix}{locale}.php`). Legacy projects needing `config/lang/<locale>.php` set it back to `'lang/'`. |
| `lang_frags` | `[]` | Frag names added in one go: `['for_myext1']` reads `lang-zh_CN-for_myext1.php`. |
| `lang_simple_mode_only_sentences` | `[]` | Simple-mode sentence set: language=>[key=>sentence]. When non-empty no files are read (frags either); it is used directly. |
| `lang_warn_on_missing` | false | Whether to `Logger::_()->warning("No Language sentence Dectected {key}")` on missing sentences; off by default. |

## Usage

Directly via the component:

```php
use DuckPhp\Component\Lang;

Lang::_()->init([
    'lang_default' => 'zh_CN',
    'lang_final'   => 'zh_CN',
]);
echo Lang::_()->language('welcome', ['name' => 'Duck']);   // outputs "欢迎，Duck"
echo Lang::_()->replaceText('登录：[[login.fail|失败]]');    // translates the [[…]] fragment
```

### Language file structure

By default stored per locale as `config/lang-zh_CN.php`, returning an associative array of sentences:

```php
return [
    'welcome' => '欢迎，{name}',
];
```

An extension can ship an additional frag file, named `{lang_file_path}{locale}-{frag}.php`:

```php
// config/lang-zh_CN-for_myext1.php
return [
    'myext1.title' => '我的扩展',
];

// the two ways of adding it are equivalent
Lang::_()->init(['lang_frags' => ['for_myext1']]);
Lang::_()->loadLanguageFrag('for_myext1');
```

- A key appearing in both the main file and a frag: **the main file wins** (the app's own copy takes precedence).
- A key appearing in multiple frags: **the later-loaded frag wins** (the later one in the `lang_frags` list, or the later `loadLanguageFrag()` call).
- A missing frag file is not an error, it is skipped; the `$default` parameter supplies "this frag's built-in sentences", equivalent to `importDefaultSentences()` (see `RouteHookWebInstaller`'s `config/lang-zh_CN-for_webinstaller.php`).

### Detection / normalization

- Locale codes are normalized to `xx_YY` (`-`→`_`, language lower-cased, region upper-cased; `zh-cn`→`zh_CN`).
- The detection order is adjustable (`lang_detect_mode`); CLI reads `LANG/LC_ALL/…`; header reads HTTP_ACCEPT_LANGUAGE (first after sorting by q descending).

## Caveats

1. final is computed only once at init; lang_follow_root needs the root already set (init the root first).
2. When no sentence is found it falls back to the default set → `$str`; if a fallback parameter is given it is used; when `language()` has no fallback and no key, a warning is logged only if `lang_warn_on_missing` is on (off by default, nothing logged).
3. Simple mode is for scenarios where language files must avoid the filesystem; with it on, no language files are read and `lang_frags` is also void (only the default set still applies).
4. For the "language config only takes effect at root" logic see the code comments — a child layer following root settings must init (after its root).
5. A frag only fills in keys the main file lacks; do not expect a frag to override the same key in the main file.

## Methods

### Public methods

    public function importDefaultSentences(array $sentences)
Merges fallback sentences into default_sentences (used when the language layer has no translation); returns this.

    public function loadLanguageFrag(string $filename, array $default = [])
Adds an extra translation file for an extension: the file name is `{lang_file_path}{locale}-{filename}.php`; `$default` is merged into the default set as built-in sentences (fallback when the frag has no corresponding file). Returns this. `''` is a no-op.

    public function init(array $options, ?object $context = null)
Parent init; non-root with follow_root uses the root lang_final, otherwise detectLanguage produces its own final; and writes final back into the context options.

    public function language(string $str, array $args = [], ?string $fallback = null): string
Main translation entry: loadLanguage fetches the sentence, then format ({k} replacement); does not fall back to the original string.

    public function replaceText(string $text, array $args = []): string
Replaces `[[key|fallback]]`/`[[key]]` fragment syntax in the text via language(). (Implemented as segment-wise preg_replace_callback — actually calls language() with fallback)

### Protected methods

    protected function getSentenceFromConfig(string $language): ?array
In simple mode returns `lang_simple_mode_only_sentences[$language]`; otherwise reads the main file `{lang_file_path}{language}.php` via Configer.

    protected function getFragSentenceFromConfig(string $language, string $frag): ?array
Reads the sentences of one frag file; returns null when the file is missing.

    protected function getFragNames(): array
Aggregates frag names from the `lang_frags` option and `loadLanguageFrag()`: dedupes, allows a `.php` suffix, skips empty names.

    protected function getSentences(string $language): array
Merges the main file and all frags into one sentence table (main file wins, later-loaded frag wins); simple mode returns the inline sentences directly without reading files.

    protected function loadLanguage(string $str, ?string $fallback = null): ?string
Looks up the merged sentence table by language → returns on hit; otherwise checks default_sentences; language null → returns fallback; when nothing hits and lang_warn_on_missing is on, logs a warning.

    protected function format(string $str, array $args): string
`{k}` → $args[k] replacement implementation.

    protected function normalizeLocale(string $locale): string
Normalizes zh-cn/zh_CN into the zh_CN form.

    protected function detectLanguage(): ?string
Tries each detect* along lang_detect_mode, returns the first non-null, normalized.

    protected function detectFromUrl(): ?string
Reads the URL parameter.

    protected function detectFromCookie(): ?string
Reads the cookie.

    protected function detectFromHeader(): ?string
Parses Accept-Language, takes the first after q-sorting.

    protected function detectFromCli(): ?string
Reads LANG/LC_ALL/LC_MESSAGES/LANGUAGE (strips the .UTF-8 tail).

    protected function detectFromDefault(): ?string
Returns lang_default.

## Related links

- [DuckPhp\DuckPhp](DuckPhp.md) — the default ext enables Lang
- [DuckPhp\Component\Configer](Component-Configer.md) — reads the language files
- [DuckPhp\Core\Functions](Core-Functions.md) — `__l/__langtext` go through it
- App::langtext / format notes (Core-App/lang)
