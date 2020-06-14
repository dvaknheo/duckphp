# DuckPhp\Ext\RouteHookDirectoryMode

## 简介
`组件类`路由钩子管理器
##### 
## 选项
无选项
## 公开方法

    public function attachPreRun()
    public function attachPostRun()
    public function detach()
    public function getHookList()
    public function setHookList($hook_list)
    public function moveBefore($new, $old)
    public function insertBefore($new, $old)
    public function removeAll($name)
    public function dump()

## 详解

一个简单的路由钩子管理器。