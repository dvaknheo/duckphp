<?php
namespace tests\DNMVCS\Ext;

use DNMVCS\Ext\RouteHookRewrite;

class RouteHookRewriteTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(RouteHookRewrite::class);
        
        $options=[
			'~article/(\d+)/?(\d+)?'=>'article?id=$1&page=$2',
        ];
        RouteHookRewrite::G()->init($options,null);
        RouteHookRewrite::G()->assignRewrite(['abc'=>'d']);
        RouteHookRewrite::G()->assignRewrite('efg','zz');
        RouteHookRewrite::G()->getRewrites();
        RouteHookRewrite::G()->replaceRegexUrl('inx','~ab','article?id=');
        RouteHookRewrite::G()->replaceRegexUrl('111','111?a1=23','article?id=11');
        RouteHookRewrite::G()->filteRewrite('zdfafd');
        
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
