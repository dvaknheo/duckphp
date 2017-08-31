<?php
class Main
{
	public function index()
	{
		$data=TestService::G()->foo();
		var_dump($data);
	}
}