<?php
namespace DNMVCS;

//use SwooleCoroutineSingleton;

class DNSwooleHttpServer
{
	use DNSingleton;
	/////////////////////////
	protected $old_error_404;
	protected $lock_init=false;
	protected $running_in_swoole=false;

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
		//SwooleCoroutineSingleton::Dump();
		SwooleHttpServer::CloneInstance(DNAutoLoader::class);
		SwooleHttpServer::CloneInstance(DNMVCS::class);
		SwooleHttpServer::CloneInstance(DNConfiger::class);
		SwooleHttpServer::CloneInstance(DNDBManager::class);
		SwooleHttpServer::CloneInstance(DNRoute::class);
		
		
		SwooleHttpServer::CloneInstance(DNSuperGlobal::class); // think more
		

		DNAutoLoader::G(new DNAutoLoader());
		DNMVCS::G(new DNMVCS());
		DNConfiger::G(new DNConfiger());
		DNDBManager::G(new DNDBManager());
		DNRoute::G(new DNRoute());
		
		
		
		//SwooleCoroutineSingleton::ForkClasses('DNMVCS');
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
		SwooleHttpServer::G()->show404();
	}
	public function getDymicClasses()
	{
		$classes=[
			DNExceptionManager::class,
			DNView::class,
			DNRoute::class,
			DNSuperGlobal::class,
			DNRuntimeState::class,
		];
		$ext_class=[];
		foreach($classes as $class){
			if(get_class($class::G())!=$class){$ext_class[]=$class;}
		}
		$classes=$classes + $ext_class;
		return $classes;
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
		
		//clone singleton;
		SwooleHttpServer::ReplaceDefaultSingletonHandler(); 
		
		DNAutoLoader::G($autoloader);
		DNMVCS::G($dn);
		static::G($self);
	}
	public function afterInit()
	{
		$dn_options=DNMVCS::G()->options;
		$server_options=$dn_options['httpd_options'];
		SwooleHttpServer::G()->init($server_options,null);
		$this->bind(DNMVCS::G(),SwooleHttpServer::G());
	}
	protected function bind($dn,$server)
	{
		$this->old_error_404=$dn->options['error_404'];
		$dn->options['error_404']=[$this,'onShow404'];
		
		$server->http_handler=$server->options['http_handler']=[$dn,'run'];
		
		return $this;
	}
	public function beforeRun()
	{
		if(!$this->running_in_swoole){
			$this->running_in_swoole=true;
			
			SwooleHttpServer::G()->run();
			DNMVCS::exit_system(0);
			return true;
		}
		$classes=$this->getDymicClasses();
		foreach($classes as $class){
			SwooleHttpServer::CloneInstance($class);
		}
		return false;
	}
}

