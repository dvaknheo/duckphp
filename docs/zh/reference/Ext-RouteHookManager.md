# DuckPhp\Ext\RouteHookManager

## 简介
`伪组件类` 路由钩子管理器
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
    public function append($name)
    public function dump()

## 详解

RouHookManager 并非是一个路由钩子，而是一个简单的路由钩子管理器。

attachPreRun 管理 运行前钩子， attachPostRun  管理运行后勾子。 detach释放绑定。

getHookList 获得钩子列表  setHookList 设置钩子列表
moveBefore insertBefore removeAll

dump();  打印钩子列表    
