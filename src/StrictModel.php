<?php
namespace DNMVCS;

trait StrictModel
{
    use DNSingleton { G as _ParentG;}
    public static function G($object=null)
    {
        DNMVCS::G()->checkStrictModel($object??static::class);
        return static::_ParentG($object);
    }
}
