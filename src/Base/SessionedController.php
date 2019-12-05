<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace DuckPhp\Base;

use DuckPhp\Core\App;

class SessionedController
{
    protected $_session_name = 'DNSESSION';
    public function __construct()
    {
        App::G()::session_start(['name' => $this->_session_name]);
    }
}
