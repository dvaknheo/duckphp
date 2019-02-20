#!/usr/bin/env php
<?php
require(__DIR__.'/../headfile/headfile.php');
////[[[[
$path=realpath(__DIR__.'/../').'/';
$dn_options=[
	'path'=>$path,
];
////]]]]
///////////
$short_opts=[
	'h',
	'H:',
	'P:',
	's',
];
$long_opts=[
	'help',
	'host:',
	'port:',
	'swoole',
];
$optind=null;
$args=getopt(implode('',$short_opts),$long_opts,$optind);

$host='0.0.0.0';
$port='8080';
$host=$args['H']??$host;
$host=$args['host']??$host;
$port=$args['P']??$port;
$port=$args['port']??$port;

if(isset($args['h']) || isset($args['help'])){
	return ShowHelp();
}
echo "DNMVCS CMD : use --help for show helps\n";
if(!CheckSwoole($args)){
	return RunHttpServer($path,$host,$port);
}

$swoole_options=[
	'host'=>$host,
	'port'=>$port,
];
$dn_options['swoole']=$swoole_options;

if(defined('DNMVCS_WARNING_IN_TEMPLATE')){ $dn_options['setting_file_basename']=''; }
if(defined('DNMVCS_WARNING_IN_TEMPLATE')){ echo "Don't run the template file directly \n"; }

\DNMVCS\DNMVCS::RunQuickly($dn_options);
/////////////////
function CheckSwoole($args)
{
	$flag=(isset($args['s']) || isset($args['swoole']))?true:false;
	return $flag;
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