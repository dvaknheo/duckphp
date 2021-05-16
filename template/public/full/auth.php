<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
require_once __DIR__.'/../../../autoload.php';  // @DUCKPHP_HEADFILE
$project_root = realpath(__DIR__).'/SimpleAuth/';
if (!class_exists(\SimpleAuth\Base\App::class)) {
    \DuckPhp\DuckPhp::assignPathNamespace($project_root , "SimpleAuth\\"); 
    \DuckPhp\DuckPhp::runAutoLoader();
}

$options = [
    'path' => $project_root,
    //'path' => $project_root,
    'is_debug'=>true,
    'use_setting_file'=>true,

    'ext'=>[
        \SimpleAuth\Base\App::class => true,
    ],
];
class X extends SimpleAuth\Controller\Main
{
    public function register()
    {
        echo "第三方注册页面已被我更改";
        return parent::register();
    }
}
 SimpleAuth\Controller\Main::G(X::G());
\DuckPhp\DuckPhp::RunQuickly($options,function(){
   
});
