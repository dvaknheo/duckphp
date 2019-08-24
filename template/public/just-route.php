<?php
use DNMVCS\Core\Route;

require_once(__DIR__.'/../headfile/headfile.php');

class Main
{
    public function index()
    {
        var_dump("Just route test done");
        var_dump(DATE(DATE_ATOM));
    }
    public function i()
    {
        phpinfo();
    }
}
$options=[
    'namespace_controller'=>'\\',
];
$flag=Route::RunQuickly($options);
if (!$flag) {
    header(404);
    echo "404!";
}
