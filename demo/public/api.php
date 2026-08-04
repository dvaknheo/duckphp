<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace {
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
}
////////////////////////////////////////

namespace Api {
// 后面是业务代码
// 这里自己加 api

    interface BaseApi
    {
    }
    class test implements BaseApi
    {
        // 访问方式 http://duckphp.demo.local/api.php/test.foo2?a=1&b=2
        // 访问方式 http://duckphp.demo.local/api.php/test.foo

        public function index()
        {
            $domain = \DuckPhp\DuckPhpAllInOne::Domain(true);
            $url = $domain . __url('test.foo');
            $url2 = $domain .__url('test.foo2?a=1&b=2');
            $message = <<<EOT
    不带参数访问： {$url}
    带参数访问：{$url2} 将会反射到 相应参数
    如果需要修改 uid ，则继承本扩展 RouteHookApiServer 覆盖 getObjectAndMethod() 和 getInputs()
EOT;
            
            $ret['message'] = $message;
            $ret['date'] = DATE(DATE_ATOM);
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
        'namespace' => '',
        'setting_file_enable' => false,
        'ext' => [
            'DuckPhp\\Ext\\RouteHookApiServer' => [
                'api_server_namespace' => '\\Api',
                'api_server_interface' => '~BaseApi',
                'api_server_404_as_exception' => true,
            ],
        ],
        'is_debug' => true,
    ];
    \DuckPhp\DuckPhp::RunQuickly($options);
}
