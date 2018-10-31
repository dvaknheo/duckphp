<?php
namespace DNMVCS;

class DNSuperGlobalRouteHook
{
	use DNSingleton;
	public function hook($route)
	{
		SuperGlobal::Init();
		$path=DNMVCS::G()->options['path'];
		if(!SuperGlobal\SERVER::Get('DOCUMENT_ROOT')){
			SuperGlobal\SERVER::Set('DOCUMENT_ROOT',$path.'www');
		
		}
		if(!SuperGlobal\SERVER::Get('SCRIPT_FILENAME')){
			SuperGlobal\SERVER::Set('SCRIPT_FILENAME',$path.'www/index.php');
		}
		$route->script_filename=SuperGlobal\SERVER::Get('SCRIPT_FILENAME')??'';
		$route->document_root=SuperGlobal\SERVER::Get('DOCUMENT_ROOT')??'';
		$route->request_method=SuperGlobal\SERVER::Get('REQUEST_METHOD')??'';
		$route->path_info=SuperGlobal\SERVER::Get('PATH_INFO')??'';
		
		$route->path_info=ltrim($route->path_info,'/');
	}
}