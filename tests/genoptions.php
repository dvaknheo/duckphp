<?php
require_once(__DIR__.'/../autoload.php');
function getDescs()
{
return array (
  'all_config' => '所有配置',
'autoload_cache_in_cli' => '在 cli 下开启缓存模式',
'autoload_path_namespace_map' => '自动加载的目录和命名空间映射',
'close_resource_at_output'=>'在输出前关闭资源（DB,Redis）',
'config_ext_files'=>'额外的配置文件数组',
'controller_class_postfix'=>'控制器类名后缀',
'controller_enable_slash'=>'激活兼容后缀的 / ',
'controller_path_ext'=>'扩展名，比如你要 .html',
'key_for_action'=>'GET 方法名的 key',
'key_for_module'=>'GET 模式 类名的 key',
'path_log'=>'日志目录',
'route_map_by_config_name'=>'路由配置名，使用配置模式用路由',
'default_exception_do_log'=>'错误的时候打开日志',
'default_exception_self_display'=>'错误的时候打开日志',
'use_output_buffer'=>'使用 OB 函数缓冲数据',
'use_path_info_by_get'=>'使用 _GET 模拟无 PathInfo 配置',

  'callable_view_head' => 'callableview 页眉',
  'callable_view_foot' => 'callableview 页脚',
  'callable_view_class' => 'callableview 视图类',
  'callable_view_prefix' => 'callableview 视图函数模板',
  'callable_view_skip_replace' => 'callableview 可调用视图跳过默认视图替换',
  'controller_base_class' => '控制器基类',
  'controller_hide_boot_class' => '控制器标记，隐藏特别的入口',
  'controller_methtod_for_miss' => '控制器，缺失方法的调用方法',
  'controller_prefix_post' => '控制器，POST 方法前缀',
  'controller_welcome_class' => '控制器默认欢迎方法',
  'database_list' => '数据库列表',
  'use_autoloader' => '使用系统自带加载器',
  'default_exception_handler' => '默认异常句柄',
  'dev_error_handler' => '默认开发错误句柄',
  'error_404' => '404 页面',
  'error_500' => '500 页面',
  'error_debug' => '错误调试页面',
  'ext' => '默认开启的扩展',
  'handle_all_dev_error' => '接管一切开发错误',
  'handle_all_exception' => '接管一切异常',
  'is_debug' => '是否调试状态',
  'log_file_template'=>'日志文件名模板',
  'log_prefix' => '日志前缀',
  'database_log_sql_level' => '记录sql 错误等级',
  'database_log_sql_query' => '记录sql 查询',
  'namespace' => '命名空间',
  'namespace_controller' => '控制器的命名空间',
  'override_class' => '重写类名',
  'path' => '基础目录',
  'path_config' => '配置目录',
  'path_lib' => '库目录',
  'path_namespace' => '命名空间目录',
  'path_view' => '视图目录',
  'path_view_override' => '覆盖视图目录',
  'platform' => '平台',
  'rewrite_map' => '目录重写映射',
  'route_map' => '路由映射',
  'route_map_important' => '重要路由映射',
  'setting' => '设置，预先载入的设置',
  'setting_file' => '设置文件',
  'skip_404_handler' => '跳过404处理',
  'skip_app_autoload' => '跳过 自动加载',
  'skip_env_file' => '跳过 .env 文件',
  'skip_exception_check' => '跳过异常检查',
  'skip_fix_path_info' => '跳过 PATH_INFO 修复',
  'skip_plugin_mode_check' => '跳过插件模式检查',
  'skip_setting_file' => '跳过设置文件',
  'skip_view_notice_error' => '跳过 View 视图的 notice',
  'system_exception_handler' => '接管系统的异常管理',
  'use_flag_by_setting' => '从设置文件里再入is_debug,platform. ',
  'use_short_functions' => '使用短函数， \\_\\_url, \\_\\_h 等 ，详见 Core\\Functions.php',
  'use_super_global' => '使用super_global 类。关闭以节约性能',
  

'database_list_reload_by_setting'=>'从设置里读取数据库列表',
'empty_view_key_view'=>'给View 的key',
'empty_view_key_wellcome_class'=>'默认的 Main',
'empty_view_skip_replace'=>'跳过默认的view',
'empty_view_trim_view_wellcome'=>'跳过 Main/',
'facades_enable_autoload'=>'使用 facdes 的 autoload',
'facades_map'=>'facade 映射',
'facades_namespace'=>'facades 开始的namespace',
'jsonrpc_backend'=>'json 的后端',
'jsonrpc_check_token_handler'=>'设置 token 检查回调',
'jsonrpc_enable_autoload'=>'json 启用 autoload',
'jsonrpc_is_debug'=>'jsonrpc 是否开启 debug 模式',
'jsonrpc_namespace'=>'jsonrpc 默认的命名空间',
'jsonrpc_service_interface'=>'json 服务接口',
'jsonrpc_service_namespace'=>'json 命名空间',
'jsonrpc_wrap_auto_adjust'=>'jsonrpc 自动调整 wrap',
'redis_cache_prefix'=>' redis cache 缓存前缀',
'redis_cache_skip_replace'=>'redis cache 跳过 默认 cache替换',
'redis_list'=>' redis 配置列表',
'redis_list_reload_by_setting'=>' redis 使用 settting 文件',
'api_class_base'=>'api 服务接口',
'api_class_prefix'=>'api类的前缀',
'api_config_file'=>'api配置文件',
'mode_dir_basepath'=>'目录模式的基类',
'namespace_business'=>'strict_check 的business目录',
'namespace_model'=>'strict_check 的model 目录',
'postfix_batch_business'=>'batchbusiness',
'postfix_business_lib'=>' businesslib',
'postfix_ex_model'=>'ExModel',
'postfix_model'=>'model',
'strict_check_context_class'=>'不用传输过来的 app类，而是特别指定类',
'strict_check_enable'=>'是否开启 strict chck',

'database'=>'单一数据库配置',
'database_list_try_single'=>'尝试使用单一数据配置',
'redis'=>'单一Redisc配置',
'redis_list_try_single'=>'尝试使用单一Redis配置',

);
}

GenOptionsGenerator::G()->init([])->run();

//以options 为核心，其他都是 key ， 只要一个 value

//echo getDefaultOptions();
//echo dumpByFile();
//dumpByFile();

// $options['handle_all_dev_error'] = true;
// $options['handle_all_exception'] = true;
// $options['route_map'] = array ( );
// $options['route_map_by_config_name'] = '';
// $options['route_map_important'] = array ( );
        
return;

class GenOptionsGenerator
{
    public static function G($object=null)
    {
        static $_instance;
        $_instance=$object?:($_instance??new static);
        return $_instance;
    }
    public function init(array $options, ?object $context = null)
    {
        return $this;
    }
    public function run()
    {
        $options=$this->getAllOptions();
        $desc=getDescs();
        
        //var_export(array_diff(array_keys($options),array_keys($desc)));
        //var_export(array_diff(array_keys($desc),array_keys($options)));
        //var_export(count($options));
        //echo $this->getDefaultOptionsString($options);
        //echo "\n // 下面是默认没开的扩展 \n";
        //echo $this->getExtOptionsString($options);
        
        //echo $this->genMdBySort($options);
        //echo $this->genMdByClass($options);
    }
    public function genMdBySort($input)
    {
        $ret=[];
        foreach($input as $option => $attrs) {
            $str="";
            $b=$attrs['is_default']?'**':'';
            $var_option=var_export($option,true);
            $comment=$attrs['desc']??'';
            $s=str_replace("\n"," ",var_export($attrs['defaut_value'],true));
            
            $classes=$attrs['class'];
            $classes=array_filter($classes,function($v){return $v!='DuckPhp\\DuckPhp';});
            array_walk($classes,function(&$v, $key){$v="[$v](".str_replace(['DuckPhp\\','\\'],['','-'],$v).".md)";});
            $x="  // ".implode(", ",$classes) ."";
            $str.=<<<EOT
+ {$b} $var_option => $s,  {$b} 

    $comment $x

EOT;
            
            $ret[]=$str;
       }
        return implode("",$ret);
    }
    public function genMdByClass($input)
    {
        $classes=getAllComponentClasses();
        array_shift($classes);
        
        $ret=[];
        foreach($classes as $class){
            $str='';
            $options=(new $class())->options;
            ksort($options);
            $var_class=var_export($class,true);
            
            $str.=<<<EOT
+ $class

EOT;

            foreach($options as $k =>$v){
                $flag = ($input[$k]['is_default'])? '// ': '';
                $flag2 = ($input[$k]['is_default'])? '【共享】': '';
                $var_option=var_export($k,true);
                $comment=$input[$k]['desc']??'';
                $value=str_replace("\n"," ",var_export($v,true));
                $str.=<<<EOT
    - $var_option => $value,
        $comment

EOT;
            }
            $str.=<<<EOT

EOT;
            $ret[]=$str;
        }
        return implode("",$ret);    }
    function getAllOptions()
    {
        $classes=getAllComponentClasses();
        $ext_classes=getAviableExtClasses();
        $default_classes=getDefaultComponentClasses();
        $desc=getDescs();
        $ret=[];
        foreach($classes as $class){
            $options=(new $class())->options;
            $in_ext=in_array($class,$ext_classes)?true:false;
            foreach($options as $option => $value){
                $ret[$option]=$ret[$option]??[];
                $v= &$ret[$option];
                $v['solid']=in_array($option, $this->getKernelOptions());
                $v['is_default']=$v['is_default']??false;
                $v['is_default']=$v['is_default'] || in_array($class,$default_classes);
                $v['in_ext']=$in_ext;
                $v['defaut_value']=$value;
                $v['class']=$v['class']??[];
                $v['class'][]=$class;
                $v['desc']=$desc[$option]??'';
                
                unset($v);
            }
        }
        
        ksort($ret);
        return $ret;
    }
    protected function getDefaultOptionsString($input)
    {
        $desc=getDescs();
        $data=[];
        foreach($input as $option => $attrs) {
            if($attrs['solid']){ continue; }
            if(!$attrs['is_default']){ continue; }
            $v=$attrs['defaut_value'];
            $s=var_export($v,true);
            if(is_array($v)){
                $s=str_replace("\n",' ',$s);
            }
            //$data[$option]=$s;
            $str="        // \$options['$option'] = {$s};\n            // ".($attrs['desc']??'') ."";
            $classes=$attrs['class'];
            $classes=array_filter($classes,function($v){return $v!='DuckPhp\\DuckPhp';});
            $str.=" (".implode(", ",$classes) .")\n";
            
            $data[$option]=$str; 
            
    if(empty($attrs['desc'])){
        var_export($option);echo "=>'',\n";
    }
        }
        
        ksort($data);
        $ret=array_values($data);
        return implode("",$ret);
    }
    protected function getExtOptionsString($input)
    {
        $classes=getAviableExtClasses();
        $ret=[];
        foreach($classes as $class){
            $str='';
            $options=(new $class())->options;
            ksort($options);
            $var_class=var_export($class,true);
            
            $str.=<<<EOT
        /*
        \$options['ext'][$var_class] = true;

EOT;

            foreach($options as $k =>$v){
                $flag = ($input[$k]['is_default'])? '// ': '';
                $flag2 = ($input[$k]['is_default'])? '【共享】': '';
                $var_option=var_export($k,true);
                $comment=$input[$k]['desc']??'';
                
if(!$comment){
    var_export($k);echo "=>'',\n";
}
                $value=str_replace("\n"," ",var_export($v,true));
                $str.=<<<EOT
            {$flag}\$options[$var_option] = $value;
                // {$flag2}$comment

EOT;
            }
            $str.=<<<EOT
        //*/

EOT;
            $ret[]=$str;
        }
        return implode("",$ret);
    }
    protected function getKernelOptions()
    {
        return [
            'use_autoloader',
            'skip_plugin_mode_check',
            'handle_all_dev_error',
            'handle_all_exception',
            'override_class',
            'path_namespace',
        ];
    }

}



function classToLink($class)
{
    $name=$class;
    $name=str_replace('DuckPhp\\','',$name);
    $name=str_replace('\\','-',$name);
    return "[$class]({$name}.md)";
}
$classes=explode("\n",$classes);

function dumpByClass()
{
    $classes = getAllComponentClasses();
    foreach($classes as $class){
        $options=(new $class())->options;
        ksort($options);
        
        echo  "+ ".classToLink($class)."\n";
        foreach($options as $k =>$v){
            echo "    - '$k' => ".json_encode($v)."\n";
        }
        echo "\n";
    }
}

function dumpByFile()
{
    $classes=getAllComponentClasses();
    
    $ret=[];
    foreach($classes as $class){
        $options=(new $class())->options;
        foreach($options as $k =>$v){
            $k="'$k' => ".var_export($v,true)."";
            if(!isset($ret[$k])){
                $ret[$k]=[];
            }
            $ret[$k][]=$class;
        }
    }
    ksort($ret);
    foreach($ret as $k =>$v){
        echo "+ $k\n";
        foreach($v as $class){
            echo "    - ".classToLink($class)."\n";
        }
        echo "\n";
    }
}
/////////////////////
function getInDependComponentClasses()
{
$classes="DuckPhp\\HttpServer
DuckPhp\\Ext\\Pager
";
    $classes=explode("\n",$classes);
    return $classes;
}
function getDefaultComponentClasses()
{
    $classes="DuckPhp\\DuckPhp
DuckPhp\\Core\\AutoLoader
DuckPhp\\Core\\Configer
DuckPhp\\Core\\Logger
DuckPhp\\Core\\Route
DuckPhp\\Core\\RuntimeState
DuckPhp\\Core\\SuperGlobal
DuckPhp\\Core\\View
DuckPhp\\Ext\\Cache
DuckPhp\\Ext\\DbManager
DuckPhp\\Ext\\EventManager
DuckPhp\\Ext\\RouteHookPathInfoByGet
DuckPhp\\Ext\\RouteHookRouteMap";
    $classes=explode("\n",$classes);
    return $classes;
}
function getAllComponentClasses()
{
$classes="DuckPhp\\DuckPhp
DuckPhp\\Core\\App
DuckPhp\\Core\\AutoLoader
DuckPhp\\Core\\Configer
DuckPhp\\Core\\ExceptionManager
DuckPhp\\Core\\Logger
DuckPhp\\Core\\Route
DuckPhp\\Core\\RuntimeState
DuckPhp\\Core\\SuperGlobal
DuckPhp\\Core\\View
DuckPhp\\Ext\\CallableView
DuckPhp\\Ext\\Cache
DuckPhp\\Ext\\DbManager
DuckPhp\\Ext\\EmptyView
DuckPhp\\Ext\\EventManager
DuckPhp\\Ext\\FacadesAutoLoader
DuckPhp\\Ext\\JsonRpcExt
DuckPhp\\Ext\\Misc
DuckPhp\\Ext\\RedisCache
DuckPhp\\Ext\\RedisManager
DuckPhp\\Ext\\RouteHookApiServer
DuckPhp\\Ext\\RouteHookDirectoryMode
DuckPhp\\Ext\\RouteHookPathInfoByGet
DuckPhp\\Ext\\RouteHookRewrite
DuckPhp\\Ext\\RouteHookRouteMap
DuckPhp\\Ext\\StrictCheck";
    $classes=explode("\n",$classes);
    return $classes;
}
function getAviableExtClasses()
{
    $default=getDefaultComponentClasses();
    $all=getAllComponentClasses();
    $ext=array_diff($all,$default);
    $ext=array_filter($ext,function($v){if(substr($v,0,strlen("DuckPhp\\Ext\\"))==="DuckPhp\\Ext\\"){return true;}else{return false;}});
    return $ext;
}

function getAllOptionDeclareClasses()
{
    $classes=getAllComponentClasses();
    
    $ret=[];
    foreach($classes as $class){
        $options=(new $class())->options;
        foreach($options as $k =>$v){
            if(!isset($ret[$k])){
                $ret[$k]=[];
            }
            $ret[$k][$class]=$v;
        }
    }
    ksort($ret);
    return $ret;
}

function SliceReplace($data, $replacement, $str1, $str2, $is_outside = false, $wrap = false)
{
    $pos_begin = strpos($data, $str1);
    $extlen = ($pos_begin === false)?0:strlen($str1);
    $pos_end = strpos($data, $str2, $pos_begin + $extlen);
    
    if ($pos_begin === false || $pos_end === false) {
        if (!$wrap) {
            return  $data;
        }
    }
    if ($is_outside) {
        $pos_begin = ($pos_begin === false)?0:$pos_begin;
        $pos_end = ($pos_end === false)?strlen($data):$pos_end + strlen($str2);
    } else {
        $pos_begin = ($pos_begin === false)?0:$pos_begin + strlen($str1);
        $pos_end = ($pos_end === false)?strlen($data):$pos_end;
    }
    
    return substr_replace($data, $replacement, $pos_begin, $pos_end - $pos_begin);
}
function replaceData($data,$file,$dir='')
{
    $content=file_get_contents($dir.$file);

    $str1="File: `$file`\n\n```php\n";
    $str2="\n```\n";
    $replacement = $content;
    $data=SliceReplace($data, $replacement, $str1, $str2);
    
    return $data;
}

return ;

//*
$data=file_get_contents("README.md");
$file="template/public/helloworld.php";
$data=replaceData($data,$file,$dir='');
$file="template/public/demo.php";
$data=replaceData($data,$file,$dir='');
file_put_contents("README.md",$data);
//*/


$data=file_get_contents("doc/tutorial-general.md");
$file="template/public/index.php";
$data=replaceData($data,$file,$dir='');
$file="template/app/System/App.php";
$data=replaceData($data,$file,$dir='');
file_put_contents("doc/tutorial-general.md",$data);


