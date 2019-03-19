<?php
namespace DNMVCS;
/*4
ISwooleHttpServer:: init run getDynamicClasses ReplaceDefaultSingletonHandler  resetInstances  forkMasterInstances setHttpHandler(!) //getBootInstances?
IDNMVCS:: 			init run getDynamicClasses getBootInstances    //toggleStop404Handler
*/
class DNSwooleExt
{
	use DNSingleton;
	
	protected $has_inited=false;
	protected $with_http_handler_root=false;
	
	public function init($options)
	{
		//for 404 RE in;
		if(get_class(DNMVCS::G())===static::class){
			return $this->initRunningModeDNMVCS($options);
		}
		return $this;
	}
	protected function initRunningModeDNMVCS($options)
	{
		SwooleHttpServer::G()->resetInstances();
		$ret=DNMVCS::G()->init($options);
		return $ret;
	}
	public function onDNMVCSBoot()
	{
		if(PHP_SAPI!=='cli'){ return; }
		
		if($this->has_inited){return;}
		$this->has_inited=true;
		
		$instances=DNMVCS::G()->getBootInstances();
		$server=SwooleHttpServer::G();
		$flag=([get_class($server),'ReplaceDefaultSingletonHandler'])();
		if(!$flag){ return; }
		SwooleHttpServer::G($server);
		
		foreach($instances as $class=>$object){
			$class::G($object);
		}
		static::G($this);
	}
	public function onDNMVCSInit($server_options)
	{
		if(PHP_SAPI!=='cli'){ return; }
		
		SwooleHttpServer::G()->init($server_options,null);
	}
	public function onDNMVCSRunOnce()
	{
		$server=SwooleHttpServer::G();
		
		if($server->with_http_handler_root){		//TODO.
			$this->with_http_handler_root=true;
			DNMVCS::G()->toggleStop404Handler(true);
		}
		$server->http_handler=[$this,'runSwoole'];
		$server->run();
	}
	public function runSwoole()
	{
		$classes=DNMVCS::G()->getDynamicClasses();
		$exclude_classes=SwooleHttpServer::G()->getDynamicClasses();
		SwooleHttpServer::G()->forkMasterInstances($classes,$exclude_classes);
		
		$ret=DNMVCS::G()->run($this->with_http_handler_root);
		if(!$ret && $this->with_http_handler_root){
			SwooleHttpServer::G()->forkMasterInstances([DNMVCS::class]);
			DNMVCS::G(static::G()); //fake object
			return false;
		}
		return true;
	}
}

