<?php
namespace DNMVCS;
class DNSwooleHttpServer extends SwooleHttpServer
{
	const DEFAULT_DN_OPTIONS=[
		'not_empty'=>true,
		
		'use_http_handler_root'=>false,
		'fake_root'=>'public',
		'fake_root_index_file'=>'index.php',
	];
	/////////////////////////
	public function init($options,$server=null)
	{
		parent::init($options,$server);
		SwooleHttpServer::G($this);
		
		return $this;
	}
	protected function onHttpRun($request,$response)
	{
		$classes=DNMVCS::G()->getDymicClasses();
		foreach($classes as $class){
			SwooleCoroutineSingleton::CloneInstance($class);
		}
		return parent::onHttpRun($request,$response);
	}
	public function onShow404()
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

		DNAutoLoader::G(new DNAutoLoader());
		DNMVCS::G(new DNMVCS());
		DNDBManager::G(new DNDBManager());
		DNRoute::G(new DNRoute());
		DNSuperGlobal::G(SwooleSuperGlobal::G());
		DNConfiger::G(new DNConfiger());
		
		$http_handler_root=$this->options['http_handler_basepath'].$this->options['http_handler_root'];
		$http_handler_root=rtrim($http_handler_root,'/').'/';
		$document_root=$this->static_root?:rtrim($http_handler_root,'/');
		
		$request_uri=SwooleSuperGlobal::G()->_SERVER['REQUEST_URI'];
		$path=parse_url($request_uri,PHP_URL_PATH);
		
		$flag=$this->runHttpFile($path,$document_root);
		if(!$flag){
			//Remark Can't Not use in dnmvcs.
			//DNMVCS::G()->onShow404();
		}
		$this->auto_clean_autoload=true;
		
	}
	public function bindDN($dn_options)
	{
		if(!$dn_options){return $this;}
		
		$dn_options['swoole']=$dn_options['swoole']??[];
		$dn_options['swoole']=array_replace_recursive(static::DEFAULT_DN_OPTIONS,$dn_options['swoole']);
		$dn_swoole_options=$dn_options['swoole'];
		
		if($dn_swoole_options['use_http_handler_root']){
			$dn_options['error_404']=[$this,'onShow404'];
		}
		
		$dn=DNMVCS::G()->init($dn_options);
		
		
		
		if(!defined('DN_SWOOLE_SERVER_HANDLER_MODE')){ define('DN_SWOOLE_SERVER_HANDLER_MODE',true); }
		///////////////////////////////
		
		$this->options['http_handler']=$this->http_handler =[$dn,'run'];
		$this->options['http_exception_handler']=$this->http_exception_handler=[$dn,'onException'];
		
		$path=$dn->options['path'];
		
		if($dn_swoole_options['use_http_handler_root']){
			$http_handler_root=$this->options['http_handler_basepath'].$this->options['http_handler_root'];
			$http_handler_root=rtrim($http_handler_root,'/').'/';
			$document_root=$this->static_root?:rtrim($http_handler_root,'/');
		}else{
			$fakeRoot=$dn_swoole_options['fake_root']??'public';
			$document_root=$path.$fakeRoot;
		}
		$fakeIndex=$dn_swoole_options['fake_root_index_file']??'index.php';
		
		$this->document_root=$document_root;  // @override
		$this->script_filename=$document_root.'/'.$fakeIndex; // @override
		
		///////////////////////////////
		return $this;
	}
	public function runDNMVCS()
	{
		//
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
		$classes=$class+$ext_class;
		return $classes;
	}
	public function getNotDymicClass()
	{
		//
	}
	public static function RunWithServer($server_options,$dn_options=[],$server=null)
	{
		return static::G()->init($server_options,$server)->bindDN($dn_options)->run();
	}
}

