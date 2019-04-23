<?php
namespace DNMVCS;

class SessionController
{
    protected $session_name;
    public function __construct()
    {
        $dn=defined('DNMVCS_CLASS')?DNMVCS_CLASS:DNMVCS::class;
        $dn::session_start(['name'=>$this->session_name]);
    }
}
