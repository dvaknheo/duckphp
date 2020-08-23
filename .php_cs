<?php
$header = <<<'EOF'
DuckPhp
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
        'ordered_imports'=>true,
		'no_blank_lines_before_namespace'=>false,
		'single_blank_line_before_namespace'=>true,
    ])
    ->setFinder($finder)
    ->setUsingCache(false);
