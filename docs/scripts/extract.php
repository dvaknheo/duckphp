<?php
$srcDir = __DIR__ . '/../../src';
$outFile = __DIR__ . '/../../funcs.txt';

$lines = [];

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($srcDir, FilesystemIterator::SKIP_DOTS)
);

$methodPattern = '/^\s*(?:(?:public|protected|private|static|final|abstract)\s+)*function\s+[A-Za-z_]\w*\s*\(/';

foreach ($iterator as $fileInfo) {
    if (!$fileInfo->isFile() || $fileInfo->getExtension() !== 'php') {
        continue;
    }
    foreach (file($fileInfo->getRealPath()) as $line) {
        if (preg_match($methodPattern, $line)) {
            $lines[] = $line;
            echo $line;
        }
    }
}

