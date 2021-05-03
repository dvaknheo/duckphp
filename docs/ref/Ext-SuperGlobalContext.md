# DuckPhp\Ext\SuperGlobalContext
[toc]
## 简介
 超全局变量包容类
## 选项
全部选项

        'superglobal_auto_extend_method' => false,
自动扩充方法

        'superglobal_auto_define' => false,
自动定义  `__SUPERGLOBAL_CONTEXT`
## 公开方法


public static function DefineSuperGlobalContext()
public static function LoadSuperGlobalAll() //读入所有 超全局变量
public static function SaveSuperGlobalAll() //保存所有 超全局变量
public static function LoadSuperGlobal($name) //读入超全局变量
public static function SaveSuperGlobal($name) //保存超全局变量

## 详解


DuckPhp 添加协程支持，

如果启用宏 `__SUPERGLOBAL_CONTEXT` 会在引用到全局变量的地方 如

`$_SERVER =>  (__SUPERGLOBAL_CONTEXT)()->_SERVER `

`SuperGlobalContext` 是对 超全局变量的包裹
`SuperGlobalContext::DefineSuperGlobalContext()`  则定义这么个指向 SuperGlobalContext::G 的宏。
`superglobal_auto_extend_method` 把 LoadSuperGlobalAll ， SaveSuperGlobalAll 添加到 App 类的 静态方法
`superglobal_auto_define` 在 init 的时候 SuperGlobalContext::DefineSuperGlobalContext