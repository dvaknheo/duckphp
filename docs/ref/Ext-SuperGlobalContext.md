# DuckPhp\Ext\SuperGlobalContext
[toc]
## 简介
 超全局变量包容类
## 选项
全部选项

        'superglobal_auto_extend_method' => false,
自动扩充 SuperGlobalContext 的静态方法

        'superglobal_auto_define' => false,
初始化时定义  `__SUPERGLOBAL_CONTEXT`宏

## 公开方法


    public function __construct()
    protected function initOptions(array $options)
    protected function initContext(object $context)
重写入口

    public static function DefineSuperGlobalContext()
定义超级变量宏

    public static function LoadSuperGlobalAll()
    public static function SaveSuperGlobalAll()
读入/保存所有超全局变量

    public static function LoadSuperGlobal($key)
    public static function SaveSuperGlobal($key)
读入/保存超全局变量

    public function _LoadSuperGlobalAll()
    public function _SaveSuperGlobalAll()
    public function _LoadSuperGlobal($key)
    public function _SaveSuperGlobal($key)
相应内部实现函数

## 详解


DuckPhp 添加协程支持，

如果启用宏 `__SUPERGLOBAL_CONTEXT` 会在引用到全局变量的地方 如

`$_SERVER =>  (__SUPERGLOBAL_CONTEXT)()->_SERVER `

`SuperGlobalContext` 是对 超全局变量的包裹
`SuperGlobalContext::DefineSuperGlobalContext()`  则定义这么个指向 SuperGlobalContext::G 的宏。
`superglobal_auto_extend_method` 把 LoadSuperGlobalAll ， SaveSuperGlobalAll 添加到 App 类的 静态方法
`superglobal_auto_define` 在 init 的时候 SuperGlobalContext::DefineSuperGlobalContext   



