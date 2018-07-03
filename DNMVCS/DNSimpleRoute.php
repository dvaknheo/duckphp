<?php
namespace DNMVCS;

class DNSimpleRoute extends \DNMVCS\DNRoute
{
	public $options;

	public $route_key='_r';
	
	public function _URL($url=null)
	{
		$path=parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);
		if($url===null || $url==='' || $url==='/'){return $path;}
		$url='/'.ltrim($url,'/');
		$c=parse_url($url,PHP_URL_PATH);
		$q=parse_url($url,PHP_URL_QUERY);
		
		$q=$q?'&'.$q:''; //TODO if this->route_key= 
		$url=$path.'?'.$this->route_key.'='.$c.$q;
		return $url;
	}
	public function init($options)
	{
		parent::init($options);
		
		$this->route_key=isset($options['route_key'])?$options['route_key']:$this->route_key;
		
		$path_info=isset($_GET[$this->route_key])?$_GET[$this->route_key]:'';
		$path_info='/'.ltrim($path_info,'/');
		$this->path_info=$path_info;
	}
}

