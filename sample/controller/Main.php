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
}