<?php declare(strict_types=1);
namespace DNMVCS\Base;

use DNMVCS\Core\SingletonEx;
use DNMVCS\Core\App;

trait StrictModelTrait
{
    use SingletonEx {
        G as _ParentG;
    }
    public static function G($object=null)
    {
        App::G()->checkStrictModel();
        return static::_ParentG($object);
    }
}
