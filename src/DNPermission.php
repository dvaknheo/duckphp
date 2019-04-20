<?php
namespace DNMVCS;

class DNPermission
{
    use DNSingleton;
    
    public static function StrictService($object=null)
    {
        if (!DNMVCS::Developing()) {
            return $object;
        }
        $trace=debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
        return DNPermission::G()->checkStrictService($trace, $object);
    }
    
    public static function StrictService($object=null)
    {
        if (!DNMVCS::Developing()) {
            return $object;
        }
        $trace=debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
        return DNPermission::G()->checkStrictModel($trace, $object);
    }
    public static function StrictDB()
    {
        if (!DNMVCS::Developing()) {
            return $object;
        }
        $trace=debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
        return DNPermission::G()->checkStrictModel($trace, $object);
    }
    public function checkDBPermission()
    {
        if (!static::Developing()) {
            return;
        }
        
        $backtrace=debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10);
        $caller_class='';
        $base_class=get_class(static::G());
        foreach ($backtrace as $i=>$v) {
            if ($v['class']===$base_class) {
                $caller_class=$backtrace[$i+1]['class'];
                break;
            }
        }
        $namespace=DNMVCS::G()->options['namespace'];
        $namespace_controller=DNMVCS::G()->options['namespace_controller'];
        
        $controller_base_class=DNMVCS::G()->options['controller_base_class'];
        $namespace_controller.='\\';
        do {
            //if ($caller_class==$default_controller_class) {
            //    static::ThrowOn(true, "DB Can not Call By Controller");
            //}
            //if (substr($caller_class, 0, strlen($namespace_controller))==$namespace_controller) {
            //    static::ThrowOn(true, "DB Can not Call By Controller");
            //}
            if (substr($caller_class, 0, strlen("$namespace\\Service\\"))=="$namespace\\Service\\") {
                DNMVCS::ThrowOn(true, "DB Can not Call By Service");
            }
            if (substr($caller_class, 0-strlen("Service"))=="Service") {
                DNMVCS::ThrowOn(true, "DB Can not Call By Service");
            }
        } while (false);
    }
    public static function CheckStrictModel($trace, $model)
    {
        if (!DNMVCS::Developing()) {
            return $object;
        }
        list($_0, $_1, $caller)=debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
        $caller_class=$caller['class'];
        
        $namespace=DNMVCS::G()->options['namespace'];
        
        do {
            if (substr($caller_class, 0, strlen("$namespace\\Service\\"))=="$namespace\\Service\\") {
                break;
            }
            if (substr($caller_class, 0, 0-strlen("Service"))=="Service") {
                break;
            }
            if (substr($caller_class, 0, 0-strlen("ExModel"))=="ExModel") {
                break;
            }
            DNMVCS::ThrowOn(true, "Model Can Only call by Service or ExModel!");
        } while (false);
        return $object;
    }
    public static function CheckStrictService($trace, $object)
    {
        $caller_class=$caller['class'];
        $namespace=DNMVCS::G()->options['namespace'];
        if (substr($caller_class, 0, 0-strlen("LibService"))==="BatchService") {
            return $object;
        }
        if (substr($caller_class, 0, 0-strlen("LibService"))==="LibService") {
            do {
                if (substr($caller_class, 0, strlen("$namespace\\Service\\"))==="$namespace\\Service\\") {
                    break;
                }
                if (substr($caller_class, 0, 0-strlen("Service"))==="Service") {
                    break;
                }
                DNMVCS::ThrowOn(true, "LibService Must Call By Serivce($caller_class)");
            } while (false);
        } else {
            do {
                if (substr($caller_class, 0, strlen("$namespace\\Service\\"))==="$namespace\\Service\\") {
                    DNMVCS::ThrowOn(true, "Service Can not call Service($caller_class)");
                }
                if (substr($caller_class, 0, strlen("Service"))==="Service") {
                    DNMVCS::ThrowOn(true, "Service Can not call Service($caller_class)");
                }
                if (substr($caller_class, 0, strlen("$namespace\\Model\\"))==="$namespace\\Model\\") {
                    DNMVCS::ThrowOn(true, "Service Can not call by Model($caller_class)");
                }
                if (substr($caller_class, 0, strlen("Model"))==="Model") {
                    DNMVCS::ThrowOn(true, "Service Can not call by Model($caller_class)");
                }
            } while (false);
        }
        return $object;
    }
}
