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
// the business code goes below
// add your own api here

    interface BaseApi
    {
    }
    class test implements BaseApi
    {
        // how to call it: http://duckphp.demo.local/api.php/test.foo2?a=1&b=2
        // how to call it: http://duckphp.demo.local/api.php/test.foo

        public function index()
        {
            $domain = \DuckPhp\DuckPhpAllInOne::Domain(true);
            $url = $domain . __url('test.foo');
            $url2 = $domain .__url('test.foo2?a=1&b=2');
            $message = __l('api.usage', ['url' => $url, 'url2' => $url2]);
            
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
        // find `demo/config/lang-*.php` (this entry point is its own app)
        'path' => __DIR__ . '/../',
        'lang_default' => 'en',
        'ext' => [
            'DuckPhp\\Ext\\RouteHookApiServer' => [
                'apiserver_namespace' => '\\Api',
                'apiserver_base_class' => '~BaseApi',
                'apiserver_404_as_exception' => true,
            ],
        ],
        'is_debug' => true,
    ];
    \DuckPhp\DuckPhp::RunQuickly($options);
}
