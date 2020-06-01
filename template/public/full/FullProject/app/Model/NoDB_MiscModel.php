<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
use DNMVCS\Core\SingletonEx;

class NoDB_MiscModel
{
    use SingletonEx;
    public function getTime()
    {
        return DATE(DATE_ATOM);
    }
}
