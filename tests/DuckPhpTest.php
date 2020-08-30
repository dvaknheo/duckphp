<?php
namespace tests\DuckPhp;

use DuckPhp\DuckPhp;
use DuckPhp\Core\SingletonEx;
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
            'skip_setting_file'=>true,
            'path_lib'=>$path_lib,
            'log_sql_query'=>true,
            'use_short_functions'=>true,
            'mode_no_path_info'=>true,
            'path_view'=>$path_view,
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
                $db=new \stdClass();
        DuckPhp::OnQuery($db,"SQL ",1,2);
        DuckPhp::G()->options['log_sql_query']=false;
        $db=new \stdClass();
        DuckPhp::OnQuery($db,"SQL ",1,2);
        ////
        \__h("test");
        \__l("test");
        \__hl("test");
        \__url("test");
        \__display("block",[]);
        
        DuckPhp::Event();
        DuckPhp::OnEvent('MyEvent',function(...$args){ var_dump($args);});
        DuckPhp::FireEvent('MyEvent','A','B','C');
        DuckPhp::FireEvent('NoExist','A','B','C');
        
        \MyCodeCoverage::G()->end(DuckPhp::class);

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

