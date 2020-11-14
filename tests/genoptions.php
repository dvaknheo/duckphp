<?php
require_once(__DIR__.'/../autoload.php');
// 自动化文档脚本

GenOptionsGenerator::G()->init([])->run();

var_dump(DATE(DATE_ATOM));

return;

class GenOptionsGenerator
{
    public function checkHasDoced()
    {
        $ret=[];
        $input=GenOptionsGenerator::G()->getAllOptions();

        $classes=DataProvider::G()->getAllComponentClasses();
        array_shift($classes);
        foreach($classes as $class){
            //var_dump($class);
            $file=realpath(__DIR__.'/../doc/');
            $str=str_replace(['DuckPhp\\','\\'],['/ref/','-'],$class);
            $file=$file.$str.'.md';
            $data=file_get_contents($file);
            $options=(new $class())->options;
            $row=[];
            foreach($options as $k =>$v){
                if(false ===strpos($data,$k)){
                    $row[]=$k;
                }
            }
            if(!empty($row)){
                $ret[$class]=$row;
            }
        }
        if(!empty($ret)){
            var_dump($ret);
        }
    }
    
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
        WrapFileAction(__DIR__ . '/../template/app/System/App.php',function($content){
            $data=$this->getOptionStringForApp();
            
            $str1="        // @autogen by tests/genoptions.php\n";
            $str2="        // @autogen end\n";
            $content=SliceReplace($content, $data, $str1, $str2);
            return $content;
        });
        $docs=GetAllDocFile();
        foreach($docs as $file){
            WrapFileAction($file,'replaceData');
        }
        $this->checkHasDoced();
        
    }
    public function diff()
    {
        $options=$this->getAllOptions();
        $desc=DataProvider::G()->getDescs();
        var_export(array_diff(array_keys($options),array_keys($desc)));
        var_export(array_diff(array_keys($desc),array_keys($options)));
    }
    public function getOptionStringForApp()
    {
        $options=$this->getAllOptions();
        $str="        // ---- 脚本生成,下面是可用的默认选项 ---- \n\n";
        $str.=$this->getDefaultOptionsString($options);
        $str.="        // ---- 下面是默认未使用的扩展 ----\n\n";
        $str.=$this->getExtOptionsString($options);
        return $str;
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
        $classes=DataProvider::G()->getAllComponentClasses();
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
        return implode("",$ret);    
    }
    public function getAllOptions()
    {
        $classes=DataProvider::G()->getAllComponentClasses();
        $ext_classes=DataProvider::G()->getAviableExtClasses();
        $default_classes=DataProvider::G()->getDefaultComponentClasses();
        $desc=DataProvider::G()->getDescs();
        $ret=[];
        foreach($classes as $class){
            $options=(new $class())->options;
            $in_ext=in_array($class,$ext_classes)?true:false;
            foreach($options as $option => $value){
                $ret[$option]=$ret[$option]??[];
                $v= &$ret[$option];
                $v['solid']=in_array($option, DataProvider::G()->getKernelOptions());
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
        $desc=DataProvider::G()->getDescs();
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
            $desc = ($attrs['desc']??'');
                        $classes=$attrs['class'];
            $classes=array_filter($classes,function($v){return $v!='DuckPhp\\DuckPhp';});
            $classes_desc =implode(", ",$classes);
            $str=<<<EOT
        // $desc ($classes_desc)
        // \$options['$option'] = $s;


EOT;            
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
        $classes=DataProvider::G()->getAviableExtClasses();
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
            // {$flag2}$comment
            {$flag}\$options[$var_option] = $value;


EOT;
            }
            $str.=<<<EOT
        //*/

EOT;
            $ret[]=$str;
        }
        return implode("",$ret);
    }

    
    protected function classToLink($class)
    {
        $name=$class;
        $name=str_replace('DuckPhp\\','',$name);
        $name=str_replace('\\','-',$name);
        return "[$class]({$name}.md)";
    }
}

/////////////////////
class DataProvider
{
    public static function G($object=null)
    {
        static $_instance;
        $_instance=$object?:($_instance??new static);
        return $_instance;
    }
    public function getKernelOptions()
    {
        return [
            //'use_autoloader',
            //'skip_plugin_mode_check',
            //'override_class',
        ];
    }
    public function getDescs()
    {
        static $cache;
        if(!isset($cache)){
            $cache=json_decode(file_get_contents(__DIR__ . '/../doc/options-desc.json'),true);
        }
        return $cache;
    }
    public function getAviableExtClasses()
    {
        $default=$this->getDefaultComponentClasses();
        $all=$this->getAllComponentClasses();
        $ext=array_diff($all,$default);
        $ext=array_filter($ext,function($v){if(substr($v,0,strlen("DuckPhp\\Ext\\"))==="DuckPhp\\Ext\\"){return true;}else{return false;}});
        return $ext;
    }
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
DuckPhp\\Core\\App
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
DuckPhp\\Ext\\RouteHookPathInfoCompat
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
DuckPhp\\Ext\\Cache
DuckPhp\\Ext\\CallableView
DuckPhp\\Ext\\Console
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
DuckPhp\\Ext\\RouteHookPathInfoCompat
DuckPhp\\Ext\\RouteHookRewrite
DuckPhp\\Ext\\RouteHookRouteMap
DuckPhp\\Ext\\StrictCheck";
        $classes=explode("\n",$classes);
        return $classes;
    }
}


///////////////////////////////////////////
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
function replaceData($content)
{
    $dir=__DIR__.'/../';
    
    if(false !== strpos($content,'@forscript')){
        $options=GenOptionsGenerator::G()->getAllOptions();
        $data=GenOptionsGenerator::G()->genMdBySort($options);
        
        $str1="@forscript genoptions.php#options-md-alpha\n";
        $str2="\n@forscript end";
        $content=SliceReplace($content, $data, $str1, $str2);
        
        $data=GenOptionsGenerator::G()->genMdByClass($options);
        $str1="@forscript genoptions.php#options-md-class\n";
        $str2="\n@forscript end";
        $content=SliceReplace($content, $data, $str1, $str2);
    }
    
    
    $flag=preg_match_all('/File: `([^`]+)`/',$content,$m);
    $files=$flag ? $m[1]:[];
    foreach($files as $file){
        $replacement=file_get_contents($dir.$file);
        if($file==='template/app/System/App.php'){
            $str1="        // @autogen by tests/genoptions.php\n";
            $str2="        // @autogen end\n";
            $replacement=SliceReplace($replacement, "// 【省略选项注释】\n", $str1, $str2);
        }
        

        $str1="File: `$file`\n\n```php\n";
        $str2="\n```\n";
        $content=SliceReplace($content, $replacement, $str1, $str2);
    }
    
    return $content;
}
function WrapFileAction($file,$callback)
{
    $data=file_get_contents($file);
    $data=$callback($data,$file);
    file_put_contents($file,$data);
}
function GetAllDocFile()
{
    $source=realpath(__DIR__.'/../doc/');
    $directory = new \RecursiveDirectoryIterator($source, \FilesystemIterator::CURRENT_AS_PATHNAME | \FilesystemIterator::SKIP_DOTS);
    $iterator = new \RecursiveIteratorIterator($directory);
    $it=new \RegexIterator($iterator,'/\.md$/', RegexIterator::MATCH);
    $ret=\iterator_to_array($it, false);
    array_unshift($ret,realpath(__DIR__.'/../README.md'));
    return $ret;
}

function getClassStaticMethods($class)
{
    $ref=new ReflectionClass($class);
    $a=$ref->getMethods(ReflectionMethod::IS_STATIC);
    $ret=[];
    foreach($a as $v){
        $ret[]=$v->getName();
    }
    return $ret;
}


$m=getClassStaticMethods(DuckPhp::class);
$m_a=getClassStaticMethods(\DuckPhp\Helper\AppHelper::class);
$m_b=getClassStaticMethods(\DuckPhp\Helper\BusinessHelper::class);
$m_c=getClassStaticMethods(\DuckPhp\Helper\ControllerHelper::class);
$m_m=getClassStaticMethods(\DuckPhp\Helper\ModelHelper::class);
$m_v=getClassStaticMethods(\DuckPhp\Helper\ViewHelper::class);

$ret=array_diff($m,$m_a,$m_b,$m_c,$m_m,$m_v);
var_dump(array_values($ret));
