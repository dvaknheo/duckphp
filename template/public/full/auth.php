<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
require_once __DIR__.'/../../../autoload.php';  // @DUCKPHP_HEADFILE
$project_root = realpath(__DIR__).'/SimpleAuth/';
if (!class_exists(\System\Base\App::class)) {
    \DuckPhp\DuckPhp::assignPathNamespace($project_root , "SimpleAuth\\"); 
    \DuckPhp\DuckPhp::runAutoLoader();
}

$options = [
    'path' => $project_root,
    'is_debug'=>true,
    'use_setting_file'=>true,
    'setting_file_ignore_exists'=>true,

    'ext'=>[
        \SimpleAuth\System\App::class => true,
    ],
];
class MainOverrider extends SimpleAuth\Controller\Main
{
    public function register()
    {
        echo "第三方注册页面已被类 ".static::class." 更改";
        return parent::register();
    }
}
\DuckPhp\DuckPhp::RunQuickly($options,function(){
   \DuckPhp\DuckPhp::replaceControllerSingelton(SimpleAuth\Controller\Main::class,MainOverrider::class);

});
