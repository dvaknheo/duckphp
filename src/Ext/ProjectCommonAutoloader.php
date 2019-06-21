<?php
namespace DNMVCS\Ext;

use DNMVCS\SingletonEx;

class ProjectCommonAutoloader
{
    use SingletonEx;
    const DEFAULT_OPTIONS_EX=[
        'fullpath_project_share_common'=>'',
    ];
    protected $path_common;
    public function init($options=[], $context=null)
    {
        $this->path_common=isset($options['fullpath_project_share_common'])??'';
        if ($context) {
            $this->run();
        }
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
