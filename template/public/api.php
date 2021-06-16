<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace {
    require_once(__DIR__.'/../../autoload.php');        // @DUCKPHP_HEADFILE
}
namespace Api {
// 后面是业务代码
// 这里自己加 api

    interface BaseApi
    {
    }
    class test implements BaseApi
    {
// 访问方式 http://duckphp.demo.dev/api.php/test.foo2?a=1&b=2
// 访问方式 http://duckphp.demo.dev/api.php/test.foo

        public function index()
        {
            $domain=\DuckPhp\DuckPhp::Domain(true);
            $url=$domain . \DuckPhp\DuckPhp::Url('test.foo');
            $url2=$domain .\DuckPhp\DuckPhp::Url('test.foo2?a=1&b=2');
            $message = <<<EOT
    不带参数访问： {$url}
    带参数访问：{$url2} 将会反射到 相应参数
    如果需要修改 uid ，则继承本扩展 RouteHookApiServer 覆盖 getObjectAndMethod() 和 getInputs()
EOT;
            
            $ret['message']=$message;
            $ret['date']=DATE(DATE_ATOM);
            return $ret;
        }
        public function foo()
        {
            return DATE(DATE_ATOM);
        }
        public function foo2($a, $b)
        {
            return [$a + $b, DATE(DATE_ATOM)];
        }
    }

}
namespace {
    $options = [
        'is_debug' => true,
        'skip_setting_file' => true,
        'namespace'=>'',
        'override_class' => '',
        'ext' => [
            'DuckPhp\\Ext\\RouteHookApiServer' => true,
        ],
        'api_server_namespace' => '\\Api',
        'api_server_interface' => '~BaseApi',
        'api_server_404_as_exception' => true,
    ];
    \DuckPhp\DuckPhp::RunQuickly($options);
}
