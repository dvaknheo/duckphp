<?php
namespace DNMVCS;

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