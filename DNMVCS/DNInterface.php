<?php
namespace DNMVCS;

interface IDNAutoLoad
{
	//public static G();
	//public $options=array();
	public function init($options=array());
	public function run();
}
interface IDNRoute
{
	//public $options=array();
	public function _URL($url=null);
	public function _Param();
	public function init($options);
	public function _default404();
	public function set404($callback);
	public function run();
	public function defaltRouteHandle();
	//public function addDefaultRoute($callback)
	//public function defalt_dispath_handle()
	public function assignRoute($key,$callback=null);
}
interface IDNRoute
{
	public function _ExitJson($ret)
	public function _ExitRedirect($url,$only_in_site=true)
	public function _Show($data=array(),$view)
	public function showBlock($view,$data)
	public function init($path)
	public function setBeforeShow($callback)
	public function setViewWrapper($head_file,$foot_file)
	public function assignViewData($key,$value=null)
}
interface DNConfig
{
	//
}
interface DNDB
{
	//
}