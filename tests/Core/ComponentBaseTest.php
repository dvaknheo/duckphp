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
}