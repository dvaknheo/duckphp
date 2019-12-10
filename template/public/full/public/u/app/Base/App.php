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
        $this->assignRewrite([
            '~article/(\d+)/?(\d+)?' => 'article?id=$1&page=$2',
        ]);
        
        $this->assignRoute([
            '~abc(\d*)' => function () {
                var_dump(App::Parameters());
            },
        ]);
        return parent::onInit();
    }
    public function onRun()
    {
        return parent::onRun();
    }
}
