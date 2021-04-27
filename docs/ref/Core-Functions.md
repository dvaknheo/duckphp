# 全局函数
[toc]

需要选项 `user_global_functions` kernel ，  （默认开启） 才能用。

都是以 两条下划线开头。

都是 DuckPhp\Core\App 类的函数的映射，


你可以在 onPrepare() 时候 替换相应函数。


目前一共有：

### 显示

View 里不想  use ViewHelper 的情况

- __l 对应 App::L();

- __h 对应 App::H();

- __hl 对应 App::Hl();

- __url 对应 App::URL();

-  __json 对应 App::Json(); 

-  __display 对应 App::Display();


### 调试

调试语句，全局性的

- __var_dump() 对应 App::var_dump();
- __is_debug() 对应 App::IsDebug()， 注意这个函数和调用者名称不一致
- __is_real_debug() 对应 App::IsRealDebug()， 注意这个函数和调用者名称不一致
- __platform() 对应 App::Platform();
- __trace_dump 对应 App::TraceDump();
- __debug_log() 对应 App::DebugLog();

