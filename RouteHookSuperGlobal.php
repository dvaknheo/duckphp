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
		if(!isset(SuperGlobal::G()->_SERVER['DOCUMENT_ROOT'])){
			SuperGlobal::G()->_SERVER['DOCUMENT_ROOT']=$path.$this->fakeRoot;
		
		}
		if(!isset(SuperGlobal::G()->_SERVER['SCRIPT_FILENAME'])){
			SuperGlobal::G()->_SERVER['SCRIPT_FILENAME']=$path.$this->fakeRoot.'/'.$this->fakeIndex;
		}
		$route->script_filename=SuperGlobal::G()->_SERVER['SCRIPT_FILENAME']??'';
		$route->document_root=SuperGlobal::G()->_SERVER['DOCUMENT_ROOT']??'';
		$route->request_method=SuperGlobal::G()->_SERVER['REQUEST_METHOD']??'';
		$route->path_info=SuperGlobal::G()->_SERVER['PATH_INFO']??'';
		
		$route->path_info=ltrim($route->path_info,'/');
	}
}