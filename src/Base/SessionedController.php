<?php
namespace DNMVCS;

use DNMVCS\Core\App;

class SessionedController
{
    protected $_session_name='DNSESSION';
    public function __construct()
    {
        App::session_start(['name'=>$this->_session_name]);
    }
}
