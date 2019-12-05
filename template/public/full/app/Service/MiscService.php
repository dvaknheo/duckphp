<?php
use DNMVCS\Basic\SingletonEx;

class MiscService
{
    use SingletonEx;
    public function foo()
    {
        //TODO log something.
        $time=NoDB_MiscModel::G()->getTime();
        $ret='Now is '.$time;
        return $ret;
    }
}