<?php
namespace DNMVCS;

use DNMVCS\Basic\SingletonEx;
use DNMVCS\Core\App;

trait StrictModel
{
    use SingletonEx { G as _ParentG;}
    public static function G($object=null)
    {
        App::G()->checkStrictModel();
        return static::_ParentG($object);
    }
}
