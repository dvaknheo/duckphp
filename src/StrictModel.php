<?php
namespace DNMVCS;

trait StrictModel
{
    use DNSingleton { G as _ParentG;}
    public static function G($object=null)
    {
        $dn=defined('DNMVCS_CLASS')?DNMVCS_CLASS:DNMVCS::class;
        $dn->checkStrictModel($object??static::class);
        return static::_ParentG($object);
    }
}
