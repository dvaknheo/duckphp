<?php
namespace tests\DuckPhp;

use DuckPhp\DuckPhp;
use DuckPhp\SingletonEx\SingletonExTrait;
use DuckPhp\Ext\Misc;
use DuckPhp\Component\Configer;
use DuckPhp\Core\View;

class DuckPhpTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
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
            'mode_no_path_info'=>true,
            'path_view'=>$path_view,
            'path_info_compact_enable'=>true,
        ];
        DuckPhp::G()->init($options);
        DuckPhp::G()->system_wrapper_replace([
            'exit' =>function(){ echo "change!\n";},
        ]);
        DuckPhp::Pager();
        
        ////
        ////
        
        DuckPhp::Event();

        
        
        View::_()->_Show([],'block');
        DuckPhp::G()->options['close_resource_at_output']=false;
        View::_()->_Show([],'block');

        
        
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
        $options['ext_options_file_enable']=true;
        
        @unlink($path.'config/DuckPhpApps.config.php');
        DuckPhp_Sub::G(new DuckPhp_Sub())->init($options);
        echo "aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa";
        DuckPhp_Sub::G()->install(['test'=>DATE(DATE_ATOM)]);
        DuckPhp_Sub::G()->options['ext_options_file_enable'] = false;
        DuckPhp_Sub::G()->install(['test'=>DATE(DATE_ATOM)]);
        //die("zzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzz");
        DuckPhp_Sub::G()->isInstalled();
        
        $options['ext'][DuckPhp_Sub::class]=['test'=>DATE(DATE_ATOM)];
        DuckPhp::G(new DuckPhp())->init($options);
        echo "\n". DuckPhp::AdminId();
       
        //DuckPhp::G()->isInstalled();
        @unlink($path.'config/DuckPhpApps.config.php');
        
        /////////////
        
        $options=[
            'is_debug' => true,
            'cli_enable'=>false,
            'path' =>$path,
            
            'cli_mode' => 'hook',
            'ext'=>[
                DuckPhp_Sub::class =>[
                    'cli_enable'=>false,
                    'cli_mode' => 'hook',
                    'controller_url_prefix'=>'advance/',
                    'exception_reporter' => FakeReporter::class,
                ],
            ],
        ];
        DuckPhp::G(new DuckPhp());
        DuckPhp_Sub::G(new DuckPhp_Sub());
        \DuckPhp\Core\PhaseContainer::GetContainerInstanceEx(new \DuckPhp\Core\PhaseContainer());

        $_SERVER['PATH_INFO'] = '/zzzzzzzzzzzz';
        $flag = DuckPhp_Sub::InitAsContainer($options)->thenRunAsContainer(false,function(){echo "welcome";});
        
        DuckPhp::G(new DuckPhp());
        DuckPhp_Sub::G(new DuckPhp_Sub());
        \DuckPhp\Core\PhaseContainer::GetContainerInstanceEx(new \DuckPhp\Core\PhaseContainer());
        $_SERVER['PATH_INFO'] = '/zzzzzzzzzzzz';
        $_SERVER['PATH_INFO'] = '/';
        $flag =DuckPhp_Sub::InitAsContainer($options)->thenRunAsContainer(true,function(){echo "welcome";});
        
        
        echo "<<<<<<<<<<<<<<<<<";
        $_SERVER['PATH_INFO'] = '/advance/hitme';
        $options['ext']=[
                DuckPhp_Sub::class =>[
                    'is_debug'=>true,
                    'console_enable'=>false,
                    'cli_mode' => 'hook',
                    'controller_url_prefix'=>'advance/',
                    'exception_reporter' => FakeReporter::class,
                    'controller_class_postfix'=>'Controller',
                    'controller_method_prefix'=>'action_',
                    'controller_welcome_class'=>'Fake',
                    'namespace_controller'=>'\tests\DuckPhp',
                    
                ],
            ];

        DuckPhp::G(new DuckPhp());
        DuckPhp_Sub::G(new DuckPhp_Sub());
        \DuckPhp\Core\PhaseContainer::GetContainerInstanceEx(new \DuckPhp\Core\PhaseContainer());
        $flag =DuckPhp_Sub::InitAsContainer($options)->thenRunAsContainer(false,function(){echo "welcome";});
        echo ">>>>>>>>>>>>>>>>>>>>>>>";
        $options = [
            'ext_options_file_enable'=>true,
            'ext_options_file'=>'NoExits.php',
        ];
        DuckPhp::G(new DuckPhp())->init($options);
        
        
        
        //////////////////////
        
        try{
        DuckPhp::Redis(0);
        }catch(\TypeError $ex){}
        DuckPhp::assignRewrite('11','22');
        DuckPhp::getRewrites();
        
        
        \LibCoverage\LibCoverage::G($LibCoverage);
        \LibCoverage\LibCoverage::End(DuckPhp::class);

    }

}
class FakeController
{
    public function action_hitme()
    {
        var_dump("hit!!!!!!!!!!!!!!");
    }
}
class DuckPhp_Sub extends DuckPhp
{
    public $options =[
        'class_session' => FakeSession::class,
        'class_admin' => FakeAdmin::class,
        'class_user' => FakeUser::class,
        
        'namespace_controller' => 'zz',
    ];
    public function onInit()
    {
        $this->bumpSingletonToRoot(FakeAdmin::class,\DuckPhp\Component\AdminObject::class);
        $this->bumpSingletonToRoot(FakeUser::class,\DuckPhp\Component\UserObject::class);
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
class FakeSession
{
    use SingletonExTrait;
    public function init($options = [], $context = null)
    {
        
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
class FakeReporter
{

}

