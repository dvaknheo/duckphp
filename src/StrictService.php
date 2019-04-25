<?php
namespace DNMVCS;

use DNMVCS\Basic\SingletonEx;

trait StrictService
{
    use SingletonEx { G as _ParentG;}
    public static function G($object=null)
    {
        $dn=defined('DNMVCS_CLASS')?DNMVCS_CLASS:DNMVCS::class;
        $dn::G()->checkStrictService();
        return static::_ParentG($object);
    }
}
