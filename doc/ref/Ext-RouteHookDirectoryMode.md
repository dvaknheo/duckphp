# Ext\RouteHookDirectoryMode

## 简介

## 选项
'mode_dir_index_file'=>'',
'mode_dir_use_path_info'=>true,
'mode_dir_key_for_module'=>true,
'mode_dir_key_for_action'=>true,

## 公开方法


## 详解


    public function __construct()
    public function init(array $options, object $context = null)
    protected function adjustPathinfo($basepath, $path_info)
    public function onURL($url = null)
    public static function Hook($path_info)
    public function _Hook($path_info)