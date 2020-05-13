# 扩展教程
[toc]

## 使用 DuckPhp 的扩展

DuckPhp 扩展的加载是通过选项里添加
$options['ext']数组实现的

    扩展映射 ,$ext_class => $options。
    
    $ext_class 为扩展的类名，如果找不到扩展类则不启用。
    
    $ext_class 满足组件接口。在初始化的时候会被调用。
    $ext_class->init(array $options,$context=null);
    
    如果 $options 为  false 则不启用，
    如果 $options 为 true ，则会把当前 $options 传递进去。

DuckPhp/Core 的其他组件如 Configer, Route, View, AutoLoader 默认都在这调用

## 默认没启用的扩展

所有的 DuckPhp 自带扩展 可以在 [参考文档](ref/index.md) 里按字母顺序查看

DuckPhp 只启用了 DBManager 一个扩展。

其他扩展按功能如下

RouteHookDirecotoryMode , RouteHookOnFileMode

这些路由钩子的扩展可以在  路由教程 里查看



RedisManager RedisSimpleCache



JsonRpcExt

StrictCheck 严格检查

FacadesAutoLoader

Misc



CallableView

DBReusePoolProxy

PluginForSwooleHttpd



## 编写扩展

假如你要做个自己的扩展 MyExtention , 你的类只要能实现这样的调用。

MyExtends::G()->init(array $options, $contetxt=null);

非强制实现 DuckPhp\\Core\\ComponentInterface();

一般要提供 MyExtends::G()->options 保存自己的 选项。

### ComponentInterface 接口



### 编写扩展的技巧

一些技巧，继承父类

```
    public function __construct()
    {
        $this->options = array_replace_recursive($this->options, (new parent())->options); //merge parent's options;
        parent::__construct();
    }
```

extendComponents ，如果你要把你的类给助手类使用。



```
$context->extendComponents(
                [
                    'assignImportantRoute' => [static::class.'::G','assignImportantRoute'],
                    'assignRoute' => [static::class.'::G','assignRoute'],
                    'routeMapNameToRegex' => [static::class.'::G','routeMapNameToRegex'],
                ],
                ['A']
            );
```



## 把你的独立工程作为扩展给第三方使用



##  组件类

组件类满足以下接口

```

```

DuckPhp 的扩展都放在 DuckPhp\\Ext 命名空间里
下面按字母顺序介绍这些扩展的作用
按选项，说明，公开方法，一一介绍。

SingletonEx 可变单例

\*Helper 是各种快捷方法。






如何写扩展


