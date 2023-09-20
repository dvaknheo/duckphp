<?php 
namespace tests\DuckPhp\Core;
use DuckPhp\Core\ComponentBase;
use DuckPhp\Core\ComponentInterface;

class ComponentBaseTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(ComponentBase::class);
        $LibCoverage=\LibCoverage\LibCoverage::G();
        $path_data=\LibCoverage\LibCoverage::G()->getClassTestPath(ComponentBase::class);

        ComponentBaseObject::G()->init(['a'=>'b'],new \stdClass());
        ComponentBaseObject::G()->isInited();


        ComponentBaseObject::G();
        ComponentBaseObject::G(new ComponentBaseObject());
        define('__SINGLETONEX_REPALACER',ComponentBaseObject::class.'::CreateObject');
        ComponentBaseObject::G();
        ComponentBaseObject2::G()->init([]);
        ComponentBaseObject2::G()->init([]);
        
        
        var_dump(ComponentBase::SlashDir(''));
        var_dump(ComponentBase::IsAbsPath(''));
        $options=[
            'path'=> $path_data,
            'path_data'=> '',
            'path_data_override_from'=> $path_data.'overrided',
        ];
        ComponentBase::GetFileFromSubComponent($options, 'data', $path_data.'data.php');
        ComponentBase::GetFileFromSubComponent($options, 'data', 'data.php');
        ComponentBase::GetFileFromSubComponent($options, 'data', 'data2.php');
        ComponentBase::GetFileFromSubComponent($options, 'data', 'datanotexist.php');
        $options=[
            'path'=> $path_data,
            'path_data'=> '',
            'path_data_override_from'=> null,
        ];
        ComponentBase::GetFileFromSubComponent($options, 'data', 'data2.php');
        
        \LibCoverage\LibCoverage::G($LibCoverage);
        \LibCoverage\LibCoverage::End();
    }
}

class ComponentBaseObject extends ComponentBase  implements ComponentInterface
{
    public $options=[
        'path'=>'',
        'namespace'=>'zzz',
        'path_test'=>'test',
        'namespace_test'=>'zef',
    ];
    protected function initOptions(array $options)
    {
        parent::initOptions($options);

        //$this->path = parent::getComponentPathByKey('path_test');
        //$this->options['path_test']='/tmp';
        //$this->path = parent::getComponentPathByKey('path_test');
        
        /*
        $this->namespace = parent::getComponentNameSpace('namespace_test');
        $this->options['namespace_test']='\\mynamespace';
        $this->namespace = parent::getComponentNameSpace('namespace_test');
        */
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
