<?php
namespace DNMVCS;

class DNSuperGlobalRouteHook
{
	use DNSingleton;
	public function hook($route)
	{
		SuperGlobal::G(); //for autoload
		$path=DNMVCS::G()->options['path'];
		if(!SuperGlobal::SERVER('DOCUMENT_ROOT')){
			SuperGlobalSERVER::Set('DOCUMENT_ROOT',$path.'www');
		
		}
		if(!SuperGlobal::SERVER('SCRIPT_FILENAME')){
			SuperGlobalSERVER::Set('SCRIPT_FILENAME',$path.'www/index.php');
		}
		$route->script_filename=SuperGlobal::SERVER('SCRIPT_FILENAME')??'';
		$route->document_root=SuperGlobal::SERVER('DOCUMENT_ROOT')??'';
		$route->request_method=SuperGlobal::SERVER('REQUEST_METHOD')??'';
		$route->path_info=SuperGlobal::SERVER('PATH_INFO')??'';
		
		$route->path_info=ltrim($route->path_info,'/');
	}
}