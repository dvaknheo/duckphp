<?php
/**
 * Aggregate coverage over every LibCoverage dump in test_coveragedumps/.
 *
 * Why: one class dump only proves "the test file that Begin()s this class hit these lines";
 * the project-wide number lives in test_reports/index.html, and stale dumps (from classes
 * that were since renamed/moved) inflate it. This script merges every dump per source file
 * and prints the files that still have unexecuted lines, so a full-suite run can be checked
 * class by class.
 *
 * Usage:
 *   php docs/scripts/covagg.php                 # scan ./test_coveragedumps
 *   php docs/scripts/covagg.php <dir>           # scan another dump dir
 *   php docs/scripts/covagg.php --quiet-ok      # only print gaps + the TOTAL line
 *
 * Note: always `rm -rf test_coveragedumps` before the full run you want to judge, otherwise
 * dumps of deleted/moved classes (e.g. Component/RouteLister.php) are counted too.
 * Note: files without executable lines (interfaces, empty-body classes) never show up in a
 * dump at all; that is not a gap.
 */
$root = getcwd();
if (file_exists($root . '/vendor/autoload.php')) {
    require $root . '/vendor/autoload.php';
}
$dir = $argv[1] ?? ($root . '/test_coveragedumps');
if (substr($dir, 0, 1) === '-') {
    $quietOk = true;
    $dir = $root . '/test_coveragedumps';
} else {
    $quietOk = in_array('--quiet-ok', $argv, true);
}

$dumps = [];
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
foreach ($it as $f) {
    if ($f->isFile() && $f->getExtension() === 'php') {
        $dumps[] = $f->getPathname();
    }
}
sort($dumps);
$merged = [];   // file => [line => [test name => true]]
$errors = 0;
foreach ($dumps as $dump) {
    try {
        $cov = @include $dump;
        if (!($cov instanceof SebastianBergmann\CodeCoverage\CodeCoverage)) {
            continue;
        }
        foreach ($cov->getData()->lineCoverage() as $file => $lines) {
            foreach ($lines as $line => $hits) {
                // xdebug3 driver records the list of tests that hit the line (array), older ones an int
                $hits = is_array($hits) ? $hits : ($hits ? [$hits] : []);
                foreach ($hits as $t) {
                    $merged[$file][$line][(string) $t] = true;
                }
                if (!isset($merged[$file][$line])) {
                    $merged[$file][$line] = [];
                }
            }
        }
    } catch (\Throwable $e) {
        $errors++;
        fwrite(STDERR, "skip $dump: " . $e->getMessage() . "\n");
    }
}
ksort($merged);
$totalLines = 0;
$totalCovered = 0;
$bad = 0;
foreach ($merged as $file => $lines) {
    ksort($lines);
    $src = file_exists($file) ? file_get_contents($file) : '';
    $srcLines = $src === '' ? [] : explode("\n", $src);
    $uncovered = array_keys(array_filter($lines, function ($h) {
        return empty($h);
    }));
    $totalLines += count($lines);
    $totalCovered += count($lines) - count($uncovered);
    if (!$uncovered) {
        if (!$quietOk) {
            printf("ok    %-58s %d/%d\n", str_replace($root . '/', '', $file), count($lines), count($lines));
        }
        continue;
    }
    $bad++;
    printf("MISS  %-58s %d/%d  (%d not executed)\n", str_replace($root . '/', '', $file), count($lines) - count($uncovered), count($lines), count($uncovered));
    foreach ($uncovered as $line) {
        printf("        %4d | %s\n", $line, trim($srcLines[$line - 1] ?? '?'));
    }
}
printf("\nTOTAL %d files, %d dumps, lines %d/%d (%.2f%%), %d files with gaps, %d unreadable dumps\n",
    count($merged), count($dumps), $totalCovered, $totalLines, $totalLines ? 100 * $totalCovered / $totalLines : 0, $bad, $errors);
