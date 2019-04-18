<?php
namespace MY\Controller;

use DNMVCS\DNMVCS as DN;
use MY\Base\Controller;
use MY\Service as S;

class Main // extends Controller
{
    public function index()
    {
        $data=[];
        $data['var']=S\TestService::G()->foo();
        DN::Show($data, 'main');
    }
    public function i()
    {
        phpinfo();
    }
}
