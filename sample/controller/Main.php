<?php
class DnAction
{
	public function index()
	{
		$data=array();
		$data['var']=TestService::G()->foo();
		DNView::Show('main',$data);
		
	}
	public function i()
	{
		phpinfo();
	}
}