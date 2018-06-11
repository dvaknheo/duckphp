<?php
class DnController
{
	public function index()
	{
		$data=array();
		$data['var']=TestService::G()->foo();
		Show($data,'main');
		
	}
	public function i()
	{
		phpinfo();
	}
}