# DuckPhp\Ext\Ext-RouteHookResource

## 简介
用于 __res 实现

## 选项
        'path' => '',

        'path_resource' => 'res',
资源目录
        'controller_resource_prefix' => '',
资源前缀

        'controller_url_prefix' => '',

## 方法
    public static function Hook($path_info)

    protected function initContext(object $context)

    public function _Hook($path_info)


## 说明

一般用 插件模式的 资源处理
比如

res/ 目录，对应 admin/res/ 之类等


    public function cloneResource($force = false, &$info = '')

    protected function get_dest_dir($path_parent, $path)

    public function copy_dir($source, $path_parent, $path, $force = false, &$info = '')

    protected function check_files_exist($source, $dest, $files, &$info)

    protected function create_directories($dest, $files, &$info)
