<?php
use DNMVCS\Basic\SingletonEx;

class NoDB_MiscModel
{
    use SingletonEx;
    public function getTime()
    {
        return DATE(DATE_ATOM);
    }
}