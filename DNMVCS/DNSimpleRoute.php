<?php
namespace DNMVCS;

class DNSimpleRoute extends \DNMVCS\DNRoute
{
	public $options;
	
	public $namespace='MY';
	protected $default_class='DNController';
	
	protected $default_controller='Main';
	protected $default_method='index';
	public $enable_param=true;
	public $enable_simple_mode=true;
	
	public $calling_path='';
	public $calling_class='';
	public $calling_method='';
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
		//http_build_query($q);
		
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

