<?php
namespace DNMVCS;

trait DNMVCS_Glue
{
    //route
    public static function URL($url=null)
    {
        return DNRoute::G()->_URL($url);
    }
    public static function Parameters()
    {
        return DNRoute::G()->_Parameters();
    }
    public function assignRewrite($key, $value=null)
    {
        if (is_array($key)&& $value===null) {
            $this->options['rewrite_map']=array_merge($this->options['rewrite_map'], $key);
        } else {
            $this->options['rewrite_map'][$key]=$value;
        }
    }
    public function assignRoute($key, $value=null)
    {
        if (is_array($key)&& $value===null) {
            $this->options['route_map']=array_merge($this->options['route_map'], $key);
        } else {
            $this->options['route_map'][$key]=$value;
        }
    }
    public function addRouteHook($hook, $prepend=false, $once=true)
    {
        return DNRoute::G()->addRouteHook($hook, $prepend, $once);
    }
    public function getRouteCallingMethod()
    {
        return DNRoute::G()->getRouteCallingMethod();
    }
    //view
    public static function Show($data=[], $view=null)
    {
        return DNView::G()->_Show($data, $view);
    }

    public static function ExitJson($ret)
    {
        return DNMVCSExt::G()->_ExitJson($ret);
    }
    public static function ExitRedirect($url, $only_in_site=true)
    {
        return DNMVCSExt::G()->_ExitRedirect($url, $only_in_site);
    }
    public static function ExitRouteTo($url)
    {
        return DNMVCSExt::G()->_ExitRedirect(static::URL($url), true);
    }
    public static function Exit404()
    {
        static::G()->onShow404();
        static::exit_system();
    }
    public function setViewWrapper($head_file=null, $foot_file=null)
    {
        return DNView::G()->setViewWrapper($head_file, $foot_file);
    }
    public static function ShowBlock($view, $data=null)
    {
        return DNView::G()->_ShowBlock($view, $data);
    }
    public function assignViewData($key, $value=null)
    {
        return DNView::G()->assignViewData($key, $value);
    }
    //config
    public static function Setting($key)
    {
        return DNConfiger::G()->_Setting($key);
    }
    public static function Config($key, $file_basename='config')
    {
        return DNConfiger::G()->_Config($key, $file_basename);
    }
    public static function LoadConfig($file_basename)
    {
        return DNConfiger::G()->_LoadConfig($file_basename);
    }
    
    //exception manager
    public function assignExceptionHandler($classes, $callback=null)
    {
        return DNExceptionManager::G()->assignExceptionHandler($classes, $callback);
    }
    public function setMultiExceptionHandler(array $classes, $callback)
    {
        return DNExceptionManager::G()->setMultiExceptionHandler($classes, $callback);
    }
    public function setDefaultExceptionHandler($callback)
    {
        return DNExceptionManager::G()->setDefaultExceptionHandler($callback);
    }
    public static function ThrowOn($flag, $message, $code=0)
    {
        if (!$flag) {
            return;
        }
        throw new DNException($message, $code);
    }

    public static function DB($tag=null)
    {
        return DNDBManager::G()->_DB($tag);
    }
    public static function DB_W()
    {
        return DNDBManager::G()->_DB_W();
    }
    public static function DB_R()
    {
        return DNDBManager::G()->_DB_R();
    }
    public static function Import($file)
    {
        return static::G()->_Import($file);
    }
    public static function DI($name, $object=null)
    {
        return DNMVCSExt::G()->_DI($name, $object);
    }
    public function assignPathNamespace($path, $namespace=null)
    {
        return DNAutoLoader::G()->assignPathNamespace($path, $namespace);
    }
    public static function Platform()
    {
        return static::G()->platform;
    }
    public static function Developing()
    {
        return static::G()->isDev;
    }
    public static function InSwoole()
    {
        if (PHP_SAPI!=='cli') {
            return false;
        }
        if (!class_exists('Swoole\Coroutine')) {
            return false;
        }
        
        $cid = \Swoole\Coroutine::getuid();
        if ($cid<=0) {
            return false;
        }
        
        return true;
    }
    public static function IsRunning()
    {
        return DNRuntimeState::G()->isRunning();
    }
    public static function SG()
    {
        return DNSuperGlobal::G();
    }
    public static function &GLOBALS($k, $v=null)
    {
        return DNSuperGlobal::G()->_GLOBALS($k, $v);
    }
    
    public static function &STATICS($k, $v=null)
    {
        return DNSuperGlobal::G()->_STATICS($k, $v, 1);
    }
    public static function &CLASS_STATICS($class_name, $var_name)
    {
        return DNSuperGlobal::G()->_CLASS_STATICS($class_name, $var_name);
    }
}
