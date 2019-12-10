<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace MY\Controller;

use MY\Base\Helper\ControllerHelper as C;
use MY\Service\TestService;

class about
{
    public function foo()
    {
        $data = [];
        $data['var'] = TestService::G()->foo();
        C::Show($data);
    }
    
    public function index()
    {
        var_dump("hhhhhhhhhhhhhhhhhh", date(DATE_ATOM));
        $data = [];
        C::Show($data);
    }
}
