<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
//autoload file
$autoload_file = __DIR__.'../vendor/autoload.php';
if (is_file($autoload_file)) {
    require_once $autoload_file;
} else {
    $autoload_file = __DIR__.'/../../vendor/autoload.php';
    if (is_file($autoload_file)) {
        require_once $autoload_file;
    }
}
////////////////////////////////////////

use DuckPhp\Core\Route;

class MainController
{
    public function action_index()
    {
        echo("这演示只用路由类，其他类都不要的情况<br>\n");
        echo ("Just route test done<br>\n");
        echo (DATE(DATE_ATOM));
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
    header(404, 'no');
    echo "404!";
}
