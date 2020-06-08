<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace SimpleBlog\Base;

use DuckPhp\App as SystemApp;

class App extends SystemApp
{
    public $componentClassMap = [
            'M' => 'ModelHelper',
            'V' => 'ViewHelper',
            'C' => 'ControllerHelper',
            'S' => 'ServiceHelper',
    ];
    //@override
    public $options = [
        'ext' => [
            'DuckPhp\\Ext\\Misc' => true,
            'DuckPhp\\Ext\\RouteHookRewrite' => true,
            'SimpleAuth\\Base\\App' => [
                //'plugin_url_prefix'=>'/simpleauth/',
            ],
        ],
        'rewrite_map' => [
            '~article/(\d+)/?(\d+)?' => 'article?id=$1&page=$2',
        ],
    ];
    public function __construct()
    {
        parent::__construct();
        $base_dir=basename(__DIR__);
        //$this->options['path_view'] = $base_dir.'/view';
        //$this->options['path_config'] = $base_dir.'/config';
    }
    protected function onPrepare()
    {
        $path = realpath($this->options['path'].'../SimpleAuth/');
        $this->assignPathNamespace($path, 'SimpleAuth');
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
