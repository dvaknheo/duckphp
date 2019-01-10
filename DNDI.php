<?php
namespace DNMVCS;

trait DNDI
{
	protected $_di_container;
	public static function DI($name,$object=null)
	{
		return static::G()->_DI($name,$object);
	}
	public function _DI($name,$object=null)
	{
		if(null===$object){
			return $this->_di_container[$name];
		}
		$this->_di_container[$name]=$object;
		return $object;
	}
}