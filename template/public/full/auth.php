<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
require_once __DIR__.'/../../../autoload.php';  // @DUCKPHP_HEADFILE
$project_root = realpath(__DIR__).'/SimpleAuth/';
if (!class_exists(\System\System\App::class)) {
    \DuckPhp\DuckPhp::assignPathNamespace($project_root , "SimpleAuth\\"); 
    \DuckPhp\DuckPhp::runAutoLoader();
}

$options = [
    'path' => $project_root,
    'is_debug'=>true,
    'setting_file_enable'=>true,
    'setting_file_ignore_exists'=>true,
    'ext'=>[
    ],
];
//$options['ext'][\SimpleAuth\System\App::class] = [
    // simple_auth_installed = false
//];
class MainOverrider extends SimpleAuth\Controller\Main
{
    public function register()
    {
        echo "第三方注册页面已被类 ".static::class." 更改";
        return parent::register();
    }
}
\SimpleAuth\System\App::RunQuickly($options,function(){
   \SimpleAuth\System\App::replaceControllerSingelton(SimpleAuth\Controller\Main::class,MainOverrider::class);

});
