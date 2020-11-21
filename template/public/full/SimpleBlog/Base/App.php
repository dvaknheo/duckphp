<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace SimpleBlog\Base;

use DuckPhp\DuckPhp;

class App extends DuckPhp
{
    public $componentClassMap = [
            
    ];
    //@override
    public $options = [
        'use_setting_file' => true,
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
        'helper_map' =>  '~\Base\\',

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
