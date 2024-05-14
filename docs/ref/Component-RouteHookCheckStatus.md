# DuckPhp\Component\RouteHookCheckStatus
[toc]


## 简介
这个路由钩子用于检查状态

隐藏选项
`is_maintain` , `need_install`,`install`

## 选项
全部选项


        'maintain_view' => null,

        'need_install_view' => null,

## 方法
都是路由方法

    public static function Hook($path_info)

    protected function initContext(object $context)

    public function doHook($path_info)

## 说明
默认启用，

1. 如果设置里的 `duckphp_is_maintain` 或者是当前应用的 `is_maintain` 启用，则报错，或显示 maintain_view` 视图

2. 如果 `need_install` 启用， 并且 isInstalled() 方法返回 false 则报错，或显示 `need_install_view`
        'error_maintain' => null,

        'error_need_install' => null,

    protected function showMaintain()

    protected function showNeedInstall()

