<?php
namespace tests\DNMVCS\Core;

use DNMVCS\Core\AutoLoader;

class AutoLoaderTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(AutoLoader::class);
        $path_base=realpath(__DIR__.'/../../tests');
        $path_autoload=$path_base.'/data_for_tests/Core/AutoLoader/';
        $options=[
            'path'=>$path_autoload,
            'path_namespace'=>'AutoApp',
            'namespace'=>'for_autoloadertest',
            'skip_system_autoload'=>false,
            'skip_app_autoload'=>false,
            
            'enable_cache_classes_in_cli'=>true,
        ];
        $secode=(new AutoLoader())->init([]);
        $G=new AutoLoader();
        $G->init($options);
        $G->init($options); // retest
        $G->run();
        $G->run(); //retest
        
        $t=new \for_autoloadertest\LoadMe(); //_autoload
        
        $G->cacheNamespacePath(__DIR__);
        $G->cleanUp();

        $sec=(new AutoLoader())->init([
            'skip_system_autoload'=>true,
            'skip_app_autoload'=>true,
        ]);
        $sec->assignPathNamespace([
            
        ]);

        \MyCodeCoverage::G()->end(AutoLoader::class);
        $this->assertTrue(true);
        /*
        AutoLoader::G()->_autoload($class);
        AutoLoader::G()->assignPathNamespace($path, $namespace=null);
        AutoLoader::G()->cacheClasses();
        AutoLoader::G()->cacheNamespacePath($path);
        AutoLoader::G()->cleanUp();
        //*/
    }
}
