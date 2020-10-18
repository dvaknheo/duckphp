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
        //'api_server_on_missing' => function(){ return OnIndex();},
    ];
    \DuckPhp\DuckPhp::RunQuickly($options);
    function On404()
    {
        $domain=\DuckPhp\DuckPhp::Domain();
        $url=$domain . \DuckPhp\DuckPhp::Url('test.foo');
        $url2=$domain .\DuckPhp\DuckPhp::Url('test.foo2?a=1&b=2');
echo  <<<EOT
    访问方式 <a href="{$url}">{$url}</a><br />
    访问方式 <a href="{$url2}">{$url2}</a><br />
EOT;

    }
    function OnIndex()
    {
        $domain=\DuckPhp\DuckPhp::Domain();
        $url=$domain . \DuckPhp\DuckPhp::Url('test.foo');
        $url2=$domain .\DuckPhp\DuckPhp::Url('test.foo2?a=1&b=2');
echo  <<<EOT
!!!
    访问方式 <a href="{$url}">{$url}</a><br />
    访问方式 <a href="{$url2}">{$url2}</a><br />
EOT;
        return true;
    }
}
