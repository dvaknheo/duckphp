<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace UUU\Base;

use DuckPhp\App as DuckPhp_App;

class App extends DuckPhp_App
{
    protected $componentClassMap = [
            'M' => 'ModelHelper',
            'V' => 'ViewHelper',
            'C' => 'ControllerHelper',
            'S' => 'ServiceHelper',
    ];
    public function onInit()
    {
        $this->options['rewrite_map']['~article/(\d+)/?(\d+)?'] = 'article?id=$1&page=$2';
        
        $this->options['ext']['UserSystemDemo\Base\App'] = true;
        
        $path = realpath($this->options['path'].'../../auth/');
        $this->assignPathNamespace($path, 'UserSystemDemo');
        $ret = parent::onInit();
        static::assignRoute([
            '~abc(\d*)' => function () {
                var_dump("OK");
            },
        ]);
        return $ret;
        
        return parent::onInit();
    }
    public function onRun()
    {
        return parent::onRun();
    }
}
