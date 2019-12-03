<?php
namespace tests\DuckPhp\Ext
{

use DuckPhp\Ext\StrictCheck;
use DuckPhp\App as DuckPhp;
use DuckPhp\Core\Route;
use DuckPhp\Core\SingletonEx;

use tests\DuckPhp\Ext\Model\FakeModel;
use tests\DuckPhp\Ext\Service\FakeService;

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
            'controller_welcome_class'=> 'StrictCheckTestMain',
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
        StrictCheck::G(new StrictCheck_FakeObject);

        DuckPhp::G()->init($dn_options);

        $options=[
            'namespace'=> __NAMESPACE__,
            'namespace_controller'=>        __NAMESPACE__ .'\\'.'Controller'.'\\',
            'namespace_service'=>           __NAMESPACE__ .'\\'.'Service'.'\\',
            'namespace_model'=>             __NAMESPACE__ .'\\'.'Model'.'\\',
            'controller_base_class'=>       __NAMESPACE__ .'\\'.'Base'.'\\'.'BaseController',
            'is_debug'=>true,
        ];
        StrictCheck::G(new StrictCheck)->init($options, DuckPhp::G());
        Route::G()->bind('foo');

        DuckPhp::G()->run();

        $options['is_debug']=false;
        StrictCheck::G()->init($options);
        DuckPhp::G()->run();
        $options['is_debug']=true;
        StrictCheck::G(new StrictCheck())->init($options)->checkStrictComponent('',0,[]);
        

        \MyCodeCoverage::G()->end(StrictCheck::class);
        $this->assertTrue(true);

    }
}
class StrictCheck_FakeObject
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
namespace tests\DuckPhp\Ext\Base {
use DuckPhp\Helper\ModelHelper as M;

class BaseController
{
}
class BaseController2 extends BaseController
{
    public function foo()
    {
        M::DB()->fetch("select 1+1 as t");
        var_dump("bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb");
    }
}
} // end tests\DuckPhp\Ext\Base

namespace tests\DuckPhp\Ext\Model {
use DuckPhp\Base\StrictModelTrait;
use tests\DuckPhp\Ext\Service\FakeService;
use DuckPhp\Helper\ModelHelper as M;

class FakeModel
{
    use StrictModelTrait;
    public function foo(){
        var_dump(DATE(DATE_ATOM));
    }
    public function callService(){
        FakeService::G()->foo();
    }
    public function callDB(){
        M::DB()->fetch("select 1+1 as t");
    }
}
class FakeExModel
{
    use StrictModelTrait;
    public function foo(){
        FakeModel::G()->foo();
    }
}
}  // end tests\DuckPhp\Ext\Model

namespace tests\DuckPhp\Ext\Service {
use DuckPhp\Base\StrictServiceTrait;
//use DuckPhp\Ext\DBManager;
use DuckPhp\App as DuckPhp;
use tests\DuckPhp\Ext\Model\FakeExModel;
use tests\DuckPhp\Ext\Model\FakeModel;
//use tests\DuckPhp\Ext\Model\FakeModel;

class FakeService
{
    use StrictServiceTrait;
    public function foo(){
        FakeLibService::G()->foo();
    }
    public function callService(){
        FakeService::G()->foo();
    }
    public function modelCallService(){
        FakeModel::G()->callService();
    }
    public function callDB(){
        DuckPhp::DB()->fetch("select 1+1 as t");
    }
    public function normal()
    {
        FakeModel::G()->callDB();
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

}  // end tests\DuckPhp\Ext\Service

namespace tests\DuckPhp\Ext\Controller {
use tests\DuckPhp\Ext\Base\BaseController;
use tests\DuckPhp\Ext\Base\BaseController2;
use tests\DuckPhp\Ext\Service\FakeBatchService;
use tests\DuckPhp\Ext\Service\FakeService;
use tests\DuckPhp\Ext\Model\FakeModel;
use DuckPhp\App as DuckPhp;
use DuckPhp\Helper\ModelHelper as M;

class StrictCheckTestMain extends BaseController
{
    public function index()
    {
    }
    public function foo()
    {
        FakeBatchService::G()->foo();
        
        echo "============================\n";
        
        try{
            DuckPhp::DB()->fetch("select 1+1 as t");
        }catch(\Throwable $ex){
            echo "zzzzzzzzzzzzz".$ex->getMessage().PHP_EOL;
        }
        try{
            M::DB()->fetch("select 1+1 as t");
        }catch(\Throwable $ex){
            echo "zzzzzzzzzzzzz Catch S::DB ".$ex->getMessage().PHP_EOL;
        }
        try{
            (new t)->foo();
        }catch(\Throwable $ex){
            echo "aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa".$ex->getMessage().PHP_EOL;
        }
        
        try{
            FakeModel::G()->foo();
        }catch(\Throwable $ex){
            echo "zzzzzzzzzzzzz".$ex->getMessage().PHP_EOL;
        }

        try{
            FakeService::G()->callService();
        }catch(\Throwable $ex){
            echo "sssFakeService::G()->callService()".$ex->getMessage().PHP_EOL;
        }
        try{
            FakeService::G()->modelCallService();
        }catch(\Throwable $ex){
            echo "sssssssss modelCallService sssssssssssssssssss".$ex->getMessage().PHP_EOL;
        }
        try{
            FakeService::G()->callDB();
        }catch(\Throwable $ex){
            echo "sssssssss modelCallService sssssssssssssssssss".$ex->getMessage().PHP_EOL;
        }
        
        
        try{
            DuckPhp::DB()->fetch("select 1+1 as t");
        }catch(\Throwable $ex){
            echo "zzzzzzzzzzzzz".$ex->getMessage().PHP_EOL;
        }
        try{
            M::DB()->fetch("select 1+1 as t");
        }catch(\Throwable $ex){
            echo "zzzzzzzzzzzzz Catch S::DB ".$ex->getMessage().PHP_EOL;
        }
        try{
            (new BaseController2)->foo();
        }catch(\Throwable $ex){
            echo "aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa".$ex->getMessage().PHP_EOL;
        }
        FakeService::G()->normal();
    }
}

}  // end tests\DuckPhp\Ext\Controller

 




