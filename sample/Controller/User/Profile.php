<?php
class DnController 
{
	public function index($p='')
	{
		var_dump($p);
		var_dump(DnRoute::Param());
		echo "hello index";
	}
	public function test()
	{
		
		var_dump($_SERVER['PATH_INFO']);
		var_dump(URL('ABC'));exit;
		var_dump($_SERVER['PATH_INFO']);
		echo (http_build_query($_GET));
	
		//phpinfo();
	}
}