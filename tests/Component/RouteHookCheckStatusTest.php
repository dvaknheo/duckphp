<?php
namespace tests\DuckPhp\Component;

use DuckPhp\Component\RouteHookCheckStatus;
use DuckPhp\DuckPhp;

class RouteHookCheckStatusTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(RouteHookCheckStatus::class);
        $path = \LibCoverage\LibCoverage::G()->getClassTestPath(RouteHookCheckStatus::class);
        $options=[
            'cli_enable'=>false,
            'is_debug'=>true,
            'namespace'=>__NAMESPACE__,
            'namespace_controller'=>'\\'.__NAMESPACE__,
            'controller_welcome_class'=> 'RouteHookCheckStatusTestMain',
            'path_view' => $path,

        ];

        //Route::_(new Route())->init($route_options);
        DuckPhp_RHCS::_()->init($options);
        
        $options = [
            'error_maintain' => null,
            'error_need_install' => null,
        ];
        //RouteHookCheckStatus::_(new RouteHookCheckStatus())->init($options, App::_());
        DuckPhp_RHCS::_()->run();
        //RouteHookCheckStatus::_()->options['error_maintain']=true;
        DuckPhp_RHCS::_()->options['is_maintain']=true;
        DuckPhp_RHCS::_()->run();
        RouteHookCheckStatus::_()->options['error_maintain'] = 'view_maintain';
        DuckPhp_RHCS::_()->options['error_maintain'] = 'view_maintain';
        DuckPhp_RHCS::_()->run();
        
        RouteHookCheckStatus::_()->options['error_maintain'] = function(){echo 'hit!';};
        DuckPhp_RHCS::_()->run();

        ///////////////

        DuckPhp_RHCS::_()->options['is_maintain']=false;
        echo "111111111111111111111111111111111111111111111111";
        DuckPhp_RHCS::_()->options['need_install']=true;
        DuckPhp_RHCS::_()->options['install']=false;
        RouteHookCheckStatus::_()->options['error_need_install']='view_need_install';
        DuckPhp_RHCS::_()->run();
        echo "2222222222222222222222222222222222222222222222";
        RouteHookCheckStatus::_()->options['error_need_install']=null;
        DuckPhp_RHCS::_()->run();
        RouteHookCheckStatus::_()->options['error_need_install']=function(){echo 'hit!';};
        DuckPhp_RHCS::_()->run();

        
        \LibCoverage\LibCoverage::End();

    }
}
class RouteHookCheckStatusTestMainController
{    
    function action_index(){
        var_dump(DATE(DATE_ATOM));
    }
}
class DuckPhp_RHCS extends DuckPhp
{
    public $options = [
        'ext' => [
            RouteHookCheckStatus::class => true,
        ],    
    ];
    protected function prepareServe()
    {
        return;
    }
}