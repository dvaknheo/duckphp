# 扩展教程
[toc]

## 使用 DuckPHP 的扩展

DuckPHP 扩展的加载是通过选项里添加
$options['ext']数组实现的

    扩展映射 ,$ext_class => $options。
    
    $ext_class 为扩展的类名，如果找不到扩展类则不启用。
    
    $ext_class 满足组件接口。在初始化的时候会被调用。
    $ext_class->init(array $options,$context=null);
    
    如果 $options 为  false 则不启用，
    如果 $options 为 true ，则会把当前 $options 传递进去。

DuckPHP/Core 的其他组件如 Configer, Route, View, AutoLoader 默认都在这调用

##  组件类

组件类满足以下接口

```
interface ComponentInterface
{
    public $options;/* array() */;
    public static function G():this;
    public init(array $options, $contetxt=null):this;
}
```

DuckPHP 的扩展都放在 DuckPHP\\Ext 命名空间里
下面按字母顺序介绍这些扩展的作用
按选项，说明，公开方法，一一介绍。

SingletonEx 可变单例

\*Helper 是各种快捷方法。




## 默认启用的扩展

默认没启用的扩展
CallableView

DBReusePoolProxy

FacadesAutoLoader

JsonRpcExt

PluginForSwooleHttpd

RedisManager

RedisSimpleCache

RouteHookDirecotoryMode

RouteHookOnFileMode

    public function __construct()
    {
        $this->options = array_replace_recursive($this->options, (new parent())->options); //merge parent's options;
        return parent::__construct();
    }
StricCheck

如何写扩展

把你的应用变成扩展


## 简介
