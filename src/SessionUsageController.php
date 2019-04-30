<?php
namespace DNMVCS;

use DNMVCS\Core\App;

class SessionUsageController
{
    protected $session_name='DNSESSION';
    public function __construct()
    {
        App::session_start(['name'=>$this->session_name]);
    }
}
