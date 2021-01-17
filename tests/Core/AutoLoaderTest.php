<?php
namespace tests\DuckPhp\Core;

use DuckPhp\Core\AutoLoader;

class AutoLoaderTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        //\opcache_reset();
        //$this->assertTrue(ini_get('opcache.enable_cli'));
        \LibCoverage\LibCoverage::Begin(AutoLoader::class);
        $path_autoload=\LibCoverage\LibCoverage::G()->getClassTestPath(AutoLoader::class);
        var_dump($path_autoload);
        $options=[
            'path'=>$path_autoload,
            'path_namespace'=>'AutoApp',
            'namespace'=>'for_autoloadertest',
            'skip_system_autoload'=>false,
            'skip_app_autoload'=>false,
            
            'autoload_cache_in_cli'=>true,
        ];
        
        $G=AutoLoader::G();
        $G->init($options);
        $G->init($options); // retest
        
        $G->assignPathNamespace([
            'ThisPathNotExsits'=>'NoNameSpace',
            $path_autoload.'AutoApp2'=> 'for_autoloadertest2',
        ]);
        $G->run();
        $G->runAutoLoader(); //re-test
        
        

        $t=new \for_autoloadertest\LoadMe(); //_autoload
        $t->foo();
    try{
        $tt=new \for_autoloadertest2\LoadMe(); //_autoload
        $tt->foo();
    }catch(\Throwable $ex){
    }
    
     try{
        $tt=new \for_autoloadertest2\ThisClassNotExsits(); //_autoload
        $tt->foo();
    }catch(\Throwable $ex){
    }
    
        AutoLoader::G()->cacheClasses();
        AutoLoader::G()->cacheClasses();
        //opcache_invalidate($file,true);
         
        AutoLoader::G()->cacheNamespacePath($path_autoload);
        AutoLoader::G()->cacheNamespacePath($path_autoload.'AutoApp/');
        AutoLoader::G()->cacheNamespacePath('ThisPastNotExsits');
        //$G->cacheNamespacePath(path_autoload);
        $G->clear();
        
        $path_autoload=\LibCoverage\LibCoverage::G()->getClassTestPath(AutoLoader::class);
        $sec=(new AutoLoader())->init([
            'skip_system_autoload'=>true,
            'skip_app_autoload'=>true,
            'path_namespace'=>'/path_autoload',
        ]);

        AutoLoader::G()->isInited();

        AutoLoader::G();
        AutoLoader::G(new AutoLoader());
        $t = \LibCoverage\LibCoverage::G();
        define('__SINGLETONEX_REPALACER',AutoLoaderObject::class.'::CreateObject');
        \LibCoverage\LibCoverage::G($t);
        AutoLoader::G();
        
        \LibCoverage\LibCoverage::End();
        /*
        AutoLoader::G()->_autoload($class);
        AutoLoader::G()->assignPathNamespace($path, $namespace=null);
        AutoLoader::G()->cacheClasses();
        AutoLoader::G()->cacheNamespacePath($path);
        AutoLoader::G()->cleanUp();
        //*/
    }
}
class AutoLoaderObject
{    
    public static function CreateObject($class, $object)
    {
        static $_instance;
        $_instance=$_instance??[];
        $_instance[$class]=$object?:($_instance[$class]??($_instance[$class]??new $class));
        return $_instance[$class];
    }

}