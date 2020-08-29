<?php
require_once(__DIR__.'/../../autoload.php');        // @DUCKPHP_HEADFILE
use DuckPhp\DuckPhp;
use DuckPhp\Ext\Misc;

class MyApp extends DuckPhp
{
    public $options =[
        'is_debug'=>true,  //调试状态开关， 开的情况下可以用 Get 访问
        
        'api_class_base'=>'BaseApi', 
        'api_class_prefix'=>'Api_',
    ];
    protected function __construct()
    {
        $this->options['skip_setting_file']=true;
        $this->options['is_debug']=true;
        parent::__construct();
        $this->options['ext'][Misc::class]=true;
    }
    protected function OnInit()
    {
        static::addRouteHook([static::class,'Hook'], 'prepend-inner');
    }
    public static function Hook($path_info)
    {
        $path_info = trim($path_info,'/');

        $class_array = explode('.',$path_info);
        $method = array_pop($class_array);

        $class=implode('/',$class_array);
        $class = static::G()->options['api_class_prefix'] . $class;

        $object = new $class;
        
        if(static::IsDebug()){
            $input = $_REQUEST; // To fixed bug  static::Request(null);
        }else{
            $input = $_POST;
        }
        
        
        static::setDefaultExceptionHandler(function($e){
            static::ExitJson([
                'error_code'=>$e->getCode(),
                'error_message'=>$e->getMessage(),
            ]);
        });
        
        // 这里用了 Misc 的 CallAPI
        $data=static::CallAPI($object, $method, $input, static::G()->options['api_class_base']);
        static::ExitJson($data);
        
        return true;
    }
    public function _ExitJson($ret, $exit = true)
    {
        //覆盖父类的 ExitJson
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Headers: Authori-zation,Authorization, Content-Type, If-Match, If-Modified-Since, If-None-Match, If-Unmodified-Since, X-Requested-With');
        header('Access-Control-Allow-Methods: GET,POST,PATCH,PUT,DELETE,OPTIONS,DELETE');
        header('Access-Control-Max-Age: 1728000');
        return parent::_ExitJson($ret,false); // 我们不要 $exit 了。
    }
}
class BaseApi
{
    //use \DuckPhp\Core\SingletonEx;
}
$options=[
    'path' => __DIR__,  // path 要指定。不指定会发生一个 bug. 控制器那套这里没用上了。
];
MyApp::RunQuickly($options,function(){
});
////
/// 后面是业务代码
// 这里自己加 api 
class API_test extends BaseApi
{
    public function foo()
    {
        return DATE(DATE_ATOM);
    }
    public function foo2($a,$b)
    {
        return [$a+$b, DATE(DATE_ATOM)];
    }
}
////////////////
// 访问方式 http://duckphp.demo.dev/api.php/test.foo2?a=1&b=2
// 访问方式 http://duckphp.demo.dev/api.php/test.foo

//