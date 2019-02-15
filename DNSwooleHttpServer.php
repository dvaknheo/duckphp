<?php
namespace DNMVCS;

//use SwooleCoroutineSingleton;

class DNSwooleHttpServer
{
	use DNSingleton;
	/////////////////////////
	protected $old_error_404;
	protected $old_before_run_handler;
	protected $lock_init=false;
	protected $has_run_once=false;

	public function init($options,$server=null)
	{
		//for 404 re in;
		if(get_class(DNMVCS::G())===static::class){
			return $this->initRunningModeDNMVCS($options);
		}
		return $this;
	}
	protected function initRunningModeDNMVCS($options)
	{
		$old_super_global=SwooleSuperGlobal::G();
		
		SwooleCoroutineSingleton::CloneAllMasterClasses();
		
		DNSuperGlobal::G(SwooleSuperGlobal::G($old_super_global));
		
		$ret=DNMVCS::G()->init($options);
		return $ret;
	}

	public function onShow404()
	{
		$options=SwooleHttpServer::G()->options;
		if(!$options['use_http_handler_root']){
			DNMVCS::G()->options['error_404']=$this->old_error_404;
			DNMVCS::G()->onShow404();
			return;
		}
		SwooleHttpServer::CloneInstance(DNMVCS::class);
		DNMVCS::G(static::G());
		// ok we passed the fake  object;
		SwooleHttpServer::G()->throw404();
	}
	
	public function beforeInit()
	{
		if(PHP_SAPI!=='cli'){ return; }
		if($this->lock_init){return;}
		$this->lock_init=true;
		
		$dn=DNMVCS::G();
		$autoloader=DNAutoLoader::G();
		$class=static::class;
		$self=$class::G();
		
		SwooleHttpServer::ReplaceDefaultSingletonHandler(); 
		
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
		SwooleHttpServer::G()->http_handler=$server->options['http_handler']=[$this,'run'];
	}
	public function runOnce()
	{
		$this->old_error_404=DNMVCS::G()->options['error_404'];
		DNMVCS::G()->options['error_404']=[$this,'onShow404'];
		SwooleHttpServer::G()->run();
	}
	public function run()
	{
		$classes=DNMVCS::G()->getDymicClasses();
		foreach($classes as $class){
			SwooleHttpServer::CloneInstance($class);
		}
		
		DNSuperGlobal::G(SwooleHttpServer::SG());
		return DNMVCS::G()->run();
	}
}

