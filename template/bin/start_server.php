#!/usr/bin/env php
<?php
require(__DIR__.'/../headfile/headfile.php');
////[[[[
$dn_options=@include('start_server.config.php');
$dn_options=$dn_options??[];

$path=realpath(__DIR__.'/../').'/';
$dn_options['path']=$path;

////]]]]
///////////
$host='0.0.0.0';
$port='8080';

$opts=[
	'help'	=>'h',
	'host:'	=>'H:',
	'port:'	=>'P:',
	'swoole'=>'s',
];

$captures=GetCmdCaptures($opts);

$show_help=isset($captures['help'])?true:false;
if($show_help){
	return ShowHelp();
}

$host=$captures['host']??$host;
$port=$captures['port']??$port;

echo "Well Come to use DNMVCS ,for more info , use --help \n";

if(!CheckSwoole($captures)){
	return RunHttpServer($path,$host,$port);
}

$dn_options['swoole']=$dn_options['swoole']??[];

$dn_options['swoole']['host']=$dn_options['swoole']['host']??$host;
$dn_options['swoole']['port']=$dn_options['swoole']['port']??$port;

if(defined('DNMVCS_WARNING_IN_TEMPLATE')){ $dn_options['setting_file_basename']=''; }
if(defined('DNMVCS_WARNING_IN_TEMPLATE')){ echo "Don't run the template file directly \n"; }

\DNMVCS\DNMVCS::RunQuickly($dn_options);

/////////////////

function GetCmdCaptures($opts)
{
	$optind=null;
	$args=getopt(implode('',array_values($opts)),array_keys($opts),$optind);

	$shorts=array_map(function($v){return trim($v,':');},array_values($opts));
	$longs=array_map(function($v){return trim($v,':');},array_keys($opts));
	$new_opts=array_combine($shorts,$longs);
	$ret=[];
	foreach($args as $k=>$v){
		$key=$new_opts[$k]??$k;
		$ret[$key]=$v;
	}
	return $ret;
}
function CheckSwoole($args)
{
	$flag=( isset($args['swoole']) )?true:false;
	if(!$flag){ return false; }
	if(!function_exists('swoole_version')){ return false; }
	
	return true;
}
function RunHttpServer($path,$host,$port)
{
	$PHP=$_SERVER['_'];
	$dir=$path.'public';
	$PHP=escapeshellcmd($PHP);
	$host=escapeshellcmd($host);
	$port=escapeshellcmd($port);
	echo "DNMVCS CMD : RunServer bye php inner http server\n";
	$cmd="$PHP -t $dir -S $host:$port";
	exec($cmd);
}
function ShowHelp()
{
	$doc=<<<EOT
DNMVCS server usage:
  -h --help   show this help;
  -s --swoole use swoole server;
  -H --host   set server host,default is '8080';
  -P --port   set server port,default is '0.0.0.0';

EOT;
	echo $doc;
}
