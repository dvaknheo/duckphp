<?php 
namespace DNMVCS;
class SwoolePool
{	
	const MODE_AUTO=0;
	const MODE_PROCESS=1;
	const MODE_COROUTINE=2;
	
	protected static $_instances=[];
	public static function G($object=null)
	{
		if($object){
			self::$_instances[static::class]=$object;
			return $object;
		}
		$me=self::$_instances[static::class]??null;
		if(null===$me){
			$me=new static();
			self::$_instances[static::class]=$me;
		}
		return $me;
	}
	
	public $items;
	public $max_length=10;
	public $timeout=null;
	public function init($max_length=10,$timeout=null,$mode=self::MODE_AUTO)
	{
		$this->max_length=$max_length;
		$this->timeout=$timeout;
		
		if($mode===self::MODE_COROUTINE){
			$this->items = new \Swoole\Coroutine\Channel(1);
		}else if($mode===self::MODE_PROCESS){
			$this->items = new \Swoole\Channel(1);
		}else if($mode===self::MODE_AUTO){
			$cid = \Swoole\Coroutine::getuid();
			if($cid<=0){
				$this->items = new \Swoole\Channel(1);
			}else{
				$this->items = new \Swoole\Coroutine\Channel(1);
			}
		}else{
			throw new Exception('Bad Mode');
		}
	}
	public function getObject($args)
	{
		do{
			if($this->items->stats()['queue_num']==0){
				break;
			}
			list($object,$time)= $this->items->pop();
			if($object===null){break;}
			if($this->timeout!==null){
				$now=time();
				$is_timeout =($now-$time)>$this->timeout?true:false;
				if($is_timeout){
					$this->gcObject($object);
					break;
				}
			}
			return $object;
		}while(true);
		
		$object=$this->createObject($args);
		return $object;
	}
	protected function createObject($args)
	{
		throw new Exception('Impelement Me');
		$object=null;
		return $object;
	}
	protected function gcObject($object)
	{
		//for override;
	}
	public function releaseObject($object)
	{
		if($this->items->stats()['queue_num']>=$this->max_length){
			return;
		}
		//debug_print_backtrace();exit;
		$flag=$this->items->push([$object,time()]);
	}
}