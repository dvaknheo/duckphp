# 全局函数
[toc]

需要选项 `user_global_functions`  [DuckPhp\Core\KernelTrait](Core-KernelTrait.md)   （默认开启） 才能用。

DuckPhp 尽量避免污染全局环境，这些函数.

都是以 两条下划线开头。

都是  [DuckPhp\Core\CoreHelper](Core-CoreHelper.md) 类的函数的映射，


目前一共有：

### 显示

    function __h(...$args)
\_\_h 对应 CoreHelper::H(); HTML 编码

    function __l($str, $args = [])
\_\_l 对应 CoreHelper::L(); 语言处理函数，后面的关联数组替换 '{$key}'

    function __hl($str, $args = [])
\_\_hl 对应 CoreHelper::Hl(); 对语言处理后进行 HTML 编码

    function __json($data)
\_\_json 对应 CoreHelper::Json(); json 编码，用于向 javascript  传送数据

    function __url($url)
\_\_url 对应 CoreHelper::URL($url); 获得资源相对 url 地址

    function __res($url)
\_\__res 对应 CoreHelper::__res($url); 获取 外部资源地址

    function __domain($use_scheme = false)
\_\_domain 对应 CoreHelper::domain();  获得带协议头的域名

    function __display(...$args)
\_\_display 对应 `CoreHelper::Display()` 包含下一个 `$view` ， 如果 `$data = null` 则带入所有当前作用域的变量。 否则带入 `$data` 关联数组的内容。用于嵌套包含视图。


### 调试

调试语句，全局性的

    function __var_dump(...$args)
\_\_var_dump() 对应 CoreHelper::var_dump();  var_dump() 调试状态下 Dump 当前变量，替代 var_dump，和 var_dump 类似，实现可以修改

    function __trace_dump()
\_\_trace_dump() 对应 CoreHelper::TraceDump(); 调试状态下，查看当前堆栈，打印当前堆栈，类似 debug_print_backtrce(2)

    function __debug_log($str, $args = [])
\_\_debug_log() 对应 CoreHelper::DebugLog($message, array $context = array()) 对应调试状态下 Log 当前变量。

    function __is_debug()
\_\_is_debug() 对应 CoreHelper::IsDebug(); 判断是否在调试状态, 默认读取选项 is_debug 和设置字段里的 duckphp_is_debug

    function __platform()
\_\_platform() 对应 CoreHelper::Platform(); 获得当前所在平台,默认读取选项和设置字段里的 duckphp_platform，用于判断当前是哪台机器等

    function __is_real_debug()
\_\_is_real_debug() 对应 CoreHelper::IsRealDebug(); 切莫乱用。用于环境设置为其他。比如线上环境，但是还是要特殊调试的场合。 如果没被接管，和 IsDebug() 一致。
    
    function __logger()
\_\_logger() 对应 CoreHelper::Logger();  获得`Psr\Log\LoggerInterface`日志对象，便于不同级别的调试

    function __var_log($var)
\_\_var_log() 对应 CoreHelper::VarLog();  在日志打印当前变量 
    function __h($str)

