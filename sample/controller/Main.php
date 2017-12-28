<?php
class Main
{
	public function index()
	{
		$data=array();
		$data['var']=TestService::G()->foo();
		
		$xx=CC;
		DNView::Show('main',$data);
		
	}
	public function foo()
	{
		$ret=TestService::G()->insert();
		var_dump($ret);
	}
	public function login()
	{
	}
	public function logout()
	{
	}
	public function reg()
	{
		
	}
	public function do_reg()
	{
	}
	public function do_login()
	{
	}
}