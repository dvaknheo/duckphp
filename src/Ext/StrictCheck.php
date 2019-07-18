<?php
namespace DNMVCS\Ext;

use DNMVCS\SingletonEx;
use Exception;

class StrictCheck
{
    use SingletonEx;
    const DEFAULT_OPTIONS=[
    ];
    const MAX_TRACE_LEVEL=20;
    protected $appClass=null;
    public function init($options=[], $context=null)
    {
        if ($context) {
            $this->initContext($options, $context);
        }
    }
    protected function initContext($options=[], $context=null)
    {
        $this->appClass=get_class($context);
    }
    public static function OnCheckStrictDB()
    {
        return static::G()->_OnCheckStrictDB($object);
    }
    ///////////////////////////////////////////////////////////
    protected function getCallerByLevel($level)
    {
        $level+=1;
        $backtrace=debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, static::MAX_TRACE_LEVEL);
        $caller_class=$backtrace[$level]['class']??'';
        if ($this->appClass && (is_subclass_of($this->appClass, $caller_class) || $this->appClass===$caller_class)) {
            $caller_class=$backtrace[$level+1]['class']??'';
        }
          

        return $caller_class;
    }
    protected function checkEnv()
    {
        if (!$this->appClass) {
            return false;
        }
        $flag=($this->appClass)::G()->options['is_debug'];
        if (!$flag) {
            return false;
        }
        return true;
    }
    public function checkStrictComponent($component_name, $trace_level)
    {
        if (!$this->checkEnv()) {
            return;
        }
        
        $caller_class=$this->getCallerByLevel($trace_level);
        $namespace=($this->appClass)::G()->options['namespace'];
        $namespace_service=$namespace."\\Service\\";
        
        $namespace_controller=$namespace."\\Controller\\"; // TODO
        
        $controller_base_class=($this->appClass)->options['controller_base_class']??'';
        
        do {
            if (substr($caller_class, 0, strlen($namespace_controller))==$namespace_controller) {
                throw new Exception(true, "$component_name Can not Call By Controller");
            }
            if ($controller_base_class && (is_subclass_of($caller_class, $controller_base_class) || $caller_class===$controller_base_class)) {
                throw new Exception(true, "$component_name Can not Call By Controller");
            }
            if (substr($caller_class, 0, strlen($namespace_service))===$namespace_service) {
                throw new Exception(true, "$component_name Can not Call By Service");
            }
            if (substr($caller_class, 0-strlen("Service"))=="Service") {
                throw new Exception(true, "$component_name Can not Call By Service");
            }
        } while (false);
    }
    public function checkStrictModel($trace_level)
    {
        if (!$this->checkEnv()) {
            return;
        }
        $caller_class=$this->getCallerByLevel($trace_level);
        
        $namespace=($this->appClass)::G()->options['namespace'];
        $namespace_service=$namespace."\\Service\\";
        
        do {
            if (substr($caller_class, 0, strlen($namespace_service))===$namespace_service) {
                break;
            }
            if (substr($caller_class, 0, 0-strlen("Service"))=="Service") {
                break;
            }
            if (substr($caller_class, 0, 0-strlen("ExModel"))=="ExModel") {
                break;
            }
            throw new Exception("Model Can Only call by Service or ExModel!");
        } while (false);
    }
    public function checkStrictService($trace_level)
    {
        if (!$this->checkEnv()) {
            return;
        }
        $caller_class=$this->getCallerByLevel($trace_level);
        
        $namespace=($this->appClass)::G()->options['namespace'];
        $namespace_service=$namespace."\\Service\\";

        $namespace_model=$namespace."\\Model\\";
        
        if (substr($caller_class, 0, 0-strlen("BatchService"))==="BatchService") {
            return;
        }
        if (substr($caller_class, 0, 0-strlen("LibService"))==="LibService") {
            do {
                if (substr($caller_class, 0, strlen($namespace_service))===$namespace_service) {
                    break;
                }
                if (substr($caller_class, 0, 0-strlen("Service"))==="Service") {
                    break;
                }
                throw new Exception(true, "LibService Must Call By Serivce($caller_class)");
            } while (false);
        } else {
            do {
                if (substr($caller_class, 0, strlen($namespace_service))===$namespace_service) {
                    throw new Exception(true, "Service Can not call Service($caller_class)");
                }
                if (substr($caller_class, 0, strlen("Service"))==="Service") {
                    throw new Exception(true, "Service Can not call Service($caller_class)");
                }
                if (substr($caller_class, 0, strlen($namespace_model))===$namespace_model) {
                    throw new Exception(true, "Service Can not call by Model($caller_class)");
                }
                if (substr($caller_class, 0, strlen("Model"))==="Model") {
                    throw new Exception(true, "Service Can not call by Model($caller_class)");
                }
            } while (false);
        }
    }
    public function checkStrictParentCaller($trace_level, $parent_class)
    {
        if (!$this->checkEnv()) {
            return;
        }
        $caller_class=$this->getCallerByLevel($trace_level);
        
        $class=get_class($object);
        $flag=(is_subclass_of($caller_class, $parent_class) || $caller_class===$parent_class)?true:false;
        throw new Exception(!$flag, " checkStrictParentCaller Fail:Class [$class] Must By Calss [$parent_class]");
    }
}
