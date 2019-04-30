<?php
namespace DNMVCS;

use DNMVCS\Basic\SingletonEx;
use DNMVCS\Core\App;

trait StrictService
{
    use SingletonEx { G as _ParentG;}
    public static function G($object=null)
    {
        App::G()->checkStrictService();
        return static::_ParentG($object);
    }
}
