<?php
namespace DNMVCS;
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
	public function initRunningModeDNMVCS($options)
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
		
		$ret=DNMVCS::G()->init($options);
		
		//SwooleCoroutineSingleton::ForkClasses('DNMVCS');
		
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
		//$this->saved_dn=DNMVCS::G();
		DNMVCS::G(static::G()); // ok we passed the fake  object;
		
		list($path,$document_root)=SwooleHttpServer::G()->prepareRootMode();
		$flag=SwooleHttpServer::G()->runHttpFile($path,$document_root);
	}

	public function onDNMVCSException($ex)
	{
//fwrite(STDERR,"-------------------".get_class($ex).":".$ex->getMessage().":".$ex->getCode()."\n");
//fwrite(STDERR,"-------------------".$ex->getTraceAsString()."\n");

		return DNMVCS::G()->onException($ex);
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
		$server->http_exception_handler=$server->options['http_exception_handler']=[$this,'onDNMVCSException'];
		
		return $this;
	}
	public function beforeRun()
	{
		if(!$this->running_in_swoole){
			$this->running_in_swoole=true;
			
			SwooleHttpServer::G()->run();
			// halt. not return;
			return true;
		}
		$classes=$this->getDymicClasses();
		foreach($classes as $class){
			SwooleHttpServer::CloneInstance($class);
		}
		return false;
	}
	public static function RunWithServer($server_options,$dn_options=[],$server=null)
	{
		$server_options['server']=$server;
		$dn_options['httpd_options']=$server_options;
		return DNMVCS::G()->init($dn_options)->run();
	}
}

