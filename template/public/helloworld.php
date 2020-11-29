<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
require_once(__DIR__.'/../../autoload.php');        // @DUCKPHP_HEADFILE

class Main
{
    public function index()
    {
        echo "hello world";
    }
}
$options = [
    'namespace_controller' => "\\",   // 本例特殊，设置控制器的命名空间为根，而不是默认的 Controller
    // 还有百来个选项以上可用，详细请查看参考文档
];
\DuckPhp\DuckPhp::RunQuickly($options);
