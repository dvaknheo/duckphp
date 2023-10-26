<?php
namespace tests\DuckPhp\Core;

use DuckPhp\Component\Configer;

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


        Configer::G(new Configer)->init($options);
       // Configer::G()->_Config('override',null, []);
        Configer::G()->_Config('NotExits',null, []);
        \LibCoverage\LibCoverage::End();
    }
}
