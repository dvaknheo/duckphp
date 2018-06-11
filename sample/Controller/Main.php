<?php
use DNMVCS as DN;

class DnController
{
	public function index()
	{
		$data=array();
		$data['var']=TestService::G()->foo();
		DN\Show($data,'main');
		
	}
	public function i()
	{
		phpinfo();
	}
}
