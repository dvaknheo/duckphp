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
class MainController
{
    public function index()
    {
        echo "hello world";
    }
}
$options = [
    'is_debug'=>true,
    'namespace_controller' => "\\",   // this example is special: controllers live in the root namespace, not the default Controller
    // a hundred or so more options are available; see the reference manual
];
try{
\DuckPhp\DuckPhp::RunQuickly($options);
}catch(\Throwable $e){

}