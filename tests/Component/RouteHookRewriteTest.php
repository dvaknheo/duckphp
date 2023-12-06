<?php
namespace tests\DuckPhp\Component;

use DuckPhp\Component\RouteHookRewrite;
use DuckPhp\Core\SuperGlobal;
use DuckPhp\Core\Route;
use DuckPhp\DuckPhp;

class RouteHookRewriteTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(RouteHookRewrite::class);
        $route_options=[
            'is_debug'=>true,
            'namespace'=>__NAMESPACE__,
            'namespace_controller'=>'\\'.__NAMESPACE__,
            'controller_welcome_class'=> 'RouteHookRewriteTestMain',

        ];
        DuckPhp::_(new DuckPhp())->init($route_options);
        
        $options=[
            'rewrite_map'=>[
                '~article/(\d+)/?(\d+)?'=>'article?id=$1&page=$2',
            ]
        ];
    
        RouteHookRewrite::_()->init($options,DuckPhp::_());
        RouteHookRewrite::_()->assignRewrite(['/k/v'=>'c/d?e=f',]);
        RouteHookRewrite::_()->assignRewrite('second','zz');
        RouteHookRewrite::_()->assignRewrite('/k/v?a=b','zz');
        RouteHookRewrite::_()->getRewrites();
        
        RouteHookRewrite::_()->filteRewrite('zdfafd');
        RouteHookRewrite::_()->filteRewrite('k/v');

        Route::_()->bind('/article/3/4')->run();
        Route::_()->bind('/k/v')->run();
        echo "-----------xxxxxxxxxxxxxxxxxxxxx-----\n";
        RouteHookRewrite::_()->filteRewrite('k/v?a=b&g=h');

        RouteHookRewrite::_()->isInited();
        
        \DuckPhp\Core\SuperGlobal::DefineSuperGlobalContext();
        
        Route::_()->bind('/article/3/4')->run();
        RouteHookRewrite::_()->options['controller_url_prefix']='noexist';
        Route::_()->bind('/article/3/4')->run();


        \LibCoverage\LibCoverage::End();
    }
}
class RouteHookRewriteTestMain{
    
    function index(){
        var_dump(DATE(DATE_ATOM));
    }

}