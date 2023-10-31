<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
require_once(__DIR__.'/../../autoload.php');        // @DUCKPHP_HEADFILE

use DuckPhp\Core\Route;

class MainController
{
    public function action_index()
    {
        var_dump("这演示只用路由类，其他类都不要的情况");
        var_dump("Just route test done");
        var_dump(DATE(DATE_ATOM));
    }
    public function action_i()
    {
        phpinfo();
    }
}
$options = [
    'namespace_controller' => '\\', // 默认的是 Controller。 我们不需要这一层
];
$flag = Route::RunQuickly($options);
if (!$flag) {
    header(404,'no');
    echo "404!";
}
