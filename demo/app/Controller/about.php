<?php
namespace MY\Controller;
use DNMVCS\DNMVCS as DN;
use MY\Service as S;
use MY\Service\TestService;
class about
{
    public function foo()
    {
        $data=[];
        $data['var']=TestService::G()->foo();
        \DNMVCS\DNMVCS::Show($data);
    }
	
	public function index()
    {
        var_dump("hhhhhhhhhhhhhhhhhh",date(DATE_ATOM));
		$data=[];
        \DNMVCS\DNMVCS::Show($data);
    }
}