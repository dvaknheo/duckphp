<?php
namespace DNMVCS;

if (!trait_exists('DNMVCS\DNDI', false)) {
    trait DNDI
    {
        protected $_di_container;
        public static function DI($name, $object=null)
        {
            return static::G()->_DI($name, $object);
        }
        public function _DI($name, $object=null)
        {
            if (null===$object) {
                return $this->_di_container[$name];
            }
            $this->_di_container[$name]=$object;
            return $object;
        }
    }
}
class RouteHookMapAndRewrite
{
    use DNSingleton;
    protected $rewrite_map=[];
    protected $route_map=[];
    protected $enable_paramters=false;
    public function init($rewrite_map, $route_map)
    {
        $this->rewrite_map=$rewrite_map;
        $this->route_map=$route_map;
        $this->enable_paramters=DNMVCS::G()->options['enable_paramters'];
    }
    public function replaceRegexUrl($input_url, $template_url, $new_url)
    {
        if (substr($template_url, 0, 1)!=='~') {
            return null;
        }
        
        
        $input_path=parse_url($input_url, PHP_URL_PATH);
        $input_get=[];
        parse_str(parse_url($input_url, PHP_URL_QUERY), $input_get);
        
        //$template_path=parse_url($template_url,PHP_URL_PATH);
        //$template_get=[];
        parse_str(parse_url($template_url, PHP_URL_QUERY), $template_get);
        $p='/'.str_replace('/', '\/', substr($template_url, 1)).'/A';
        if (!preg_match($p, $input_path)) {
            return null;
        }
        //if(array_diff_assoc($input_get,$template_get)){ return null; }
        
        $new_url=str_replace('$', '\\', $new_url);
        $new_url=preg_replace($p, $new_url, $input_path);
        
        $new_path=parse_url($new_url, PHP_URL_PATH);
        $new_get=[];
        parse_str(parse_url($new_url, PHP_URL_QUERY), $new_get);
        
        $get=array_merge($input_get, $new_get);
        $query=$get?'?'.http_build_query($get):'';
        return $new_path.$query;
    }
    public function replaceNormalUrl($input_url, $template_url, $new_url)
    {
        if (substr($template_url, 0, 1)==='~') {
            return null;
        }
        
        $input_path=parse_url($input_url, PHP_URL_PATH);
        $input_get=[];
        parse_str(parse_url($input_url, PHP_URL_QUERY), $input_get);
        
        $template_path=parse_url($template_url, PHP_URL_PATH);
        $template_get=[];
        parse_str(parse_url($template_url, PHP_URL_QUERY), $template_get);
        
        if (array_diff_assoc($input_get, $template_get)) {
            return null;
        }
        
        $new_path=parse_url($new_url, PHP_URL_PATH);
        $new_get=[];
        parse_str(parse_url($new_url, PHP_URL_QUERY), $new_get);
        if ($input_path!==$template_path) {
            return null;
        }
        
        $get=array_merge($input_get, $new_get);
        $query=$get?'?'.http_build_query($get):'';
        
        return $new_path.$query;
    }
    public function filteRewrite($input_url)
    {
        foreach ($this->rewrite_map as $template_url=>$new_url) {
            $ret=$this->replaceNormalUrl($input_url, $template_url, $new_url);
            if ($ret!==null) {
                return $ret;
            }
            $ret=$this->replaceRegexUrl($input_url, $template_url, $new_url);
            if ($ret!==null) {
                return $ret;
            }
        }
        return null;
    }
    protected function matchRoute($pattern_url, $path_info, $route, $enable_paramters)
    {
        $request_method=$route->request_method;
        
        $pattern='/^(([A-Z_]+)\s+)?(~)?\/?(.*)\/?$/';
        $flag=preg_match($pattern, $pattern_url, $m);
        if (!$flag) {
            return false;
        }
        $method=$m[2];
        $is_regex=$m[3];
        $url=$m[4];
        if ($method && $method!==$request_method) {
            return false;
        }
        if (!$is_regex) {
            $params=explode('/', $path_info);
            $url_params=explode('/', $url);
            if (!$enable_paramters) {
                return ($url_params===$params)?true:false;
            }
            if ($url_params === array_slice($params, 0, count($url_params))) {
                $route->parameters=array_slice($params, 0, count($url_params));
                return true;
            } else {
                return false;
            }
        }
        
        $p='/'.str_replace('/', '\/', $url).'/';
        $flag=preg_match($p, $path_info, $m);
        
        if (!$flag) {
            return false;
        }
        array_shift($m);
        $route->parameters=$m;
        return true;
    }
    protected function getRouteHandelByMap($route, $routeMap)
    {
        $path_info=$route->path_info;
        $enable_paramters=$this->enable_paramters;
        
        foreach ($routeMap as $pattern =>$callback) {
            if (!$this->matchRoute($pattern, $path_info, $route, $enable_paramters)) {
                continue;
            }
            if (!is_string($callback)) {
                return $callback;
            }
            if (false!==strpos($callback, '->')) {
                list($class, $method)=explode('->', $callback);
                return [new $class(),$method];
            }
            return $callback;
        }
        return null;
    }
    protected function changeRouteUrl($route, $url)
    {
        $path=parse_url($url, PHP_URL_PATH);
        $input_get=[];
        parse_str(parse_url($url, PHP_URL_QUERY), $input_get);
        $route->path_info=$path;
        DNSuperGlobal::G()->_SERVER['init_get']=DNSuperGlobal::G()->_GET;
        DNSuperGlobal::G()->_GET=$input_get;
    }
    protected function hookRewrite($route)
    {
        $path_info=$route->path_info;
        
        $uri=DNSuperGlobal::G()->_SERVER['REQUEST_URI'];
        $query=parse_url($uri, PHP_URL_QUERY);
        $query=$query?'?'.$query:'';
        $input_url=$path_info.$query;
        foreach ($this->rewrite_map as $template_url=>$new_url) {
            $url=$this->replaceNormalUrl($input_url, $template_url, $new_url);
            if ($url!==null) {
                $this->changeRouteUrl($route, $url);
            }
            $url=$this->replaceRegexUrl($input_url, $template_url, $new_url);
            if ($url!==null) {
                $this->changeRouteUrl($route, $url);
            }
        }
    }
    protected function hookRouteMap($route)
    {
        $route->callback=$this->getRouteHandelByMap($route, $this->route_map);
    }
    public function hook($route)
    {
        $this->hookRewrite($route);
        $this->hookRouteMap($route);
    }
}
//deal with onefile.php?module=?&act=?

// basedir  a/b.php/d
class RouteHookOneFileMode
{
    use DNSingleton;

    public $key_for_action='_r';
    public $key_for_module='';
    public function init($key_for_action, $key_for_module='')
    {
        $this->key_for_action=$key_for_action;
        $this->key_for_module=$key_for_module;
        
        return $this;
    }
    public function onURL($url=null)
    {
        if (strlen($url)>0 && '/'==$url{0}) {
            return $url;
        };
        
        $key_for_action=$this->key_for_action;
        $key_for_module=$this->key_for_module;
        $get=[];
        $path='';
        $path=DNSuperGlobal::G()->_SERVER['REQUEST_URI'];
        $path_info=DNSuperGlobal::G()->_SERVER['PATH_INFO'];

        
        $path=parse_url($path, PHP_URL_PATH);
        if (strlen($path_info)) {
            $path=substr($path, 0, 0-strlen($path_info));
        }
        if ($url===null || $url==='') {
            return $path;
        }
        ////////////////////////////////////
        
        $new_url=RouteHookMapAndRewrite::G()->filteRewrite($url);
        if ($new_url) {
            $url=$new_url;
            if (strlen($url)>0 && '/'==$url{0}) {
                return $url;
            };
        }
        
        $input_path=parse_url($url, PHP_URL_PATH);
        $input_get=[];
        parse_str(parse_url($url, PHP_URL_QUERY), $input_get);
        
        $blocks=explode('/', $input_path);
        if (isset($blocks[0])) {
            $basefile=basename(DNSuperGlobal::G()->_SERVER['SCRIPT_FILENAME']);
            if ($blocks[0]===$basefile) {
                array_shift($blocks);
            }
        }
        
        if ($key_for_module) {
            $action=array_pop($blocks);
            $module=implode('/', $blocks);
            if ($module) {
                $get[$key_for_module]=$module;
            }
            $get[$key_for_action]=$action;
        } else {
            $get[$key_for_action]=$input_path;
        }
        $get=array_merge($input_get, $get);
        if ($key_for_module && isset($get[$key_for_module]) && $get[$key_for_module]==='') {
            unset($get[$key_for_module]);
        }
        $query=$get?'?'.http_build_query($get):'';
        $url=$path.$query;
        
        return $url;
    }
    public function hook($route)
    {
        $route->setURLHandler([$this,'onURL']);
        
        $k=$this->key_for_action;
        $m=$this->key_for_module;
        $old_path_info=DNSuperGlobal::G()->_SERVER['PATH_INFO']??'';
        
        $module=DNSuperGlobal::G()->_REQUEST[$m]??null;
        $path_info=DNSuperGlobal::G()->_REQUEST[$k]??null;

        $path_info=$module.'/'.$path_info;
        $path_info=ltrim($path_info, '/');
        
        $path_info=($path_info==='')?ltrim($old_path_info, '/'):$path_info;
        $route->path_info=$path_info;
        $route->calling_path=$path_info;
    }
}

class RouteHookDirectoryMode
{
    use DNSingleton;

    public function init($options)
    {
        $this->basepath=$options['mode_dir_basepath'];
    }
    protected function adjustPathinfo($path_info, $document_root)
    {
        //$this->basepath=ltrim($this->basepath,'/').'/';
        $basepath=$this->basepath;
        $input_path=parse_url(DNSuperGlobal::G()->_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $script_filename=DNSuperGlobal::G()->_SERVER['SCRIPT_FILENAME'];
        $path_info=substr($document_root.$input_path, strlen($basepath));
        $path_info=ltrim($path_info, '/').'/';
        $blocks=explode('/', $path_info);

        $path_info='';
        $has_file=false;
        foreach ($blocks as $i=>$v) {
            if (!$has_file && substr($v, -strlen('.php'))==='.php') {
                $has_file=true;
                $path_info.=substr($v, 0, -strlen('.php')).'/';
                if (!($blocks[$i+1])) {
                    $path_info.='index';
                    break;
                }
            } else {
                $path_info.=$v.'/';
            }
        }
        $path_info=rtrim($path_info, '/');
        
        return $path_info;
    }
    public function onURL($url=null)
    {
        if (strlen($url)>0 && '/'==$url{0}) {
            return $url;
        };
        $document_root=DNSuperGlobal::G()->_SERVER['DOCUMENT_ROOT'];
        $base_url=substr($this->basepath, strlen($document_root));
        $input_path=parse_url($url, PHP_URL_PATH);
        
        $blocks=explode('/', $input_path);
        
        $basepath=$this->basepath;
        $new_path='';
        $l=count($blocks);
        foreach ($blocks as $i=> $v) {
            if ($i+1>=$l) {
                break;
            }
            $class_names=array_slice($blocks, 0, $i+1);
            $file=$basepath.'/'.implode('/', $class_names).'.php';
            $path_info=isset($blocks[$i])?array_slice($blocks, -$i-1):[];
            $path_info=implode('/', $path_info);
            if (is_file($file)) {
                $new_path=$base_url.'/'.implode('/', $class_names).'.php'.($path_info?'/'.$path_info:'');
            }
        }
        if (!$new_path) {
            return $new_path;
        }
    
        $new_get=[];
        parse_str(parse_url($url, PHP_URL_QUERY), $new_get);
        
        $get=array_merge($new_get, $new_get);
        $query=$get?'?'.http_build_query($get):'';
        $ret=$new_path.$query;
        return $ret;
    }
    // abc/d/e.php/g/h?act=z  abc/d/e/g
    public function hook($route)
    {
        $route->setURLHandler([$this,'onURL']); //todo once ?
        
        $route->path_info=$this->adjustPathinfo($route->path_info, $route->document_root);
        $route->calling_path=$route->path_info;
    }
}


class DBReusePoolProxy
{
    use DNSingleton;
    
    public $tag_write='0';
    public $tag_read='1';
    
    protected $db_create_handler;
    protected $db_close_handler;
    protected $db_queue_write;
    protected $db_queue_write_time;
    protected $db_queue_read;
    protected $db_queue_read_time;
    public $max_length=100;
    public $timeout=5;
    public function __construct()
    {
        $this->db_queue_write=new \SplQueue();
        $this->db_queue_write_time=new \SplQueue();
        $this->db_queue_read=new \SplQueue();
        $this->db_queue_read_time=new \SplQueue();
    }
    public function init($max_length=10, $timeout=5, $dbm=null)
    {
        $this->max_length=$max_length;
        $this->timeout=$timeout;
        $this->proxy($dbm);
        return $this;
    }
    public function setDBHandler($db_create_handler, $db_close_handler=null)
    {
        $this->db_create_handler=$db_create_handler;
        $this->db_close_handler=$db_close_handler;
    }
    protected function getObject($queue, $queue_time, $db_config, $tag)
    {
        if ($queue->isEmpty()) {
            return ($this->db_create_handler)($db_config, $tag);
        }
        $db=$queue->shift();
        $time=$queue_time->shift();
        $now=time();
        $is_timeout =($now-$time)>$this->timeout?true:false;
        if ($is_timeout) {
            ($this->db_close_handler)($db, $tag);
            return ($this->db_create_handler)($db_config, $tag);
        }
        return $db;
    }
    protected function reuseObject($queue, $queue_time, $db)
    {
        if (count($queue)>=$this->max_length) {
            ($this->db_close_handler)($db, $tag);
            return;
        }
        $time=time();
        $queue->push($db);
        $queue_time->push($time);
    }
    public function onCreate($db_config, $tag)
    {
        if ($tag!=$this->tag_write) {
            return $this->getObject($this->db_queue_write, $this->db_queue_write_time, $db_config, $tag);
        } else {
            return $this->getObject($this->db_queue_read, $this->db_queue_read_time, $db_config, $tag);
        }
    }
    public function onClose($db, $tag)
    {
        if ($tag!=$this->tag_write) {
            return $this->reuseObject($this->db_queue_write, $this->db_queue_write_time, $db);
        } else {
            return $this->reuseObject($this->db_queue_read, $this->db_queue_read_time, $db);
        }
    }
    public function proxy($dbm)
    {
        if (!$dbm) {
            return;
        }
        list($db_create_handler, $db_close_handler)=$dnm->getDBHandler();
        $this->setDBHandler($db_create_handler, $db_close_handler);
        $dnm->setDBHandler([$this,'onCreate'], [$this,'onClose']);
    }
}
class ProjectCommonAutoloader
{
    use DNSingleton;
    protected $path_common;
    public function init($options)
    {
        $this->path_common=isset($options['fullpath_project_share_common'])??'';
        return $this;
    }
    public function run()
    {
        spl_autoload_register([$this,'_autoload']);
    }
    public function _autoload($class)
    {
        if (strpos($class, '\\')!==false) {
            return;
        }
        $path_common=$this->path_common;
        if (!$path_common);
        return;
        $flag=preg_match('/Common(Service|Model)$/', $class, $m);
        if (!$flag) {
            return;
        }
        $file=$path_common.'/'.$class.'.php';
        if (!$file || !file_exists($file)) {
            return;
        }
        require $file;
    }
}
class ProjectCommonConfiger extends DNConfiger
{
    public $fullpath_config_common;

    public function init($options=[], $context=null)
    {
        $this->fullpath_config_common=isset($options['fullpath_config_common'])??'';
        return parent::init($options, $context);
    }
    protected function loadFile($basename, $checkfile=true)
    {
        $common_config=[];
        if ($this->fullpath_config_common) {
            $file=$this->fullpath_config_common.$basename.'.php';
            if (is_file($file)) {
                $common_config=(function ($file) {
                    return include($file);
                })($file);
            }
        }
        $ret=parent::loadFile($basename, $checkfile);
        $ret=array_merge($common_config, $ret);
        return $ret;
    }
}
//TODO don't do so more;
class FunctionDispatcher
{
    use DNSingleton;
    
    protected $path_info;
    public $prefix='action_';
    public $default_callback='action_index';
    public function hook($route)
    {
        $this->path_info=$route->path_info;
        $flag=$this->runRoute();
        if ($flag) {
            $route->stopRunDefaultHandler();
        }
    }
    public function runRoute()
    {
        $route=DNRoute::G();
        $post=($route->request_method==='POST')?$route->prefix_post:'';
        $callback=$this->prefix.$post.$this->path_info;
        $path_info=$this->path_info?:'index';
        $prefix=str_replace('\\', '/', $this->prefix);
        $fullpath=$prefix.$path_info;
        $blocks=explode('/', $fullpath);
        $method=array_pop($blocks);
        $classname=implode('\\', $blocks);
        // a\b
        if ($classname) {
            if (class_exists($classname)) {
                $class=new $classname();
                $method=$post?$post.$method:$method;
                $callback=[$class,$method];
            } else {
                $callback=null;
            }
        } else {
            $method=$post?$post.$path_info:$path_info;
            $method=$this->prefix.$method;
            $callback=$method;
            if (!is_callable($callback)) {
                var_dump('xx'.$callback);

                $callback=null;
            }
        }
        
        if ($callback) {
            ($callback)();
            return true;
        }
        if (is_callable($this->default_callback)) {
            ($this->default_callback)();
            return true;
        } else {
            //($route->the404Handler)();
            return false;
        }
    }
}
class FunctionView extends DNView
{
    public $prefix='view_';
    public $head_callback;
    public $foot_callback;
    
    private $callback;
    
    public function init($options=[], $context=null)
    {
        $ret=parent::init($options, $context);
        $options=DNMVCS::G()->options;
        $this->head_callback=$options['function_view_head']??'';
        $this->foot_callback=$options['function_view_foot']??'';
        return $ret;
    }
    public function _Show($data=[], $view)
    {
        $this->view=$view;
        $this->data=array_merge($this->data, $data);
        $data=null;
        $view=null;
        extract($this->data);
        
        if (isset($this->before_show_handler)) {
            ($this->before_show_handler)($data, $this->view);
        }
        $this->prepareFiles();
        
        
        if ($this->head_callback) {
            if (is_callable($this->head_callback)) {
                ($this->head_callback)($this->data);
            }
        } else {
            if ($this->head_file) {
                $this->head_file=rtrim($this->head_file, '.php').'.php';
                include($this->path.$this->head_file);
            }
        }
        
        $this->callback=$this->prefix.str_replace('/', '__', preg_replace('/^Main\//', '', $this->view));
        if (is_callable($this->callback)) {
            ($this->callback)($this->data);
        } else {
            if (!is_file($this->view_file)) {
                //echo "DNMVCS FunctionView: Not callback {$this->callback}; not file $this->view_file";
                return;
            }
            include($this->view_file);
        }
        
        if ($this->head_callback) {
            if (is_callable($this->foot_callback)) {
                ($this->foot_callback)($this->data);
            }
        } else {
            if ($this->foot_file) {
                $this->foot_file=rtrim($this->foot_file, '.php').'.php';
                include($this->path.$this->foot_file);
            }
        }
    }
    public function _ShowBlock($view, $data=null)
    {
        $this->view=$view;
        $this->data=array_merge($this->data, $data);
        $data=null;
        $view=null;
        extract($this->data);
        
        $this->callback=$this->prefix.str_replace('/', '__', preg_replace('/^Main\//', '', $this->view));
        if (is_callable($this->callback)) {
            ($this->callback)($this->data);
        } else {
            if (!is_file($this->view_file)) {
                echo "NMVCS FunctionView ShowBlock: Not callback {$this->callback}; No file {$this->view_file}";
                return;
            }
            include($this->view_file);
        }
    }
}
class FacadesAutoLoader
{
    use DNSingleton;

    protected $prefix='';
    protected $facades_map=[];
    
    protected $is_loaded=false;
    protected $is_inited=false;
    
    public function init($namespace_facades='', $facades_map=[], $namespace='')
    {
        if (substr($namespace_facades, 0, 1)!=='\\') {
            $namespace_facades=$namespace.'\\'.$namespace_facades;
        }
        $namespace_facades=ltrim($namespace_facades, '\\');
        $this->prefix=$namespace_facades.'\\Facade\\';
        
        $this->is_init=true;
        return $this;
    }
    public function run()
    {
        if ($this->is_loaded) {
            return;
        }
        $this->is_loaded=true;
        spl_autoload_register([$this,'_autoload']);
    }
    
    public function _autoload($class)
    {
        if (substr($class, 0, strlen($this->prefix))!==$this->prefix) {
            return;
        }
        
        $blocks=explode('\\', $class);
        $basename=array_pop($blocks);
        $namespace=implode('\\', $blocks);
        
        $code="namespace $namespace{ class $basename extends \\DNMVCS\\FacadesBase{} }";
        eval($code);
    }
    public function getFacadesCallback($class, $name)
    {
        foreach ($this->facades_map as $k=>$v) {
            if ($k===$class) {
                $class=$v;
                break;
            }
        }
        // DNexception::ThrowOn(!class_exists($class),"No Class");
        $object=call_user_func([$class,'G']);
        return [$object,$name];
    }
}
class FacadesBase
{
    use DNSingleton;
    
    public static function __callStatic($name, $arguments)
    {
        $callback=FacadesAutoLoader::G()->getFacadesCallback(static::class, $name);
        $ret=call_user_func_array($callback, $arguments);
        return $ret;
    }
}
class DNMVCSExt
{
    use DNSingleton;
    use DNDI;
    
    const DEFAULT_OPTIONS_EX=[
    
            'use_function_view'=>false,
                'function_view_head'=>'view_header',
                'function_view_foot'=>'view_footer',
            'use_function_dispatch'=>false,
            'use_common_configer'=>false,
                'fullpath_project_share_common'=>'',
            'use_common_autoloader'=>false,
                'fullpath_config_common'=>'',
            'use_strict_db'=>false,
            
            'use_facades'=>false,
            'facades_namespace'=>'Facades',
            'facades_map'=>[],
            
            'use_session_auto_start'=>false,
            'session_auto_start_name'=>'DNSESSION',
            
            'mode_onefile'=>false,
            'mode_onefile_key_for_action'=>null,
            'mode_onefile_key_for_module'=>null,
            
            'mode_dir'=>false,
            'mode_dir_basepath'=>null,
            'mode_dir_index_file'=>'',
            'mode_dir_use_path_info'=>true,
            'mode_dir_key_for_module'=>true,
            'mode_dir_key_for_action'=>true,
            
            'use_db_reuse'=>false,
            'db_reuse_size'=>0,
            'db_reuse_timeout'=>5,
        ];
    public function init($dn)
    {
        $ext_options=$dn->options['ext'];
        
        $options=array_replace_recursive(self::DEFAULT_OPTIONS_EX, $ext_options);
        
        if ($options['use_common_autoloader']) {
            ProjectCommonAutoloader::G()->init($options)->run();
        }
        
        if ($options['use_common_configer']) {
            $dn->initConfiger(DNConfiger::G(ProjectCommonConfiger::G()));
            $dn->isDev=DNConfiger::G()->_Setting('is_dev')??$dn->isDev;
            // 可能要调整测试状态
        }
        if ($options['use_function_view']) {
            $dn->initView(DNView::G(FunctionView::G()));
        }
        if ($options['use_strict_db']) {
            DNDBManager::G()->setBeforeGetDBHandler([static::G(),'checkDBPermission']);
        }
        
        if ($options['mode_onefile']) {
            RouteHookOneFileMode::G()->init($options['mode_onefile_key_for_action'], $options['mode_onefile_key_for_module']);
            DNRoute::G()->addRouteHook([RouteHookOneFileMode::G(),'hook']);
        }
        if ($options['mode_dir']) {
            RouteHookDirectoryMode::G()->init($options);
            DNRoute::G()->addRouteHook([RouteHookDirectoryMode::G(),'hook']);
        }
        
        if ($options['use_function_dispatch']) {
            DNRoute::G()->addRouteHook([FunctionDispatcher::G(),'hook']);
        }
        if ($options['use_session_auto_start']) {
            DNMVCS::session_start(['name'=>$options['session_auto_start_name']]);
        }
        
        if ($options['use_facades']) {
            $namespace=$dn->options['namespace']??'';
            FacadesAutoLoader::G()->init($options['facades_namespace'], $options['facades_map'], $namespace)->run();
        }
        if ($options['use_db_reuse']) {
            DBReusePoolProxy::G()->init($options['db_reuse_size'], $db_reuse_timeout=$options['db_reuse_timeout'], DNDBManager::G());
        }
    }
    public function checkDBPermission()
    {
        if (!DNMVCS::Developing()) {
            return;
        }
        
        $backtrace=debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10);
        $caller_class='';
        $base_class=get_class(DNMVCS::G());
        foreach ($backtrace as $i=>$v) {
            if ($v['class']===$base_class) {
                $caller_class=$backtrace[$i+1]['class'];
                break;
            }
        }
        $namespace=DNMVCS::G()->options['namespace'];
        $namespace_controller=DNMVCS::G()->options['namespace_controller'];
        $default_controller_class=DNMVCS::G()->options['default_controller_class'];
        $namespace_controller.='\\';
        do {
            if ($caller_class==$default_controller_class) {
                DNMVCS::ThrowOn(true, "DB Can not Call By Controller");
            }
            if (substr($caller_class, 0, strlen($namespace_controller))==$namespace_controller) {
                DNMVCS::ThrowOn(true, "DB Can not Call By Controller");
            }
            if (substr($caller_class, 0, strlen("$namespace\\Service\\"))=="$namespace\\Service\\") {
                DNMVCS::ThrowOn(true, "DB Can not Call By Service");
            }
            if (substr($caller_class, 0-strlen("Service"))=="Service") {
                DNMVCS::ThrowOn(true, "DB Can not Call By Service");
            }
        } while (false);
    }

    public function dealMapAndRewrite($rewrite_map, $route_map)
    {
        RouteHookMapAndRewrite::G()->init($rewrite_map, $route_map);
        DNRoute::G()->addRouteHook([RouteHookMapAndRewrite::G(),'hook'], true);
    }
}
//mysqldump -uroot -p123456 DnSample -d --opt --skip-dump-date --skip-comments | sed 's/ AUTO_INCREMENT=[0-9]*\b//g' >../data/database.sql
