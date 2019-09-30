<?php
namespace DNMVCS\Base;

use DNMVCS\Core\SingletonEx;
use DNMVCS\Core\App;

trait StrictServiceTrait
{
    use SingletonEx {
        G as _ParentG;
    }
    public static function G($object=null)
    {
        App::G()->checkStrictService(static::class);
        return static::_ParentG($object);
    }
}
