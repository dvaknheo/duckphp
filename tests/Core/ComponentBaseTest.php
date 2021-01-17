<?php 
namespace tests\DuckPhp\Ext;
use DuckPhp\Core\ComponentBase;
use DuckPhp\Core\ComponentInterface;

class ComponentBaseTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(ComponentBase::class);

        ComponentBaseObject::G()->init(['a'=>'b'],new \stdClass());
        ComponentBaseObject::G()->isInited();


        ComponentBaseObject::G();
        ComponentBaseObject::G(new ComponentBaseObject());
        $t=\LibCoverage\LibCoverage::G();
        define('__SINGLETONEX_REPALACER',ComponentBaseObject::class.'::CreateObject');
        \LibCoverage\LibCoverage::G($t);
        ComponentBaseObject::G();
        
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

        $this->path = parent::getComponenetPathByKey('path_test');
        $this->options['path_test']='/tmp';
        $this->path = parent::getComponenetPathByKey('path_test');
        
        /*
        $this->namespace = parent::getComponenetNameSpace('namespace_test');
        $this->options['namespace_test']='\\mynamespace';
        $this->namespace = parent::getComponenetNameSpace('namespace_test');
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