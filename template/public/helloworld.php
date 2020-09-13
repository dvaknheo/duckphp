<?php
require_once(__DIR__.'/../../autoload.php');        // @DUCKPHP_HEADFILE

class Main
{
    public function index()
    {
        echo "hello world";
    }
}
$options=[
    'namespace_controller'=>"\\",   // 本例特殊，设置控制器的命名空间为根
    'skip_setting_file'=>true,      // 本例特殊，跳过配置文件
];
\DuckPhp\DuckPhp::RunQuickly($options);