<?php 
namespace tests\DuckPhp\Core;
use DuckPhp\Core\App;
use DuckPhp\Core\ComponentBase;
use DuckPhp\Core\ComponentInterface;

class ComponentBaseTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(ComponentBase::class);
        $LibCoverage=\LibCoverage\LibCoverage::G();
        $path_data=\LibCoverage\LibCoverage::G()->getClassTestPath(ComponentBase::class);

        ComponentBaseObject::_()->init(['a'=>'b'],new \stdClass());
        ComponentBaseObject::_()->isInited();
        ComponentBaseObject::_()->testStaticMethods();

        ComponentBaseObject::_();
        ComponentBaseObject::_();
        ComponentBaseObject::_(new ComponentBaseObject());
        define('__SINGLETONEX_REPALACER',ComponentBaseObject::class.'::CreateObject');
        ComponentBaseObject::_();
        ComponentBaseObject2::_()->init([]);
        ComponentBaseObject2::_()->init([],App::_());
        ComponentBaseObject2::_()->reInit(['extx'=>true],App::_());
        

        $options=[
            'path'=> $path_data,
            'path_data'=> '',
        ];

    
        \LibCoverage\LibCoverage::G($LibCoverage);
        \LibCoverage\LibCoverage::End();
    }
}

class ComponentBaseObject extends ComponentBase  implements ComponentInterface
{
    public $context_class;
    public $options=[
        'path'=>'',
        'namespace'=>'zzz',
        'path_test'=>'test',
        'namespace_test'=>'zef',
    ];
    protected function initOptions(array $options): void
    {
        parent::initOptions($options);

    }
    public static function CreateObject($class, $object)
    {
        static $_instance;
        $_instance=$_instance??[];
        $_instance[$class]=$object?:($_instance[$class]??($_instance[$class]??new $class));
        return $_instance[$class];
    }
    public function testStaticMethods()
    {
        $this->context();
        ComponentBase::SlashDir('');
        ComponentBase::IsAbsPath('');
    }
}

class ComponentBaseObject2 extends ComponentBase  implements ComponentInterface
{
     protected $init_once = true;
}
class ComponentBaseObject3 extends ComponentBase  implements ComponentInterface
{
    public function context()
    {
        return null;
    }
}
