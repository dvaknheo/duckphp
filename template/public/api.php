<?php
require_once(__DIR__.'/../../autoload.php');        // @DUCKPHP_HEADFILE
use DuckPhp\DuckPhp;

class BaseApi
{
    //use \DuckPhp\SingletonEx\SingletonEx;
}

$options = [
    'is_debug'=>true,
    'skip_setting_file'=>true,
    'override_class'=>'',
    'ext'=>[
        'DuckPhp\\Ext\\RouteHookApiServer' => true,
    ],
    'api_class_base'=>'BaseApi', 
    'api_class_prefix'=>'Api_',
];

DuckPhp::RunQuickly($options);
////
/// 后面是业务代码
// 这里自己加 api 
class API_test extends BaseApi
{
    public function foo()
    {
        return DATE(DATE_ATOM);
    }
    public function foo2($a,$b)
    {
        return [$a+$b, DATE(DATE_ATOM)];
    }
}
////////////////
// 访问方式 http://duckphp.demo.dev/api.php/test.foo2?a=1&b=2
// 访问方式 http://duckphp.demo.dev/api.php/test.foo

//