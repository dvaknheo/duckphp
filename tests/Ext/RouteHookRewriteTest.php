<?php
namespace tests\DNMVCS\Ext;

use DNMVCS\Ext\RouteHookRewrite;

class RouteHookRewriteTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(RouteHookRewrite::class);
        
        //code here
        
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
