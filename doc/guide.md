
DuckPhp 类中的在其他 Helper 里出现的方法。

  0 => string 'OnQuery' (length=7)
  1 => string 'RunQuickly' (length=10)
  2 => string 'Blank' (length=5)
  3 => string 'system_wrapper_replace' (length=22)
  4 => string 'system_wrapper_get_providers' (length=28)
  5 => string 'On404' (length=5)
  6 => string 'OnDefaultException' (length=18)
  7 => string 'OnDevErrorHandler' (length=17)
  

Helper 类为什么要在 Helper 目录下，

原因，配合 cloneHelper 用。

System 目录下，为什么以 Base 开头。

因为一开始是 Base 目录

为什么会有个“我觉得恶心的”G() 单字母静态方法

你可以把 ::G()-> 看成和 facades 类似的门面方法。
可变单例是 DuckPhp 的核心。
你如果引入第三方包的时候，不满意默认实现，可以通过可变单例来替换他