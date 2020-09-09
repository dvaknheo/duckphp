<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
use DuckPhp\SingletonEx\SingletonEx;

class MiscService
{
    use SingletonEx;
    public function foo()
    {
        //TODO log something.
        $time = NoDB_MiscModel::G()->getTime();
        $ret = 'Now is '.$time;
        return $ret;
    }
}
