<?php
namespace DNMVCS\Base;

use DNMVCS\Base\C;

class SessionedController
{
    protected $_session_name='DNSESSION';
    public function __construct()
    {
        App::G()::session_start(['name'=>$this->_session_name]);
    }
}
