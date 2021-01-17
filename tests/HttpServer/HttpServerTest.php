<?php
namespace tests\DuckPhp\HttpServer;

use DuckPhp\HttpServer\HttpServer;

class HttpServerTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(HttpServer::class);
        
        $options=[
            'path_document'=>__DIR__,
        ];
        HttpServerParent::G();
        HttpServerParent::G(new HttpServerParent())->RunQuickly($options);
        HttpServerParent::G()->close();
        HttpServerParent::G()->test_showHelp();
        HttpServerParent::G()->test_run2();
HttpServerParent::G()->isInited();
        
        $options=[
            'path_document'=>__DIR__,
            'background'=>true,
        ];
        HttpServerParent::RunQuickly($options);

        echo HttpServerParent::G()->getPid();
        HttpServerParent::G()->close();
        $t=\LibCoverage\LibCoverage::G();
        define('__SINGLETONEX_REPALACER',HttpServerParent::class.'::CreateObject');
        \LibCoverage\LibCoverage::G($t);
        HttpServerParent::G();
        echo "zzzzzzzzzzzzzzzzzzzzzzzz";
        \LibCoverage\LibCoverage::End();
    }
}
class HttpServerParent extends HttpServer
{
    public static function CreateObject($class, $object)
    {
        static $_instance;
        $_instance=$_instance??[];
        $_instance[$class]=$object?:($_instance[$class]??($_instance[$class]??new $class));
        return $_instance[$class];
    }

    protected $my_cli_options=[
            'just-test'=>[
                'short'=>'z',
                'desc'=>'do not use swoole httpserver',
                'optional'=>true,
            ],
    ];
    public function __construct()
    {
        parent::__construct();
        $this->cli_options=array_merge_recursive($this->cli_options,$this->my_cli_options);
    }
    protected function getopt($options,$longopts,&$optind)
    {
        $ret=getopt($options,$longopts,$optind);
        $ret['z']='zzzzzzzzzzzzz';
        return $ret;
    }
    public function test_showHelp()
    {
        $this->showHelp();
    }
    public function test_run2()
    {
        $this->args['help']=true;
        $this->run();
    }
    protected function runHttpServer()
    {
        if(! ($this->options['background']??false)){
            $this->args['dry']=true;
        }else{
            $this->args['background']=true;
        }
        return parent::runHttpServer();
    }
}