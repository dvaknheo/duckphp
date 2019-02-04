<?php
namespace DNMVCS;
class DNSwooleHttpServer
{
	use DNSingleton;
	/////////////////////////
	protected $old_error_404;
	
	public function init($options,$server=null)
	{
		if(get_class(DNMVCS::G())===static::class){
			return $this->initRunningModeDNMVCS($options);
		}
		return $this;
	}
	public function initRunningModeDNMVCS($options)
	{
var_dump("failing!");exit;
		SwooleCoroutineSingleton::CloneInstance(DNConfiger::class);
		SwooleCoroutineSingleton::CloneInstance(DNAutoLoader::class);
		SwooleCoroutineSingleton::CloneInstance(DNMVCS::class);
		SwooleCoroutineSingleton::CloneInstance(DNRoute::class);
		SwooleCoroutineSingleton::CloneInstance(DNDBManager::class);
		SwooleCoroutineSingleton::CloneInstance(DNSuperGlobal::class);
		
		$class=get_class(DNMVCS::G());
		SwooleCoroutineSingleton::CloneInstance($class);
		$class::G(new $class());

		DNConfiger::G(new DNConfiger());
		DNAutoLoader::G(new DNAutoLoader());
		
		DNDBManager::G(new DNDBManager());
		DNRoute::G(new DNRoute());
		DNSuperGlobal::G(SwooleSuperGlobal::G());
	}

	public function onShow404()
	{
		$options=SwooleHttpServer::G()->options;
		if(!$options['use_http_handler_root']){
			DNMVCS::G()->options['error_404']=$this->old_error_404;
			DNMVCS::G()->onShow404();
			return;
		}
		SwooleCoroutineSingleton::CloneInstance(DNMVCS::class);
		DNMVCS::G(static::G());
		
		list($path,$document_root)=SwooleHttpServer::G()->prepareRootMode();
		$flag=SwooleHttpServer::G()->runHttpFile($path,$document_root);
		//throw new SwooleException("404 Not Found!",404);
	}
	public function bind($dn,$server)
	{
		$server->http_handler=$server->options['http_handler']=[$this,'runDNMVCS'];
		$server->http_exception_handler=$server->options['http_exception_handler']=[$this,'onDNMVCSException'];
		$this->old_error_404=$dn->options['error_404'];
		$dn->options['error_404']=[$this,'onShow404'];
		return $this;
	}
	public function runDNMVCS()
	{
		$classes=$this->getDymicClasses();
		foreach($classes as $class){
			SwooleCoroutineSingleton::CloneInstance($class);
		}
		DNMVCS::G()->run();
	}
	public function onDNMVCSException($ex)
	{
var_dump("???".__FILE__ . __LINE__);

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
	public function getStaticClass()
	{
		//
	}
	public static function RunWithServer($server_options,$dn_options=[],$server=null)
	{
		//if(!$dn_options){return $this;}
		// DNAutoLoader=
		//SwooleHttpServer::G() ;
		$t=new SwooleHttpServer();
		$t->init($server_options,$server);
		$dn=DNMVCS::G()->init($dn_options);
		DNMVCS::G($dn); // Remark , keep this
		
		
		if($dn_options){
			static::G()->bind(DNMVCS::G(),SwooleHttpServer::G());
		}
		SwooleCoroutineSingleton::Dump();
		SwooleHttpServer::G()->run();
	}
}

