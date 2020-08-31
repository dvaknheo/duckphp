<?php declare(strict_types=1);
namespace tests\DuckPhp\Ext
{

use DuckPhp\Ext\StrictCheck;
use DuckPhp\DuckPhp;
use DuckPhp\Core\Route;
use DuckPhp\Core\SingletonEx;

use tests\DuckPhp\Ext\Model\FakeModel;
use tests\DuckPhp\Ext\Service\FakeService;

class StrictCheckTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(StrictCheck::class);
        $database_list = include \MyCodeCoverage::G()->options['path_data'] . 'database_list.php';

        $dn_options=[
            'skip_setting_file'=>true,
            'error_404'=>null,
            
            'is_debug'=>true,
            
            'error_debug'=>null,
            'namespace'=> __NAMESPACE__,
            'controller_welcome_class'=> 'StrictCheckTestMain',
            'database_list'=>$database_list,

        ];
        StrictCheck::G(new StrictCheck_FakeObject);

        DuckPhp::G()->init($dn_options);

        $options=[
            'namespace'=> __NAMESPACE__,
            'namespace_controller'=>        __NAMESPACE__ .'\\'.'Controller'.'\\',
            'namespace_business'=>           __NAMESPACE__ .'\\'.'Service'.'\\',
            'namespace_model'=>             __NAMESPACE__ .'\\'.'Model'.'\\',
            'controller_base_class'=>       __NAMESPACE__ .'\\'.'Base'.'\\'.'BaseController',
            'is_debug'=>true,
            'ext'=>[
                StrictCheck::class => true,
            ],

        ];
        StrictCheck::G(new StrictCheck)->init($options, DuckPhp::G());
        Route::G()->bind('foo');

        DuckPhp::G()->run();

        StrictCheck::G()->options['is_debug']=false;
        DuckPhp::G()->run();

        $options['is_debug']=true;
        $options['namespace_business']='';
        StrictCheck::G(new StrictCheck())->init($options)->checkStrictClass('NoExt',0);
        

        \MyCodeCoverage::G()->end();
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
        // no use $parent_class=StrictCheckTest::class;
        // no use StrictCheck::G()->checkStrictParentCaller($parent_class,1);
        // no use StrictCheck::G()->checkStrictParentCaller($parent_class,1);
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
use DuckPhp\Ext\StrictCheckObjectTrait;
use tests\DuckPhp\Ext\Service\FakeService;
use DuckPhp\Helper\ModelHelper as M;

class FakeModel
{
    use StrictCheckObjectTrait;
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
    use StrictCheckObjectTrait;
    public function foo(){
        FakeModel::G()->foo();
    }
}
}  // end tests\DuckPhp\Ext\Model

namespace tests\DuckPhp\Ext\Service {
use DuckPhp\Ext\StrictCheckObjectTrait;
//use DuckPhp\Ext\DBManager;
use DuckPhp\DuckPhp;
use tests\DuckPhp\Ext\Model\FakeExModel;
use tests\DuckPhp\Ext\Model\FakeModel;
//use tests\DuckPhp\Ext\Model\FakeModel;

class FakeService
{
    use StrictCheckObjectTrait;
    public function foo(){
        FakeLib::G()->foo();
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
class FakeBatchBusiness
{
    use StrictCheckObjectTrait;
    public function foo(){
        FakeService::G()->foo();
    }
}

class FakeLib
{
    use StrictCheckObjectTrait;
    public function foo(){
        FakeExModel::G()->foo();
    }
}

}  // end tests\DuckPhp\Ext\Service

namespace tests\DuckPhp\Ext\Controller {

use tests\DuckPhp\Ext\Base\BaseController;
use tests\DuckPhp\Ext\Base\BaseController2;
use tests\DuckPhp\Ext\Service\FakeBatchBusiness;
use tests\DuckPhp\Ext\Service\FakeService;
use tests\DuckPhp\Ext\Model\FakeModel;
use DuckPhp\DuckPhp;
use DuckPhp\Helper\ModelHelper as M;

class StrictCheckTestMain extends BaseController
{
    public function index()
    {
    }
    public function foo()
    {
        echo "0000000\n";
        
        try{
            DuckPhp::DB()->fetch("select 1+1 as t");
        }catch(\Throwable $ex){
            echo "111111111111".$ex->getMessage().PHP_EOL;
        }
        try{
            M::DB()->fetch("select 1+1 as t");
        }catch(\Throwable $ex){
            echo "2222222222222222222 Catch M::DB ".$ex->getMessage().PHP_EOL;
        }
        
        try{
            (new t)->foo();
        }catch(\Throwable $ex){
            echo "33333333333333333333333".$ex->getMessage().PHP_EOL;
        }
        
        try{
        
            FakeModel::G()->foo();
        }catch(\Throwable $ex){
            echo "4444444444444444444444444".$ex->getMessage().PHP_EOL;
        }
    
        try{
            
            FakeService::G()->callService();
        }catch(\Throwable $ex){
            echo "55555555555555555555555555555FakeService::G()->callService()".$ex->getMessage().PHP_EOL;
        }
        try{
            FakeService::G()->modelCallService();
        }catch(\Throwable $ex){
            echo "6666666666666 modelCallService ".$ex->getMessage().PHP_EOL;
        }
        try{
            FakeService::G()->callDB();
        }catch(\Throwable $ex){
            echo "7777777777777777 modelCallService ".$ex->getMessage().PHP_EOL;
        }

        
        try{
            DuckPhp::DB()->fetch("select 1+1 as t");
        }catch(\Throwable $ex){
            echo "8888888888888888 ".$ex->getMessage().PHP_EOL;
        }
        try{
            M::DB()->fetch("select 1+1 as t");
        }catch(\Throwable $ex){
            echo "9999999999999999999 Catch S::DB ".$ex->getMessage().PHP_EOL;
        }

        try{
            (new BaseController2)->foo();
        }catch(\Throwable $ex){
            echo "aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa ".$ex->getMessage().PHP_EOL;
        }
                               

        FakeService::G()->normal();
        
        echo "============================\n";
        
         
        FakeBatchBusiness::G()->foo();
//exit;
    }
}

}  // end tests\DuckPhp\Ext\Controller

 




