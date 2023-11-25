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


        ComponentBaseObject::_();
        ComponentBaseObject::_();
        ComponentBaseObject::_(new ComponentBaseObject());
        define('__SINGLETONEX_REPALACER',ComponentBaseObject::class.'::CreateObject');
        ComponentBaseObject::_();
        ComponentBaseObject2::_()->init([]);
        ComponentBaseObject2::_()->init([],App::_());
        ComponentBaseObject2::_()->init(['force_new_init'=>true],App::_());
        
        ComponentBaseObject2::_()->context();
        //var_dump(ComponentBase::SlashDir(''));
        //var_dump(ComponentBase::IsAbsPath(''));
        $options=[
            'path'=> $path_data,
            'path_data'=> '',
        ];
        ComponentBaseObject::_()->extendFullFile($options['path'],$options['path_data'],$options['path'].'data.php');

        ComponentBaseObject::_()->extendFullFile($options['path'],$options['path_data'], 'data.php');
        ComponentBaseObject::_()->extendFullFile($options['path'],$options['path'], 'data.php');
        ComponentBaseObject::_()->init($options, App::_());
        ComponentBaseObject::_()->extendFullFile($options['path'],$options['path_data'],$options['path'].'data.php');
        
        ComponentBaseObject3::_()->extendFullFile($options['path'],$options['path_data'],$options['path'].'data.php');
        ComponentBaseObject3::_()->extendFullFile($options['path'],'/sub','data.php');
        
        echo ComponentBaseObject3::_()->extendFullFile($options['path'],'sub','data.php');

    
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
    protected function initOptions(array $options)
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
