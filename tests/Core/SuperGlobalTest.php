<?php
namespace tests\DuckPhp\Core;

use DuckPhp\Core\SuperGlobal;

class SuperGlobalTest extends \PHPUnit\Framework\TestCase
{

    public function testAll()
    {
        \MyCodeCoverage::G()->begin(SuperGlobal::class);
        
        //code here
        SuperGlobal::G()->init([])->reset()->init([]);
        
        $k="k";$v="v";
        SuperGlobal::G()->_GLOBALS($k, $v=null);
        SuperGlobal::G()->_STATICS($k, $v=null);
        SuperGlobal::G()->_CLASS_STATICS(SuperGlobal_SimpleObject::class, 'class_var');        
        
        SuperGlobal::G()->session_start($options=[]);
        SuperGlobal::G()->session_start($options=[]);
        SuperGlobal::G()->session_id(null);
        SuperGlobal::G()->session_destroy();
        
        $handler=new SuperGlobal_FakeSessionHandler();
        SuperGlobal::G()->session_set_save_handler($handler);
        try {
            SuperGlobal::G()->session_id('12345'); //again;
        } catch (\Throwable $ex) {
        }
        SuperGlobal::G()->isInited();
        
        \MyCodeCoverage::G()->end();
        /*
        SuperGlobal::G()->__construct();
        SuperGlobal::G()->init();
        SuperGlobal::G()->session_start(array $options=[]);
        SuperGlobal::G()->session_id($session_id);
        SuperGlobal::G()->session_destroy();
        SuperGlobal::G()->session_set_save_handler($handler);
        SuperGlobal::G()->_GLOBALS($k, $v=null);
        SuperGlobal::G()->_STATICS($name, $value=null, $parent=0);
        SuperGlobal::G()->_CLASS_STATICS($class_name, $var_name);
        //*/
    }
}
class SuperGlobal_SimpleObject
{
    static $class_var;

}
class SuperGlobal_FakeSessionHandler implements \SessionHandlerInterface
{
    public function open($savePath, $sessionName)
    {
    }
    public function close()
    {
    }
    public function read($id)
    {
    }
    public function write($id, $data)
    {
    }
    public function destroy($id)
    {
        return true;
    }
    public function gc($maxlifetime)
    {
        return true;
    }
}