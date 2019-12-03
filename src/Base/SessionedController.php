<?php declare(strict_types=1);
namespace DNMVCS\Base;

use DNMVCS\Core\App;

class SessionedController
{
    protected $_session_name='DNSESSION';
    public function __construct()
    {
        App::G()::session_start(['name'=>$this->_session_name]);
    }
}
