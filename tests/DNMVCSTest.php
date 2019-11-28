<?php
namespace tests\DNMVCS;

use DNMVCS\DNMVCS;
use DNMVCS\Core\SingletonEx;
use DNMVCS\Ext\Misc;

class DNMVCSTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(DNMVCS::class);
        
        //code here
        //$handler=null;
        //DNMVCS::G()->addBeforeRunHandler($handler);
        DNMVCS::G()->getStaticComponentClasses();
        DNMVCS::G()->getDynamicComponentClasses();
        
        $SwooleHttpd=new fakeSwooleHttpd;
        DNMVCS::G()->onSwooleHttpdInit($SwooleHttpd, false,function(){var_dump("OK");});
        DNMVCS::G()->onSwooleHttpdInit($SwooleHttpd,true,null);

        $this->doGlue();
        $path_lib=\GetClassTestPath(DNMVCS::class).'lib/';

        $options=[
            'skip_setting_file'=>true,
            'path_lib'=>$path_lib,
        ];
        DNMVCS::G()->init($options);
        DNMVCS::G()->system_wrapper_replace([
            'exit_system' =>function(){ echo "change!\n";},

        ]);
        
        DNMVCS::MapToService(FakeService::class, []);
        DNMVCS::Import('file');
        $data=[['A'=>'b']];
        DNMVCS::RecordsetUrl($data, $cols_map=[]);
        DNMVCS::RecordsetH($data, $cols=[]);
        
        DNMVCS::DB();
        DNMVCS::DB_W();
        DNMVCS::DB_R();
        
        $object=new \stdClass();
        DNMVCS::DI('a',$object);
        
        \MyCodeCoverage::G()->end(DNMVCS::class);
        $this->assertTrue(true);

    }
    public function doGlue()
    {
        //DNMVCS::DB($tag=null);
        //DNMVCS::DB_W();
        //DNMVCS::DB_R();
        
        DNMVCS::Logger()->info("OK");
        DNMVCS::setDBHandler($db_create_handler=null, $db_close_handler=null, $db_excption_handler=null);
        DNMVCS::Pager();
        DNMVCS::assignRewrite($key="abc", $value=null);
        DNMVCS::getRewrites();
        DNMVCS::assignRoute($key="zzz", $value=null);
        DNMVCS::getRoutes();
        DNMVCS::CheckStrictDB();
        DNMVCS::checkStrictComponent($component_name="z", $trace_level=2);
        DNMVCS::checkStrictService($trace_level=2);
        DNMVCS::checkStrictModel($trace_level=2);

        //DNMVCS::callAPI($class, $method, $input);
        DNMVCS::explodeService(FakeObject::G(), $namespace=__NAMESPACE__);
    }
}
class fakeSwooleHttpd
{
    public static function SG()
    {
        return null;
    }
    public static function system_wrapper_get_providers()
    {
        return [];
    }
    public function is_with_http_handler_root()
    {
        return true; // return false;
    }
    public function set_http_exception_handler(callable $callback)
    {
        return;
    }
    public function set_http_404_handler(callable $callback)
    {
        return;
    }
}
class FakeService
{
use SingletonEx;
}
class FakeObject 
{
    use SingletonEx;
    
}

