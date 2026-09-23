<?php declare(strict_types=1);
/**
 * gen-options-docs.php - generator for the option-related summary pages of the
 * reference manual (docs/zh/reference).
 *
 * Outputs (all of them live under docs/zh/reference/):
 *   options-by-class.md   fully generated   app options grouped by declaring class
 *   options-index.md      fully generated   app options as an A-Z index
 *   index.md              block generated   manual index page (stats / nav / A-Z of the docs)
 *   options.md            block generated   options home page (layer cheat sheet / hidden / setting)
 *   setting.md            block generated   Setting() mechanism page (setting keys / setting options)
 *
 * Three sources of truth:
 *   1. src/**\/*.php      declared options ($options/$core_options/$kernel_options/$common_options),
 *                         hidden options ($hidden_options) and their default literals
 *   2. the 113 per-class reference pages   option descriptions (their "## 选项" table)
 *   3. the two tables in this file         descriptions for hidden options + setting keys
 *                                          (they have no per-class option table to read from)
 *
 * Block mode: a mixed page keeps its human prose; only the text between
 *   <!-- GEN:<name> start -->  and  <!-- GEN:<name> end -->  is rewritten.
 * When such a page does not exist yet, a skeleton with the markers is created.
 *
 * Usage:
 *   php docs/scripts/gen-options-docs.php            write the pages
 *   php docs/scripts/gen-options-docs.php --check    verify only; exit 1 when something is stale
 *   php docs/scripts/gen-options-docs.php --json     dump the gathered facts
 *
 * Note: no timestamps are written anywhere, so re-running is byte-identical (idempotent).
 * English comments on purpose (same style as docs/scripts/gen-reference.php); the generated
 * pages themselves are Chinese.
 */

const ROOT = __DIR__ . '/../..';
const SRC_DIR = ROOT . '/src';
const REF_DIR = ROOT . '/docs/zh/reference';

const OPT_VARS = ['options', 'core_options', 'kernel_options', 'common_options'];
const HIDDEN_VAR = 'hidden_options';

/**
 * Documentation of the hidden options (source: the $hidden_options tables in src/).
 * key => [where it is read, description]
 * A key missing here is reported as an error; a key here that src no longer lists
 * is reported as a warning, so the two can not drift apart silently.
 */
const HIDDEN_DESC = [
    'session_prefix' => ['Foundation\\Controller\\SessionTrait', '会话名的前缀（根应用的设置也走这里）。'],
    'table_prefix' => ['Ext\\SqlDumper / Ext\\RouteHookWebInstaller', '数据库表名前缀，导出/安装 SQL 时用 `{prefix}` 占位替换。'],
    'exception_for_business' => ['CoreHelper::_BusinessThrowOn()', '`BusinessThrowOn()` 未显式指定时的异常类。'],
    'exception_for_controller' => ['CoreHelper::_ControllerThrowOn()', '`ControllerThrowOn()` 未显式指定时的异常类。'],
    'duckphp_all_in_one_wrap_header_foot' => ['DuckPhpAllInOne::onInited()', 'AllInOne 入口是否给 `_Show()` 包 head/foot 视图（该类自己会置 true）。'],
    'permission_menu_tree_for_admin' => ['Ext\\PermissionMenu::getMenuJsonFileConfig()', '后台权限菜单树的配置文件（相对 `path_config`）。'],
    'duckcoverage_test_lister' => ['外部包 dvaknheo/duckcoverage', '配合该 composer 包做覆盖测试使用，框架自身不读。'],
    'not_empty' => ['DuckPhp::$common_options', '声明在默认选项里、但源码中没有任何读取点（历史遗留，可忽略）。'],
    'background' => ['HttpServer::run*()', '内置服务器是否后台运行；CLI 开关 `-b/--background` 会把它置 true。'],
];

/**
 * Setting keys: not options, they live in the setting file / .env and are read
 * with Setting().  key => [who reads it, description]
 */
const SETTING_KEYS = [
    'duckphp_is_debug' => ['Core\\App::_IsDebug()', '调试开关；与选项 `is_debug` 取或。'],
    'duckphp_platform' => ['Core\\App::_Platform()', '平台标识，由 App::Platform() 读出。'],
    'duckphp_is_maintain' => ['Core\\App::prepareServe()', '维护模式；与选项 `is_maintain` 取或，为真时直接输出维护页。'],
    'database_list' => ['Component\\DbManager', '数据库连接列表（`dsn/username/password/driver_options`）；`database_list_reload_by_setting` 与「选项未给」共同决定是否采用。'],
    'database' => ['Component\\DbManager', '单个数据库连接的简写，`database_list_try_single` 为真时生效。'],
    'redis_list' => ['Component\\RedisManager', 'Redis 连接列表；`redis_list_reload_by_setting` 与「选项未给」共同决定是否采用。'],
    'redis' => ['Component\\RedisManager / Ext\\RouteHookWebInstaller', '单个 Redis 连接的简写，`redis_list_try_single` 为真时生效。'],
];

/** src/<dir> => [order, layer title, section anchor] */
const LAYERS = [
    '' => [0, '入口类', 'entry'],
    'Core' => [1, '核心', 'core'],
    'Component' => [2, '自带组件', 'component'],
    'Ext' => [3, '可选扩展', 'ext'],
    'Db' => [4, '数据库', 'db'],
    'HttpServer' => [5, 'HTTP 服务器', 'httpserver'],
    'Helper' => [6, '助手', 'helper'],
    'Foundation' => [7, '基础骨架', 'foundation'],
    'GlobalAdmin' => [8, '管理员系统', 'admin'],
    'GlobalUser' => [9, '用户系统', 'user'],
];

/** the 5 App options that drive the setting mechanism, in source order */
const SETTING_OPTIONS = ['setting', 'setting_file', 'setting_file_enable', 'setting_file_ignore_exists', 'use_env_file'];

const GEN_NOTE = '> 本页由 `docs/scripts/gen-options-docs.php` 生成，**请勿手改**：改选项请改 `src/` 与对应类文档，然后重跑生成器。';

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
    if (!preg_match('/\n## 简介\n(.*?)(?=\n## |\Z)/s', $md_text, $m)) {
        return '';
    }
    $txt = preg_replace('~```.*?```~s', '', $m[1]);
    $txt = str_replace(["\r", "\n"], ' ', (string) $txt);
    $txt = preg_replace('~`([^`]*)`~', '$1', (string) $txt);
    $txt = preg_replace('~\[([^\]]*)\]\([^)]*\)~', '$1', (string) $txt);
    $txt = trim(preg_replace('~\s+~', ' ', (string) $txt));

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

/** option rows of a class page: "## 选项" table => key => [default, desc] */
function doc_option_rows(string $md_text): array
{
    $rows = [];
    if (!preg_match('/\n## 选项\n(.*?)(?=\n## |\Z)/s', $md_text, $m)) {
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
        $md_path = REF_DIR . '/' . $md;
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
        $layer = LAYERS[$dir] ?? [99, $dir, 'other'];

        $classes[$fqcn] = [
            'file' => $src_rel,
            'md' => is_file($md_path) ? $md : '',
            'role' => $role,
            'layer' => $layer,
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
    $hidden_extra = array_values(array_diff(array_keys(HIDDEN_DESC), array_keys($hidden_src_keys)));
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

    $out = "# 应用选项（按类分组）\n\n" . GEN_NOTE . "\n\n";
    $out .= "选项来自各类的 `\$options` / `\$core_options` / `\$kernel_options` / `\$common_options`；"
        . "**默认值取自源码**，说明取自该类的参考文档。共 **" . $n_class . "** 个类、**" . $n_key . "** 个选项"
        . "（同名选项在不同类各自声明，合计 " . $n_decl . " 处；另有 " . $hidden_n . " 个隐藏选项见文末）。\n\n";
    $out .= "想按名字找？看 [应用选项（按字母顺序索引）](options-index.md)；选项机制见 [应用选项总览](options.md)。\n\n---\n\n";

    $last_layer = -1;
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
                $out .= "\n类文档：[" . $fqcn . '](' . $info['md'] . ")\n";
            }
            $out .= "\n| 选项 | 默认值 | 说明 |\n|---|---|---|\n";
            foreach ($info['declared'] as $key => $dv) {
                $rows = $info['doc_rows'];
                $desc = $rows[$key][1] ?? '';
                $default = $dv[0];
                if (isset($rows[$key][0]) && $rows[$key][0] !== '' && strpos($rows[$key][0], $default) === false
                    && strpos($default, '[') === false) {
                    $default = $rows[$key][0] . ' ⇐文档';
                }
                $out .= '| `' . $key . '` | `' . $default . '` | ' . ($desc !== '' ? $desc : '（该类文档未写说明）') . " |\n";
            }
            $out .= "\n";
        }
    }

    // hidden options
    $out .= "## 隐藏选项\n\n";
    $out .= "框架会读、但**故意不写进 `\$options`** 的键（源码里集中在 `\$hidden_options` 表）。"
        . "它们可以像普通选项一样在 `init()`/`\$options` 里给出，只是不出现在上面的正式选项里。\n\n";
    $out .= "| 选项 | 默认值 | 出处 | 说明 |\n|---|---|---|---|\n";
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
        $where = HIDDEN_DESC[$key][0] ?? implode(' / ', $owners);
        $desc = HIDDEN_DESC[$key][1] ?? '';
        if ($desc === '' && $note !== '') {
            $desc = $note;
        }
        $out .= '| `' . $key . '` | `' . $default . '` | ' . $where . ' | ' . ($desc !== '' ? $desc : '（待补说明）') . " |\n";
    }
    $out .= "\n## 相关链接\n\n";
    $out .= "- [应用选项（按字母顺序索引）](options-index.md)\n- [应用选项总览（首页）](options.md)\n- [应用设置 Setting](setting.md)\n";
    return $out;
}

function render_options_index(array $g): string
{
    $classes = $g['classes'];
    $keys = array_keys($g['declared']);
    sort($keys, SORT_STRING);

    $out = "# 应用选项（按字母顺序索引）\n\n" . GEN_NOTE . "\n\n";
    $out .= "一共 **" . count($keys) . "** 个选项；同名选项出现在多个类时**合并为一行**，来源类并列。"
        . "隐藏选项见 [按类分组](options-by-class.md#隐藏选项) 文末（本页只收正式选项）。\n\n";
    $out .= "按类查看：[应用选项（按类分组）](options-by-class.md) · 选项机制：[应用选项总览](options.md)\n\n";

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
    $out .= '**跳转**：' . implode(' · ', $bar) . "\n\n---\n\n";

    $current = '';
    foreach ($keys as $key) {
        $letter = strtoupper(substr($key, 0, 1));
        if ($letter !== $current) {
            if ($current !== '') {
                $out .= "\n";
            }
            $current = $letter;
            $out .= '## ' . strtolower($letter) . "\n\n";
            $out .= "| 选项 | 默认值 | 来源类 | 说明 |\n|---|---|---|---|\n";
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
            . ($desc !== '' ? $desc : '（类文档未写说明）') . " |\n";
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
        $out .= "\n---\n\n## 按前缀分组（便于成组记忆）\n\n";
        foreach ($groups as $prefix => $list) {
            $out .= '- **`' . $prefix . '*`**（' . count($list) . '）：`' . implode('`、`', $list) . "`\n";
        }
    }
    $out .= "\n## 相关链接\n\n";
    $out .= "- [应用选项（按类分组）](options-by-class.md)\n- [应用选项总览（首页）](options.md)\n- [应用设置 Setting](setting.md)\n";
    return $out;
}

/** A-Z index of all per-class reference pages (used by index.md) */
function render_doc_az(array $g): string
{
    $items = [];
    foreach (glob(REF_DIR . '/*.md') as $p) {
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

    $out = "| 类 | 一句话 |\n|---|---|\n";
    foreach ($items as $fqcn => $v) {
        $role = $v[2] !== '' ? $v[2] : '—';
        $out .= '| [' . $fqcn . '](' . $v[1] . ') | ' . $role . " |\n";
    }
    return $out;
}

/** nav tables of all per-class pages grouped by src dir (used by index.md) */
function render_doc_nav(array $g): string
{
    $groups = [
        '入口类' => [''],
        '核心类' => ['Core'],
        '组件' => ['Component'],
        '扩展' => ['Ext'],
        '数据库' => ['Db'],
        'HTTP 服务器' => ['HttpServer'],
        '助手' => ['Helper', 'Foundation'],
        '管理员系统' => ['GlobalAdmin'],
        '用户系统' => ['GlobalUser'],
    ];
    $pages = [];
    foreach (glob(REF_DIR . '/*.md') as $p) {
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
    foreach ($groups as $title => $dirs) {
        $rows = '';
        foreach ($pages as $pg) {
            if (!in_array($pg['dir'], $dirs, true)) {
                continue;
            }
            $rows .= '| [' . $pg['fqcn'] . '](' . $pg['md'] . ') | ' . ($pg['role'] !== '' ? $pg['role'] : '—') . " |\n";
        }
        if ($rows === '') {
            continue;
        }
        $out .= '## ' . $title . "\n\n| 类 | 说明 |\n|---|---|\n" . $rows . "\n";
    }
    return rtrim($out) . "\n";
}

/** stats block for index.md */
function render_stats(array $g): string
{
    $docs = 0;
    foreach (glob(REF_DIR . '/*.md') as $p) {
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
    $out = "| 项目 | 数量 |\n|---|---|\n";
    $out .= '| 逐类参考页 | ' . $docs . " 篇 |\n";
    $out .= '| 声明了选项的类 | ' . $n_class . " 个 |\n";
    $out .= '| 应用选项（去重后） | ' . count($g['declared']) . " 个 |\n";
    $out .= '| 应用选项（隐藏） | ' . count($g['hidden']) . " 个 |\n";
    return $out;
}

/** option layer cheat sheet for options.md */
function render_layers(array $g): string
{
    $rows = [
        ['`DuckPhp\Core\KernelTrait::$kernel_options`', '应用骨架：path/namespace/app/cmd/ext/cli_enable/on_* 等'],
        ['`DuckPhp\Core\App::$core_options`', '核心：path_runtime、path_config、setting*、error_*、exception_map…'],
        ['`DuckPhp::$common_options`', '入口类默认：`ext` 默认组件表、provider、lang_*、data_file_*…'],
        ['各组件自己的 `$options`', '组件被 init 时用自己的白名单合并，见 [按类分组](options-by-class.md)'],
        ['`ext` 表装载的扩展', '`类 => true/数组/\'选项键\'/EXT_* 常量`；字符串值是**选项键名**'],
        ['`init($options)` 运行时传入', '最后合并，**优先级最高**（`KernelTrait::initOptions()` 直接 `array_replace_recursive`）'],
        ['设置文件 / `.env`', '不进 `$options`，用 `Setting()` 读，见 [应用设置](setting.md)'],
        ['数据文件（`ExtOptionsLoader`）', '运行时可改的选项落在 `runtime/DuckPhpData.config.json`'],
    ];
    $out = "| 层 | 内容 |\n|---|---|\n";
    foreach ($rows as $r) {
        $out .= '| ' . $r[0] . ' | ' . $r[1] . " |\n";
    }
    return $out;
}

/** hidden option table for options.md */
function render_hidden(array $g): string
{
    $out = "| 隐藏选项 | 默认值 | 出处 | 说明 |\n|---|---|---|---|\n";
    foreach ($g['hidden'] as $key => $owners) {
        $default = '';
        foreach ($owners as $fqcn) {
            $h = $g['classes'][$fqcn]['hidden'][$key] ?? null;
            if ($h !== null && $default === '') {
                $default = $h[0];
            }
        }
        $where = HIDDEN_DESC[$key][0] ?? implode(' / ', $owners);
        $desc = HIDDEN_DESC[$key][1] ?? '（待补说明）';
        $out .= '| `' . $key . '` | `' . $default . '` | ' . $where . ' | ' . $desc . " |\n";
    }
    return $out;
}

/** setting key table */
function render_setting_keys(array $g): string
{
    $out = "| 设置键 | 谁在读 | 作用 |\n|---|---|---|\n";
    foreach (SETTING_KEYS as $key => $v) {
        $out .= '| `' . $key . '` | ' . $v[0] . ' | ' . $v[1] . " |\n";
    }
    return $out;
}

/** the App options that drive the setting mechanism */
function render_setting_options(array $g): string
{
    $app = $g['classes']['DuckPhp\\Core\\App'] ?? null;
    $out = "| 选项 | 默认值 | 说明 |\n|---|---|---|\n";
    foreach (SETTING_OPTIONS as $key) {
        $default = '[未声明]';
        $desc = '（类文档未写说明）';
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

const SKELETONS = [
    'index.md' => "# DuckPhp 参考手册\n\n> 本手册回答「**有什么**」：每个类/接口/Trait 的声明、选项、方法、注意事项。要学「**怎么做**」请看用户指南。\n\n## 速查入口\n\n- [应用选项总览（首页）](options.md) · [按类分组](options-by-class.md) · [字母索引](options-index.md) · [应用设置 Setting](setting.md)\n\n## 全书统计\n\n<!-- GEN:stats start -->\n<!-- GEN:stats end -->\n\n## 分类导航\n\n<!-- GEN:nav start -->\n<!-- GEN:nav end -->\n\n## 全量索引（按类名）\n\n<!-- GEN:az start -->\n<!-- GEN:az end -->\n",
    'options.md' => "# 应用选项总览\n\n## 选项从哪来\n\n（手写：合并链与优先级说明）\n\n## 配置层小抄\n\n<!-- GEN:layers start -->\n<!-- GEN:layers end -->\n\n## 隐藏选项\n\n<!-- GEN:hidden start -->\n<!-- GEN:hidden end -->\n\n## 相关链接\n\n- [按类分组](options-by-class.md) · [字母索引](options-index.md) · [应用设置 Setting](setting.md)\n",
    'setting.md' => "# 应用设置（Setting）\n\n## 简介\n\n（手写）\n\n## 设置键\n\n<!-- GEN:settingkeys start -->\n<!-- GEN:settingkeys end -->\n\n## 相关选项\n\n<!-- GEN:settingoptions start -->\n<!-- GEN:settingoptions end -->\n\n## 相关链接\n\n- [应用选项总览](options.md)\n",
];

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
        if (!isset(HIDDEN_DESC[$key])) {
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
    $bad_encoding = [];
    foreach ($targets as $file => $content) {
        if (!mb_check_encoding($content, 'UTF-8')) {
            $bad_encoding[] = $file;
        }
        $path = REF_DIR . '/' . $file;
        $old = is_file($path) ? (string) file_get_contents($path) : null;
        if ($old === $content) {
            continue;
        }
        $changed[] = $file;
        if (!$check) {
            file_put_contents($path, $content);
        }
    }
    foreach ($blocks as $file => $spec) {
        $path = REF_DIR . '/' . $file;
        $old = is_file($path) ? (string) file_get_contents($path) : SKELETONS[$file];
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
    if ($check) {
        foreach ($problems as $p) {
            fwrite(STDERR, '!! ' . $p . "\n");
        }
        if ($changed) {
            fwrite(STDERR, "stale pages (" . count($changed) . "): " . implode(', ', $changed) . "\n");
            return 1;
        }
        echo "options docs are up to date\n";
        return $problems ? 1 : 0;
    }

    echo "written: " . ($changed ? implode(', ', $changed) : '(nothing changed)') . "\n";
    foreach ($problems as $p) {
        echo '!! ' . $p . "\n";
    }
    return $problems ? 1 : 0;
}

exit(main());
