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
            'setting_file_enable'=>true,
            'use_env_file'=>true,
        ];
        Configer::G()->init($options);
        Configer::G()->init($options);
        $key="key";
        Configer::G()->_Setting($key);
        Configer::G()->_Setting($key);
        Configer::G()->_Config($key, null,$file_basename='config');
        Configer::G()->_Config(null, [],$file_basename='config');

        $options=[
            'path'=>dirname($path_config),
            'path_config'=>basename($path_config),
        ];

        
        //Configer::G()->options['config_ext_file_map']=['X/a'=>$path_config.'/for_assign.php'];
        //Configer::G()->_Config(null, [],'X/a');
        //Configer::G()->isInited();
        
        $options['setting_file_enable'] =true;
        $options['setting_file_ignore_exists'] =true;
        Configer::G(new Configer)->init($options);
        Configer::G()->_Setting($key);
                $options['setting_file_ignore_exists'] =false;
        try{
            Configer::G(new Configer)->init($options);
        }catch(\Throwable $ex){
            //
        }
        
        
        \LibCoverage\LibCoverage::End();
    }
}
