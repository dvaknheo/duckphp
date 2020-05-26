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
    //@override
    protected $options_project = [
        'path_view' => 'app/view',
        'path_config' => 'app/config',
        
        'ext' => [
            'UserSystemDemo\\Base\\App' => true,
            'DuckPhp\\Ext\\Misc' => true,
            'DuckPhp\\Ext\\RouteHookRewrite' => true,
        ],
        'rewrite_map' => [
            '~article/(\d+)/?(\d+)?' => 'article?id=$1&page=$2',
        ],
    ];
    protected function onPrepare()
    {
        $path = realpath($this->options['path'].'../../auth/');
        $this->assignPathNamespace($path, 'UserSystemDemo');
    }
    protected function onInit()
    {
        static::assignRoute([
            '^abc(\d*)' => function () {
                var_dump("OK");
            },
        ]);
    }
}
