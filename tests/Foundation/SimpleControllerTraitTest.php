<?php 
namespace tests\DuckPhp\Foundation;
use DuckPhp\Foundation\SimpleControllerTrait;
use DuckPhp\DuckPhpAllInOne;
use DuckPhp\Core\Route;

class SimpleControllerTraitTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        $LibCoverage = \LibCoverage\LibCoverage::G();
        \LibCoverage\LibCoverage::Begin(SimpleControllerTrait::class);
        //ControllerFakeSingletonTraitObject::_(ControllerFakeSingletonTraitObject2::_());
        $options = [
            'namespace_controller' => __NAMESPACE__,
            'controller_class_postfix' => 'Controller',
            'controller_method_prefix' => '',
            'controller_class_base' => '',
        ];
        Route::_()->init($options);
        Route::_()->bind('/hello/world');
        Route::_()->run();
        helloController::_(helloController::_());
        helloController::_(MyController::_());
        Route::_()->run();
        echo "-------------\n";
        $options = [
            'namespace_controller' => __NAMESPACE__,
            'controller_class_postfix' => 'Controller',
            'controller_method_prefix' => '',
            'controller_class_base' => BaseX::class,
        ];
        Route::_(new Route())->init($options);
        Route::_()->bind('/hello2/world');
        hello2Controller::_(My2Controller::_());
        Route::_()->run();
        
        echo "-------------\n";
        $options = [
            'namespace_controller' => __NAMESPACE__,
            'controller_class_postfix' => '',
            'controller_method_prefix' => '',
            'controller_class_base' => BaseX::class,
        ];
        Route::_(new Route())->init($options);
        Route::_()->bind('/hello2Controller/world');
        hello2Controller::_(My2Controller::_());
        Route::_()->run();
        
        echo "-------------\n";
        $options = [
            'namespace_controller' => __NAMESPACE__,
            'controller_class_postfix' => '',
            'controller_method_prefix' => '',
            'controller_class_base' => '',
        ];
        Route::_(new Route())->init($options);
        Route::_()->bind('/hello2Controller/world');
        hello2Controller::_(My2Controller::_());
        Route::_()->run();
        
        echo "-6666------------\n";
        
        
        
        
        
        
        $options = [
            'namespace_controller' => __NAMESPACE__,
            'controller_class_postfix' => 'Controller',
            'controller_method_prefix' => 'action',
            'controller_class_base' => '',
        ];
        Route::_()->init($options);
        Route::_()->bind('/hello/world');
        helloController::_(helloController::_());
        helloController::_(MyController::_());
        Route::_()->run();
        
        MyAction::_(MyAction2::_())->foo();
        MyAction::_Z(DuckPhpAllInOne::class)->foo();
        
        My2Controller::OverrideParent();

        \LibCoverage\LibCoverage::G($LibCoverage);
        \LibCoverage\LibCoverage::End();
    }
}
class helloController
{
    use SimpleControllerTrait;

    public function __construct()
    {
        var_dump('new:' . static::class);
    }
    public function world()
    {
        var_dump('static::class: '.static::class);
    }
}

class MyController extends helloController
{
}
class BaseX
{
    use SimpleControllerTrait;
}
class hello2Controller extends BaseX
{
    
}
class My2Controller extends hello2Controller
{
}
class My3Controller
{
    use SimpleControllerTrait;

}
////////////////
class MyAction extends BaseX
{
 public function __construct()
    {
        var_dump('MyAction:' . static::class);
    }
    public function foo()
    {
        var_dump('foo');
    }
}
class MyAction2 extends MyAction
{
    public function foo()
    {
        var_dump('MyAction2MyAction2MyAction2');
    }
}
