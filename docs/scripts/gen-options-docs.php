<?php declare(strict_types=1);
/**
 * gen-options-docs.php - generator for the option-related summary pages of the
 * reference manual (docs/<lang>/reference).
 *
 * Outputs (all of them live under docs/<lang>/reference/):
 *   options-by-class.md   fully generated   app options grouped by declaring class
 *   options-index.md      fully generated   app options as an A-Z index
 *   index.md              block generated   manual index page (stats / nav / A-Z of the docs)
 *   options.md            block generated   options home page (layer cheat sheet / hidden / setting)
 *   setting.md            block generated   Setting() mechanism page (setting keys / setting options)
 *
 * Three sources of truth:
 *   1. src/**\/*.php      declared options ($options/$core_options/$kernel_options/$common_options),
 *                         hidden options ($hidden_options) and their default literals
 *   2. the per-class reference pages   option descriptions (their "## 选项" / "## Options" table)
 *   3. the two tables in this file     descriptions for hidden options + setting keys
 *                                      (they have no per-class option table to read from)
 *
 * Language: --lang=zh (default) writes the Chinese tree, --lang=en the English one.
 * The Chinese tree is authoritative; the English one is generated from the very same
 * src/ facts, with descriptions read from the English per-class pages. Every literal
 * lives in the LANG_PACK table below, so the two trees can not drift apart in shape.
 *
 * Block mode: a mixed page keeps its human prose; only the text between
 *   <!-- GEN:<name> start -->  and  <!-- GEN:<name> end -->  is rewritten.
 * When such a page does not exist yet, a skeleton with the markers is created.
 *
 * Usage:
 *   php docs/scripts/gen-options-docs.php                 write the pages (zh)
 *   php docs/scripts/gen-options-docs.php --lang=en       write the English pages
 *   php docs/scripts/gen-options-docs.php --check         verify only; exit 1 when something is stale
 *   php docs/scripts/gen-options-docs.php --json          dump the gathered facts
 *
 * Note: no timestamps are written anywhere, so re-running is byte-identical (idempotent).
 * English comments on purpose (same style as docs/scripts/gen-reference.php); the generated
 * pages themselves are Chinese or English depending on --lang.
 */

const ROOT = __DIR__ . '/../..';
const SRC_DIR = ROOT . '/src';

const OPT_VARS = ['options', 'core_options', 'kernel_options', 'common_options'];
const HIDDEN_VAR = 'hidden_options';

/** the languages this generator speaks, in the order --lang accepts them */
const LANGS = ['zh', 'en'];

/** the active language, from --lang=xx (default zh) */
define('LANG', (function () {
    foreach (($GLOBALS['argv'] ?? []) as $a) {
        if (preg_match('~^--lang=(.+)$~', (string) $a, $m) && in_array($m[1], LANGS, true)) {
            return $m[1];
        }
    }
    return 'zh';
})());

/** the reference tree of the active language */
function ref_dir(): string
{
    return ROOT . '/docs/' . LANG . '/reference';
}

/** one literal of the active language pack (a string, or an array of rows) */
function L(string $key)
{
    return LANG_PACK[LANG][$key];
}

/**
 * Documentation of the hidden options, per language (source: the $hidden_options tables in src/).
 * key => [where it is read, description]
 * A key missing here is reported as an error; a key here that src no longer lists
 * is reported as a warning, so the two can not drift apart silently.
 */
const HIDDEN_DESC = [
    'zh' => [
        'session_prefix' => ['Foundation\\Controller\\SessionTrait', '会话名的前缀（根应用的设置也走这里）。'],
        'table_prefix' => ['Ext\\SqlDumper / Ext\\RouteHookWebInstaller', '数据库表名前缀，导出/安装 SQL 时用 `{prefix}` 占位替换。'],
        'exception_for_business' => ['CoreHelper::_BusinessThrowOn()', '`BusinessThrowOn()` 未显式指定时的异常类。'],
        'exception_for_controller' => ['CoreHelper::_ControllerThrowOn()', '`ControllerThrowOn()` 未显式指定时的异常类。'],
        'duckphp_all_in_one_wrap_header_footer' => ['DuckPhpAllInOne::onInited()', 'AllInOne 入口是否给 `_Show()` 包页眉/页脚视图（该类自己会置 true）。'],
        'permission_menu_tree_for_admin' => ['Ext\\PermissionMenu::getMenuJsonFileConfig()', '后台权限菜单树的配置文件（相对 `path_config`）。'],
        'duckcoverage_test_lister' => ['外部包 dvaknheo/duckcoverage', '配合该 composer 包做覆盖测试使用，框架自身不读。'],
        'not_empty' => ['DuckPhp::$common_options', '声明在默认选项里、但源码中没有任何读取点（历史遗留，可忽略）。'],
        'url_admin_home' => ['GlobalAdmin\\Admin::urlForHome()', '后台首页 URL 的「应用级」覆盖：优先于组件的 `globaladmin_url_home`。'],
        'url_user_home' => ['GlobalUser\\User::urlForHome()', '站内首页 URL 的「应用级」覆盖：优先于组件的 `globaluser_url_home`。'],
        'background' => ['HttpServer::run*()', '内置服务器是否后台运行；CLI 开关 `-b/--background` 会把它置 true。'],
    ],
    'en' => [
        'session_prefix' => ['Foundation\\Controller\\SessionTrait', 'The session-name prefix (the root application\'s setting goes through here too).'],
        'table_prefix' => ['Ext\\SqlDumper / Ext\\RouteHookWebInstaller', 'The database table-name prefix; the `{prefix}` placeholder stands for it when SQL is exported/installed.'],
        'exception_for_business' => ['CoreHelper::_BusinessThrowOn()', 'The exception class used when `BusinessThrowOn()` does not name one explicitly.'],
        'exception_for_controller' => ['CoreHelper::_ControllerThrowOn()', 'The exception class used when `ControllerThrowOn()` does not name one explicitly.'],
        'duckphp_all_in_one_wrap_header_footer' => ['DuckPhpAllInOne::onInited()', 'Whether the AllInOne entry wraps `_Show()` in header/footer views (that class sets it true itself).'],
        'permission_menu_tree_for_admin' => ['Ext\\PermissionMenu::getMenuJsonFileConfig()', 'The configuration file of the back-office permission menu tree (relative to `path_config`).'],
        'duckcoverage_test_lister' => ['external package dvaknheo/duckcoverage', 'Used with that composer package for coverage tests; the framework itself never reads it.'],
        'not_empty' => ['DuckPhp::$common_options', 'Declared among the default options but read nowhere in the source (a historical leftover; ignore it).'],
        'url_admin_home' => ['GlobalAdmin\\Admin::urlForHome()', 'The "application-level" override of the back-office home URL: it wins over the component\'s `globaladmin_url_home`.'],
        'url_user_home' => ['GlobalUser\\User::urlForHome()', 'The "application-level" override of the in-site home URL: it wins over the component\'s `globaluser_url_home`.'],
        'background' => ['HttpServer::run*()', 'Whether the built-in server runs in the background; the CLI switch `-b/--background` sets it true.'],
    ],
];

/**
 * Setting keys, per language: not options, they live in the setting file / .env and are read
 * with Setting().  key => [who reads it, description]
 */
const SETTING_KEYS = [
    'zh' => [
        'duckphp_is_debug' => ['Core\\App::_IsDebug()', '调试开关；与选项 `is_debug` 取或。'],
        'duckphp_platform' => ['Core\\App::_Platform()', '平台标识，由 App::Platform() 读出。'],
        'duckphp_is_maintain' => ['Core\\App::prepareServe()', '维护模式；与选项 `is_maintain` 取或，为真时直接输出维护页。'],
        'database_list' => ['Component\\DbManager', '数据库连接列表（`dsn/username/password/driver_options`）；`database_list_reload_by_setting` 与「选项未给」共同决定是否采用。'],
        'database' => ['Component\\DbManager', '单个数据库连接的简写，`database_list_try_single` 为真时生效。'],
        'redis_list' => ['Component\\RedisManager', 'Redis 连接列表；`redis_list_reload_by_setting` 与「选项未给」共同决定是否采用。'],
        'redis' => ['Component\\RedisManager / Ext\\RouteHookWebInstaller', '单个 Redis 连接的简写，`redis_list_try_single` 为真时生效。'],
    ],
    'en' => [
        'duckphp_is_debug' => ['Core\\App::_IsDebug()', 'The debug switch; OR-ed with the `is_debug` option.'],
        'duckphp_platform' => ['Core\\App::_Platform()', 'The platform marker, read out by App::Platform().'],
        'duckphp_is_maintain' => ['Core\\App::prepareServe()', 'Maintenance mode; OR-ed with the `is_maintain` option, and when truthy the maintenance page is output straight away.'],
        'database_list' => ['Component\\DbManager', 'The database connection list (`dsn/username/password/driver_options`); `database_list_reload_by_setting` together with "the option was not given" decides whether it is used.'],
        'database' => ['Component\\DbManager', 'A shorthand for a single database connection; it takes effect when `database_list_try_single` is true.'],
        'redis_list' => ['Component\\RedisManager', 'The Redis connection list; `redis_list_reload_by_setting` together with "the option was not given" decides whether it is used.'],
        'redis' => ['Component\\RedisManager / Ext\\RouteHookWebInstaller', 'A shorthand for a single Redis connection; it takes effect when `redis_list_try_single` is true.'],
    ],
];

/** src/<dir> => [order, layer title (per language), section anchor] */
const LAYERS = [
    '' => [0, ['zh' => '入口类', 'en' => 'Entry class'], 'entry'],
    'Core' => [1, ['zh' => '核心', 'en' => 'Core'], 'core'],
    'Component' => [2, ['zh' => '自带组件', 'en' => 'Built-in components'], 'component'],
    'Ext' => [3, ['zh' => '可选扩展', 'en' => 'Optional extensions'], 'ext'],
    'Db' => [4, ['zh' => '数据库', 'en' => 'Database'], 'db'],
    'HttpServer' => [5, ['zh' => 'HTTP 服务器', 'en' => 'HTTP server'], 'httpserver'],
    'Helper' => [6, ['zh' => '助手', 'en' => 'Helper'], 'helper'],
    'Foundation' => [7, ['zh' => '基础骨架', 'en' => 'Foundation skeleton'], 'foundation'],
    'GlobalAdmin' => [8, ['zh' => '管理员系统', 'en' => 'Admin system'], 'admin'],
    'GlobalUser' => [9, ['zh' => '用户系统', 'en' => 'User system'], 'user'],
];

/** the 5 App options that drive the setting mechanism, in source order */
const SETTING_OPTIONS = ['setting', 'setting_file', 'setting_file_enable', 'setting_file_ignore_exists', 'use_env_file'];

// ---------------------------------------------------------------------------
// language packs: every literal the renderers emit lives here
// ---------------------------------------------------------------------------

const LANG_PACK = [
    'zh' => [
        'gen_note' => '> 本页由 `docs/scripts/gen-options-docs.php` 生成，**请勿手改**：改选项请改 `src/` 与对应类文档，然后重跑生成器。',

        'by_class_title' => '应用选项（按类分组）',
        'by_class_intro' => "选项来自各类的 `\$options` / `\$core_options` / `\$kernel_options` / `\$common_options`；**默认值取自源码**，说明取自该类的参考文档。共 **%d** 个类、**%d** 个选项（同名选项在不同类各自声明，合计 %d 处；另有 %d 个隐藏选项见文末）。\n\n",
        'by_class_hint' => "想按名字找？看 [应用选项（按字母顺序索引）](options-index.md)；选项机制见 [应用选项总览](options.md)。\n\n---\n\n",
        'class_doc_link' => '类文档：',
        'th_option_default_desc' => "| 选项 | 默认值 | 说明 |\n|---|---|---|\n",
        'th_option_default_source_desc' => "| 选项 | 默认值 | 出处 | 说明 |\n|---|---|---|---|\n",
        'default_from_doc' => ' ⇐文档',
        'no_desc' => '（该类文档未写说明）',
        'hidden_title' => '隐藏选项',
        'hidden_intro' => "框架会读、但**故意不写进 `\$options`** 的键（源码里集中在 `\$hidden_options` 表）。它们可以像普通选项一样在 `init()`/`\$options` 里给出，只是不出现在上面的正式选项里。\n\n",
        'todo_desc' => '（待补说明）',

        'index_title' => '应用选项（按字母顺序索引）',
        'index_intro' => "一共 **%d** 个选项；同名选项出现在多个类时**合并为一行**，来源类并列。隐藏选项见 [按类分组](options-by-class.md#隐藏选项) 文末（本页只收正式选项）。\n\n",
        'index_hint' => "按类查看：[应用选项（按类分组）](options-by-class.md) · 选项机制：[应用选项总览](options.md)\n\n",
        'jump_bar' => '**跳转**：',
        'th_option_default_sources_desc' => "| 选项 | 默认值 | 来源类 | 说明 |\n|---|---|---|---|\n",
        'no_desc_short' => '（类文档未写说明）',
        'prefix_title' => "## 按前缀分组（便于成组记忆）\n\n",
        'prefix_item' => "- **`%s*`**（%d）：`%s`\n",
        'prefix_sep' => '`、`',

        'related_title' => '相关链接',
        'link_index' => '- [应用选项（按字母顺序索引）](options-index.md)',
        'link_by_class' => '- [应用选项（按类分组）](options-by-class.md)',
        'link_options' => '- [应用选项总览（首页）](options.md)',
        'link_setting' => '- [应用设置 Setting](setting.md)',

        'az_header' => "| 类 | 一句话 |\n|---|---|\n",
        'dash' => '—',
        'nav_header' => "| 类 | 说明 |\n|---|---|\n",
        'nav_groups' => ['入口类', '核心类', '组件', '扩展', '数据库', 'HTTP 服务器', '助手', '管理员系统', '用户系统'],
        'stats_header' => "| 项目 | 数量 |\n|---|---|\n",
        'stats_docs' => '逐类参考页',
        'stats_docs_unit' => ' 篇',
        'stats_class' => '声明了选项的类',
        'stats_class_unit' => ' 个',
        'stats_keys' => '应用选项（去重后）',
        'stats_keys_unit' => ' 个',
        'stats_hidden' => '应用选项（隐藏）',
        'stats_hidden_unit' => ' 个',
        'layers_header' => "| 层 | 内容 |\n|---|---|\n",
        'layers_rows' => [
            ['`DuckPhp\\Core\\KernelTrait::$kernel_options`', '应用骨架：path/namespace/app/cmd/ext/cli_enable/on_* 等'],
            ['`DuckPhp\\Core\\App::$core_options`', '核心：path_runtime、path_config、setting*、error_*、exception_map…'],
            ['`DuckPhp::$common_options`', '入口类默认：`ext` 默认组件表、provider、lang_*、data_file_*…'],
            ['各组件自己的 `$options`', '组件被 init 时用自己的白名单合并，见 [按类分组](options-by-class.md)'],
            ['`ext` 表装载的扩展', '`类 => true/数组/\'选项键\'/EXT_* 常量`；字符串值是**选项键名**'],
            ['`init($options)` 运行时传入', '最后合并，**优先级最高**（`KernelTrait::initOptions()` 直接 `array_replace_recursive`）'],
            ['设置文件 / `.env`', '不进 `$options`，用 `Setting()` 读，见 [应用设置](setting.md)'],
            ['数据文件（`ExtOptionsLoader`）', '运行时可改的选项落在 `runtime/DuckPhpData.config.json`'],
        ],
        'settingkeys_header' => "| 设置键 | 谁在读 | 作用 |\n|---|---|---|\n",
        'not_declared' => '[未声明]',
    ],
    'en' => [
        'gen_note' => '> This page is generated by `docs/scripts/gen-options-docs.php` — **do not edit it by hand**: to change an option, change `src/` and the matching class page, then re-run the generator.',

        'by_class_title' => 'Application options (by class)',
        'by_class_intro' => "The options come from each class's `\$options` / `\$core_options` / `\$kernel_options` / `\$common_options`; **the defaults are taken from the source** and the descriptions from that class's reference page. **%d** classes and **%d** options in total (a repeated name is declared separately in each class, %d declarations altogether; %d hidden options are listed at the end).\n\n",
        'by_class_hint' => "Looking one up by name? See [Application options (A-Z index)](options-index.md); for how options work see [Application options overview](options.md).\n\n---\n\n",
        'class_doc_link' => 'Class page: ',
        'th_option_default_desc' => "| Option | Default | Description |\n|---|---|---|\n",
        'th_option_default_source_desc' => "| Option | Default | Where it is read | Description |\n|---|---|---|---|\n",
        'default_from_doc' => ' ⇐docs',
        'no_desc' => '(no description on that class page)',
        'hidden_title' => 'Hidden options',
        'hidden_intro' => "Keys the framework reads but **deliberately keeps out of `\$options`** (collected in the `\$hidden_options` tables in the source). They can be given like ordinary options, in `init()`/`\$options`; they simply do not appear among the formal options above.\n\n",
        'todo_desc' => '(description still to be written)',

        'index_title' => 'Application options (A-Z index)',
        'index_intro' => "**%d** options in total; when the same name appears in several classes it is **merged into one row** with the source classes side by side. The hidden options are at the end of [by class](options-by-class.md#hidden-options) (this page lists the formal options only).\n\n",
        'index_hint' => "By class: [Application options (by class)](options-by-class.md) · How options work: [Application options overview](options.md)\n\n",
        'jump_bar' => '**Jump to**: ',
        'th_option_default_sources_desc' => "| Option | Default | Source classes | Description |\n|---|---|---|---|\n",
        'no_desc_short' => '(no description on the class page)',
        'prefix_title' => "## Grouped by prefix (easier to remember as a group)\n\n",
        'prefix_item' => "- **`%s*`** (%d): `%s`\n",
        'prefix_sep' => '`, `',

        'related_title' => 'Related links',
        'link_index' => '- [Application options (A-Z index)](options-index.md)',
        'link_by_class' => '- [Application options (by class)](options-by-class.md)',
        'link_options' => '- [Application options overview (home)](options.md)',
        'link_setting' => '- [Application settings (Setting)](setting.md)',

        'az_header' => "| Class | One-liner |\n|---|---|\n",
        'dash' => '—',
        'nav_header' => "| Class | Description |\n|---|---|\n",
        'nav_groups' => ['Entry classes', 'Core classes', 'Components', 'Extensions', 'Database', 'HTTP server', 'Helpers', 'Admin system', 'User system'],
        'stats_header' => "| Item | Count |\n|---|---|\n",
        'stats_docs' => 'Per-class reference pages',
        'stats_docs_unit' => ' pages',
        'stats_class' => 'Classes declaring options',
        'stats_class_unit' => ' classes',
        'stats_keys' => 'Application options (de-duplicated)',
        'stats_keys_unit' => ' options',
        'stats_hidden' => 'Application options (hidden)',
        'stats_hidden_unit' => ' options',
        'layers_header' => "| Layer | Content |\n|---|---|\n",
        'layers_rows' => [
            ['`DuckPhp\\Core\\KernelTrait::$kernel_options`', 'The application skeleton: path/namespace/app/cmd/ext/cli_enable/on_* and so on'],
            ['`DuckPhp\\Core\\App::$core_options`', 'Core: path_runtime, path_config, setting*, error_*, exception_map…'],
            ['`DuckPhp::$common_options`', 'Entry-class defaults: the default `ext` component table, provider, lang_*, data_file_*…'],
            ['Each component\'s own `$options`', 'A component merges with its own whitelist when it is init\'ed; see [by class](options-by-class.md)'],
            ['Extensions mounted by the `ext` table', '`class => true/array/\'option key\'/EXT_* constant`; a string value is an **option key name**'],
            ['`init($options)` at runtime', 'Merged last, **highest priority** (`KernelTrait::initOptions()` is a plain `array_replace_recursive`)'],
            ['Setting file / `.env`', 'Not part of `$options`; read with `Setting()`, see [Application settings](setting.md)'],
            ['The data file (`ExtOptionsLoader`)', 'Runtime-changeable options land in `runtime/DuckPhpData.config.json`'],
        ],
        'settingkeys_header' => "| Setting key | Who reads it | What it does |\n|---|---|---|\n",
        'not_declared' => '[not declared]',
    ],
];

// ---------------------------------------------------------------------------
// small helpers
// ---------------------------------------------------------------------------

/** all files under $dir ending with $ext, as absolute paths */
function files_under(string $dir, string $ext = '.php'): array
{
    $out = [];
    if (!is_dir($dir)) {
        return $out;
    }
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
    foreach ($it as $f) {
        if (substr((string) $f, -strlen($ext)) === $ext) {
            $out[] = (string) $f;
        }
    }
    sort($out);
    return $out;
}

/** top level keys of an array literal; value may be empty (multi line array) */
function array_pairs(string $block): array
{
    $pairs = [];
    $lines = explode("\n", $block);
    for ($i = 0; $i < count($lines); $i++) {
        $line = $lines[$i];
        $s = trim($line);
        if ($s === '' || substr($s, 0, 2) === '//' || substr($s, 0, 1) === '*') {
            continue;
        }
        if (strlen($line) - strlen(ltrim($line)) > 8) {
            continue;                       // nested array entries
        }
        if (!preg_match("/^'([^']+)'\s*=>\s*(.*)$/", $s, $m)) {
            continue;
        }
        $key = $m[1];
        $val = preg_replace('~\s*//.*$~', '', $m[2]);     // strip line comment
        $val = trim((string) $val);
        $val = rtrim($val, ',');
        if ($val === '' || $val === '[') {
            // multi line array: render as [...] unless it closes on the same line
            $val = ($val === '[') ? '[...]' : '[';
            if ($val === '[') {
                $val = '[...]';
            }
        }
        $pairs[$key] = $val;
    }
    return $pairs;
}

/** read "$var = [ ... ];" (ends at a line that is only "];") */
function options_of(string $php, string $var): array
{
    if (!preg_match('/\$' . preg_quote($var, '/') . '\s*=\s*\[(.*?)\n\s*\];/s', $php, $m)) {
        return [];
    }
    return array_pairs($m[1]);
}

/** hidden option table: key => [default literal, note] ; note carries "@used-by <pkg>" */
function hidden_of(string $php): array
{
    if (!preg_match('/\$' . HIDDEN_VAR . '\s*=\s*\[(.*?)\n\s*\];/s', $php, $m)) {
        return [];
    }
    $out = [];
    $pending = '';
    foreach (explode("\n", $m[1]) as $line) {
        $s = trim($line);
        if (substr($s, 0, 2) === '//' || substr($s, 0, 1) === '*') {
            if (preg_match('/@used-by\s+([^\s（(]+)/', $s, $um)) {
                $pending = 'external:' . $um[1];
            }
            continue;
        }
        if (strlen($line) - strlen(ltrim($line)) > 8) {
            continue;
        }
        if (!preg_match("/^'([^']+)'\s*=>\s*(.*)$/", $s, $mm)) {
            continue;
        }
        $val = (string) preg_replace('~\s*//.*$~', '', $mm[2]);
        $val = rtrim(trim($val), ',');
        $note = $pending;
        if (preg_match('/@used-by\s+([^\s（(]+)/', $s, $um)) {
            $note = 'external:' . $um[1];
        }
        $pending = '';
        $out[$mm[1]] = [$val, $note];
    }
    return $out;
}

/** doc page path for a src file, same mapping rule as the drift scanner */
function md_for(string $src_rel): string
{
    $rel = str_replace('\\', '/', $src_rel);
    $rel = preg_replace('~^src/~', '', $rel);
    return str_replace('/', '-', substr($rel, 0, -4)) . '.md';
}

/** concise brief of a page: an explicit "<!-- brief: ... -->" wins, else the intro */
function short_role(string $md_text): string
{
    if (preg_match('~<!--\s*brief:\s*(.*?)\s*-->~s', $md_text, $bm)) {
        return trim($bm[1]);
    }
    $lang = LANG;
    $section = $lang === 'en' ? 'Introduction' : '简介';
    if (!preg_match('/\n## ' . preg_quote($section, '/') . '\n(.*?)(?=\n## |\Z)/s', $md_text, $m)) {
        return '';
    }
    $txt = preg_replace('~```.*?```~s', '', $m[1]);
    $txt = str_replace(["\r", "\n"], ' ', (string) $txt);
    $txt = preg_replace('~`([^`]*)`~', '$1', (string) $txt);
    $txt = preg_replace('~\[([^\]]*)\]\([^)]*\)~', '$1', (string) $txt);
    $txt = trim(preg_replace('~\s+~', ' ', (string) $txt));

    if ($lang === 'en') {
        // first sentence; the English pages are plain prose, so "." is enough
        $sentence = $txt;
        if (preg_match('/^(.*?\.)(?:\s|$)/', $txt, $sm)) {
            $sentence = $sm[1];
        }
        $candidate = $sentence;
        if (mb_strlen($candidate, 'UTF-8') > 60) {
            $candidate = mb_substr($candidate, 0, 59, 'UTF-8');
            $candidate = preg_replace('/\s+\S*$/', '', $candidate);
            $candidate .= '…';
        }
        return $candidate;
    }

    $sentence = $txt;
    $pos = mb_strpos($sentence, '。', 0, 'UTF-8');
    if ($pos !== false) {
        $sentence = mb_substr($sentence, 0, $pos + 1, 'UTF-8');
    }

    // "`Foo` 是 xxx：yyy。" -> "xxx"   (keep the nav tables crisp)
    $candidate = $sentence;
    foreach (['：', ':'] as $sep) {
        $p = mb_strpos($candidate, $sep, 0, 'UTF-8');
        if ($p !== false) {
            $candidate = mb_substr($candidate, 0, $p, 'UTF-8');
            break;
        }
    }
    $candidate = preg_replace('/^\S+\s*(?:是|位于)\s*/u', '', $candidate);
    // never use trim() with a multibyte charlist here: it strips bytes, not characters
    $candidate = (string) preg_replace('/^\s+|\s+$/u', '', (string) $candidate);
    $candidate = (string) preg_replace('/。$/u', '', $candidate);
    // a bare "X 位于 Y" style sentence carries no information: prefer the full first sentence
    if (mb_strpos($candidate, '位于', 0, 'UTF-8') !== false || mb_strlen($candidate, 'UTF-8') < 6) {
        $candidate = $sentence;
    }
    if (mb_strlen($candidate, 'UTF-8') > 40) {
        $candidate = mb_substr($candidate, 0, 39, 'UTF-8');
        // drop a dangling trailing clause ("…，而是" / "…，并且") before the ellipsis
        $candidate = preg_replace('/[，,]\s*[而并因以和及但就则且或]\S*$/u', '', $candidate);
        $candidate .= '…';
    }
    return $candidate;
}

/** option rows of a class page: "## 选项" (zh) / "## Options" (en) table => key => [default, desc] */
function doc_option_rows(string $md_text): array
{
    $rows = [];
    $section = LANG === 'en' ? 'Options' : '选项';
    if (!preg_match('/\n## ' . preg_quote($section, '/') . '\n(.*?)(?=\n## |\Z)/s', $md_text, $m)) {
        return $rows;
    }
    foreach (explode("\n", $m[1]) as $line) {
        if (!preg_match('/^\|\s*`([^`]+)`\s*\|\s*(.*?)\s*\|\s*(.*?)\s*\|\s*$/', $line, $mm)) {
            continue;
        }
        $rows[$mm[1]] = [$mm[2], $mm[3]];
    }
    return $rows;
}

// ---------------------------------------------------------------------------
// gather facts
// ---------------------------------------------------------------------------

/** @return array{classes:array,declared:array,hidden:array,hidden_extra:array} */
function gather(): array
{
    $classes = [];
    $declared_all = [];
    $hidden_all = [];
    $hidden_src_keys = [];

    foreach (files_under(SRC_DIR) as $abs) {
        $src_rel = str_replace('\\', '/', substr($abs, strlen(ROOT) + 1));
        $php = (string) file_get_contents($abs);
        if (!preg_match('/^namespace\s+([^;]+);/m', $php, $nsm)) {
            continue;
        }
        $ns = trim($nsm[1]);
        if (!preg_match('/^(?:final\s+|abstract\s+)?(?:class|trait)\s+(\w+)/m', $php, $cm)) {
            continue;
        }
        $fqcn = $ns . '\\' . $cm[1];

        $declared = [];
        foreach (OPT_VARS as $var) {
            foreach (options_of($php, $var) as $k => $v) {
                $declared[$k] = [$v, $var];
            }
        }
        $hidden = hidden_of($php);
        if (!$declared && !$hidden) {
            continue;
        }

        $md = md_for($src_rel);
        $md_path = ref_dir() . '/' . $md;
        $role = '';
        $rows = [];
        if (is_file($md_path)) {
            $md_text = (string) file_get_contents($md_path);
            $role = short_role($md_text);
            $rows = doc_option_rows($md_text);
        }

        $dir = dirname($src_rel);
        $dir = preg_replace('~^src/?~', '', $dir);
        $dir = ($dir === '.' || $dir === '') ? '' : $dir;
        $layer = LAYERS[$dir] ?? [99, ['zh' => $dir, 'en' => $dir], 'other'];

        $classes[$fqcn] = [
            'file' => $src_rel,
            'md' => is_file($md_path) ? $md : '',
            'role' => $role,
            'layer' => [$layer[0], $layer[1][LANG], $layer[2]],
            'declared' => $declared,
            'doc_rows' => $rows,
            'hidden' => $hidden,
        ];
        foreach ($declared as $k => $dv) {
            $declared_all[$k][] = $fqcn;
        }
        foreach ($hidden as $k => $dv) {
            $hidden_all[$k][] = $fqcn;
            $hidden_src_keys[$k] = true;
        }
    }
    ksort($classes);

    // cross check the hidden option documentation table
    $hidden_extra = array_values(array_diff(array_keys(HIDDEN_DESC[LANG]), array_keys($hidden_src_keys)));
    return [
        'classes' => $classes,
        'declared' => $declared_all,
        'hidden' => $hidden_all,
        'hidden_extra' => $hidden_extra,
    ];
}

// ---------------------------------------------------------------------------
// renderers
// ---------------------------------------------------------------------------

function link_class(string $fqcn, array $info): string
{
    return $info['md'] !== '' ? '[' . $fqcn . '](' . $info['md'] . ')' : $fqcn;
}

function render_by_class(array $g): string
{
    $classes = $g['classes'];
    $by_layer = [];
    foreach ($classes as $fqcn => $info) {
        if (!$info['declared']) {
            continue;
        }
        $by_layer[$info['layer'][0]][] = $fqcn;
    }
    ksort($by_layer);

    $n_class = 0;
    $n_decl = 0;
    foreach ($by_layer as $list) {
        $n_class += count($list);
        foreach ($list as $fqcn) {
            $n_decl += count($classes[$fqcn]['declared']);
        }
    }
    $n_key = count($g['declared']);
    $hidden_n = count($g['hidden']);

    $L = LANG_PACK[LANG];
    $out = '# ' . L('by_class_title') . "\n\n" . L('gen_note') . "\n\n";
    $out .= sprintf(L('by_class_intro'), $n_class, $n_key, $n_decl, $hidden_n);
    $out .= L('by_class_hint');

    foreach ($by_layer as $order => $list) {
        $title = $classes[$list[0]]['layer'][1];
        $out .= '## ' . $title . "\n\n";
        foreach ($list as $fqcn) {
            $info = $classes[$fqcn];
            $out .= '### ' . $fqcn . "\n\n";
            if ($info['role'] !== '') {
                $out .= $info['role'] . "\n";
            }
            if ($info['md'] !== '') {
                $out .= "\n" . L('class_doc_link') . '[' . $fqcn . '](' . $info['md'] . ")\n";
            }
            $out .= "\n" . L('th_option_default_desc');
            foreach ($info['declared'] as $key => $dv) {
                $rows = $info['doc_rows'];
                $desc = $rows[$key][1] ?? '';
                $default = $dv[0];
                if (isset($rows[$key][0]) && $rows[$key][0] !== '' && strpos($rows[$key][0], $default) === false
                    && strpos($default, '[') === false) {
                    $default = $rows[$key][0] . L('default_from_doc');
                }
                $out .= '| `' . $key . '` | `' . $default . '` | ' . ($desc !== '' ? $desc : L('no_desc')) . " |\n";
            }
            $out .= "\n";
        }
    }

    // hidden options
    $out .= '## ' . L('hidden_title') . "\n\n";
    $out .= L('hidden_intro');
    $out .= L('th_option_default_source_desc');
    $out .= render_hidden_rows($g);
    $out .= "\n## " . L('related_title') . "\n\n";
    $out .= L('link_index') . "\n" . L('link_options') . "\n" . L('link_setting') . "\n";
    return $out;
}

/** the body rows of the hidden-option table (shared by two pages) */
function render_hidden_rows(array $g): string
{
    $out = '';
    $desc_table = HIDDEN_DESC[LANG];
    foreach ($g['hidden'] as $key => $owners) {
        $default = '';
        $note = '';
        foreach ($owners as $fqcn) {
            $h = $g['classes'][$fqcn]['hidden'][$key] ?? null;
            if ($h !== null) {
                $default = $h[0];
                $note = $h[1] ?? '';
            }
        }
        $where = $desc_table[$key][0] ?? implode(' / ', $owners);
        $desc = $desc_table[$key][1] ?? '';
        if ($desc === '' && $note !== '') {
            $desc = $note;
        }
        $out .= '| `' . $key . '` | `' . $default . '` | ' . $where . ' | ' . ($desc !== '' ? $desc : L('todo_desc')) . " |\n";
    }
    return $out;
}

function render_options_index(array $g): string
{
    $classes = $g['classes'];
    $keys = array_keys($g['declared']);
    sort($keys, SORT_STRING);

    $out = '# ' . L('index_title') . "\n\n" . L('gen_note') . "\n\n";
    $out .= sprintf(L('index_intro'), count($keys));
    $out .= L('index_hint');

    // letter bar
    $letters = [];
    foreach ($keys as $k) {
        $c = strtoupper(substr($k, 0, 1));
        $letters[$c] = true;
    }
    $bar = [];
    foreach (array_keys($letters) as $c) {
        $bar[] = '[' . $c . '](#' . strtolower($c) . ')';
    }
    $out .= L('jump_bar') . implode(' · ', $bar) . "\n\n---\n\n";

    $current = '';
    foreach ($keys as $key) {
        $letter = strtoupper(substr($key, 0, 1));
        if ($letter !== $current) {
            if ($current !== '') {
                $out .= "\n";
            }
            $current = $letter;
            $out .= '## ' . strtolower($letter) . "\n\n";
            $out .= L('th_option_default_sources_desc');
        }
        $owners = $g['declared'][$key];
        $sources = [];
        $default = '';
        $desc = '';
        foreach ($owners as $fqcn) {
            $info = $classes[$fqcn];
            $sources[] = link_class($fqcn, $info);
            $default = $default === '' ? $info['declared'][$key][0] : $default;
            if ($desc === '') {
                $desc = $info['doc_rows'][$key][1] ?? '';
            }
        }
        $out .= '| `' . $key . '` | `' . $default . '` | ' . implode(' / ', $sources) . ' | '
            . ($desc !== '' ? $desc : L('no_desc_short')) . " |\n";
    }

    // prefix groups
    $groups = [];
    foreach ($keys as $k) {
        $pos = strpos($k, '_');
        if ($pos === false || $pos === 0) {
            continue;
        }
        $groups[substr($k, 0, $pos + 1)][] = $k;
    }
    $groups = array_filter($groups, function ($v) {
        return count($v) >= 3;
    });
    ksort($groups);
    if ($groups) {
        $out .= "\n---\n\n" . L('prefix_title');
        foreach ($groups as $prefix => $list) {
            $out .= sprintf(L('prefix_item'), $prefix, count($list), implode(L('prefix_sep'), $list));
        }
    }
    $out .= "\n## " . L('related_title') . "\n\n";
    $out .= L('link_by_class') . "\n" . L('link_options') . "\n" . L('link_setting') . "\n";
    return $out;
}

/** A-Z index of all per-class reference pages (used by index.md) */
function render_doc_az(array $g): string
{
    $items = [];
    foreach (glob(ref_dir() . '/*.md') as $p) {
        $base = basename($p);
        if (in_array($base, ['index.md', 'options.md', 'options-by-class.md', 'options-index.md', 'setting.md'], true)) {
            continue;
        }
        $txt = (string) file_get_contents($p);
        if (!preg_match('/^#\s+(\S+)/m', $txt, $m)) {
            continue;
        }
        $fqcn = $m[1];
        $short = substr(strrchr('\\' . $fqcn, '\\'), 1);
        // key by FQCN: several pages share a short name (Base / Helper / ...)
        $items[$fqcn] = [$short, $base, short_role($txt)];
    }
    uasort($items, function ($a, $b) {
        return strcasecmp($a[0] . $a[1], $b[0] . $b[1]);
    });

    $out = L('az_header');
    foreach ($items as $fqcn => $v) {
        $role = $v[2] !== '' ? $v[2] : L('dash');
        $out .= '| [' . $fqcn . '](' . $v[1] . ') | ' . $role . " |\n";
    }
    return $out;
}

/** nav tables of all per-class pages grouped by src dir (used by index.md) */
function render_doc_nav(array $g): string
{
    // the src dirs behind each nav group, in the order L('nav_groups') names them
    $dirs = [
        [''], ['Core'], ['Component'], ['Ext'],
        ['Db'], ['HttpServer'],
        ['Helper', 'Foundation'], ['GlobalAdmin'], ['GlobalUser'],
    ];
    $titles = L('nav_groups');
    $groups = [];
    foreach ($dirs as $i => $dir_list) {
        $groups[$titles[$i]] = $dir_list;
    }
    $pages = [];
    foreach (glob(ref_dir() . '/*.md') as $p) {
        $base = basename($p);
        if (in_array($base, ['index.md', 'options.md', 'options-by-class.md', 'options-index.md', 'setting.md'], true)) {
            continue;
        }
        $txt = (string) file_get_contents($p);
        if (!preg_match('/^#\s+(\S+)/m', $txt, $m)) {
            continue;
        }
        // "DuckPhp\Core\App" -> "Core" ; "DuckPhp\DuckPhp" (entry class) -> ""
        $rel = preg_replace('~^DuckPhp\\\\~', '', $m[1]);
        $parts = explode('\\', (string) $rel);
        array_pop($parts);
        $dir = $parts ? $parts[0] : '';
        $pages[] = ['fqcn' => $m[1], 'md' => $base, 'role' => short_role($txt), 'dir' => $dir];
    }
    $out = '';
    foreach ($groups as $title => $dirs_of_group) {
        if ($dirs_of_group === null) {
            continue;
        }
        $rows = '';
        foreach ($pages as $pg) {
            if (!in_array($pg['dir'], $dirs_of_group, true)) {
                continue;
            }
            $rows .= '| [' . $pg['fqcn'] . '](' . $pg['md'] . ') | ' . ($pg['role'] !== '' ? $pg['role'] : L('dash')) . " |\n";
        }
        if ($rows === '') {
            continue;
        }
        $out .= '## ' . $title . "\n\n" . L('nav_header') . $rows . "\n";
    }
    return rtrim($out) . "\n";
}

/** stats block for index.md */
function render_stats(array $g): string
{
    $docs = 0;
    foreach (glob(ref_dir() . '/*.md') as $p) {
        if (!in_array(basename($p), ['index.md', 'options.md', 'options-by-class.md', 'options-index.md', 'setting.md'], true)) {
            $docs++;
        }
    }
    $n_class = 0;
    $n_key = 0;
    foreach ($g['classes'] as $info) {
        if ($info['declared']) {
            $n_class++;
            $n_key += count($info['declared']);
        }
    }
    $out = L('stats_header');
    $out .= '| ' . L('stats_docs') . ' | ' . $docs . L('stats_docs_unit') . " |\n";
    $out .= '| ' . L('stats_class') . ' | ' . $n_class . L('stats_class_unit') . " |\n";
    $out .= '| ' . L('stats_keys') . ' | ' . count($g['declared']) . L('stats_keys_unit') . " |\n";
    $out .= '| ' . L('stats_hidden') . ' | ' . count($g['hidden']) . L('stats_hidden_unit') . " |\n";
    return $out;
}

/** option layer cheat sheet for options.md */
function render_layers(array $g): string
{
    $out = L('layers_header');
    foreach (L('layers_rows') as $r) {
        $out .= '| ' . $r[0] . ' | ' . $r[1] . " |\n";
    }
    return $out;
}

/** hidden option table for options.md */
function render_hidden(array $g): string
{
    return L('th_option_default_source_desc') . render_hidden_rows($g);
}

/** setting key table */
function render_setting_keys(array $g): string
{
    $out = L('settingkeys_header');
    foreach (SETTING_KEYS[LANG] as $key => $v) {
        $out .= '| `' . $key . '` | ' . $v[0] . ' | ' . $v[1] . " |\n";
    }
    return $out;
}

/** the App options that drive the setting mechanism */
function render_setting_options(array $g): string
{
    $app = $g['classes']['DuckPhp\\Core\\App'] ?? null;
    $out = L('th_option_default_desc');
    foreach (SETTING_OPTIONS as $key) {
        $default = L('not_declared');
        $desc = L('no_desc_short');
        if ($app !== null && isset($app['declared'][$key])) {
            $default = $app['declared'][$key][0];
            $desc = $app['doc_rows'][$key][1] ?? $desc;
        }
        $out .= '| `' . $key . '` | `' . $default . '` | ' . $desc . " |\n";
    }
    return $out;
}

// ---------------------------------------------------------------------------
// block / file writer
// ---------------------------------------------------------------------------

function block_markers(string $name): array
{
    return ['<!-- GEN:' . $name . ' start -->', '<!-- GEN:' . $name . ' end -->'];
}

/** replace the content of a GEN block; append the block when missing */
function apply_block(string $text, string $name, string $content): string
{
    list($start, $end) = block_markers($name);
    $pos_a = strpos($text, $start);
    $pos_b = strpos($text, $end);
    $body = "\n" . rtrim($content) . "\n";
    if ($pos_a === false || $pos_b === false) {
        return rtrim($text) . "\n\n" . $start . $body . $end . "\n";
    }
    return substr($text, 0, $pos_a + strlen($start)) . $body . substr($text, $pos_b);
}

/** the hand-written prose around the GEN blocks, per language */
function skeletons(string $file): string
{
    if (LANG === 'zh') {
        $zh = [
            'index.md' => "# DuckPhp 参考手册\n\n> 本手册回答「**有什么**」：每个类/接口/Trait 的声明、选项、方法、注意事项。要学「**怎么做**」请看用户指南。\n\n## 速查入口\n\n- [应用选项总览（首页）](options.md) · [按类分组](options-by-class.md) · [字母索引](options-index.md) · [应用设置 Setting](setting.md)\n\n## 全书统计\n\n<!-- GEN:stats start -->\n<!-- GEN:stats end -->\n\n## 分类导航\n\n<!-- GEN:nav start -->\n<!-- GEN:nav end -->\n\n## 全量索引（按类名）\n\n<!-- GEN:az start -->\n<!-- GEN:az end -->\n",
            'options.md' => "# 应用选项总览\n\n## 选项从哪来\n\n（手写：合并链与优先级说明）\n\n## 配置层小抄\n\n<!-- GEN:layers start -->\n<!-- GEN:layers end -->\n\n## 隐藏选项\n\n<!-- GEN:hidden start -->\n<!-- GEN:hidden end -->\n\n## 相关链接\n\n- [按类分组](options-by-class.md) · [字母索引](options-index.md) · [应用设置 Setting](setting.md)\n",
            'setting.md' => "# 应用设置（Setting）\n\n## 简介\n\n（手写）\n\n## 设置键\n\n<!-- GEN:settingkeys start -->\n<!-- GEN:settingkeys end -->\n\n## 相关选项\n\n<!-- GEN:settingoptions start -->\n<!-- GEN:settingoptions end -->\n\n## 相关链接\n\n- [应用选项总览](options.md)\n",
        ];
        return $zh[$file];
    }
    $en = [
        'index.md' => "# DuckPhp Reference Manual\n\n"
            . "> This manual answers \"**what is there**\": the namespace, declaration, options, method signatures and caveats of every class / interface / Trait — every statement here can be found word for word in `src/`.\n"
            . "> To learn \"**how to do it**\", read the user guide (`docs/en/guide/`): it covers the how, and links the mechanism details back to this manual.\n\n"
            . "## Quick entry points\n\n"
            . "| What you want | Where to go |\n|---|---|\n"
            . "| The full picture of a class | the [category navigation](#category-navigation) or the [full index (by class name)](#full-index-by-class-name) below |\n"
            . "| The meaning and default of an option | [Application options overview (home)](options.md) → [by class](options-by-class.md) / [A-Z index](options-index.md) |\n"
            . "| How `options` and `setting` divide the work | [Application settings (Setting)](setting.md) |\n"
            . "| Global functions (`__h()` and friends) | [DuckPhp\\Core\\Functions](Core-Functions.md) |\n"
            . "| How to read a per-class page | see [how to use this manual](#how-to-use-this-manual) below |\n\n"
            . "## Book statistics\n\n"
            . "<!-- GEN:stats start -->\n<!-- GEN:stats end -->\n\n"
            . "## How to use this manual\n\n"
            . "- **A fixed layout for per-class pages**: Introduction → Class info → Options → Usage → Configuration example → Caveats → All options → Methods → Related links. Jump by that layout when you are looking for something.\n"
            . "- **The method lists**: an entry is \"a signature indented by four spaces plus one sentence\"; static shells and instance implementations are listed separately (such as `Show()` and `_Show()`); methods provided by a Trait are not repeated, only their origin is named and linked.\n"
            . "- **\"Caveats\" records the source's temper**: the traps that were stepped in, the counter-intuitive behaviour that is deliberate, and the reminder that \"the source is authoritative\".\n"
            . "- **A link is proof of existence**: this manual only links files that really exist, so a link that opens means the page is really there.\n"
            . "- **Planned pages** follow the user guide's convention: plain text plus `⏳` and no link, to avoid dead links. All 114 pages are written today, so `⏳` appears only while a **new** page is still unfinished.\n\n"
            . "## Category navigation\n\n"
            . "<!-- GEN:nav start -->\n<!-- GEN:nav end -->\n\n"
            . "## Full index (by class name)\n\n"
            . "<!-- GEN:az start -->\n<!-- GEN:az end -->\n",
        'options.md' => "# Application options overview\n\n"
            . "> This is the home page of the \"application options\" feature: first where options **come from** and **who overrides whom**, then the two indexes.\n"
            . "> By class: [Application options (by class)](options-by-class.md) · By name: [Application options (A-Z index)](options-index.md) · The other configuration set: [Application settings (Setting)](setting.md)\n\n"
            . "## In one sentence\n\n"
            . "`\$options` is the framework's **only configuration entrance**: whether it is written in the application class's `\$options` property, in `init(\$options)`, in the `ext` table, or changed dynamically from the command line, it all merges into the current App instance's `\$options` (`App::\$options`).\n\n"
            . "## Where options come from (the merge chain)\n\n"
            . "```php\n"
            . "// 1) on construction: the \"declared defaults\" are combined first (the later one wins)\n"
            . "//    App::__construct()\n"
            . "\$this->options = array_replace_recursive(\$this->kernel_options, \$this->core_options, \$this->common_options, \$this->options);\n\n"
            . "// 2) on initialisation: the options the caller passed are merged in (highest priority)\n"
            . "//    KernelTrait::initOptions()\n"
            . "\$this->options = array_replace_recursive(\$this->options, \$options);\n"
            . "```\n\n"
            . "| Order | Source | Written by | Note |\n| --- | --- | --- | --- |\n"
            . "| 1 | `KernelTrait::\$kernel_options` | the framework | the application skeleton: `path`/`namespace`/`app`/`cmd`/`ext`/`cli_enable`/`on_*` |\n"
            . "| 2 | `App::\$core_options` | the framework | core: `path_runtime`, `path_config`, `setting*`, `error_*`, `exception_map` |\n"
            . "| 3 | `DuckPhp::\$common_options` | the framework | entry-class defaults: the default `ext` component table, `*_provider`, `lang_*`, `local_database` and so on |\n"
            . "| 4 | **the subclass's `\$options` property** | your project | your application class writes here; the same key overrides the three layers above |\n"
            . "| 5 | the **array** options given in the `ext` table | your project | each component merges them when it is initialised |\n"
            . "| 6 | `init(\$options)` | your project | **merged last, highest priority** (`initOptions()` applies no whitelist) |\n"
            . "| — | the setting file / `.env` | the environment | not part of `\$options`; read with `Setting()`: see [Application settings](setting.md) |\n"
            . "| — | the data file (`ExtOptionsLoader`) | runtime | runtime-changeable options land in `runtime/DuckPhpData.config.json` |\n\n"
            . "> ⚠️ **Components have a whitelist, App does not**: a component (`ComponentBase` subclass) applies `array_intersect_key(\$this->options, \$options)` in `init()` — **a key the component does not have in its own `\$options` is silently dropped**; `App` goes through `KernelTrait::initOptions()` instead, where any key may come in (which is why \"implicit options\" work on App).\n\n"
            . "## Configuration layers cheat sheet\n\n"
            . "<!-- GEN:layers start -->\n<!-- GEN:layers end -->\n\n"
            . "## Hidden options\n\n"
            . "Keys the framework reads but **deliberately keeps out of every `\$options`**. They can be used like ordinary options (written in the application class's `\$options`, or given in `init(\$options)`); they simply are not part of the formal list above, because they are \"framework-internal switches\" or \"hooks for external tools\" that should not be mistaken for ordinary configuration.\n\n"
            . "<!-- GEN:hidden start -->\n<!-- GEN:hidden end -->\n\n"
            . "Conventions:\n\n"
            . "- for the complete machine-readable list: `php docs/scripts/gen-options-docs.php --json`, or the human-readable `python3 docs/scripts/scan-options.py`;\n"
            . "- the scanner cross-checks whether the defaults in the hidden table agree with the source, whether anything really reads the key, and whether the construction flow empties it;\n"
            . "- the `// @used-by <package>` line in the hidden table means **the entry is read by an external package** (this repository has no read site for it, so the scanner no longer warns).\n\n"
            . "## Two dynamic channels (so an \"option list\" can never be exhaustive)\n\n"
            . "1. **Callback keys looked up dynamically**: `run_callback_by_key(\$key)` in `GlobalAdmin` / `GlobalUser` reads `\$this->options[\$key]`, and the key names come from their own internal constants (such as `admin_callback_for_login_service`).\n"
            . "2. **A string in the `ext` table is an option key name**: in `initExtensionsByOptions()`, when a component's value in the `ext` table is written as a **string**, that string is taken as an **option key name** and `\$this->options[...]` is read. For example, in `DuckPhp::\$common_options`\n\n"
            . "   ```php\n   RouteHookPathInfoCompat::class => 'path_info_compact_enable',\n   ```\n\n"
            . "   so `path_info_compact_enable` (declared by `RouteHookPathInfoCompat::\$options` itself) becomes \"this extension's switch\".\n\n"
            . "## Extensions and the data file\n\n"
            . "The value in the `ext` table may be written in four ways (`KernelTrait::initExtensionsByOptions()`):\n\n"
            . "| Form | Meaning |\n|---|---|\n"
            . "| `true` | initialise the default way (following the App options) |\n"
            . "| `false` / `null` / `EXT_DISABLE(0)` | do not load |\n"
            . "| an array | initialise that component with this array (the component whitelist still applies) |\n"
            . "| a `'option key name'` string | take `\$options[option key name]` and decide from the value found |\n"
            . "| `EXT_SKIP_INIT(-1)` / `EXT_DEFAULT(1)` / `EXT_FOLLOW_APP(2)` / `EXT_RENEW(3)` | take the instance without initialising / initialise by default / follow the App options / rebuild on every request (a dynamic component) |\n\n"
            . "**The data file** (`ExtOptionsLoader`, the `data_file_*` options): it writes \"runtime-changeable options\" into a JSON file under `path_runtime` and overrides them back on the next start; the command line `php xx debug` changes `is_debug` in it. The file also carries two fields the framework writes itself: `__class__` (the options' originating class) and `__date__` (when it was written) — both are internal mechanics, never write them by hand.\n\n"
            . "## Common recipes\n\n"
            . "- **Differentiating several child applications**: `'app' => [AppA::class => ['name' => 'a', 'controller_url_prefix' => 'a']]`; a child application's options are merged independently in `initChildren()` and do not affect the others. The mixed form also supports `['class' => AppA::class]` (the table key becomes the `controller_url_prefix`).\n"
            . "- **Per-Phase override files**: `getOverrideableFile()` walks back from the current Phase layer by layer looking for a file — the same `config/x.php` can have an override copy for a child application.\n"
            . "- **Three debug switches**: the option `is_debug`, the setting `duckphp_is_debug` (the two are OR-ed, see `App::IsDebug()`), and `is_debug` in the data file (changeable from the command line).\n"
            . "- **Changing an option temporarily**: `App::_()->options['some_key'] = value;` affects the current instance only; to persist it, write the setting file or the data file.\n"
            . "- **CLI arguments**: things like `php xx run --port=8080` are **command-line arguments** (`Console::getCliParameters()`), not app options — do not mix the two.\n\n"
            . "## Related links\n\n"
            . "- [Application options (by class)](options-by-class.md) —— \"which configuration does a class support\"\n"
            . "- [Application options (A-Z index)](options-index.md) —— \"what does this key mean\", quickly\n"
            . "- [Application settings (Setting)](setting.md) —— \"environment data\" such as database passwords and the debug/maintenance switches\n"
            . "- [DuckPhp\\Core\\KernelTrait](Core-KernelTrait.md) —— where `kernel_options` and `initOptions()` live\n"
            . "- [DuckPhp\\Core\\App](Core-App.md) —— `core_options` and the setting-file loading\n"
            . "- [DuckPhp\\DuckPhp](DuckPhp.md) —— `common_options` and the hidden option table\n",
        'setting.md' => "# Application settings (Setting)\n\n"
            . "> `options` governs \"**how the framework runs**\" (behaviour switches); `setting` governs \"**what this environment is**\" (database passwords, Redis addresses, the platform marker, the debug and maintenance state).\n"
            . "> Settings **do not take part** in the `\$options` merge and are read with `App::Setting()`; for who wins between the two, see the end of this page.\n\n"
            . "## Introduction\n\n"
            . "Settings are the configuration layer that \"follows the environment and normally stays out of version control\". It has three sources, merged in order (the later one wins), and it is **loaded only once, in the root application**: child applications and child Phases all read the root application's copy.\n\n"
            . "## Where settings come from (three-step merge)\n\n"
            . "| Order | Source | When it is read | Note |\n|---|---|---|---|\n"
            . "| 1 | the `setting` option (an array) | always | `[]` by default in `App::\$core_options`; just write the array in the application options |\n"
            . "| 2 | `<path>/.env` | when `use_env_file` is true | `parse_ini_file()` reads the `.env` in the root directory (INI syntax) |\n"
            . "| 3 | `<path>/<setting_file>` | when `setting_file_enable` is true | `require` that file, which must `return` an array; `setting_file` defaults to `config/DuckPhpSettings.config.php` and may be an absolute path |\n\n"
            . "- When the step-3 file does not exist: with `setting_file_ignore_exists` true (the default) it is **skipped silently**, and with it false an `ErrorException('DuckPhp: no Setting File')` is thrown.\n"
            . "- The implementation all lives in `App::loadSetting()` (plus `dealWithEnvFile()` / `dealWithSettingFile()`).\n"
            . "- ⚠️ **Loaded only in the root application**: `App::onPrepare()` calls `loadSetting()` only after checking `is_root`; child applications and child Phases **do not load it again** and always read the root application's copy.\n\n"
            . "## How to read them\n\n"
            . "```php\n"
            . "\$dsn  = App::Setting('database_list');     // one key; null when it is absent\n"
            . "\$all  = App::Setting();                    // no key → the whole setting array\n"
            . "\$home = App::Setting('my_home', '/');      // with a default\n"
            . "```\n\n"
            . "- The static entry point is `App::Setting(\$key = null, \$default = null)`; the instance form is `App::_()->_Setting(...)`.\n"
            . "- The implementation is `static::Root()->setting[\$key] ?? \$default` —— it **always reads the root application's settings**, so the same values are available inside a child Phase.\n"
            . "- The Helpers have it too: `Helper::Setting()` (provided by both [Foundation\\Business\\BusinessHelper](Foundation-Business-BusinessHelper.md) and [Foundation\\Controller\\ControllerHelper](Foundation-Controller-ControllerHelper.md)), so the business/controller layers need not depend on `App` directly.\n\n"
            . "## Setting keys the framework itself understands\n\n"
            . "<!-- GEN:settingkeys start -->\n<!-- GEN:settingkeys end -->\n\n"
            . "> Apart from the table above, **every other key belongs entirely to your project** (`Setting('my_key')`); the framework neither touches nor validates it.\n\n"
            . "## Related options\n\n"
            . "<!-- GEN:settingoptions start -->\n<!-- GEN:settingoptions end -->\n\n"
            . "- writing an array in `setting` is \"step 1\", equivalent to writing a setting file (just without a file outside version control);\n"
            . "- to make the setting file optional: keep `setting_file_ignore_exists = true` (the default);\n"
            . "- to keep passwords in `.env`: set `use_env_file` true.\n\n"
            . "## Who wins: `options` or `setting`\n\n"
            . "| Scenario | Rule |\n|---|---|\n"
            . "| the debug switch | `Setting('duckphp_is_debug')` **or** `options['is_debug']` —— whichever is true wins (`App::IsDebug()`) |\n"
            . "| maintenance mode | `Setting('duckphp_is_maintain')` **or** `options['is_maintain']` —— when truthy the maintenance page is output straight away |\n"
            . "| the platform marker | only `duckphp_platform` in the settings is honoured (`App::Platform()`) |\n"
            . "| database / Redis | **the option wins**: a non-empty `options['database_list']` (or `options['database']`) is used; only when it is empty and `database_list_reload_by_setting` (Redis: `redis_list_reload_by_setting`) is true does it fall back to the settings |\n"
            . "| what the installer writes | after a successful installation `RouteHookWebInstaller` **writes `database_list` / `redis_list` into the setting file** and sets the matching `*_reload_by_setting` to false, so the options cannot override them |\n\n"
            . "## Environment separation and deployment\n\n"
            . "- The setting file **normally does not go into git**: the header of `skeleton/config/DuckPhpSettings.config.php` in the scaffold literally says `Do no save me in git`.\n"
            . "- `.env` uses INI syntax (`parse_ini_file`) and is a good place for passwords; a leading `#` is a comment.\n"
            . "- For production: set `installed` true and switch `is_debug` off (turning it off in either the option or the setting is not enough, since the two are OR-ed — **to switch it off, switch it off in both**).\n"
            . "- Deployment details are in the configuration / deployment chapters of the user guide; this page covers only the mechanism and the keys.\n\n"
            . "## Real samples in this repository\n\n"
            . "| File | Purpose |\n|---|---|\n"
            . "| `skeleton/config/DuckPhpSettings.config.php` | the template the scaffold generates (`duckphp_*` and `database_list`/`redis_list` are all commented out) |\n"
            . "| `demo/config/DuckPhpSettings.config.php` | used by the demo application |\n"
            . "| `tests/data_for_tests/setting.sample.php` | the sample used by tests (two databases in `database_list`, plus `redis_list`) |\n\n"
            . "## Related links\n\n"
            . "- [Application options overview (home)](options.md) —— the `options` set\n"
            . "- [Application options (by class)](options-by-class.md) / [Application options (A-Z index)](options-index.md)\n"
            . "- [DuckPhp\\Core\\App](Core-App.md) —— `loadSetting()`, `Setting()` and the `setting*` options\n"
            . "- [DuckPhp\\Component\\DbManager](Component-DbManager.md) / [DuckPhp\\Component\\RedisManager](Component-RedisManager.md) —— who reads `database_list` / `redis_list`\n",
    ];
    return $en[$file];
}

/**
 * 编辑器重排表格只改空白（列宽对齐、`|---|` 写成 `| --- |`、末尾补一行空表格行），
 * 内容其实没变。这类差异**不算文档过时**：按「去掉行内空白 + 丢掉纯占位表格行」归一后再比。
 * 详见 reference-maintenance-guide.md 的陷阱表（Obsidian 表格插件）。
 */
function normalize_layout(string $text): string
{
    $out = [];
    foreach (preg_split('~\R~', $text) as $line) {
        $line = rtrim($line);
        if (preg_match('~^\|[\s|]*\|$~', $line)) {
            continue;   // 只有 | 与空白的占位行
        }
        $t = preg_replace('~[ \t]+~', '', $line);
        if (preg_match('~^\|[\-:|]+\|$~', $t)) {
            // 分隔行只看列数：编辑器会把 `---` 拉长到列宽（`|---|` -> `| -------- |`）
            $out[] = '|' . str_repeat('-|', max(0, substr_count($t, '|') - 1));
            continue;
        }
        $out[] = $t;
    }
    return implode("\n", $out);
}

function main(): int
{
    $argv = $GLOBALS['argv'];
    $check = in_array('--check', $argv, true);
    $json = in_array('--json', $argv, true);

    $g = gather();

    if ($json) {
        echo json_encode($g, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT), "\n";
        return 0;
    }

    $problems = [];
    foreach (array_keys($g['hidden']) as $key) {
        if (!isset(HIDDEN_DESC[LANG][$key])) {
            $problems[] = '隐藏选项缺少说明（请补 HIDDEN_DESC）：' . $key;
        }
    }
    foreach ($g['hidden_extra'] as $key) {
        $problems[] = 'HIDDEN_DESC 里的选项源码已不存在（请删）：' . $key;
    }

    $targets = [
        'options-by-class.md' => render_by_class($g),
        'options-index.md' => render_options_index($g),
    ];

    $blocks = [
        'index.md' => ['stats' => render_stats($g), 'nav' => render_doc_nav($g), 'az' => render_doc_az($g)],
        'options.md' => ['layers' => render_layers($g), 'hidden' => render_hidden($g)],
        'setting.md' => ['settingkeys' => render_setting_keys($g), 'settingoptions' => render_setting_options($g)],
    ];

    $changed = [];
    $layout_only = [];
    $bad_encoding = [];
    foreach ($targets as $file => $content) {
        if (!mb_check_encoding($content, 'UTF-8')) {
            $bad_encoding[] = $file;
        }
        $path = ref_dir() . '/' . $file;
        $old = is_file($path) ? (string) file_get_contents($path) : null;
        if ($old === $content) {
            continue;
        }
        if ($old !== null && normalize_layout($old) === normalize_layout($content)) {
            $layout_only[] = $file;   // 只被编辑器重排过：不报 stale，也不写回（保留编辑器的排版）
            continue;
        }
        $changed[] = $file;
        if (!$check) {
            file_put_contents($path, $content);
        }
    }
    foreach ($blocks as $file => $spec) {
        $path = ref_dir() . '/' . $file;
        $old = is_file($path) ? (string) file_get_contents($path) : skeletons($file);
        $new = $old;
        foreach ($spec as $name => $content) {
            $new = apply_block($new, $name, $content);
        }
        if (!mb_check_encoding($new, 'UTF-8')) {
            $bad_encoding[] = $file;
        }
        if ($new === $old) {
            continue;
        }
        if (normalize_layout($old) === normalize_layout($new)) {
            $layout_only[] = $file;   // 同上：块内容没变，只是被编辑器重排过
            continue;
        }
        $changed[] = $file;
        if (!$check) {
            file_put_contents($path, $new);
        }
    }

    foreach ($bad_encoding as $f) {
        $problems[] = '生成结果不是合法 UTF-8（已拒绝写入，请检查文案来源）：' . $f;
    }
    if ($bad_encoding) {
        // do not leave broken files behind on the next run either
        $changed = array_values(array_diff($changed, $bad_encoding));
    }

    sort($changed);
    sort($layout_only);
    if ($check) {
        foreach ($problems as $p) {
            fwrite(STDERR, '!! ' . $p . "\n");
        }
        if ($changed) {
            fwrite(STDERR, "stale pages (" . count($changed) . "): " . implode(', ', $changed) . "\n");
            return 1;
        }
        echo "options docs are up to date\n";
        if ($layout_only) {
            echo 'note: layout-only differences ignored (' . count($layout_only) . '): ' . implode(', ', $layout_only) . "\n";
        }
        return $problems ? 1 : 0;
    }

    echo "written: " . ($changed ? implode(', ', $changed) : '(nothing changed)') . "\n";
    if ($layout_only) {
        echo 'kept as-is (layout-only, 编辑器排版过): ' . implode(', ', $layout_only) . "\n";
    }
    foreach ($problems as $p) {
        echo '!! ' . $p . "\n";
    }
    return $problems ? 1 : 0;
}

exit(main());
