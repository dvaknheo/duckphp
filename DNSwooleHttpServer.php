<?php
namespace DNMVCS;

class DNSwooleHttpServer
{
	use DNSingleton;
	
	protected $old_error_404;
	protected $lock_init=false;
	protected $has_run_once=false;

	public function init($options)
	{
		//for 404 re in;
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
		
		DNMVCS::G(static::G()); //fake object ,
		
		SwooleHttpServer::G()->throw404(); 
	}
	
	public function beforeInit()
	{
		if(PHP_SAPI!=='cli'){ return; }
		if($this->lock_init){return;}
		$this->lock_init=true;
		
		$dn=DNMVCS::G();
		$autoloader=DNAutoLoader::G();   // TODO remove referer
		$class=static::class;
		$self=$class::G();
		
		$flag=SwooleHttpServer::ReplaceDefaultSingletonHandler(); 
		if(!$flag){ return; }
		
		DNAutoLoader::G($autoloader);
		DNMVCS::G($dn);
		static::G($self);
	}
	public function afterInit()
	{
		if(PHP_SAPI!=='cli'){ return; }
		
		$dn_options=DNMVCS::G()->options;
		$server_options=$dn_options['swoole'];
		SwooleHttpServer::G()->init($server_options,null);
	}
	public function runOnce()
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

