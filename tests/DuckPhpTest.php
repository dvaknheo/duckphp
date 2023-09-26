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
        $extpath = $ref->getFileName();
        $extFile=dirname($extpath).'/Core/Functions.php';
        \LibCoverage\LibCoverage::G()->addExtFile($extFile);
        
        \LibCoverage\LibCoverage::Begin(DuckPhp::class);
        $LibCoverage = \LibCoverage\LibCoverage::G();
        $path = \LibCoverage\LibCoverage::G()->getClassTestPath(DuckPhp::class);
        
       
        
        //code here
        //$handler=null;
        //DuckPhp::G()->addBeforeRunHandler($handler);
        
        //$SwooleHttpd=new fakeSwooleHttpd;
        //DuckPhp::G()->onSwooleHttpdInit($SwooleHttpd, false,function(){var_dump("OK");});
        //DuckPhp::G()->onSwooleHttpdInit($SwooleHttpd,true,null);

        $path_view= $path.'views/';

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
        
        //DuckPhp::G()->xx
        DuckPhp::G()->setBeforeGetDbHandler(null);
        DuckPhp::G()->getRoutes();
        DuckPhp::G()->assignRoute('ab/c',['z']);
        
        DuckPhp::G()->assignImportantRoute('ab/c',['z']);
        
        DuckPhp::G()->Admin(FakeAdmin::G());
        DuckPhp::G()->User(FakeUser::G());
        DuckPhp::G()->AdminId();
        DuckPhp::G()->UserId();
        
        $options['path'] = $path;
        $options['path_test'] = 'abc';
        $options['ext_options_from_config']=true;
        
        @unlink($path.'config/'.'DuckPhpOptions.php');
        DuckPhp_Sub::G(new DuckPhp_Sub())->init($options);
        DuckPhp_Sub::G()->install(['test'=>DATE(DATE_ATOM)]);
        DuckPhp_Sub::G()->isInstalled();
        
        $options['ext'][DuckPhp_Sub::class]=['test'=>DATE(DATE_ATOM)];
        DuckPhp::G(new DuckPhp())->init($options);
        echo "\n". DuckPhp::AdminId();
        echo "\n". DuckPhp::G()->getPath();
        echo "\n". DuckPhp::G()->getPath('test');
        echo "\n". DuckPhp::G()->getPath('config');
        echo "\n". DuckPhp::G()->getPath('zz');
       
        //DuckPhp::G()->isInstalled();
        @unlink($path.'config/'.'DuckPhpOptions.php');
        
        /////////////
        
        ///////////
        
        
        
        \LibCoverage\LibCoverage::G($LibCoverage);
        \LibCoverage\LibCoverage::End(DuckPhp::class);

    }
    protected function doFunctions()
    {
        \__h("test");
        \__l("test");
        \__hl("test");
        \__url("test");
        \__res("test");
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
class DuckPhp_Sub extends DuckPhp
{
    public function onInit()
    {
        $this->bumpSingletonToRoot(FakeAdmin::class,\DuckPhp\Component\AdminObject::class);
        $this->bumpSingletonToRoot(FakeUser::class,\DuckPhp\Component\UserObject::class);
    }
    public function install($options)
    {
        return $this->installWithExtOptions($options);
    }
    
}
class fakeSwooleHttpd
{
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
class FakeAdmin
{
    use SingletonExTrait;
    public function id()
    {
        return 1;
    }
}
class FakeUser
{
    use SingletonExTrait;
    public function id()
    {
        return 1;
    }
}
