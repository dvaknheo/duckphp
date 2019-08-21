<?php
namespace MY\Controller;

use MY\Base\ControllerHelper as C;
use MY\Base\BaseController;
use MY\Service as S;

class Main // extends BaseController
{
    public function index()
    {
        $data=[];
        $data['var']=S\TestService::G()->foo();
        C::Show($data, 'main');
    }
    public function i()
    {
        phpinfo();
    }
}
