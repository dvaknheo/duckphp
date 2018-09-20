<?php 
namespace DNMVCS;
class SwoolePool
{
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
	public $create_handler=null;

	public function init($create_handler,$max_length=10,$timeout=null,$no_co=false)
	{
		$this->max_length=$max_length;
		$this->create_handler=$create_handler;
		$this->timeout=$timeout;
		$cid = \Swoole\Coroutine::getuid();
		if($no_co || $cid<=0){
			$this->items = new \Swoole\Channel(1);
		}else{
			$this->items = new \Swoole\Coroutine\Channel(1);
		}
	}
	
	public function getObject()
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
					break;
				}
			}
			return $object;
		}while(true);
		
		$object=$this->createObject();
		return $object;
	}
	protected function createObject()
	{
		$object=($this->create_handler)();
		return $object;
	}
	public function releaseObject($object)
	{
		if($this->items->stats()['queue_num']>=$this->max_length){
			return;
		}
		$flag=$this->items->push([$object,time()]);
	}
}