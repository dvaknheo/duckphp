<?php
namespace DNMVCS\Base;

use DNMVCS\Core\Base\ControllerHelper as Helper;

use DNMVCS\Core\App;
use DNMVCS\Basic\SuperGlobal;
use DNMVCS\Ext\Pager;

class ControllerHelper extends Helper
{
    public $_GET;
    public $_POST;
    public $_REQUEST;
    public $_SERVER;
    public $_ENV;
    public $_COOKIE;
    public $_SESSION;
    public $_FILES;
    
    public static function EnableStaticSuperGlobal()
    {
        $this->_GET		=SuperGlobal::G()->_GET;
        $this->_POST	=SuperGlobal::G()->_POST;
        $this->_REQUEST	=SuperGlobal::G()->_REQUEST;
        $this->_SERVER	=SuperGlobal::G()->_SERVER;
        $this->_ENV		=SuperGlobal::G()->_ENV;
        $this->_COOKIE	=SuperGlobal::G()->_COOKIE;
        $this->_SESSION	=SuperGlobal::G()->_SESSION;
        $this->_FILES	=SuperGlobal::G()->_FILES;
    }
    ///////
    public static function Import($file)
    {
        return App::G()->_Import($file);
    }
    public static function RecordsetUrl(&$data, $cols_map=[])
    {
        return App::G()->_RecordsetUrl($data, $cols_map);
    }
    
    public static function RecordsetH(&$data, $cols=[])
    {
        return App::G()->_RecordsetH($data, $cols);
    }
    ///////
    public static function SG()
    {
        return SuperGlobal::G();
    }
    public static function &GLOBALS($k, $v=null)
    {
        return SuperGlobal::G()->_GLOBALS($k, $v);
    }
    
    public static function &STATICS($k, $v=null)
    {
        return SuperGlobal::G()->_STATICS($k, $v, 1);
    }
    public static function &CLASS_STATICS($class_name, $var_name)
    {
        return SuperGlobal::G()->_CLASS_STATICS($class_name, $var_name);
    }
    ////
    public static function session_start(array $options=[])
    {
        return SuperGlobal::G()->session_start($options);
    }
    public function session_id($session_id=null)
    {
        return SuperGlobal::G()->session_id($session_id);
    }
    public static function session_destroy()
    {
        return SuperGlobal::G()->session_destroy();
    }
    public static function session_set_save_handler(\SessionHandlerInterface $handler)
    {
        return SuperGlobal::G()->session_set_save_handler($handler);
    }
    ////
    public static function Pager()
    {
        return Pager::G();
    }
}
