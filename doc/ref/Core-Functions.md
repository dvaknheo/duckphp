# 全局函数
[toc]

需要选项 `user_global_functions` kernel ，  （默认开启） 才能用。

主要用于 这里的函数

和 Model 里不想引入 helper 的情况

目前一共有：

显示用的，用于 view 里不想  use ViewHelper 的情况

- __l 对应 App::L();

- __h 对应 App::H();

- __hl 对应 App::Hl();

- __url 对应 App::URL();

-  __display 对应 App::Display();

用于调试的

- __trace_dump 对应 App::trace_dump();

- __var_dump() 对应 App::var_dump();

- __debug_log() 对应 App::debug_log();

* __is_debug() 对应 App::L()， 注意这个函数和调用者名称不一致

用于 Model

__db() 对应 App::Db();


为了通用化，这些函数的参数都是 (...$args) 动态参数

都是 App 的函数

你可以在 onPrepare() 时候 替换相应函数。

