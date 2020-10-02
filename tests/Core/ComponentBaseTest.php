<?php 
namespace tests\DuckPhp\Ext;
use DuckPhp\Core\ComponentBase;
use DuckPhp\Core\ComponentInterface;

class ComponentBaseTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(ComponentBase::class);

        ComponentBaseObject::G()->init(['a'=>'b'],new \stdClass());
        ComponentBaseObject::G()->isInited();


        ComponentBaseObject::G();
        ComponentBaseObject::G(new ComponentBaseObject());
        $t=\MyCodeCoverage::G();
        define('__SINGLETONEX_REPALACER',ComponentBaseObject::class.'::CreateObject');
        \MyCodeCoverage::G($t);
        ComponentBaseObject::G();
        
        \MyCodeCoverage::G()->end();
    }
}

class ComponentBaseObject extends ComponentBase  implements ComponentInterface
{
    public $options=[
        'path'=>'',
        'path_test'=>'test',
    ];
    protected function initOptions(array $options)
    {
        parent::initOptions($options);

        $this->path = parent::getComponenetPathByKey('path_test');
        $this->options['path_test']='/tmp';
        $this->path = parent::getComponenetPathByKey('path_test');
        
    }
    public static function CreateObject($class, $object)
    {
        static $_instance;
        $_instance=$_instance??[];
        $_instance[$class]=$object?:($_instance[$class]??($_instance[$class]??new static));
        return $_instance[$class];
    }
}