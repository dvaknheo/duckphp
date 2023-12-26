<?php
use DuckPhp\Core\PhaseContainer;
use DuckPhp\DuckPhp;

require_once(__DIR__.'/../autoload.php');

class MyContainer extends PhaseContainer
{
    public function getComponents()
    {
        $classes= array_keys($this->containers[DuckPhp::class]);
        return $classes;
    }
}
//////////////////////
// 自动化文档脚本

//DataProvider::G()->getAviableExtClasses();return;
DocFixer::G()->init([])->run(); //填充缺失的
var_dump(DocFixer::G()->options_descs);
echo "-----------------\n";
// 从上面运行的结果要数据， 然后 生成在 options.md 里 // 还影响到 template/app/System/App.php
OptionsGenerator::G()->init([])->run();

var_dump(DATE(DATE_ATOM));

return;
class DataProvider
{
    // 独立的组件
    public $independs = "DuckPhp\\HttpServer\\HttpServer
DuckPhp\\Component\\Pager
";
    // 所有可配组件
    public $all="DuckPhp\\DuckPhp";
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
            $cache =  DocFixer::G()->options_descs;
        }
        return $cache;
    }
    public function getAviableExtClasses()
    {
        $source=realpath(__DIR__.'/../src/ext');
        $directory = new \RecursiveDirectoryIterator($source, \FilesystemIterator::CURRENT_AS_PATHNAME | \FilesystemIterator::SKIP_DOTS);
        $iterator = new \RecursiveIteratorIterator($directory);
        $it=new \RegexIterator($iterator,'/\.php$/', RegexIterator::MATCH);
        $ret=\iterator_to_array($it, false);
        $classes =[];
        foreach($ret as $file){
            if('Trait.php' === substr($file,-strlen('Trait.php'))){ continue; }
            if('HookChain.php' === substr($file,-strlen('HookChain.php'))){ continue; }
            //其他忽略的也放进这里
            $classes[] = 'DuckPhp\\Ext\\'.basename($file,'.php');
        }
        return $classes;
    }
    function getInDependComponentClasses()
    {
        
        // 这里要移动到配置里
        $classes=explode("\n",$this->independs);
        return $classes;
    }
    function getDefaultComponentClasses()
    {
        // 我们override  phasecontainer ,然后把所有 comoont dump 出来
        $container = new MyContainer();
        PhaseContainer::GetContainerInstanceEx($container);
        DuckPhp::_(new DuckPhp())->init([
            'is_debug' => true,
            'path_info_compact_enable' => true,
        ]);
        $classes = $container->getComponents();
        return $classes;
    }
    function getAllComponentClasses()
    {
        return array_merge($this->getDefaultComponentClasses(), $this->getAviableExtClasses());
    }
}
class DocFixer
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
    protected $path_base='';
    public $options_descs=[];
    public function __construct()
    {
        $ref=new ReflectionClass(\DuckPhp\DuckPhp::class);
        $this->path_base=realpath(dirname($ref->getFileName()) . '/../').'/';
    }

    public function run()
    {
        $options_descs =[];
        $files=$this->getSrcFiles($this->path_base.'src/');
        foreach($files as $file){
            $data = $this->drawSrc($file);  //从代码中抽取选项和函数
            $this->doFixDoc($file, $data);
            $options_desc =$this->getOptionsDesc($file,$data);
            $options_descs=array_merge($options_descs,$options_desc);
        }
        ksort($options_descs);
        
        
        //$flag = JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK | JSON_PRETTY_PRINT;;
        //file_put_contents(__DIR__.'/../docs/out.json',json_encode($all,$flag));
        $this->options_descs = $options_descs;
        ///TODO 我们把 ref的东西复制出来，然后 复制到 readme 里
        return true;
    }
    
    protected function getSrcFiles($source)
    {
        $directory = new \RecursiveDirectoryIterator($source, \FilesystemIterator::CURRENT_AS_PATHNAME | \FilesystemIterator::SKIP_DOTS);
        $iterator = new \RecursiveIteratorIterator($directory);
        $it=new \RegexIterator($iterator,'/\.php$/', RegexIterator::MATCH);
        $ret=\iterator_to_array($it, false);
        return $ret;
    }
    protected function doFixDoc($file,$data)
    {
        $doc_file = $this->getDocFilename($file);
        $docs_lines=is_file($doc_file)?file($doc_file):[];
        
        $options_diff=array_diff($data['options'],$docs_lines); // 简单的看有没有相同的 options
        $functions_diff=array_diff($data['functions'],$docs_lines); // 简单的看文档行有没有相同的 function
        $text ='';
        if($options_diff){
            $text.=implode("\n",$options_diff)."\n";
        }
        if($functions_diff){
            $text.=implode("\n",$functions_diff)."\n";

        }
        if($text){
            var_dump($file,$text);
            file_put_contents($doc_file.'',$text,FILE_APPEND);
        }
    }
    
    protected function getOptionsDesc($file,$data)
    {
        $doc_file = $this->getDocFilename($file);
        $doc =  is_file($doc_file) ?  file_get_contents($doc_file) : '';
        
        // 从 md 文件抽取  options第二行 key => 第二行注释数组
        $ret=[];
        foreach($data['options'] as $v){
            $pos=strpos($doc,$v);
            if(false===$pos){continue;}
            $str=substr($doc,$pos,255);
            $t=explode("\n",$str);
            array_shift($t);
            $z=array_shift($t);
            preg_match("/'([^']+)/",$str,$m);
            $k=$m[1];
            $ret[$k]=$z;
        }
        return $ret;
    }
    public function drawSrc($file)
    {
        //从文件中抽取参数和函数
        
        $head  = '    public $options = ['."\n";
        $head2 = '    protected $kernel_options = ['."\n";
        $head3 = '    protected $core_options = ['."\n";
        $head4 = '    protected $common_options = ['."\n";

        $foot  = '    ];'."\n";
        $foot2 = '    ];'."\n";
        $foot3 = '    ];'."\n";
        $foot4 = '    ];'."\n";
        
        
        $in=false;
        
        $options=[];
        $functions=[];
        
        $lines=file($file);
        foreach($lines as $l){
            if($l === $foot || $l === $foot2 || $l === $foot3 || $l === $foot4){
                break;
            }
            if($in){
                if(empty(trim($l))){ continue; }
                if(substr(trim($l),0,2)==='//'){continue;}
                $options[]=$l;
            }
            if($l===$head || $l === $head2 || $l === $head3 || $l === $head4){
                $in=true;
            }
            
        }
        $functions=array_filter($lines,function($v){return preg_match('/function [a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/',$v);});
        return ['options'=>$options,'functions'=>$functions];
    }
    protected function getDocFilename($file)
    {
        $md=substr($file,strlen($this->path_base.'src/'));
        $md=$this->path_base.'/docs/ref/'.str_replace(['/','.php'],['-','.md'],$md);
        return $md;
    }
}
class OptionsGenerator
{
    public function checkHasDoced()
    {
        $ret=[];
        $input=$this->getAllOptions();

        //重新调整，我们按文件来。 如果是 options, 那么
        $classes=DataProvider::G()->getAllComponentClasses();
        array_shift($classes);
        foreach($classes as $class){
            //var_dump($class);
            $file=realpath(__DIR__.'/../docs/');
            $str=str_replace(['DuckPhp\\','\\'],['/ref/','-'],$class);
            $file=$file.$str.'.md';
            $data=file_get_contents($file);
            
            //  
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
        // 这里是显示没有 注释的 $options;
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
    protected static function GetAllDocFile()
    {
        $source=realpath(__DIR__.'/../docs/');
        $directory = new \RecursiveDirectoryIterator($source, \FilesystemIterator::CURRENT_AS_PATHNAME | \FilesystemIterator::SKIP_DOTS);
        $iterator = new \RecursiveIteratorIterator($directory);
        $it=new \RegexIterator($iterator,'/\.md$/', RegexIterator::MATCH);
        $ret=\iterator_to_array($it, false);
        array_unshift($ret,realpath(__DIR__.'/../README.md'));
        return $ret;
    }
    function WrapFileAction($file,$callback)
    {
        $data=file_get_contents($file);
        $data=$callback($data,$file);
        file_put_contents($file,$data);
    }
    public function run()
    {

        static::WrapFileAction(__DIR__ . '/../template/src/System/Options.php',function($content){
            $data=$this->getOptionStringForApp();
            $str1="        // @autogen by tests/genoptions.php\n";
            $str2="        // @autogen end\n";
            $content=SliceReplace($content, $data, $str1, $str2);
            return $content;
        });
        static::WrapFileAction(__DIR__ . '/../README.md','replaceData');
        //static::WrapFileAction(__DIR__ . '/../README-zh-CN.md','replaceData');
        
        $docs=static::GetAllDocFile();
        foreach($docs as $file){
            static::WrapFileAction($file,'replaceData');
        }
        return;
        $this->checkHasDoced();
        return;        
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
+ {$b}$var_option => $s,{$b} 

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
            try{
                $options=(new $class())->options;
            }catch(\Throwable $ex){
                continue;
            }
            $in_ext=in_array($class,$ext_classes)?true:false;
            foreach($options as $option => $value){
                $ret[$option]=$ret[$option]??[];
                $v= &$ret[$option];
                $v['solid']= false;
                $v['is_default']=$v['is_default'] ?? false;
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
        $options=OptionsGenerator::G()->getAllOptions();
        $data=OptionsGenerator::G()->genMdBySort($options);
        
        $str1="@forscript genoptions.php#options-md-alpha\n";
        $str2="\n@forscript end";
        $content=SliceReplace($content, $data, $str1, $str2);
        
        $data=OptionsGenerator::G()->genMdByClass($options);
        $str1="@forscript genoptions.php#options-md-class\n";
        $str2="\n@forscript end";
        $content=SliceReplace($content, $data, $str1, $str2);
    }
    
    
    $flag=preg_match_all('/File: `([^`]+)`/',$content,$m);
    $files=$flag ? $m[1]:[];
    foreach($files as $file){
        if(!is_file($dir.$file)){ continue;}
        $replacement=file_get_contents($dir.$file);
        if($file==='template/app/System/App.phpx'){
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


/*
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
*/