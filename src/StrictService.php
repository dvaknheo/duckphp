<?php
namespace DNMVCS;

trait StrictService
{
    use DNSingleton { G as _ParentG;}
    public static function G($object=null)
    {
        DNMVCS::G()->checkStrictService($object??static::class);
        return static::_ParentG($object);
    }
}
