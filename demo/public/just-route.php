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
    public function index()
    {
        // This entry point boots the Route class only - there is no App instance here,
        // so it cannot use __l() (that needs an initialised app); its text is English only.
        echo("This demo uses the Route class only, none of the other classes<br>\n");
        echo ("Just route test done<br>\n");
        echo (DATE(DATE_ATOM));
    }
    public function i()
    {
        phpinfo();
    }
}
$options = [
    'namespace_controller' => '\\', // the default is Controller; we do not need that level
];
$flag = Route::RunQuickly($options);
if (!$flag) {
    header(404, 'no');
    echo "404!";
}
