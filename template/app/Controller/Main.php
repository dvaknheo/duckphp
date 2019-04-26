<?php
namespace MY\Controller;

use MY\Base\App;
use MY\Base\Controller;
use MY\Service as S;

class Main // extends Controller
{
    public function index()
    {
        $data=[];
        $data['var']=S\TestService::G()->foo();
        App::Show($data, 'main');
    }
    public function i()
    {
        phpinfo();
    }
}
