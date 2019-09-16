<?php
namespace MY\Controller;

use MY\Base\Helper\ControllerHelper as C;
use MY\Base\BaseController;
use MY\Service\TestService;

class Main // extends BaseController
{
    public function index()
    {
        $data=[];
        $data['var']=TestService::G()->foo();
        C::Show($data, 'main');
    }
    public function i()
    {
        phpinfo();
    }
}
