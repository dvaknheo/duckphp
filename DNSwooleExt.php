<?php
namespace DNMVCS;

class DNSwooleExt
{
	use DNSingleton;
	
	protected $old_error_404;
	protected $has_inited=false;
	
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

	public function onSwoole404()
	{
		$options=SwooleHttpServer::G()->options;
		if(!$options['use_http_handler_root']){
			DNMVCS::G()->options['error_404']=$this->old_error_404;
			DNMVCS::G()->onShow404();
			return;
		}
		$class=DNMVCS::class;
		SwooleHttpServer::G()->createCoInstance($class,new $class);
		
		DNMVCS::G(static::G()); //fake object
		
		SwooleHttpServer::G()->throw404(); 
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
		$this->old_error_404=DNMVCS::G()->options['error_404'];
		DNMVCS::G()->options['error_404']=[$this,'onSwoole404'];
		
		$options=SwooleHttpServer::G()->options;
		if($options['use_http_handler_root']){
			// mark
		}
		SwooleHttpServer::G()->http_handler=$server->options['http_handler']=[$this,'runSwoole'];
		SwooleHttpServer::G()->run();
	}
	public function runSwoole()
	{
		$classes=DNMVCS::G()->getDymicClasses();
		SwooleHttpServer::G()->forkMasterInstances($classes);
		
		return DNMVCS::G()->run();
	}
}

