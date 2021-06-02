# 全局函数
[toc]

需要选项 `user_global_functions`  [DuckPhp\Core\KernelTrait](Core-KernelTrait.md)   （默认开启） 才能用。

DuckPhp 尽量避免污染全局环境，这些函数

都是以 两条下划线开头。

都是  [DuckPhp\Core\App](Core-App.md) 类的函数的映射，


你可以在 App::onPrepare() 时候 替换相应函数的实现。


目前一共有：

### 显示

用于 View 里不想  use ViewHelper 的情况

    function __h(...$args)
\_\_h 对应 App::H(); Html 编码

    function __l($str, $args = [])
\_\_l 对应 App::L();

    function __hl($str, $args = [])
\_\_hl 对应 App::Hl();

    function __json($data)
\_\_json 对应 App::Json(); 

    function __url($url)
\_\_url 对应 App::URL($url);

    function __domain($use_scheme = false)
\_\_domain 对应 App::domain();

    function __display(...$args)
\_\_display 对应 App::Display();

### 调试

调试语句，全局性的

    function __var_dump(...$args)
\_\_var_dump() 对应 App::var_dump();  和 var_dump 类似，实现可以修改

    function __trace_dump()
\_\_debug_log() 对应 App::TraceDump();

    function __debug_log($str, $args = [])
\_\_debug_log() 对应 App::DebugLog();

    function __is_debug()
\_\_is_debug() 对应 App::IsDebug();

    function __platform()
\_\_platform() 对应 App::Platform();

    function __is_real_debug()
\_\_is_real_debug() 对应 App::IsRealDebug();

    function __h($str)
    
    function __logger()
\_\__logger() 对应 App::Logger();
