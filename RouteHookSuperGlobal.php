<?php
namespace DNMVCS;

class RouteHookSuperGlobal
{
	use DNSingleton;
	public $fakeRoot='public';
	public $fakeIndex='index.php';
	public function hook($route)
	{
		$path=DNMVCS::G()->options['path'];
		SuperGlobal::CheckLoad();
		if(!SuperGlobal::SERVER('DOCUMENT_ROOT')){
			SuperGlobal::SetSERVER('DOCUMENT_ROOT',$path.$this->fakeRoot);
		
		}
		if(!SuperGlobal::SERVER('SCRIPT_FILENAME')){
			SuperGlobal::SetSERVER('SCRIPT_FILENAME',$path.$this->fakeRoot.'/'.$this->fakeIndex);
		}
		$route->script_filename=SuperGlobal::SERVER('SCRIPT_FILENAME')??'';
		$route->document_root=SuperGlobal::SERVER('DOCUMENT_ROOT')??'';
		$route->request_method=SuperGlobal::SERVER('REQUEST_METHOD')??'';
		$route->path_info=SuperGlobal::SERVER('PATH_INFO')??'';
		
		$route->path_info=ltrim($route->path_info,'/');
	}
}