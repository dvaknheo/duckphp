<?php
namespace tests\DuckPhp\Core;

use DuckPhp\Core\Configer;

class ConfigerTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(Configer::class);
        
        $path_config=\LibCoverage\LibCoverage::G()->getClassTestPath(Configer::class);
        $options=[
            'path'=>$path_config,
            'path_config'=>$path_config,
            'use_setting_file'=>true,
            'use_env_file'=>true,
        ];
        Configer::G()->init($options);
        Configer::G()->init($options);
        $key="key";
        Configer::G()->_Setting($key);
        Configer::G()->_Setting($key);
        Configer::G()->_Config($key, $file_basename='config');
        Configer::G()->_LoadConfig($file_basename='config');

        $options=[
            'path'=>dirname($path_config),
            'path_config'=>basename($path_config),
        ];
        Configer::G(new Configer)->init($options);
        try{
         Configer::G()->_Setting($key);
        }catch(\Throwable $ex){
        }
        Configer::G()->options['config_ext_file_map']=['X/a'=>$path_config.'/for_assign.php'];
        Configer::G()->_LoadConfig('X/a');
        
        Configer::G()->isInited();
        
        \LibCoverage\LibCoverage::End();
    }
}
