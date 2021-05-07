<?php
namespace tests\DuckPhp;

use DuckPhp\DuckPhp;
use DuckPhp\SingletonEx\SingletonExTrait;
use DuckPhp\Ext\Misc;

class DuckPhpTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        $ref = new \ReflectionClass(DuckPhp::class);
        $path = $ref->getFileName();
        
        $extFile=dirname($path).'/Core/Functions.php';
        \LibCoverage\LibCoverage::G()->addExtFile($extFile);
        \LibCoverage\LibCoverage::Begin(DuckPhp::class);
        
        //code here
        //$handler=null;
        //DuckPhp::G()->addBeforeRunHandler($handler);
        
        //$SwooleHttpd=new fakeSwooleHttpd;
        //DuckPhp::G()->onSwooleHttpdInit($SwooleHttpd, false,function(){var_dump("OK");});
        //DuckPhp::G()->onSwooleHttpdInit($SwooleHttpd,true,null);

        $path_view=\LibCoverage\LibCoverage::G()->getClassTestPath(DuckPhp::class).'views/';

        $options=[
            'log_sql_query'=>true,
            'use_short_functions'=>true,
            'mode_no_path_info'=>true,
            'path_view'=>$path_view,
            'path_info_compact_enable'=>true,
        ];
        DuckPhp::G()->init($options);
        DuckPhp::G()->system_wrapper_replace([
            'exit' =>function(){ echo "change!\n";},
        ]);
        DuckPhp::Pager();
        
        try{
        DuckPhp::Db();
        }catch(\Exception $ex){}
        try{
        DuckPhp::DbForRead();
        }catch(\Exception $ex){}
        try{
        DuckPhp::DbForWrite();
        }catch(\Exception $ex){}
        ////
        DuckPhp::DbCloseAll();

        ////
        $this->doFunctions();
        
        DuckPhp::Event();
        DuckPhp::OnEvent('MyEvent',function(...$args){ var_dump($args);});
        DuckPhp::FireEvent('MyEvent','A','B','C');
        DuckPhp::FireEvent('NoExist','A','B','C');
        
        
        DuckPhp::Show([],'block');
        DuckPhp::G()->options['close_resource_at_output']=false;
        DuckPhp::Show([],'block');

        $t=new \stdClass();
        DuckPhp::Cache($t);
        \LibCoverage\LibCoverage::End(DuckPhp::class);

    }
    protected function doFunctions()
    {
        \__h("test");
        \__l("test");
        \__hl("test");
        \__url("test");
        \__json("test");
        \__domain();
        \__display("block",[]);
        \__trace_dump();
        \__var_dump("abc");
        \__debug_log("OK");
        
        \__is_debug();
        \__is_real_debug();
        \__platform();
        \__logger();


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
    use SingletonExTrait;
}
class FakeObject 
{
    use SingletonExTrait;
    
}

