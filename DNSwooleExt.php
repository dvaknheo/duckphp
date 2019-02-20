<?php
namespace DNMVCS;

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
		
		$flag=SwooleHttpServer::ReplaceDefaultSingletonHandler(); 
		if(!$flag){ return; }
		
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
		
		if($server->with_http_handler_root){
			$this->with_http_handler_root=true;
			DNMVCS::G()->options['error_404']=function(){};
		}
		$server->http_handler=[$this,'runSwoole'];
		$server->run();
	}
	public function runSwoole()
	{
		$classes=DNMVCS::G()->getDymicClasses();
		SwooleHttpServer::G()->forkMasterInstances($classes);
		
		$ret=DNMVCS::G()->run();
		if(!$ret && $this->with_http_handler_root){
			$class=DNMVCS::class;
			SwooleHttpServer::G()->createCoInstance($class,new $class);
			DNMVCS::G(static::G()); //fake object
		}
		return $ret;
	}
}

