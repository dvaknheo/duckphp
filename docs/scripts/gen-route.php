<?php declare(strict_types=1);
/**
 * gen-route.php — Route 版参考骨架生成（极简，只做一个文件）。
 *
 * 定场：只输出“机械可摘的三块”—— 命名空间/类声明、【方法列表】、【全部选项】，
 * 均来自源码的“行级原文”。不给缺省值做任何语义解析，也不生成中文说明（说明由人工补）。
 *
 * 用法：
 *   php scripts/gen-route.php Core/Route.php              # 处理 src/Core/Route.php
 *   输出到  docs/zh/reference/.work/skeleton/Core-Route.md
 *   php scripts/gen-route.php Core/Route.php out.md         # 可选指定输出
 */

error_reporting(E_ERROR | E_WARNING | E_PARSE);
$argc = $_SERVER['argc'] ?? 0;   // avoid cli-server interplay
$argv = $argv ?? [];

if ($argc < 2 || $argv[1] === '-h' || $argv[1] === '--help') {
    fwrite(STDERR, "usage: php scripts/gen-route.php <srcRel> [out]\n");
    exit(1);
}
$rel = $argv[1];
$src = __DIR__ . '/../../src/' . $rel;
if (!is_file($src)) {
    fwrite(STDERR, "src not found: $src\n");
    exit(1);
}

/* ---------- 文本剥离：只把注释与字面串做“空格化”，供数符号用 ---------- */
function inertify(string $line): string
{
    $line = preg_replace('#/\*[\s\S]*?\*/#', ' ', $line);
    $line = preg_replace('#^\s*//.*$#', '', $line);
    $line = preg_replace('#//.*$#', '', $line);
    $line = preg_replace('#"(?:[^"\\\\]|\\\\.)*"|\'(?:[^\'\\\\]|\\\\.)*\'#', ' ', $line);
    return $line ?? '';
}
function netBal(string $line, string $open, string $close): int
{
    $s = inertify($line);
    return substr_count($s, $open) - substr_count($s, $close);
}

$raw = file_get_contents($src);
if ($raw === false) {
    fwrite(STDERR, "read fail: $src\n");
    exit(1);
}
$lines = preg_split('/\R/', $raw);
$n = count($lines);

/* ---------- 1) 命名空间 + 第一个真正的 class/interface/trait 及其 body 范围 ---------- */
$namespace = '';
$kind = $cls = $decl = '';
$bodyStart = $bodyEnd = -1;

for ($i = 0; $i < $n && $bodyStart === -1; $i++) {
    $ln = $lines[$i];
    if ($namespace === '' && preg_match('/^\s*namespace\s+([\w\\\\]+)\s*;/', $ln, $m)) {
        $namespace = $m[1];
        continue;
    }
    if (strpos($ln, 'new class') !== false || strpos($ln, '::class') !== false) {
        continue; // 匿名类或类名引用，不当作“该类的声明”
    }
    if (preg_match('/^\s*(abstract\s+|final\s+)?(class|interface|trait)\s+(\w+)\b/', $ln, $m)) {
        $kind = $m[2];
        $cls  = $m[3];
        $decl = trim(preg_replace('/[;{].*$/', '', $ln));
        // 累计到 body 结束：以第一个出现 `{` 之后归零的行为收尾
        $bal = 0;
        $seenOpen = false;
        for ($j = $i; $j < $n; $j++) {
            if (netBal($lines[$j], '{', '}') > 0) {
                $seenOpen = true;
            }
            $bal += netBal($lines[$j], '{', '}');
            if ($seenOpen && $bal <= 0) {
                $bodyStart = $i;
                $bodyEnd   = $j;
                break;
            }
        }
    }
}

/* ---------- 2) body 内摘方法签名（按可见性分组时保持源码顺序） ---------- */
$methods = [];   // list of ['vis','sig']
if ($bodyStart >= 0) {
    for ($i = $bodyStart; $i <= $bodyEnd; $i++) {
        $ln = $lines[$i];
        if (preg_match('/^\s*(public|protected|private)\s+(?:static\s+)?function\s+\w+\s*\(/', $ln)) {
            $vis = (preg_match('/^\s*(public|protected|private)/', $ln, $p)) ? $p[1] : 'public';
            $methods[] = ['vis' => $vis, 'sig' => ltrim(rtrim($ln))];
        }
    }
}

/* ---------- 3) 首个 $…options = [ … ] 的“内层行原文” ---------- */
$optionsInner = [];
if ($bodyStart >= 0) {
    for ($i = $bodyStart; $i <= $bodyEnd; $i++) {
        if (preg_match('/\$[A-Za-z_]*[Oo]ptions\s*=\s*\[/', $lines[$i])) {
            $abal = 0;
            for ($j = $i + 1; $j <= $bodyEnd; $j++) {
                $b = netBal($lines[$j], '[', ']');
                $abal += $b;
                if ($abal < 0) {
                    $tail = preg_replace('/\]\s*;?\s*$/', '', rtrim($lines[$j]));
                    if (trim($tail) !== '') {
                        $optionsInner[] = $tail;
                    }
                    break;
                }
                $optionsInner[] = $lines[$j];
            }
            break; // 每个文件只取一个 options 块（示意）
        }
    }
}

/* ---------- 组装 md ---------- */
$mdBase = preg_replace('/\.php$/', '', $rel);
$mdBase = str_replace(['\\', '/'], '-', $mdBase);

$body = "# " . ($namespace !== '' ? $namespace . '\\' : '') . $cls . "\n\n";
$body .= "> 骨架由 scripts/gen-route.php 自动生成；请补齐叙述性章节（简介、选项讲解、使用方式、配置示例、注意事项、相关链接）后删除本行。\n\n";
$body .= "## 类信息\n\n";
$body .= "- 命名空间：`$namespace`\n";
$body .= "- 声明：`" . $decl . "`\n";
$body .= "- 类型：`" . $kind . "`\n\n";

// —— 全部选项 ——
$body .= "## 全部选项\n\n```php\n";
if ($optionsInner) {
    foreach ($optionsInner as $seg) {
        $body .= $seg . "\n";
    }
} else {
    $body .= "// 本脚本未识别到 $…options = [ … ] 字面量数组，请从源码手动摘录；如确实无 options 可删除本节。\n";
}
$body .= "```\n\n";

// —— 方法列表 ——
$body .= "## 方法列表\n\n";
$groups = ['public' => '公共方法', 'protected' => '受保护方法', 'private' => '私有方法'];
if ($methods) {
    // 先把每个可见性的方法各自聚合，再按 Route 文档习惯整段排（public / protected / private）
    $byVis = ['public' => [], 'protected' => [], 'private' => []];
    foreach ($methods as $mt) {
        $byVis[$mt['vis']][] = $mt['sig'];
    }
    $firstVis = true;
    foreach (['public', 'protected', 'private'] as $v) {
        if (!$byVis[$v]) {
            continue;
        }
        $body .= ($firstVis ? '' : "\n") . "### " . $groups[$v] . "\n\n";
        $firstVis = false;
        foreach ($byVis[$v] as $sig) {
            $body .= "    " . $sig . "\n";
            $body .= "（待补：一句用途说明）\n";
        }
    }
} else {
    $body .= "// 骨架未取到方法行（缩进风格异常？）请人工从源码补方法。\n";
}

// —— 相关链接占位（Route 版为人工清单）——
$body .= "\n## 相关链接\n\n<!-- 补：同类/相邻类参考链接，以及对 Guide 的指向（参考其它已完成的 doc） -->\n";

/* 输出 */
if (isset($argv[2]) && $argv[2] !== '') {
    $out = $argv[2];
} else {
    $dir = __DIR__ . '/../../docs/zh/reference/.work/skeleton';
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
    $out = $dir . '/' . $mdBase . '.md';
}
if (file_put_contents($out, $body) === false) {
    fwrite(STDERR, "write fail: $out\n");
    exit(1);
}
echo "wrote $out\n";
