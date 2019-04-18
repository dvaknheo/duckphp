<?php
namespace DNMVCS;

use DNMVCS\DNConfiger;

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
