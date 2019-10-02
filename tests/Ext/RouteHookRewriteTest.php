<?php
namespace tests\DNMVCS\Ext;

use DNMVCS\Ext\RouteHookRewrite;
use DNMVCS\Core\Route;
use DNMVCS\DNMVCS;

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
        DNMVCS::G(new DNMVCS())->init($route_options);
        
        $options=[
            'rewrite_map'=>[
                '~article/(\d+)/?(\d+)?'=>'article?id=$1&page=$2',
            ]
        ];
    
        RouteHookRewrite::G()->init($options,Route::G());
        RouteHookRewrite::G()->assignRewrite(['/k/v?a=b'=>'c/d?e=f',]);
        RouteHookRewrite::G()->assignRewrite('second','zz');
        RouteHookRewrite::G()->getRewrites();
        
        RouteHookRewrite::G()->filteRewrite('zdfafd');
        RouteHookRewrite::G()->filteRewrite('k/v');

        Route::G()->bind('/article/3/4')->run();
        Route::G()->bind('/k/v')->run();
        
if(false){

        RouteHookRewrite::G()->getRewrites();
        
        
          
        RouteHookRewrite::G()->replaceRegexUrl('inx','~ab','article?id=');
        RouteHookRewrite::G()->replaceRegexUrl('111','111?a1=23','article?id=11');
        RouteHookRewrite::G()->filteRewrite('zdfafd');
}

        \MyCodeCoverage::G()->end(RouteHookRewrite::class);
        $this->assertTrue(true);
        /*
        RouteHookRewrite::G()->init($options=[], $context=null);
        RouteHookRewrite::G()->assignRewrite($key, $value=null);
        RouteHookRewrite::G()->getRewrites();
        RouteHookRewrite::G()->replaceRegexUrl($input_url, $template_url, $new_url);
        RouteHookRewrite::G()->replaceNormalUrl($input_url, $template_url, $new_url);
        RouteHookRewrite::G()->filteRewrite($input_url);
        RouteHookRewrite::G()->changeRouteUrl($route, $url);
        RouteHookRewrite::G()->_Hook($route);
        RouteHookRewrite::G()->Hook($route);
        //*/
    }
}
class RouteHookRewriteTestMain{
    
    function index(){
        var_dump(DATE(DATE_ATOM));
    }

}