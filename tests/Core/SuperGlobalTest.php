<?php
namespace tests\DNMVCS\Core;

use DNMVCS\Core\SuperGlobal;

class SuperGlobalTest extends \PHPUnit\Framework\TestCase
{
    static $x;

    public function testAll()
    {
        \MyCodeCoverage::G()->begin(SuperGlobal::class);
        
        //code here
        SuperGlobal::G()->init();
        
        $k="k";$v="v";
        $class_name=static::class;
        $var_name="x";
        SuperGlobal::G()->_GLOBALS($k, $v=null);
        SuperGlobal::G()->_STATICS($k, $v=null);
        SuperGlobal::G()->_CLASS_STATICS($class_name, $var_name);        
        
        SuperGlobal::G()->session_start($options=[]);
        SuperGlobal::G()->session_id(null);
        SuperGlobal::G()->session_destroy();
        $handler=new SuperGlobalFakeSessionHandler();
        SuperGlobal::G()->session_set_save_handler( $handler);
        

        \MyCodeCoverage::G()->end();
        $this->assertTrue(true);
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
class SuperGlobalFakeSessionHandler implements \SessionHandlerInterface
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