<?php
require_once __DIR__ . '/../vendor/autoload.php';

use DuckPhp\DuckPhpAllInOne;

class ChildApp extends DuckPhpAllInOne
{
    public function action_index()
    {
        echo "I'm child.";
    }
}
class ParentApp extends DuckPhpAllInOne
{
    public $options = [
        'app' => [
            ChildApp::class => [
                'controller_url_prefix' => 'child/',
            ],
        ]
    ];
    public function action_index()
    {
        $url_child = __url('child/index');
        echo "I'm Parent. Goto <a href='{$url_child}'>child</a>";
    }
}
ParentApp::RunQuickly([]);