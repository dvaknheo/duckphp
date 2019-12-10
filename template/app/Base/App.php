<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace MY\Base;

use DuckPhp\App as DuckPhp_App;

class App extends DuckPhp_App
{
    public function onInit()
    {
        // your code here
        $ret = parent::onInit();
        // your code here
        return $ret;
    }
    protected function onRun()
    {
        // your code here
        return parent::onRun();
    }
}
