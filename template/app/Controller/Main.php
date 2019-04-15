<?php
namespace MY\Controller;

use DNMVCS\DNMVCS as DN;
use MY\Service as S;

class Main
{
    public function index()
    {
        $data=array();
        $data['var']=S\TestService::G()->foo();
        DN::Show($data, 'main');
    }
    public function i()
    {
        phpinfo();
    }
}
