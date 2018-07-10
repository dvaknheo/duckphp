<?php
namespace DNMVCS;

interface IDNAutoLoad
{
	//public $path;
	//public $options=[];
	public function init($options=array());
	public function isInited();
	public function run();
}
interface IDNRoute
{
	//public $options;
	//public $enable_param=true;
	//public $enable_simple_mode=true;
	//public $calling_path='';
	//public $calling_class='';
	//public $calling_method='';
	public function _URL($url=null);
	public function _Parameters();
	public function init($options);
	public function _default404();
	public function set404($callback);
	public function run();
	public function defaltRouteHandle();
	public function addDefaultRoute($callback);
	public function defalt_dispath_handle();
	public function assignRoute($key,$callback=null);
}
interface IDNView
{
	//public $data=array();
	//public $onBeforeShow=null;
	//public $path;
	//public $isDev=false;
	public function _ExitJson($ret);
	public function _ExitRedirect($url,$only_in_site=true);
	public function _Show($data=array(),$view);
	public function init($path);
	public function setBeforeShow($callback);
	public function setViewWrapper($head_file,$foot_file);
	public function showBlock($view,$data);
	public function assignViewData($key,$value=null);
}
interface IDNConfig
{
	public function init($path,$path_common=null);
	public function _Setting($key);
	public function _Get($key,$file_basename='config');
	public function _LoadConfig($file_basename='config');
}
interface IDNDB
{
	//public $pdo;
	public function init($config);
	public function close();
	public function quote($string);
	public function fetchAll($sql);
	public function fetch($sql);
	public function fetchColumn($sql);
	public function execQuick($sql);
	public function rowCount();
}
interface IDNDBInstaller
{
	public static function CreateDBInstance();
}
interface IDNExceptionManager
{
	//public static $is_handeling;
	//public static $OnErrorException;
	//public static $OnException;
	//public static $OnError;
	//public static $OnDevError;
	//public static $SpecailExceptionMap=array();
	public static function HandelAllException($OnErrorException,$OnException);
	public static function SetSpecialErrorCallback($class,$callback);
	public static function SetException($OnException);
	public static function ManageException($ex);
	public static function HandelAllError($OnError,$OnDevError);
	public function onErrorHandler($errno, $errstr, $errfile, $errline);
}
interface IDNDBManager
{
	//public $db=null;
	//public $db_r=null;
	public function _DB();
	public function _DB_W();
	public function _DB_R();
	public function closeAllDB();
}
interface IDNMVCS
{
	public static function Show($data=array(),$view=null);
	public static function ExitRedirectRouteTo($url);
	public function assignExceptionHandel($classes,$callback=null);
	public function setDefaultExceptionHandel($Exception);
	
	public function _Import($file);
	public function _H($str);
	public function recordset_url($data,$cols_map);
	public function recordset_h($data,$cols=array());
	public function onShow404();
	public function onException($ex);
	public function onErrorException($ex);
	public function onDebugError($errno, $errstr, $errfile, $errline);
	public function onErrorHandel($errno, $errstr, $errfile, $errline);
	public function onBeforeShow();
	//public $options=[];
	//public $config;
	//public $isDev=false;
	public static function RunQuickly($options=array());
	public function autoload($options=array());
	public function init($options=array());
	public function isDev();
	public function run();
	public static function ThrowOn($flag,$message,$code=0);
}