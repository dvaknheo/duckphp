<?php
namespace tests\DuckPhp\Foundation;

use DuckPhp\Foundation\SimpleControllerTrait;
use DuckPhp\Core\App;

class SimpleControllerTraitTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(SimpleControllerTrait::class);
        
        $options=[
            'is_debug' => true,
            'namespace_controller'=>'\tests\DuckPhp\Foundation',
        ];
        App::G()->init($options);
            App::G()->system_wrapper_replace(['exit'=>function($code=0){
            echo 'Exit';
            return ;
        }]);
        App::Route()::PathInfo('/MyController/helper');
        App::G()->run();

        App::Route()::PathInfo('/MyController/foo');
        App::G()->run();
        ProjectControllerBase::G(MyBase::G());
        App::Route()::PathInfo('/MyController/foo');
        App::G()->run();
        \LibCoverage\LibCoverage::End();
    }
}
class ProjectController
{
    use SimpleControllerTrait;
    
    public function helper()
    {
        //
    }
    public static function H()
    {
        return static::G()->helper();
    }
}
class ProjectControllerBase extends ProjectController
{
}
class MyBase extends ProjectControllerBase
{
}
class MyController extends ProjectControllerBase
{
    public function foo()
    {
        ProjectControllerBase::H();
    }
    public function __construct()
    {
        parent::__construct();
    }

}
