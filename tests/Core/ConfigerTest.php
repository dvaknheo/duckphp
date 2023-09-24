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
        Configer::G()->_Config('config', $key, null,);
        Configer::G()->_Config('config', null, []);

        $options=[
            'path'=>dirname($path_config),
            'path_config'=>basename($path_config),
        ];
        
        $options['setting_file']='noooooooo.php';
        $options['setting_file_enable'] =true;
        $options['setting_file_ignore_exists'] =true;
        Configer::G(new Configer)->init($options);
        Configer::G()->_Setting($key);
            $options['setting_file_ignore_exists'] = false;
        try{
            Configer::G(new Configer)->init($options);
        }catch(\Throwable $ex){
            //
        }
        $options['setting_file']='setting.php';
        $options['setting_file_ignore_exists'] = true;
        $options['path_config_override_from'] = $path_config.'overrided/';;
        $options['setting_file_enable'] = true;
        $options['setting_file'] = $path_config.'setting.php';
        Configer::G(new Configer)->init($options);
        Configer::G()->_Config('override',null, []);
        Configer::G()->_Config('NotExits',null, []);
        \LibCoverage\LibCoverage::End();
    }
}
