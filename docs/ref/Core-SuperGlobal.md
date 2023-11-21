# DuckPhp\Core\SuperGlobal
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

内置 trait Core_SuperGlobal 主要用于超全局变量处理
```php
    public static function GET($key = null, $default = null)
    public static function POST($key = null, $default = null)
    public static function REQUEST($key = null, $default = null)
    public static function COOKIE($key = null, $default = null)
    public static function SERVER($key = null, $default = null)
    public static function SESSION($key = null, $default = null)
    public static function FILES($key = null, $default = null)
```
这些对应于超全局变量 $_GET[$key]??$value; 类推。如果宏 \_\_SUPERGLOBAL_CONTEXT 被定义，那么将 获得 (\_\_SUPERGLOBAL_CONTEXT)()->\_GET 等
```php
    public static function SessionSet($key, $value)
    public static function SessionUnset($key)
    public static function SessionGet($key, $default = null)

```
因为 Session 不仅仅读取，还有写入，所以用 SessionGet  /SessionSet 对称方法 。因为 \_\_SUPERGLOBAL_CONTEXT ，还有了 SessionUnset


```php
    public static function CookieSet($key, $value, $expire = 0)
```
因为 Cookie 不仅仅读取，还有写入，所以用 CookieSet 。


    public static function CookieGet($key, $default = null)
对称， CookieGet / CookieSet


DuckPhp 添加协程支持，

如果启用宏 `__SUPERGLOBAL_CONTEXT` 会在引用到全局变量的地方 如

`$_SERVER =>  (__SUPERGLOBAL_CONTEXT)()->_SERVER `

`SuperGlobalContext` 是对 超全局变量的包裹
`SuperGlobalContext::DefineSuperGlobalContext()`  则定义这么个指向 SuperGlobalContext::G 的宏。
`superglobal_auto_extend_method` 把 LoadSuperGlobalAll ， SaveSuperGlobalAll 添加到 App 类的 静态方法
`superglobal_auto_define` 在 init 的时候 SuperGlobalContext::DefineSuperGlobalContext   




        'superglobal_auto_define' => false,

    protected function initOptions(array $options)

    public static function DefineSuperGlobalContext()

    public static function LoadSuperGlobalAll()

    public static function SaveSuperGlobalAll()

    public function _LoadSuperGlobalAll()

    public function _SaveSuperGlobalAll()

    public static function LoadSuperGlobal($key)

    public static function SaveSuperGlobal($key)

    public function _LoadSuperGlobal($key)

    public function _SaveSuperGlobal($key)

    public static function GET($key = null, $default = null)

    public static function POST($key = null, $default = null)

    public static function REQUEST($key = null, $default = null)

    public static function COOKIE($key = null, $default = null)

    public static function SERVER($key = null, $default = null)

    public static function SESSION($key = null, $default = null)

    public static function FILES($key = null, $default = null)

    public static function SessionSet($key, $value)

    public static function SessionUnset($key)

    public static function SessionGet($key, $default = null)

    public static function CookieSet($key, $value, $expire = 0)

    public static function CookieGet($key, $default = null)

    protected function getSuperGlobalData($superglobal_key, $key, $default)

    public function _GET($key = null, $default = null)

    public function _POST($key = null, $default = null)

    public function _REQUEST($key = null, $default = null)

    public function _COOKIE($key = null, $default = null)

    public function _SERVER($key = null, $default = null)

    public function _SESSION($key = null, $default = null)

    public function _FILES($key = null, $default = null)

    public function _SessionSet($key, $value)

    public function _SessionUnset($key)

    public function _CookieSet($key, $value, $expire = 0)

    public function _SessionGet($key, $default = null)

    public function _CookieGet($key, $default = null)

