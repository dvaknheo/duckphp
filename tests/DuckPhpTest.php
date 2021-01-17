<?php
namespace tests\DuckPhp;

use DuckPhp\DuckPhp;
use DuckPhp\SingletonEx\SingletonExTrait;
use DuckPhp\Ext\Misc;

class DuckPhpTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        $extFile=dirname(\MyCodeCoverage::G()->classToPath(DuckPhp::class)).'/Core/Functions.php';
        \MyCodeCoverage::G()->prepareAttachFile($extFile);
        \MyCodeCoverage::G()->begin(DuckPhp::class);
        
        //code here
        //$handler=null;
        //DuckPhp::G()->addBeforeRunHandler($handler);
        
        //$SwooleHttpd=new fakeSwooleHttpd;
        //DuckPhp::G()->onSwooleHttpdInit($SwooleHttpd, false,function(){var_dump("OK");});
        //DuckPhp::G()->onSwooleHttpdInit($SwooleHttpd,true,null);

        $path_lib=\MyCodeCoverage::GetClassTestPath(DuckPhp::class).'lib/';
        $path_view=\MyCodeCoverage::GetClassTestPath(DuckPhp::class).'views/';

        $options=[
            'path_lib'=>$path_lib,
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
        \MyCodeCoverage::G()->end(DuckPhp::class);

    }
    protected function doFunctions()
    {
        \__h("test");
        \__l("test");
        \__hl("test");
        \__url("test");
        \__display("block",[]);
        \__trace_dump();
        \__var_dump("abc");
        \__debug_log("OK");

        try{
        \__db();
        }catch(\Exception $ex){
        }
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

