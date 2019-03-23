<?php
namespace DNMVCS;
use Exception;
class DNSwooleExtServerHolder
{
	use DNSingleton;
	
	public static function ReplaceDefaultSingletonHandler(){
	
		throw new Exception("You Need DNMVCS\\SwooleHttpd !");
	}
	
	public function init(){throw new Exception("Impelement Me!");}
	public function run(){throw new Exception("Impelement Me!");}
	public function getDynamicClasses(){throw new Exception("Impelement Me!");}
	public function getBootInstances(){throw new Exception("Impelement Me!");}
	public function resetInstances(){throw new Exception("Impelement Me!");}
	public function forkMasterInstances(){throw new Exception("Impelement Me!");}
}
class DNSwooleExtAppHolder
{
	use DNSingleton;
	public function init(){throw new Exception("Impelement Me!");}
	public function run(){throw new Exception("Impelement Me!");}
	public function getDynamicClasses(){throw new Exception("Impelement Me!");}
	public function getBootInstances(){throw new Exception("Impelement Me!");}
}
class DNSwooleExt
{
	use DNSingleton;
	
	protected $with_http_handler_root=false;
	protected $appClass;
	public static function Server($server=null)
	{
		return DNSwooleExtServerHolder::G($server);
	}
	public static function App($app=null)
	{
		return DNSwooleExtAppHolder::G($app);
	}
	public function setAppClass($class)
	{
		$app=([$class,'G'])();
		
		static::App($app);
		
		$this->appClass=$class;
	}
	public function init($options)
	{
		//for 404 re-in;
		$class=get_class(([$this->appClass,'G'])());
		if($class===static::class){
			return $this->initRunningModeDNMVCS($options);
		}
		return $this;
	}
	protected function initRunningModeDNMVCS($options)
	{
		static::Server()->resetInstances();
		
		$ret=([$this->appClass,'G'])()->init($options);
		return $ret;
	}
	public function onAppBoot($class,$options=[])
	{
		if(PHP_SAPI!=='cli'){ return; }
		$this->setAppClass($class);
		
		$server=static::Server();
		$app=static::App();
		
		$instances=$app->getBootInstances();
		$flag=([get_class($server),'ReplaceDefaultSingletonHandler'])();
		if(!$flag){ return; }
		static::Server($server);
		static::App($app);
		foreach($instances as $class=>$object){
			$class::G($object);
		}
		static::G($this);
	}
	public function onAppInit($server_options)
	{
		if(PHP_SAPI!=='cli'){ return; }
		
		$this->with_http_handler_root=$server_options['with_http_handler_root']??false;
		$server_options['http_handler']=[$this,'runSwoole'];
		
		static::Server()->init($server_options,null);
	}
	public function onAppBeforeRun()
	{
		static::Server()->run();
	}
	public function runSwoole()
	{
		$classes=static::App()->getDynamicClasses();
		$exclude_classes=static::Server()->getDynamicClasses();
		static::Server()->forkMasterInstances($classes,$exclude_classes);
		
		$ret=static::App()->run($this->with_http_handler_root);
		if(!$ret && $this->with_http_handler_root){
			static::Server()->forkMasterInstances(array_keys(static::App()->getBootInstances()));
			([$this->appClass,'G'])(static::G()); //fake object
			return false;
		}
		return true;
	}
}

