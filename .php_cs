<?php
$header = <<<'EOF'
DuckPHP
From this time, you never be alone~
EOF;
$finder = PhpCsFixer\Finder::create()
    ->files()
    ->in(__DIR__.'/src')
    ->name('*.php')
;
return PhpCsFixer\Config::create()
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR2' => true,
        'header_comment' => [
            'commentType' => 'PHPDoc',
            'header' => $header,
            'separate' => 'none',
            'location' => 'after_declare_strict',
        ],
        'declare_strict_types' => true,
        'binary_operator_spaces'=>true,
    ])
    ->setFinder($finder)
    ->setUsingCache(false);
