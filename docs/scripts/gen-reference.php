<?php declare(strict_types=1);
/**
 * gen-reference.php - DuckPhp reference documentation fact-skeleton generator.
 *
 * Parses src/ classes/interfaces/traits WITHOUT loading them (tokenizer based),
 * and renders a Markdown "fact skeleton" per file into the reference tree.
 * Facts: namespace, declaration kind, extends/implements, used traits,
 *        constants, options defaults, method signatures + docblock first line.
 * Human-written prose (intro, option descriptions, examples) is left as
 *   <!-- TODO(人工): ... -->
 * placeholders, to be filled during the reference manual rewrite.
 *
 * Encoding: output files are UTF-8 without BOM, LF line endings.
 *
 * Usage:
 *   php docs/scripts/gen-reference.php facts <src-rel-path>   dump parse result
 *   php docs/scripts/gen-reference.php skeleton [--out DIR] [--file REL]
 *        default --out docs/zh/reference/.work/skeleton
 *        generate all files under src/, or only one file when --file given
 *   php docs/scripts/gen-reference.php verify --file docs/zh/reference/X.md
 *        compare option keys / method lists in an md against its source file
 */

const VER = '1.0.0';

const ROOT = __DIR__ . '/../..';
const SRC_DIR = ROOT . '/src';
const REF_DIR = ROOT . '/docs/zh/reference';
const SKELETON_DEFAULT = REF_DIR . '/.work/skeleton';

// ---------------------------------------------------------------------------
// token helpers (PHP 7.4 compatible; no 8.x-only syntax)
// ---------------------------------------------------------------------------

/** Return tokens array with [id,text] kept, whitespace skipped. */
function tk(string $code): array
{
    $out = [];
    foreach (token_get_all($code) as $t) {
        if (is_array($t)) {
            if ($t[0] === T_WHITESPACE) {
                continue;
            }
            $out[] = ['id' => $t[0], 'text' => $t[1], 'line' => $t[2]];
        } else {
            $out[] = ['id' => null, 'text' => $t, 'line' => null];
        }
    }
    return $out;
}

function tok_is($tok, $id): bool
{
    return $tok !== null && $tok['id'] === $id;
}

function tok_text_is($tok, string $s): bool
{
    return $tok !== null && $tok['text'] === $s;
}

const MODIFIERS = [T_PUBLIC, T_PROTECTED, T_PRIVATE, T_STATIC, T_FINAL, T_ABSTRACT, T_VAR];

function is_modifier($tok): bool
{
    if ($tok === null || $tok['id'] === null) {
        return false;
    }
    foreach (MODIFIERS as $m) {
        if ($tok['id'] === $m) {
            return true;
        }
    }
    return false;
}

/** Collect an (optionally qualified, \ separated) name starting at $i; returns [text, nextIndex]. */
function read_name(array $ts, int $i): array
{
    $s = '';
    while ($i < count($ts)) {
        $t = $ts[$i];
        $name_toks = [T_STRING, T_NS_SEPARATOR];
        if (defined('T_NAME_QUALIFIED')) {
            $name_toks[] = T_NAME_QUALIFIED;
        }
        if (in_array($t['id'], $name_toks, true)) {
            $s .= $t['text'];
            $i++;
        } else {
            break;
        }
    }
    return [$s, $i];
}

/** Whether $i points at a comment/docblock token. */
function is_comment($tok): bool
{
    return $tok !== null && ($tok['id'] === T_COMMENT || $tok['id'] === T_DOC_COMMENT);
}

/** Find the doc comment immediately before token index $i (skipping comments only). */
function prev_doc(array $ts, int $i): ?string
{
    $j = $i - 1;
    $found = null;
    while ($j >= 0) {
        $t = $ts[$j];
        if (is_comment($t)) {
            $found = $t;
            $j--;
            continue;
        }
        if (tok_text_is($t, ';') || tok_text_is($t, '{') || tok_text_is($t, '}')) {
            break;
        }
        // other significant token in between -> no doc
        if ($t['id'] !== null) {
            break;
        }
        $j--;
    }
    if ($found === null || $found['id'] !== T_DOC_COMMENT) {
        return null;
    }
    // first meaningful line of the docblock
    $lines = preg_split('/\R/', trim($found['text'], "/ \t\n\r\0\x0B*"));
    foreach ($lines as $l) {
        $l = trim($l, " \t*");
        if ($l === '' || strncmp($l, '@', 1) === 0) {
            continue;
        }
        return $l;
    }
    return null;
}

/** Advance over balanced () [] {} starting at open token; returns index AFTER the matching closer (or last). */
function skip_balanced(array $ts, int $i): int
{
    $depth = 0;
    $n = count($ts);
    for (; $i < $n; $i++) {
        $c = $ts[$i]['text'];
        if ($c === '(' || $c === '[' || $c === '{') {
            $depth++;
        } elseif ($c === ')' || $c === ']' || $c === '}') {
            $depth--;
            if ($depth === 0) {
                return $i + 1;
            }
        }
    }
    return $i;
}

/** Collect text between indexes [from, to). */
function slice_text(array $ts, int $from, int $to): string
{
    $s = '';
    for ($i = $from; $i < $to && $i < count($ts); $i++) {
        $s .= $ts[$i]['text'];
    }
    return $s;
}

/** Line of token $i. */
function line_of(array $ts, int $i)
{
    return $ts[$i]['line'] ?? null;
}

// ---------------------------------------------------------------------------
// structure parse
// ---------------------------------------------------------------------------

/** Parse balanced code from $i (at a '{') to matching '}', returns [innerFrom, endAfterClose]. */
function brace_span(array $ts, int $i): array
{
    $end = skip_balanced($ts, $i);
    return [$i + 1, $end - 1]; // index of '}' is end-1
}

/**
 * Scan class/interface/trait body tokens [from, to) (the '{' '}' excluded).
 * Returns members: constants, properties, methods, used traits.
 */
function scan_body(array $ts, int $from, int $to): array
{
    $members = ['consts' => [], 'props' => [], 'methods' => [], 'traits' => []];
    $mods = [];
    $expect = true; // at statement boundary
    $i = $from;
    $n = count($ts);
    $depth = 0;
    while ($i < $to) {
        $t = $ts[$i];
        $c = $t['text'];
        if ($c === '(' || $c === '[' || $c === '{') {
            $depth++;
            $i++;
            continue;
        }
        if ($c === ')' || $c === ']' || $c === '}') {
            $depth--;
            if ($depth === 0) {
                $expect = true; // finished a statement/method body
            }
            $i++;
            continue;
        }
        if ($c === ';') {
            $expect = true;
            $mods = [];
            $i++;
            continue;
        }
        if ($depth > 0) {
            $i++;
            continue;
        }
        if (is_comment($t)) {
            $i++;
            continue;
        }
        if ($expect && is_modifier($t)) {
            $mods[] = $t['text'];
            $i++;
            continue;
        }
        if ($expect && tok_is($t, T_FUNCTION)) {
            $members['methods'][] = parse_method($ts, $i, $mods, $to);
            $mods = [];
            // jump past body or ';'
            $j = $i + 1;
            // function name
            if (isset($ts[$j]) && tok_text_is($ts[$j], '&')) {
                $j++;
            }
            if (isset($ts[$j]) && $ts[$j]['id'] === T_STRING) {
                $j++;
            }
            // args
            while ($j < $n && !tok_text_is($ts[$j], '(')) {
                $j++;
            }
            if ($j < $n) {
                $j = skip_balanced($ts, $j);
            }
            // return type
            while ($j < $n && !tok_text_is($ts[$j], '{') && !tok_text_is($ts[$j], ';')) {
                $j++;
            }
            if ($j < $n && tok_text_is($ts[$j], '{')) {
                $j = skip_balanced($ts, $j);
            } elseif ($j < $n) {
                $j++;
            }
            $i = $j;
            $expect = true;
            continue;
        }
        if ($expect && tok_is($t, T_CONST)) {
            $members['consts'][] = parse_const($ts, $i, $mods);
            $mods = [];
            $j = $i + 1;
            while ($j < $n && !tok_text_is($ts[$j], ';')) {
                $j++;
            }
            $i = $j + 1;
            $expect = true;
            continue;
        }
        if ($expect && tok_is($t, T_VARIABLE)) {
            $members['props'][] = parse_prop($ts, $i, $mods, $to);
            $mods = [];
            // jump to ';'
            $j = $i + 1;
            $d = 0;
            while ($j < $n) {
                $cc = $ts[$j]['text'];
                if ($cc === '(' || $cc === '[' || $cc === '{') {
                    $d++;
                } elseif ($cc === ')' || $cc === ']' || $cc === '}') {
                    $d--;
                } elseif ($cc === ';' && $d <= 0) {
                    break;
                }
                $j++;
            }
            $i = $j + 1;
            $expect = true;
            continue;
        }
        if ($expect && tok_is($t, T_USE)) {
            // trait use statement: `use A, B;` or `use A, B { adjustments };`
            $names = [];
            $cur = '';
            $j = $i + 1;
            while ($j < $n) {
                $tt = $ts[$j];
                if (tok_is($tt, T_STRING) || tok_is($tt, T_NS_SEPARATOR)) {
                    $cur .= $tt['text'];
                } elseif (tok_text_is($tt, ',')) {
                    if ($cur !== '') {
                        $names[] = $cur;
                        $cur = '';
                    }
                } elseif (tok_text_is($tt, ';')) {
                    if ($cur !== '') {
                        $names[] = $cur;
                    }
                    $j++;
                    break;
                } elseif (tok_text_is($tt, '{')) {
                    // trait name already collected; consume the whole `{ ... }` adjustments block
                    // (it may contain method aliasing statements) as one balanced unit
                    if ($cur !== '') {
                        $names[] = $cur;
                    }
                    break; // stop scanning names; balanced-block skip handled below
                } elseif (tok_text_is($tt, ')')) {
                    break; // safety: not a class-body use
                }
                $j++;
            }
            // if we paused at a '{', consume the balanced adjustments block (and optional ';')
            if ($j < $n && tok_text_is($ts[$j], '{')) {
                $j = skip_balanced($ts, $j); // returns index just after the matching '}'
                if ($j < $n && tok_text_is($ts[$j], ';')) {
                    $j++;
                }
            }
            foreach ($names as $nm) {
                $members['traits'][] = $nm;
            }
            $i = $j;
            $expect = true;
            continue;
        }
        // anything else at top level: not a member we track; skip a statement-ish unit
        if ($expect) {
            $expect = false;
        }
        $i++;
    }
    return $members;
}

/** parse one method; $i at T_FUNCTION. */
function parse_method(array $ts, int $i, array $mods, int $to): array
{
    $n = count($ts);
    $doc = prev_doc($ts, $i);
    $mod = [];
    $is_abstract = false;
    foreach ($mods as $m) {
        $mm = trim($m);
        if ($mm === 'public' || $mm === 'protected' || $mm === 'private') {
            $mod['visibility'] = $mm;
        } elseif ($mm === 'static') {
            $mod['static'] = true;
        } elseif ($mm === 'abstract') {
            $is_abstract = true;
        } elseif ($mm === 'final') {
            $mod['final'] = true;
        }
    }
    $j = $i + 1;
    $by_ref = false;
    if (isset($ts[$j]) && tok_text_is($ts[$j], '&')) {
        $by_ref = true;
        $j++;
    }
    $name = null;
    if (isset($ts[$j]) && $ts[$j]['id'] === T_STRING) {
        $name = $ts[$j]['text'];
        $line = $ts[$j]['line'];
        $j++;
    }
    while ($j < $n && !tok_text_is($ts[$j], '(')) {
        $j++;
    }
    $args_end = skip_balanced($ts, $j);
    $args_text = slice_text($ts, $j, $args_end);
    // normalize the argument list text for documentation readability:
    //   'array$options, ?object$context = null' -> 'array $options, ?object $context = null'
    $args_text = (string) preg_replace('/\s+/', ' ', trim($args_text));
    $args_text = (string) preg_replace('/(?<=[A-Za-z_0-9])(\$[A-Za-z_])/', ' $1', $args_text);
    $args_text = (string) preg_replace('/\s*,\s*/', ', ', $args_text); // space after commas
    $args_text = (string) preg_replace('/\)\s*$/', ')', $args_text);    // drop any trailing space before ')'
    // return type
    $ret = '';
    $k = $args_end;
    if ($k < $n && tok_text_is($ts[$k], ':')) {
        $k++;
        $rstart = $k;
        while ($k < $n && !tok_text_is($ts[$k], '{') && !tok_text_is($ts[$k], ';')) {
            $k++;
        }
        $ret = ' : ' . trim(slice_text($ts, $rstart, $k));
    }
    $has_body = $k < $n && tok_text_is($ts[$k], '{');
    $ret = (string) preg_replace('/^ : /', ': ', $ret); // normalize to ': type'
    $sig = 'function ' . $name . $args_text . $ret;
    if ($is_abstract) {
        $sig = 'abstract ' . $sig;
    }
    if (!empty($mod['final'])) {
        $sig = 'final ' . $sig;
    }
    $out = [
        'name' => $name,
        'visibility' => $mod['visibility'] ?? 'public',
        'static' => !empty($mod['static']),
        'abstract' => $is_abstract,
        'by_ref' => $by_ref,
        'signature' => $sig,
        'doc' => $doc,
        'line' => $line ?? null,
        'has_body' => $has_body,
    ];
    return $out;
}

function parse_const(array $ts, int $i, array $mods): array
{
    $n = count($ts);
    $doc = prev_doc($ts, $i);
    $visibility = 'public';
    foreach ($mods as $m) {
        $mm = trim($m);
        if ($mm === 'private') {
            $visibility = 'private';
        } elseif ($mm === 'protected') {
            $visibility = 'protected';
        }
    }
    $j = $i + 1;
    $name = null;
    if (isset($ts[$j]) && $ts[$j]['id'] === T_STRING) {
        $name = $ts[$j]['text'];
        $line = $ts[$j]['line'];
        $j++;
    }
    while ($j < $n && !tok_text_is($ts[$j], '=') && !tok_text_is($ts[$j], ';')) {
        $j++;
    }
    $val = '';
    if ($j < $n && tok_text_is($ts[$j], '=')) {
        $j++;
        $d = 0;
        $vstart = $j;
        while ($j < $n) {
            $cc = $ts[$j]['text'];
            if ($cc === '(' || $cc === '[' || $cc === '{') {
                $d++;
            } elseif ($cc === ')' || $cc === ']' || $cc === '}') {
                $d--;
                if ($d < 0) {
                    break;
                }
            } elseif ($cc === ';' && $d === 0) {
                break;
            }
            $j++;
        }
        $val = preg_replace('/\s+/', ' ', trim(slice_text($ts, $vstart, $j)));
        $val = truncate($val, 160);
    }
    return ['name' => $name, 'value' => $val, 'visibility' => $visibility, 'doc' => $doc, 'line' => $line ?? null];
}

function parse_prop(array $ts, int $i, array $mods, int $to): array
{
    $n = count($ts);
    $doc = prev_doc($ts, $i);
    $visibility = 'public';
    $static = false;
    foreach ($mods as $m) {
        $mm = trim($m);
        if ($mm === 'private') {
            $visibility = 'private';
        } elseif ($mm === 'protected') {
            $visibility = 'protected';
        } elseif ($mm === 'static') {
            $static = true;
        }
    }
    $name = $ts[$i]['text'];
    $line = $ts[$i]['line'];
    $j = $i + 1;
    while ($j < $n && !tok_text_is($ts[$j], '=') && !tok_text_is($ts[$j], ';')) {
        $j++;
    }
    $default = null;
    if ($j < $n && tok_text_is($ts[$j], '=')) {
        $j++;
        $d = 0;
        $vstart = $j;
        while ($j < $n) {
            $cc = $ts[$j]['text'];
            if ($cc === '(' || $cc === '[' || $cc === '{') {
                $d++;
            } elseif ($cc === ')' || $cc === ']' || $cc === '}') {
                $d--;
                if ($d < 0) {
                    break;
                }
            } elseif ($cc === ';' && $d === 0) {
                break;
            }
            $j++;
        }
        $default = trim(slice_text($ts, $vstart, $j));
    }
    $out = [
        'name' => $name,
        'visibility' => $visibility,
        'static' => $static,
        'doc' => $doc,
        'line' => $line,
        'default' => $default,
        'options' => null,
    ];
    if ($default !== null && strncmp(ltrim($default), '[', 1) === 0) {
        $out['options'] = parse_assoc($ts, $vstart ?? 0, $j);
    }
    return $out;
}

/**
 * Parse an associative array literal between token indexes [from,to) (inside "[ ... ]").
 * Returns list of [key, value, line]; $ts may be the full file token list.
 */
function parse_assoc(array $ts, int $from, int $to): array
{
    // Parse a PHP `['k1' => v1, 'k2' => v2, ...]` literal and return info rows:
    //   array{key:string, value:string, line:?int}
    $items = [];
    $n = count($ts);
    // find the opening '[' of the options literal
    $i = $from;
    while ($i < $n && !tok_text_is($ts[$i], '[')) {
        $i++;
    }
    if ($i >= $n) {
        return $items;
    }
    $depth = 1;                 // we are inside the options array
    $i++;                       // move past the opening '['
    $pending_key = null;
    $pending_line = null;
    $value_start = null;

    $flushValue = function () use (&$items, &$pending_key, &$pending_line, &$value_start, $ts, $n) {
        if ($pending_key === null || $value_start === null) {
            return;
        }
        // value text spans tokens [value_start, scanEnd); decide scanEnd by reading a full value:
        $text = slice_text($ts, $value_start, $n);   // too wide; re-slice precisely below
        $pending_key = null;
        $pending_line = null;
        $value_start = null;
    };

    // helper: given an index just AFTER '=>', parse one full value, return [text, nextIndex]
    $readValue = function ($vi) use ($ts, $n) {
        while ($vi < $n && (is_comment($ts[$vi]) || $ts[$vi]['id'] === T_WHITESPACE)) {
            $vi++;
        }
        if ($vi >= $n) {
            return ['', $vi];
        }
        $vc = $ts[$vi]['text'];
        if ($vc === '[' || $vc === '{' || $vc === '(') {
            $end = skip_balanced($ts, $vi);
            return [trim(preg_replace('/\\s+/', ' ', trim(slice_text($ts, $vi, $end)))), $end];
        }
        // scalar: take tokens until a top-level ',' or closer ']' (balanced already closed)
        $out = '';
        $k = $vi;
        while ($k < $n) {
            $tt = $ts[$k];
            if ($tt['id'] === T_WHITESPACE || is_comment($tt)) {
                $k++;
                continue;
            }
            $cc = $tt['text'];
            if ($cc === ',' || $cc === ']') {
                break;
            }
            $out .= $cc;
            $k++;
        }
        return [trim($out), $k];
    };

    for (; $i < $n && $depth > 0; $i++) {
        $t = $ts[$i];
        if (is_comment($t)) {
            continue;
        }
        $c = $t['text'];
        if ($c === '[' || $c === '(' || $c === '{') {
            $depth++;
            continue;
        }
        if ($c === ']' || $c === ')' || $c === '}') {
            $depth--;
            if ($depth === 0) {
                // reached end of options literal
                if ($pending_key !== null) {
                    // no trailing comma case: value ends right before this closer
                }
                break;
            }
            continue;
        }
        if ($depth !== 1) {
            continue; // inside a nested value, ignore
        }
        // at top level: could be a key candidate followed by '=>'
        $is_key = ($t['id'] === T_CONSTANT_ENCAPSED_STRING || $t['id'] === T_LNUMBER);
        if (!$is_key) {
            continue;
        }
        $j = $i + 1;
        while ($j < $n && (is_comment($ts[$j]) || $ts[$j]['id'] === T_WHITESPACE)) {
            $j++;
        }
        if ($j >= $n || !tok_text_is($ts[$j], '=>')) {
            continue;
        }
        // ---- found a 'key' => value  pair ----
        list($value, $afterValue) = $readValue($j + 1);
        $key = trim($t['text'], "'\"");
        $items[] = ['key' => $key, 'value' => $value, 'line' => $t['line'] ?? null];
        // jump the loop to just after the value (stops before the separating comma / closer)
        if ($afterValue <= $i) {
            $i = $j; // safety
        } else {
            $i = $afterValue - 1; // for-loop does ++
        }
    }
    return $items;
}

function truncate(string $s, int $len): string
{
    $s = trim($s);
    if (mb_strlen($s) > $len) {
        return mb_substr($s, 0, $len) . ' …';
    }
    return $s;
}

/** value text escaping for markdown table cells */
function md_escape(string $s): string
{
    $s = str_replace('|', '\\|', $s);
    $s = str_replace("\n", ' ', $s);
    return $s;
}

// ---------------------------------------------------------------------------
// file level parse
// ---------------------------------------------------------------------------

function parse_file(string $rel): array
{
    $code = file_get_contents(SRC_DIR . '/' . $rel);
    $ts = tk($code);
    $n = count($ts);
    $namespace = '';
    $decls = [];
    $i = 0;
    $doc = null;
    while ($i < $n) {
        $t = $ts[$i];
        if (is_comment($t)) {
            $doc = $t['id'] === T_DOC_COMMENT ? $t : $doc;
            $i++;
            continue;
        }
        if (tok_is($t, T_NAMESPACE)) {
            list($nm, $i) = read_name($ts, $i + 1);
            $namespace = $nm;
            // skip to ; or {
            while ($i < $n && !tok_text_is($ts[$i], ';') && !tok_text_is($ts[$i], '{')) {
                $i++;
            }
            $i++;
            continue;
        }
        if (tok_is($t, T_USE)) {
            // skip top-level use statements (imports)
            $j = $i + 1;
            $d = 0;
            while ($j < $n) {
                $cc = $ts[$j]['text'];
                if ($cc === '(' || $cc === '{') {
                    $d++;
                } elseif ($cc === ')') {
                    $d--;
                } elseif ($cc === ';' && $d <= 0) {
                    break;
                }
                $j++;
            }
            $i = $j + 1;
            continue;
        }
        $kind = null;
        if (tok_is($t, T_CLASS)) {
            $kind = 'class';
        } elseif (tok_is($t, T_INTERFACE)) {
            $kind = 'interface';
        } elseif (tok_is($t, T_TRAIT)) {
            $kind = 'trait';
        }
        if ($kind !== null) {
            // skip `::class`/`new class` false positives: previous significant token
            $prev = $i - 1;
            while ($prev >= 0 && $ts[$prev]['id'] === T_COMMENT) {
                $prev--;
            }
            $is_static_ref = $prev >= 0 && tok_text_is($ts[$prev], '::');
            $is_anon = $prev >= 0 && tok_is($ts[$prev], T_NEW);
            if ($is_static_ref || $is_anon) {
                // anonymous class: skip its body safely
                $j = $i + 1;
                while ($j < $n && !tok_text_is($ts[$j], '{')) {
                    $j++;
                }
                $i = skip_balanced($ts, $j);
                continue;
            }
            list($name, $i) = read_name($ts, $i + 1);
            $extends = null;
            $implements = [];
            $abstract = false;
            if ($kind === 'class') {
                // check abstract/final before class keyword (we come after keyword; scan back)
                $b = $i;
                // look ahead
            }
            // look ahead for extends / implements
            while ($i < $n && !tok_text_is($ts[$i], '{')) {
                $t2 = $ts[$i];
                if (tok_is($t2, T_EXTENDS)) {
                    $extends = [];
                    list($first_name, $i) = read_name($ts, $i + 1);
                    $extends[] = $first_name;
                    // interfaces may extend several: 'interface A extends B, C'
                    while ($i < $n && tok_text_is($ts[$i], ',')) {
                        list($more, $i) = read_name($ts, $i + 1);
                        $extends[] = $more;
                    }
                    if ($kind === 'class') {
                        $extends = $extends[0]; // class extends single parent
                    }
                    continue;
                }
                if (tok_is($t2, T_IMPLEMENTS)) {
                    $i++;
                    while ($i < $n && !tok_text_is($ts[$i], '{')) {
                        list($in, $i) = read_name($ts, $i);
                        if ($in !== '') {
                            $implements[] = $in;
                        }
                        if ($i < $n && tok_text_is($ts[$i], ',')) {
                            $i++;
                            continue;
                        }
                        break;
                    }
                    continue;
                }
                if (is_comment($t2) || $t2['id'] === T_STRING || $t2['id'] === T_NS_SEPARATOR) {
                    $i++;
                    continue;
                }
                $i++;
            }
            // abstract check: token right before T_CLASS
            if ($kind === 'class') {
                $b = $i;
                $p = $prev;
                if ($p >= 0 && $ts[$p]['id'] === T_ABSTRACT) {
                    $abstract = true;
                }
                // note: abstract is usually before 'class' e.g. 'abstract class X'; prev token to class is 'abstract'
            }
            $body_from = $i; // at '{'
            list($inner_from, $inner_to) = brace_span($ts, $i);
            $members = scan_body($ts, $inner_from, $inner_to);
            $file_doc = $doc;
            $decls[] = [
                'kind' => $kind,
                'abstract' => $abstract,
                'name' => $name,
                'namespace' => $namespace,
                'extends' => $extends,
                'implements' => $implements,
                'file_doc' => $file_doc,
                'members' => $members,
            ];
            $doc = null;
            $i = $inner_to + 1; // past '}'
            continue;
        }
        $i++;
    }
    return ['namespace' => $namespace, 'rel' => $rel, 'decls' => $decls];
}

// ---------------------------------------------------------------------------
// markdown rendering
// ---------------------------------------------------------------------------

/** reference md basename from src relative path: 'Core/App.php' -> 'Core-App', 'DuckPhp.php' -> 'DuckPhp' */
function md_base_from_rel(string $rel): string
{
    $p = str_replace('\\', '/', $rel);
    $p = preg_replace('/\.php$/', '', $p);
    $parts = explode('/', $p);
    if (count($parts) === 1) {
        return $parts[0];
    }
    return implode('-', $parts);
}

function decl_kind_label(array $d): string
{
    $s = $d['kind'];
    if ($d['kind'] === 'class' && $d['abstract']) {
        $s = 'abstract class';
    }
    return $s;
}

function decl_headline(array $d): string
{
    $s = decl_kind_label($d) . ' ' . $d['name'];
    if ($d['extends']) {
        $ex = is_array($d['extends']) ? implode(', ', $d['extends']) : $d['extends'];
        $s .= ' extends ' . $ex;
    }
    if ($d['implements']) {
        $s .= ' implements ' . implode(', ', $d['implements']);
    }
    return $s;
}

function render_method_group(array $methods, string $group): string
{
    $sel = array_values(array_filter($methods, function ($m) use ($group) {
        return $m['visibility'] === $group;
    }));
    if (!$sel) {
        return '';
    }
    $out = '### ' . $group . '方法' . "\n\n";
    // '公共方法' '受保护方法' '私有方法'
    $out = '### ' . ['public' => '公共方法', 'protected' => '受保护方法', 'private' => '私有方法'][$group] . "\n\n";
    foreach ($sel as $m) {
        $head = '`' . $m['signature'] . '`';
        $out .= $head . "\n\n";
        if (!empty($m['doc'])) {
            $out .= '- 源码注释：' . md_escape($m['doc']) . "\n";
        }
        $out .= "- <!-- TODO(人工)：说明该方法用途/调用时机；必要时附示例 -->\n\n";
    }
    return $out;
}

function render_decl_md(array $d, string $rel): string
{
    $fq = ($d['namespace'] !== '' ? $d['namespace'] . '\\' : '') . $d['name'];
    $out = '';
    $out .= '# ' . $fq . "\n\n";
    $out .= '> 事实骨架由 `docs/scripts/gen-reference.php` 自动生成（源：`' . $rel . '`）；润色完成后请删除本提示。' . "\n\n";
    // ---- 简介 ----
    $out .= "## 简介\n\n";
    if (!empty($d['file_doc'])) {
        $raw = is_array($d['file_doc']) ? ($d['file_doc']['text'] ?? '') : $d['file_doc'];
        $line = trim((string) preg_replace('/\s+/', ' ', trim(trim((string) $raw, "/ \t\n\r\0\x0B*"), " \t*")));
        if ($line !== '') {
            $out .= '> 源码文件头说明：' . md_escape($line) . "\n\n";
        }
    }
    $out .= "<!-- TODO(人工)：用 2~5 句说明该类/接口/Trait 在框架中的角色与典型用法 -->\n\n";
    // ---- 类信息 ----
    $out .= "## 类信息\n\n";
    $out .= '- 命名空间：`' . $d['namespace'] . "`\n";
    $out .= '- 声明：`' . decl_headline($d) . "`\n";
    if ($d['implements'] && $d['kind'] !== 'interface') {
        $out .= '- 接口：`' . implode('`, `', $d['implements']) . "`\n";
    }
    if ($d['members']['traits']) {
        $out .= '- 使用的 Trait：`' . implode('`, `', $d['members']['traits']) . "`\n";
    }
    $out .= "\n";
    // ---- 选项 ----
    $props = $d['members']['props'];
    $list = [];
    $has_any = false;
    foreach ($props as $pr) {
        if ($pr['name'] === '$options' || preg_match('/\$.*options$/i', $pr['name'])) {
            if ($pr['options'] !== null) {
                $has_any = true;
                foreach ($pr['options'] as $o) {
                    $list[] = ['key' => $o['key'], 'value' => $o['value']]; // merges e.g. $options + $kernel_options
                }
            }
        }
    }
    if ($has_any) {
        // de-duplicate by key (last occurrence wins) keeping source order
        $seen = [];
        $uniq = [];
        foreach ($list as $o) {
            if (!isset($seen[$o['key']])) {
                $seen[$o['key']] = true;
                $uniq[] = $o;
            }
        }
        $out .= "## 选项\n\n";
        $out .= "| 选项 | 默认值 | 说明 |\n|---|---|---|\n";
        foreach ($uniq as $opt) {
            $val = $opt['value'] === '' ? '（空数组/空值）' : '`' . md_escape($opt['value']) . '`';
            $out .= '| `' . $opt['key'] . '` | ' . $val . ' | <!-- TODO(人工)：说明 --> |' . "\n";
        }
        $out .= "\n";
    } elseif ($props) {
        foreach ($props as $pr) {
            if ($pr['name'] === '$options' || preg_match('/\$.*options$/i', $pr['name'])) {
                $out .= "## 选项\n\n";
                $out .= "```php\n" . $pr['name'] . ' = ' . truncate($pr['default'], 400) . "\n```\n";
                $out .= "\n<!-- TODO(人工)：解析以上默认值数组并整理为选项表，或说明该选项机制 -->\n\n";
                break;
            }
        }
    }
    // ---- 常量 ----
    if ($d['members']['consts']) {
        $out .= "## 常量\n\n";
        $out .= "| 常量 | 值 |\n|---|---|\n";
        foreach ($d['members']['consts'] as $c) {
            if ($c['visibility'] !== 'public') {
                continue;
            }
            $out .= '| `' . $c['name'] . '` | `' . md_escape($c['value']) . '` |' . "\n";
        }
        $out .= "\n";
    }
    // ---- 方法列表 ----
    $methods = $d['members']['methods'];
    if ($methods) {
        $out .= "## 方法列表\n\n";
        $g = render_method_group($methods, 'public');
        if ($g !== '') {
            $out .= $g . "\n";
        }
        $g = render_method_group($methods, 'protected');
        if ($g !== '') {
            $out .= $g . "\n";
        }
        $g = render_method_group($methods, 'private');
        if ($g !== '') {
            $out .= $g . "\n";
        }
    }
    $out .= "## 使用方式\n\n<!-- TODO(人工)：典型用法（初始化、调用、与其它组件的协作） -->\n\n";
    $out .= "## 相关链接\n\n<!-- TODO(人工)：链向 reference/guide 中相关文档 -->\n";
    return $out;
}

function render_file_md(array $parsed, string $rel): string
{
    $decls = $parsed['decls'];
    if (!$decls) {
        $name = md_base_from_rel($rel);
        $out = '# ' . $name . "\n\n";
        $out .= '> 事实骨架由 `docs/scripts/gen-reference.php` 自动生成（源：`' . $rel . '`）。' . "\n\n";
        $out .= "本文件在源码中没有顶层 `class` / `interface` / `trait` 声明；通常它是：\n\n";
        $out .= "- 一组以 `if (! function_exists(...))` 包裹定义**全局函数**的文件（如 `Core/Functions.php`），函数列表与说明需要你逐一手工编写；或\n";
        $out .= "- 被框架作为**直接输出的内嵌视图/脚本**的文件（如 `Ext/RouteHookWebInstallerView.php`），此时本文档解释它被谁引用、如何被输出。\n\n";
        $out .= "<!-- TODO(人工): 根据该文件实际用途补充正文；函数级文档可参考 Core-Functions.md 的风格 -->\n";
        return $out;
    }
    // main decl: kind==='class' preferred else first
    $main = null;
    foreach ($decls as $d) {
        if ($d['kind'] === 'class') {
            $main = $d;
            break;
        }
    }
    $main = $main ?? $decls[0];
    $out = render_decl_md($main, $rel);
    // secondary decls (same file)
    $others = array_values(array_filter($decls, function ($d) use ($main) {
        return $d !== $main;
    }));
    if ($others) {
        $out .= "\n---\n\n## 同文件的其他声明\n\n";
        foreach ($others as $d) {
            $out .= "\n### " . decl_kind_label($d) . ' ' . $d['name'] . "\n\n";
            $out .= "- 命名空间：`" . $d['namespace'] . "`\n";
            if ($d['implements']) {
                $out .= '- 接口：`' . implode('`, `', $d['implements']) . "`\n";
            }
            $out .= "\n";
            $out .= "#### 方法列表\n\n";
            $g = render_method_group($d['members']['methods'], 'public');
            if ($g !== '') {
                $out .= $g . "\n";
            }
            $g = render_method_group($d['members']['methods'], 'protected');
            if ($g !== '') {
                $out .= $g . "\n";
            }
        }
    }
    return $out;
}

// ---------------------------------------------------------------------------
// CLI
// ---------------------------------------------------------------------------

function main(array $argv): int
{
    $cmd = $argv[1] ?? 'skeleton';
    if ($cmd === 'facts') {
        $rel = $argv[2] ?? '';
        if ($rel === '') {
            fwrite(STDERR, "usage: php gen-reference.php facts <src-rel-path>\n");
            return 2;
        }
        $parsed = parse_file($rel);
        echo json_encode($parsed, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n";
        return 0;
    }
    if ($cmd === 'skeleton') {
        $out = SKELETON_DEFAULT;
        $only = null;
        $n = count($argv);
        for ($i = 2; $i < $n; $i++) {
            if ($argv[$i] === '--out' && $i + 1 < $n) {
                $out = $argv[++$i];
            } elseif ($argv[$i] === '--file' && $i + 1 < $n) {
                $only = $argv[++$i];
            }
        }
        if (!is_dir($out) && !mkdir($out, 0777, true) && !is_dir($out)) {
            fwrite(STDERR, "cannot create out dir: $out\n");
            return 2;
        }
        $files = [];
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(SRC_DIR, FilesystemIterator::SKIP_DOTS));
        foreach ($it as $f) {
            if ($f->isFile() && $f->getExtension() === 'php') {
                $rel = substr($f->getPathname(), strlen(SRC_DIR) + 1);
                $rel = str_replace('\\', '/', $rel);
                if ($only !== null && $rel !== $only) {
                    continue;
                }
                $files[] = $rel;
            }
        }
        sort($files);
        if ($only !== null && !$files) {
            fwrite(STDERR, "no such src file: $only\n");
            return 2;
        }
        $made = 0;
        $skipped = [];
        foreach ($files as $rel) {
            $parsed = parse_file($rel);
            $md = render_file_md($parsed, $rel);
            if ($md === '') {
                $skipped[] = $rel;
                continue;
            }
            $name = md_base_from_rel($rel) . '.md';
            file_put_contents($out . '/' . $name, $md);
            $made++;
        }
        echo "generated $made skeleton(s) into $out\n";
        if ($skipped) {
            echo 'skipped (no parseable decl): ' . implode(', ', $skipped) . "\n";
        }
        return 0;
    }
    if ($cmd === 'verify') {
        $file = null;
        $all = false;
        $n = count($argv);
        for ($i = 2; $i < $n; $i++) {
            if ($argv[$i] === '--file' && $i + 1 < $n) {
                $file = $argv[++$i];
            } elseif ($argv[$i] === '--all') {
                $all = true;
            }
        }
        if ($file === null && !$all) {
            fwrite(STDERR, "usage: php gen-reference.php verify --file <md-path> | --all\n");
            return 2;
        }
        $md_files = [];
        if ($file !== null) {
            $md_files[] = $file;
        } else {
            foreach (glob(REF_DIR . '/*.md') as $m) {
                if (preg_match('#/(index|options|options-by-class|options-index)\.md$#', $m)) {
                    continue;
                }
                $md_files[] = $m;
            }
        }
        $problems = 0;
        foreach ($md_files as $mdfile) {
            $base = pathinfo($mdfile, PATHINFO_FILENAME);
            $rel = str_replace('-', '/', $base) . '.php';
            $src = SRC_DIR . '/' . $rel;
            if (!file_exists($src)) {
                $src = SRC_DIR . '/' . $base . '.php';
            }
            if (!file_exists($src)) {
                echo "SKIP (no source) $base\n";
                continue;
            }
            $issues = verify_one($mdfile, $src);
            if ($issues) {
                $problems += count($issues);
                echo "== $base\n";
                foreach ($issues as $it) {
                    echo "   $it\n";
                }
            }
        }
        if ($problems === 0) {
            echo "verify OK\n";
        } else {
            echo "verify: $problems issue(s)\n";
        }
        return $problems === 0 ? 0 : 1;
    }
    fwrite(STDERR, "unknown command: $cmd\n");
    return 2;
}

/** compare option keys & method names in md vs source. returns list of issue strings. */
function verify_one(string $mdfile, string $src): array
{
    $md = file_get_contents($mdfile);
    $rel = str_replace('\\', '/', substr($src, strlen(SRC_DIR) + 1));
    $parsed = parse_file($rel);
    $issues = [];
    // ---- source facts ----
    $src_opts = [];
    foreach ($parsed['decls'] as $d) {
        foreach ($d['members']['props'] as $pr) {
            if (preg_match('/\$(.*[Oo]ptions?)$/', $pr['name']) && $pr['options'] !== null) {
                foreach ($pr['options'] as $o) {
                    $src_opts[$o['key']] = true;
                }
            }
        }
    }
    $src_methods = [];
    foreach ($parsed['decls'] as $d) {
        foreach ($d['members']['methods'] as $m) {
            if ($m['name'] !== null) {
                $src_methods[$m['name']] = true;
            }
        }
    }
    // ---- split md into sections; collect '## 选项' table keys ----
    $md_opts = [];
    $in_opts = false;
    // ---- collect '## 方法列表' signature lines ----
    $md_methods = [];
    $in_methods = false;
    foreach (explode("\n", $md) as $line) {
        if (preg_match('/^##\s+方法列表/', $line)) {
            $in_opts = false;
            $in_methods = true;
            continue;
        }
        if (preg_match('/^##\s+选项/', $line)) {
            $in_opts = true;
            $in_methods = false;
            continue;
        }
        if (preg_match('/^##\s+[^#]/', $line) && !preg_match('/^##\s+(选项|方法列表)/', $line)) {
            $in_opts = false;
            $in_methods = false;
            continue;
        }
        if ($in_opts && preg_match('/^\|\s*`([^`]+)`\s*\|/', $line, $m)) {
            $md_opts[$m[1]] = true;
        }
        if ($in_methods && preg_match('/^`.*\bfunction\s+([A-Za-z_]\w*)\s*\(/', $line, $m)) {
            $md_methods[$m[1]] = true;
        }
    }
    foreach ($src_opts as $k => $v) {
        if (!isset($md_opts[$k])) {
            $issues[] = "option missing in md: $k";
        }
    }
    foreach ($md_opts as $k => $v) {
        if (!isset($src_opts[$k])) {
            $issues[] = "option in md but not in source options: $k (check example tables)";
        }
    }
    foreach ($src_methods as $k => $v) {
        if (!isset($md_methods[$k])) {
            $issues[] = "method missing in md: $k";
        }
    }
    foreach ($md_methods as $k => $v) {
        if (!isset($src_methods[$k])) {
            $issues[] = "method in md but not in source: $k (check example code)";
        }
    }
    return $issues;
}

exit(main($argv));
