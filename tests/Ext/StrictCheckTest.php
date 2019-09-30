<?php
namespace tests\DNMVCS\Ext
{

use DNMVCS\Ext\StrictCheck;
use DNMVCS\DNMVCS;
use DNMVCS\Core\Route;
use DNMVCS\Core\SingletonEx;

use tests\DNMVCS\Ext\Model\FakeModel;
use tests\DNMVCS\Ext\Service\FakeService;

class StrictCheckTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(StrictCheck::class);
        
        $dn_options=[
            'skip_setting_file'=>true,
            'error_404'=>null,
            'is_debug'=>true,
            'error_debug'=>null,
            'namespace'=> __NAMESPACE__,
            
'database_list'=>[[
	'dsn'=>"mysql:host=127.0.0.1;port=3306;dbname=DnSample;charset=utf8;",
	'username'=>'admin',	
	'password'=>'123456'
],[
	'dsn'=>"mysql:host=127.0.0.1;port=3306;dbname=DnSample;charset=utf8;",
	'username'=>'admin',	
	'password'=>'123456'
]],

        ];
        StrictCheck::G(new FakeObject);
        
        DNMVCS::G()->init($dn_options);
        $options=[
            'namespace'=> __NAMESPACE__,
            'namespace_controller'=>        __NAMESPACE__ .'\\'.'Controller'.'\\',
            'namespace_service'=>           __NAMESPACE__ .'\\'.'Service'.'\\',
            'namespace_model'=>             __NAMESPACE__ .'\\'.'Model'.'\\',
            'controller_base_class'=>       __NAMESPACE__ .'\\'.'Base'.'\\'.'BaseController'.'\\',
            'is_debug'=>1,
            'app_class'=>null,
        ];
        StrictCheck::G(new StrictCheck)->init($options, DNMVCS::G());
        Route::G()->bind('foo');
        DNMVCS::G()->run();
        
        
        
        
if(false){
        
        
        
        try{
            FakeObject::G()->forModel();
        }catch(\Throwable $ex){
            echo $ex->getMessage();
            echo PHP_EOL;
        }
        try{
            FakeModel::G()->foo();
        }catch(\Throwable $ex){
            echo $ex->getMessage();
            echo PHP_EOL;
        }

        $trace_level=1;
        
        try{
            FakeObject::G()->foo();
        }catch(\Throwable $ex){
            echo "!";
            echo $ex->getMessage();
            echo PHP_EOL;
        }
        
}
        \MyCodeCoverage::G()->end(StrictCheck::class);
        $this->assertTrue(true);

    }
}
class FakeObject
{
    use SingletonEx;
    public function init($options,$context)
    {
        echo "FakeOject init...";
    }
    public function forModel()
    {
        FakeModel::G()->foo();
    }
    public function forcheckStrictParentCaller()
    {
        //checkStrictParentCaller
    }
    public function foo()
    {
        $parent_class=StrictCheckTest::class;
        StrictCheck::G()->checkStrictParentCaller($parent_class,1);
        StrictCheck::G()->checkStrictParentCaller($parent_class,1);
    }
}

}
namespace tests\DNMVCS\Ext\Base {
} // end tests\DNMVCS\Ext\Base

namespace tests\DNMVCS\Ext\Model {
use DNMVCS\Base\StrictModelTrait;
class FakeModel
{
    use StrictModelTrait;
    public function foo(){
        var_dump(DATE(DATE_ATOM));
    }
}
class FakeExModel
{
    use StrictModelTrait;
    public function foo(){
        FakeModel::G()->foo();
    }
}
}  // end tests\DNMVCS\Ext\Model

namespace tests\DNMVCS\Ext\Service {
use DNMVCS\Base\StrictServiceTrait;
use tests\DNMVCS\Ext\Model\FakeExModel;
use tests\DNMVCS\Ext\Model\FakeModel;

class FakeService
{
    use StrictServiceTrait;
    public function foo(){
        FakeLibService::G()->foo();
    }
}
class FakeBatchService
{
    use StrictServiceTrait;
    public function foo(){
        FakeService::G()->foo();
    }
}

class FakeLibService
{
    use StrictServiceTrait;
    public function foo(){
        FakeExModel::G()->foo();
    }
}

}  // end tests\DNMVCS\Ext\Service

namespace tests\DNMVCS\Ext\Controller {
use tests\DNMVCS\Ext\Service\FakeBatchService;
use DNMVCS\DNMVCS;
class Main
{
    public function index()
    {
    }
    public function foo()
    {
        FakeBatchService::G()->foo();
        try{
        DNMVCS::DB()->query("select 1+1 as t");
        }catch(\Throwable $ex){
        }
        echo "ssssssssssssssssssssssssssss";
        
    }
}
}  // end tests\DNMVCS\Ext\Controller

 




