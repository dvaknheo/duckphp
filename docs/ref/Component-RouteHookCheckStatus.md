# DuckPhp\Component\RouteHookCheckStatus
[toc]


## 简介
这个路由钩子用于检查是否在维护中或者需要安装

隐藏选项
`is_maintain` , `need_install`,`install`

## 选项

        'error_maintain' => null,
维修页面view ，类似 error_404
        'error_need_install' => null,
需要安装的页面
## 方法
都是路由方法

    public static function Hook($path_info)

    protected function initContext(object $context)

    public function doHook($path_info)

    protected function showMaintain()

    protected function showNeedInstall()

## 说明
默认启用，

1. 如果设置里的 `duckphp_is_maintain` 或者是当前应用的 `is_maintain` 启用，则报错，或显示 maintain_view` 视图

2. 如果 `need_install` 启用， 并且 isInstalled() 方法返回 false 则报错，或显示 `need_install_view`



