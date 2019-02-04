<?php
namespace DNMVCS;
class DNSwooleHttpServer
{
	use DNSingleton;
	/////////////////////////
	public function init($options,$server=null)
	{
		if(get_class(DNMVCS::G())==static::class){
			return initRunningModeDNMVCS($options);
		}
		return $this;
	}
	public function initRunningModeDNMVCS($options)
	{
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
		echo "404111";
		return;
		
		SwooleCoroutineSingleton::CloneInstance(DNMVCS::class);
		DNMVCS::G(static::G());
		
		//throw new SwooleException("404 Not Found!",404);
	}
	public function bind($dn,$server)
	{
		$server->http_handler=$server->options['http_handler']=[$this,'runDNMVCS'];
		$server->http_exception_handler=$server->options['http_exception_handler']=[$this,'onDNMVCSException'];
		return $this;
	}
	public function runDNMVCS()
	{
		$classes=$this->getDymicClasses();
		foreach($classes as $class){
			SwooleCoroutineSingleton::CloneInstance($class);
		}
		$flag=DNMVCS::G()->run();
		DNMVCS::G()->options['error_404']=[$this,'onShow404'];
		if(!$flag){
			//404 ,so what?
			//if use_root
			// ok,we push fake dnmvcs
			//throw new 404 exception;
			// then include file mode ;
		}
	}
	public function onDNMVCSException($ex)
	{
		//
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
		SwooleHttpServer::G()->init($server_options,$server);
		$dn=DNMVCS::G()->init($dn_options);
		//DNMVCS::G($dn);
		SwooleCoroutineSingleton::Dump();
		if($dn_options){
			static::G()->bind(DNMVCS::G(),SwooleHttpServer::G());
		}
		SwooleHttpServer::G()->run();
		//return static::G()->init($server_options,$server)->bindDN($dn_options)->run();
	}
}

