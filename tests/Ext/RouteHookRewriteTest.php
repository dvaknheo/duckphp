<?php
namespace tests\DuckPhp\Ext;

use DuckPhp\Ext\RouteHookRewrite;
use DuckPhp\Core\Route;
use DuckPhp\App as DuckPhp;

class RouteHookRewriteTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(RouteHookRewrite::class);
        $route_options=[
            'is_debug'=>true,
            'skip_setting_file'=>true,
            'namespace'=>__NAMESPACE__,
            'namespace_controller'=>'\\'.__NAMESPACE__,
            'controller_welcome_class'=> 'RouteHookRewriteTestMain',

        ];
        DuckPhp::G(new DuckPhp())->init($route_options);
        
        $options=[
            'rewrite_map'=>[
                '~article/(\d+)/?(\d+)?'=>'article?id=$1&page=$2',
            ]
        ];
    
        RouteHookRewrite::G()->init($options,Route::G());
        RouteHookRewrite::G()->assignRewrite(['/k/v'=>'c/d?e=f',]);
        RouteHookRewrite::G()->assignRewrite('second','zz');
        RouteHookRewrite::G()->assignRewrite('/k/v?a=b','zz');
        RouteHookRewrite::G()->getRewrites();
        
        RouteHookRewrite::G()->filteRewrite('zdfafd');
        RouteHookRewrite::G()->filteRewrite('k/v');

        Route::G()->bind('/article/3/4')->run();
        Route::G()->bind('/k/v')->run();
        echo "-----------xxxxxxxxxxxxxxxxxxxxx-----\n";
        RouteHookRewrite::G()->filteRewrite('k/v?a=b&g=h');


        \MyCodeCoverage::G()->end(RouteHookRewrite::class);
        $this->assertTrue(true);
    }
}
class RouteHookRewriteTestMain{
    
    function index(){
        var_dump(DATE(DATE_ATOM));
    }

}