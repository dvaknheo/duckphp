<?php
namespace DNMVCS;
class DBConnectPoolProxy
{
	use DNSingleton;
	
	public $tag_write='0';
	public $tag_read='1';
	
	protected $db_create_handler;
	protected $db_close_handler;
	protected $db_queue_write;
	protected $db_queue_write_time;
	protected $db_queue_read;
	protected $db_queue_read_time;
	public $max_length=100;
	public $timeout=5;
	public function __construct()
	{
		$this->db_queue_write=new \SplQueue();
		$this->db_queue_write_time=new \SplQueue();
		$this->db_queue_read=new \SplQueue();
		$this->db_queue_read_time=new \SplQueue();
	}
	public function init($max_length=10,$timeout=5)
	{
		$this->max_length=$max_length;
		$this->timeout=$timeout;
		return $this;
	}
	public function setDBHandler($db_create_handler,$db_close_handler=null)
	{
		$this->db_create_handler=$db_create_handler;
		$this->db_close_handler=$db_close_handler;
	}
	protected function getObject($queue,$queue_time,$db_config,$tag)
	{
		if($queue->isEmpty()){
			return ($this->db_create_handler)($db_config,$tag);
		}
		$db=$queue->shift();
		$time=$queue_time->shift();
		$now=time();
		$is_timeout =($now-$time)>$this->timeout?true:false;
		if($is_timeout){
			($this->db_close_handler)($db,$tag);
			return ($this->db_create_handler)($db_config,$tag);
		}
		return $db;
		
	}
	protected function reuseObject($queue,$queue_time,$db)
	{
		if(count($queue)>=$this->max_length){
			($this->db_close_handler)($db,$tag);
			return;
		}
		$time=time();
		$queue->push($db);
		$queue_time->push($time);
	}
	public function onCreate($db_config,$tag)
	{
		if($tag!=$this->tag_write){
			return $this->getObject($this->db_queue_write,$this->db_queue_write_time,$db_config,$tag);
		}else{
			return $this->getObject($this->db_queue_read,$this->db_queue_read_time,$db_config,$tag);
		}
	}
	public function onClose($db,$tag)
	{
		if($tag!=$this->tag_write){
			return $this->reuseObject($this->db_queue_write,$this->db_queue_write_time,$db);
		}else{
			return $this->reuseObject($this->db_queue_read,$this->db_queue_read_time,$db);
		}
	}
}
class DNSwooleHttpServer extends SwooleHttpServer
{
		const DEFAULT_DN_OPTIONS=[
			'not_empty'=>true,
			'db_reuse_size'=>0,
			'db_reuse_timeout'=>5,
			'use_http_handler_root'=>false,
		];
	/////////////////////////
	public function init($options,$server=null)
	{
		parent::init($options,$server);
		SwooleHttpServer::G($this);
		
		return $this;
	}
	public function onShow404()
	{
		SwooleCoroutineSingleton::CloneInstance(DNMVCS::class);
		
		$http_handler_root=$this->options['http_handler_basepath'].$this->options['http_handler_root'];
		$http_handler_root=rtrim($http_handler_root,'/').'/';
		$document_root=$this->static_root?:rtrim($http_handler_root,'/');
		
		
		$request_uri=DNMVCS::SG()->_SERVER['REQUEST_URI'];
		$path=parse_url($request_uri,PHP_URL_PATH);
		
		$flag=$this->runHttpFile($path,$document_root);
		if(!$flag){
			DNMVCS::G()->onShow404();
		}
		
	}
	public function bindDN($dn_options)
	{
		if(!$dn_options){return $this;}
		
		$dn_options['swoole']=$dn_options['swoole']??[];
		$dn_options['swoole']=array_replace_recursive(static::DEFAULT_DN_OPTIONS,$dn_options['swoole']);
		$dn_swoole_options=$dn_options['swoole'];
		
		DNSuperGlobal::G(SwooleSuperGlobal::G());
		$dn=DNMVCS::G()->init($dn_options);
		///////////////////////////////
		
		$this->options['http_handler']=$this->http_handler =[$dn,'run'];
		$this->options['http_exception_handler']=$this->http_exception_handler=[$dn,'onException'];
		
		$db_reuse_size=$dn_swoole_options['db_reuse_size']??0;
		if($db_reuse_size){
			$db_reuse_timeout=$dn_swoole_options['db_reuse_timeout']??5;
			$dbm=DNDBManager::G();
			DBConnectPoolProxy::G()->init($db_reuse_size,$db_reuse_timeout)->setDBHandler($dbm->db_create_handler,$dbm->db_close_handler);
			$dn->setDBHandler([DBConnectPoolProxy::G(),'onCreate'],[DBConnectPoolProxy::G(),'onClose']);
		}		
		if($dn_swoole_options['use_http_handler_root']){
			DNRoute::G()->set404([$this,'onShow404']);
		}
		DNMVCS::G()->header_handler=[static::class,'header'];
		DNMVCS::G()->cookie_handler=[static::class,'setcookie'];
		
		
		$dn->setBeforeRunHandler(function($dn){
			SwooleCoroutineSingleton::CloneInstance(DNExceptionManager::class);
			// SwooleCoroutineSingleton::CloneInstance(DNConfig::class);
			SwooleCoroutineSingleton::CloneInstance(DNView::class);
			SwooleCoroutineSingleton::CloneInstance(DNRoute::class);
			SwooleCoroutineSingleton::CloneInstance(DNRuntimeState::class);
			//SwooleCoroutineSingleton::CloneInstance(DNDBManager::class);
			SwooleCoroutineSingleton::CloneInstance(DNSuperGlobal::class);
			
			//SwooleSuperGlobal::G()->init();
			DNSuperGlobal::G(SwooleSuperGlobal::G());

			$fakeRoot='public';
			$fakeIndex='index.php';
			$path=DNMVCS::G()->options['path'];
			if(!isset(DNSuperGlobal::G()->_SERVER['DOCUMENT_ROOT'])){
				DNSuperGlobal::G()->_SERVER['DOCUMENT_ROOT']=$path.$fakeRoot;
			
			}
			if(!isset(DNSuperGlobal::G()->_SERVER['SCRIPT_FILENAME'])){
				DNSuperGlobal::G()->_SERVER['SCRIPT_FILENAME']=$path.$fakeRoot.'/'.$fakeIndex;
			}
		});
		
		return $this;
	}
	public function run()
	{
		if(!defined('DN_SWOOLE_SERVER_RUNNING')){ define('DN_SWOOLE_SERVER_RUNNING',true); }
		fwrite(STDOUT,get_class($this)." run at ".DATE(DATE_ATOM)." ...\n");
		$t=$this->server->start();
		fwrite(STDOUT,get_class($this)." run end ".DATE(DATE_ATOM)." ...\n");
	}
	public static function RunWithServer($server_options,$dn_options=[],$server=null)
	{
		return static::G()->init($server_options,$server)->bindDN($dn_options)->run();
	}
}

