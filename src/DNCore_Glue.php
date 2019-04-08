<?php
namespace DNMVCS;

trait DNCore_Glue
{
    // system static
    public static function Platform()
    {
        return static::G()->platform;
    }
    public static function Developing()
    {
        return static::G()->is_dev;
    }
    public static function IsRunning()
    {
        return DNRuntimeState::G()->isRunning();
    }
    public static function Import($file)
    {
        return static::G()->_Import($file);
    }
    
    // route static
    public static function URL($url=null)
    {
        return DNRoute::G()->_URL($url);
    }
    public static function Parameters()
    {
        return DNRoute::G()->_Parameters();
    }
    public static function Show($data=[], $view=null)
    {
        return DNView::G()->_Show($data, $view);
    }
    public static function ShowBlock($view, $data=null)
    {
        return DNView::G()->_ShowBlock($view, $data);
    }
    
    // config static
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
    
    /////////////////////////////////
    //autoloader
    public function assignPathNamespace($path, $namespace=null)
    {
        return DNAutoLoader::G()->assignPathNamespace($path, $namespace);
    }
    
    // route
    public function addRouteHook($hook, $prepend=false, $once=true)
    {
        return DNRoute::G()->addRouteHook($hook, $prepend, $once);
    }
    public function getRouteCallingMethod()
    {
        return DNRoute::G()->getRouteCallingMethod();
    }
    
    //view
    public function setViewWrapper($head_file=null, $foot_file=null)
    {
        return DNView::G()->setViewWrapper($head_file, $foot_file);
    }
    public function assignViewData($key, $value=null)
    {
        return DNView::G()->assignViewData($key, $value);
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
}
