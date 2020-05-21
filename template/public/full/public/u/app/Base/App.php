<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace UUU\Base;

use DuckPhp\App as DuckPhp_App;

class App extends DuckPhp_App
{
    public $componentClassMap = [
            'M' => 'ModelHelper',
            'V' => 'ViewHelper',
            'C' => 'ControllerHelper',
            'S' => 'ServiceHelper',
    ];
    public function onPrepare()
    {
        $this->options['rewrite_map']['~article/(\d+)/?(\d+)?'] = 'article?id=$1&page=$2';
        
        $this->options['ext']['UserSystemDemo\Base\App'] = true;
        $this->options['ext']['DuckPhp\\Ext\\Misc'] = true;
        $this->options['ext']['DuckPhp\\Ext\\RouteHookRewrite'] = true;
        
        $path = realpath($this->options['path'].'../../auth/');
        $this->assignPathNamespace($path, 'UserSystemDemo');
    }
    public function onInit()
    {
        static::assignRoute([
            '^abc(\d*)' => function () {
                var_dump("OK");
            },
        ]);
    }
    public function onRun()
    {
    }
}
