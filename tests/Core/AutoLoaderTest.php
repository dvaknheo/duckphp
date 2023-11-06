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
        $options=[
            'path'=>$path_autoload,
            'path_namespace'=>'AutoApp',
            'namespace'=>'for_autoloadertest',
            'skip_system_autoload'=>false,
            'skip_app_autoload'=>false,
            
            'autoload_cache_in_cli'=>true,
            'autoload_path_namespace_map' =>[
                'AutoApp3' => 'for_psr4\\'
            ],
        ];
        
        $G=AutoLoader::_();
        $G->init($options);
        $G->init($options); // retest
        
        $G->assignPathNamespace([
            'ThisPathNotExsits'=>'NoNameSpace',
            $path_autoload.'AutoApp2'=> 'for_autoloadertest2',
        ]);
        AutoLoader::addPsr4('ThisPathNotExsits3','NoNameSpace3');
        $G->run();
        $G->runAutoLoader(); //re-test
        AutoLoader::RunQuickly($options);
        
        echo "\n";
        $t=new \for_psr4\LoadMe(); //_autoload
$t->foo();
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
    
        AutoLoader::_()->cacheClasses();
        AutoLoader::_()->cacheClasses();
        //opcache_invalidate($file,true);
         
        AutoLoader::_()->cacheNamespacePath($path_autoload);
        AutoLoader::_()->cacheNamespacePath($path_autoload.'AutoApp/');
        AutoLoader::_()->cacheNamespacePath('ThisPastNotExsits');
        //$G->cacheNamespacePath(path_autoload);
        $G->clear();
        
        $path_autoload=\LibCoverage\LibCoverage::G()->getClassTestPath(AutoLoader::class);
        $sec=(new AutoLoader())->init([
            'skip_system_autoload'=>true,
            'skip_app_autoload'=>true,
            'path_namespace'=>'/path_autoload',
        ]);

        AutoLoader::_()->isInited();

        AutoLoader::_();
        AutoLoader::_(new AutoLoader());
        $t = \LibCoverage\LibCoverage::G();
        define('__SINGLETONEX_REPALACER',AutoLoaderObject::class.'::CreateObject');
        \LibCoverage\LibCoverage::G($t);
        AutoLoader::_();
        
        \LibCoverage\LibCoverage::End();
        /*
        AutoLoader::_()->_autoload($class);
        AutoLoader::_()->assignPathNamespace($path, $namespace=null);
        AutoLoader::_()->cacheClasses();
        AutoLoader::_()->cacheNamespacePath($path);
        AutoLoader::_()->cleanUp();
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