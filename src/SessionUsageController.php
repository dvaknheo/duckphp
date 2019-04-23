<?php
namespace DNMVCS;

class SessionUsageController
{
    protected $session_name='DNSESSION';
    public function __construct()
    {
        $dn=defined('DNMVCS_CLASS')?DNMVCS_CLASS:DNMVCS::class;
        $dn::session_start(['name'=>$this->session_name]);
    }
}
