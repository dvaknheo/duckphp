<?php
namespace DNMVCS;

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
        if (!$path_common) {
            return;
        }
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
