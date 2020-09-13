# 全局函数
[toc]

需要开启 user_global_functions （默认开启） 才能用。

view 的函数 全局化 
这里的函数用于 view 里不想  use 的情况


目前一共有：
__h
__l
__hl
__url
__display

为了通用化，这些函数的参数都是 (...$args) 动态参数

你可以在 onPrepare() 时候 替换相应函数。
