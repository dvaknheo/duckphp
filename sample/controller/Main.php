<?php
class Main
{
	public function index()
	{
		$data=array();
		$data['var']=TestService::G()->foo();
		DNView::Show('main',$data);
	}
}