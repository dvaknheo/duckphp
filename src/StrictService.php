<?php
namespace DNMVCS;

trait StrictService
{
    use DNSingleton { G as _ParentG;}
    public static function G($object=null)
    {
        $dn=defined('DNMVCS_CLASS')?DNMVCS_CLASS:DNMVCS::class;
        $dn::G()->checkStrictService($object??static::class);
        return static::_ParentG($object);
    }
}
