<?php
namespace tests\DuckPhp;

use DuckPhp\App as DuckPhp;
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

        $this->doGlue();
        $path_lib=\GetClassTestPath(DuckPhp::class).'lib/';
        $path_view=\GetClassTestPath(DuckPhp::class).'views/';

        $options=[
            'skip_setting_file'=>true,
            'path_lib'=>$path_lib,
            'log_sql'=>true,
            'use_short_functions'=>true,
            'path_view'=>$path_view,
        ];
        DuckPhp::G()->init($options);
        DuckPhp::G()->system_wrapper_replace([
            'exit' =>function(){ echo "change!\n";},
        ]);
        DuckPhp::Pager();
        
        ////
        
        DuckPhp::OnQuery("SQL ",1,2);
        ////
        \__h("test");
        \__l("test");
        \__hl("test");
        \__url("test");
        \__display("block",[]);
        
        \MyCodeCoverage::G()->end(DuckPhp::class);
        $this->assertTrue(true);

    }
    public function doGlue()
    {
        //DuckPhp::DB($tag=null);
        //DuckPhp::DB_W();
        //DuckPhp::DB_R();
        
        //DuckPhp::setDBHandler($db_create_handler=null, $db_close_handler=null, $db_excption_handler=null);
        
        
        //DuckPhp::assignRewrite($key="abc", $value=null);
        /*
        DuckPhp::getRewrites();
        DuckPhp::assignRoute($key="zzz", $value=null);
        DuckPhp::getRoutes();
        */
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

