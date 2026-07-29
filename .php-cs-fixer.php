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
return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR2' => true,
        'header_comment' => [
            'comment_type' => 'PHPDoc',
            'header' => $header,
            'separate' => 'none',
            'location' => 'after_declare_strict',
        ],
        'declare_strict_types' => true,
        'binary_operator_spaces'=>true,
        'ordered_imports'=>true,
        'no_whitespace_in_blank_line'=>true,
        'blank_lines_before_namespace'=>false,
        'no_blank_lines_before_namespace'=>false,
        'single_blank_line_before_namespace'=>true,
    ])
    ->setFinder($finder)
    ->setUsingCache(false);
