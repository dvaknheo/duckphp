<?php
namespace DNMVCS\Base;

use DNMVCS\Basic\SingletonEx;
use DNMVCS\Core\App;

trait StrictServiceTrait
{
    use SingletonEx { G as _ParentG;}
    public static function G($object=null)
    {
        App::G()->checkStrictService();
        return static::_ParentG($object);
    }
}
